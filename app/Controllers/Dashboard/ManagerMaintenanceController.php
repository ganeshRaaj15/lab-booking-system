<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Libraries\MaintenanceForecastService;
use App\Libraries\MaintenancePredictionService;
use App\Models\AssetModel;
use App\Models\MaintenanceLogModel;
use App\Models\MaintenanceRecordModel;

class ManagerMaintenanceController extends BaseController
{
    protected MaintenanceRecordModel $maintenanceModel;
    protected MaintenanceLogModel $logModel;
    protected AssetModel $assetModel;

    public function __construct()
    {
        helper('auth');
        $this->maintenanceModel = new MaintenanceRecordModel();
        $this->logModel         = new MaintenanceLogModel();
        $this->assetModel       = new AssetModel();
    }

    /**
     * GET /dashboard/manager/maintenance
     * Read-only list of all maintenance records (system-wide).
     */
    public function index()
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $status  = trim((string) $this->request->getGet('status'));
        $assetId = (int) $this->request->getGet('asset_id');
        $labels  = $this->maintenanceModel->workflowLabels();

        if ($status !== '' && ! \array_key_exists($status, $labels)) {
            $status = '';
        }

        $query = $this->maintenanceModel->withRelations();
        if ($status !== '') {
            $query->where('maintenance_records.status', $status);
        }
        if ($assetId > 0) {
            $query->where('maintenance_records.asset_id', $assetId);
        }

        $records = $query->orderBy('maintenance_records.created_at', 'DESC')->paginate(20);
        $pager   = $this->maintenanceModel->pager;

        $forecastService   = new MaintenanceForecastService();
        $predictionService = new MaintenancePredictionService();
        $upcomingForecasts = $forecastService->getUpcomingForecasts(90);

        return view('dashboard/manager/maintenance/index', [
            'records'           => $records,
            'pager'             => $pager,
            'statusLabels'      => $labels,
            'filters'           => ['status' => $status, 'asset_id' => $assetId],
            'upcomingForecasts' => $upcomingForecasts,
            'modelSummary'      => $predictionService->getModelSummary(),
            'user'              => auth()->user(),
        ]);
    }

    /**
     * GET /dashboard/manager/maintenance/:id
     * Read-only detail view of one maintenance record.
     */
    public function show(int $id)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $record = $this->maintenanceModel->withRelations()->where('maintenance_records.id', $id)->first();
        if (! $record) {
            return redirect()->to('/dashboard/manager/maintenance')->with('error', 'Maintenance record not found.');
        }

        $logs = $this->logModel
            ->select('maintenance_logs.*, users.username, users.full_name')
            ->join('users', 'users.id = maintenance_logs.changed_by', 'left')
            ->where('maintenance_logs.maintenance_id', $id)
            ->orderBy('maintenance_logs.created_at', 'ASC')
            ->findAll();

        $asset = $this->assetModel->find($record['asset_id']);

        return view('dashboard/manager/maintenance/show', [
            'record'       => $record,
            'logs'         => $logs,
            'asset'        => $asset,
            'statusLabels' => $this->maintenanceModel->workflowLabels(),
            'user'         => auth()->user(),
        ]);
    }

    protected function ensureManager()
    {
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('manager')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        return null;
    }
}
