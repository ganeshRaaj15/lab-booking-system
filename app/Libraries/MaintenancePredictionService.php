<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Database;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class MaintenancePredictionService
{
    protected \CodeIgniter\Database\BaseConnection $db;
    protected DateTimeZone $timezone;
    protected MaintenanceFeatureExtractor $featureExtractor;
    protected MaintenanceModelTrainer $trainer;
    protected string $modelPath;

    public function __construct(
        ?\CodeIgniter\Database\BaseConnection $db = null,
        ?DateTimeZone $timezone = null,
        ?MaintenanceFeatureExtractor $featureExtractor = null,
        ?MaintenanceModelTrainer $trainer = null,
        ?string $modelPath = null
    ) {
        $this->db = $db ?? Database::connect();
        $this->timezone = $timezone ?? new DateTimeZone('Asia/Kuala_Lumpur');
        $this->featureExtractor = $featureExtractor ?? new MaintenanceFeatureExtractor($this->db, $this->timezone);
        $this->trainer = $trainer ?? new MaintenanceModelTrainer();
        $this->modelPath = $modelPath ?? WRITEPATH . 'models/maintenance_predictor.json';
    }

    public function modelPath(): string
    {
        return $this->modelPath;
    }

    public function modelExists(): bool
    {
        return is_file($this->modelPath);
    }

    public function loadModel(): ?array
    {
        if (! $this->modelExists()) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($this->modelPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    public function saveModel(array $model): void
    {
        $directory = dirname($this->modelPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->modelPath, json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function removeModel(): void
    {
        if ($this->modelExists()) {
            unlink($this->modelPath);
        }
    }

    public function trainAndPersist(int $horizonDays = 60, int $stepDays = 14, int $lookbackDays = 540): array
    {
        $samples = $this->featureExtractor->buildTrainingSamples($horizonDays, $stepDays, $lookbackDays);
        $recordsUsed = $this->db->table('maintenance_records')->countAllResults();
        $assetsUsed = $this->db->table('assets')->countAllResults();

        if (count($samples) < $this->trainer->minimumSamples()) {
            $this->removeModel();

            return [
                'available' => false,
                'mode' => 'rule_based_only',
                'notice' => 'Insufficient training samples after migration; predictive scoring will use rule-based fallback only.',
                'dataset' => [
                    'samples_total' => count($samples),
                    'samples_train' => 0,
                    'samples_test' => 0,
                    'positive_train' => 0,
                    'positive_test' => 0,
                ],
                'training' => [
                    'horizon_days' => $horizonDays,
                    'step_days' => $stepDays,
                    'lookback_days' => $lookbackDays,
                    'records_used' => $recordsUsed,
                    'assets_used' => $assetsUsed,
                    'minimum_samples_required' => $this->trainer->minimumSamples(),
                ],
            ];
        }

        $model = $this->trainer->train($samples);
        $model['training'] = [
            'horizon_days' => $horizonDays,
            'step_days' => $stepDays,
            'lookback_days' => $lookbackDays,
            'records_used' => $recordsUsed,
            'assets_used' => $assetsUsed,
            'minimum_samples_required' => $this->trainer->minimumSamples(),
        ];
        $this->saveModel($model);

        return $model;
    }

    public function getModelSummary(): array
    {
        $model = $this->loadModel();
        if (! $model) {
            return [
                'available' => false,
                'mode' => 'rule_based_only',
                'notice' => 'No trained maintenance model is available. Rule-based predictive scoring remains active.',
                'path' => $this->modelPath,
            ];
        }

        return [
            'available' => true,
            'mode' => 'model_plus_rules',
            'path' => $this->modelPath,
            'trained_at' => $model['trained_at'] ?? null,
            'threshold' => (float) ($model['threshold'] ?? 0.5),
            'threshold_policy' => $model['threshold_policy'] ?? [],
            'metrics' => $model['metrics']['test'] ?? [],
            'dataset' => $model['dataset'] ?? [],
            'training' => $model['training'] ?? [],
        ];
    }

    public function predictAsset(int $assetId, ?array $model = null): ?array
    {
        $model = $model ?? $this->loadModel();

        $asset = $this->db->table('assets a')
            ->select('a.id, a.name, a.asset_code, a.status, a.total_quantity, a.quantity, l.name AS lab_name, l.room AS lab_room')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->where('a.id', $assetId)
            ->get()
            ->getRowArray();

        if (! $asset) {
            return null;
        }

        $features = $this->featureExtractor->extractCurrentAssetFeatures($assetId);
        if (! $features) {
            return null;
        }

        $ruleFloor = $this->ruleBasedFloor($features);
        $modelAvailable = is_array($model) && ($model['feature_names'] ?? []) !== [];
        $modelProbabilityRaw = $modelAvailable
            ? $this->trainer->predictRawProbability($features, $model)
            : null;
        $modelProbability = $modelAvailable
            ? $this->trainer->predictProbability($features, $model)
            : null;
        $probability = $modelAvailable
            ? max((float) $modelProbability, $ruleFloor)
            : $ruleFloor;
        $probability = $this->dampByHistoryConfidence($probability, $features);
        $threshold = $modelAvailable ? $this->trainer->resolveThreshold($features, $model) : 0.60;
        $thresholdSegment = $modelAvailable ? $this->trainer->thresholdSegment($features, $model) : 'rule_based_only';

        $decision = $this->decision($probability, $features, $threshold);

        return array_merge($asset, [
            'risk_probability' => $probability,
            'model_probability_raw' => $modelProbabilityRaw,
            'model_probability_calibrated' => $modelProbability,
            'model_available' => $modelAvailable,
            'risk_percent' => (int) round($probability * 100),
            'risk_band' => $this->riskBand($probability, $threshold),
            'decision' => $decision,
            'reasons' => $this->reasons($features, $probability, $threshold, $decision),
            'features' => $features,
            'threshold' => $threshold,
            'threshold_segment' => $thresholdSegment,
        ]);
    }

    public function predictAllAssets(?array $model = null): array
    {
        $model = $model ?? $this->loadModel();

        $assets = $this->db->table('assets')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $predictions = [];
        foreach ($assets as $asset) {
            $prediction = $this->predictAsset((int) ($asset['id'] ?? 0), $model);
            if ($prediction) {
                $predictions[] = $prediction;
            }
        }

        usort($predictions, static function (array $a, array $b): int {
            $riskSort = ($b['risk_probability'] <=> $a['risk_probability']);
            if ($riskSort !== 0) {
                return $riskSort;
            }

            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return $predictions;
    }

    public function decision(float $probability, array $features, float $threshold): array
    {
        $plannedGapDelta  = (float) ($features['planned_gap_delta'] ?? 0.0);
        $hasCompletedPlannedHistory = (float) ($features['has_completed_planned_history'] ?? 0.0) >= 1.0;
        $correctiveRecent = (float) ($features['corrective_last_120d'] ?? 0.0);
        $highPriority     = (float) ($features['high_priority_last_180d'] ?? 0.0);
        $bookingCount90   = (float) ($features['booking_count_90d'] ?? 0.0);
        $scheduleThreshold = $this->scheduleThreshold($threshold);

        // schedule_now: requires strong combined evidence, not just one flag
        $strongCorrectiveCombination    = $correctiveRecent >= 2.0 && $highPriority >= 1.0;
        $badlyOverdueWithUsageEvidence  = $hasCompletedPlannedHistory
            && $plannedGapDelta >= 45.0
            && ($correctiveRecent >= 1.0 || $bookingCount90 >= 10.0);

        if ($probability >= $scheduleThreshold || $strongCorrectiveCombination || $badlyOverdueWithUsageEvidence) {
            return [
                'action' => 'schedule_now',
                'label' => 'Schedule preventive maintenance now',
                'priority' => 'high',
            ];
        }

        // inspect_soon: moderate evidence or approaching/exceeded average interval
        $moderateEvidence = $correctiveRecent >= 1.0 || $highPriority >= 1.0;
        $overduePlannedInterval = $hasCompletedPlannedHistory && $plannedGapDelta >= 30.0;
        $moderatelyOverdueWithEvidence = $hasCompletedPlannedHistory && $plannedGapDelta >= 14.0 && $moderateEvidence;

        if ($probability >= $threshold || $overduePlannedInterval || $moderatelyOverdueWithEvidence) {
            return [
                'action' => 'inspect_soon',
                'label' => 'Inspect within 14 days',
                'priority' => 'medium',
            ];
        }

        return [
            'action' => 'monitor',
            'label' => 'Normal monitoring',
            'priority' => 'low',
        ];
    }

    protected function riskBand(float $probability, float $threshold): string
    {
        if ($probability >= $this->scheduleThreshold($threshold)) {
            return 'high';
        }
        if ($probability >= $threshold) {
            return 'medium';
        }

        return 'low';
    }

    protected function scheduleThreshold(float $threshold): float
    {
        return min(0.98, max($threshold + 0.12, $threshold * 1.22));
    }

    protected function reasons(array $features, float $probability, float $threshold, array $decision): array
    {
        $reasons = [];
        $hasCompletedPlannedHistory = (float) ($features['has_completed_planned_history'] ?? 0.0) >= 1.0;
        $hasCorrectivePressure = false;

        if ((float) ($features['corrective_last_120d'] ?? 0.0) >= 2.0) {
            $reasons[] = 'Multiple corrective cases were recorded in the last 120 days.';
            $hasCorrectivePressure = true;
        }

        if ((float) ($features['high_priority_last_180d'] ?? 0.0) >= 1.0) {
            $reasons[] = 'At least one high-priority maintenance case was recorded recently.';
            $hasCorrectivePressure = true;
        }

        if ($hasCompletedPlannedHistory && (float) ($features['planned_gap_delta'] ?? 0.0) >= 30.0) {
            $reasons[] = 'The planned maintenance interval has been exceeded.';
        }

        if ($hasCorrectivePressure && (float) ($features['events_last_30d'] ?? 0.0) >= 1.0) {
            $reasons[] = 'The asset had maintenance activity in the last 30 days.';
        }

        if ((float) ($features['corrective_ratio_365d'] ?? 0.0) >= 0.45) {
            $reasons[] = 'A large share of the last year\'s maintenance history was corrective rather than preventive.';
        }

        $bookingCount90 = (float) ($features['booking_count_90d'] ?? 0.0);
        $bookingHours90 = (float) ($features['booking_hours_90d'] ?? 0.0);

        if ($bookingCount90 >= 20.0) {
            $reasons[] = 'This asset has been booked heavily (' . (int) $bookingCount90 . ' sessions in the last 90 days), increasing wear exposure.';
        } elseif ($bookingHours90 >= 80.0) {
            $reasons[] = 'High cumulative usage hours (' . (int) $bookingHours90 . ' h in 90 days) detected from booking records.';
        }

        if ($reasons === [] && ($decision['action'] ?? 'monitor') === 'schedule_now' && $probability >= $this->scheduleThreshold($threshold)) {
            $reasons[] = 'The learned risk score exceeded the scheduling threshold based on the asset\'s maintenance pattern.';
        } elseif ($reasons === [] && ($decision['action'] ?? 'monitor') === 'inspect_soon' && $probability >= $threshold) {
            $reasons[] = 'The learned risk score exceeded the inspection threshold based on the asset\'s maintenance pattern.';
        }

        if ($reasons === []) {
            if ((float) ($features['days_since_last_planned'] ?? 999.0) <= 30.0) {
                $reasons[] = 'Planned maintenance was completed recently, so no immediate action is needed.';
            } elseif ($bookingCount90 > 0.0) {
                $reasons[] = 'Maintenance history is stable; booking usage is within normal range.';
            } else {
                $reasons[] = 'Recent maintenance history is stable and within the expected planned interval.';
            }
        }

        return array_slice($reasons, 0, 3);
    }

    protected function dampByHistoryConfidence(float $probability, array $features): float
    {
        $eventsLast365  = (float) ($features['events_last_365d'] ?? $features['events_last_90d'] ?? 0.0);
        $plannedLifetime = (float) ($features['planned_events_lifetime'] ?? 0.0);
        $bookingCount90  = (float) ($features['booking_count_90d'] ?? 0.0);
        $depthScore      = (float) ($features['history_depth_score'] ?? 0.0);

        // Very sparse: almost no evidence — cap aggressive scores from rule-based floors.
        if ($eventsLast365 <= 1.0 && $plannedLifetime <= 1.0 && $bookingCount90 <= 5.0) {
            return $probability * 0.70;
        }

        // Thin history: limited basis for a confident prediction.
        if ($depthScore <= 0.25 && $eventsLast365 <= 3.0) {
            return $probability * 0.82;
        }

        return $probability;
    }

    protected function ruleBasedFloor(array $features): float
    {
        // Floors are intentionally modest — sparsity damping handles further reduction.
        if ((float) ($features['corrective_last_120d'] ?? 0.0) >= 2.0) {
            return 0.68;
        }

        if ((float) ($features['high_priority_last_180d'] ?? 0.0) >= 1.0 && (float) ($features['events_last_30d'] ?? 0.0) >= 1.0) {
            return 0.62;
        }

        if ((float) ($features['planned_gap_delta'] ?? 0.0) >= 45.0) {
            return 0.58;
        }

        if ((float) ($features['planned_gap_delta'] ?? 0.0) >= 14.0) {
            return 0.42;
        }

        if ((float) ($features['corrective_ratio_365d'] ?? 0.0) >= 0.45 && (float) ($features['events_last_30d'] ?? 0.0) >= 1.0) {
            return 0.48;
        }

        // Booking pressure alone is not sufficient for high risk; keep floors modest.
        if ((float) ($features['booking_count_90d'] ?? 0.0) >= 20.0 && (float) ($features['planned_last_180d'] ?? 0.0) === 0.0) {
            return 0.44;
        }

        if ((float) ($features['booking_hours_90d'] ?? 0.0) >= 100.0 && (float) ($features['planned_last_180d'] ?? 0.0) === 0.0) {
            return 0.40;
        }

        return 0.0;
    }
}
