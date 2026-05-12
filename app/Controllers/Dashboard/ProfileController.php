<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\FacultyModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserModel;
use Throwable;

class ProfileController extends BaseController
{
    public function index()
    {
        helper('auth');

        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        if ($user->inGroup('pic') || $user->inGroup('manager')) {
            return redirect()->to('/dashboard')->with('error', 'Profile updates for PIC and Manager roles are managed by Admin.');
        }
        $userModel = model(UserModel::class);
        $facultyModel = new FacultyModel();

        $db = \Config\Database::connect();
        $identity = $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();

        $email = $identity['secret'] ?? '';
        $faculties = $facultyModel->getAllForDropdown();

        $layout = 'layouts/main_user';
        $backUrl = '/dashboard';
        $title = 'My Profile | FKMP Smart Lab';

        if ($user->inGroup('admin')) {
            $layout = 'layouts/main_admin';
            $backUrl = '/dashboard/admin';
        } elseif ($user->inGroup('technician')) {
            $layout = 'layouts/main_technician';
            $backUrl = '/dashboard/technician';
            $title = 'Technician Profile | FKMP Smart Lab';
        }

        return view('dashboard/profile/index', [
            'layout' => $layout,
            'title' => $title,
            'roleLabel' => $user->inGroup('technician') ? 'Technician' : null,
            'backUrl' => $backUrl,
            'user' => $userModel->findById($user->id),
            'email' => $email,
            'faculties' => $faculties,
            'page' => 'Profile',
        ]);
    }

    public function update(): RedirectResponse
    {
        helper('auth');

        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        if ($user->inGroup('pic') || $user->inGroup('manager')) {
            return redirect()->to('/dashboard')->with('error', 'Profile updates for PIC and Manager roles are managed by Admin.');
        }
        $post = $this->request->getPost();

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
            'full_name' => 'permit_empty|max_length[120]',
            'phone' => 'permit_empty|max_length[40]',
            'faculty_id' => 'permit_empty|integer',
            'email' => 'required|valid_email|max_length[255]',
            'password' => 'permit_empty|min_length[8]',
            'password_confirm' => 'matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $email = strtolower(trim((string) $post['email']));

        // Email uniqueness check
        $existingEmail = $db->table('auth_identities')
            ->where('type', 'email_password')
            ->where('LOWER(secret) =', $email)
            ->where('user_id !=', $user->id)
            ->countAllResults();

        if ($existingEmail > 0) {
            return redirect()->back()->withInput()->with('errors', ['Email already exists.']);
        }

        // Username uniqueness check
        $existingUsername = $db->table('users')
            ->where('username', trim($post['username']))
            ->where('id !=', $user->id)
            ->countAllResults();

        if ($existingUsername > 0) {
            return redirect()->back()->withInput()->with('errors', ['Username already exists.']);
        }

        $photoUpload = $this->handleProfilePhotoUpload();
        if ($photoUpload['error'] !== null) {
            return redirect()->back()->withInput()->with('errors', [$photoUpload['error']]);
        }
        $photoPath = $photoUpload['path'];

        $updateData = [
            'username' => trim($post['username']),
            'full_name' => $post['full_name'] !== '' ? trim($post['full_name']) : null,
            'phone' => $post['phone'] !== '' ? trim($post['phone']) : null,
            'faculty_id' => $post['faculty_id'] !== '' ? (int)$post['faculty_id'] : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($photoPath) {
            $updateData['profile_photo'] = $photoPath;
        }

        $db->table('users')
            ->where('id', $user->id)
            ->update($updateData);

        $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->set('secret', $email)
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();

        if (! empty($post['password'])) {
            $db->table('auth_identities')
                ->where('user_id', $user->id)
                ->where('type', 'email_password')
                ->set('secret2', password_hash($post['password'], PASSWORD_DEFAULT))
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->update();
        }

        return redirect()->to('/dashboard/profile')->with('message', 'Profile updated successfully.');
    }

    /**
     * @return array{path: string|null, error: string|null}
     */
    protected function handleProfilePhotoUpload(): array
    {
        $photoFile = $this->request->getFile('profile_photo');
        if (! $photoFile || $photoFile->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        if (! $photoFile->isValid()) {
            return ['path' => null, 'error' => $photoFile->getErrorString()];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($photoFile->getMimeType(), $allowedTypes, true)) {
            return ['path' => null, 'error' => 'Profile photo must be a JPG, PNG, or WEBP image.'];
        }

        $destination = rtrim(FCPATH, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'users';
        if (is_file($destination)) {
            return ['path' => null, 'error' => 'Profile photo upload path is blocked by a file on the server.'];
        }

        if (! is_dir($destination) && ! @mkdir($destination, 0775, true) && ! is_dir($destination)) {
            return ['path' => null, 'error' => 'Profile photo upload directory could not be created on the server.'];
        }

        if (! is_writable($destination)) {
            @chmod($destination, 0775);
        }

        if (! is_writable($destination)) {
            return ['path' => null, 'error' => 'Profile photo upload directory is not writable on the server.'];
        }

        $newName = $photoFile->getRandomName();

        try {
            $photoFile->move($destination, $newName);
        } catch (Throwable $exception) {
            return ['path' => null, 'error' => 'Unable to store the profile photo on the server: ' . $exception->getMessage()];
        }

        return ['path' => 'images/users/' . $newName, 'error' => null];
    }
}

