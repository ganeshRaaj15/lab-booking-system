<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactPersonnelTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
            ],
            'sort_order' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('contact_personnel');

        // Seed the three existing staff members
        $now = date('Y-m-d H:i:s');
        $this->db->table('contact_personnel')->insertBatch([
            [
                'name'       => 'Mrs. Haslina binti Abd. Rashid',
                'role'       => 'Office Secretary, Dean Office',
                'phone'      => '+607 4537703',
                'email'      => 'haslinar@uthm.edu.my',
                'photo_path' => 'images/staff/haslina_placeholder.jpg',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Mrs. Asyarofah bt. Othman',
                'role'       => 'Assistant Registrar, Academic Division',
                'phone'      => '+607 4537351',
                'email'      => 'asyarofa@uthm.edu.my',
                'photo_path' => 'images/staff/asyarofah_placeholder.jpg',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Dr. Azwan Bin Sapit',
                'role'       => 'Web Administrator',
                'phone'      => '+607 4538470',
                'email'      => 'azwans@uthm.edu.my',
                'photo_path' => 'images/staff/azwan_placeholder.jpg',
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('contact_personnel', true);
    }
}
