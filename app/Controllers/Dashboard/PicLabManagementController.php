<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\LaboratoryModel;

class PicLabManagementController extends BaseController
{
    protected LaboratoryModel $labModel;

    public function __construct()
    {
        helper(['auth', 'filesystem']);
        $this->labModel = new LaboratoryModel();

        foreach ([FCPATH . 'images/labs', FCPATH . 'images/pic'] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * GET /dashboard/pic/lab/edit/:id
     * Show the edit form for a lab this PIC is assigned to.
     */
    public function edit(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $lab = $this->labForPic($id);
        if (! $lab) {
            return redirect()->to('/dashboard/pic')->with('error', 'Laboratory not found or you are not the assigned PIC.');
        }

        $lab['image_url']     = ! empty($lab['image'])     ? base_url($lab['image'])     : '';
        $lab['pic_image_url'] = ! empty($lab['pic_image']) ? base_url($lab['pic_image']) : '';

        return view('dashboard/pic/lab_edit', [
            'lab'  => $lab,
            'user' => auth()->user(),
        ]);
    }

    /**
     * POST /dashboard/pic/lab/update/:id
     * Save PIC-editable fields for the assigned lab.
     */
    public function update(int $id)
    {
        if ($redirect = $this->ensurePic()) {
            return $redirect;
        }

        $lab = $this->labForPic($id);
        if (! $lab) {
            return redirect()->to('/dashboard/pic')->with('error', 'Laboratory not found or you are not the assigned PIC.');
        }

        $rules = [
            'description'       => 'permit_empty|string|max_length[2000]',
            'availability_note' => 'permit_empty|max_length[255]',
            'safety_note'       => 'permit_empty|string|max_length[2000]',
            'capacity'          => 'permit_empty|integer|greater_than[0]',
            'image'             => 'permit_empty|max_size[image,2048]|ext_in[image,jpg,jpeg,png,gif,webp]',
            'pic_image'         => 'permit_empty|max_size[pic_image,2048]|ext_in[pic_image,jpg,jpeg,png,gif,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'description'       => trim((string) $this->request->getPost('description')),
            'availability_note' => trim((string) $this->request->getPost('availability_note')),
            'safety_note'       => trim((string) $this->request->getPost('safety_note')),
            'capacity'          => $this->request->getPost('capacity') !== '' ? (int) $this->request->getPost('capacity') : null,
        ];

        $payload['image']     = $this->handleUpload('image',     'images/labs', $lab['image']     ?? null, (bool) $this->request->getPost('remove_image'));
        $payload['pic_image'] = $this->handleUpload('pic_image', 'images/pic',  $lab['pic_image'] ?? null, (bool) $this->request->getPost('remove_pic_image'));

        $this->labModel->update($id, $payload);

        return redirect()->to('/dashboard/pic')->with('message', 'Laboratory updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Ensure the authenticated user is in the pic group. */
    protected function ensurePic()
    {
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('pic')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        return null;
    }

    /**
     * Return the lab record only if pic_email matches the current user.
     * This is the ownership guard — called on every mutating action.
     */
    protected function labForPic(int $id): ?array
    {
        $lab = $this->labModel->find($id);
        if (! $lab) {
            return null;
        }

        $picEmail = strtolower(trim((string) auth()->user()->email));
        if (strtolower(trim((string) ($lab['pic_email'] ?? ''))) !== $picEmail) {
            return null;
        }

        return $lab;
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

        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
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
}
