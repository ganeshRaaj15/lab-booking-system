<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FacultyModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class UserManagementController extends BaseController
{
    protected $users;
    protected BaseConnection $db;
    protected FacultyModel $faculties;
    protected array $allowedRoles = ['student', 'external', 'technician', 'pic', 'manager', 'admin'];

    public function __construct()
    {
        helper('auth');
        $this->users = model(UserModel::class);
        $this->db = db_connect();
        $this->faculties = new FacultyModel();
    }

    public function index()
    {
        $users = $this->users->findAll();
        $userData = [];

        foreach ($users as $u) {
            $email = $this->getEmailForUser((int) $u->id);
            $roles = $this->db->table('auth_groups_users')->where('user_id', $u->id)->get()->getResultArray();
            $userData[] = [
                'id' => $u->id,
                'username' => $u->username,
                'email' => $email,
                'roles' => array_column($roles, 'group'),
                'active' => $u->active,
                'full_name' => $u->full_name,
                'phone' => $u->phone,
            ];
        }

        return view('admin/users/index', ['users' => $userData]);
    }

    public function edit($id)
    {
        $user = $this->users->findById($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $roles = $this->db->table('auth_groups_users')->where('user_id', $user->id)->get()->getResultArray();

        return view('admin/users/edit', [
            'user' => $user,
            'email' => $this->getEmailForUser((int) $user->id),
            'roles' => array_column($roles, 'group'),
            'allRoles' => $this->allowedRoles,
            'faculties' => $this->faculties->getAllForDropdown(),
        ]);
    }

    public function update($id)
    {
        $user = $this->users->findById($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $data = $this->request->getPost();
        $username = trim((string) ($data['username'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirm = (string) ($data['password_confirm'] ?? '');
        $facultyId = ($data['faculty_id'] ?? '') !== '' ? (int) $data['faculty_id'] : null;
        $active = isset($data['active']) ? (int) $data['active'] : (int) $user->active;
        $newRoles = array_values(array_intersect($data['roles'] ?? [], $this->allowedRoles));
        $currentEmail = $this->getEmailForUser((int) $user->id);

        if ($username === '' || $email === '') {
            return redirect()->back()->withInput()->with('error', 'Username and email cannot be empty.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid email address.');
        }
        if ($password !== '' && strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters long.');
        }
        if ($password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('error', 'Passwords do not match.');
        }
        if ($newRoles === []) {
            return redirect()->back()->withInput()->with('error', 'Assign at least one role to the user.');
        }
        if (in_array('student', $newRoles, true) && ! $facultyId) {
            return redirect()->back()->withInput()->with('error', 'Student accounts must have a faculty assigned for booking approval routing.');
        }

        $usernameExists = $this->db->table('users')->where('username', $username)->where('id !=', $user->id)->countAllResults();
        if ($usernameExists > 0) {
            return redirect()->back()->withInput()->with('error', 'Username already exists.');
        }

        $emailExists = $this->db->table('auth_identities')->where('type', 'email_password')->where('secret', $email)->where('user_id !=', $user->id)->countAllResults();
        if ($emailExists > 0) {
            return redirect()->back()->withInput()->with('error', 'Email already exists.');
        }

        if ($this->isPicEmailInUse($currentEmail) && $email !== $currentEmail) {
            return redirect()->back()->withInput()->with('error', 'This user is currently assigned as a laboratory PIC. Update the laboratory PIC email first before changing this account email.');
        }
        if ($this->isPicEmailInUse($currentEmail) && ! in_array('pic', $newRoles, true)) {
            return redirect()->back()->withInput()->with('error', 'This user is currently assigned as a laboratory PIC. Update the laboratory record before removing the PIC role.');
        }

        $user->username = $username;
        $this->users->save($user);

        $this->db->table('users')->where('id', $user->id)->update([
            'username' => $username,
            'full_name' => trim((string) ($data['full_name'] ?? '')) ?: null,
            'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
            'faculty_id' => $facultyId,
            'active' => $active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('auth_identities')->where('user_id', $user->id)->where('type', 'email_password')->set('secret', $email)->set('updated_at', date('Y-m-d H:i:s'))->update();

        if ($password !== '') {
            $this->db->table('auth_identities')->where('user_id', $user->id)->where('type', 'email_password')->set('secret2', password_hash($password, PASSWORD_DEFAULT))->set('updated_at', date('Y-m-d H:i:s'))->update();
        }

        $this->db->table('auth_groups_users')->where('user_id', $user->id)->delete();
        $now = date('Y-m-d H:i:s');
        foreach ($newRoles as $role) {
            $this->db->table('auth_groups_users')->insert(['user_id' => $user->id, 'group' => $role, 'created_at' => $now]);
        }

        return redirect()->to('/admin/users')->with('message', 'User updated successfully.');
    }

    public function delete($id)
    {
        $user = $this->users->findById($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $email = $this->getEmailForUser((int) $user->id);
        $adminCheck = $this->db->table('auth_groups_users')->where('user_id', $user->id)->whereIn('group', ['admin', 'manager'])->countAllResults() > 0;
        if ($adminCheck) {
            return redirect()->back()->with('error', 'Cannot delete Admin or Manager accounts.');
        }
        if ($this->isPicEmailInUse($email)) {
            return redirect()->back()->with('error', 'Cannot delete this user while they are assigned as a laboratory PIC. Update the laboratory record first.');
        }
        if ($this->hasOperationalLinks((int) $user->id)) {
            return redirect()->back()->with('error', 'Cannot delete this user because linked booking or maintenance records exist. Deactivate the account instead.');
        }

        $this->db->table('auth_identities')->where('user_id', $user->id)->delete();
        $this->db->table('auth_groups_users')->where('user_id', $user->id)->delete();
        $this->users->delete($user->id);

        return redirect()->to('/admin/users')->with('message', 'User deleted successfully.');
    }

    public function create()
    {
        return view('admin/users/create', [
            'allRoles' => $this->allowedRoles,
            'faculties' => $this->faculties->getAllForDropdown(),
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $username = trim((string) ($data['username'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirm = (string) ($data['password_confirm'] ?? '');
        $newRoles = array_values(array_intersect($data['roles'] ?? [], $this->allowedRoles));
        $facultyId = ($data['faculty_id'] ?? '') !== '' ? (int) $data['faculty_id'] : null;

        if ($username === '' || $email === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Username, email and password are required.');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Please provide a valid email address.');
        }
        if (strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', 'Password must be at least 8 characters long.');
        }
        if ($password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('error', 'Passwords do not match.');
        }
        if ($newRoles === []) {
            return redirect()->back()->withInput()->with('error', 'Assign at least one role to the user.');
        }
        if (in_array('student', $newRoles, true) && ! $facultyId) {
            return redirect()->back()->withInput()->with('error', 'Student accounts must have a faculty assigned for booking approval routing.');
        }

        $existingUser = $this->db->table('users')->where('username', $username)->countAllResults() > 0;
        if ($existingUser) {
            return redirect()->back()->withInput()->with('error', 'Username already exists.');
        }
        $emailExists = $this->db->table('auth_identities')->where('secret', $email)->where('type', 'email_password')->countAllResults() > 0;
        if ($emailExists) {
            return redirect()->back()->withInput()->with('error', 'Email already exists.');
        }

        $this->db->transStart();
        try {
            $this->db->table('users')->insert([
                'username' => $username,
                'active' => 1,
                'full_name' => trim((string) ($data['full_name'] ?? '')) ?: null,
                'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
                'faculty_id' => $facultyId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
            if (empty($userId)) {
                throw new \RuntimeException('Failed to get user ID from database insert');
            }

            $this->db->table('auth_identities')->insert([
                'user_id' => $userId,
                'type' => 'email_password',
                'secret' => $email,
                'secret2' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $now = date('Y-m-d H:i:s');
            foreach ($newRoles as $role) {
                $this->db->table('auth_groups_users')->insert(['user_id' => $userId, 'group' => $role, 'created_at' => $now]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }

            return redirect()->to('/admin/users')->with('message', 'User created successfully.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'User creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    protected function getEmailForUser(int $userId): string
    {
        $identity = $this->db->table('auth_identities')->where('user_id', $userId)->where('type', 'email_password')->get()->getRow();
        return $identity->secret ?? '';
    }

    protected function isPicEmailInUse(string $email): bool
    {
        if ($email === '') {
            return false;
        }
        return $this->db->table('laboratories')->where('pic_email', $email)->countAllResults() > 0;
    }

    protected function hasOperationalLinks(int $userId): bool
    {
        $bookingCount = $this->db->table('bookings')->where('user_id', $userId)->countAllResults();
        $reportedCount = $this->db->table('maintenance_records')->where('reported_by', $userId)->countAllResults();
        $assignedCount = $this->db->table('maintenance_records')->where('assigned_technician_id', $userId)->countAllResults();
        return ($bookingCount + $reportedCount + $assignedCount) > 0;
    }
}