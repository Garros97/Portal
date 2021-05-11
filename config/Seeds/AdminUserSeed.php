<?php
use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class AdminUserSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'ADMIN', 'description' => 'Zugang zum Admin-Bereich', 'supports_subresources' => 0],
            ['name' => 'GRANT_RIGHTS', 'description' => 'Kann Rechte von Accounts ändern', 'supports_subresources' => 0],
            ['name' => 'SUPERADMIN', 'description' => 'Uneingeschränkter Zugriff', 'supports_subresources' => 0],
            ['name' => 'QISIMPORT', 'description' => 'Kurse aus dem QIS importieren', 'supports_subresources' => 0],
            ['name' => 'LOGIN_AS', 'description' => 'Anmelden als anderer Account', 'supports_subresources' => 0],
            ['name' => 'EDIT_TAGS', 'description' => 'Tags hinzufügen/entfernen', 'supports_subresources' => 0],
            ['name' => 'UPLOAD_FOR_USER', 'description' => 'Dateien für anderen Account hochladen', 'supports_subresources' => 0],
            ['name' => 'RATE', 'description' => 'Bewertungen sehen/abgeben', 'supports_subresources' => 1],
            ['name' => 'MANAGE_PROJECTS', 'description' => 'Projekte/Kurse/etc. bearbeiten', 'supports_subresources' => 1],
            ['name' => 'DELETE_PROJECTS', 'description' => 'Projekte löschen', 'supports_subresources' => 0],
            ['name' => 'MANAGE_USERS', 'description' => 'Accounts bearbeiten', 'supports_subresources' => 0],
            ['name' => 'REVOKE_RIGHTS', 'description' => 'Anderen Accounts Rechte entziehen', 'supports_subresources' => 0]
        ];

        $table = $this->table('rights');
        $table->insert($data)->save();

        $data = [
            [
                'id' => '1',
                'username' => 'admin',
                'email' => 'root@localhost',
                'password' => '$2a$10$g2kaGERe9eEJ1IWxsLmbcenXF28emX1YHa5GCYioZKFC/K31ZlYPW',
                'first_name' => 'Admin',
                'last_name' => 'Nistrator',
                'sex' => 'x',
                'street' => 'Welfengarten',
                'house_number' => '1',
                'postal_code' => '12345',
                'city' => 'Hannover',
                'birthday' => '1994-02-10',
                'created' => '0000-00-00 00:00:00',
                'modified' => '0000-00-00 00:00:00',
                'last_login' => '0000-00-00 00:00:00'
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();


    }
}
