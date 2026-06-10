<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendMaintenanceAssetStatusEnum extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('maintenance_records')) {
            return;
        }

        $this->db->query("ALTER TABLE maintenance_records
            MODIFY asset_status_before ENUM('available','maintenance','faulty','decommissioned') NOT NULL DEFAULT 'available',
            MODIFY asset_status_after  ENUM('available','maintenance','faulty','decommissioned') NULL DEFAULT NULL"
        );
    }

    public function down()
    {
        if (! $this->db->tableExists('maintenance_records')) {
            return;
        }

        // Reset any decommissioned values so the shrink doesn't error.
        $this->db->query("UPDATE maintenance_records SET asset_status_before = 'faulty' WHERE asset_status_before = 'decommissioned'");
        $this->db->query("UPDATE maintenance_records SET asset_status_after  = 'faulty' WHERE asset_status_after  = 'decommissioned'");

        $this->db->query("ALTER TABLE maintenance_records
            MODIFY asset_status_before ENUM('available','maintenance','faulty') NOT NULL DEFAULT 'available',
            MODIFY asset_status_after  ENUM('available','maintenance','faulty') NULL DEFAULT NULL"
        );
    }
}
