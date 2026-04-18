<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\LaboratoryModel;

class ApprovalsController extends BaseController
{
    public function index()
    {
        helper('auth');

        // Must be logged in
        if (! auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please log in.');
        }

        $user = auth()->user();

        // Determine role
        if ($user->inGroup('pic')) {
            $role = 'pic';
        } elseif ($user->inGroup('manager')) {
            $role = 'manager';
        } elseif ($user->inGroup('admin')) {
            $role = 'admin';
        } else {
            return redirect()->to('/dashboard')
                ->with('error', 'You do not have access to approvals.');
        }

        $bookingModel = new BookingModel();
        $labModel     = new LaboratoryModel();
        $filters = [
            'q' => trim((string) $this->request->getGet('q')),
            'date_from' => $this->validDate((string) $this->request->getGet('date_from')),
            'date_to' => $this->validDate((string) $this->request->getGet('date_to')),
        ];

        $userEmail = strtolower(trim((string) $user->email));

        // ---------------------------------------------------------------------
        // 1. Labs relevant to this approver
        // ---------------------------------------------------------------------
        if ($role === 'pic') {
            // PIC: only labs they are PIC for
             $labs = $labModel->where("LOWER(TRIM(pic_email)) =", $userEmail)->findAll();
            $labIds = array_column($labs, 'id');

            if (empty($labIds)) {
                return view('dashboard/approvals/index', [
                    'bookings' => [],
                    'role'     => $role,
                    'focusBookingId' => (int) $this->request->getGet('focus_booking'),
                    'filters' => $filters,
                ]);
            }
        } else {
            // Manager/Admin: lab ownership is not restricted here
            $labs   = $labModel->findAll();
            $labIds = array_column($labs, 'id');
        }

        // ---------------------------------------------------------------------
        // 2. Base query: we always work from bookings + user + lab
        // ---------------------------------------------------------------------
        $builder = $bookingModel
            ->select("
                bookings.*,
                users.username,
                laboratories.name AS lab_name,
                laboratories.room AS lab_room
            ")
            ->join('users', 'users.id = bookings.user_id', 'left')
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left');

        // ---------------------------------------------------------------------
        // 3. Role-specific filtering
        // ---------------------------------------------------------------------

        if ($role === 'pic') {
            // PIC: see only bookings for their labs that are pending PIC approval
            $builder
                ->whereIn('bookings.lab_id', $labIds)
                ->where('bookings.status', 'PENDING')
                ->where('bookings.approved_by_pic', 0);
        } else {
            // Manager or Admin: see only bookings that require manager approval
            // i.e. non-FKMP bookings, PIC already approved, manager not yet
            $builder
                ->where('bookings.status', 'PENDING')
                ->where('bookings.approved_by_pic', 1)
                ->where('bookings.approved_by_manager', 0)
                ->where('bookings.approval_flow !=', 'FKMP_APPROVAL');
        }

        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('users.username', $filters['q'])
                ->orLike('laboratories.name', $filters['q'])
                ->orLike('laboratories.room', $filters['q'])
                ->orLike('bookings.activity', $filters['q'])
                ->groupEnd();
        }
        if ($filters['date_from'] !== '') {
            $builder->where('bookings.date >=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $builder->where('bookings.date <=', $filters['date_to']);
        }

        // ---------------------------------------------------------------------
        // 4. Fetch results
        // ---------------------------------------------------------------------
        $bookings = $builder
            ->orderBy('bookings.date', 'ASC')
            ->orderBy('bookings.start_time', 'ASC')
            ->findAll();

        // ---------------------------------------------------------------------
        // 5. Render approvals page
        // ---------------------------------------------------------------------
        return view('dashboard/approvals/index', [
            'bookings' => $bookings,
            'role'     => $role,
            'focusBookingId' => (int) $this->request->getGet('focus_booking'),
            'filters' => $filters,
        ]);
    }

    private function validDate(string $value): string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}

