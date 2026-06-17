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
            $detail = sprintf(
                '[%s] %s in %s line %d',
                get_class($e),
                $e->getMessage(),
                basename($e->getFile()),
                $e->getLine()
            );
            log_message('error', 'Analytics build failed ' . $detail);

            return $this->response->setBody(
                '<html><head><title>Report Error</title>'
                . '<style>body{font-family:sans-serif;padding:32px;background:#f8f9fa}'
                . 'pre{background:#fff;border:1px solid #dee2e6;border-radius:6px;padding:20px;white-space:pre-wrap;word-break:break-word}'
                . 'a{color:#0d6efd}</style></head><body>'
                . '<h2 style="color:#dc3545">Report Build Failed</h2>'
                . '<p>Copy the error below and share it to get it fixed:</p>'
                . '<pre>' . htmlspecialchars($detail) . '</pre>'
                . '<p><a href="/dashboard">Back to Dashboard</a></p>'
                . '</body></html>'
            );
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
