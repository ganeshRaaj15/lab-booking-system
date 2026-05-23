<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTechnicianUsers extends Migration
{
    public function up(): void
    {
        $technicianUserIds = array_column(
            $this->db->table('auth_groups_users')
                ->where('group', 'technician')
                ->get()
                ->getResultArray(),
            'user_id'
        );

        if (empty($technicianUserIds)) {
            return;
        }

        // Clear Shield identity and session tables before deleting the user rows
        foreach (['auth_identities', 'auth_groups_users', 'auth_remember_tokens'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->table($table)
                    ->whereIn('user_id', $technicianUserIds)
                    ->delete();
            }
        }

        // Deleting from users cascades to notifications, push_tokens, push_subscriptions,
        // and sets maintenance_records.assigned_technician_id to NULL via FK rules.
        $this->db->table('users')
            ->whereIn('id', $technicianUserIds)
            ->delete();
    }

    public function down(): void
    {
        // User deletion is not reversible
    }
}
