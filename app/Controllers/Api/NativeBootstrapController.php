<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\AssetIntelligenceService;
use App\Libraries\NativeUserSerializer;
use App\Models\BookingModel;
use App\Models\ExternalRequestModel;
use App\Models\LaboratoryModel;
use App\Models\MaintenanceRecordModel;
use App\Models\NotificationModel;
use CodeIgniter\Shield\Entities\User;

class NativeBootstrapController extends BaseController
{
    protected NativeUserSerializer $serializer;

    public function __construct()
    {
        helper('auth');
        $this->serializer = new NativeUserSerializer();
    }

    public function show()
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ]);
        }

        $serializedUser = $this->serializer->serialize($user);
        $role = (string) ($serializedUser['primary_role'] ?? 'student');

        return $this->response->setJSON([
            'status' => 'success',
            'user' => $serializedUser,
            'navigation' => $this->navigationForRole($role),
            'summary' => $this->summaryForRole($user, $role),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function navigationForRole(string $role): array
    {
        return match ($role) {
            'external' => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'labs', 'label' => 'Labs'],
                ['id' => 'requests', 'label' => 'Requests'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
            'student', 'staff' => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'labs', 'label' => 'Labs'],
                ['id' => 'bookings', 'label' => 'Bookings'],
                ['id' => 'issues', 'label' => 'Issues'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
            'pic' => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'labs', 'label' => 'Labs'],
                ['id' => 'issues', 'label' => 'Issues'],
                ['id' => 'maintenance', 'label' => 'Maintenance'],
                ['id' => 'approvals', 'label' => 'Approvals'],
                ['id' => 'requests', 'label' => 'External'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
            'manager' => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'labs', 'label' => 'Labs'],
                ['id' => 'approvals', 'label' => 'Approvals'],
                ['id' => 'reports', 'label' => 'Reports'],
                ['id' => 'requests', 'label' => 'External'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
            'admin' => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'reports', 'label' => 'Reports'],
                ['id' => 'admin', 'label' => 'Admin'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
            default => [
                ['id' => 'home', 'label' => 'Home'],
                ['id' => 'labs', 'label' => 'Labs'],
                ['id' => 'notifications', 'label' => 'Alerts'],
                ['id' => 'profile', 'label' => 'Profile'],
            ],
        };
    }

    protected function summaryForRole(User $user, string $role): array
    {
        $notificationCount = (new NotificationModel())
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->countAllResults();

        return match ($role) {
            'external' => $this->externalSummary($user, $notificationCount),
            'staff' => $this->staffSummary($user, $notificationCount),
            'pic' => $this->picSummary($user, $notificationCount),
            'manager' => $this->managerSummary($notificationCount),
            'admin' => $this->adminSummary($notificationCount),
            default => $this->studentSummary($user, $notificationCount),
        };
    }

    protected function studentSummary(User $user, int $notificationCount): array
    {
        $countsRow = db_connect()
            ->table('bookings')
            ->select("
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) AS approved
            ")
            ->where('user_id', $user->id)
            ->get()
            ->getRowArray() ?? [];

        $pending        = (int) ($countsRow['pending'] ?? 0);
        $approved       = (int) ($countsRow['approved'] ?? 0);
        $activeBookings = $pending + $approved;

        $bookingModel = new BookingModel();
        $nextBooking  = $bookingModel
            ->select('bookings.id, bookings.date, bookings.start_time, bookings.end_time, bookings.status, laboratories.name AS lab_name, laboratories.room AS lab_room')
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $user->id)
            ->whereIn('bookings.status', BookingModel::ACTIVE_STATUSES)
            ->where('bookings.date >=', date('Y-m-d'))
            ->orderBy('bookings.date', 'ASC')
            ->orderBy('bookings.start_time', 'ASC')
            ->first();

        return [
            'role' => 'student',
            'attention_count' => max($activeBookings, $notificationCount),
            'attention_label' => $activeBookings > 0 ? $activeBookings . ' active booking(s)' : 'Ready to book',
            'attention_meta' => 'Track reservations, approvals, and lab activity in one place.',
            'stats' => [
                ['id' => 'active_bookings', 'label' => 'Active', 'value' => $activeBookings, 'tone' => 'primary'],
                ['id' => 'pending', 'label' => 'Pending', 'value' => $pending, 'tone' => 'warning'],
                ['id' => 'approved', 'label' => 'Approved', 'value' => $approved, 'tone' => 'success'],
                ['id' => 'notifications', 'label' => 'Alerts', 'value' => $notificationCount, 'tone' => 'neutral'],
            ],
            'next_item' => $nextBooking ? [
                'type' => 'booking',
                'title' => (string) ($nextBooking['lab_name'] ?? 'Upcoming Booking'),
                'subtitle' => trim(((string) ($nextBooking['date'] ?? '')) . '  ' . substr((string) ($nextBooking['start_time'] ?? ''), 0, 5) . '-' . substr((string) ($nextBooking['end_time'] ?? ''), 0, 5)),
                'meta' => trim((string) ($nextBooking['lab_room'] ?? '')),
            ] : null,
            'message' => 'View your bookings, report issues, and monitor approval updates from your dashboard.',
        ];
    }

    protected function staffSummary(User $user, int $notificationCount): array
    {
        $countsRow = db_connect()
            ->table('bookings')
            ->select("
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) AS approved
            ")
            ->where('user_id', $user->id)
            ->get()
            ->getRowArray() ?? [];

        $pending        = (int) ($countsRow['pending'] ?? 0);
        $approved       = (int) ($countsRow['approved'] ?? 0);
        $activeBookings = $pending + $approved;

        $bookingModel = new BookingModel();
        $nextBooking  = $bookingModel
            ->select('bookings.id, bookings.date, bookings.start_time, bookings.end_time, bookings.status, laboratories.name AS lab_name, laboratories.room AS lab_room')
            ->join('laboratories', 'laboratories.id = bookings.lab_id', 'left')
            ->where('bookings.user_id', $user->id)
            ->whereIn('bookings.status', BookingModel::ACTIVE_STATUSES)
            ->where('bookings.date >=', date('Y-m-d'))
            ->orderBy('bookings.date', 'ASC')
            ->orderBy('bookings.start_time', 'ASC')
            ->first();

        return [
            'role' => 'staff',
            'attention_count' => max($activeBookings, $notificationCount),
            'attention_label' => $activeBookings > 0 ? $activeBookings . ' active booking(s)' : 'Ready to book',
            'attention_meta' => 'Track your lab reservations, approvals, and activity from your staff dashboard.',
            'stats' => [
                ['id' => 'active_bookings', 'label' => 'Active', 'value' => $activeBookings, 'tone' => 'primary'],
                ['id' => 'pending', 'label' => 'Pending', 'value' => $pending, 'tone' => 'warning'],
                ['id' => 'approved', 'label' => 'Approved', 'value' => $approved, 'tone' => 'success'],
                ['id' => 'notifications', 'label' => 'Alerts', 'value' => $notificationCount, 'tone' => 'neutral'],
            ],
            'next_item' => $nextBooking ? [
                'type' => 'booking',
                'title' => (string) ($nextBooking['lab_name'] ?? 'Upcoming Booking'),
                'subtitle' => trim(((string) ($nextBooking['date'] ?? '')) . '  ' . substr((string) ($nextBooking['start_time'] ?? ''), 0, 5) . '-' . substr((string) ($nextBooking['end_time'] ?? ''), 0, 5)),
                'meta' => trim((string) ($nextBooking['lab_room'] ?? '')),
            ] : null,
            'message' => 'View your bookings, report issues, and monitor approval updates from your staff dashboard.',
        ];
    }

    protected function externalSummary(User $user, int $notificationCount): array
    {
        $requestModel = new ExternalRequestModel();
        $activeStatuses = ['pending_pic_approval', 'pending_manager_approval', 'needs_information', 'approved_for_scheduling'];
        $activeRequests = (int) $requestModel
            ->where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->countAllResults();
        $needsInfo = (int) (new ExternalRequestModel())
            ->where('user_id', $user->id)
            ->where('status', 'needs_information')
            ->countAllResults();
        $approvedForScheduling = (int) (new ExternalRequestModel())
            ->where('user_id', $user->id)
            ->where('status', 'approved_for_scheduling')
            ->countAllResults();

        $latest = $requestModel
            ->select('external_requests.id, external_requests.status, external_requests.preferred_date, laboratories.name AS lab_name, laboratories.room AS lab_room')
            ->join('laboratories', 'laboratories.id = external_requests.lab_id', 'left')
            ->where('external_requests.user_id', $user->id)
            ->orderBy('external_requests.updated_at', 'DESC')
            ->first();

        return [
            'role' => 'external',
            'attention_count' => max($activeRequests, $notificationCount),
            'attention_label' => $activeRequests > 0 ? $activeRequests . ' active request(s)' : 'Request flow clear',
            'attention_meta' => 'External users request lab access here instead of booking slots directly.',
            'stats' => [
                ['id' => 'active_requests', 'label' => 'Active', 'value' => $activeRequests, 'tone' => 'primary'],
                ['id' => 'needs_information', 'label' => 'Needs Info', 'value' => $needsInfo, 'tone' => 'warning'],
                ['id' => 'approved_for_scheduling', 'label' => 'Approved', 'value' => $approvedForScheduling, 'tone' => 'success'],
                ['id' => 'notifications', 'label' => 'Alerts', 'value' => $notificationCount, 'tone' => 'neutral'],
            ],
            'next_item' => $latest ? [
                'type' => 'external_request',
                'title' => (string) ($latest['lab_name'] ?? 'Latest Request'),
                'subtitle' => $requestModel->statusLabel((string) ($latest['status'] ?? 'pending_pic_approval')),
                'meta' => trim(((string) ($latest['preferred_date'] ?? '')) . '  ' . ((string) ($latest['lab_room'] ?? ''))),
            ] : null,
            'message' => 'Requests stay in review until staff schedule the final booking internally.',
        ];
    }

    protected function picSummary(User $user, int $notificationCount): array
    {
        $labIds = $this->picLabIds((string) $user->email);

        $pendingPic = $labIds === [] ? 0 : (int) (new BookingModel())
            ->whereIn('lab_id', $labIds)
            ->where('status', 'PENDING')
            ->where('approved_by_pic', 0)
            ->countAllResults();

        $pendingManager = $labIds === [] ? 0 : (int) (new BookingModel())
            ->whereIn('lab_id', $labIds)
            ->where('status', 'PENDING')
            ->where('approved_by_pic', 1)
            ->where('approved_by_manager', 0)
            ->where('approval_flow !=', 'FKMP_APPROVAL')
            ->countAllResults();

        $externalReview = $labIds === [] ? 0 : (int) (new ExternalRequestModel())
            ->whereIn('lab_id', $labIds)
            ->where('status', 'pending_pic_approval')
            ->countAllResults();

        $openMaintenance = 0;
        if ($labIds !== []) {
            $openStatuses = (new MaintenanceRecordModel())->openStatuses();
            $openMaintenance = (int) db_connect()
                ->table('maintenance_records mr')
                ->join('assets pic_a', 'pic_a.id = mr.asset_id', 'inner')
                ->whereIn('pic_a.lab_id', $labIds)
                ->whereIn('mr.status', $openStatuses)
                ->countAllResults();
        }

        $totalPending = $pendingPic + $externalReview;

        return [
            'role' => 'pic',
            'attention_count' => $totalPending + $openMaintenance,
            'attention_label' => ($totalPending + $openMaintenance) > 0 ? ($totalPending + $openMaintenance) . ' items waiting' : 'Queue is clear',
            'attention_meta' => 'Approve bookings, review external intake, and manage maintenance for your assigned laboratories.',
            'stats' => [
                ['id' => 'pending_pic', 'label' => 'Need PIC', 'value' => $pendingPic, 'tone' => 'warning'],
                ['id' => 'pending_manager', 'label' => 'Mgr Handoff', 'value' => $pendingManager, 'tone' => 'primary'],
                ['id' => 'external_review', 'label' => 'External', 'value' => $externalReview, 'tone' => 'accent'],
                ['id' => 'open_maintenance', 'label' => 'Maintenance', 'value' => $openMaintenance, 'tone' => 'danger'],
            ],
            'next_item' => null,
            'message' => 'Approvals, issue reporting, external requests, maintenance cases, and reports are available for your assigned laboratories.',
        ];
    }

    protected function managerSummary(int $notificationCount): array
    {
        $pendingManager = (int) (new BookingModel())
            ->where('status', 'PENDING')
            ->where('approved_by_pic', 1)
            ->where('approved_by_manager', 0)
            ->where('approval_flow !=', 'FKMP_APPROVAL')
            ->countAllResults();

        $externalReview = (int) (new ExternalRequestModel())
            ->where('status', 'pending_manager_approval')
            ->countAllResults();

        $upcomingApproved = (int) (new BookingModel())
            ->where('status', 'APPROVED')
            ->where('date >=', date('Y-m-d'))
            ->where('date <=', date('Y-m-d', strtotime('+7 days')))
            ->countAllResults();

        return [
            'role' => 'manager',
            'attention_count' => $pendingManager + $externalReview,
            'attention_label' => ($pendingManager + $externalReview) > 0 ? ($pendingManager + $externalReview) . ' reviews pending' : 'Manager queue is clear',
            'attention_meta' => 'Handle approvals, monitor demand, and review external requests.',
            'stats' => [
                ['id' => 'pending_manager', 'label' => 'Pending', 'value' => $pendingManager, 'tone' => 'warning'],
                ['id' => 'external_review', 'label' => 'External', 'value' => $externalReview, 'tone' => 'accent'],
                ['id' => 'upcoming_approved', 'label' => 'Next 7 Days', 'value' => $upcomingApproved, 'tone' => 'success'],
                ['id' => 'notifications', 'label' => 'Alerts', 'value' => $notificationCount, 'tone' => 'neutral'],
            ],
            'next_item' => null,
            'message' => 'Use reports and review queues to manage cross-laboratory scheduling and oversight.',
        ];
    }

    protected function adminSummary(int $_notificationCount): array
    {
        $db = db_connect();

        $bookingCounts = $db->table('bookings')
            ->select("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) AS approved
            ")
            ->get()
            ->getRowArray() ?? [];

        $totalBookings    = (int) ($bookingCounts['total'] ?? 0);
        $approvedBookings = (int) ($bookingCounts['approved'] ?? 0);

        $maintenanceOpen = (int) $db->table('maintenance_records')
            ->countAllResults();

        $assetIntelligenceService = new AssetIntelligenceService();
        $intelligenceStats = $assetIntelligenceService->stats($assetIntelligenceService->mapForAssets());
        $dueSoon = (int) ($intelligenceStats['due_soon'] ?? 0);

        return [
            'role' => 'admin',
            'attention_count' => max($totalBookings, $maintenanceOpen),
            'attention_label' => $maintenanceOpen > 0
                ? $maintenanceOpen . ' open maintenance case(s)'
                : ($totalBookings > 0 ? $totalBookings . ' total booking(s)' : 'No activity yet'),
            'attention_meta' => 'Oversee bookings, maintenance cases, and asset health across all laboratories.',
            'stats' => [
                ['id' => 'approved_bookings', 'label' => 'Approved', 'value' => $approvedBookings, 'tone' => 'success'],
                ['id' => 'total_bookings', 'label' => 'Total Bookings', 'value' => $totalBookings, 'tone' => 'primary'],
                ['id' => 'maintenance_open', 'label' => 'Maintenance', 'value' => $maintenanceOpen, 'tone' => 'warning'],
                ['id' => 'due_soon_assets', 'label' => 'Due Soon', 'value' => $dueSoon, 'tone' => 'accent'],
            ],
            'next_item' => null,
            'message' => 'User management, reports, settings, laboratories, and assets are available from the Admin workspace.',
        ];
    }

    /**
     * @return list<int>
     */
    protected function picLabIds(string $email): array
    {
        if ($email === '') {
            return [];
        }

        $labs = (new LaboratoryModel())
            ->where('LOWER(TRIM(pic_email)) =', strtolower(trim($email)))
            ->findAll();

        return array_map(static fn(array $lab): int => (int) $lab['id'], $labs);
    }
}
