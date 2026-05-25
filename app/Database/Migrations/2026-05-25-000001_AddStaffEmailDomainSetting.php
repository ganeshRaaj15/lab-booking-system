<?php

namespace App\Database\Migrations;

use App\Libraries\StaffRoleService;
use CodeIgniter\Database\Migration;

class AddStaffEmailDomainSetting extends Migration
{
    public function up()
    {
        $builder = $this->db->table('settings');

        $exists = $builder
            ->where('class', 'system')
            ->where('key', 'staff_email_domain')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $builder->insert([
            'class'      => 'system',
            'key'        => 'staff_email_domain',
            'value'      => StaffRoleService::DEFAULT_STAFF_EMAIL_DOMAIN,
            'type'       => 'string',
            'context'    => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down()
    {
        $this->db->table('settings')
            ->where('class', 'system')
            ->where('key', 'staff_email_domain')
            ->delete();
    }
}
