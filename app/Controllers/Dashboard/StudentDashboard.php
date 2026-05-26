<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Libraries\BookingSlotService;
use App\Models\BookingApplicantModel;
use App\Models\BookingAssetModel;
use App\Models\AssetModel;
use App\Models\BookingModel;
use App\Models\FacultyModel;

class StudentDashboard extends BaseController
{
    public function index()
    {
        helper('auth');

        if (!auth()->loggedIn() || (! auth()->user()->inGroup('student') && ! auth()->user()->inGroup('staff'))) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user   = auth()->user();
        $userId = $user->id;
        $dashboardLabel = $user->inGroup('staff') ? 'Staff Dashboard' : 'Student Dashboard';

        $bookingModel = new BookingModel();
        $filters = $this->bookingFilters();

        $baseSelect = "
            bookings.*,
            laboratories.name AS lab_name,
            laboratories.room AS lab_room
        ";

        // All bookings
        $bookingsQuery = $bookingModel
            ->select($baseSelect)
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $userId)
            ->whereIn('bookings.status', BookingModel::CORE_STATUSES);

        $bookings = $this->applyBookingFilters($bookingsQuery, $filters)
            ->orderBy('bookings.date', 'DESC')
            ->orderBy('bookings.start_time', 'ASC')
            ->findAll();

        // Stats
        $stats = [
            'pending'  => (new BookingModel())->where('user_id', $userId)->where('status', 'PENDING')->countAllResults(),
            'approved' => (new BookingModel())->where('user_id', $userId)->where('status', 'APPROVED')->countAllResults(),
            'rejected' => (new BookingModel())->where('user_id', $userId)->where('status', 'REJECTED')->countAllResults(),
            'cancelled' => (new BookingModel())->where('user_id', $userId)->where('status', 'CANCELLED')->countAllResults(),
        ];
        $stats['total'] = $stats['pending'] + $stats['approved'] + $stats['rejected'] + $stats['cancelled'];

