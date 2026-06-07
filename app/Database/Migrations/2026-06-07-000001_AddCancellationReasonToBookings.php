<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancellationReasonToBookings extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('bookings')) {
            return;
        }

        $fields = $this->db->getFieldNames('bookings');
        if (! in_array('cancellation_reason', $fields, true)) {
            $this->db->query("ALTER TABLE `bookings` ADD COLUMN `cancellation_reason` VARCHAR(500) NULL AFTER `status`");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('bookings')) {
            $fields = $this->db->getFieldNames('bookings');
            if (in_array('cancellation_reason', $fields, true)) {
                $this->db->query("ALTER TABLE `bookings` DROP COLUMN `cancellation_reason`");
            }
        }
    }
}
