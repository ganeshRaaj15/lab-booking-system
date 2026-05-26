<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceAndAssetsToExternalRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('external_requests') && ! $this->db->fieldExists('service_id', 'external_requests')) {
            $this->forge->addColumn('external_requests', [
                'service_id' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'lab_id',
                ],
            ]);
        }

        if ($this->db->tableExists('external_requests') && ! $this->db->fieldExists('selected_assets', 'external_requests')) {
            $this->forge->addColumn('external_requests', [
                'selected_assets' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'equipment_notes',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('external_requests') && $this->db->fieldExists('selected_assets', 'external_requests')) {
            $this->forge->dropColumn('external_requests', 'selected_assets');
        }

        if ($this->db->tableExists('external_requests') && $this->db->fieldExists('service_id', 'external_requests')) {
            $this->forge->dropColumn('external_requests', 'service_id');
        }
    }
}
