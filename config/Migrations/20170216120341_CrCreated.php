<?php
use Migrations\AbstractMigration;

class CrCreated extends AbstractMigration
{
    public function up()
    {

        $this->table('courses_registrations')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('courses_registrations')
            ->removeColumn('created', 'datetime')
            ->update();
    }
}
