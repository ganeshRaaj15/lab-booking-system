<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsAppWebhookEventModel extends Model
{
    protected $table = 'whatsapp_webhook_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'object_type',
        'event_type',
        'whatsapp_business_account_id',
        'phone_number_id',
        'message_id',
        'recipient_phone',
        'status',
        'payload_json',
        'created_at',
        'updated_at',
    ];
}
