<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaboratoryModel;

class LaboratoryAdminController extends BaseController
{
    protected LaboratoryModel $labModel;

    public function __construct()
    {
        helper(['auth', 'filesystem']);

        if (! auth()->loggedIn() || ! auth()->user()->inGroup('admin')) {
            redirect()->to('/')->with('error', 'You are not authorized to access this page.')->send();
            exit;
        }

        $this->labModel = new LaboratoryModel();
        foreach ([FCPATH . 'images/labs', FCPATH . 'images/pic'] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    public function index()
    {
        $labs = $this->labModel->orderBy('name', 'ASC')->findAll();
        $assetRows = $this->dbAssetSummary();

        foreach ($labs as &$lab) {
            $summary = $assetRows[$lab['id']] ?? ['asset_total' => 0, 'assets_in_maintenance' => 0, 'faulty_assets' => 0];
            $lab['asset_total'] = (int) $summary['asset_total'];
            $lab['assets_in_maintenance'] = (int) $summary['assets_in_maintenance'];
            $lab['faulty_assets'] = (int) $summary['faulty_assets'];
            $lab['image_url'] = ! empty($lab['image']) ? base_url($lab['image']) : '';
            $lab['pic_image_url'] = ! empty($lab['pic_image']) ? base_url($lab['pic_image']) : '';
        }
        unset($lab);

        return view('admin/labs/index', ['labs' => $labs]);
    }

    public function create()
    {
        return view('admin/labs/form', ['lab' => null]);
    }

    public function edit($id)
    {
        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/labs')->with('error', 'Laboratory not found.');
        }

        $lab['image_url'] = ! empty($lab['image']) ? base_url($lab['image']) : '';
        $lab['pic_image_url'] = ! empty($lab['pic_image']) ? base_url($lab['pic_image']) : '';

        return view('admin/labs/form', ['lab' => $lab]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->collectPayload();
        $payload['image'] = $this->handleUpload('image', 'images/labs');
        $payload['pic_image'] = $this->handleUpload('pic_image', 'images/pic');

        $this->labModel->insert($payload);
        return redirect()->to('/admin/labs')->with('message', 'Laboratory created successfully.');
    }

    public function update($id)
    {
        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/labs')->with('error', 'Laboratory not found.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->collectPayload();
        $payload['image'] = $this->handleUpload('image', 'images/labs', $lab['image'] ?? null, (bool) $this->request->getPost('remove_image'));
        $payload['pic_image'] = $this->handleUpload('pic_image', 'images/pic', $lab['pic_image'] ?? null, (bool) $this->request->getPost('remove_pic_image'));

        $this->labModel->update($id, $payload);
        return redirect()->to('/admin/labs')->with('message', 'Laboratory updated successfully.');
    }

    public function delete($id)
    {
        $lab = $this->labModel->find($id);
        if (! $lab) {
            return redirect()->to('/admin/labs')->with('error', 'Laboratory not found.');
        }

        $assetCount = model('App\\Models\\AssetModel')->where('lab_id', $id)->countAllResults();
        if ($assetCount > 0) {
            return redirect()->to('/admin/labs')->with('error', 'Delete or reassign laboratory assets before removing this laboratory.');
        }

        foreach (['image', 'pic_image'] as $field) {
            if (! empty($lab[$field]) && is_file(FCPATH . $lab[$field])) {
                unlink(FCPATH . $lab[$field]);
            }
        }

        $this->labModel->delete($id);
        return redirect()->to('/admin/labs')->with('message', 'Laboratory deleted successfully.');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min_length[3]|max_length[255]',
            'room' => 'required|max_length[50]',
            'description' => 'permit_empty|string',
            'capacity' => 'permit_empty|integer|greater_than[0]',
            'availability_note' => 'permit_empty|max_length[255]',
            'safety_note' => 'permit_empty|string',
            'pic_name' => 'required|min_length[3]|max_length[255]',
            'pic_email' => 'permit_empty|valid_email|max_length[255]',
            'pic_phone' => 'permit_empty|max_length[30]',
            'image' => 'permit_empty|max_size[image,2048]|ext_in[image,jpg,jpeg,png,gif]',
            'pic_image' => 'permit_empty|max_size[pic_image,2048]|ext_in[pic_image,jpg,jpeg,png,gif]',
        ];
    }

    protected function collectPayload(): array
    {
        return [
            'name' => trim((string) $this->request->getPost('name')),
            'room' => trim((string) $this->request->getPost('room')),
            'description' => trim((string) $this->request->getPost('description')),
            'capacity' => $this->request->getPost('capacity') !== '' ? (int) $this->request->getPost('capacity') : null,
            'availability_note' => trim((string) $this->request->getPost('availability_note')),
            'safety_note' => trim((string) $this->request->getPost('safety_note')),
            'pic_name' => trim((string) $this->request->getPost('pic_name')),
            'pic_email' => trim((string) $this->request->getPost('pic_email')),
            'pic_phone' => trim((string) $this->request->getPost('pic_phone')),
        ];
    }

    protected function handleUpload(string $field, string $targetDir, ?string $current = null, bool $remove = false): ?string
    {
        if ($remove) {
            if ($current && is_file(FCPATH . $current)) {
                unlink(FCPATH . $current);
            }
            return null;
        }

        $file = $this->request->getFile($field);
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $current;
        }

        if ($current && is_file(FCPATH . $current)) {
            unlink(FCPATH . $current);
        }

        $newName = $file->getRandomName();
        if ($file->move(FCPATH . trim($targetDir, '/'), $newName)) {
            return trim($targetDir, '/') . '/' . $newName;
        }

        return $current;
    }

    protected function dbAssetSummary(): array
    {
        $rows = model('App\\Models\\AssetModel')
            ->select("lab_id, COUNT(*) AS asset_total, SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) AS assets_in_maintenance, SUM(CASE WHEN status = 'faulty' THEN 1 ELSE 0 END) AS faulty_assets")
            ->groupBy('lab_id')
            ->findAll();

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['lab_id']] = $row;
        }

        return $summary;
    }
}