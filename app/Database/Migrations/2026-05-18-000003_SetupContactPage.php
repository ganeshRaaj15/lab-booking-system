<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetupContactPage extends Migration
{
    private const SETTINGS = [
        'faculty_name'    => 'Faculty of Mechanical & Manufacturing Engineering',
        'university_name' => 'Universiti Tun Hussein Onn Malaysia (UTHM)',
        'address'         => '86400, Parit Raja, Batu Pahat, Johor, Malaysia',
        'phone'           => '+607 4537703',
        'fax'             => '+607 4536080',
        'operating_hours' => '8:00 AM - 5:00 PM (Monday - Friday)',
        'location'        => 'Main Campus, Parit Raja',
        'general_note'    => 'For general inquiries about our facilities and academic programs, feel free to contact us during office hours.',
        'personnel_note'  => 'For laboratory-related inquiries or booking assistance through SLAMS, please contact the relevant personnel listed above.',
        'map_embed_src'   => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.919158657578!2d103.0859238!3d1.8564176!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d05fa8c1d6b90f%3A0xfa4a13957533a50f!2sFaculty%20of%20Mechanical%20%26%20Manufacturing%20Engineering%2C%20Universiti%20Tun%20Hussein%20Onn%20Malaysia%20(UTHM)!5e0!3m2!1sen!2smy!4v1700000000000!5m2!1sen!2smy',
        'directions_url'  => 'https://www.google.com/maps/dir//Faculty+of+Mechanical+%26+Manufacturing+Engineering,+Universiti+Tun+Hussein+Onn+Malaysia+(UTHM),+86400+Parit+Raja,+Batu+Pahat,+Johor/@1.8564176,103.0859238,17z',
        'google_maps_url' => 'https://www.google.com/maps/place/Faculty+of+Mechanical+%26+Manufacturing+Engineering,+Universiti+Tun+Hussein+Onn+Malaysia+(UTHM)/data=!4m7!3m6!1s0x31d05fa8c1d6b90f:0xfa4a13957533a50f!8m2!3d1.8564176!4d103.0881125',
        'waze_url'        => 'https://waze.com/ul?ll=1.8564176,103.0881125&navigate=yes',
        'coordinates'     => '1.8564176 N, 103.0881125 E',
        'parking_info'    => 'Available at nearby faculty parking lots',
        'transport_info'  => 'UTHM shuttle bus stops nearby',
    ];

    private const PERSONNEL = [
        ['name' => 'Mrs. Haslina binti Abd. Rashid',  'role' => 'Office Secretary, Dean Office',          'phone' => '+607 4537703', 'email' => 'haslinar@uthm.edu.my',  'photo_path' => 'images/staff/haslina_placeholder.jpg',  'sort_order' => 1],
        ['name' => 'Mrs. Asyarofah bt. Othman',       'role' => 'Assistant Registrar, Academic Division', 'phone' => '+607 4537351', 'email' => 'asyarofa@uthm.edu.my',  'photo_path' => 'images/staff/asyarofah_placeholder.jpg', 'sort_order' => 2],
        ['name' => 'Dr. Azwan Bin Sapit',             'role' => 'Web Administrator',                      'phone' => '+607 4538470', 'email' => 'azwans@uthm.edu.my',    'photo_path' => 'images/staff/azwan_placeholder.jpg',    'sort_order' => 3],
    ];

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // Create contact_personnel table if it doesn't exist
        if (!$this->db->tableExists('contact_personnel')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 255],
                'role'       => ['type' => 'VARCHAR', 'constraint' => 255],
                'phone'      => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true, 'default' => null],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
                'photo_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
                'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'created_at' => ['type' => 'DATETIME'],
                'updated_at' => ['type' => 'DATETIME'],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->createTable('contact_personnel');
        }

        // Seed personnel if table is empty
        if ($this->db->table('contact_personnel')->countAllResults() === 0) {
            foreach (self::PERSONNEL as $person) {
                $this->db->table('contact_personnel')->insert(array_merge($person, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // Seed contact settings (skip any key that already exists)
        foreach (self::SETTINGS as $key => $value) {
            $exists = $this->db->table('settings')
                ->where('class', 'contact')
                ->where('key', $key)
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('settings')->insert([
                    'class'      => 'contact',
                    'key'        => $key,
                    'value'      => $value,
                    'type'       => 'string',
                    'context'    => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('contact_personnel', true);

        $this->db->table('settings')->where('class', 'contact')->delete();
    }
}
