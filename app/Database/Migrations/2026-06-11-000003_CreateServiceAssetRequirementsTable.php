<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiceAssetRequirementsTable extends Migration
{
    public function up()
    {
        if (
            ! $this->db->tableExists('lab_services')
            || ! $this->db->tableExists('assets')
            || $this->db->tableExists('service_asset_requirements')
        ) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'lab_service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'asset_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'quantity_required' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['lab_service_id', 'asset_id']);
        $this->forge->addForeignKey('lab_service_id', 'lab_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('service_asset_requirements', true);
    }

    public function down()
    {
        $this->forge->dropTable('service_asset_requirements', true);
    }
}
