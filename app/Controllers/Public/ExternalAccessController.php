<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Models\ExternalAccessRequestModel;

class ExternalAccessController extends BaseController
{
    protected ExternalAccessRequestModel $requestModel;

    public function __construct()
    {
        $this->requestModel = new ExternalAccessRequestModel();
    }

    /** GET /external-access/request */
    public function form()
    {
        return view('public/external_access/request');
    }

    /** POST /external-access/submit */
    public function submit()
    {
        $rules = [
            'full_name'    => 'required|min_length[3]|max_length[255]',
            'email'        => 'required|valid_email|max_length[255]',
            'phone'        => 'permit_empty|max_length[50]',
            'organization' => 'required|min_length[2]|max_length[255]',
            'purpose'      => 'required|min_length[10]',
            'notes'        => 'permit_empty|string',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));

        // Prevent duplicate pending submissions.
        if ($this->requestModel->hasPendingRequest($email)) {
            return redirect()->back()->withInput()->with('error', 'A pending access request already exists for this email address. Please wait for the administrator to review it.');
        }

        // Prevent re-registering an already-approved address.
        if ($this->requestModel->hasApprovedRequest($email)) {
            return redirect()->back()->withInput()->with('error', 'An access request for this email has already been approved. Check your inbox for login details, or contact the administrator.');
        }

        // Prevent creating a duplicate user account.
        $emailExists = db_connect()
            ->table('auth_identities')
            ->where('type', 'email_password')
            ->where('LOWER(secret) =', $email)
            ->countAllResults() > 0;

        if ($emailExists) {
            return redirect()->back()->withInput()->with('error', 'An account with this email already exists. Use the login page or request a password reset.');
        }

        $requestId = (int) $this->requestModel->insert([
            'full_name'    => trim((string) $this->request->getPost('full_name')),
            'email'        => $email,
            'phone'        => trim((string) $this->request->getPost('phone')) ?: null,
            'organization' => trim((string) $this->request->getPost('organization')),
            'purpose'      => trim((string) $this->request->getPost('purpose')),
            'notes'        => trim((string) $this->request->getPost('notes')) ?: null,
            'status'       => 'pending',
        ], true);

        NotificationService::dispatchSafely(
            fn(NotificationService $svc) => $svc->notifyExternalAccessSubmitted($requestId),
            'external access request submitted'
        );

        return redirect()->to('/external-access/submitted');
    }

    /** GET /external-access/submitted */
    public function submitted()
    {
        return view('public/external_access/submitted');
    }
}
