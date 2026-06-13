<?php

namespace Tests;

use Tests\Support\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->upsertSystemSetting('whatsapp_verify_token', 'test-verify-token-123');
        $this->upsertSystemSetting('whatsapp_public_base_url', 'https://slams.cloud');
    }

    public function testWebhookVerificationReturnsChallengeWhenTokenMatches(): void
    {
        $result = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test-verify-token-123&hub.challenge=challenge-abc');

        $result->assertStatus(200);
        $this->assertSame('challenge-abc', trim((string) $result->getBody()));
    }

    public function testWebhookStatusEventIsStored(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '27446277668375300',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'display_phone_number' => '+1 555 676 8530',
                            'phone_number_id' => '1186163661242583',
                        ],
                        'statuses' => [[
                            'id' => 'wamid.HBgLTEST123',
                            'status' => 'delivered',
                            'recipient_id' => '60123456789',
                        ]],
                    ],
                ]],
            ]],
        ];

        $result = $this->withBody(json_encode($payload), 'application/json')
            ->post('/webhooks/whatsapp');

        $result->assertStatus(200);
        $this->assertSame('EVENT_RECEIVED', trim((string) $result->getBody()));

        $row = db_connect()
            ->table('whatsapp_webhook_events')
            ->where('message_id', 'wamid.HBgLTEST123')
            ->get()
            ->getRowArray();

        $this->assertNotNull($row, 'WhatsApp webhook status event was not stored.');
        $this->assertSame('message_status', $row['event_type'] ?? null);
        $this->assertSame('delivered', $row['status'] ?? null);
        $this->assertSame('1186163661242583', $row['phone_number_id'] ?? null);
    }

    private function upsertSystemSetting(string $key, string $value, string $type = 'string'): void
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');
        $existing = $db->table('settings')
            ->select('id')
            ->where('class', 'system')
            ->where('key', $key)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('settings')
                ->where('id', (int) $existing['id'])
                ->update([
                    'value' => $value,
                    'type' => $type,
                    'updated_at' => $now,
                ]);

            return;
        }

        $db->table('settings')->insert([
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