        // Upcoming bookings
        $today = date('Y-m-d');
        $upcomingBookings = $bookingModel
            ->select($baseSelect)
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $userId)
            ->whereIn('bookings.status', BookingModel::ACTIVE_STATUSES)
            ->where('bookings.date >=', $today)
            ->orderBy('bookings.date', 'ASC')
            ->orderBy('bookings.start_time', 'ASC')
            ->limit(10)
            ->findAll();

        $nextBooking = $upcomingBookings[0] ?? null;

        // Monthly trend (ONLY_FULL_GROUP_BY safe)
        $db = \Config\Database::connect();
        $monthlyRows = $db->table('bookings')
            ->select("DATE_FORMAT(date, '%b %Y') AS month, COUNT(*) AS count", false)
            ->where('user_id', $userId)
            ->whereIn('status', BookingModel::CORE_STATUSES)
            ->groupBy("DATE_FORMAT(date, '%b %Y')", false)
            ->orderBy("MIN(date)", "DESC", false)
            ->limit(6)
            ->get()
            ->getResultArray();

        $monthlyCounts = array_reverse($monthlyRows);

        // Personalized hints
        $db = \Config\Database::connect();
        $topLab = $db->table('bookings b')
            ->select('b.lab_id, laboratories.name AS lab_name, COUNT(*) AS total', false)
            ->join('laboratories', 'laboratories.id = b.lab_id', 'left')
            ->where('b.user_id', $userId)
            ->where('b.status', 'APPROVED')
            ->groupBy('b.lab_id')
            ->orderBy('total', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $timeRows = $db->table('bookings')
            ->select('start_time')
            ->where('user_id', $userId)
            ->where('status', 'APPROVED')
            ->get()
            ->getResultArray();

        $slotCounts = [];
        foreach ($timeRows as $row) {
            $slot = $this->mapTimeToSlot($row['start_time']);
            if (!$slot) continue;
            if (!isset($slotCounts[$slot])) {
                $slotCounts[$slot] = 0;
            }
            $slotCounts[$slot]++;
        }
        arsort($slotCounts);
        $topSlot = null;
        if (!empty($slotCounts)) {
            $topSlot = array_key_first($slotCounts);
        }

        $personalizedHints = [
            'lab_name' => $topLab['lab_name'] ?? null,
            'slot' => $topSlot,
        ];

        return view('dashboard/student/index', [
            'user'             => $user,
            'bookings'         => $bookings,
            'stats'            => $stats,
            'upcomingBookings' => $upcomingBookings,
            'nextBooking'      => $nextBooking,
            'monthlyCounts'    => $monthlyCounts,
            'personalizedHints'=> $personalizedHints,
            'dashboardLabel'   => $dashboardLabel,
            'filters'          => $filters,
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

    private function mapTimeToSlot(string $startTime): ?string
    {
        $time = substr(trim($startTime), 0, 5);

        if ($time >= '08:00' && $time < '10:00') return '08:00-10:00';
        if ($time >= '10:00' && $time < '12:00') return '10:00-12:00';
        if ($time >= '13:00' && $time < '15:00') return '13:00-15:00';
        if ($time >= '15:00' && $time < '17:00') return '15:00-17:00';

        return null;
    }

    // =====================================================
    // BOOKING DETAILS (MODAL AJAX)
    // =====================================================
    public function bookingDetails($id)
    {
        helper('auth');

        $userId = auth()->id();
        $bookingModel = new BookingModel();
        $bookingAssetModel = new BookingAssetModel();

        $booking = $bookingModel
            ->select("
                bookings.*,
                laboratories.name AS lab_name,
                laboratories.room AS lab_room
            ")
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.id', $id)
            ->where('bookings.user_id', $userId)
            ->first();

        if (!$booking) {
            return $this->response->setJSON(['success' => false, 'message' => 'Booking not found']);
        }

        $assets = $bookingAssetModel
            ->select("booking_assets.*, assets.name")
            ->join("assets", "assets.id = booking_assets.asset_id", "left")
            ->where("booking_id", $id)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'booking' => $booking,
            'assets'  => $assets
        ]);
    }

    // =====================================================
    // EDIT BOOKING
    // =====================================================
    public function editBooking(int $id)
    {
        helper('auth');

        $userId = auth()->id();
        $bookingModel = new BookingModel();

        $booking = $bookingModel
            ->select('bookings.*, laboratories.name AS lab_name, laboratories.room AS lab_room, lab_services.service_name')
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->join('lab_services', 'lab_services.id = bookings.service_id', 'left')
            ->where('bookings.id', $id)
            ->where('bookings.user_id', $userId)
            ->first();

        if (! $booking || $booking['status'] !== 'PENDING' || ! empty($booking['approved_by_pic'])) {
            return redirect()->back()->with('error', 'This booking cannot be edited.');
        }

        $applicants = (new BookingApplicantModel())->getForBooking($id);
        $faculties  = (new FacultyModel())->orderBy('name', 'ASC')->findAll();
        $assets     = (new BookingAssetModel())
            ->select('booking_assets.*, assets.name')
            ->join('assets', 'assets.id = booking_assets.asset_id', 'left')
            ->where('booking_id', $id)
            ->findAll();

        $slotService = new BookingSlotService();

        return view('dashboard/student/booking_edit', [
            'booking'    => $booking,
            'applicants' => $applicants,
            'faculties'  => $faculties,
            'assets'     => $assets,
            'slotDefs'   => $slotService->getDefinitions(),
        ]);
    }

    public function updateBooking(int $id)
    {
        helper('auth');

        $userId       = auth()->id();
        $bookingModel = new BookingModel();
        $slotService  = new BookingSlotService();

        $booking = $bookingModel->where('id', $id)->where('user_id', $userId)->first();

        if (! $booking || $booking['status'] !== 'PENDING' || ! empty($booking['approved_by_pic'])) {
            return redirect()->back()->with('error', 'This booking cannot be edited.');
        }

        $date      = trim((string) $this->request->getPost('date'));
        $startTime = $slotService->normalizeTime((string) $this->request->getPost('start_time'));
        $endTime   = $slotService->normalizeTime((string) $this->request->getPost('end_time'));
        $activity  = trim((string) $this->request->getPost('activity'));

        if ($date === '' || $startTime === '' || $endTime === '' || $activity === '') {
            return redirect()->back()->withInput()->with('error', 'Date, start time, end time, and activity are required.');
        }

        if ($startTime >= $endTime) {
            return redirect()->back()->withInput()->with('error', 'End time must be later than start time.');
        }

        if ($slotService->findMatchingDefinition($startTime, $endTime) === null) {
            return redirect()->back()->withInput()->with('error', 'Please choose one of the configured booking sessions for this laboratory.');
        }

        if ($bookingModel->hasLabConflict((int) $booking['lab_id'], $date, $startTime, $endTime, $id)) {
            return redirect()->back()->withInput()->with('error', 'This laboratory is already booked for the selected date and time.');
        }

        $names     = $this->request->getPost('applicant_name') ?? [];
        $ids       = $this->request->getPost('applicant_id') ?? [];
        $emails    = $this->request->getPost('applicant_email') ?? [];
        $phones    = $this->request->getPost('applicant_phone') ?? [];
        $faculties = $this->request->getPost('applicant_faculty') ?? [];

        $rowCount   = max(count((array) $names), count((array) $ids), count((array) $emails), count((array) $phones), count((array) $faculties));
        $applicants = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $name       = trim((string) ($names[$i] ?? ''));
            $matricId   = trim((string) ($ids[$i] ?? ''));
            $email      = trim((string) ($emails[$i] ?? ''));
            $phone      = trim((string) ($phones[$i] ?? ''));
            $facultyVal = trim((string) ($faculties[$i] ?? ''));
            if ($name === '' && $matricId === '' && $email === '' && $phone === '' && $facultyVal === '') continue;
            if ($name === '' || $matricId === '' || $email === '' || $phone === '' || $facultyVal === '') {
                return redirect()->back()->withInput()->with('error', 'Each applicant must include name, ID, email, phone, and faculty.');
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('error', 'One or more applicant email addresses are invalid.');
            }
            $applicants[] = ['name' => $name, 'matric_id' => $matricId, 'email' => $email, 'phone' => $phone, 'faculty' => $facultyVal];
        }

        if ($applicants === []) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one applicant.');
        }

        $pdfFile = $this->request->getFile('pdf');
        $pdfPath = (string) ($booking['pdf_path'] ?? '');

        $bookingApplicantModel = new BookingApplicantModel();
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            if ($pdfFile && $pdfFile->isValid() && ! $pdfFile->hasMoved()) {
                $uploadDir = WRITEPATH . 'uploads/pdfs';
                if (! is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $storedName = $pdfFile->getRandomName();
                if (! $pdfFile->move($uploadDir, $storedName)) {
                    throw new \RuntimeException('Unable to store uploaded PDF.');
                }
                $pdfPath = '/uploads/pdfs/' . $storedName;
            }

            $bookingModel->update($id, [
                'date'             => $date,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'activity'         => $activity,
                'supervisor_name'  => trim((string) $this->request->getPost('supervisor_name')) ?: null,
                'supervisor_email' => trim((string) $this->request->getPost('supervisor_email')) ?: null,
                'supervisor_phone' => trim((string) $this->request->getPost('supervisor_phone')) ?: null,
                'pdf_path'         => $pdfPath ?: null,
                'approved_by_pic'     => 0,
                'approved_by_manager' => 0,
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);

            $bookingApplicantModel->where('booking_id', $id)->delete();
            $bookingApplicantModel->insertBatchApplicants($id, $applicants);

            if ($db->transStatus() === false) throw new \RuntimeException('Unable to save booking.');
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Booking update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update booking. Please try again.');
        }

        $dashboardUrl = auth()->user()->inGroup('staff') ? '/dashboard/staff' : '/dashboard/student';
        return redirect()->to($dashboardUrl)->with('success', 'Booking updated successfully.');
    }

    // =====================================================
    // CANCEL BOOKING
    // =====================================================
    public function cancelBooking($id)
    {
        helper('auth');

        $userId = auth()->id();
        $bookingModel = new BookingModel();

        $booking = $bookingModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$booking) {
            return $this->response->setJSON(['success' => false, 'message' => 'Booking not found']);
        }

        if ($booking['status'] !== 'PENDING') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only pending bookings can be cancelled.']);
        }

        $bookingModel->update($id, [
            'status' => 'CANCELLED',
            'approved_by_pic' => 0,
            'approved_by_manager' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Booking cancelled successfully.'
        ]);
    }
}

