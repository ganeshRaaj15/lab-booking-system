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
        $filters = $this->bookingFilters();

        // Fetch all bookings for this external user
        $bookingsQuery = $bookingModel
            ->select("
                bookings.*,
                laboratories.name AS lab_name,
                laboratories.room AS lab_room
            ")
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $userId)
            ->whereIn('bookings.status', BookingModel::CORE_STATUSES);

        $bookings = $this->applyBookingFilters($bookingsQuery, $filters)
            ->orderBy('bookings.date', 'DESC')
            ->orderBy('bookings.start_time', 'ASC')
            ->findAll();

        // Stats for this user
        $stats = [
            'pending'  => $bookingModel->where('user_id', $userId)->where('status', 'PENDING')->countAllResults(),
            'approved' => $bookingModel->where('user_id', $userId)->where('status', 'APPROVED')->countAllResults(),
            'rejected' => $bookingModel->where('user_id', $userId)->where('status', 'REJECTED')->countAllResults(),
            'cancelled' => $bookingModel->where('user_id', $userId)->where('status', 'CANCELLED')->countAllResults(),
        ];

        $stats['total'] = $stats['pending'] + $stats['approved'] + $stats['rejected'] + $stats['cancelled'];

        return view('dashboard/external/index', [
            'user'     => $user,
            'bookings' => $bookings,
            'stats'    => $stats,
            'monthlyCounts' => [],
            'filters' => $filters,
        ]);
    }

    private function bookingFilters(): array
    {
        $status = trim((string) $this->request->getGet('status'));
        if (! in_array($status, BookingModel::CORE_STATUSES, true)) {
            $status = '';
        }

        return [
            'q' => trim((string) $this->request->getGet('q')),
            'status' => $status,
            'date_from' => $this->validDate((string) $this->request->getGet('date_from')),
            'date_to' => $this->validDate((string) $this->request->getGet('date_to')),
        ];
    }

    private function applyBookingFilters($query, array $filters)
    {
        if ($filters['q'] !== '') {
            $query->groupStart()
                ->like('laboratories.name', $filters['q'])
                ->orLike('laboratories.room', $filters['q'])
                ->orLike('bookings.activity', $filters['q'])
                ->groupEnd();
        }
        if ($filters['status'] !== '') {
            $query->where('bookings.status', $filters['status']);
        }
        if ($filters['date_from'] !== '') {
            $query->where('bookings.date >=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->where('bookings.date <=', $filters['date_to']);
        }

        return $query;
    }

    private function validDate(string $value): string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}
