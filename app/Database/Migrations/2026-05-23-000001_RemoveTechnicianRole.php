<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTechnicianRole extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('auth_groups_users')) {
            $this->db->table('auth_groups_users')
                ->where('group', 'technician')
                ->delete();
        }
    }

    public function down(): void
    {
        // Intentionally empty — restoring individual user-group assignments is not reversible
    }
}
