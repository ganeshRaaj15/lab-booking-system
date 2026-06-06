<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AccountRecoveryService;
use App\Libraries\NotificationService;
use App\Models\ExternalAccessRequestModel;

class ExternalAccessAdminController extends BaseController
{
    protected ExternalAccessRequestModel $requestModel;

    public function __construct()
    {
        helper('auth');
        if (! auth()->loggedIn() || ! auth()->user()->inGroup('admin')) {
            redirect()->to('/')->send();
            exit;
        }
        $this->requestModel = new ExternalAccessRequestModel();
    }

    /** GET /admin/external-access */
    public function index()
    {
        $status = trim((string) $this->request->getGet('status'));
        if (! \in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = '';
        }

        $query = $this->requestModel->orderBy('created_at', 'DESC');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $perPage  = 15;
        $requests = $query->paginate($perPage);
        $pager    = $this->requestModel->pager;

        $counts = [];
        foreach (['pending', 'approved', 'rejected'] as $s) {
            $counts[$s] = (int) $this->requestModel->where('status', $s)->countAllResults();
        }

        return view('admin/external_access/index', [
            'requests' => $requests,
            'pager'    => $pager,
            'filters'  => ['status' => $status],
            'counts'   => $counts,
        ]);
    }

    /** GET /admin/external-access/:id */
    public function show(int $id)
    {
        $req = $this->requestModel->find($id);
        if (! $req) {
            return redirect()->to('/admin/external-access')->with('error', 'Request not found.');
        }

        return view('admin/external_access/show', ['req' => $req]);
    }

    /** POST /admin/external-access/:id/approve */
    public function approve(int $id)
    {
        $req = $this->requestModel->find($id);
        if (! $req) {
            return redirect()->to('/admin/external-access')->with('error', 'Request not found.');
        }
        if ($req['status'] !== 'pending') {
            return redirect()->to('/admin/external-access')->with('error', 'Only pending requests can be approved.');
        }

        $email = strtolower(trim((string) $req['email']));
        $db    = db_connect();

        // Prevent creating a duplicate account.
        $emailExists = $db->table('auth_identities')
            ->where('type', 'email_password')
            ->where('LOWER(secret) =', $email)
            ->countAllResults() > 0;

        if ($emailExists) {
            $this->requestModel->update($id, [
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);
            return redirect()->to('/admin/external-access')->with('message', 'Request marked approved. Note: an account with this email already exists — no new account was created.');
        }

        // Generate username from email local-part, ensure uniqueness.
        $baseUsername = preg_replace('/[^a-z0-9_]/', '_', explode('@', $email)[0]);
        $username     = $baseUsername;
        $suffix       = 1;
        while ($db->table('users')->where('username', $username)->countAllResults() > 0) {
            $username = $baseUsername . '_' . $suffix++;
        }

        // Generate a random temporary password.
        $tempPassword = bin2hex(random_bytes(8));

        $db->transStart();

        $db->table('users')->insert([
            'username'   => $username,
            'active'     => 1,
            'full_name'  => $req['full_name'],
            'phone'      => $req['phone'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $userId = $db->insertID();

        $db->table('auth_identities')->insert([
            'user_id'    => $userId,
            'type'       => 'email_password',
            'secret'     => $email,
            'secret2'    => password_hash($tempPassword, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => 'external',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->requestModel->update($id, [
            'status'          => 'approved',
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => date('Y-m-d H:i:s'),
            'created_user_id' => $userId,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'ExternalAccessAdminController: failed to create account for request #' . $id);
            return redirect()->to('/admin/external-access')->with('error', 'Database error while creating the external account. Please try again.');
        }

        // Send login credentials via email if the recovery service is available.
        try {
            $userModel = model(\CodeIgniter\Shield\Models\UserModel::class);
            $newUser   = $userModel->findById($userId);
            if ($newUser) {
                (new AccountRecoveryService())->sendLoginLink($newUser);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'ExternalAccessAdminController: recovery email failed — ' . $e->getMessage());
        }

        return redirect()->to('/admin/external-access')->with('message', 'Request approved and external account created. A login link has been sent to ' . esc($email) . '.');
    }

    /** POST /admin/external-access/:id/reject */
    public function reject(int $id)
    {
        $req = $this->requestModel->find($id);
        if (! $req) {
            return redirect()->to('/admin/external-access')->with('error', 'Request not found.');
        }
        if ($req['status'] !== 'pending') {
            return redirect()->to('/admin/external-access')->with('error', 'Only pending requests can be rejected.');
        }

        $reason = trim((string) $this->request->getPost('rejection_reason'));

        $this->requestModel->update($id, [
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason ?: null,
        ]);

        NotificationService::dispatchSafely(
            fn(NotificationService $svc) => $svc->notifyExternalAccessRejected($id, $reason),
            'external access request rejected'
        );

        return redirect()->to('/admin/external-access')->with('message', 'Request rejected.');
    }
}
