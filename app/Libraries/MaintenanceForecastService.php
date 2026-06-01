<?php

namespace App\Libraries;

use Config\Database;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class MaintenanceForecastService
{
    protected \CodeIgniter\Database\BaseConnection $db;
    protected DateTimeZone $timezone;
    protected array $allowedIssueTypes = ['preventive', 'inspection', 'calibration'];

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null, ?DateTimeZone $timezone = null)
    {
        $this->db = $db ?? Database::connect();
        $this->timezone = $timezone ?? new DateTimeZone('Asia/Kuala_Lumpur');
    }

    public function getUpcomingForecasts(int $daysAhead = 90): array
    {
        $raw = $this->getUpcomingForecastsRaw($daysAhead);

        return array_map(static function (array $row): array {
            if (($row['last_completed_at'] ?? null) instanceof DateTimeImmutable) {
                $row['last_completed_at'] = $row['last_completed_at']->format('Y-m-d');
            }
            if (($row['next_due_at'] ?? null) instanceof DateTimeImmutable) {
                $row['next_due_at'] = $row['next_due_at']->format('Y-m-d');
            }
            return $row;
        }, $raw);
    }

    public function sendUpcomingDueReminders(int $daysAhead = 30): int
    {
        $forecasts = $this->getUpcomingForecastsRaw($daysAhead);
        if (empty($forecasts)) {
            return 0;
        }

        $notificationService = new NotificationService();
        $sent = 0;

        foreach ($forecasts as $forecast) {
            $assetId = (int) ($forecast['asset_id'] ?? 0);
            if ($assetId <= 0) {
                continue;
            }

            $nextDue = $forecast['next_due_at'] ?? null;
            $windowStart = $nextDue instanceof DateTimeImmutable
                ? $nextDue->sub(new DateInterval('P' . max($daysAhead, 1) . 'D'))
                : (new DateTimeImmutable('now', $this->timezone))->sub(new DateInterval('P' . max($daysAhead, 1) . 'D'));

            $alreadySent = $this->db->table('notifications')
                ->where('type', 'maintenance_due')
                ->where('entity_type', 'asset')
                ->where('entity_id', $assetId)
                ->where('created_at >=', $windowStart->format('Y-m-d H:i:s'))
                ->countAllResults() > 0;

            if ($alreadySent) {
                continue;
            }

            try {
                $notificationService->notifyMaintenanceDue($forecast);
                $sent++;
            } catch (\Throwable $e) {
                log_message('error', 'Maintenance due reminder failed for asset #' . $assetId . ': ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function getUpcomingForecastsRaw(int $daysAhead = 90): array
    {
        $assets = $this->db->table('assets a')
            ->select('a.id, a.name, a.asset_code, a.lab_id, l.name AS lab_name, l.room AS lab_room')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->orderBy('l.name', 'ASC')
            ->orderBy('a.name', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($assets)) {
            return [];
        }

        $assetIds = array_map(static fn(array $row): int => (int) $row['id'], $assets);
        $forecasts = $this->buildForecasts($assetIds);
        $predictionService = new MaintenancePredictionService($this->db, $this->timezone);
        $predictions = [];
        foreach ($predictionService->predictAllAssets() as $prediction) {
            $predictions[(int) ($prediction['id'] ?? 0)] = $prediction;
        }

        // Suppress assets that already have an open maintenance record — no duplicate alerting.
        $openAssetIds = array_flip($this->assetsWithOpenMaintenance($assetIds));

        $now = new DateTimeImmutable('now', $this->timezone);
        $cutoff = $now->add(new DateInterval('P' . max($daysAhead, 1) . 'D'));

        $upcoming = [];
        foreach ($assets as $asset) {
            $assetId = (int) $asset['id'];

            if (isset($openAssetIds[$assetId])) {
                continue;
            }

            $forecast = $forecasts[$assetId] ?? [
                'asset_id' => $assetId,
                'history_count' => 0,
                'interval_days' => 0,
                'basis' => 'model_only',
                'last_completed_at' => null,
                'next_due_at' => null,
            ];
            $nextDue = $forecast['next_due_at'] ?? null;
            $prediction = $predictions[$assetId] ?? null;
            $decision = $prediction['decision'] ?? ['action' => 'monitor', 'label' => 'Normal monitoring', 'priority' => 'low'];
            $riskProbability = (float) ($prediction['risk_probability'] ?? 0.0);
            $riskBand = (string) ($prediction['risk_band'] ?? 'low');

            $diffDays = null;
            $status = 'monitor';
            if ($nextDue instanceof DateTimeImmutable) {
                $diffDays = (int) $now->diff($nextDue)->format('%r%a');
                $status = $diffDays < 0 ? 'overdue' : 'upcoming';
            } elseif ($decision['action'] !== 'monitor') {
                $status = 'predicted';
            }

            // Tighter inclusion: only surface genuinely actionable items.
            $shouldInclude = false;
            if ($nextDue instanceof DateTimeImmutable && $nextDue <= $cutoff) {
                $shouldInclude = true;
            }
            if ($riskBand === 'high' || ($decision['action'] ?? 'monitor') === 'schedule_now') {
                $shouldInclude = true;
            }
            if (! $shouldInclude && $riskBand === 'medium') {
                $pFeatures = $prediction['features'] ?? [];
                $hasCorrectiveEvidence = (float) ($pFeatures['corrective_last_120d'] ?? 0.0) >= 1.0
                    || (float) ($pFeatures['high_priority_last_180d'] ?? 0.0) >= 1.0;
                if ($hasCorrectiveEvidence) {
                    $shouldInclude = true;
                }
            }

            if (! $shouldInclude) {
                continue;
            }

            $upcoming[] = array_merge($asset, $forecast, [
                'days_until' => $diffDays,
                'status' => $status,
                'risk_probability' => $riskProbability,
                'risk_percent' => (int) round($riskProbability * 100),
                'risk_band' => $riskBand,
                'decision' => $decision,
                'decision_label' => $decision['label'] ?? 'Normal monitoring',
                'decision_priority' => $decision['priority'] ?? 'low',
                'reasons' => $prediction['reasons'] ?? [],
                'threshold' => (float) ($prediction['threshold'] ?? 0.5),
            ]);
        }

        usort($upcoming, static function (array $a, array $b) use ($predictions): int {
            $priorityRank = ['high' => 0, 'medium' => 1, 'low' => 2];
            $priorityCompare = ($priorityRank[$a['decision_priority'] ?? 'low'] ?? 2) <=> ($priorityRank[$b['decision_priority'] ?? 'low'] ?? 2);
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }

            // Overdue beats upcoming/predicted at the same priority level.
            $aOverdue = ($a['status'] ?? '') === 'overdue' ? 0 : 1;
            $bOverdue = ($b['status'] ?? '') === 'overdue' ? 0 : 1;
            if ($aOverdue !== $bOverdue) {
                return $aOverdue <=> $bOverdue;
            }

            // Corrective pressure: corrective events + weighted high-priority events.
            $aFeat = $predictions[(int) ($a['asset_id'] ?? 0)]['features'] ?? [];
            $bFeat = $predictions[(int) ($b['asset_id'] ?? 0)]['features'] ?? [];
            $aScore = (float) ($aFeat['corrective_last_120d'] ?? 0.0) + (float) ($aFeat['high_priority_last_180d'] ?? 0.0) * 1.5;
            $bScore = (float) ($bFeat['corrective_last_120d'] ?? 0.0) + (float) ($bFeat['high_priority_last_180d'] ?? 0.0) * 1.5;
            if (abs($aScore - $bScore) > 0.1) {
                return $bScore <=> $aScore;
            }

            // Booking pressure as tiebreaker.
            $aBookings = (float) ($aFeat['booking_count_90d'] ?? 0.0);
            $bBookings = (float) ($bFeat['booking_count_90d'] ?? 0.0);
            if (abs($aBookings - $bBookings) > 1.0) {
                return $bBookings <=> $aBookings;
            }

            $riskCompare = ((float) ($b['risk_probability'] ?? 0.0)) <=> ((float) ($a['risk_probability'] ?? 0.0));
            if ($riskCompare !== 0) {
                return $riskCompare;
            }

            $aTimestamp = ($a['next_due_at'] ?? null) instanceof DateTimeImmutable ? $a['next_due_at']->getTimestamp() : PHP_INT_MAX;
            $bTimestamp = ($b['next_due_at'] ?? null) instanceof DateTimeImmutable ? $b['next_due_at']->getTimestamp() : PHP_INT_MAX;

            return $aTimestamp <=> $bTimestamp;
        });

        return $upcoming;
    }

    protected function buildForecasts(array $assetIds = []): array
    {
        $datesByAsset = $this->completedDatesByAsset($assetIds);
        $forecasts = [];

        foreach ($datesByAsset as $assetId => $dates) {
            if (empty($dates)) {
                continue;
            }

            usort($dates, static fn(DateTimeImmutable $a, DateTimeImmutable $b): int => $a <=> $b);
            $historyCount = count($dates);
            $lastCompleted = $dates[$historyCount - 1];

            if ($historyCount >= 2) {
                $intervalSeconds = [];
                for ($i = 1; $i < $historyCount; $i++) {
                    $intervalSeconds[] = $dates[$i]->getTimestamp() - $dates[$i - 1]->getTimestamp();
                }
                $avgSeconds = array_sum($intervalSeconds) / max(count($intervalSeconds), 1);
                $intervalDays = max((int) round($avgSeconds / 86400), 1);
                $nextDue = $lastCompleted->add(new DateInterval('P' . $intervalDays . 'D'));
                $basis = 'average';
            } else {
                $intervalDays = 365;
                $nextDue = $lastCompleted->add(new DateInterval('P12M'));
                $basis = 'default';
            }

            $forecasts[(int) $assetId] = [
                'asset_id' => (int) $assetId,
                'history_count' => $historyCount,
                'interval_days' => $intervalDays,
                'basis' => $basis,
                'last_completed_at' => $lastCompleted,
                'next_due_at' => $nextDue,
            ];
        }

        return $forecasts;
    }

    protected function assetsWithOpenMaintenance(array $assetIds): array
    {
        if (empty($assetIds)) {
            return [];
        }

        $rows = $this->db->table('maintenance_records')
            ->select('asset_id')
            ->distinct()
            ->whereIn('asset_id', $assetIds)
            ->whereIn('status', ['reported', 'scheduled', 'in_progress', 'testing'])
            ->get()
            ->getResultArray();

        return array_map(static fn(array $row): int => (int) $row['asset_id'], $rows);
    }

    protected function completedDatesByAsset(array $assetIds = []): array
    {
        $builder = $this->db->table('maintenance_records')
            ->select('asset_id, completed_at')
            ->where('status', 'completed')
            ->whereIn('issue_type', $this->allowedIssueTypes)
            ->where('completed_at IS NOT NULL', null, false);

        if (! empty($assetIds)) {
            $builder->whereIn('asset_id', $assetIds);
        }

        $rows = $builder->orderBy('completed_at', 'ASC')
            ->get()
            ->getResultArray();

        $datesByAsset = [];
        foreach ($rows as $row) {
            $assetId = (int) ($row['asset_id'] ?? 0);
            if ($assetId <= 0 || empty($row['completed_at'])) {
                continue;
            }

            try {
                $datesByAsset[$assetId][] = new DateTimeImmutable($row['completed_at'], $this->timezone);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $datesByAsset;
    }
}
