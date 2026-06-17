<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Services\ReportAnalyticsService;
use Throwable;
use InvalidArgumentException;
use RuntimeException;

class AnalyticsController extends BaseController
{
    protected ReportAnalyticsService $reports;

    public function __construct()
    {
        helper('auth');
        $this->reports = new ReportAnalyticsService();
    }

    public function index()
    {
        $user = auth()->user();
        if ($user === null) {
            return redirect()->to('/login');
        }

        try {
            $report = $this->reports->build($user, $this->request->getGet());
        } catch (InvalidArgumentException | RuntimeException $e) {
            return redirect()
                ->to('/dashboard/reports/analytics')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Analytics report page failed: {message}', ['message' => $e->getMessage()]);

            return redirect()
                ->to('/dashboard')
                ->with('error', 'The analytics report is temporarily unavailable.');
        }

        $layoutView = in_array($report['role'], ['admin', 'pic'], true) ? 'layouts/main_admin' : 'layouts/main_user';

        return view('reports/analytics', [
            'layoutView' => $layoutView,
            'pageTitle' => $report['reportTitle'],
            'pageDescription' => $report['scopeDescription'],
            'report' => $report,
            'summaryExportUrls' => [
                'pdf' => site_url('/dashboard/reports/pdf') . $this->queryString(),
                'csv' => site_url('/dashboard/reports/csv') . $this->queryString(),
            ],
            'filterAction' => site_url('/dashboard/reports/analytics'),
            'filterFields' => $this->filterFields($report),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filterFields(array $report): array
    {
        $available = $report['availableFilters'] ?? [];
        $role = (string) ($report['role'] ?? 'pic');
        $labs = $available['labs'] ?? [];

        $fields = [
            ['name' => 'date_from', 'label' => 'From Date', 'type' => 'date'],
            ['name' => 'date_to', 'label' => 'To Date', 'type' => 'date'],
            ['name' => 'asset_id', 'label' => 'Asset', 'type' => 'select', 'options' => $available['assets'] ?? []],
            ['name' => 'booking_status', 'label' => 'Booking Status', 'type' => 'select', 'options' => $available['booking_statuses'] ?? []],
            ['name' => 'maintenance_status', 'label' => 'Maintenance Status', 'type' => 'select', 'options' => $available['maintenance_statuses'] ?? []],
            ['name' => 'asset_category', 'label' => 'Asset Category', 'type' => 'select', 'options' => $available['asset_categories'] ?? []],
            ['name' => 'asset_status', 'label' => 'Asset Status', 'type' => 'select', 'options' => $available['asset_statuses'] ?? []],
        ];

        if ($role !== 'pic' || count($labs) > 1) {
            array_splice($fields, 2, 0, [[
                'name' => 'lab_id',
                'label' => $role === 'pic' ? 'Assigned Laboratory' : 'Laboratory',
                'type' => 'select',
                'options' => $labs,
            ]]);
        }

        return $fields;
    }

    private function queryString(): string
    {
        $query = http_build_query(array_filter(
            $this->request->getGet(),
            static fn($value): bool => $value !== null && $value !== ''
        ));

        return $query !== '' ? '?' . $query : '';
    }
}
