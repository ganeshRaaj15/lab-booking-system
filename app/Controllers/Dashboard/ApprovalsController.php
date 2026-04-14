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

        $userEmail = $user->email;

        // ---------------------------------------------------------------------
        // 1. Labs relevant to this approver
        // ---------------------------------------------------------------------
        if ($role === 'pic') {
            // PIC: only labs they are PIC for
             $labs = $labModel->where("TRIM(pic_email) =", trim($userEmail))->findAll();
            $labIds = array_column($labs, 'id');

            if (empty($labIds)) {
                return view('dashboard/approvals/index', [
                    'bookings' => [],
                    'role'     => $role,
                    'focusBookingId' => (int) $this->request->getGet('focus_booking'),
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
        ]);
    }
}

