<?php

namespace App\Models;

use CodeIgniter\Model;

class ExternalAccessRequestModel extends Model
{
    protected $table         = 'external_access_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'full_name',
        'email',
        'phone',
        'organization',
        'purpose',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'created_user_id',
    ];

    protected function tableReady(): bool
    {
        return $this->db->tableExists($this->table);
    }

    /** True if a pending request already exists for this email. */
    public function hasPendingRequest(string $email): bool
    {
        if (! $this->tableReady()) return false;
        return $this->where('LOWER(email) =', strtolower(trim($email)))
                    ->where('status', 'pending')
                    ->countAllResults() > 0;
    }

    /** True if an approved request already exists for this email (account may already exist). */
    public function hasApprovedRequest(string $email): bool
    {
        if (! $this->tableReady()) return false;
        return $this->where('LOWER(email) =', strtolower(trim($email)))
                    ->where('status', 'approved')
                    ->countAllResults() > 0;
    }
}
