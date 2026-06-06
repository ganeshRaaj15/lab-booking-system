<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Models\AssetModel;
use App\Models\LaboratoryModel;
use App\Models\MaintenanceLogModel;
use App\Models\MaintenanceRecordModel;

class IssueReportController extends BaseController
{
    protected AssetModel $assetModel;
    protected LaboratoryModel $labModel;
    protected MaintenanceRecordModel $maintenanceModel;
    protected MaintenanceLogModel $logModel;

    public function __construct()
    {
        helper(['auth', 'filesystem']);
        $this->assetModel = new AssetModel();
        $this->labModel   = new LaboratoryModel();
        $this->maintenanceModel = new MaintenanceRecordModel();
        $this->logModel = new MaintenanceLogModel();

        $directory = FCPATH . 'images/maintenance';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function create()
    {
        if ($redirect = $this->ensureReporter()) {
            return $redirect;
        }

        $user = auth()->user();
        $recentReports = $this->maintenanceModel->withRelations()
            ->where('maintenance_records.reported_by', $user->id)
            ->orderBy('maintenance_records.created_at', 'DESC')
            ->findAll(8);

        // PIC sees only their labs; others see all labs (assets filtered client-side after lab selection).
        $labs = $user->inGroup('pic')
            ? $this->labModel->where('LOWER(TRIM(pic_email)) =', strtolower(trim((string) $user->email)))->orderBy('name', 'ASC')->findAll()
            : $this->labModel->orderBy('name', 'ASC')->findAll();

        return view('dashboard/issues/form', [
            'title'         => 'Report Asset Issue | FKMP Smart Lab',
            'page'          => 'Report Asset Issue',
            'user'          => $user,
            'labs'          => $labs,
            'recentReports' => $recentReports,
            'priorities'    => ['low', 'medium', 'high', 'critical'],
        ]);
    }

    public function store()
    {
        if ($redirect = $this->ensureReporter()) {
            return $redirect;
        }

        $user = auth()->user();
        $rules = [
            'lab_id'   => 'required|integer',
            'asset_id' => 'required|integer',
            'quantity_affected' => 'required|integer|greater_than[0]',
            'title' => 'required|min_length[3]|max_length[255]',
            'priority' => 'required|in_list[low,medium,high,critical]',
            'description' => 'required|min_length[10]',
            'unit_reference' => 'permit_empty|max_length[120]',
            'report_photo' => 'permit_empty|max_size[report_photo,4096]|is_image[report_photo]|mime_in[report_photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $labId            = (int) $this->request->getPost('lab_id');
        $assetId          = (int) $this->request->getPost('asset_id');
        $quantityAffected = (int) $this->request->getPost('quantity_affected');
        $unitReference    = trim((string) $this->request->getPost('unit_reference'));

        $asset = $this->assetModel->find($assetId);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Selected asset was not found.');
        }

        // Backend validation: asset must belong to the submitted lab_id.
        if ((int) $asset['lab_id'] !== $labId) {
            return redirect()->back()->withInput()->with('error', 'The selected asset does not belong to the selected laboratory.');
        }

        $assetOptions = $this->availableAssetsForReporter($user);
        $allowedAsset = null;
        foreach ($assetOptions as $option) {
            if ((int) $option['id'] === $assetId) {
                $allowedAsset = $option;
                break;
            }
        }

        if (! $allowedAsset) {
            return redirect()->back()->withInput()->with('error', 'You are not allowed to report issues for this asset.');
        }

        $availableUnits = max((int) ($asset['quantity'] ?? 0), 0);
        $totalUnits = max((int) ($asset['total_quantity'] ?? 0), $availableUnits);
        if ($availableUnits < 1) {
            return redirect()->back()->withInput()->with('error', 'This asset has no available units left to report. The lab PIC is already handling the full stock.');
        }
        if ($quantityAffected > $availableUnits) {
            return redirect()->back()->withInput()->with('error', 'The affected quantity cannot exceed the currently available units for this asset.');
        }
        if ($totalUnits > 1 && $unitReference === '') {
            return redirect()->back()->withInput()->with('error', 'Please specify the workstation, unit code, seat number, or physical label for multi-unit equipment.');
        }

        $reportPhotoPath = $this->handlePhotoUpload('report_photo');

        $db = \Config\Database::connect();
        $db->transStart();

        $maintenanceId = $this->maintenanceModel->insert([
            'asset_id' => $assetId,
            'quantity_affected' => $quantityAffected,
            'unit_reference' => $unitReference !== '' ? $unitReference : null,
            'reported_by' => $user->id,
            'assigned_technician_id' => null,
            'title' => trim((string) $this->request->getPost('title')),
            'issue_type' => 'corrective',
            'priority' => trim((string) $this->request->getPost('priority')),
            'description' => trim((string) $this->request->getPost('description')),
            'report_photo_path' => $reportPhotoPath,
            'status' => 'reported',
            'asset_status_before' => $asset['status'],
            'asset_status_after' => null,
            'scheduled_for' => null,
            'accepted_at' => null,
            'diagnosis_notes' => null,
            'started_at' => null,
            'work_notes' => null,
            'tested_at' => null,
            'test_notes' => null,
            'completed_at' => null,
            'resolution_notes' => null,
            'completion_photo_path' => null,
        ], true);

        $logText = 'Issue reported by ' . ($user->full_name ?: $user->username) . ' for ' . $quantityAffected . ' unit(s).';
        if ($unitReference !== '') {
            $logText .= ' Unit reference: ' . $unitReference . '.';
        }

        $this->logModel->insert([
            'maintenance_id' => $maintenanceId,
            'changed_by' => $user->id,
            'from_status' => null,
            'to_status' => 'reported',
            'notes' => $logText,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assetModel->syncManagedAvailability($assetId);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Unable to submit the issue report right now.');
        }

        NotificationService::dispatchSafely(
            fn(NotificationService $notifications) => $notifications->notifyMaintenanceReported((int) $maintenanceId),
            'maintenance reported'
        );

        return redirect()->to('/dashboard/report-issue')->with('success', 'Issue report submitted successfully. The lab PIC has been notified.');
    }

    /**
     * GET /dashboard/report-issue/assets-by-lab/:labId
     * AJAX: return available (non-decommissioned) assets for a given lab.
     * Used by the issue report form to populate the asset dropdown after lab selection.
     */
    public function assetsForLab(int $labId)
    {
        if ($redirect = $this->ensureReporter()) {
            return $redirect;
        }

        $user = auth()->user();

        // PIC may only query their own lab(s).
        if ($user->inGroup('pic')) {
            $picEmail  = strtolower(trim((string) $user->email));
            $lab       = $this->labModel->find($labId);
            $labPicEmail = strtolower(trim((string) ($lab['pic_email'] ?? '')));
            if (! $lab || $labPicEmail !== $picEmail) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Access denied.']);
            }
        }

        $assets = $this->assetModel
            ->select('assets.id, assets.name, assets.asset_code, assets.status, assets.quantity, assets.total_quantity')
            ->where('assets.lab_id', $labId)
            ->where('assets.status !=', 'decommissioned')
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        // Only return assets that have at least 1 available unit.
        $available = array_values(array_filter($assets, static fn(array $a): bool => (int) ($a['quantity'] ?? 0) > 0));

        return $this->response->setJSON([
            'status' => 'success',
            'assets' => array_map(static fn(array $a): array => [
                'id'             => (int) $a['id'],
                'name'           => (string) $a['name'],
                'asset_code'     => (string) ($a['asset_code'] ?? ''),
                'quantity'       => (int) $a['quantity'],
                'total_quantity' => (int) ($a['total_quantity'] ?? $a['quantity']),
            ], $available),
        ]);
    }

    protected function ensureReporter()
    {
        helper('auth');

        if (! auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        if (! $user->inGroup('student') && ! $user->inGroup('staff') && ! $user->inGroup('pic') && ! $user->inGroup('external')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        return null;
    }

    protected function availableAssetsForReporter($user): array
    {
        $builder = $this->assetModel
            ->select('assets.id, assets.name, assets.asset_code, assets.status, assets.quantity, assets.total_quantity, laboratories.name AS lab_name, laboratories.room AS lab_room')
            ->join('laboratories', 'laboratories.id = assets.lab_id', 'left');

        if ($user->inGroup('pic')) {
            $builder->where('LOWER(TRIM(laboratories.pic_email)) =', strtolower(trim((string) $user->email)));
        }

        $assets = $builder
            ->orderBy('laboratories.name', 'ASC')
            ->orderBy('assets.name', 'ASC')
            ->findAll();

        return array_values(array_filter($assets, static fn(array $asset): bool => (int) ($asset['quantity'] ?? 0) > 0));
    }

    protected function handlePhotoUpload(string $fieldName): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $newName = $file->getRandomName();
        if ($file->move(FCPATH . 'images/maintenance', $newName)) {
            return 'images/maintenance/' . $newName;
        }

        return null;
    }
}

