<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MaintenanceForecastService;
use App\Libraries\NotificationService;
use App\Models\SettingsModel;

class SettingsController extends BaseController
{
    protected $settings;

    public function __construct()
    {
        helper(['auth']);

        if (!auth()->loggedIn() || !auth()->user()->inGroup('admin')) {
            redirect()->to('/')->with('error', 'Unauthorized access.')->send();
            exit;
        }

        $this->settings = new SettingsModel();
    }

    /**
     * Display all settings including booking slot editor
     */
    public function index()
    {
        // Fetch normal settings
        $rows = $this->settings
            ->where('class', 'system')
            ->orderBy('key', 'ASC')
            ->findAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = [
                'value' => $row['value'],
                'type'  => $row['type']
            ];
        }

        // Load booking slots JSON
        $slotsJson = setting('system.booking_slots') ?? '[]';
        $bookingSlots = json_decode($slotsJson, true);

        return view('admin/settings/index', [
            'settings'      => $settings,
            'bookingSlots'  => $bookingSlots
        ]);
    }


    /**
     * Save non-slot settings (emails, faculty, etc.)
     */
    public function update()
    {
        $rules = [
            'lab_manager_email'   => 'required|valid_email',
            'deputy_dean_email'   => 'required|valid_email',
            'lab_assistant_email' => 'required|valid_email',
            'fkmp_faculty_id'     => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $keys = [
            'lab_manager_email',
            'deputy_dean_email',
            'lab_assistant_email',
            'fkmp_faculty_id'
        ];

        foreach ($keys as $key) {
            $this->settings
                ->where('class', 'system')
                ->where('key', $key)
                ->set([
                    'value'      => $this->request->getPost($key),
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->update();
        }

        return redirect()->to('/admin/settings')->with('message', 'Settings updated.');
    }


    /**
     * AJAX: Save booking slots
     */
    public function saveSlots()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Invalid request type.'
            ]);
        }

        $slots = $this->request->getPost('slots');

        if (is_string($slots)) {
            $decoded = json_decode($slots, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $slots = $decoded;
            }
        }

        if (!is_array($slots)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid slots format.'
            ]);
        }

        // Validate each time slot
        foreach ($slots as $slot) {
            if (
                empty($slot['start']) ||
                empty($slot['end'])
            ) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Each slot must have start and end times.'
                ]);
            }

            if ($slot['start'] >= $slot['end']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Start time must be earlier than end time.'
                ]);
            }
        }

        // Normalize and check for overlaps
        $normalized = [];
        foreach ($slots as $slot) {
            $start = $slot['start'];
            $end = $slot['end'];
            $normalized[] = ['start' => $start, 'end' => $end];
        }

        usort($normalized, function ($a, $b) {
            if ($a['start'] === $b['start']) {
                return strcmp($a['end'], $b['end']);
            }
            return strcmp($a['start'], $b['start']);
        });

        for ($i = 1; $i < count($normalized); $i++) {
            $prev = $normalized[$i - 1];
            $cur = $normalized[$i];
            if ($cur['start'] < $prev['end']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Time slots cannot overlap.'
                ]);
            }
        }

        // Generate labels automatically
        foreach ($slots as &$s) {
            $s['label'] = $s['start'] . ' - ' . $s['end'];
        }

        // Convert to JSON
        $json = json_encode($slots);

        // Save to settings table (upsert)
        $existing = $this->settings
            ->where('class', 'system')
            ->where('key', 'booking_slots')
            ->first();

        if ($existing) {
            $this->settings
                ->where('class', 'system')
                ->where('key', 'booking_slots')
                ->set([
                    'value'      => $json,
                    'type'       => 'string',
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->update();
        } else {
            $this->settings->insert([
                'class'      => 'system',
                'key'        => 'booking_slots',
                'value'      => $json,
                'type'       => 'string',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Booking slots updated successfully.',
            'slots'   => $slots
        ]);
    }

    public function runScheduledTasks()
    {
        $bookingSent = 0;
        $maintenanceSent = 0;
        $errors = [];

        try {
            $bookingSent = (new NotificationService())->sendUpcomingBookingReminders(24);
        } catch (\Throwable $e) {
            $errors[] = 'booking reminders';
            log_message('error', 'Manual scheduled task failed [booking reminders]: ' . $e->getMessage());
        }

        try {
            $maintenanceSent = (new MaintenanceForecastService())->sendUpcomingDueReminders(30);
        } catch (\Throwable $e) {
            $errors[] = 'maintenance due reminders';
            log_message('error', 'Manual scheduled task failed [maintenance due reminders]: ' . $e->getMessage());
        }

        $message = 'Scheduled tasks completed. Booking reminders: ' . $bookingSent . '. Maintenance due reminders: ' . $maintenanceSent . '.';
        $redirect = redirect()->to('/admin/settings')->with('message', $message);

        if ($errors !== []) {
            return $redirect->with('warning', 'Some scheduled task checks failed: ' . implode(', ', $errors) . '. The app is still usable; check writable/logs for details.');
        }

        return $redirect;
    }
}
