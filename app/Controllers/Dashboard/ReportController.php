<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\AnalyticsReportModel;
use App\Models\BookingModel;
use App\Models\MaintenanceRecordModel;
use Dompdf\Dompdf;

class ReportController extends BaseController
{
    private const REPORT_TYPES = [
        'bookings' => [
            'title' => 'Booking Reports',
            'description' => 'Track booking demand, approval progress, requester activity, and filtered operational history.',
            'file_prefix' => 'bookings_report',
            'empty_message' => 'No bookings match the selected filters.',
        ],
        'laboratory-usage' => [
            'title' => 'Laboratory Usage Reports',
            'description' => 'Review utilization, used hours, peak time windows, and underused laboratories.',
            'file_prefix' => 'laboratory_usage_report',
            'empty_message' => 'No laboratory usage data matches the selected filters.',
        ],
        'assets' => [
            'title' => 'Asset Reports',
            'description' => 'Review asset status, availability, maintenance recency, and category coverage across laboratories.',
            'file_prefix' => 'assets_report',
            'empty_message' => 'No assets match the selected filters.',
        ],
        'maintenance' => [
            'title' => 'Maintenance Reports',
            'description' => 'Monitor maintenance workload, priorities, assigned support, and completion timelines.',
            'file_prefix' => 'maintenance_report',
            'empty_message' => 'No maintenance records match the selected filters.',
        ],
    ];

    private const USER_ROLE_OPTIONS = [
        'student' => 'Student',
        'staff' => 'Staff',
        'external' => 'External',
        'technician' => 'Technician',
        'pic' => 'PIC',
        'manager' => 'Lab Manager',
        'admin' => 'Admin',
    ];

    private AnalyticsReportModel $analytics;
    private MaintenanceRecordModel $maintenanceRecords;

    public function __construct()
    {
        $this->analytics = new AnalyticsReportModel();
        $this->maintenanceRecords = new MaintenanceRecordModel();
    }

    public function analytics()
    {
        $context = $this->reportContext();
        if (! is_array($context)) {
            return $context;
        }

        $filters = $this->sanitizeFilters($context, 'analytics');
        $bundle = $this->buildAnalyticsBundle($context, $filters);

        return view('reports/analytics', [
            'title' => 'Analytics & Reporting | SLAMS',
            'page' => 'Analytics & Reporting',
            'layoutView' => $context['layoutView'],
            'user' => $context['user'],
            'routeBase' => $context['routeBase'],
            'scopeLabel' => $context['scopeLabel'],
            'pageTitle' => 'Analytics Overview',
            'pageDescription' => 'Role-scoped insights across bookings, laboratory usage, assets, and maintenance activity.',
            'navItems' => $this->moduleNav($context, $filters, 'analytics'),
            'filterAction' => $context['routeBase'] . '/analytics',
            'filters' => $filters,
            'filterFields' => $this->filterFields('analytics', $context['filterOptions']),
            'appliedFilters' => $this->appliedFilters($filters, $context['filterOptions'], 'analytics'),
            'summaryCards' => $bundle['summary_cards'],
            'charts' => $bundle['charts'],
            'mostUsedLabs' => $bundle['most_used_labs'],
            'leastUsedLabs' => $bundle['least_used_labs'],
            'frequentMaintenanceAssets' => $bundle['frequent_maintenance_assets'],
            'recentMaintenance' => $bundle['recent_maintenance'],
            'emptyMaintenanceMessage' => 'No recent maintenance activity matches the selected filters.',
            'summaryExportUrls' => [
                'pdf' => $this->exportUrl($context, 'summary', 'pdf', $filters),
                'csv' => $this->exportUrl($context, 'summary', 'csv', $filters),
            ],
        ]);
    }

    public function show(string $type)
    {
        $context = $this->reportContext();
        if (! is_array($context)) {
            return $context;
        }

        $type = $this->normalizeReportType($type);
        if ($type === null) {
            return redirect()->back()->with('error', 'Unknown report type requested.');
        }

        $filters = $this->sanitizeFilters($context, $type);
        $dataset = $this->reportDataset($type, $context, $filters);

        return view('reports/report', [
            'title' => $dataset['title'] . ' | SLAMS',
            'page' => 'Analytics & Reporting',
            'layoutView' => $context['layoutView'],
            'user' => $context['user'],
            'routeBase' => $context['routeBase'],
            'scopeLabel' => $context['scopeLabel'],
            'pageTitle' => $dataset['title'],
            'pageDescription' => $dataset['description'],
            'reportType' => $type,
            'navItems' => $this->moduleNav($context, $filters, $type),
            'filterAction' => $context['routeBase'] . '/reports/' . $type,
            'filters' => $filters,
            'filterFields' => $this->filterFields($type, $context['filterOptions']),
            'appliedFilters' => $this->appliedFilters($filters, $context['filterOptions'], $type),
            'summaryCards' => $dataset['summary_cards'],
            'charts' => $dataset['charts'],
            'columns' => $dataset['columns'],
            'rows' => $dataset['rows'],
            'emptyMessage' => $dataset['empty_message'],
            'exportUrls' => [
                'pdf' => $this->exportUrl($context, $type, 'pdf', $filters),
                'csv' => $this->exportUrl($context, $type, 'csv', $filters),
            ],
        ]);
    }

    public function download()
    {
        $type = (string) ($this->request->getGet('type') ?: 'summary');
        return $this->exportPdf($type);
    }

    public function downloadCsv()
    {
        $type = (string) ($this->request->getGet('type') ?: 'summary');
        return $this->exportCsv($type);
    }

