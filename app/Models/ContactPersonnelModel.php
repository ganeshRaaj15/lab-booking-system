<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactPersonnelModel extends Model
{
    protected $table         = 'contact_personnel';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'name', 'role', 'phone', 'email', 'photo_path', 'sort_order',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;

    public function allOrdered(): array
    {
        return $this->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
