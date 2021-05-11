<?php
use Migrations\AbstractMigration;

class RegistrationNote extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change() {
        $table = $this->table('projects');
        $table->addColumn('registration_note', 'text', [
            'after' => 'long_description',
            'limit' => null,
            'null' => false
        ])
            ->update();
    }
}