    public function exportPdf(?string $type = null)
    {
        $context = $this->reportContext();
        if (! is_array($context)) {
            return $context;
        }

        $payload = $this->exportPayload($type ?? 'summary', $context);
        if (! is_array($payload)) {
            return redirect()->back()->with('error', 'Unable to prepare the PDF export.');
        }

        try {
            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml(view('reports/export_pdf', $payload));
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
            $canvas->page_text(730, 565, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 9, [0.35, 0.41, 0.39]);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $payload['filename'] . '.pdf"')
                ->setBody($dompdf->output());
        } catch (\Throwable $exception) {
            log_message('error', 'Report PDF export failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->back()->with('error', 'Unable to generate the PDF report right now.');
        }
    }

    public function exportCsv(?string $type = null)
    {
        $context = $this->reportContext();
        if (! is_array($context)) {
            return $context;
        }

        $payload = $this->exportPayload($type ?? 'summary', $context);
        if (! is_array($payload)) {
            return redirect()->back()->with('error', 'Unable to prepare the CSV export.');
        }

        try {
            $handle = fopen('php://temp', 'r+');
            if ($handle === false) {
                throw new \RuntimeException('Unable to open temporary CSV handle.');
            }

            fputcsv($handle, ['Smart Laboratory and Asset Management System']);
            fputcsv($handle, [$payload['reportTitle']]);
            fputcsv($handle, ['Generated', $payload['generatedAt']]);
            fputcsv($handle, ['Scope', $payload['scopeLabel']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Applied Filters', 'Value']);
            foreach ($payload['appliedFilters'] as $filter) {
                fputcsv($handle, [$filter['label'], $filter['value']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Summary', 'Value']);
            foreach ($payload['summaryCards'] as $card) {
                fputcsv($handle, [$card['label'], $card['value']]);
            }

            foreach ($payload['sections'] as $section) {
                fputcsv($handle, []);
                fputcsv($handle, [$section['title']]);
                fputcsv($handle, $section['columns']);
                if ($section['rows'] === []) {
                    fputcsv($handle, ['No matching records.']);
                    continue;
                }
                foreach ($section['rows'] as $row) {
                    fputcsv($handle, $row);
                }
            }

            rewind($handle);
            $csv = stream_get_contents($handle) ?: '';
            fclose($handle);

            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $payload['filename'] . '.csv"')
                ->setBody($csv);
        } catch (\Throwable $exception) {
            log_message('error', 'Report CSV export failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->back()->with('error', 'Unable to generate the CSV report right now.');
        }
    }

    private function reportContext()
    {
        helper('auth');

        if (! auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $role = null;
        if ($user->inGroup('admin')) {
            $role = 'admin';
        } elseif ($user->inGroup('manager')) {
            $role = 'manager';
        } elseif ($user->inGroup('pic')) {
            $role = 'pic';
        }

        if ($role === null) {
            return redirect()->back()->with('error', 'You do not have access to analytics and reports.');
        }

        $email = $this->currentUserEmail((int) $user->id);
        $labIds = [];
        if ($role === 'pic') {
            $labIds = array_map(
                static fn(array $row): int => (int) $row['id'],
                db_connect()->table('laboratories')
                    ->select('id')
                    ->where('LOWER(TRIM(pic_email)) =', strtolower(trim($email)))
                    ->get()
                    ->getResultArray()
            );
        }

        $filterOptions = [
            'labs' => $this->mapOptions(
                $this->analytics->availableLaboratories(['role' => $role, 'labIds' => $labIds]),
                'id',
                static fn(array $row): string => ($row['name'] ?? 'Lab') . (! empty($row['room']) ? ' • ' . $row['room'] : '')
            ),
            'faculties' => $this->mapOptions(
                $this->analytics->availableFaculties(['role' => $role, 'labIds' => $labIds]),
                'id',
                static fn(array $row): string => (string) ($row['name_en'] ?? 'Unknown Faculty')
            ),
            'users' => $this->mapOptions(
                $this->analytics->availableUsers(['role' => $role, 'labIds' => $labIds]),
                'id',
                static fn(array $row): string => (string) ($row['label'] ?? 'Unknown User')
            ),
            'asset_categories' => array_map(
                static fn(string $value): array => ['value' => $value, 'label' => $value],
                $this->analytics->availableAssetCategories(['role' => $role, 'labIds' => $labIds])
            ),
            'booking_statuses' => array_map(
                static fn(string $status): array => ['value' => $status, 'label' => ucwords(strtolower($status))],
                BookingModel::CORE_STATUSES
            ),
            'asset_statuses' => [
                ['value' => 'available', 'label' => 'Available'],
                ['value' => 'maintenance', 'label' => 'Under Maintenance'],
                ['value' => 'faulty', 'label' => 'Faulty'],
            ],
            'maintenance_statuses' => array_map(
                static fn(string $status, string $label): array => ['value' => $status, 'label' => $label],
                array_keys($this->maintenanceRecords->workflowLabels()),
                array_values($this->maintenanceRecords->workflowLabels())
            ),
            'user_roles' => array_map(
                static fn(string $value, string $label): array => ['value' => $value, 'label' => $label],
                array_keys(self::USER_ROLE_OPTIONS),
                array_values(self::USER_ROLE_OPTIONS)
            ),
        ];

        return [
            'user' => $user,
            'role' => $role,
            'labIds' => $labIds,
            'scopeLabel' => $role === 'pic' ? 'Assigned laboratories only' : 'System-wide scope',
            'layoutView' => $role === 'admin' ? 'layouts/main_admin' : 'layouts/main_user',
            'routeBase' => $role === 'admin' ? '/admin' : '/dashboard',
            'filterOptions' => $filterOptions,
        ];
    }

    private function sanitizeFilters(array $context, string $pageType): array
    {
        $dateFrom = $this->validDate((string) $this->request->getGet('date_from'));
        $dateTo = $this->validDate((string) $this->request->getGet('date_to'));

        if ($pageType === 'analytics' && $dateFrom === null && $dateTo === null) {
            $dateTo = date('Y-m-d');
            $dateFrom = date('Y-m-d', strtotime('-29 days'));
        }

        if ($dateFrom !== null && $dateTo !== null && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'lab_id' => $this->validOptionId((string) $this->request->getGet('lab_id'), $context['filterOptions']['labs']),
            'booking_status' => $this->validOptionValue((string) $this->request->getGet('booking_status'), $context['filterOptions']['booking_statuses']),
            'user_id' => $this->validOptionId((string) $this->request->getGet('user_id'), $context['filterOptions']['users']),
            'user_role' => $this->validOptionValue((string) $this->request->getGet('user_role'), $context['filterOptions']['user_roles']),
            'faculty_id' => $this->validOptionId((string) $this->request->getGet('faculty_id'), $context['filterOptions']['faculties']),
            'asset_category' => $this->validOptionValue((string) $this->request->getGet('asset_category'), $context['filterOptions']['asset_categories']),
            'asset_status' => $this->validOptionValue((string) $this->request->getGet('asset_status'), $context['filterOptions']['asset_statuses']),
            'maintenance_status' => $this->validOptionValue((string) $this->request->getGet('maintenance_status'), $context['filterOptions']['maintenance_statuses']),
            'trend' => $this->validTrend((string) $this->request->getGet('trend')),
        ];

        if ($filters['user_role'] !== null) {
            $filters['role_user_ids'] = $this->resolveRoleUserIds($filters['user_role']);
        }

        return $filters;
    }

    private function buildAnalyticsBundle(array $context, array $filters): array
    {
        $bookingStatus = $this->analytics->bookingStatusSummary($filters, $context);
        $usageRows = $this->analytics->laboratoryUsageRows($filters, $context);
        $assetStatus = $this->analytics->assetStatusSummary($filters, $context);
        $maintenanceStatus = $this->analytics->maintenanceStatusSummary($filters, $context);
        $frequentMaintenanceAssets = $this->analytics->frequentMaintenanceAssets($filters, $context, 6);
        $recentMaintenance = $this->analytics->recentMaintenanceActivities($filters, $context, 8);
        $trend = $this->analytics->bookingTrend($filters, $context, $filters['trend'] ?? 'week');

        $mostUsedLabs = $usageRows;
        usort($mostUsedLabs, static function (array $left, array $right): int {
            return [$right['total_used_hours'], $right['total_bookings'], $right['usage_percentage']]
                <=> [$left['total_used_hours'], $left['total_bookings'], $left['usage_percentage']];
        });
        $mostUsedLabs = array_slice($mostUsedLabs, 0, 5);

        $leastUsedLabs = $usageRows;
        usort($leastUsedLabs, static function (array $left, array $right): int {
            return [$left['total_used_hours'], $left['total_bookings'], $left['usage_percentage']]
                <=> [$right['total_used_hours'], $right['total_bookings'], $right['usage_percentage']];
        });
        $leastUsedLabs = array_slice($leastUsedLabs, 0, 5);
        $usageChartRows = array_slice($mostUsedLabs, 0, 8);

        $summaryCards = [
            ['label' => 'Total Bookings', 'value' => array_sum($bookingStatus), 'tone' => 'primary'],
            ['label' => 'Approved Bookings', 'value' => $bookingStatus['APPROVED'] ?? 0, 'tone' => 'success'],
            ['label' => 'Pending Bookings', 'value' => $bookingStatus['PENDING'] ?? 0, 'tone' => 'warning'],
            ['label' => 'Cancelled / Rejected', 'value' => ($bookingStatus['CANCELLED'] ?? 0) + ($bookingStatus['REJECTED'] ?? 0), 'tone' => 'danger'],
            ['label' => 'Available Assets', 'value' => $assetStatus['available'] ?? 0, 'tone' => 'success'],
            ['label' => 'Assets Under Maintenance', 'value' => $assetStatus['maintenance'] ?? 0, 'tone' => 'warning'],
            ['label' => 'Open Maintenance', 'value' => ($maintenanceStatus['reported'] ?? 0) + ($maintenanceStatus['scheduled'] ?? 0) + ($maintenanceStatus['in_progress'] ?? 0) + ($maintenanceStatus['testing'] ?? 0), 'tone' => 'danger'],
            ['label' => 'Completed Maintenance', 'value' => $maintenanceStatus['completed'] ?? 0, 'tone' => 'info'],
        ];

        return [
            'summary_cards' => $summaryCards,
            'booking_status' => $bookingStatus,
            'asset_status' => $assetStatus,
            'maintenance_status' => $maintenanceStatus,
            'most_used_labs' => $mostUsedLabs,
            'least_used_labs' => $leastUsedLabs,
            'frequent_maintenance_assets' => $frequentMaintenanceAssets,
            'recent_maintenance' => $recentMaintenance,
            'charts' => [
                $this->doughnutChart(
                    'analyticsBookingStatusChart',
                    'Booking Status Breakdown',
                    array_keys($bookingStatus),
                    array_values($bookingStatus),
                    ['#0f766e', '#f59e0b', '#b91c1c', '#475569']
                ),
                $this->lineChart(
                    'analyticsBookingTrendChart',
                    'Booking Trend',
                    array_column($trend, 'display_label'),
                    array_map('intval', array_column($trend, 'total')),
                    'Bookings'
                ),
                $this->barChart(
                    'analyticsUsageChart',
                    'Laboratory Usage Percentage',
                    array_column($usageChartRows, 'laboratory_name'),
                    array_map(static fn(array $row): float => (float) $row['usage_percentage'], $usageChartRows),
                    'Usage %',
                    '#2563eb'
                ),
                $this->doughnutChart(
                    'analyticsAssetStatusChart',
                    'Asset Availability Summary',
                    ['Available', 'Under Maintenance', 'Faulty'],
                    [
                        $assetStatus['available'] ?? 0,
                        $assetStatus['maintenance'] ?? 0,
                        $assetStatus['faulty'] ?? 0,
                    ],
                    ['#15803d', '#b45309', '#b91c1c']
                ),
                $this->doughnutChart(
                    'analyticsMaintenanceStatusChart',
                    'Maintenance Status Summary',
                    array_map(static fn(string $status): string => ucwords(str_replace('_', ' ', $status)), array_keys($maintenanceStatus)),
                    array_values($maintenanceStatus),
                    ['#2563eb', '#f59e0b', '#0f766e', '#7c3aed', '#15803d', '#b91c1c']
                ),
                $this->barChart(
                    'analyticsFrequentMaintenanceChart',
                    'Most Frequently Maintained Assets',
                    array_column($frequentMaintenanceAssets, 'asset_name'),
                    array_map('intval', array_column($frequentMaintenanceAssets, 'total')),
                    'Maintenance Cases',
                    '#7c3aed'
                ),
            ],
        ];
    }

    private function reportDataset(string $type, array $context, array $filters): array
    {
        $meta = self::REPORT_TYPES[$type];

        if ($type === 'bookings') {
            $rows = array_map(function (array $row): array {
                return [
                    'booking_id' => (string) $row['id'],
                    'laboratory' => trim((string) $row['laboratory_name'] . (! empty($row['laboratory_room']) ? ' • ' . $row['laboratory_room'] : '')),
                    'requested_by' => (string) $row['requested_by'],
                    'booking_date_time' => ($row['date'] ? date('d M Y', strtotime((string) $row['date'])) : '-') . ' • ' . substr((string) ($row['start_time'] ?? ''), 0, 5) . ' - ' . substr((string) ($row['end_time'] ?? ''), 0, 5),
                    'purpose' => trim((string) ($row['activity'] ?? '')) !== '' ? (string) $row['activity'] : '-',
                    'status' => ucwords(strtolower((string) ($row['status'] ?? ''))),
                    'approval_status' => (string) ($row['approval_status'] ?? 'Pending'),
                    'created_date' => $this->formatDateTime($row['created_at'] ?? null),
                ];
            }, $this->analytics->bookingReportRows($filters, $context));

            $statusSummary = $this->analytics->bookingStatusSummary($filters, $context);
            $trend = $this->analytics->bookingTrend($filters, $context, $filters['trend'] ?? 'week');

            return [
                'title' => $meta['title'],
                'description' => $meta['description'],
                'file_prefix' => $meta['file_prefix'],
                'empty_message' => $meta['empty_message'],
                'summary_cards' => [
                    ['label' => 'Total Bookings', 'value' => array_sum($statusSummary), 'tone' => 'primary'],
                    ['label' => 'Approved', 'value' => $statusSummary['APPROVED'] ?? 0, 'tone' => 'success'],
                    ['label' => 'Pending', 'value' => $statusSummary['PENDING'] ?? 0, 'tone' => 'warning'],
                    ['label' => 'Cancelled / Rejected', 'value' => ($statusSummary['CANCELLED'] ?? 0) + ($statusSummary['REJECTED'] ?? 0), 'tone' => 'danger'],
                ],
                'charts' => [
                    $this->doughnutChart(
                        'bookingStatusChart',
                        'Booking Status Breakdown',
                        array_keys($statusSummary),
                        array_values($statusSummary),
                        ['#0f766e', '#f59e0b', '#b91c1c', '#475569']
                    ),
                    $this->lineChart(
                        'bookingTrendChart',
                        'Booking Trend',
                        array_column($trend, 'display_label'),
                        array_map('intval', array_column($trend, 'total')),
                        'Bookings'
                    ),
                ],
                'columns' => [
                    ['key' => 'booking_id', 'label' => 'Booking ID'],
                    ['key' => 'laboratory', 'label' => 'Laboratory'],
                    ['key' => 'requested_by', 'label' => 'Requested By'],
                    ['key' => 'booking_date_time', 'label' => 'Booking Date / Time'],
                    ['key' => 'purpose', 'label' => 'Purpose'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'approval_status', 'label' => 'Approval Status'],
                    ['key' => 'created_date', 'label' => 'Created Date'],
                ],
                'rows' => $rows,
            ];
        }

        if ($type === 'laboratory-usage') {
            $usageRows = $this->analytics->laboratoryUsageRows($filters, $context);
            $rows = array_map(static function (array $row): array {
                return [
                    'laboratory' => trim((string) $row['laboratory_name'] . (! empty($row['laboratory_room']) ? ' • ' . $row['laboratory_room'] : '')),
                    'total_bookings' => (string) $row['total_bookings'],
                    'total_used_hours' => number_format((float) $row['total_used_hours'], 1),
                    'usage_percentage' => number_format((float) $row['usage_percentage'], 1) . '%',
                    'peak_usage' => $row['peak_usage_day'] . ' • ' . $row['peak_usage_time'],
                    'cancelled_rejected' => (string) $row['cancelled_rejected_count'],
                ];
            }, $usageRows);

            $totalBookings = array_sum(array_map(static fn(array $row): int => (int) $row['total_bookings'], $usageRows));
            $totalHours = array_sum(array_map(static fn(array $row): float => (float) $row['total_used_hours'], $usageRows));
            $avgUsage = $usageRows === [] ? 0.0 : round(array_sum(array_map(static fn(array $row): float => (float) $row['usage_percentage'], $usageRows)) / count($usageRows), 1);

            return [
                'title' => $meta['title'],
                'description' => $meta['description'],
                'file_prefix' => $meta['file_prefix'],
                'empty_message' => $meta['empty_message'],
                'summary_cards' => [
                    ['label' => 'Laboratories in Scope', 'value' => count($usageRows), 'tone' => 'primary'],
                    ['label' => 'Total Bookings', 'value' => $totalBookings, 'tone' => 'success'],
                    ['label' => 'Total Used Hours', 'value' => number_format($totalHours, 1), 'tone' => 'info'],
                    ['label' => 'Average Usage', 'value' => number_format($avgUsage, 1) . '%', 'tone' => 'warning'],
                ],
                'charts' => [
                    $this->barChart(
                        'usagePercentageChart',
                        'Usage Percentage by Laboratory',
                        array_column($usageRows, 'laboratory_name'),
                        array_map(static fn(array $row): float => (float) $row['usage_percentage'], $usageRows),
                        'Usage %',
                        '#2563eb'
                    ),
                    $this->barChart(
                        'usageHoursChart',
                        'Used Hours by Laboratory',
                        array_column($usageRows, 'laboratory_name'),
                        array_map(static fn(array $row): float => (float) $row['total_used_hours'], $usageRows),
                        'Used Hours',
                        '#0f766e'
                    ),
                ],
                'columns' => [
                    ['key' => 'laboratory', 'label' => 'Laboratory'],
                    ['key' => 'total_bookings', 'label' => 'Total Bookings'],
                    ['key' => 'total_used_hours', 'label' => 'Total Used Hours'],
                    ['key' => 'usage_percentage', 'label' => 'Usage Percentage'],
                    ['key' => 'peak_usage', 'label' => 'Peak Usage Day / Time'],
                    ['key' => 'cancelled_rejected', 'label' => 'Cancelled / Rejected'],
                ],
                'rows' => $rows,
            ];
        }

        if ($type === 'assets') {
            $assetRows = $this->analytics->assetReportRows($filters, $context);
            $assetStatus = $this->analytics->assetStatusSummary($filters, $context);
            $mostUsedAssets = $this->analytics->mostUsedAssets($filters, $context, 6);

            $rows = array_map(static function (array $row): array {
                $totalQuantity = max((int) ($row['total_quantity'] ?? 0), (int) ($row['quantity'] ?? 0), 1);
                return [
                    'asset_name' => (string) ($row['name'] ?? 'Unknown Asset'),
                    'asset_code' => trim((string) ($row['asset_code'] ?? '')) !== '' ? (string) $row['asset_code'] : '-',
                    'laboratory_location' => trim((string) ($row['laboratory_name'] ?? '-') . (! empty($row['laboratory_room']) ? ' • ' . $row['laboratory_room'] : '') . (! empty($row['location_note']) ? ' • ' . $row['location_note'] : '')),
                    'category' => trim((string) ($row['category'] ?? '')) !== '' ? (string) $row['category'] : 'Uncategorized',
                    'status' => ucwords(strtolower((string) ($row['status'] ?? 'unknown'))),
                    'availability' => (string) max((int) ($row['quantity'] ?? 0), 0) . ' / ' . $totalQuantity,
                    'last_maintenance_date' => $this->formatDate($row['last_maintenance_date'] ?? null),
                ];
            }, $assetRows);

            return [
                'title' => $meta['title'],
                'description' => $meta['description'],
                'file_prefix' => $meta['file_prefix'],
                'empty_message' => $meta['empty_message'],
                'summary_cards' => [
                    ['label' => 'Total Assets', 'value' => count($assetRows), 'tone' => 'primary'],
                    ['label' => 'Available', 'value' => $assetStatus['available'] ?? 0, 'tone' => 'success'],
                    ['label' => 'Under Maintenance', 'value' => $assetStatus['maintenance'] ?? 0, 'tone' => 'warning'],
                    ['label' => 'Faulty', 'value' => $assetStatus['faulty'] ?? 0, 'tone' => 'danger'],
                ],
                'charts' => [
                    $this->doughnutChart(
                        'assetStatusChart',
                        'Asset Status Summary',
                        ['Available', 'Under Maintenance', 'Faulty'],
                        [
                            $assetStatus['available'] ?? 0,
                            $assetStatus['maintenance'] ?? 0,
                            $assetStatus['faulty'] ?? 0,
                        ],
                        ['#15803d', '#b45309', '#b91c1c']
                    ),
                    $this->barChart(
                        'assetUsageChart',
                        'Most Used Assets',
                        array_column($mostUsedAssets, 'name'),
                        array_map(static fn(array $row): int => (int) ($row['total_used'] ?? 0), $mostUsedAssets),
                        'Units Booked',
                        '#7c3aed'
                    ),
                ],
                'columns' => [
                    ['key' => 'asset_name', 'label' => 'Asset Name'],
                    ['key' => 'asset_code', 'label' => 'Asset Code / Tag'],
                    ['key' => 'laboratory_location', 'label' => 'Laboratory / Location'],
                    ['key' => 'category', 'label' => 'Category'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'availability', 'label' => 'Availability'],
                    ['key' => 'last_maintenance_date', 'label' => 'Last Maintenance Date'],
                ],
                'rows' => $rows,
            ];
        }

        $maintenanceRows = $this->analytics->maintenanceReportRows($filters, $context);
        $maintenanceStatus = $this->analytics->maintenanceStatusSummary($filters, $context);
        $maintenanceTrend = $this->analytics->maintenanceTrend($filters, $context, $filters['trend'] ?? 'week');

        $rows = array_map(static function (array $row): array {
            $assigned = 'PIC: ' . (! empty($row['pic_name']) ? $row['pic_name'] : 'Unassigned');
            $assigned .= ' • Technician: ' . (! empty($row['technician_name']) ? $row['technician_name'] : 'Unassigned');

            return [
                'maintenance_id' => (string) $row['id'],
                'asset_laboratory' => trim((string) ($row['asset_name'] ?? 'Unknown Asset') . (! empty($row['asset_code']) ? ' • ' . $row['asset_code'] : '') . (! empty($row['laboratory_name']) ? ' • ' . $row['laboratory_name'] : '')),
                'issue_type' => ucwords(str_replace('_', ' ', (string) ($row['issue_type'] ?? 'other'))),
                'reported_by' => (string) ($row['reported_by_name'] ?? 'System'),
                'assigned_support' => $assigned,
                'status' => ucwords(str_replace('_', ' ', (string) ($row['status'] ?? 'unknown'))),
                'priority' => ucwords((string) ($row['priority'] ?? 'medium')),
                'created_date' => $this->formatDateTime($row['created_at'] ?? null),
                'completed_date' => $this->formatDateTime($row['completed_at'] ?? null),
            ];
        }, $maintenanceRows);

        $criticalCount = count(array_filter($maintenanceRows, static fn(array $row): bool => strtolower((string) ($row['priority'] ?? '')) === 'critical'));
        $openCount = ($maintenanceStatus['reported'] ?? 0) + ($maintenanceStatus['scheduled'] ?? 0) + ($maintenanceStatus['in_progress'] ?? 0) + ($maintenanceStatus['testing'] ?? 0);

        return [
            'title' => $meta['title'],
            'description' => $meta['description'],
            'file_prefix' => $meta['file_prefix'],
            'empty_message' => $meta['empty_message'],
            'summary_cards' => [
                ['label' => 'Total Records', 'value' => count($maintenanceRows), 'tone' => 'primary'],
                ['label' => 'Open Cases', 'value' => $openCount, 'tone' => 'warning'],
                ['label' => 'Completed', 'value' => $maintenanceStatus['completed'] ?? 0, 'tone' => 'success'],
                ['label' => 'Critical Priority', 'value' => $criticalCount, 'tone' => 'danger'],
            ],
            'charts' => [
                $this->doughnutChart(
                    'maintenanceStatusChart',
                    'Maintenance Status Breakdown',
                    array_map(static fn(string $status): string => ucwords(str_replace('_', ' ', $status)), array_keys($maintenanceStatus)),
                    array_values($maintenanceStatus),
                    ['#2563eb', '#f59e0b', '#0f766e', '#7c3aed', '#15803d', '#b91c1c']
                ),
                $this->lineChart(
                    'maintenanceTrendChart',
                    'Maintenance Activity Trend',
                    array_column($maintenanceTrend, 'display_label'),
                    array_map('intval', array_column($maintenanceTrend, 'total')),
                    'Maintenance Cases'
                ),
            ],
            'columns' => [
                ['key' => 'maintenance_id', 'label' => 'Maintenance ID'],
                ['key' => 'asset_laboratory', 'label' => 'Asset / Laboratory'],
                ['key' => 'issue_type', 'label' => 'Issue Type'],
                ['key' => 'reported_by', 'label' => 'Reported By'],
                ['key' => 'assigned_support', 'label' => 'Assigned PIC / Technician'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'priority', 'label' => 'Priority'],
                ['key' => 'created_date', 'label' => 'Created Date'],
                ['key' => 'completed_date', 'label' => 'Completed Date'],
            ],
            'rows' => $rows,
        ];
    }

    private function exportPayload(string $type, array $context): ?array
    {
        $type = $type === '' ? 'summary' : $type;
        if ($type !== 'summary' && ! array_key_exists($type, self::REPORT_TYPES)) {
            return null;
        }

        $pageType = $type === 'summary' ? 'analytics' : $type;
        $filters = $this->sanitizeFilters($context, $pageType);
        $appliedFilters = $this->appliedFilters($filters, $context['filterOptions'], $pageType);

        if ($type === 'summary') {
            $bundle = $this->buildAnalyticsBundle($context, $filters);

            return [
                'filename' => 'analytics_summary_report_' . date('Ymd_His'),
                'reportTitle' => 'Analytics Summary Report',
                'generatedAt' => date('Y-m-d H:i'),
                'scopeLabel' => $context['scopeLabel'],
                'appliedFilters' => $appliedFilters,
                'summaryCards' => $bundle['summary_cards'],
                'sections' => [
                    [
                        'title' => 'Booking Status Summary',
                        'columns' => ['Status', 'Total'],
                        'rows' => array_map(
                            static fn(string $status, int $total): array => [ucwords(strtolower($status)), (string) $total],
                            array_keys($bundle['booking_status']),
                            array_values($bundle['booking_status'])
                        ),
                    ],
                    [
                        'title' => 'Asset Availability Summary',
                        'columns' => ['Status', 'Total'],
                        'rows' => array_map(
                            static fn(string $status, int $total): array => [ucwords(str_replace('_', ' ', $status)), (string) $total],
                            array_keys($bundle['asset_status']),
                            array_values($bundle['asset_status'])
                        ),
                    ],
                    [
                        'title' => 'Maintenance Status Summary',
                        'columns' => ['Status', 'Total'],
                        'rows' => array_map(
                            static fn(string $status, int $total): array => [ucwords(str_replace('_', ' ', $status)), (string) $total],
                            array_keys($bundle['maintenance_status']),
                            array_values($bundle['maintenance_status'])
                        ),
                    ],
                    [
                        'title' => 'Most Used Laboratories',
                        'columns' => ['Laboratory', 'Bookings', 'Used Hours', 'Usage %'],
                        'rows' => array_map(static fn(array $row): array => [
                            (string) $row['laboratory_name'],
                            (string) $row['total_bookings'],
                            number_format((float) $row['total_used_hours'], 1),
                            number_format((float) $row['usage_percentage'], 1) . '%',
                        ], $bundle['most_used_labs']),
                    ],
                    [
                        'title' => 'Least Used Laboratories',
                        'columns' => ['Laboratory', 'Bookings', 'Used Hours', 'Usage %'],
                        'rows' => array_map(static fn(array $row): array => [
                            (string) $row['laboratory_name'],
                            (string) $row['total_bookings'],
                            number_format((float) $row['total_used_hours'], 1),
                            number_format((float) $row['usage_percentage'], 1) . '%',
                        ], $bundle['least_used_labs']),
                    ],
                    [
                        'title' => 'Most Frequently Maintained Assets',
                        'columns' => ['Asset', 'Total Cases'],
                        'rows' => array_map(static fn(array $row): array => [
                            (string) ($row['asset_name'] ?? 'Unknown Asset'),
                            (string) ($row['total'] ?? 0),
                        ], $bundle['frequent_maintenance_assets']),
                    ],
                    [
                        'title' => 'Recent Maintenance Activities',
                        'columns' => ['ID', 'Title', 'Asset', 'Laboratory', 'Status', 'Priority', 'Technician', 'Created'],
                        'rows' => array_map(function (array $row): array {
                            return [
                                (string) ($row['id'] ?? ''),
                                (string) ($row['title'] ?? '-'),
                                (string) ($row['asset_name'] ?? '-'),
                                (string) ($row['laboratory_name'] ?? '-'),
                                ucwords(str_replace('_', ' ', (string) ($row['status'] ?? 'unknown'))),
                                ucwords((string) ($row['priority'] ?? 'medium')),
                                (string) ($row['technician_name'] ?? 'Unassigned'),
                                $this->formatDateTime($row['created_at'] ?? null),
                            ];
                        }, $bundle['recent_maintenance']),
                    ],
                ],
            ];
        }

        $dataset = $this->reportDataset($type, $context, $filters);

        return [
            'filename' => $dataset['file_prefix'] . '_' . date('Ymd_His'),
            'reportTitle' => $dataset['title'],
            'generatedAt' => date('Y-m-d H:i'),
            'scopeLabel' => $context['scopeLabel'],
            'appliedFilters' => $appliedFilters,
            'summaryCards' => $dataset['summary_cards'],
            'sections' => [
                [
                    'title' => $dataset['title'],
                    'columns' => array_column($dataset['columns'], 'label'),
                    'rows' => array_map(function (array $row) use ($dataset): array {
                        $values = [];
                        foreach ($dataset['columns'] as $column) {
                            $values[] = (string) ($row[$column['key']] ?? '');
                        }
                        return $values;
                    }, $dataset['rows']),
                ],
            ],
        ];
    }

    private function moduleNav(array $context, array $filters, string $active): array
    {
        $items = [
            'analytics' => [
                'label' => 'Analytics',
                'href' => $context['routeBase'] . '/analytics',
            ],
            'bookings' => [
                'label' => 'Bookings',
                'href' => $context['routeBase'] . '/reports/bookings',
            ],
            'laboratory-usage' => [
                'label' => 'Lab Usage',
                'href' => $context['routeBase'] . '/reports/laboratory-usage',
            ],
            'assets' => [
                'label' => 'Assets',
                'href' => $context['routeBase'] . '/reports/assets',
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'href' => $context['routeBase'] . '/reports/maintenance',
            ],
        ];

        $query = $this->queryString($filters);
        foreach ($items as $key => &$item) {
            $item['active'] = $key === $active;
            if ($query !== '') {
                $item['href'] .= '?' . $query;
            }
        }

        return array_values($items);
    }

    private function filterFields(string $pageType, array $options): array
    {
        $fields = [
            ['name' => 'date_from', 'label' => 'Date From', 'type' => 'date'],
            ['name' => 'date_to', 'label' => 'Date To', 'type' => 'date'],
            ['name' => 'lab_id', 'label' => 'Laboratory', 'type' => 'select', 'options' => $options['labs']],
        ];

        if (in_array($pageType, ['analytics', 'bookings', 'laboratory-usage'], true)) {
            $fields[] = ['name' => 'faculty_id', 'label' => 'Faculty', 'type' => 'select', 'options' => $options['faculties']];
        }

        if (in_array($pageType, ['analytics', 'bookings', 'laboratory-usage'], true)) {
            $fields[] = ['name' => 'booking_status', 'label' => 'Booking Status', 'type' => 'select', 'options' => $options['booking_statuses']];
        }

        if (in_array($pageType, ['bookings', 'laboratory-usage'], true)) {
            $fields[] = ['name' => 'user_role', 'label' => 'User Role', 'type' => 'select', 'options' => $options['user_roles']];
            $fields[] = ['name' => 'user_id', 'label' => 'User', 'type' => 'select', 'options' => $options['users']];
        }

        if (in_array($pageType, ['assets', 'maintenance'], true)) {
            $fields[] = ['name' => 'asset_category', 'label' => 'Asset Category', 'type' => 'select', 'options' => $options['asset_categories']];
            $fields[] = ['name' => 'asset_status', 'label' => 'Asset Status', 'type' => 'select', 'options' => $options['asset_statuses']];
        }

        if (in_array($pageType, ['analytics', 'assets', 'maintenance'], true)) {
            $fields[] = ['name' => 'maintenance_status', 'label' => 'Maintenance Status', 'type' => 'select', 'options' => $options['maintenance_statuses']];
        }

        if (in_array($pageType, ['analytics', 'bookings', 'maintenance'], true)) {
            $fields[] = ['name' => 'trend', 'label' => 'Trend', 'type' => 'select', 'options' => [
                ['value' => 'day', 'label' => 'Daily'],
                ['value' => 'week', 'label' => 'Weekly'],
                ['value' => 'month', 'label' => 'Monthly'],
            ]];
        }

        return $fields;
    }

    private function appliedFilters(array $filters, array $options, string $pageType): array
    {
        $items = [];

        if ($filters['date_from'] || $filters['date_to']) {
            $items[] = [
                'label' => 'Date Range',
                'value' => trim(($filters['date_from'] ?? 'Any') . ' to ' . ($filters['date_to'] ?? 'Any')),
            ];
        }
        if ($filters['lab_id']) {
            $items[] = ['label' => 'Laboratory', 'value' => $this->optionLabel($options['labs'], (string) $filters['lab_id'])];
        }
        if ($filters['faculty_id']) {
            $items[] = ['label' => 'Faculty', 'value' => $this->optionLabel($options['faculties'], (string) $filters['faculty_id'])];
        }
        if ($filters['booking_status']) {
            $items[] = ['label' => 'Booking Status', 'value' => $this->optionLabel($options['booking_statuses'], (string) $filters['booking_status'])];
        }
        if ($filters['user_role']) {
            $items[] = ['label' => 'User Role', 'value' => $this->optionLabel($options['user_roles'], (string) $filters['user_role'])];
        }
        if ($filters['user_id']) {
            $items[] = ['label' => 'User', 'value' => $this->optionLabel($options['users'], (string) $filters['user_id'])];
        }
        if ($filters['asset_category']) {
            $items[] = ['label' => 'Asset Category', 'value' => (string) $filters['asset_category']];
        }
        if ($filters['asset_status']) {
            $items[] = ['label' => 'Asset Status', 'value' => $this->optionLabel($options['asset_statuses'], (string) $filters['asset_status'])];
        }
        if ($filters['maintenance_status']) {
            $items[] = ['label' => 'Maintenance Status', 'value' => $this->optionLabel($options['maintenance_statuses'], (string) $filters['maintenance_status'])];
        }
        if (in_array($pageType, ['analytics', 'bookings', 'maintenance'], true)) {
            $trendLabel = match ($filters['trend']) {
                'day' => 'Daily',
                'month' => 'Monthly',
                default => 'Weekly',
            };
            $items[] = ['label' => 'Trend View', 'value' => $trendLabel];
        }

        return $items === [] ? [['label' => 'Filters', 'value' => 'No filters applied']] : $items;
    }

    private function exportUrl(array $context, string $type, string $format, array $filters): string
    {
        $base = $type === 'summary'
            ? $context['routeBase'] . '/reports/' . $format
            : $context['routeBase'] . '/reports/export/' . $format . '/' . $type;

        $query = $this->queryString($filters);
        if ($type === 'summary') {
            $query = ($query !== '' ? $query . '&' : '') . 'type=summary';
        }

        return $base . ($query !== '' ? '?' . $query : '');
    }

    private function doughnutChart(string $id, string $title, array $labels, array $values, array $colors): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'type' => 'doughnut',
            'height' => 280,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $title,
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ]],
            ],
        ];
    }

    private function barChart(string $id, string $title, array $labels, array $values, string $datasetLabel, string $color): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'type' => 'bar',
            'height' => 300,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $datasetLabel,
                    'data' => $values,
                    'backgroundColor' => $color,
                    'borderRadius' => 8,
                ]],
            ],
        ];
    }

    private function lineChart(string $id, string $title, array $labels, array $values, string $datasetLabel): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'type' => 'line',
            'height' => 300,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $datasetLabel,
                    'data' => $values,
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.14)',
                    'tension' => 0.3,
                    'fill' => true,
                ]],
            ],
        ];
    }

    private function mapOptions(array $rows, string $valueKey, callable $labelResolver): array
    {
        return array_map(static function (array $row) use ($valueKey, $labelResolver): array {
            return [
                'value' => (string) ($row[$valueKey] ?? ''),
                'label' => $labelResolver($row),
            ];
        }, $rows);
    }

    private function optionLabel(array $options, string $value): string
    {
        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return (string) ($option['label'] ?? $value);
            }
        }

        return $value;
    }

    private function validOptionId(string $value, array $options): ?int
    {
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return (int) $value;
            }
        }

        return null;
    }

    private function validOptionValue(string $value, array $options): ?string
    {
        if ($value === '') {
            return null;
        }

        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return $value;
            }
        }

        return null;
    }

    private function validTrend(string $value): string
    {
        return in_array($value, ['day', 'week', 'month'], true) ? $value : 'week';
    }

    private function validDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $parts = date_parse($value);
        if (($parts['error_count'] ?? 0) > 0 || ($parts['warning_count'] ?? 0) > 0) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }

    private function resolveRoleUserIds(string $role): array
    {
        return array_map(
            static fn(array $row): int => (int) $row['user_id'],
            db_connect()->table('auth_groups_users')
                ->select('user_id')
                ->where('group', $role)
                ->get()
                ->getResultArray()
        );
    }

    private function normalizeReportType(string $type): ?string
    {
        return array_key_exists($type, self::REPORT_TYPES) ? $type : null;
    }

    private function queryString(array $filters): string
    {
        $query = [];
        foreach (['date_from', 'date_to', 'lab_id', 'booking_status', 'user_id', 'user_role', 'faculty_id', 'asset_category', 'asset_status', 'maintenance_status', 'trend'] as $key) {
            if (! array_key_exists($key, $filters) || $filters[$key] === null || $filters[$key] === '') {
                continue;
            }
            $query[$key] = $filters[$key];
        }

        return http_build_query($query);
    }

    private function currentUserEmail(int $userId): string
    {
        $row = db_connect()->table('auth_identities')
            ->select('secret')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();

        return strtolower(trim((string) ($row['secret'] ?? '')));
    }

    private function formatDateTime(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('d M Y H:i', $timestamp) : '-';
    }

    private function formatDate(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('d M Y', $timestamp) : '-';
    }
}
