<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\WhatsAppConfiguration;
use App\Models\WhatsAppWebhookEventModel;

class WhatsAppWebhookController extends BaseController
{
    private WhatsAppConfiguration $configuration;
    private WhatsAppWebhookEventModel $events;

    public function __construct()
    {
        $this->configuration = new WhatsAppConfiguration();
        $this->events = new WhatsAppWebhookEventModel();
    }

    public function verify()
    {
        $mode = trim((string) $this->request->getGet('hub.mode'));
        $token = trim((string) $this->request->getGet('hub.verify_token'));
        $challenge = (string) $this->request->getGet('hub.challenge');
        $expectedToken = $this->configuration->verifyToken();

        if ($mode === 'subscribe' && $expectedToken !== '' && hash_equals($expectedToken, $token)) {
            return $this->response
                ->setStatusCode(200)
                ->setContentType('text/plain')
                ->setBody($challenge);
        }

        log_message('warning', 'WhatsApp webhook verification failed for mode "' . $mode . '".');

        return $this->response
            ->setStatusCode(403)
            ->setContentType('text/plain')
            ->setBody('Forbidden');
    }

    public function receive()
    {
        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $rawBody = trim((string) $this->request->getBody());
            $payload = $rawBody !== '' ? json_decode($rawBody, true) : null;
        }

        if (! is_array($payload)) {
            log_message('warning', 'WhatsApp webhook received invalid JSON payload.');

            return $this->response
                ->setStatusCode(400)
                ->setContentType('text/plain')
                ->setBody('Invalid payload');
        }

        $records = $this->extractRecords($payload);
        if ($records !== []) {
            try {
                $this->events->insertBatch($records);
            } catch (\Throwable $e) {
                log_message('error', 'WhatsApp webhook event log insert failed: ' . $e->getMessage());
            }
        }

        return $this->response
            ->setStatusCode(200)
            ->setContentType('text/plain')
            ->setBody('EVENT_RECEIVED');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractRecords(array $payload): array
    {
        $objectType = trim((string) ($payload['object'] ?? ''));
        $entries = is_array($payload['entry'] ?? null) ? $payload['entry'] : [];
        $records = [];

        if ($entries === []) {
            return [[
                'object_type' => $objectType,
                'event_type' => 'unknown',
                'whatsapp_business_account_id' => null,
                'phone_number_id' => null,
                'message_id' => null,
                'recipient_phone' => null,
                'status' => null,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]];
        }

        foreach ($entries as $entry) {
            $wabaId = trim((string) ($entry['id'] ?? ''));
            $changes = is_array($entry['changes'] ?? null) ? $entry['changes'] : [];

            if ($changes === []) {
                $records[] = [
                    'object_type' => $objectType,
                    'event_type' => 'unknown',
                    'whatsapp_business_account_id' => $wabaId !== '' ? $wabaId : null,
                    'phone_number_id' => null,
                    'message_id' => null,
                    'recipient_phone' => null,
                    'status' => null,
                    'payload_json' => json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ];
                continue;
            }

            foreach ($changes as $change) {
                $value = is_array($change['value'] ?? null) ? $change['value'] : [];
                $metadata = is_array($value['metadata'] ?? null) ? $value['metadata'] : [];
                $phoneNumberId = trim((string) ($metadata['phone_number_id'] ?? ''));
                $statuses = is_array($value['statuses'] ?? null) ? $value['statuses'] : [];
                $messages = is_array($value['messages'] ?? null) ? $value['messages'] : [];

                foreach ($statuses as $statusRow) {
                    $records[] = [
                        'object_type' => $objectType,
                        'event_type' => 'message_status',
                        'whatsapp_business_account_id' => $wabaId !== '' ? $wabaId : null,
                        'phone_number_id' => $phoneNumberId !== '' ? $phoneNumberId : null,
                        'message_id' => trim((string) ($statusRow['id'] ?? '')) ?: null,
                        'recipient_phone' => trim((string) ($statusRow['recipient_id'] ?? '')) ?: null,
                        'status' => trim((string) ($statusRow['status'] ?? '')) ?: null,
                        'payload_json' => json_encode($statusRow, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ];
                }

                foreach ($messages as $messageRow) {
                    $records[] = [
                        'object_type' => $objectType,
                        'event_type' => 'incoming_message',
                        'whatsapp_business_account_id' => $wabaId !== '' ? $wabaId : null,
                        'phone_number_id' => $phoneNumberId !== '' ? $phoneNumberId : null,
                        'message_id' => trim((string) ($messageRow['id'] ?? '')) ?: null,
                        'recipient_phone' => trim((string) ($messageRow['from'] ?? '')) ?: null,
                        'status' => trim((string) ($messageRow['type'] ?? 'message')) ?: 'message',
                        'payload_json' => json_encode($messageRow, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ];
                }

                if ($statuses === [] && $messages === []) {
                    $records[] = [
                        'object_type' => $objectType,
                        'event_type' => 'unknown',
                        'whatsapp_business_account_id' => $wabaId !== '' ? $wabaId : null,
                        'phone_number_id' => $phoneNumberId !== '' ? $phoneNumberId : null,
                        'message_id' => null,
                        'recipient_phone' => null,
                        'status' => null,
                        'payload_json' => json_encode($change, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ];
                }
            }
        }

        return $records;
    }
}
