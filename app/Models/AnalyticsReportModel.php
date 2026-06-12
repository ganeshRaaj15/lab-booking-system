<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class AnalyticsReportModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;

    public function availableLaboratories(array $scope): array
    {
        $builder = $this->db->table('laboratories')
            ->select('id, name, room')
            ->orderBy('name', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'id');

        return $builder->get()->getResultArray();
    }

    public function availableFaculties(array $scope): array
    {
        $builder = $this->db->table('faculties f')
            ->select('DISTINCT f.id, f.name_en', false)
            ->join('bookings b', 'b.faculty_id = f.id', 'inner')
            ->where('f.name_en IS NOT NULL', null, false)
            ->orderBy('f.name_en', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'b.lab_id');

        return $builder->get()->getResultArray();
    }

    public function availableUsers(array $scope): array
    {
        $builder = $this->db->table('users u')
            ->select("DISTINCT u.id, COALESCE(NULLIF(u.full_name, ''), u.username, i.secret) AS label", false)
            ->join('bookings b', 'b.user_id = u.id', 'inner')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->orderBy('label', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'b.lab_id');

        return $builder->get()->getResultArray();
    }

    public function availableAssetCategories(array $scope): array
    {
        $builder = $this->db->table('assets')
            ->select('DISTINCT category', false)
            ->where('category IS NOT NULL', null, false)
            ->where("TRIM(category) !=", '')
            ->orderBy('category', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'lab_id');

        return array_values(array_filter(array_map(
            static fn(array $row): string => trim((string) ($row['category'] ?? '')),
            $builder->get()->getResultArray()
        )));
    }

    public function availableAssets(array $scope, ?int $labId = null): array
    {
        $builder = $this->db->table('assets a')
            ->select('a.id, a.name, a.asset_code, l.name AS laboratory_name', false)
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->orderBy('l.name', 'ASC')
            ->orderBy('a.name', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'a.lab_id');

        if ($labId !== null && $labId > 0) {
            $builder->where('a.lab_id', $labId);
        }

        return $builder->get()->getResultArray();
    }

    public function bookingStatusSummary(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select('UPPER(b.status) AS normalized_status, COUNT(*) AS total', false)
            ->groupBy('UPPER(b.status)');

        $this->applyBookingFilters($builder, $filters, $scope);

        $summary = array_fill_keys(BookingModel::CORE_STATUSES, 0);
        foreach ($builder->get()->getResultArray() as $row) {
            $status = strtoupper((string) ($row['normalized_status'] ?? ''));
            if (array_key_exists($status, $summary)) {
                $summary[$status] = (int) ($row['total'] ?? 0);
            }
        }

        return $summary;
    }

    public function bookingTrend(array $filters, array $scope, string $granularity = 'week'): array
    {
        $granularity = in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'week';
        $builder = $this->db->table('bookings b');

        if ($granularity === 'day') {
            $builder->select("DATE_FORMAT(b.date, '%Y-%m-%d') AS label, DATE_FORMAT(b.date, '%b %d') AS display_label, COUNT(*) AS total", false)
                ->groupBy("DATE_FORMAT(b.date, '%Y-%m-%d')")
                ->orderBy('label', 'ASC');
        } elseif ($granularity === 'month') {
            $builder->select("DATE_FORMAT(b.date, '%Y-%m') AS label, DATE_FORMAT(b.date, '%b %Y') AS display_label, COUNT(*) AS total", false)
                ->groupBy("DATE_FORMAT(b.date, '%Y-%m')")
                ->orderBy('label', 'ASC');
        } else {
            $builder->select("
                YEARWEEK(b.date, 1) AS sort_key,
                CONCAT(YEAR(b.date), '-W', LPAD(WEEK(b.date, 1), 2, '0')) AS label,
                CONCAT('Week ', LPAD(WEEK(b.date, 1), 2, '0')) AS display_label,
                COUNT(*) AS total
            ", false)
                ->groupBy('YEARWEEK(b.date, 1)')
                ->orderBy('sort_key', 'ASC');
        }

        $this->applyBookingFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    public function bookingReportRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select("
                b.id,
                l.name AS laboratory_name,
                l.room AS laboratory_room,
                COALESCE(NULLIF(u.full_name, ''), u.username, i.secret, 'External Request') AS requested_by,
                i.secret AS requester_email,
                f.name_en AS faculty_name,
                b.user_type,
                b.date,
                b.start_time,
                b.end_time,
                b.activity,
                UPPER(b.status) AS status,
                b.approved_by_pic,
                b.approved_by_manager,
                b.approval_flow,
                b.created_at
            ", false)
            ->join('laboratories l', 'l.id = b.lab_id', 'left')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('auth_identities i', "i.user_id = u.id AND i.type = 'email_password'", 'left')
            ->join('faculties f', 'f.id = b.faculty_id', 'left')
            ->orderBy('b.date', 'DESC')
            ->orderBy('b.start_time', 'DESC')
            ->orderBy('b.id', 'DESC');

        $this->applyBookingFilters($builder, $filters, $scope);

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['approval_status'] = $this->approvalStatusLabel($row);
        }

        return $rows;
    }

    public function laboratoryUsageRows(array $filters, array $scope): array
    {
        $labs = $this->availableLaboratoriesForFilters($filters, $scope);
        $stats = [];
        $peaks = [];

        if ($labs !== []) {
            $aggregateBuilder = $this->db->table('bookings b')
                ->select("
                    b.lab_id,
                    SUM(CASE WHEN UPPER(b.status) = 'APPROVED' THEN 1 ELSE 0 END) AS total_bookings,
                    SUM(CASE WHEN UPPER(b.status) = 'APPROVED' THEN TIMESTAMPDIFF(MINUTE, b.start_time, b.end_time) ELSE 0 END) AS used_minutes,
                    SUM(CASE WHEN UPPER(b.status) IN ('CANCELLED', 'REJECTED') THEN 1 ELSE 0 END) AS cancelled_rejected
                ", false)
                ->groupBy('b.lab_id');

            $this->applyBookingFilters($aggregateBuilder, $filters, $scope);
            foreach ($aggregateBuilder->get()->getResultArray() as $row) {
                $stats[(int) $row['lab_id']] = $row;
            }

            $peakBuilder = $this->db->table('bookings b')
                ->select("
                    b.lab_id,
                    DAYNAME(b.date) AS peak_day,
                    CASE
                        WHEN b.start_time >= '08:00:00' AND b.start_time < '10:00:00' THEN '08:00-10:00'
                        WHEN b.start_time >= '10:00:00' AND b.start_time < '12:00:00' THEN '10:00-12:00'
                        WHEN b.start_time >= '13:00:00' AND b.start_time < '15:00:00' THEN '13:00-15:00'
                        WHEN b.start_time >= '15:00:00' AND b.start_time < '17:00:00' THEN '15:00-17:00'
                        ELSE 'Other'
                    END AS peak_slot,
                    COUNT(*) AS total
                ", false)
                ->where("UPPER(b.status) = " . $this->db->escape('APPROVED'), null, false)
                ->groupBy('b.lab_id, DAYNAME(b.date), peak_slot')
                ->orderBy('total', 'DESC');

            $this->applyBookingFilters($peakBuilder, $filters, $scope, ['skip_status' => true]);

            foreach ($peakBuilder->get()->getResultArray() as $row) {
                $labId = (int) $row['lab_id'];
                if (! isset($peaks[$labId])) {
                    $peaks[$labId] = [
                        'peak_day' => (string) ($row['peak_day'] ?? 'N/A'),
                        'peak_slot' => (string) ($row['peak_slot'] ?? 'N/A'),
                    ];
                }
            }
        }

        $window = $this->resolveUsageWindow($filters, $scope);
        $availableHoursPerLab = max((float) ($window['available_hours_per_lab'] ?? 0), 8.0);

        $rows = [];
        foreach ($labs as $lab) {
            $labId = (int) $lab['id'];
            $labStats = $stats[$labId] ?? [];
            $usedHours = round(((int) ($labStats['used_minutes'] ?? 0)) / 60, 1);
            $rows[] = [
                'laboratory_id' => $labId,
                'laboratory_name' => (string) ($lab['name'] ?? 'Unknown Lab'),
                'laboratory_room' => (string) ($lab['room'] ?? ''),
                'total_bookings' => (int) ($labStats['total_bookings'] ?? 0),
                'total_used_hours' => $usedHours,
                'usage_percentage' => $availableHoursPerLab > 0
                    ? round(min(($usedHours / $availableHoursPerLab) * 100, 100), 1)
                    : 0.0,
                'peak_usage_day' => $peaks[$labId]['peak_day'] ?? 'N/A',
                'peak_usage_time' => $peaks[$labId]['peak_slot'] ?? 'N/A',
                'cancelled_rejected_count' => (int) ($labStats['cancelled_rejected'] ?? 0),
            ];
        }

        return $rows;
    }

    public function assetStatusSummary(array $filters, array $scope): array
    {
        $builder = $this->db->table('assets a')
            ->select('LOWER(a.status) AS normalized_status, COUNT(*) AS total', false)
            ->groupBy('LOWER(a.status)');

        $this->applyAssetFilters($builder, $filters, $scope);

        $summary = array_fill_keys(['available', 'maintenance', 'faulty'], 0);
        foreach ($builder->get()->getResultArray() as $row) {
            $status = strtolower((string) ($row['normalized_status'] ?? ''));
            if (array_key_exists($status, $summary)) {
                $summary[$status] = (int) ($row['total'] ?? 0);
            }
        }

        return $summary;
    }

    public function assetReportRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('assets a')
            ->select("
                a.id,
                a.name,
                a.asset_code,
                a.category,
                a.status,
                a.quantity,
                a.total_quantity,
                a.location_note,
                l.name AS laboratory_name,
                l.room AS laboratory_room,
                MAX(mr.completed_at) AS last_maintenance_date
            ", false)
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->join('maintenance_records mr', 'mr.asset_id = a.id', 'left')
            ->groupBy('a.id, a.name, a.asset_code, a.category, a.status, a.quantity, a.total_quantity, a.location_note, l.name, l.room')
            ->orderBy('l.name', 'ASC')
            ->orderBy('a.name', 'ASC');

        $this->applyAssetFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    public function mostUsedAssets(array $filters, array $scope, int $limit = 5): array
    {
        $builder = $this->db->table('booking_assets ba')
            ->select('a.name, SUM(ba.quantity_used) AS total_used', false)
            ->join('assets a', 'a.id = ba.asset_id', 'inner')
            ->join('bookings b', 'b.id = ba.booking_id', 'inner')
            ->where("UPPER(b.status) = " . $this->db->escape('APPROVED'), null, false)
            ->groupBy('a.id, a.name')
            ->orderBy('total_used', 'DESC')
            ->limit($limit);

        $this->applyScopeToBuilder($builder, $scope, 'b.lab_id');

        if (! empty($filters['lab_id'])) {
            $builder->where('b.lab_id', (int) $filters['lab_id']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('b.date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('b.date <=', $filters['date_to']);
        }
        if (! empty($filters['faculty_id'])) {
            $builder->where('b.faculty_id', (int) $filters['faculty_id']);
        }
        if (! empty($filters['user_id'])) {
            $builder->where('b.user_id', (int) $filters['user_id']);
        }
        if (array_key_exists('role_user_ids', $filters)) {
            $this->applyUserRoleFilter($builder, 'b.user_id', $filters['role_user_ids']);
        }
        if (! empty($filters['asset_category'])) {
            $builder->where('a.category', $filters['asset_category']);
        }
        if (! empty($filters['asset_status'])) {
            $builder->where("LOWER(a.status) = " . $this->db->escape(strtolower((string) $filters['asset_status'])), null, false);
        }

        return $builder->get()->getResultArray();
    }

    public function maintenanceStatusSummary(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('LOWER(mr.status) AS normalized_status, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->groupBy('LOWER(mr.status)');

        $this->applyMaintenanceFilters($builder, $filters, $scope);

        $summary = array_fill_keys(['reported', 'scheduled', 'in_progress', 'testing', 'completed', 'cancelled'], 0);
        foreach ($builder->get()->getResultArray() as $row) {
            $status = strtolower((string) ($row['normalized_status'] ?? ''));
            if (array_key_exists($status, $summary)) {
                $summary[$status] = (int) ($row['total'] ?? 0);
            }
        }

        return $summary;
    }

    public function maintenanceTrend(array $filters, array $scope, string $granularity = 'month'): array
    {
        $granularity = in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'month';
        $builder = $this->db->table('maintenance_records mr')
            ->join('assets a', 'a.id = mr.asset_id', 'left');

        if ($granularity === 'day') {
            $builder->select("DATE_FORMAT(mr.created_at, '%Y-%m-%d') AS label, DATE_FORMAT(mr.created_at, '%b %d') AS display_label, COUNT(*) AS total", false)
                ->groupBy("DATE_FORMAT(mr.created_at, '%Y-%m-%d')")
                ->orderBy('label', 'ASC');
        } elseif ($granularity === 'week') {
            $builder->select("
                YEARWEEK(mr.created_at, 1) AS sort_key,
                CONCAT(YEAR(mr.created_at), '-W', LPAD(WEEK(mr.created_at, 1), 2, '0')) AS label,
                CONCAT('Week ', LPAD(WEEK(mr.created_at, 1), 2, '0')) AS display_label,
                COUNT(*) AS total
            ", false)
                ->groupBy('YEARWEEK(mr.created_at, 1)')
                ->orderBy('sort_key', 'ASC');
        } else {
            $builder->select("DATE_FORMAT(mr.created_at, '%Y-%m') AS label, DATE_FORMAT(mr.created_at, '%b %Y') AS display_label, COUNT(*) AS total", false)
                ->groupBy("DATE_FORMAT(mr.created_at, '%Y-%m')")
                ->orderBy('label', 'ASC');
        }

        $this->applyMaintenanceFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    public function maintenanceReportRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("
                mr.id,
                mr.title,
                mr.issue_type,
                mr.priority,
                mr.status,
                mr.created_at,
                mr.completed_at,
                a.name AS asset_name,
                a.asset_code,
                l.name AS laboratory_name,
                l.room AS laboratory_room,
                l.pic_name,
                COALESCE(NULLIF(reporter.full_name, ''), reporter.username, reporter_identity.secret, 'System') AS reported_by_name,
                COALESCE(NULLIF(technician.full_name, ''), technician.username, technician_identity.secret) AS technician_name
            ", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->join('users reporter', 'reporter.id = mr.reported_by', 'left')
            ->join('auth_identities reporter_identity', "reporter_identity.user_id = reporter.id AND reporter_identity.type = 'email_password'", 'left')
            ->join('users technician', 'technician.id = mr.assigned_technician_id', 'left')
            ->join('auth_identities technician_identity', "technician_identity.user_id = technician.id AND technician_identity.type = 'email_password'", 'left')
            ->orderBy('mr.created_at', 'DESC')
            ->orderBy('mr.id', 'DESC');

        $this->applyMaintenanceFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    public function frequentMaintenanceAssets(array $filters, array $scope, int $limit = 5): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('a.name AS asset_name, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->groupBy('a.id, a.name')
            ->orderBy('total', 'DESC')
            ->limit($limit);

        $this->applyMaintenanceFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    public function recentMaintenanceActivities(array $filters, array $scope, int $limit = 8): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("
                mr.id,
                mr.title,
                mr.status,
                mr.priority,
                mr.created_at,
                a.name AS asset_name,
                l.name AS laboratory_name,
                COALESCE(NULLIF(technician.full_name, ''), technician.username, 'Unassigned') AS technician_name
            ", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->join('users technician', 'technician.id = mr.assigned_technician_id', 'left')
            ->orderBy('mr.created_at', 'DESC')
            ->limit($limit);

        $this->applyMaintenanceFilters($builder, $filters, $scope);

        return $builder->get()->getResultArray();
    }

    private function applyScopeToBuilder(BaseBuilder $builder, array $scope, string $column): void
    {
        if (($scope['role'] ?? '') !== 'pic') {
            return;
        }

        $labIds = array_values(array_unique(array_map('intval', $scope['labIds'] ?? [])));
        if ($labIds === []) {
            $builder->where('1 = 0', null, false);
            return;
        }

        $builder->whereIn($column, $labIds);
    }

    private function applyBookingFilters(BaseBuilder $builder, array $filters, array $scope, array $options = []): void
    {
        $skipStatus = (bool) ($options['skip_status'] ?? false);

        $this->applyScopeToBuilder($builder, $scope, 'b.lab_id');

        if (! empty($filters['date_from'])) {
            $builder->where('b.date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('b.date <=', $filters['date_to']);
        }
        if (! empty($filters['lab_id'])) {
            $builder->where('b.lab_id', (int) $filters['lab_id']);
        }
        if (! empty($filters['asset_id'])) {
            $builder->join('booking_assets baf', 'baf.booking_id = b.id', 'inner');
            $builder->where('baf.asset_id', (int) $filters['asset_id']);
        }
        if (! empty($filters['faculty_id'])) {
            $builder->where('b.faculty_id', (int) $filters['faculty_id']);
        }
        if (! empty($filters['user_id'])) {
            $builder->where('b.user_id', (int) $filters['user_id']);
        }
        if (! $skipStatus && ! empty($filters['booking_status'])) {
            $builder->where("UPPER(b.status) = " . $this->db->escape(strtoupper((string) $filters['booking_status'])), null, false);
        }
        if (array_key_exists('role_user_ids', $filters)) {
            $this->applyUserRoleFilter($builder, 'b.user_id', $filters['role_user_ids']);
        }
    }

    private function applyAssetFilters(BaseBuilder $builder, array $filters, array $scope): void
    {
        $this->applyScopeToBuilder($builder, $scope, 'a.lab_id');

        if (! empty($filters['lab_id'])) {
            $builder->where('a.lab_id', (int) $filters['lab_id']);
        }
        if (! empty($filters['asset_id'])) {
            $builder->where('a.id', (int) $filters['asset_id']);
        }
        if (! empty($filters['asset_category'])) {
            $builder->where('a.category', $filters['asset_category']);
        }
        if (! empty($filters['asset_status'])) {
            $builder->where("LOWER(a.status) = " . $this->db->escape(strtolower((string) $filters['asset_status'])), null, false);
        }
        if (! empty($filters['maintenance_status'])) {
            $builder->where("LOWER(mr.status) = " . $this->db->escape(strtolower((string) $filters['maintenance_status'])), null, false);
        }
        if (! empty($filters['date_from'])) {
            $builder->groupStart()
                ->where('mr.created_at >=', $filters['date_from'] . ' 00:00:00')
                ->orWhere('mr.created_at IS NULL', null, false)
                ->groupEnd();
        }
        if (! empty($filters['date_to'])) {
            $builder->groupStart()
                ->where('mr.created_at <=', $filters['date_to'] . ' 23:59:59')
                ->orWhere('mr.created_at IS NULL', null, false)
                ->groupEnd();
        }
    }

    private function applyMaintenanceFilters(BaseBuilder $builder, array $filters, array $scope): void
    {
        $this->applyScopeToBuilder($builder, $scope, 'a.lab_id');

        if (! empty($filters['date_from'])) {
            $builder->where('mr.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $builder->where('mr.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        if (! empty($filters['lab_id'])) {
            $builder->where('a.lab_id', (int) $filters['lab_id']);
        }
        if (! empty($filters['asset_id'])) {
            $builder->where('a.id', (int) $filters['asset_id']);
        }
        if (! empty($filters['asset_category'])) {
            $builder->where('a.category', $filters['asset_category']);
        }
        if (! empty($filters['asset_status'])) {
            $builder->where("LOWER(a.status) = " . $this->db->escape(strtolower((string) $filters['asset_status'])), null, false);
        }
        if (! empty($filters['maintenance_status'])) {
            $builder->where("LOWER(mr.status) = " . $this->db->escape(strtolower((string) $filters['maintenance_status'])), null, false);
        }
    }

    private function applyUserRoleFilter(BaseBuilder $builder, string $column, array $userIds): void
    {
        if ($userIds === []) {
            $builder->where('1 = 0', null, false);
            return;
        }

        $builder->whereIn($column, array_values(array_unique(array_map('intval', $userIds))));
    }

    private function availableLaboratoriesForFilters(array $filters, array $scope): array
    {
        $builder = $this->db->table('laboratories')
            ->select('id, name, room')
            ->orderBy('name', 'ASC');

        $this->applyScopeToBuilder($builder, $scope, 'id');
        if (! empty($filters['lab_id'])) {
            $builder->where('id', (int) $filters['lab_id']);
        }

        return $builder->get()->getResultArray();
    }

    private function resolveUsageWindow(array $filters, array $scope): array
    {
        $dateFrom = (string) ($filters['date_from'] ?? '');
        $dateTo = (string) ($filters['date_to'] ?? '');

        if ($dateFrom === '' || $dateTo === '') {
            $rangeBuilder = $this->db->table('bookings b')
                ->select('MIN(b.date) AS min_date, MAX(b.date) AS max_date', false)
                ->where("UPPER(b.status) = " . $this->db->escape('APPROVED'), null, false);

            $this->applyBookingFilters($rangeBuilder, $filters, $scope, ['skip_status' => true]);
            $row = $rangeBuilder->get()->getRowArray();
            $dateFrom = $dateFrom !== '' ? $dateFrom : (string) ($row['min_date'] ?? '');
            $dateTo = $dateTo !== '' ? $dateTo : (string) ($row['max_date'] ?? '');
        }

        if ($dateFrom === '' || $dateTo === '') {
            $today = date('Y-m-d');
            $dateFrom = $today;
            $dateTo = $today;
        }

        $workingDays = $this->countWorkingDays($dateFrom, $dateTo);
        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'working_days' => $workingDays,
            'available_hours_per_lab' => max($workingDays * 8, 8),
        ];
    }

    private function countWorkingDays(string $dateFrom, string $dateTo): int
    {
        $start = new DateTimeImmutable($dateFrom);
        $end = new DateTimeImmutable($dateTo);

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $period = new DatePeriod($start, new DateInterval('P1D'), $end->add(new DateInterval('P1D')));
        $days = 0;
        foreach ($period as $date) {
            $dayOfWeek = (int) $date->format('N');
            if ($dayOfWeek <= 5) {
                $days++;
            }
        }

        return max($days, 1);
    }

    private function approvalStatusLabel(array $row): string
    {
        $status = strtoupper((string) ($row['status'] ?? ''));
        if ($status === 'APPROVED') {
            return 'Approved';
        }
        if ($status === 'REJECTED') {
            return 'Rejected';
        }
        if ($status === 'CANCELLED') {
            return 'Cancelled';
        }
        if ((int) ($row['approved_by_pic'] ?? 0) === 0) {
            return 'Awaiting PIC';
        }
        if ((int) ($row['approved_by_manager'] ?? 0) === 0 && strtoupper((string) ($row['approval_flow'] ?? '')) !== 'FKMP_APPROVAL') {
            return 'Awaiting Manager';
        }

        return 'Pending';
    }
}
