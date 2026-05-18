<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContactPersonnelModel;
use App\Models\SettingsModel;

class ContactSettingsController extends BaseController
{
    private const CLASS_NAME = 'contact';

    private const SETTING_KEYS = [
        'faculty_name', 'university_name', 'address', 'phone', 'fax',
        'operating_hours', 'location', 'general_note', 'personnel_note',
        'map_embed_src', 'directions_url', 'google_maps_url', 'waze_url',
        'coordinates', 'parking_info', 'transport_info',
    ];

    protected SettingsModel $settings;
    protected ContactPersonnelModel $personnel;

    public function __construct()
    {
        helper(['auth']);

        if (!auth()->loggedIn() || !auth()->user()->inGroup('admin')) {
            redirect()->to('/')->with('error', 'Unauthorized access.')->send();
            exit;
        }

        $this->settings  = new SettingsModel();
        $this->personnel = new ContactPersonnelModel();
    }

    public function index()
    {
        $rows = $this->settings
            ->where('class', self::CLASS_NAME)
            ->findAll();

        $stored = [];
        foreach ($rows as $row) {
            $stored[$row['key']] = $row['value'];
        }

        $contactSettings = [];
        foreach (self::SETTING_KEYS as $key) {
            $contactSettings[$key] = $stored[$key] ?? '';
        }

        return view('admin/contact/index', [
            'contactSettings' => $contactSettings,
            'personnel'       => $this->personnel->allOrdered(),
        ]);
    }

    public function updateSettings()
    {
        $rules = [
            'faculty_name'    => 'required|max_length[255]',
            'university_name' => 'required|max_length[255]',
            'address'         => 'required|max_length[500]',
            'phone'           => 'permit_empty|max_length[50]',
            'fax'             => 'permit_empty|max_length[50]',
            'operating_hours' => 'permit_empty|max_length[255]',
            'location'        => 'permit_empty|max_length[255]',
            'general_note'    => 'permit_empty|max_length[1000]',
            'personnel_note'  => 'permit_empty|max_length[1000]',
            'map_embed_src'   => 'permit_empty|max_length[2000]',
            'directions_url'  => 'permit_empty|max_length[2000]',
            'google_maps_url' => 'permit_empty|max_length[2000]',
            'waze_url'        => 'permit_empty|max_length[2000]',
            'coordinates'     => 'permit_empty|max_length[100]',
            'parking_info'    => 'permit_empty|max_length[500]',
            'transport_info'  => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        foreach (self::SETTING_KEYS as $key) {
            $value = trim((string) $this->request->getPost($key));
            $this->upsert($key, $value);
        }

        return redirect()->to('/admin/contact-settings')->with('message', 'Contact page settings updated.');
    }

    public function addPersonnel()
    {
        $rules = [
            'name'  => 'required|max_length[255]',
            'role'  => 'required|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'photo_path'  => 'permit_empty|max_length[500]',
            'sort_order'  => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('personnel_errors', $this->validator->getErrors());
        }

        $this->personnel->insert([
            'name'       => trim($this->request->getPost('name')),
            'role'       => trim($this->request->getPost('role')),
            'phone'      => trim($this->request->getPost('phone') ?? ''),
            'email'      => trim($this->request->getPost('email') ?? ''),
            'photo_path' => trim($this->request->getPost('photo_path') ?? ''),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
        ]);

        return redirect()->to('/admin/contact-settings#personnel')->with('message', 'Personnel added.');
    }

    public function updatePersonnel(int $id)
    {
        $person = $this->personnel->find($id);
        if (!$person) {
            return redirect()->to('/admin/contact-settings')->with('error', 'Personnel not found.');
        }

        $rules = [
            'name'  => 'required|max_length[255]',
            'role'  => 'required|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'photo_path'  => 'permit_empty|max_length[500]',
            'sort_order'  => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('personnel_errors', $this->validator->getErrors());
        }

        $this->personnel->update($id, [
            'name'       => trim($this->request->getPost('name')),
            'role'       => trim($this->request->getPost('role')),
            'phone'      => trim($this->request->getPost('phone') ?? ''),
            'email'      => trim($this->request->getPost('email') ?? ''),
            'photo_path' => trim($this->request->getPost('photo_path') ?? ''),
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
        ]);

        return redirect()->to('/admin/contact-settings#personnel')->with('message', 'Personnel updated.');
    }

    public function deletePersonnel(int $id)
    {
        $person = $this->personnel->find($id);
        if (!$person) {
            return redirect()->to('/admin/contact-settings')->with('error', 'Personnel not found.');
        }

        $this->personnel->delete($id);

        return redirect()->to('/admin/contact-settings#personnel')->with('message', 'Personnel removed.');
    }

    private function upsert(string $key, string $value): void
    {
        $now      = date('Y-m-d H:i:s');
        $existing = $this->settings
            ->where('class', self::CLASS_NAME)
            ->where('key', $key)
            ->first();

        if ($existing) {
            $this->settings->update($existing['id'], [
                'value'      => $value,
                'updated_at' => $now,
            ]);
            return;
        }

        $this->settings->insert([
            'class'      => self::CLASS_NAME,
            'key'        => $key,
            'value'      => $value,
            'type'       => 'string',
            'context'    => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
