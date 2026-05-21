<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\AnalyticsReportModel;

class AnalyticsController extends BaseController
{
    public function index(): string
    {
        helper('auth');

        $user = auth()->user();
        $role = 'user';
        if ($user->inGroup('admin')) {
            $role = 'admin';
        } elseif ($user->inGroup('manager')) {
            $role = 'manager';
        } elseif ($user->inGroup('pic')) {
            $role = 'pic';
        }

        $db    = \Config\Database::connect();
        $email = strtolower(trim((string) ($db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->get()
            ->getRow('secret') ?? '')));

        $labIds = [];
        if ($role === 'pic') {
            $rows   = $db->table('laboratories')
                ->select('id')
                ->where('LOWER(TRIM(pic_email)) =', $email)
                ->get()
                ->getResultArray();
            $labIds = array_map(static fn(array $r): int => (int) $r['id'], $rows);
        }

        $scope = ['role' => $role, 'labIds' => $labIds];

        $filters = [
            'date_from' => $this->request->getGet('date_from') ?? '',
            'date_to'   => $this->request->getGet('date_to') ?? '',
            'lab_id'    => $this->request->getGet('lab_id') ?? '',
        ];

        $model = new AnalyticsReportModel();

        // --- Summary data ---
        $statusSummary = $model->bookingStatusSummary($filters, $scope);
        $totalBookings = array_sum($statusSummary);
        $labRows       = $model->laboratoryUsageRows($filters, $scope);
        $mostUsedLabs  = array_slice($labRows, 0, 5);
        $leastUsedLabs = array_slice(array_reverse($labRows), 0, 5);

        $maintenanceSummary        = $model->maintenanceStatusSummary($filters, $scope);
        $frequentMaintenanceAssets = $model->frequentMaintenanceAssets($filters, $scope);
        $recentMaintenance         = $model->recentMaintenanceActivities($filters, $scope);

        // --- Chart data ---
        $bookingTrend      = $model->bookingTrend($filters, $scope, 'month');
        $maintenanceTrend  = $model->maintenanceTrend($filters, $scope, 'month');

        // Peak hours: aggregate approved bookings by time slot
        $peakBuilder = $db->table('bookings b')
            ->select("
                CASE
                    WHEN b.start_time >= '08:00:00' AND b.start_time < '10:00:00' THEN '08:00-10:00'
                    WHEN b.start_time >= '10:00:00' AND b.start_time < '12:00:00' THEN '10:00-12:00'
                    WHEN b.start_time >= '13:00:00' AND b.start_time < '15:00:00' THEN '13:00-15:00'
                    WHEN b.start_time >= '15:00:00' AND b.start_time < '17:00:00' THEN '15:00-17:00'
                    ELSE 'Other'
                END AS time_slot,
                COUNT(*) AS total
            ", false)
            ->where("UPPER(b.status) = " . $db->escape('APPROVED'), null, false)
            ->groupBy('time_slot')
            ->orderBy('total', 'DESC');
        if ($role === 'pic') {
            if ($labIds === []) {
                $peakBuilder->where('1 = 0', null, false);
            } else {
                $peakBuilder->whereIn('b.lab_id', $labIds);
            }
        }
        if (! empty($filters['date_from'])) {
            $peakBuilder->where('b.date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $peakBuilder->where('b.date <=', $filters['date_to']);
        }
        if (! empty($filters['lab_id'])) {
            $peakBuilder->where('b.lab_id', (int) $filters['lab_id']);
        }
        $peakHours = $peakBuilder->get()->getResultArray();

        // --- Build Chart.js chart configs ---
        $charts = [];

        if (! empty($bookingTrend)) {
            $charts[] = [
                'id'     => 'chartBookingTrend',
                'title'  => 'Booking Trend (Monthly)',
                'type'   => 'bar',
                'height' => 280,
                'data'   => [
                    'labels'   => array_column($bookingTrend, 'display_label'),
                    'datasets' => [[
                        'label'           => 'Bookings',
                        'data'            => array_map('intval', array_column($bookingTrend, 'total')),
                        'backgroundColor' => 'rgba(59,130,246,0.7)',
                        'borderColor'     => 'rgba(59,130,246,1)',
                        'borderWidth'     => 1,
                    ]],
                ],
            ];
        }

        if (! empty($peakHours)) {
            $slotOrder = ['08:00-10:00', '10:00-12:00', '13:00-15:00', '15:00-17:00', 'Other'];
            usort($peakHours, static fn(array $a, array $b): int =>
                array_search($a['time_slot'], $slotOrder, true) <=> array_search($b['time_slot'], $slotOrder, true)
            );
            $charts[] = [
                'id'     => 'chartPeakHours',
                'title'  => 'Peak Usage Hours',
                'type'   => 'bar',
                'height' => 260,
                'data'   => [
                    'labels'   => array_column($peakHours, 'time_slot'),
                    'datasets' => [[
                        'label'           => 'Approved Bookings',
                        'data'            => array_map('intval', array_column($peakHours, 'total')),
                        'backgroundColor' => [
                            'rgba(16,185,129,0.7)',
                            'rgba(59,130,246,0.7)',
                            'rgba(245,158,11,0.7)',
                            'rgba(239,68,68,0.7)',
                            'rgba(156,163,175,0.7)',
                        ],
                        'borderWidth' => 1,
                    ]],
                ],
            ];
        }

        if (! empty($labRows)) {
            $charts[] = [
                'id'     => 'chartLabUtilization',
                'title'  => 'Laboratory Utilization Rate (%)',
                'type'   => 'bar',
                'height' => 280,
                'data'   => [
                    'labels'   => array_column(array_slice($labRows, 0, 8), 'laboratory_name'),
                    'datasets' => [[
                        'label'           => 'Utilization %',
                        'data'            => array_map(static fn(array $r): float => (float) $r['usage_percentage'], array_slice($labRows, 0, 8)),
                        'backgroundColor' => 'rgba(139,92,246,0.7)',
                        'borderColor'     => 'rgba(139,92,246,1)',
                        'borderWidth'     => 1,
                    ]],
                ],
            ];
        }

        if (! empty($maintenanceTrend)) {
            $charts[] = [
                'id'     => 'chartMaintenanceTrend',
                'title'  => 'Maintenance Cases Trend (Monthly)',
                'type'   => 'line',
                'height' => 260,
                'data'   => [
                    'labels'   => array_column($maintenanceTrend, 'display_label'),
                    'datasets' => [[
                        'label'           => 'Cases Logged',
                        'data'            => array_map('intval', array_column($maintenanceTrend, 'total')),
                        'backgroundColor' => 'rgba(239,68,68,0.15)',
                        'borderColor'     => 'rgba(239,68,68,1)',
                        'borderWidth'     => 2,
                        'fill'            => true,
                        'tension'         => 0.3,
                    ]],
                ],
            ];
        }

        // --- Summary cards ---
        $maintenanceOpen = $maintenanceSummary['reported'] + $maintenanceSummary['scheduled']
            + $maintenanceSummary['in_progress'] + $maintenanceSummary['testing'];

        $summaryCards = [
            ['label' => 'Total Bookings',        'value' => $totalBookings,                          'tone' => 'primary'],
            ['label' => 'Approved',              'value' => $statusSummary['APPROVED'] ?? 0,         'tone' => 'success'],
            ['label' => 'Pending',               'value' => $statusSummary['PENDING'] ?? 0,          'tone' => 'warning'],
            ['label' => 'Rejected',              'value' => $statusSummary['REJECTED'] ?? 0,         'tone' => 'danger'],
            ['label' => 'Open Maintenance',      'value' => $maintenanceOpen,                        'tone' => 'warning'],
            ['label' => 'Completed Maintenance', 'value' => $maintenanceSummary['completed'] ?? 0,   'tone' => 'success'],
        ];

        // --- Filters ---
        $availableLabs = $model->availableLaboratories($scope);
        $labOptions    = array_map(static fn(array $l): array => [
            'value' => $l['id'],
            'label' => $l['name'] . ($l['room'] ? ' (' . $l['room'] . ')' : ''),
        ], $availableLabs);

        $filterFields = [
            ['name' => 'date_from', 'label' => 'From Date', 'type' => 'date'],
            ['name' => 'date_to',   'label' => 'To Date',   'type' => 'date'],
            ['name' => 'lab_id',    'label' => 'Laboratory', 'type' => 'select', 'options' => $labOptions],
        ];

        $appliedFilters = [];
        if (! empty($filters['date_from'])) {
            $appliedFilters[] = ['label' => 'From', 'value' => $filters['date_from']];
        }
        if (! empty($filters['date_to'])) {
            $appliedFilters[] = ['label' => 'To', 'value' => $filters['date_to']];
        }
        if (! empty($filters['lab_id'])) {
            $matched = array_filter($labOptions, static fn(array $o): bool => (string) $o['value'] === (string) $filters['lab_id']);
            $labLabel = $matched ? reset($matched)['label'] : $filters['lab_id'];
            $appliedFilters[] = ['label' => 'Lab', 'value' => $labLabel];
        }

        // --- Nav items ---
        $navItems = [
            ['label' => 'Analytics Overview', 'href' => site_url('/dashboard/reports/analytics'), 'active' => true],
        ];

        $layoutView = match ($role) {
            'admin'   => 'layouts/main_admin',
            default   => 'layouts/main_user',
        };

        $scopeLabel = $role === 'pic' ? 'PIC Scope (Assigned Labs)' : 'System-wide Scope';

        return view('reports/analytics', [
            'layoutView'                => $layoutView,
            'pageTitle'                 => strtoupper($role) . ' Analytics Dashboard',
            'pageDescription'           => 'Booking trends, peak usage, laboratory utilization, and maintenance insights.',
            'scopeLabel'                => $scopeLabel,
            'summaryCards'              => $summaryCards,
            'summaryExportUrls'         => [
                'pdf'   => site_url('/dashboard/reports/pdf'),
                'excel' => site_url('/dashboard/reports/excel'),
                'csv'   => site_url('/dashboard/reports/csv'),
            ],
            'charts'                    => $charts,
            'mostUsedLabs'              => $mostUsedLabs,
            'leastUsedLabs'             => $leastUsedLabs,
            'frequentMaintenanceAssets' => $frequentMaintenanceAssets,
            'recentMaintenance'         => $recentMaintenance,
            'emptyMaintenanceMessage'   => 'No maintenance activity found for the selected period.',
            'navItems'                  => $navItems,
            'filterAction'              => site_url('/dashboard/reports/analytics'),
            'filterFields'              => $filterFields,
            'filters'                   => $filters,
            'appliedFilters'            => $appliedFilters,
        ]);
    }
}
