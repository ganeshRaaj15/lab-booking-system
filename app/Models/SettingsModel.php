<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'class', 'key', 'value', 'type', 'context',
        'created_at', 'updated_at'
    ];

    public function get(string $class, string $key, ?string $context = null)
    {
        $builder = $this->where('class', $class)
                        ->where('key', $key);

        if ($context !== null) {
            $builder->where('context', $context);
        }

        $row = $builder->first();

        if (!$row) {
            return null;
        }

        // Cast value by type
        return match ($row['type']) {
            'int'    => (int) $row['value'],
            'bool'   => filter_var($row['value'], FILTER_VALIDATE_BOOL),
            default  => $row['value'],
        };
    }
}
