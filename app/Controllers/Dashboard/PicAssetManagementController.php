<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\AssetModel;
use App\Models\LaboratoryModel;
use App\Models\MaintenanceRecordModel;

class PicAssetManagementController extends BaseController
{
    protected AssetModel $assetModel;
    protected LaboratoryModel $labModel;
    protected MaintenanceRecordModel $maintenanceModel;

    public function __construct()
    {
        helper(['auth', 'filesystem']);
        $this->assetModel       = new AssetModel();
        $this->labModel         = new LaboratoryModel();
        $this->maintenanceModel = new MaintenanceRecordModel();

        $dir = FCPATH . 'images/assets';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * GET /dashboard/pic/assets
     * List all assets belonging to the PIC's assigned labs.
     */
    public function index()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $picLabIds = $this->picLabIds();

        if ($picLabIds === []) {
            return view('dashboard/pic/assets/index', [
                'assets'  => [],
                'labs'    => [],
                'filters' => ['q' => '', 'lab_id' => 0, 'status' => ''],
                'user'    => auth()->user(),
            ]);
        }

        $filters = [
            'q'      => trim((string) $this->request->getGet('q')),
            'lab_id' => (int) $this->request->getGet('lab_id'),
            'status' => trim((string) $this->request->getGet('status')),
        ];
        if (! in_array($filters['status'], ['available', 'maintenance', 'faulty', 'decommissioned'], true)) {
            $filters['status'] = '';
        }
        if ($filters['lab_id'] > 0 && ! in_array($filters['lab_id'], $picLabIds, true)) {
            $filters['lab_id'] = 0;
        }

        $builder = $this->assetModel
            ->select('assets.*, laboratories.name AS lab_name, laboratories.room AS lab_room')
            ->join('laboratories', 'laboratories.id = assets.lab_id', 'left')
            ->whereIn('assets.lab_id', $picLabIds);

        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('assets.name', $filters['q'])
                ->orLike('assets.asset_code', $filters['q'])
                ->orLike('assets.category', $filters['q'])
                ->groupEnd();
        }
        if ($filters['lab_id'] > 0) {
            $builder->where('assets.lab_id', $filters['lab_id']);
        }
        if ($filters['status'] !== '') {
            $builder->where('assets.status', $filters['status']);
        }

        $assets = $builder->orderBy('laboratories.name', 'ASC')->orderBy('assets.name', 'ASC')->paginate(20);
        $pager  = $this->assetModel->pager;

        foreach ($assets as &$asset) {
            $asset = $this->applyLegacyDefaults($asset);
            $asset['maintenance_quantity'] = max((int) $asset['total_quantity'] - (int) $asset['quantity'], 0);
        }
        unset($asset);

        $labs = $this->labModel->whereIn('id', $picLabIds)->orderBy('name', 'ASC')->findAll();

