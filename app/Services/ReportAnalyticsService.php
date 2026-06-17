<?php

namespace App\Services;

use App\Libraries\UserRoleResolver;
use App\Models\AnalyticsReportModel;
use App\Models\BookingModel;
use App\Models\MaintenanceRecordModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Shield\Entities\User;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class ReportAnalyticsService
{
    protected ConnectionInterface $db;
    protected AnalyticsReportModel $model;
    protected UserRoleResolver $roleResolver;
    protected MaintenanceRecordModel $maintenanceModel;

    public function __construct(?ConnectionInterface $db = null, ?AnalyticsReportModel $model = null)
    {
        $this->db = $db ?? db_connect();
        $this->model = $model ?? new AnalyticsReportModel();
        $this->roleResolver = new UserRoleResolver();
        $this->maintenanceModel = new MaintenanceRecordModel();
    }

    public function build(User $user, array $rawFilters = []): array
    {
        $scope = $this->resolveScope($user);
        $filters = $this->normalizeFilters($rawFilters, $scope);

        $availableLabs = $this->model->availableLaboratories($scope);
        $availableAssets = $this->model->availableAssets($scope, $filters['lab_id'] !== '' ? (int) $filters['lab_id'] : null);
        $assetCategories = $this->model->availableAssetCategories($scope);
        $assetStatuses = $this->availableAssetStatuses($scope, $filters);
        $bookingStatusSummary = $this->model->bookingStatusSummary($filters, $scope);
        $bookingMonthlyTrend = $this->normalizeTrendRows($this->model->bookingTrend($filters, $scope, 'month'));
        $bookingRows = $this->model->bookingReportRows($filters, $scope);
        $recentBookingRows = array_slice(array_map(function (array $row): array {
            return [
                'laboratory' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
                'date' => (string) ($row['date'] ?? 'N/A'),
                'time' => $this->timeRangeLabel($row['start_time'] ?? null, $row['end_time'] ?? null),
                'applicant' => (string) ($row['requested_by'] ?? 'Unknown User'),
                'applicant_type' => strtoupper((string) ($row['user_type'] ?? 'UTHM')),
                'status' => (string) ($row['status'] ?? 'UNKNOWN'),
                'approval_stage' => (string) ($row['approval_status'] ?? 'Pending'),
            ];
        }, $bookingRows), 0, 12);

        $labUsageRows = $this->model->laboratoryUsageRows($filters, $scope);
        usort($labUsageRows, static fn(array $a, array $b): int => ($b['total_bookings'] <=> $a['total_bookings']) ?: ($b['usage_percentage'] <=> $a['usage_percentage']));
        $maintenanceStatusSummary = $this->model->maintenanceStatusSummary($filters, $scope);
        $maintenanceTrend = $this->normalizeTrendRows($this->model->maintenanceTrend($filters, $scope, 'month'));
        $maintenanceRows = $this->model->maintenanceReportRows($filters, $scope);
        $assetRows = $this->model->assetReportRows($filters, $scope);
        $mostUsedAssets = $this->normalizeAssetDemandRows($this->model->mostUsedAssets($filters, $scope, 10));
        $leastUsedAssets = $this->leastUsedAssets($filters, $scope, 10);
        $frequentMaintenanceAssets = $this->frequentMaintenanceAssets($filters, $scope, 10);
        $recentMaintenanceRows = array_slice(array_map(function (array $row): array {
            return [
                'case' => (string) ($row['title'] ?? ('Maintenance #' . ($row['id'] ?? ''))),
                'asset' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
                'laboratory' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
                'status' => $this->maintenanceStatusLabel((string) ($row['status'] ?? 'unknown')),
                'priority' => $this->titleize((string) ($row['priority'] ?? 'medium')),
                'reported_at' => $this->displayDateTime($row['created_at'] ?? null),
            ];
        }, $maintenanceRows), 0, 12);

        $assetStatusRows = $this->assetStatusRows($filters, $scope);
        $assetsByLabRows = $this->assetsByLaboratoryRows($filters, $scope);
        $assetsByCategoryRows = $this->assetsByCategoryRows($filters, $scope);
        $assetAvailabilityRows = $this->assetAvailabilityRows($assetRows, $assetStatusRows);
        $currentMaintenanceAssets = $this->currentMaintenanceAssets($filters, $scope);

        $bookingRoleRows = $this->bookingRoleRows($filters, $scope);
        $bookingApplicantTypeRows = $this->bookingApplicantTypeRows($filters, $scope);
        $bookingPeakDayRows = $this->bookingPeakDayRows($filters, $scope);
        $peakHours = $this->bookingPeakHourRows($filters, $scope);
        $bookingDuration = $this->bookingDurationMetrics($filters, $scope);
        $externalBookingStats = $this->externalBookingMetrics($filters, $scope);

        $labMaintenanceRows = $this->maintenanceByLaboratoryRows($filters, $scope);
        $labComparisonRows = $this->laboratoryComparisonRows($labUsageRows, $assetsByLabRows, $labMaintenanceRows);

        $maintenancePriorityRows = $this->maintenancePriorityRows($filters, $scope);
        $maintenanceByAssetRows = $this->maintenanceByAssetRows($filters, $scope);
        $maintenanceReporterRows = $this->maintenanceReporterRows($filters, $scope);
        $maintenancePicWorkloadRows = $this->maintenancePicWorkloadRows($filters, $scope);
        $maintenanceResolutionMetrics = $this->maintenanceResolutionMetrics($filters, $scope);
        $repeatedMaintenanceRows = $this->repeatedMaintenanceRows($filters, $scope);

        $notificationRows = $this->notificationAnalytics($filters, $scope);
        $userAnalytics = $scope['role'] === 'admin' ? $this->userAnalytics() : null;

        $totalBookings = array_sum($bookingStatusSummary);
        $totalAssets = array_sum(array_map(static fn(array $row): int => (int) ($row['asset_count'] ?? 0), $assetStatusRows));
        $totalNotifications = (int) ($notificationRows['totals']['total_notifications'] ?? 0);
        $assetAvailabilityRate = (float) ($assetAvailabilityRows['availability_rate'] ?? 0.0);
        $pendingBookings = (int) ($bookingStatusSummary['PENDING'] ?? 0);
        $approvedBookings = (int) ($bookingStatusSummary['APPROVED'] ?? 0);
        $rejectedBookings = (int) ($bookingStatusSummary['REJECTED'] ?? 0);
        $cancelledBookings = (int) ($bookingStatusSummary['CANCELLED'] ?? 0);
        $approvalRate = $totalBookings > 0 ? round(($approvedBookings / $totalBookings) * 100, 1) : 0.0;
        $rejectionRate = $totalBookings > 0 ? round(($rejectedBookings / $totalBookings) * 100, 1) : 0.0;
        $cancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0.0;
        $openMaintenance = (int) ($maintenanceStatusSummary['reported'] ?? 0)
            + (int) ($maintenanceStatusSummary['scheduled'] ?? 0)
            + (int) ($maintenanceStatusSummary['in_progress'] ?? 0)
            + (int) ($maintenanceStatusSummary['testing'] ?? 0);

        $kpis = [
            'total_bookings' => $totalBookings,
            'approved' => $approvedBookings,
            'pending' => $pendingBookings,
            'rejected' => $rejectedBookings,
            'cancelled' => $cancelledBookings,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
            'cancellation_rate' => $cancellationRate,
            'total_labs' => count($availableLabs),
            'total_assets' => $totalAssets,
            'asset_availability_rate' => $assetAvailabilityRate,
            'maintenance_total' => array_sum($maintenanceStatusSummary),
            'maintenance_open' => $openMaintenance,
            'maintenance_completed' => (int) ($maintenanceStatusSummary['completed'] ?? 0),
            'notifications_total' => $totalNotifications,
            'users' => $scope['role'] === 'admin' ? (int) ($userAnalytics['kpis']['total_users'] ?? 0) : null,
        ];

        $summaryCards = [
            ['label' => 'Total Bookings', 'value' => $kpis['total_bookings'], 'tone' => 'primary'],
            ['label' => 'Approval Rate (%)', 'value' => $kpis['approval_rate'], 'tone' => 'success'],
            ['label' => 'Pending Bookings', 'value' => $kpis['pending'], 'tone' => 'warning'],
            ['label' => 'Rejected Bookings', 'value' => $kpis['rejected'], 'tone' => 'danger'],
            ['label' => 'Laboratories In Scope', 'value' => $kpis['total_labs'], 'tone' => 'info'],
            ['label' => 'Assets In Scope', 'value' => $kpis['total_assets'], 'tone' => 'primary'],
            ['label' => 'Asset Availability (%)', 'value' => $kpis['asset_availability_rate'], 'tone' => 'success'],
            ['label' => 'Open Maintenance', 'value' => $kpis['maintenance_open'], 'tone' => 'warning'],
            ['label' => 'Completed Maintenance', 'value' => $kpis['maintenance_completed'], 'tone' => 'success'],
            ['label' => 'Notifications', 'value' => $kpis['notifications_total'], 'tone' => 'info'],
        ];

        if ($scope['role'] === 'admin' && $kpis['users'] !== null) {
            $summaryCards[] = ['label' => 'Total Users', 'value' => $kpis['users'], 'tone' => 'info'];
        }

        $summaryCards = $this->tailorSummaryCards($scope['role'], $summaryCards);
        $appliedFilters = $this->appliedFilters($filters, $availableLabs, $availableAssets);
        $scopeLaboratories = array_map(static function (array $lab): array {
            return [
                'id' => (int) ($lab['id'] ?? 0),
                'name' => trim((string) ($lab['name'] ?? '')),
                'room' => trim((string) ($lab['room'] ?? '')),
            ];
        }, $availableLabs);

        $sectionGroups = [
            [
                'id' => 'booking',
                'title' => 'Booking Analytics',
                'description' => 'Booking statistics and demand indicators within the authorized reporting scope.',
                'tables' => [
                    $this->metricTable('Booking KPI Summary', [
                        ['metric' => 'Total bookings', 'value' => $totalBookings],
                        ['metric' => 'Approved bookings', 'value' => $approvedBookings],
                        ['metric' => 'Pending bookings', 'value' => $pendingBookings],
                        ['metric' => 'Rejected bookings', 'value' => $rejectedBookings],
                        ['metric' => 'Cancelled bookings', 'value' => $cancelledBookings],
                        ['metric' => 'Approval rate (%)', 'value' => $approvalRate],
                        ['metric' => 'Rejection rate (%)', 'value' => $rejectionRate],
                        ['metric' => 'Cancellation rate (%)', 'value' => $cancellationRate],
                        ['metric' => 'Average booking duration (minutes)', 'value' => $bookingDuration['average_minutes']],
                        ['metric' => 'Average booking duration (hours)', 'value' => $bookingDuration['average_hours']],
                        ['metric' => 'Peak booking day', 'value' => $bookingPeakDayRows[0]['day'] ?? 'No data available'],
                        ['metric' => 'Peak booking slot', 'value' => $peakHours[0]['time_slot'] ?? 'No data available'],
                    ], true),
                    $this->standardTable('Booking Status Breakdown', [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'total', 'label' => 'Total'],
                        ['key' => 'percentage', 'label' => 'Percentage (%)'],
                    ], $this->statusPercentageRows($bookingStatusSummary), false, 'No booking records were found for the selected scope.'),
                    $this->standardTable('Booking Trend By Month', [
                        ['key' => 'period', 'label' => 'Month'],
                        ['key' => 'total', 'label' => 'Total Bookings'],
                    ], array_map(static fn(array $row): array => ['period' => (string) ($row['display_label'] ?? $row['label'] ?? '-'), 'total' => (int) ($row['total'] ?? 0)], $bookingMonthlyTrend), false, 'No monthly booking trend data is available.'),
                    $this->standardTable('Booking Trend By Laboratory', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'total_bookings', 'label' => 'Bookings'],
                        ['key' => 'usage_percentage', 'label' => 'Utilization (%)'],
                        ['key' => 'peak_usage_day', 'label' => 'Peak Day'],
                        ['key' => 'peak_usage_time', 'label' => 'Peak Time Slot'],
                    ], array_map(static fn(array $row): array => [
                        'laboratory' => (string) ($row['laboratory_name'] ?? '-'),
                        'total_bookings' => (int) ($row['total_bookings'] ?? 0),
                        'usage_percentage' => (float) ($row['usage_percentage'] ?? 0),
                        'peak_usage_day' => (string) ($row['peak_usage_day'] ?? 'N/A'),
                        'peak_usage_time' => (string) ($row['peak_usage_time'] ?? 'N/A'),
                    ], $labUsageRows), true, 'No laboratory booking trend data is available.'),
                    $this->standardTable('Booking Trend By Applicant Role', [
                        ['key' => 'role', 'label' => 'Applicant Role'],
                        ['key' => 'total', 'label' => 'Total Bookings'],
                    ], $bookingRoleRows, false, 'Applicant role statistics are not available.'),
                    $this->standardTable('Booking Trend By Applicant Type', [
                        ['key' => 'applicant_type', 'label' => 'Applicant Type'],
                        ['key' => 'total', 'label' => 'Total Bookings'],
                    ], $bookingApplicantTypeRows, false, 'Applicant type statistics are not available.'),
                    $this->standardTable('Peak Booking Days', [
                        ['key' => 'day', 'label' => 'Day'],
                        ['key' => 'total', 'label' => 'Total Bookings'],
                    ], $bookingPeakDayRows, false, 'Peak booking day statistics are not available.'),
                    $this->standardTable('Peak Booking Time Slots', [
                        ['key' => 'time_slot', 'label' => 'Time Slot'],
                        ['key' => 'total', 'label' => 'Total Bookings'],
                    ], $peakHours, false, 'Peak booking time slot statistics are not available.'),
                    $this->standardTable('Most Frequently Booked Laboratories', [
                        ['key' => 'lab_name', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Bookings'],
                    ], array_map(static fn(array $row): array => ['lab_name' => (string) ($row['laboratory_name'] ?? '-'), 'total' => (int) ($row['total_bookings'] ?? 0)], array_slice($labUsageRows, 0, 10)), false, 'No laboratory booking demand records are available.'),
                    $this->standardTable('Most Frequently Booked Assets', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'total_used', 'label' => 'Booked Quantity'],
                    ], $mostUsedAssets, false, 'No asset booking demand data is available.'),
                    $this->standardTable('Least Used Assets', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'total_used', 'label' => 'Booked Quantity'],
                    ], $leastUsedAssets, false, 'No low-usage asset data is available.'),
                    $this->standardTable('External Booking Statistics', [
                        ['key' => 'metric', 'label' => 'Metric'],
                        ['key' => 'value', 'label' => 'Value'],
                    ], [
                        ['metric' => 'External booking count', 'value' => $externalBookingStats['count']],
                        ['metric' => 'External booking rate (%)', 'value' => $externalBookingStats['rate']],
                    ], false, 'External booking data is not available.'),
                    $this->standardTable('Recent Booking Activity Summary', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'time', 'label' => 'Time'],
                        ['key' => 'applicant', 'label' => 'Applicant'],
                        ['key' => 'applicant_type', 'label' => 'Applicant Type'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'approval_stage', 'label' => 'Approval Stage'],
                    ], $recentBookingRows, true, 'No recent booking activity is available.'),
                ],
            ],
            [
                'id' => 'laboratory',
                'title' => 'Laboratory Analytics',
                'description' => 'Laboratory performance, utilization, and comparison statistics within scope.',
                'tables' => [
                    $this->metricTable('Laboratory KPI Summary', [
                        ['metric' => 'Laboratories within scope', 'value' => count($availableLabs)],
                        ['metric' => 'Most demanded laboratory', 'value' => $labComparisonRows[0]['laboratory'] ?? 'No data available'],
                        ['metric' => 'Highest maintenance activity', 'value' => $this->firstLabel($labMaintenanceRows, 'laboratory')],
                        ['metric' => 'Lowest usage laboratory', 'value' => $this->firstLabel(array_reverse($labComparisonRows), 'laboratory')],
                    ], false),
                    $this->standardTable('Laboratory Comparison Table', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'room', 'label' => 'Room'],
                        ['key' => 'asset_count', 'label' => 'Assets'],
                        ['key' => 'booking_count', 'label' => 'Bookings'],
                        ['key' => 'used_hours', 'label' => 'Used Hours'],
                        ['key' => 'utilization_percentage', 'label' => 'Utilization (%)'],
                        ['key' => 'maintenance_count', 'label' => 'Maintenance Cases'],
                        ['key' => 'peak_day', 'label' => 'Peak Day'],
                    ], $labComparisonRows, true, 'No laboratory comparison data is available.'),
                    $this->standardTable('Laboratories With Highest Booking Demand', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'booking_count', 'label' => 'Bookings'],
                    ], array_slice($labComparisonRows, 0, 10), false, 'No booking demand rankings are available.'),
                    $this->standardTable('Laboratories With Highest Maintenance Activity', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'maintenance_count', 'label' => 'Maintenance Cases'],
                    ], $labMaintenanceRows, false, 'No maintenance activity rankings are available.'),
                    $this->standardTable('Laboratories With Low Usage', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'utilization_percentage', 'label' => 'Utilization (%)'],
                        ['key' => 'booking_count', 'label' => 'Bookings'],
                    ], array_slice(array_reverse($labComparisonRows), 0, 10), false, 'No low-usage laboratory data is available.'),
                ],
            ],
            [
                'id' => 'asset',
                'title' => 'Asset Analytics',
                'description' => 'Asset inventory, utilization, availability, and maintenance demand statistics.',
                'tables' => [
                    $this->metricTable('Asset KPI Summary', [
                        ['metric' => 'Assets within scope', 'value' => $totalAssets],
                        ['metric' => 'Available units', 'value' => $assetAvailabilityRows['available_units']],
                        ['metric' => 'Units under maintenance', 'value' => $assetAvailabilityRows['maintenance_units']],
                        ['metric' => 'Total managed units', 'value' => $assetAvailabilityRows['total_units']],
                        ['metric' => 'Asset availability (%)', 'value' => $assetAvailabilityRate],
                    ], false),
                    $this->standardTable('Asset Status Breakdown', [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'asset_count', 'label' => 'Assets'],
                        ['key' => 'total_units', 'label' => 'Units'],
                        ['key' => 'available_units', 'label' => 'Available Units'],
                    ], $assetStatusRows, false, 'No asset status data is available.'),
                    $this->standardTable('Assets By Laboratory', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'asset_count', 'label' => 'Assets'],
                        ['key' => 'total_units', 'label' => 'Units'],
                    ], $assetsByLabRows, false, 'No laboratory asset data is available.'),
                    $this->standardTable('Assets By Category', [
                        ['key' => 'category', 'label' => 'Category'],
                        ['key' => 'asset_count', 'label' => 'Assets'],
                        ['key' => 'total_units', 'label' => 'Units'],
                    ], $assetsByCategoryRows, false, 'No asset category data is available.'),
                    $this->standardTable('Most Used Assets', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'total_used', 'label' => 'Booked Quantity'],
                    ], $mostUsedAssets, false, 'No asset usage data is available.'),
                    $this->standardTable('Least Used Assets', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'total_used', 'label' => 'Booked Quantity'],
                    ], $leastUsedAssets, false, 'No least-used asset data is available.'),
                    $this->standardTable('Assets With Highest Maintenance Frequency', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Maintenance Cases'],
                    ], $frequentMaintenanceAssets, false, 'No asset maintenance ranking data is available.'),
                    $this->standardTable('Assets Currently Under Maintenance', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'affected_units', 'label' => 'Affected Units'],
                    ], $currentMaintenanceAssets, true, 'No assets are currently under maintenance.'),
                ],
            ],
            [
                'id' => 'maintenance',
                'title' => 'Maintenance Analytics',
                'description' => 'Maintenance workload, status, priority, and resolution statistics.',
                'tables' => [
                    $this->metricTable('Maintenance KPI Summary', [
                        ['metric' => 'Total maintenance records', 'value' => array_sum($maintenanceStatusSummary)],
                        ['metric' => 'Pending maintenance', 'value' => (int) ($maintenanceStatusSummary['reported'] ?? 0)],
                        ['metric' => 'Scheduled maintenance', 'value' => (int) ($maintenanceStatusSummary['scheduled'] ?? 0)],
                        ['metric' => 'In-progress maintenance', 'value' => (int) ($maintenanceStatusSummary['in_progress'] ?? 0)],
                        ['metric' => 'Testing and verification', 'value' => (int) ($maintenanceStatusSummary['testing'] ?? 0)],
                        ['metric' => 'Completed maintenance', 'value' => (int) ($maintenanceStatusSummary['completed'] ?? 0)],
                        ['metric' => 'Average resolution time (hours)', 'value' => $maintenanceResolutionMetrics['average_resolution_hours']],
                        ['metric' => 'Average resolution time (days)', 'value' => $maintenanceResolutionMetrics['average_resolution_days']],
                    ], true),
                    $this->standardTable('Maintenance Status Breakdown', [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'total', 'label' => 'Total'],
                        ['key' => 'percentage', 'label' => 'Percentage (%)'],
                    ], $this->statusPercentageRows($maintenanceStatusSummary, true), false, 'No maintenance records were found for the selected scope.'),
                    $this->standardTable('Maintenance Trend By Month', [
                        ['key' => 'period', 'label' => 'Month'],
                        ['key' => 'total', 'label' => 'Total Records'],
                    ], array_map(static fn(array $row): array => ['period' => (string) ($row['display_label'] ?? $row['label'] ?? '-'), 'total' => (int) ($row['total'] ?? 0)], $maintenanceTrend), false, 'No maintenance trend data is available.'),
                    $this->standardTable('Maintenance By Laboratory', [
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Maintenance Cases'],
                        ['key' => 'open_cases', 'label' => 'Open Cases'],
                    ], $labMaintenanceRows, false, 'No laboratory maintenance data is available.'),
                    $this->standardTable('Maintenance By Asset', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Maintenance Cases'],
                    ], $maintenanceByAssetRows, true, 'No asset-level maintenance data is available.'),
                    $this->standardTable('Maintenance Priority Breakdown', [
                        ['key' => 'priority', 'label' => 'Priority'],
                        ['key' => 'total', 'label' => 'Total Records'],
                    ], $maintenancePriorityRows, false, 'No maintenance priority data is available.'),
                    $this->standardTable('Maintenance Reporter Statistics', [
                        ['key' => 'reported_by', 'label' => 'Reporter'],
                        ['key' => 'total', 'label' => 'Reported Cases'],
                    ], $maintenanceReporterRows, false, 'No maintenance reporter statistics are available.'),
                    $this->standardTable('Responsible PIC Maintenance Workload', [
                        ['key' => 'pic_name', 'label' => 'PIC'],
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Maintenance Cases'],
                        ['key' => 'open_cases', 'label' => 'Open Cases'],
                    ], $maintenancePicWorkloadRows, true, 'PIC workload statistics are not available.'),
                    $this->standardTable('Assets With Repeated Maintenance Issues', [
                        ['key' => 'asset_name', 'label' => 'Asset'],
                        ['key' => 'laboratory_name', 'label' => 'Laboratory'],
                        ['key' => 'total', 'label' => 'Repeated Cases'],
                    ], $repeatedMaintenanceRows, false, 'No repeated maintenance issue patterns were found.'),
                    $this->standardTable('Recent Maintenance Activity Summary', [
                        ['key' => 'case', 'label' => 'Case'],
                        ['key' => 'asset', 'label' => 'Asset'],
                        ['key' => 'laboratory', 'label' => 'Laboratory'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'priority', 'label' => 'Priority'],
                        ['key' => 'reported_at', 'label' => 'Reported At'],
                    ], $recentMaintenanceRows, true, 'No recent maintenance activity is available.'),
                ],
            ],
            [
                'id' => 'notification',
                'title' => 'Notification Analytics',
                'description' => 'Notification volume, type, scope, and read-status statistics.',
                'tables' => [
                    $this->metricTable('Notification KPI Summary', [
                        ['metric' => 'Total notifications', 'value' => $notificationRows['totals']['total_notifications']],
                        ['metric' => 'Unread notifications', 'value' => $notificationRows['totals']['unread_notifications']],
                        ['metric' => 'Read notifications', 'value' => $notificationRows['totals']['read_notifications']],
                    ], false),
                    $this->standardTable('Notification Type Breakdown', [
                        ['key' => 'type', 'label' => 'Type'],
                        ['key' => 'total', 'label' => 'Total Notifications'],
                    ], $notificationRows['type_rows'], false, 'No notification type statistics are available.'),
                    $this->standardTable('Notification Entity Scope Breakdown', [
                        ['key' => 'entity_type', 'label' => 'Entity Type'],
                        ['key' => 'total', 'label' => 'Total Notifications'],
                    ], $notificationRows['entity_rows'], false, 'No entity-scoped notification statistics are available.'),
                    $this->standardTable('Notification Read Status Breakdown', [
                        ['key' => 'status', 'label' => 'Read Status'],
                        ['key' => 'total', 'label' => 'Total Notifications'],
                    ], $notificationRows['read_rows'], false, 'No notification read-status statistics are available.'),
                    $this->standardTable('Notification Trend By Month', [
                        ['key' => 'period', 'label' => 'Month'],
                        ['key' => 'total', 'label' => 'Total Notifications'],
                    ], $notificationRows['trend_rows'], false, 'No notification trend data is available.'),
                    $this->standardTable('Notification Recipient Role Breakdown', [
                        ['key' => 'role', 'label' => 'Recipient Role'],
                        ['key' => 'total', 'label' => 'Total Notifications'],
                    ], $notificationRows['role_rows'], false, 'Notification role statistics are not available.'),
                ],
            ],
        ];

        if ($userAnalytics !== null) {
            $sectionGroups[] = [
                'id' => 'users',
                'title' => 'User And Access Analytics',
                'description' => 'System-wide user distribution and account activity statistics.',
                'tables' => [
                    $this->metricTable('User KPI Summary', [
                        ['metric' => 'Total users', 'value' => $userAnalytics['kpis']['total_users']],
                        ['metric' => 'External users', 'value' => $userAnalytics['kpis']['external_users']],
                        ['metric' => 'Active users', 'value' => $userAnalytics['kpis']['active_users']],
                        ['metric' => 'Inactive users', 'value' => $userAnalytics['kpis']['inactive_users']],
                    ], false),
                    $this->standardTable('Users By Role', [
                        ['key' => 'role', 'label' => 'Role'],
                        ['key' => 'total', 'label' => 'Total Users'],
                    ], $userAnalytics['role_rows'], false, 'User role statistics are not available.'),
                    $this->standardTable('User Status Breakdown', [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'total', 'label' => 'Total Users'],
                    ], $userAnalytics['status_rows'], false, 'User status statistics are not available.'),
                    $this->standardTable('Account Registration Trend', [
                        ['key' => 'period', 'label' => 'Month'],
                        ['key' => 'total', 'label' => 'Registered Accounts'],
                    ], $userAnalytics['registration_rows'], false, 'Account registration trend data is not available.'),
                ],
            ];
        }

        $sectionGroups = $this->tailorSectionGroups($scope['role'], $sectionGroups);
        $uiProfile = $this->uiProfile($scope['role'], $kpis, $scopeLaboratories, $labComparisonRows, $frequentMaintenanceAssets);

        $report = [
            'role' => $scope['role'],
            'roleDisplay' => $scope['role_display'],
            'reportTitle' => $scope['report_title'],
            'scopeLabel' => $scope['scope_label'],
            'scopeDescription' => $scope['scope_description'],
            'uiProfile' => $uiProfile,
            'generatedAt' => date('Y-m-d H:i:s'),
            'generatedAtDisplay' => date('d M Y, h:i A'),
            'filters' => $filters,
            'appliedFilters' => $appliedFilters,
            'scopeLaboratories' => $scopeLaboratories,
            'kpis' => $kpis,
            'summaryCards' => $summaryCards,
            'assetTotals' => $this->assetTotalsMap($assetStatusRows),
            'statusMap' => $bookingStatusSummary,
            'monthlyTrend' => array_map(static fn(array $row): array => [
                'month' => (string) ($row['display_label'] ?? $row['label'] ?? '-'),
                'total' => (int) ($row['total'] ?? 0),
            ], $bookingMonthlyTrend),
            'topLabs' => array_map(static fn(array $row): array => [
                'lab_name' => (string) ($row['laboratory_name'] ?? '-'),
                'total' => (int) ($row['total_bookings'] ?? 0),
            ], array_slice($labUsageRows, 0, 10)),
            'facultyCounts' => $this->facultyCountsRows($filters, $scope),
            'labs' => $availableLabs,
            'maintenanceStatus' => $maintenanceStatusSummary,
            'maintenanceTrend' => array_map(static fn(array $row): array => [
                'month' => (string) ($row['display_label'] ?? $row['label'] ?? '-'),
                'total' => (int) ($row['total'] ?? 0),
            ], $maintenanceTrend),
            'topMaintenanceAssets' => array_map(static fn(array $row): array => [
                'asset_name' => (string) ($row['asset_name'] ?? '-'),
                'total' => (int) ($row['total'] ?? 0),
            ], $frequentMaintenanceAssets),
            'upcomingBookings' => $this->upcomingBookingRows($filters, $scope),
            'labUtilization' => array_map(static fn(array $row): array => [
                'laboratory_name' => (string) ($row['laboratory_name'] ?? '-'),
                'laboratory_room' => (string) ($row['laboratory_room'] ?? ''),
                'total_bookings' => (int) ($row['total_bookings'] ?? 0),
                'total_used_hours' => (float) ($row['total_used_hours'] ?? 0),
                'usage_percentage' => (float) ($row['usage_percentage'] ?? 0),
                'peak_usage_day' => (string) ($row['peak_usage_day'] ?? 'N/A'),
                'peak_usage_time' => (string) ($row['peak_usage_time'] ?? 'N/A'),
            ], $labUsageRows),
            'peakHours' => $peakHours,
            'charts' => $this->chartsFromData($bookingMonthlyTrend, $peakHours, $labComparisonRows, $maintenanceTrend),
            'sectionGroups' => $sectionGroups,
            'availableFilters' => [
                'labs' => $this->optionRows($availableLabs, 'id', static fn(array $row): string => trim((string) ($row['name'] ?? '')) . (($row['room'] ?? '') !== '' ? ' (' . $row['room'] . ')' : '')),
                'assets' => $this->optionRows($availableAssets, 'id', static fn(array $row): string => trim((string) ($row['asset_code'] ?? '')) !== '' ? trim((string) $row['asset_code']) . ' - ' . trim((string) ($row['name'] ?? '')) : trim((string) ($row['name'] ?? ''))),
                'booking_statuses' => array_map(static fn(string $status): array => ['value' => $status, 'label' => ucfirst(strtolower($status))], BookingModel::CORE_STATUSES),
                'maintenance_statuses' => array_map(fn(string $status): array => ['value' => $status, 'label' => $this->maintenanceStatusLabel($status)], array_keys($this->maintenanceModel->workflowLabels())),
                'asset_categories' => array_map(static fn(string $category): array => ['value' => $category, 'label' => $category], $assetCategories),
                'asset_statuses' => array_map(static fn(array $row): array => ['value' => (string) ($row['status_key'] ?? ''), 'label' => (string) ($row['status'] ?? '')], $assetStatuses),
            ],
            'limitations' => $this->collectLimitations(),
        ];

        return $report;
    }

    public function buildCsv(array $report): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, ['SLAMS System', $report['reportTitle'] ?? 'SLAMS Analytics Report']);
        fputcsv($handle, ['Role / Scope', $report['scopeLabel'] ?? 'Unknown Scope']);
        fputcsv($handle, ['Generated At', $report['generatedAtDisplay'] ?? ($report['generatedAt'] ?? '')]);
        fputcsv($handle, ['Selected Filters', $this->filterSummaryLine($report['appliedFilters'] ?? [])]);
        fputcsv($handle, []);

        fputcsv($handle, ['Summary KPI', 'Value']);
        foreach (($report['summaryCards'] ?? []) as $card) {
            fputcsv($handle, [(string) ($card['label'] ?? 'Metric'), (string) ($card['value'] ?? '0')]);
        }
        fputcsv($handle, []);

        foreach (($report['sectionGroups'] ?? []) as $group) {
            fputcsv($handle, [strtoupper((string) ($group['title'] ?? 'Section'))]);
            $description = trim((string) ($group['description'] ?? ''));
            if ($description !== '') {
                fputcsv($handle, [$description]);
            }

            foreach (($group['tables'] ?? []) as $table) {
                fputcsv($handle, []);
                fputcsv($handle, [(string) ($table['title'] ?? 'Table')]);
                $columns = array_map(static fn(array $column): string => (string) ($column['label'] ?? $column['key'] ?? 'Column'), $table['columns'] ?? []);
                if ($columns !== []) {
                    fputcsv($handle, $columns);
                }

                $rows = $table['rows'] ?? [];
                if ($rows === []) {
                    fputcsv($handle, [(string) ($table['emptyMessage'] ?? 'No data available.')]);
                    continue;
                }

                foreach ($rows as $row) {
                    $csvRow = [];
                    foreach (($table['columns'] ?? []) as $column) {
                        $csvRow[] = (string) ($row[$column['key']] ?? '');
                    }
                    fputcsv($handle, $csvRow);
                }
            }

            fputcsv($handle, []);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    public function pdfFilename(array $report): string
    {
        return sprintf(
            'slams_%s_analytics_report_%s.pdf',
            $this->roleSlug((string) ($report['role'] ?? 'report')),
            date('Ymd_His')
        );
    }

    public function csvFilename(array $report): string
    {
        return sprintf(
            'slams_%s_analytics_report_%s.csv',
            $this->roleSlug((string) ($report['role'] ?? 'report')),
            date('Ymd_His')
        );
    }

    private function resolveScope(User $user): array
    {
        $role = $this->roleResolver->approvalRole($user);
        if (! in_array($role, ['pic', 'manager', 'admin'], true)) {
            throw new RuntimeException('You do not have access to the analytics reports module.');
        }

        $email = strtolower(trim((string) ($this->db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->get()
            ->getRow('secret') ?? '')));

        $labIds = [];
        if ($role === 'pic') {
            $labIds = array_map(
                static fn(array $row): int => (int) ($row['id'] ?? 0),
                $this->db->table('laboratories')
                    ->select('id')
                    ->where('LOWER(TRIM(pic_email)) =', $email)
                    ->get()
                    ->getResultArray()
            );
        }

        return match ($role) {
            'pic' => [
                'role' => 'pic',
                'role_display' => 'PIC',
                'report_title' => 'PIC Laboratory Analytics Report',
                'scope_label' => 'PIC Scope (Assigned Laboratories)',
                'scope_description' => 'Analytics are restricted to laboratories and records assigned to the authenticated PIC account.',
                'labIds' => array_values(array_unique(array_filter($labIds))),
                'user_id' => (int) $user->id,
                'email' => $email,
            ],
            'manager' => [
                'role' => 'manager',
                'role_display' => 'Lab Manager',
                'report_title' => 'Lab Manager Laboratory Operations Analytics Report',
                'scope_label' => 'Lab Manager Scope (All Laboratories)',
                'scope_description' => 'Analytics cover laboratory operations across all laboratories without full system administration detail.',
                'labIds' => [],
                'user_id' => (int) $user->id,
                'email' => $email,
            ],
            default => [
                'role' => 'admin',
                'role_display' => 'Admin',
                'report_title' => 'Admin System-Wide Analytics Report',
                'scope_label' => 'Admin Scope (System-Wide)',
                'scope_description' => 'Analytics cover all laboratories, assets, bookings, maintenance, notifications, and user access statistics.',
                'labIds' => [],
                'user_id' => (int) $user->id,
                'email' => $email,
            ],
        };
    }

    private function normalizeFilters(array $rawFilters, array $scope): array
    {
        $filters = [
            'date_from' => $this->normalizeDate($rawFilters['date_from'] ?? ''),
            'date_to' => $this->normalizeDate($rawFilters['date_to'] ?? ''),
            'lab_id' => '',
            'asset_id' => '',
            'booking_status' => '',
            'maintenance_status' => '',
            'asset_category' => '',
            'asset_status' => '',
        ];

        if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        $allowedLabIds = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $this->model->availableLaboratories($scope));
        $requestedLabId = $this->normalizeId($rawFilters['lab_id'] ?? '');
        if ($requestedLabId !== null) {
            if (! in_array($requestedLabId, $allowedLabIds, true)) {
                throw new RuntimeException('The selected laboratory is outside your authorized reporting scope.');
            }
            $filters['lab_id'] = (string) $requestedLabId;
        }

        $requestedAssetId = $this->normalizeId($rawFilters['asset_id'] ?? '');
        if ($requestedAssetId !== null) {
            $allowedAssetIds = array_map(
                static fn(array $row): int => (int) ($row['id'] ?? 0),
                $this->model->availableAssets($scope, $filters['lab_id'] !== '' ? (int) $filters['lab_id'] : null)
            );
            if (! in_array($requestedAssetId, $allowedAssetIds, true)) {
                throw new RuntimeException('The selected asset is outside your authorized reporting scope.');
            }
            $filters['asset_id'] = (string) $requestedAssetId;
        }

        $bookingStatus = strtoupper(trim((string) ($rawFilters['booking_status'] ?? '')));
        if ($bookingStatus !== '') {
            if (! in_array($bookingStatus, BookingModel::CORE_STATUSES, true)) {
                throw new InvalidArgumentException('The selected booking status is invalid.');
            }
            $filters['booking_status'] = $bookingStatus;
        }

        $maintenanceStatuses = array_keys($this->maintenanceModel->workflowLabels());
        $maintenanceStatus = strtolower(trim((string) ($rawFilters['maintenance_status'] ?? '')));
        if ($maintenanceStatus !== '') {
            if (! in_array($maintenanceStatus, $maintenanceStatuses, true)) {
                throw new InvalidArgumentException('The selected maintenance status is invalid.');
            }
            $filters['maintenance_status'] = $maintenanceStatus;
        }

        $assetCategories = $this->model->availableAssetCategories($scope);
        $assetCategory = trim((string) ($rawFilters['asset_category'] ?? ''));
        if ($assetCategory !== '') {
            if (! in_array($assetCategory, $assetCategories, true)) {
                throw new InvalidArgumentException('The selected asset category is invalid.');
            }
            $filters['asset_category'] = $assetCategory;
        }

        $assetStatuses = array_map(static fn(array $row): string => (string) ($row['status_key'] ?? ''), $this->availableAssetStatuses($scope, $filters));
        $assetStatus = strtolower(trim((string) ($rawFilters['asset_status'] ?? '')));
        if ($assetStatus !== '') {
            if (! in_array($assetStatus, $assetStatuses, true)) {
                throw new InvalidArgumentException('The selected asset status is invalid.');
            }
            $filters['asset_status'] = $assetStatus;
        }

        return $filters;
    }

    private function normalizeDate(mixed $value): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return '';
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $trimmed);
        if (! $date || $date->format('Y-m-d') !== $trimmed) {
            throw new InvalidArgumentException('One or more report dates are invalid.');
        }

        return $trimmed;
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $normalized === false ? null : (int) $normalized;
    }

    private function statusPercentageRows(array $summary, bool $titleize = false): array
    {
        $total = max(array_sum($summary), 1);
        $rows = [];
        foreach ($summary as $status => $count) {
            $rows[] = [
                'status' => $titleize ? $this->maintenanceStatusLabel((string) $status) : strtoupper((string) $status),
                'total' => (int) $count,
                'percentage' => round(((int) $count / $total) * 100, 1),
            ];
        }

        return $rows;
    }

    private function bookingRoleRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select(
                "COALESCE(" . $this->roleLookupSql('b.user_id') . ", CASE WHEN UPPER(b.user_type) = 'EXTERNAL' THEN 'external' ELSE 'unknown' END) AS role_label, COUNT(DISTINCT b.id) AS total",
                false
            )
            ->groupBy('role_label')
            ->orderBy('total', 'DESC');

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        return array_map(fn(array $row): array => [
            'role' => $this->titleize((string) ($row['role_label'] ?? 'unknown')),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function bookingApplicantTypeRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select('UPPER(b.user_type) AS applicant_type, COUNT(DISTINCT b.id) AS total', false)
            ->groupBy('UPPER(b.user_type)')
            ->orderBy('total', 'DESC');

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'applicant_type' => (string) ($row['applicant_type'] ?? 'UNKNOWN'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function bookingPeakDayRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select("DAYNAME(b.date) AS day, COUNT(DISTINCT b.id) AS total", false)
            ->groupBy('DAYNAME(b.date)')
            ->orderBy('total', 'DESC');

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        $rows = $builder->get()->getResultArray();
        usort($rows, fn(array $a, array $b): int => ($b['total'] <=> $a['total']) ?: ($this->dayOrder((string) ($a['day'] ?? '')) <=> $this->dayOrder((string) ($b['day'] ?? ''))));

        return array_map(static fn(array $row): array => [
            'day' => (string) ($row['day'] ?? 'Unknown'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    private function bookingPeakHourRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select("
                CASE
                    WHEN b.start_time >= '08:00:00' AND b.start_time < '10:00:00' THEN '08:00-10:00'
                    WHEN b.start_time >= '10:00:00' AND b.start_time < '12:00:00' THEN '10:00-12:00'
                    WHEN b.start_time >= '13:00:00' AND b.start_time < '15:00:00' THEN '13:00-15:00'
                    WHEN b.start_time >= '15:00:00' AND b.start_time < '17:00:00' THEN '15:00-17:00'
                    ELSE 'Other'
                END AS time_slot,
                COUNT(DISTINCT b.id) AS total
            ", false)
            ->groupBy('time_slot')
            ->orderBy('total', 'DESC');

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        $rows = $builder->get()->getResultArray();
        usort($rows, fn(array $a, array $b): int => $this->slotOrder((string) ($a['time_slot'] ?? '')) <=> $this->slotOrder((string) ($b['time_slot'] ?? '')));

        return array_map(static fn(array $row): array => [
            'time_slot' => (string) ($row['time_slot'] ?? 'Other'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    private function bookingDurationMetrics(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select('AVG(TIMESTAMPDIFF(MINUTE, b.start_time, b.end_time)) AS avg_minutes', false);

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        $row = $builder->get()->getRowArray() ?? [];
        $avgMinutes = round((float) ($row['avg_minutes'] ?? 0), 1);

        return [
            'average_minutes' => $avgMinutes,
            'average_hours' => round($avgMinutes / 60, 2),
        ];
    }

    private function externalBookingMetrics(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select("
                SUM(CASE WHEN UPPER(b.user_type) = 'EXTERNAL' THEN 1 ELSE 0 END) AS external_count,
                COUNT(DISTINCT b.id) AS total_count
            ", false);

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        $row = $builder->get()->getRowArray() ?? [];
        $externalCount = (int) ($row['external_count'] ?? 0);
        $totalCount = max((int) ($row['total_count'] ?? 0), 0);

        return [
            'count' => $externalCount,
            'rate' => $totalCount > 0 ? round(($externalCount / $totalCount) * 100, 1) : 0.0,
        ];
    }

    private function frequentMaintenanceAssets(array $filters, array $scope, int $limit): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('a.name AS asset_name, l.name AS laboratory_name, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy('a.id, a.name, l.name')
            ->orderBy('total', 'DESC')
            ->limit($limit);

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
            'laboratory_name' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function leastUsedAssets(array $filters, array $scope, int $limit): array
    {
        $builder = $this->db->table('assets a')
            ->select("
                a.name AS asset_name,
                l.name AS laboratory_name,
                COALESCE(SUM(CASE WHEN UPPER(b.status) = 'APPROVED' THEN ba.quantity_used ELSE 0 END), 0) AS total_used
            ", false)
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->join('booking_assets ba', 'ba.asset_id = a.id', 'left')
            ->join('bookings b', 'b.id = ba.booking_id', 'left')
            ->groupBy('a.id, a.name, l.name')
            ->orderBy('total_used', 'ASC')
            ->orderBy('a.name', 'ASC')
            ->limit($limit);

        $this->applyScopeToLabColumn($builder, $scope, 'a.lab_id');

        if ($filters['lab_id'] !== '') {
            $builder->where('a.lab_id', (int) $filters['lab_id']);
        }
        if ($filters['asset_id'] !== '') {
            $builder->where('a.id', (int) $filters['asset_id']);
        }
        if ($filters['asset_category'] !== '') {
            $builder->where('a.category', $filters['asset_category']);
        }
        if ($filters['asset_status'] !== '') {
            $builder->where("LOWER(a.status) = " . $this->db->escape($filters['asset_status']), null, false);
        }
        if ($filters['date_from'] !== '') {
            $builder->groupStart()
                ->where('b.date >=', $filters['date_from'])
                ->orWhere('b.date IS NULL', null, false)
                ->groupEnd();
        }
        if ($filters['date_to'] !== '') {
            $builder->groupStart()
                ->where('b.date <=', $filters['date_to'])
                ->orWhere('b.date IS NULL', null, false)
                ->groupEnd();
        }

        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
            'laboratory_name' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
            'total_used' => (int) ($row['total_used'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenanceByLaboratoryRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("
                l.name AS laboratory,
                COUNT(*) AS total,
                SUM(CASE WHEN mr.status IN ('reported','scheduled','in_progress','testing') THEN 1 ELSE 0 END) AS open_cases
            ", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy('l.id, l.name')
            ->orderBy('total', 'DESC');

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'laboratory' => (string) ($row['laboratory'] ?? 'Unknown Lab'),
            'total' => (int) ($row['total'] ?? 0),
            'open_cases' => (int) ($row['open_cases'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenanceByAssetRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('a.name AS asset_name, l.name AS laboratory_name, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy('a.id, a.name, l.name')
            ->orderBy('total', 'DESC')
            ->limit(20);

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
            'laboratory_name' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenancePriorityRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('LOWER(mr.priority) AS priority, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->groupBy('LOWER(mr.priority)')
            ->orderBy('total', 'DESC');

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(fn(array $row): array => [
            'priority' => $this->titleize((string) ($row['priority'] ?? 'unknown')),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenanceReporterRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("COALESCE(NULLIF(reporter.full_name, ''), reporter.username, reporter_identity.secret, 'System') AS reported_by, COUNT(*) AS total", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('users reporter', 'reporter.id = mr.reported_by', 'left')
            ->join('auth_identities reporter_identity', "reporter_identity.user_id = reporter.id AND reporter_identity.type = 'email_password'", 'left')
            ->groupBy("COALESCE(NULLIF(reporter.full_name, ''), reporter.username, reporter_identity.secret, 'System')")
            ->orderBy('total', 'DESC')
            ->limit(15);

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'reported_by' => (string) ($row['reported_by'] ?? 'System'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenancePicWorkloadRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("
                COALESCE(NULLIF(l.pic_name, ''), l.pic_email, 'Unassigned PIC') AS pic_name,
                l.name AS laboratory,
                COUNT(*) AS total,
                SUM(CASE WHEN mr.status IN ('reported','scheduled','in_progress','testing') THEN 1 ELSE 0 END) AS open_cases
            ", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy("COALESCE(NULLIF(l.pic_name, ''), l.pic_email, 'Unassigned PIC'), l.name")
            ->orderBy('total', 'DESC');

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'pic_name' => (string) ($row['pic_name'] ?? 'Unassigned PIC'),
            'laboratory' => (string) ($row['laboratory'] ?? 'Unknown Lab'),
            'total' => (int) ($row['total'] ?? 0),
            'open_cases' => (int) ($row['open_cases'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function maintenanceResolutionMetrics(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('AVG(TIMESTAMPDIFF(HOUR, COALESCE(mr.started_at, mr.created_at), mr.completed_at)) AS avg_hours', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->where('mr.completed_at IS NOT NULL', null, false);

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        $row = $builder->get()->getRowArray() ?? [];
        $avgHours = round((float) ($row['avg_hours'] ?? 0), 1);

        return [
            'average_resolution_hours' => $avgHours,
            'average_resolution_days' => round($avgHours / 24, 2),
        ];
    }

    private function repeatedMaintenanceRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select('a.name AS asset_name, l.name AS laboratory_name, COUNT(*) AS total', false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy('a.id, a.name, l.name')
            ->having('COUNT(*) >', 1, false)
            ->orderBy('total', 'DESC')
            ->limit(15);

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
            'laboratory_name' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function assetsByLaboratoryRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('assets a')
            ->select('l.name AS laboratory, COUNT(*) AS asset_count, SUM(COALESCE(a.total_quantity, a.quantity, 0)) AS total_units', false)
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->groupBy('l.id, l.name')
            ->orderBy('asset_count', 'DESC');

        $this->modelApplyAssetFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'laboratory' => (string) ($row['laboratory'] ?? 'Unknown Lab'),
            'asset_count' => (int) ($row['asset_count'] ?? 0),
            'total_units' => (int) ($row['total_units'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function assetsByCategoryRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('assets a')
            ->select("COALESCE(NULLIF(TRIM(a.category), ''), 'Uncategorized') AS category, COUNT(*) AS asset_count, SUM(COALESCE(a.total_quantity, a.quantity, 0)) AS total_units", false)
            ->groupBy("COALESCE(NULLIF(TRIM(a.category), ''), 'Uncategorized')")
            ->orderBy('asset_count', 'DESC');

        $this->modelApplyAssetFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'category' => (string) ($row['category'] ?? 'Uncategorized'),
            'asset_count' => (int) ($row['asset_count'] ?? 0),
            'total_units' => (int) ($row['total_units'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function assetStatusRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('assets a')
            ->select("
                LOWER(a.status) AS status_key,
                CONCAT(UCASE(LEFT(LOWER(a.status), 1)), SUBSTRING(LOWER(a.status), 2)) AS status,
                COUNT(*) AS asset_count,
                SUM(COALESCE(a.total_quantity, a.quantity, 0)) AS total_units,
                SUM(COALESCE(a.quantity, 0)) AS available_units
            ", false)
            ->groupBy('LOWER(a.status)')
            ->orderBy('asset_count', 'DESC');

        $this->modelApplyAssetFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'status_key' => (string) ($row['status_key'] ?? ''),
            'status' => (string) ($row['status'] ?? 'Unknown'),
            'asset_count' => (int) ($row['asset_count'] ?? 0),
            'total_units' => (int) ($row['total_units'] ?? 0),
            'available_units' => (int) ($row['available_units'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function assetAvailabilityRows(array $assetRows, array $assetStatusRows): array
    {
        $totalUnits = 0;
        $availableUnits = 0;
        $maintenanceUnits = 0;

        foreach ($assetRows as $row) {
            $assetTotal = max((int) ($row['total_quantity'] ?? 0), (int) ($row['quantity'] ?? 0), 0);
            $assetAvailable = max((int) ($row['quantity'] ?? 0), 0);
            $totalUnits += $assetTotal;
            $availableUnits += $assetAvailable;
        }

        foreach ($assetStatusRows as $row) {
            if (($row['status_key'] ?? '') === 'maintenance') {
                $maintenanceUnits += max((int) ($row['total_units'] ?? 0), 0);
            }
        }

        return [
            'total_units' => $totalUnits,
            'available_units' => $availableUnits,
            'maintenance_units' => $maintenanceUnits,
            'availability_rate' => $totalUnits > 0 ? round(($availableUnits / $totalUnits) * 100, 1) : 0.0,
        ];
    }

    private function currentMaintenanceAssets(array $filters, array $scope): array
    {
        $builder = $this->db->table('maintenance_records mr')
            ->select("
                a.name AS asset_name,
                l.name AS laboratory_name,
                a.status AS status,
                SUM(COALESCE(mr.quantity_affected, 1)) AS affected_units
            ", false)
            ->join('assets a', 'a.id = mr.asset_id', 'left')
            ->join('laboratories l', 'l.id = a.lab_id', 'left')
            ->whereIn('mr.status', ['reported', 'scheduled', 'in_progress', 'testing'])
            ->groupBy('a.id, a.name, l.name, a.status')
            ->orderBy('affected_units', 'DESC');

        $this->modelApplyMaintenanceFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['asset_name'] ?? 'Unknown Asset'),
            'laboratory_name' => (string) ($row['laboratory_name'] ?? 'Unknown Lab'),
            'status' => $this->titleize((string) ($row['status'] ?? 'maintenance')),
            'affected_units' => (int) ($row['affected_units'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function laboratoryComparisonRows(array $labUsageRows, array $assetsByLabRows, array $labMaintenanceRows): array
    {
        $assetMap = [];
        foreach ($assetsByLabRows as $row) {
            $assetMap[$row['laboratory']] = $row;
        }

        $maintenanceMap = [];
        foreach ($labMaintenanceRows as $row) {
            $maintenanceMap[$row['laboratory']] = $row;
        }

        $rows = [];
        foreach ($labUsageRows as $row) {
            $labName = (string) ($row['laboratory_name'] ?? 'Unknown Lab');
            $assetRow = $assetMap[$labName] ?? [];
            $maintenanceRow = $maintenanceMap[$labName] ?? [];
            $rows[] = [
                'laboratory' => $labName,
                'room' => (string) ($row['laboratory_room'] ?? ''),
                'asset_count' => (int) ($assetRow['asset_count'] ?? 0),
                'booking_count' => (int) ($row['total_bookings'] ?? 0),
                'used_hours' => (float) ($row['total_used_hours'] ?? 0),
                'utilization_percentage' => (float) ($row['usage_percentage'] ?? 0),
                'maintenance_count' => (int) ($maintenanceRow['total'] ?? 0),
                'peak_day' => (string) ($row['peak_usage_day'] ?? 'N/A'),
                'peak_time' => (string) ($row['peak_usage_time'] ?? 'N/A'),
            ];
        }

        usort($rows, static fn(array $a, array $b): int => ($b['booking_count'] <=> $a['booking_count']) ?: ($b['utilization_percentage'] <=> $a['utilization_percentage']));

        return $rows;
    }

    private function notificationAnalytics(array $filters, array $scope): array
    {
        if (! $this->db->tableExists('notifications')) {
            return [
                'totals' => [
                    'total_notifications' => 0,
                    'read_notifications' => 0,
                    'unread_notifications' => 0,
                ],
                'type_rows' => [],
                'entity_rows' => [],
                'read_rows' => [],
                'trend_rows' => [],
                'role_rows' => [],
            ];
        }

        $totalsBuilder = $this->notificationScopeBuilder($filters, $scope)
            ->select("
                COUNT(DISTINCT n.id) AS total_notifications,
                SUM(CASE WHEN n.is_read = 1 THEN 1 ELSE 0 END) AS read_notifications,
                SUM(CASE WHEN n.is_read = 0 THEN 1 ELSE 0 END) AS unread_notifications
            ", false);
        $totals = $totalsBuilder->get()->getRowArray() ?? [];

        $typeRows = $this->notificationScopeBuilder($filters, $scope)
            ->select("COALESCE(NULLIF(TRIM(n.type), ''), 'general') AS type, COUNT(DISTINCT n.id) AS total", false)
            ->groupBy("COALESCE(NULLIF(TRIM(n.type), ''), 'general')")
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $entityRows = $this->notificationScopeBuilder($filters, $scope)
            ->select("COALESCE(NULLIF(TRIM(n.entity_type), ''), 'general') AS entity_type, COUNT(DISTINCT n.id) AS total", false)
            ->groupBy("COALESCE(NULLIF(TRIM(n.entity_type), ''), 'general')")
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $readRows = $this->notificationScopeBuilder($filters, $scope)
            ->select("CASE WHEN n.is_read = 1 THEN 'Read' ELSE 'Unread' END AS status, COUNT(DISTINCT n.id) AS total", false)
            ->groupBy('n.is_read')
            ->orderBy('status', 'ASC')
            ->get()
            ->getResultArray();

        $trendRows = $this->notificationScopeBuilder($filters, $scope)
            ->select("DATE_FORMAT(n.created_at, '%Y-%m') AS sort_key, DATE_FORMAT(n.created_at, '%b %Y') AS period, COUNT(DISTINCT n.id) AS total", false)
            ->where('n.created_at IS NOT NULL', null, false)
            ->groupBy("DATE_FORMAT(n.created_at, '%Y-%m')")
            ->orderBy('sort_key', 'ASC')
            ->get()
            ->getResultArray();

        $roleRows = $this->notificationRoleRows($filters, $scope);

        return [
            'totals' => [
                'total_notifications' => (int) ($totals['total_notifications'] ?? 0),
                'read_notifications' => (int) ($totals['read_notifications'] ?? 0),
                'unread_notifications' => (int) ($totals['unread_notifications'] ?? 0),
            ],
            'type_rows' => array_map(fn(array $row): array => [
                'type' => $this->titleize((string) ($row['type'] ?? 'general')),
                'total' => (int) ($row['total'] ?? 0),
            ], $typeRows),
            'entity_rows' => array_map(fn(array $row): array => [
                'entity_type' => $this->titleize((string) ($row['entity_type'] ?? 'general')),
                'total' => (int) ($row['total'] ?? 0),
            ], $entityRows),
            'read_rows' => array_map(static fn(array $row): array => [
                'status' => (string) ($row['status'] ?? 'Unread'),
                'total' => (int) ($row['total'] ?? 0),
            ], $readRows),
            'trend_rows' => array_map(static fn(array $row): array => [
                'period' => (string) ($row['period'] ?? '-'),
                'total' => (int) ($row['total'] ?? 0),
            ], $trendRows),
            'role_rows' => $roleRows,
        ];
    }

    private function notificationScopeBuilder(array $filters, array $scope): BaseBuilder
    {
        $hasMaintenanceRecords = $this->db->tableExists('maintenance_records');
        $hasAssets = $this->db->tableExists('assets');
        $hasExternalRequests = $this->db->tableExists('external_requests');
        $hasExternalAccessRequests = $this->db->tableExists('external_access_requests');

        $builder = $this->db->table('notifications n')
            ->join('bookings nb', "n.entity_type = 'booking' AND nb.id = n.entity_id", 'left', false);

        if ($hasMaintenanceRecords) {
            $builder->join('maintenance_records nmr', "n.entity_type = 'maintenance' AND nmr.id = n.entity_id", 'left', false);
        }

        if ($hasMaintenanceRecords && $hasAssets) {
            $builder->join('assets nma', 'nma.id = nmr.asset_id', 'left');
        }

        if ($hasAssets) {
            $builder->join('assets na', "n.entity_type = 'asset' AND na.id = n.entity_id", 'left', false);
        }

        if ($hasExternalRequests) {
            $builder->join('external_requests ner', "n.entity_type = 'external_request' AND ner.id = n.entity_id", 'left', false);
        }

        if ($hasExternalAccessRequests) {
            $builder->join('external_access_requests near', "n.entity_type = 'external_access_request' AND near.id = n.entity_id", 'left', false);
        }

        if ($filters['date_from'] !== '') {
            $builder->where('n.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if ($filters['date_to'] !== '') {
            $builder->where('n.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if ($scope['role'] === 'pic') {
            $labIds = $scope['labIds'] ?? [];
            if ($labIds === []) {
                $builder->where('n.user_id', (int) $scope['user_id']);
            } else {
                $builder->groupStart()
                    ->where('n.user_id', (int) $scope['user_id'])
                    ->orWhereIn('nb.lab_id', $labIds);

                if ($hasMaintenanceRecords && $hasAssets) {
                    $builder->orWhereIn('nma.lab_id', $labIds);
                }

                if ($hasAssets) {
                    $builder->orWhereIn('na.lab_id', $labIds);
                }

                if ($hasExternalRequests) {
                    $builder->orWhereIn('ner.lab_id', $labIds);
                }

                $builder->groupEnd();
            }
        } elseif ($scope['role'] === 'manager') {
            $entityTypes = ['booking'];
            if ($hasMaintenanceRecords) {
                $entityTypes[] = 'maintenance';
            }
            if ($hasAssets) {
                $entityTypes[] = 'asset';
            }
            if ($hasExternalRequests) {
                $entityTypes[] = 'external_request';
            }

            $builder->groupStart()
                ->whereIn('n.entity_type', $entityTypes)
                ->orWhere('n.user_id', (int) $scope['user_id'])
                ->groupEnd();
        }

        if ($filters['lab_id'] !== '') {
            $labId = (int) $filters['lab_id'];
            $builder->groupStart()
                ->where('nb.lab_id', $labId);

            if ($hasMaintenanceRecords && $hasAssets) {
                $builder->orWhere('nma.lab_id', $labId);
            }

            if ($hasAssets) {
                $builder->orWhere('na.lab_id', $labId);
            }

            if ($hasExternalRequests) {
                $builder->orWhere('ner.lab_id', $labId);
            }

            $builder->groupEnd();
        }

        return $builder;
    }

    private function notificationRoleRows(array $filters, array $scope): array
    {
        $roleSql = $this->roleLookupSql('n.user_id');
        $builder = $this->notificationScopeBuilder($filters, $scope)
            ->select("COALESCE($roleSql, 'system') AS role_label, COUNT(DISTINCT n.id) AS total", false)
            ->groupBy("COALESCE($roleSql, 'system')")
            ->orderBy('total', 'DESC');

        return array_map(fn(array $row): array => [
            'role' => $this->titleize((string) ($row['role_label'] ?? 'system')),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function userAnalytics(): array
    {
        $baseBuilder = $this->db->table('users u')->select('COUNT(*) AS total', false);
        if ($this->db->fieldExists('deleted_at', 'users')) {
            $baseBuilder->where('u.deleted_at', null);
        }
        $totalUsers = (int) (($baseBuilder->get()->getRowArray() ?? [])['total'] ?? 0);

        $roleRows = $this->userRoleRows();
        $roleMap = [];
        foreach ($roleRows as $row) {
            $roleMap[strtolower((string) $row['role'])] = (int) ($row['total'] ?? 0);
        }

        $statusRows = [];
        $activeUsers = null;
        $inactiveUsers = null;
        if ($this->db->fieldExists('active', 'users')) {
            $statusBuilder = $this->db->table('users u')
                ->select("CASE WHEN u.active = 1 THEN 'Active' ELSE 'Inactive' END AS status, COUNT(*) AS total", false)
                ->groupBy('u.active');
            if ($this->db->fieldExists('deleted_at', 'users')) {
                $statusBuilder->where('u.deleted_at', null);
            }
            $statusRows = array_map(static fn(array $row): array => [
                'status' => (string) ($row['status'] ?? 'Inactive'),
                'total' => (int) ($row['total'] ?? 0),
            ], $statusBuilder->get()->getResultArray());
            foreach ($statusRows as $row) {
                if (($row['status'] ?? '') === 'Active') {
                    $activeUsers = (int) $row['total'];
                }
                if (($row['status'] ?? '') === 'Inactive') {
                    $inactiveUsers = (int) $row['total'];
                }
            }
        }

        $registrationRows = [];
        if ($this->db->fieldExists('created_at', 'users')) {
            $registrationBuilder = $this->db->table('users u')
                ->select("DATE_FORMAT(u.created_at, '%Y-%m') AS sort_key, DATE_FORMAT(u.created_at, '%b %Y') AS period, COUNT(*) AS total", false)
                ->groupBy("DATE_FORMAT(u.created_at, '%Y-%m')")
                ->orderBy('sort_key', 'ASC');
            if ($this->db->fieldExists('deleted_at', 'users')) {
                $registrationBuilder->where('u.deleted_at', null);
            }
            $registrationRows = array_map(static fn(array $row): array => [
                'period' => (string) ($row['period'] ?? '-'),
                'total' => (int) ($row['total'] ?? 0),
            ], $registrationBuilder->get()->getResultArray());
        }

        return [
            'kpis' => [
                'total_users' => $totalUsers,
                'external_users' => (int) ($roleMap['external'] ?? 0),
                'active_users' => $activeUsers ?? 0,
                'inactive_users' => $inactiveUsers ?? 0,
            ],
            'role_rows' => $roleRows,
            'status_rows' => $statusRows,
            'registration_rows' => $registrationRows,
        ];
    }

    private function userRoleRows(): array
    {
        if ($this->db->fieldExists('group', 'auth_groups_users')) {
            $builder = $this->db->table('auth_groups_users agu')
                ->select('LOWER(agu.group) AS role, COUNT(DISTINCT agu.user_id) AS total', false)
                ->groupBy('LOWER(agu.group)')
                ->orderBy('total', 'DESC');

            return array_map(fn(array $row): array => [
                'role' => $this->titleize((string) ($row['role'] ?? 'user')),
                'total' => (int) ($row['total'] ?? 0),
            ], $builder->get()->getResultArray());
        }

        if ($this->db->fieldExists('group_id', 'auth_groups_users') && $this->db->tableExists('auth_groups')) {
            $builder = $this->db->table('auth_groups_users agu')
                ->select('LOWER(ag.name) AS role, COUNT(DISTINCT agu.user_id) AS total', false)
                ->join('auth_groups ag', 'ag.id = agu.group_id', 'inner')
                ->groupBy('LOWER(ag.name)')
                ->orderBy('total', 'DESC');

            return array_map(fn(array $row): array => [
                'role' => $this->titleize((string) ($row['role'] ?? 'user')),
                'total' => (int) ($row['total'] ?? 0),
            ], $builder->get()->getResultArray());
        }

        return [];
    }

    private function upcomingBookingRows(array $filters, array $scope): array
    {
        $builder = $this->db->table('bookings b')
            ->select('l.name AS lab_name, b.date, b.start_time, b.end_time, b.status, b.approval_flow', false)
            ->join('laboratories l', 'l.id = b.lab_id', 'left')
            ->where('b.date >=', date('Y-m-d'))
            ->whereIn('b.status', BookingModel::ACTIVE_STATUSES)
            ->orderBy('b.date', 'ASC')
            ->orderBy('b.start_time', 'ASC')
            ->limit(12);

        $this->modelApplyBookingFilters($builder, $filters, $scope, ['skip_status' => true]);

        return $builder->get()->getResultArray();
    }

    private function facultyCountsRows(array $filters, array $scope): array
    {
        if (! $this->db->tableExists('faculties')) {
            return [];
        }

        $builder = $this->db->table('bookings b')
            ->select("COALESCE(NULLIF(TRIM(f.name_en), ''), 'Unspecified') AS faculty_name, COUNT(DISTINCT b.id) AS total", false)
            ->join('faculties f', 'f.id = b.faculty_id', 'left')
            ->groupBy("COALESCE(NULLIF(TRIM(f.name_en), ''), 'Unspecified')")
            ->orderBy('total', 'DESC')
            ->limit(12);

        $this->modelApplyBookingFilters($builder, $filters, $scope);

        return array_map(static fn(array $row): array => [
            'faculty_name' => (string) ($row['faculty_name'] ?? 'Unspecified'),
            'total' => (int) ($row['total'] ?? 0),
        ], $builder->get()->getResultArray());
    }

    private function chartsFromData(array $bookingTrend, array $peakHours, array $labComparisonRows, array $maintenanceTrend): array
    {
        $charts = [];

        if ($bookingTrend !== []) {
            $charts[] = [
                'id' => 'chartBookingTrend',
                'title' => 'Booking Trend By Month',
                'type' => 'bar',
                'height' => 280,
                'data' => [
                    'labels' => array_column($bookingTrend, 'display_label'),
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => array_map('intval', array_column($bookingTrend, 'total')),
                        'backgroundColor' => 'rgba(59,130,246,0.70)',
                        'borderColor' => 'rgba(37,99,235,1)',
                        'borderWidth' => 1,
                    ]],
                ],
            ];
        }

        if ($peakHours !== []) {
            $charts[] = [
                'id' => 'chartPeakHours',
                'title' => 'Peak Booking Time Slots',
                'type' => 'bar',
                'height' => 260,
                'data' => [
                    'labels' => array_column($peakHours, 'time_slot'),
                    'datasets' => [[
                        'label' => 'Bookings',
                        'data' => array_map('intval', array_column($peakHours, 'total')),
                        'backgroundColor' => [
                            'rgba(16,185,129,0.70)',
                            'rgba(59,130,246,0.70)',
                            'rgba(245,158,11,0.70)',
                            'rgba(239,68,68,0.70)',
                            'rgba(148,163,184,0.70)',
                        ],
                        'borderWidth' => 1,
                    ]],
                ],
            ];
        }

        if ($labComparisonRows !== []) {
            $topRows = array_slice($labComparisonRows, 0, 8);
            $charts[] = [
                'id' => 'chartLabUtilization',
                'title' => 'Laboratory Utilization',
                'type' => 'bar',
                'height' => 300,
                'data' => [
                    'labels' => array_column($topRows, 'laboratory'),
                    'datasets' => [[
                        'label' => 'Utilization (%)',
                        'data' => array_map(static fn(array $row): float => (float) ($row['utilization_percentage'] ?? 0), $topRows),
                        'backgroundColor' => 'rgba(139,92,246,0.70)',
                        'borderColor' => 'rgba(124,58,237,1)',
                        'borderWidth' => 1,
                    ]],
                ],
            ];
        }

        if ($maintenanceTrend !== []) {
            $charts[] = [
                'id' => 'chartMaintenanceTrend',
                'title' => 'Maintenance Trend By Month',
                'type' => 'line',
                'height' => 280,
                'data' => [
                    'labels' => array_column($maintenanceTrend, 'display_label'),
                    'datasets' => [[
                        'label' => 'Maintenance Cases',
                        'data' => array_map('intval', array_column($maintenanceTrend, 'total')),
                        'backgroundColor' => 'rgba(239,68,68,0.18)',
                        'borderColor' => 'rgba(220,38,38,1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.3,
                    ]],
                ],
            ];
        }

        return $charts;
    }

    private function normalizeTrendRows(array $rows): array
    {
        return array_map(static fn(array $row): array => [
            'label' => (string) ($row['label'] ?? ''),
            'display_label' => (string) ($row['display_label'] ?? $row['label'] ?? ''),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    private function normalizeAssetDemandRows(array $rows): array
    {
        return array_map(static fn(array $row): array => [
            'asset_name' => (string) ($row['name'] ?? $row['asset_name'] ?? 'Unknown Asset'),
            'total_used' => (int) ($row['total_used'] ?? 0),
        ], $rows);
    }

    private function availableAssetStatuses(array $scope, array $filters): array
    {
        $builder = $this->db->table('assets a')
            ->select("LOWER(a.status) AS status_key, CONCAT(UCASE(LEFT(LOWER(a.status), 1)), SUBSTRING(LOWER(a.status), 2)) AS status", false)
            ->distinct()
            ->orderBy('status', 'ASC');

        $this->applyScopeToLabColumn($builder, $scope, 'a.lab_id');

        if (($filters['lab_id'] ?? '') !== '') {
            $builder->where('a.lab_id', (int) $filters['lab_id']);
        }

        return $builder->get()->getResultArray();
    }

    private function appliedFilters(array $filters, array $availableLabs, array $availableAssets): array
    {
        $applied = [];
        if ($filters['date_from'] !== '') {
            $applied[] = ['label' => 'From Date', 'value' => $filters['date_from']];
        }
        if ($filters['date_to'] !== '') {
            $applied[] = ['label' => 'To Date', 'value' => $filters['date_to']];
        }
        if ($filters['lab_id'] !== '') {
            $lab = $this->findById($availableLabs, (int) $filters['lab_id']);
            $applied[] = ['label' => 'Laboratory', 'value' => $lab ? trim((string) ($lab['name'] ?? '')) : $filters['lab_id']];
        }
        if ($filters['asset_id'] !== '') {
            $asset = $this->findById($availableAssets, (int) $filters['asset_id']);
            $applied[] = ['label' => 'Asset', 'value' => $asset ? trim((string) ($asset['name'] ?? $asset['asset_code'] ?? '')) : $filters['asset_id']];
        }
        if ($filters['booking_status'] !== '') {
            $applied[] = ['label' => 'Booking Status', 'value' => ucfirst(strtolower($filters['booking_status']))];
        }
        if ($filters['maintenance_status'] !== '') {
            $applied[] = ['label' => 'Maintenance Status', 'value' => $this->maintenanceStatusLabel($filters['maintenance_status'])];
        }
        if ($filters['asset_category'] !== '') {
            $applied[] = ['label' => 'Asset Category', 'value' => $filters['asset_category']];
        }
        if ($filters['asset_status'] !== '') {
            $applied[] = ['label' => 'Asset Status', 'value' => $this->titleize($filters['asset_status'])];
        }

        return $applied;
    }

    private function filterSummaryLine(array $appliedFilters): string
    {
        if ($appliedFilters === []) {
            return 'None';
        }

        return implode(' | ', array_map(static fn(array $filter): string => ($filter['label'] ?? 'Filter') . ': ' . ($filter['value'] ?? '-'), $appliedFilters));
    }

    /**
     * @param array<int, array<string, mixed>> $summaryCards
     * @return array<int, array<string, mixed>>
     */
    private function tailorSummaryCards(string $role, array $summaryCards): array
    {
        $preferredLabels = match ($role) {
            'pic' => [
                'Laboratories In Scope',
                'Total Bookings',
                'Pending Bookings',
                'Asset Availability (%)',
                'Open Maintenance',
                'Completed Maintenance',
            ],
            'manager' => [
                'Total Bookings',
                'Approval Rate (%)',
                'Laboratories In Scope',
                'Assets In Scope',
                'Open Maintenance',
                'Notifications',
            ],
            default => [
                'Total Bookings',
                'Total Users',
                'Laboratories In Scope',
                'Assets In Scope',
                'Open Maintenance',
                'Notifications',
            ],
        };

        $cardMap = [];
        foreach ($summaryCards as $card) {
            $cardMap[(string) ($card['label'] ?? '')] = $card;
        }

        $ordered = [];
        foreach ($preferredLabels as $label) {
            if (isset($cardMap[$label])) {
                $ordered[] = $cardMap[$label];
                unset($cardMap[$label]);
            }
        }

        return array_values(array_merge($ordered, $cardMap));
    }

    /**
     * @param array<int, array<string, mixed>> $sectionGroups
     * @return array<int, array<string, mixed>>
     */
    private function tailorSectionGroups(string $role, array $sectionGroups): array
    {
        $order = match ($role) {
            'pic' => ['booking', 'maintenance', 'asset', 'laboratory', 'notification'],
            'manager' => ['laboratory', 'booking', 'maintenance', 'asset', 'notification'],
            default => ['booking', 'laboratory', 'asset', 'maintenance', 'notification', 'users'],
        };

        $groupMap = [];
        foreach ($sectionGroups as $group) {
            $groupMap[(string) ($group['id'] ?? '')] = $group;
        }

        $ordered = [];
        foreach ($order as $id) {
            if (isset($groupMap[$id])) {
                $ordered[] = $groupMap[$id];
                unset($groupMap[$id]);
            }
        }

        return array_values(array_merge($ordered, $groupMap));
    }

    /**
     * @param array<int, array{id:int,name:string,room:string}> $scopeLaboratories
     * @param array<int, array<string, mixed>> $labComparisonRows
     * @param array<int, array<string, mixed>> $frequentMaintenanceAssets
     * @return array<string, mixed>
     */
    private function uiProfile(string $role, array $kpis, array $scopeLaboratories, array $labComparisonRows, array $frequentMaintenanceAssets): array
    {
        $topLab = $labComparisonRows[0]['laboratory'] ?? 'No data available';
        $topMaintenanceAsset = $frequentMaintenanceAssets[0]['asset_name'] ?? 'No data available';

        return match ($role) {
            'pic' => [
                'headline' => 'Operational view for assigned laboratories',
                'subheadline' => 'Focus on day-to-day laboratory control, booking flow, asset readiness, and maintenance workload inside your authorized scope.',
                'focusAreas' => ['My laboratory operations', 'Booking approvals and demand', 'Asset readiness', 'Maintenance workload'],
                'highlights' => [
                    ['label' => 'Assigned Laboratories', 'value' => count($scopeLaboratories), 'tone' => 'primary'],
                    ['label' => 'Pending Bookings', 'value' => $kpis['pending'] ?? 0, 'tone' => 'warning'],
                    ['label' => 'Open Maintenance', 'value' => $kpis['maintenance_open'] ?? 0, 'tone' => 'danger'],
                    ['label' => 'Asset Availability (%)', 'value' => $kpis['asset_availability_rate'] ?? 0, 'tone' => 'success'],
                ],
                'mobileSections' => ['bookingStatus', 'maintenanceStatus', 'labUtilization', 'upcomingBookings'],
                'webCallout' => [
                    ['label' => 'Top Demand Laboratory', 'value' => $topLab],
                    ['label' => 'Most Maintained Asset', 'value' => $topMaintenanceAsset],
                ],
            ],
            'manager' => [
                'headline' => 'Cross-laboratory operations overview',
                'subheadline' => 'Focus on comparison between laboratories, operational demand, maintenance activity, and service pressure across the institution.',
                'focusAreas' => ['Laboratory comparison', 'Booking performance', 'Maintenance trend', 'Utilization monitoring'],
                'highlights' => [
                    ['label' => 'Laboratories', 'value' => $kpis['total_labs'] ?? 0, 'tone' => 'accent'],
                    ['label' => 'Approval Rate (%)', 'value' => $kpis['approval_rate'] ?? 0, 'tone' => 'success'],
                    ['label' => 'Open Maintenance', 'value' => $kpis['maintenance_open'] ?? 0, 'tone' => 'warning'],
                    ['label' => 'Notifications', 'value' => $kpis['notifications_total'] ?? 0, 'tone' => 'primary'],
                ],
                'mobileSections' => ['topLabs', 'bookingTrend', 'labUtilization', 'maintenanceTrend'],
                'webCallout' => [
                    ['label' => 'Highest Demand Laboratory', 'value' => $topLab],
                    ['label' => 'Most Maintained Asset', 'value' => $topMaintenanceAsset],
                ],
            ],
            default => [
                'headline' => 'System-wide governance analytics',
                'subheadline' => 'Focus on institution-wide laboratory operations, maintenance activity, asset inventory, notifications, and user distribution.',
                'focusAreas' => ['System-wide oversight', 'User distribution', 'Laboratory performance', 'Asset and maintenance control'],
                'highlights' => [
                    ['label' => 'Total Users', 'value' => $kpis['users'] ?? 0, 'tone' => 'accent'],
                    ['label' => 'Total Laboratories', 'value' => $kpis['total_labs'] ?? 0, 'tone' => 'primary'],
                    ['label' => 'Total Assets', 'value' => $kpis['total_assets'] ?? 0, 'tone' => 'primary'],
                    ['label' => 'Open Maintenance', 'value' => $kpis['maintenance_open'] ?? 0, 'tone' => 'warning'],
                ],
                'mobileSections' => ['bookingStatus', 'maintenanceStatus', 'topLabs', 'bookingTrend', 'maintenanceTrend'],
                'webCallout' => [
                    ['label' => 'Highest Demand Laboratory', 'value' => $topLab],
                    ['label' => 'Most Maintained Asset', 'value' => $topMaintenanceAsset],
                ],
            ],
        };
    }

    private function collectLimitations(): array
    {
        $limitations = [];

        if (! $this->db->fieldExists('status', 'laboratories')) {
            $limitations[] = 'Laboratory status or availability analytics are not included because the laboratories table does not contain a status field.';
        }
        if (! in_array('COMPLETED', BookingModel::CORE_STATUSES, true)) {
            $limitations[] = 'Booking completion analytics are not included because the bookings table does not use a completed status.';
        }
        if (! $this->db->fieldExists('created_at', 'notifications')) {
            $limitations[] = 'Notification trend analytics are limited when notification timestamps are unavailable.';
        }
        if (! $this->db->fieldExists('active', 'users')) {
            $limitations[] = 'User active and inactive statistics are not available because the users table does not expose an active flag.';
        }

        return $limitations;
    }

    private function roleLookupSql(string $userColumn): string
    {
        if ($this->db->fieldExists('group', 'auth_groups_users')) {
            return "(SELECT LOWER(agu.`group`) FROM auth_groups_users agu WHERE agu.user_id = {$userColumn} ORDER BY FIELD(LOWER(agu.`group`), 'admin', 'manager', 'pic', 'student', 'staff', 'external') LIMIT 1)";
        }

        if ($this->db->fieldExists('group_id', 'auth_groups_users') && $this->db->tableExists('auth_groups')) {
            return "(SELECT LOWER(ag.`name`) FROM auth_groups_users agu JOIN auth_groups ag ON ag.id = agu.group_id WHERE agu.user_id = {$userColumn} ORDER BY FIELD(LOWER(ag.`name`), 'admin', 'manager', 'pic', 'student', 'staff', 'external') LIMIT 1)";
        }

        return 'NULL';
    }

    private function modelApplyBookingFilters(BaseBuilder $builder, array $filters, array $scope, array $options = []): void
    {
        $this->model->applyBookingFilters($builder, $filters, $scope, $options);
    }

    private function modelApplyAssetFilters(BaseBuilder $builder, array $filters, array $scope): void
    {
        $this->model->applyAssetFilters($builder, $filters, $scope);
    }

    private function modelApplyMaintenanceFilters(BaseBuilder $builder, array $filters, array $scope): void
    {
        $this->model->applyMaintenanceFilters($builder, $filters, $scope);
    }

    private function applyScopeToLabColumn(BaseBuilder $builder, array $scope, string $column): void
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

    private function metricTable(string $title, array $rows, bool $fullWidth): array
    {
        return [
            'title' => $title,
            'columns' => [
                ['key' => 'metric', 'label' => 'Metric'],
                ['key' => 'value', 'label' => 'Value'],
            ],
            'rows' => $rows,
            'fullWidth' => $fullWidth,
            'emptyMessage' => 'No data available.',
        ];
    }

    private function standardTable(string $title, array $columns, array $rows, bool $fullWidth, string $emptyMessage): array
    {
        return [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'fullWidth' => $fullWidth,
            'emptyMessage' => $emptyMessage,
        ];
    }

    private function optionRows(array $rows, string $valueKey, callable $labelResolver): array
    {
        return array_map(static function (array $row) use ($valueKey, $labelResolver): array {
            return [
                'value' => (string) ($row[$valueKey] ?? ''),
                'label' => $labelResolver($row),
            ];
        }, $rows);
    }

    private function assetTotalsMap(array $assetStatusRows): array
    {
        $totals = [];
        foreach ($assetStatusRows as $row) {
            $totals[strtolower((string) ($row['status_key'] ?? 'unknown'))] = (int) ($row['asset_count'] ?? 0);
        }

        return $totals;
    }

    private function maintenanceStatusLabel(string $status): string
    {
        return $this->maintenanceModel->statusLabel($status);
    }

    private function timeRangeLabel(mixed $start, mixed $end): string
    {
        $startText = is_string($start) && $start !== '' ? substr($start, 0, 5) : 'N/A';
        $endText = is_string($end) && $end !== '' ? substr($end, 0, 5) : 'N/A';
        return $startText . ' - ' . $endText;
    }

    private function displayDateTime(mixed $value): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return 'N/A';
        }

        try {
            return (new DateTimeImmutable($trimmed))->format('d M Y, h:i A');
        } catch (\Throwable) {
            return $trimmed;
        }
    }

    private function titleize(string $value): string
    {
        return ucwords(str_replace('_', ' ', strtolower($value)));
    }

    private function dayOrder(string $day): int
    {
        return match (strtolower($day)) {
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
            default => 99,
        };
    }

    private function slotOrder(string $slot): int
    {
        return match ($slot) {
            '08:00-10:00' => 1,
            '10:00-12:00' => 2,
            '13:00-15:00' => 3,
            '15:00-17:00' => 4,
            default => 99,
        };
    }

    private function roleSlug(string $role): string
    {
        return match ($role) {
            'manager' => 'lab_manager',
            default => preg_replace('/[^a-z0-9_]+/i', '_', strtolower($role)) ?: 'report',
        };
    }

    private function findById(array $rows, int $id): ?array
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    private function firstLabel(array $rows, string $key): string
    {
        return $rows[0][$key] ?? 'No data available';
    }
}
