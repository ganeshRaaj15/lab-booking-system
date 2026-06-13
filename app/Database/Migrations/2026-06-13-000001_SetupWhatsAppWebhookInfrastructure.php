<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetupWhatsAppWebhookInfrastructure extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'object_type' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'whatsapp_business_account_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'phone_number_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'message_id' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'recipient_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'payload_json' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
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
        $this->forge->addKey(['event_type', 'created_at']);
        $this->forge->addKey(['message_id']);
        $this->forge->addKey(['recipient_phone', 'created_at']);
        $this->forge->createTable('whatsapp_webhook_events', true);

        $this->upsertSystemSetting('whatsapp_enabled', '0', 'bool');
        $this->upsertSystemSetting('whatsapp_public_base_url', 'https://slams.cloud', 'string');
        $this->upsertSystemSetting('whatsapp_verify_token', bin2hex(random_bytes(24)), 'string');
        $this->upsertSystemSetting('whatsapp_access_token', '', 'string');
        $this->upsertSystemSetting('whatsapp_phone_number_id', '', 'string');
        $this->upsertSystemSetting('whatsapp_business_account_id', '', 'string');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_webhook_events', true);

        if (! $this->db->tableExists('settings')) {
            return;
        }

        $this->db->table('settings')
            ->where('class', 'system')
            ->whereIn('key', [
                'whatsapp_enabled',
                'whatsapp_public_base_url',
                'whatsapp_verify_token',
                'whatsapp_access_token',
                'whatsapp_phone_number_id',
                'whatsapp_business_account_id',
            ])
            ->delete();
    }

    private function upsertSystemSetting(string $key, string $value, string $type): void
    {
        if (! $this->db->tableExists('settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->db->table('settings')
            ->select('id')
            ->where('class', 'system')
            ->where('key', $key)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('settings')
                ->where('id', (int) $existing['id'])
                ->update([
                    'value' => $value,
                    'type' => $type,
                    'updated_at' => $now,
                ]);

            return;
        }

        $this->db->table('settings')->insert([
            'class' => 'system',
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'context' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
