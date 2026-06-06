<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDecommissionedAssetStatus extends Migration
{
    public function up()
    {
        // Extend the status ENUM to include 'decommissioned'.
        // Existing rows are unaffected — only the allowed value set changes.
        $this->forge->modifyColumn('assets', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['available', 'maintenance', 'faulty', 'decommissioned'],
                'default'    => 'available',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        // First reset any decommissioned assets back to faulty so the ENUM shrink doesn't error.
        $this->db->table('assets')
            ->where('status', 'decommissioned')
            ->update(['status' => 'faulty']);

        $this->forge->modifyColumn('assets', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['available', 'maintenance', 'faulty'],
                'default'    => 'available',
                'null'       => false,
            ],
        ]);
    }
}
