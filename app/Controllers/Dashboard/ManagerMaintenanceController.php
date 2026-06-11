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
        if ($redirect = $this->ensureViewer()) {
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
            'roleLabel'         => $this->viewerRoleLabel(),
            'backUrl'           => $this->viewerBackUrl(),
            'basePath'          => $this->viewerBasePath(),
        ]);
    }

    /**
     * GET /dashboard/manager/maintenance/:id
     * Read-only detail view of one maintenance record.
     */
    public function show(int $id)
    {
        if ($redirect = $this->ensureViewer()) {
            return $redirect;
        }

        $record = $this->maintenanceModel->withRelations()->where('maintenance_records.id', $id)->first();
        if (! $record) {
            return redirect()->to($this->viewerBasePath())->with('error', 'Maintenance record not found.');
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
            'roleLabel'    => $this->viewerRoleLabel(),
            'backUrl'      => $this->viewerBasePath(),
        ]);
    }

    protected function ensureViewer()
    {
        if (! auth()->loggedIn()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user = auth()->user();
        if (! $user->inGroup('manager') && ! $user->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        return null;
    }

    protected function viewerRoleLabel(): string
    {
        $user = auth()->user();
        return $user && $user->inGroup('admin') ? 'Administrator' : 'Lab Manager';
    }

    protected function viewerBackUrl(): string
    {
        $user = auth()->user();
        return $user && $user->inGroup('admin') ? '/dashboard/admin' : '/dashboard/manager';
    }

    protected function viewerBasePath(): string
    {
        $user = auth()->user();
        return $user && $user->inGroup('admin') ? '/dashboard/admin/maintenance' : '/dashboard/manager/maintenance';
    }
}
