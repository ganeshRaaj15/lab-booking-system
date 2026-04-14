<?php

namespace App\Controllers\Technician;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Models\AssetModel;
use App\Models\MaintenanceLogModel;
use App\Models\MaintenanceRecordModel;

class MaintenanceController extends BaseController
{
    protected MaintenanceRecordModel $maintenanceModel;
    protected MaintenanceLogModel $logModel;
    protected AssetModel $assetModel;

    public function __construct()
    {
        helper(['auth', 'filesystem']);
        $this->maintenanceModel = new MaintenanceRecordModel();
        $this->logModel = new MaintenanceLogModel();
        $this->assetModel = new AssetModel();

        $directory = FCPATH . 'images/maintenance';
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function index()
    {
        if ($redirect = $this->ensureTechnician()) {
            return $redirect;
        }

        $user = auth()->user();
        $status = trim((string) $this->request->getGet('status'));
        $assetId = (int) $this->request->getGet('asset_id');
        $scope = trim((string) $this->request->getGet('scope'));

        $recordsQuery = $this->maintenanceModel->withRelations();
        if ($status !== '') {
            $recordsQuery->where('maintenance_records.status', $status);
        }
        if ($assetId > 0) {
            $recordsQuery->where('maintenance_records.asset_id', $assetId);
        }
        if ($scope === 'mine') {
            $recordsQuery->where('maintenance_records.assigned_technician_id', $user->id);
        }

        $records = $recordsQuery->orderBy('maintenance_records.created_at', 'DESC')->findAll();
        $labels = $this->maintenanceModel->workflowLabels();

        return view('technician/maintenance/index', [
            'title' => 'Maintenance Records | FKMP Smart Lab',
            'page' => 'Maintenance Records',
            'roleLabel' => 'Technician',
            'user' => $user,
            'records' => $records,
            'assets' => $this->assetOptions(),
            'filters' => ['status' => $status, 'asset_id' => $assetId, 'scope' => $scope],
            'statusOptions' => array_keys($labels),
            'statusLabels' => $labels,
        ]);
    }

    public function create(?int $assetId = null)
    {
        if ($redirect = $this->ensureTechnician()) {
            return $redirect;
        }

        $record = [
            'asset_id' => $assetId,
            'quantity_affected' => 1,
            'unit_reference' => '',
            'title' => '',
            'issue_type' => 'preventive',
            'priority' => 'medium',
            'status' => 'reported',
            'description' => '',
            'scheduled_for' => '',
            'diagnosis_notes' => '',
            'work_notes' => '',
            'test_notes' => '',
            'resolution_notes' => '',
            'report_photo_path' => null,
            'completion_photo_path' => null,
        ];

        return view('technician/maintenance/form', [
            'title' => 'Plan Maintenance | FKMP Smart Lab',
            'page' => 'Plan Maintenance',
            'roleLabel' => 'Technician',
            'user' => auth()->user(),
            'mode' => 'create',
            'record' => $record,
            'assets' => $this->assetOptions(),
            'logs' => [],
            'issueTypes' => ['preventive', 'inspection', 'calibration', 'other'],
            'priorities' => ['low', 'medium', 'high', 'critical'],
            'statusLabels' => $this->maintenanceModel->workflowLabels(),
            'stageMode' => 'pre',
            'isLocked' => false,
        ]);
    }

    public function store()
    {
        if ($redirect = $this->ensureTechnician()) {
            return $redirect;
        }

        $user = auth()->user();
        $input = $this->collectPayload();
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        if ($input['issue_type'] === 'corrective') {
            return redirect()->back()->withInput()->with('error', 'Corrective maintenance must start from a student or PIC issue report. Use this screen only for planned maintenance work.');
        }

        $asset = $this->assetModel->find($input['asset_id']);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Selected asset was not found.');
        }

        if ($message = $this->validatePreMaintenanceInput($input, $asset, null)) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        $data = [
            'asset_id' => $input['asset_id'],
            'quantity_affected' => $input['quantity_affected'],
            'unit_reference' => $input['unit_reference'] !== '' ? $input['unit_reference'] : null,
            'reported_by' => $user->id,
            'assigned_technician_id' => $user->id,
            'title' => $input['title'],
            'issue_type' => $input['issue_type'],
            'priority' => $input['priority'],
            'description' => $input['description'],
            'report_photo_path' => null,
            'status' => 'scheduled',
            'asset_status_before' => $asset['status'],
            'asset_status_after' => null,
            'scheduled_for' => $input['scheduled_for'],
            'accepted_at' => date('Y-m-d H:i:s'),
            'diagnosis_notes' => $input['diagnosis_notes'],
            'started_at' => null,
            'work_notes' => null,
            'tested_at' => null,
            'test_notes' => null,
            'completed_at' => null,
            'resolution_notes' => null,
            'completion_photo_path' => null,
        ];

        $db = \Config\Database::connect();
        $db->transStart();
        $this->maintenanceModel->insert($data);
        $maintenanceId = $this->maintenanceModel->getInsertID();
        $this->logModel->insert([
            'maintenance_id' => $maintenanceId,
            'changed_by' => $user->id,
            'from_status' => null,
            'to_status' => 'scheduled',
            'notes' => 'Preventive maintenance accepted and scheduled. Diagnosis recorded.',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->assetModel->syncManagedAvailability((int) $input['asset_id']);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Unable to create maintenance record at the moment.');
        }

        (new NotificationService())->notifyMaintenanceScheduled((int) $maintenanceId);

        return redirect()->to('/technician/maintenance')->with('success', 'Maintenance case created and scheduled successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->ensureTechnician()) {
            return $redirect;
        }

        $record = $this->maintenanceModel->withRelations()->where('maintenance_records.id', $id)->first();
        if (! $record) {
            return redirect()->to('/technician/maintenance')->with('error', 'Maintenance record not found.');
        }

        $logs = $this->logModel
            ->select('maintenance_logs.*, users.full_name, users.username')
            ->join('users', 'users.id = maintenance_logs.changed_by', 'left')
            ->where('maintenance_id', $id)
            ->orderBy('maintenance_logs.created_at', 'DESC')
            ->findAll();

        $isLocked = in_array($record['status'], ['completed', 'cancelled'], true);
        $stageMode = $record['status'] === 'reported' ? 'pre' : ($isLocked ? 'locked' : 'post');

        return view('technician/maintenance/form', [
            'title' => 'Update Maintenance | FKMP Smart Lab',
            'page' => 'Update Maintenance',
            'roleLabel' => 'Technician',
            'user' => auth()->user(),
            'mode' => 'edit',
            'record' => $record,
            'assets' => $this->assetOptions(),
            'logs' => $logs,
            'issueTypes' => $record['issue_type'] === 'corrective' ? ['corrective'] : ['preventive', 'inspection', 'calibration', 'other'],
            'priorities' => ['low', 'medium', 'high', 'critical'],
            'statusLabels' => $this->maintenanceModel->workflowLabels(),
            'stageMode' => $stageMode,
            'isLocked' => $isLocked,
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->ensureTechnician()) {
            return $redirect;
        }

        $user = auth()->user();
        $record = $this->maintenanceModel->find($id);
        if (! $record) {
            return redirect()->to('/technician/maintenance')->with('error', 'Maintenance record not found.');
        }
        if (in_array($record['status'], ['completed', 'cancelled'], true)) {
            return redirect()->to('/technician/maintenance/edit/' . $id)->with('error', 'This record is closed and can no longer be changed.');
        }

        $input = $this->collectPayload();
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $asset = $this->assetModel->find($input['asset_id']);
        if (! $asset) {
            return redirect()->back()->withInput()->with('error', 'Selected asset was not found.');
        }

        $stageMode = $record['status'] === 'reported' ? 'pre' : 'post';
        $message = $stageMode === 'pre'
            ? $this->validatePreMaintenanceInput($input, $asset, $record)
            : $this->validatePostMaintenanceInput($input, $asset, $record);
        if ($message) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        $completionPhotoPath = $record['completion_photo_path'] ?? null;
        if ($stageMode === 'post') {
            $completionPhotoPath = $this->handlePhotoUpload('completion_photo', $completionPhotoPath);
        }

        $updateData = [
            'asset_id' => $input['asset_id'],
            'quantity_affected' => $input['quantity_affected'],
            'unit_reference' => $input['unit_reference'] !== '' ? $input['unit_reference'] : null,
            'assigned_technician_id' => $user->id,
            'title' => $input['title'],
            'issue_type' => $record['issue_type'] === 'corrective' ? 'corrective' : $input['issue_type'],
            'priority' => $input['priority'],
            'description' => $input['description'],
        ];

        if ($stageMode === 'pre') {
            $updateData = array_merge($updateData, [
                'status' => 'scheduled',
                'scheduled_for' => $input['scheduled_for'],
                'accepted_at' => $record['accepted_at'] ?: date('Y-m-d H:i:s'),
                'diagnosis_notes' => $input['diagnosis_notes'],
            ]);
        } else {
            $updateData = array_merge($updateData, [
                'status' => 'completed',
                'work_notes' => $input['work_notes'],
                'test_notes' => $input['test_notes'],
                'resolution_notes' => $input['resolution_notes'],
                'completion_photo_path' => $completionPhotoPath,
                'started_at' => $record['started_at'] ?: date('Y-m-d H:i:s'),
                'tested_at' => date('Y-m-d H:i:s'),
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $oldAssetId = (int) $record['asset_id'];
        $newAssetId = (int) $input['asset_id'];

        $db = \Config\Database::connect();
        $db->transStart();
        $this->maintenanceModel->update($id, $updateData);
        $this->logModel->insert([
            'maintenance_id' => $id,
            'changed_by' => $user->id,
            'from_status' => $record['status'],
            'to_status' => $stageMode === 'pre' ? 'scheduled' : 'completed',
            'notes' => $stageMode === 'pre'
                ? 'Technician accepted the case, added diagnosis, and scheduled the maintenance work.'
                : 'Technician completed the repair, testing, and completion summary with evidence.',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($oldAssetId !== $newAssetId) {
            $this->assetModel->syncManagedAvailability($oldAssetId);
        }
        $this->assetModel->syncManagedAvailability($newAssetId);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Unable to update maintenance record at the moment.');
        }

        if ($stageMode === 'pre') {
            (new NotificationService())->notifyMaintenanceScheduled($id);
        } else {
            (new NotificationService())->notifyMaintenanceCompleted($id);
        }

        return redirect()->to('/technician/maintenance/edit/' . $id)->with('success', $stageMode === 'pre'
            ? 'Pre-maintenance stage saved successfully.'
            : 'Post-maintenance stage completed successfully.');
    }

    protected function ensureTechnician()
    {
        helper('auth');
        if (! auth()->loggedIn()) {
            return redirect()->to('/login');
        }
        if (! auth()->user()->inGroup('technician')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        return null;
    }

    protected function rules(): array
    {
        return [
            'asset_id' => 'required|integer',
            'quantity_affected' => 'required|integer|greater_than[0]',
            'unit_reference' => 'permit_empty|max_length[120]',
            'title' => 'required|min_length[3]|max_length[255]',
            'issue_type' => 'required|in_list[preventive,corrective,inspection,calibration,other]',
            'priority' => 'required|in_list[low,medium,high,critical]',
            'description' => 'required|min_length[10]',
            'scheduled_for' => 'permit_empty|valid_date[Y-m-d\TH:i]',
            'diagnosis_notes' => 'permit_empty|string',
            'work_notes' => 'permit_empty|string',
            'test_notes' => 'permit_empty|string',
            'resolution_notes' => 'permit_empty|string',
            'completion_photo' => 'permit_empty|max_size[completion_photo,4096]|is_image[completion_photo]|mime_in[completion_photo,image/jpg,image/jpeg,image/png,image/webp]',
        ];
    }

    protected function collectPayload(): array
    {
        return [
            'asset_id' => (int) $this->request->getPost('asset_id'),
            'quantity_affected' => max((int) $this->request->getPost('quantity_affected'), 0),
            'unit_reference' => trim((string) $this->request->getPost('unit_reference')),
            'title' => trim((string) $this->request->getPost('title')),
            'issue_type' => trim((string) $this->request->getPost('issue_type')),
            'priority' => trim((string) $this->request->getPost('priority')),
            'description' => trim((string) $this->request->getPost('description')),
            'scheduled_for' => trim((string) $this->request->getPost('scheduled_for')),
            'diagnosis_notes' => trim((string) $this->request->getPost('diagnosis_notes')),
            'work_notes' => trim((string) $this->request->getPost('work_notes')),
            'test_notes' => trim((string) $this->request->getPost('test_notes')),
            'resolution_notes' => trim((string) $this->request->getPost('resolution_notes')),
        ];
    }

    protected function assetOptions(): array
    {
        return $this->assetModel
            ->select('assets.id, assets.name, assets.asset_code, assets.status, assets.quantity, assets.total_quantity, laboratories.name AS lab_name')
            ->join('laboratories', 'laboratories.id = assets.lab_id', 'left')
            ->orderBy('laboratories.name', 'ASC')
            ->orderBy('assets.name', 'ASC')
            ->findAll();
    }

    protected function editableQuantityCapacity(int $assetId, ?array $record = null): int
    {
        $asset = $this->assetModel->find($assetId);
        if (! $asset) {
            return 0;
        }

        $ignoreRecordId = 0;
        if ($record !== null && (int) $record['asset_id'] === $assetId) {
            $ignoreRecordId = (int) ($record['id'] ?? 0);
        }

        $total = $this->assetModel->totalQuantity($asset);
        $openOtherUnits = min($this->assetModel->openMaintenanceUnits($assetId, $ignoreRecordId), $total);

        return max($total - $openOtherUnits, 0);
    }

    protected function validatePreMaintenanceInput(array $input, array $asset, ?array $record): ?string
    {
        $availableCapacity = $this->editableQuantityCapacity($input['asset_id'], $record);
        if ($input['quantity_affected'] > $availableCapacity) {
            return 'Affected quantity cannot exceed the units currently available for maintenance on this asset.';
        }

        $totalUnits = $this->assetModel->totalQuantity($asset);
        if ($totalUnits > 1 && $input['unit_reference'] === '') {
            return 'Please identify the exact workstation, unit label, or physical equipment reference for multi-unit assets.';
        }
        if ($input['scheduled_for'] === '') {
            return 'Please set the maintenance schedule before saving the pre-maintenance stage.';
        }
        if ($input['diagnosis_notes'] === '') {
            return 'Please record the diagnosis before completing the pre-maintenance stage.';
        }

        return null;
    }

    protected function validatePostMaintenanceInput(array $input, array $asset, array $record): ?string
    {
        $totalUnits = $this->assetModel->totalQuantity($asset);
        if ($totalUnits > 1 && trim((string) ($record['unit_reference'] ?? $input['unit_reference'])) === '') {
            return 'A unit reference is required for multi-unit assets before maintenance can be completed.';
        }
        if ($input['work_notes'] === '') {
            return 'Please enter the repair work notes before completing the post-maintenance stage.';
        }
        if ($input['test_notes'] === '') {
            return 'Please enter the testing or verification notes before completing the post-maintenance stage.';
        }
        if ($input['resolution_notes'] === '') {
            return 'Please enter the completion summary before completing the post-maintenance stage.';
        }

        $existingPhoto = $record['completion_photo_path'] ?? null;
        $newPhoto = $this->request->getFile('completion_photo');
        $hasNewPhoto = $newPhoto && $newPhoto->isValid() && ! $newPhoto->hasMoved();
        if (! $hasNewPhoto && empty($existingPhoto)) {
            return 'Please attach a completion photo before completing the post-maintenance stage.';
        }

        return null;
    }

    protected function handlePhotoUpload(string $fieldName, ?string $currentPath = null): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $currentPath;
        }

        if ($currentPath && is_file(FCPATH . $currentPath)) {
            unlink(FCPATH . $currentPath);
        }

        $newName = $file->getRandomName();
        if ($file->move(FCPATH . 'images/maintenance', $newName)) {
            return 'images/maintenance/' . $newName;
        }

        return $currentPath;
    }
}


