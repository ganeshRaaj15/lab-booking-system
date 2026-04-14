<?php

namespace App\Commands;

use App\Libraries\NotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunScheduledTasks extends BaseCommand
{
    protected $group = 'SLAMS';
    protected $name = 'slams:run-scheduled-tasks';
    protected $description = 'Run the scheduled SLAMS background tasks such as booking reminders.';

    public function run(array $params)
    {
        $service = new NotificationService();
        $hoursAhead = max((int) ($params[0] ?? 24), 1);
        $sent = $service->sendUpcomingBookingReminders($hoursAhead);

        CLI::write('Scheduled tasks completed.', 'green');
        CLI::write('Booking reminder notifications sent: ' . $sent, 'green');
    }
}