        return view('dashboard/pic/assets/index', [
            'assets'  => $assets,
            'pager'   => $pager,
            'labs'    => $labs,
            'filters' => $filters,
            'user'    => auth()->user(),
        ]);
    }

    /**
     * GET /dashboard/pic/assets/create
     * Show the form to add a new asset.
     */
    public function create()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $labs = $this->picLabs();
        if ($labs === []) {
            return redirect()->to('/dashboard/pic')->with('error', 'You are not assigned to any laboratory. Ask an administrator to assign you first.');
        }

        return view('dashboard/pic/assets/form', [
            'asset' => null,
            'labs'  => $labs,
            'user'  => auth()->user(),
        ]);
    }

    /**
     * POST /dashboard/pic/assets/store
     * Save a new asset under one of the PIC's assigned labs.
     */
    public function store()
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $labId = (int) $this->request->getPost('lab_id');
        if (! in_array($labId, $this->picLabIds(), true)) {
            return redirect()->back()->withInput()->with('error', 'You can only add assets to your assigned laboratory.');
        }

        $payload = $this->collectPayload();

        if ($err = $this->duplicateMessage($payload)) {
            return redirect()->back()->withInput()->with('error', $err);
        }

        $payload['image']          = $this->handleImageUpload();
        $payload['quantity']       = $payload['total_quantity'];
        $payload['status']         = 'available';

        $this->assetModel->insert($payload);

        return redirect()->to('/dashboard/pic/assets')->with('message', 'Asset created successfully.');
    }

    /**
     * GET /dashboard/pic/assets/edit/:id
     * Show the edit form for an asset belonging to the PIC's lab.
     */
    public function edit(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $asset = $this->assetForPic($id);
        if (! $asset) {
            return redirect()->to('/dashboard/pic/assets')->with('error', 'Asset not found or you are not the assigned PIC for this laboratory.');
        }

        $asset = $this->applyLegacyDefaults($asset);
        $asset['image_url'] = ! empty($asset['image']) ? base_url('/' . ltrim((string) $asset['image'], '/')) : '';

        return view('dashboard/pic/assets/form', [
            'asset' => $asset,
            'labs'  => $this->picLabs(),
            'user'  => auth()->user(),
        ]);
    }

    /**
     * POST /dashboard/pic/assets/update/:id
     * Update an asset belonging to the PIC's lab.
     */
    public function update(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $asset = $this->assetForPic($id);
        if (! $asset) {
            return redirect()->to('/dashboard/pic/assets')->with('error', 'Asset not found or you are not the assigned PIC for this laboratory.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $labId = (int) $this->request->getPost('lab_id');
        if (! in_array($labId, $this->picLabIds(), true)) {
            return redirect()->back()->withInput()->with('error', 'You can only assign assets to your own laboratory.');
        }

        $payload = $this->collectPayload();

        if ($err = $this->duplicateMessage($payload, $id)) {
            return redirect()->back()->withInput()->with('error', $err);
        }

        $openUnits          = min($this->assetModel->openMaintenanceUnits($id), $payload['total_quantity']);
        $payload['quantity'] = max($payload['total_quantity'] - $openUnits, 0);
        $payload['status']  = $openUnits > 0 ? 'maintenance' : 'available';
        $payload['image']   = $this->handleImageUpload($asset['image'] ?? null);

        $this->assetModel->update($id, $payload);
        $this->assetModel->syncManagedAvailability($id);

        return redirect()->to('/dashboard/pic/assets')->with('message', 'Asset updated successfully.');
    }

    /**
     * POST /dashboard/pic/assets/decommission/:id
     * Mark an assigned asset as decommissioned (non-destructive alternative to deletion).
     */
    public function decommission(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $asset = $this->assetForPic($id);
        if (! $asset) {
            return redirect()->to('/dashboard/pic/assets')->with('error', 'Asset not found or you are not the assigned PIC for this laboratory.');
        }

        $action = trim((string) $this->request->getPost('action'));

        if ($action === 'restore') {
            $this->assetModel->update($id, ['status' => 'available', 'quantity' => (int) ($asset['total_quantity'] ?? 1)]);
            $this->assetModel->syncManagedAvailability($id);
            return redirect()->to('/dashboard/pic/assets')->with('message', 'Asset restored to active status.');
        }

        $this->assetModel->update($id, ['status' => 'decommissioned', 'quantity' => 0]);
        return redirect()->to('/dashboard/pic/assets')->with('message', 'Asset marked as decommissioned.');
    }

    /**
     * POST /dashboard/pic/assets/delete/:id
     * Delete an asset belonging to the PIC's lab (only if no maintenance history).
     */
    public function delete(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $asset = $this->assetForPic($id);
        if (! $asset) {
            return redirect()->to('/dashboard/pic/assets')->with('error', 'Asset not found or you are not the assigned PIC for this laboratory.');
        }

        $hasMaintenance = $this->maintenanceModel->where('asset_id', $id)->countAllResults() > 0;
        if ($hasMaintenance) {
            return redirect()->to('/dashboard/pic/assets')->with('error', 'This asset has maintenance history and cannot be deleted. To retire it, update its status instead.');
        }

        if (! empty($asset['image']) && is_file(FCPATH . $asset['image'])) {
            unlink(FCPATH . $asset['image']);
        }

        $this->assetModel->delete($id);

        return redirect()->to('/dashboard/pic/assets')->with('message', 'Asset deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function ensurePic()
    {
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('pic')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        return null;
    }

    /** Return lab IDs where pic_email == current user's email. */
    protected function picLabIds(): array
    {
        $picEmail = strtolower(trim((string) auth()->user()->email));
        return array_column(
            $this->labModel->where('LOWER(TRIM(pic_email)) =', $picEmail)->findAll(),
            'id'
        );
    }

    /** Return lab records where pic_email == current user's email. */
    protected function picLabs(): array
    {
        $picEmail = strtolower(trim((string) auth()->user()->email));
        return $this->labModel
            ->where('LOWER(TRIM(pic_email)) =', $picEmail)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Return the asset only if it belongs to one of this PIC's assigned labs.
     * This is the IDOR guard — called before every read/write on a specific asset.
     */
    protected function assetForPic(int $id): ?array
    {
        $picLabIds = $this->picLabIds();
        if ($picLabIds === []) {
            return null;
        }

        return $this->assetModel
            ->whereIn('lab_id', $picLabIds)
            ->where('id', $id)
            ->first();
    }

    protected function rules(): array
    {
        return [
            'asset_code'     => 'required|min_length[2]|max_length[50]',
            'name'           => 'required|min_length[3]|max_length[255]',
            'category'       => 'permit_empty|max_length[100]',
            'brand'          => 'permit_empty|max_length[100]',
            'model'          => 'permit_empty|max_length[100]',
            'serial_number'  => 'permit_empty|max_length[100]',
            'lab_id'         => 'required|integer',
            'total_quantity' => 'required|integer|greater_than[0]',
            'location_note'  => 'permit_empty|max_length[255]',
            'purchase_date'  => 'permit_empty|valid_date[Y-m-d]',
            'specifications' => 'permit_empty|string',
            'image'          => 'permit_empty|max_size[image,2048]|ext_in[image,jpg,jpeg,png,gif,webp]',
        ];
    }

    protected function collectPayload(): array
    {
        return [
            'asset_code'     => strtoupper(trim((string) $this->request->getPost('asset_code'))),
            'name'           => trim((string) $this->request->getPost('name')),
            'category'       => trim((string) $this->request->getPost('category')),
            'brand'          => trim((string) $this->request->getPost('brand')),
            'model'          => trim((string) $this->request->getPost('model')),
            'serial_number'  => trim((string) $this->request->getPost('serial_number')),
            'lab_id'         => (int) $this->request->getPost('lab_id'),
            'total_quantity' => (int) $this->request->getPost('total_quantity'),
            'location_note'  => trim((string) $this->request->getPost('location_note')),
            'purchase_date'  => trim((string) $this->request->getPost('purchase_date')) ?: null,
            'specifications' => trim((string) $this->request->getPost('specifications')),
        ];
    }

    protected function duplicateMessage(array $payload, int $ignoreId = 0): ?string
    {
        $q = $this->assetModel->where('asset_code', $payload['asset_code']);
        if ($ignoreId > 0) {
            $q->where('id !=', $ignoreId);
        }
        if ($payload['asset_code'] !== '' && $q->first()) {
            return 'Asset code already exists.';
        }

        if ($payload['serial_number'] !== '') {
            $sq = $this->assetModel->where('serial_number', $payload['serial_number']);
            if ($ignoreId > 0) {
                $sq->where('id !=', $ignoreId);
            }
            if ($sq->first()) {
                return 'Serial number already exists.';
            }
        }

        return null;
    }

    protected function handleImageUpload(?string $current = null): ?string
    {
        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $current;
        }

        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            return $current;
        }

        if ($current && is_file(FCPATH . $current)) {
            unlink(FCPATH . $current);
        }

        $newName = $file->getRandomName();
        if ($file->move(FCPATH . 'images/assets', $newName)) {
            return 'images/assets/' . $newName;
        }

        return $current;
    }

    protected function applyLegacyDefaults(array $asset): array
    {
        $asset['asset_code']     = ! empty($asset['asset_code'])     ? $asset['asset_code']     : ('AST-' . str_pad((string) ($asset['id'] ?? 0), 4, '0', STR_PAD_LEFT));
        $asset['category']       = ! empty($asset['category'])       ? $asset['category']       : 'General Equipment';
        $asset['total_quantity'] = max((int) ($asset['total_quantity'] ?? 0), (int) ($asset['quantity'] ?? 0), 1);
        $asset['quantity']       = max((int) ($asset['quantity'] ?? 0), 0);
        $asset['status']         = ! empty($asset['status'])         ? $asset['status']         : 'available';
        return $asset;
    }
}
