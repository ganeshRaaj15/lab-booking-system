<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\BookingModel;

class ExternalDashboard extends BaseController
{
    public function index()
    {
        helper('auth');

        if (!auth()->loggedIn() || !auth()->user()->inGroup('external')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user   = auth()->user();
        $userId = $user->id;

        $bookingModel = new BookingModel();

        // Fetch all bookings for this external user
        $bookings = $bookingModel
            ->select("
                bookings.*,
                laboratories.name AS lab_name,
                laboratories.room AS lab_room
            ")
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $userId)
            ->whereIn('bookings.status', ['PENDING', 'APPROVED', 'REJECTED'])
            ->orderBy('bookings.date', 'DESC')
            ->orderBy('bookings.start_time', 'ASC')
            ->findAll();

        // Stats for this user
        $stats = [
            'pending'  => $bookingModel->where('user_id', $userId)->where('status', 'PENDING')->countAllResults(),
            'approved' => $bookingModel->where('user_id', $userId)->where('status', 'APPROVED')->countAllResults(),
            'rejected' => $bookingModel->where('user_id', $userId)->where('status', 'REJECTED')->countAllResults(),
        ];

        $stats['total'] = $stats['pending'] + $stats['approved'] + $stats['rejected'];

        return view('dashboard/external/index', [
            'user'     => $user,
            'bookings' => $bookings,
            'stats'    => $stats,
        ]);
    }
}
