<?php
use Migrations\AbstractMigration;

class CoursesRegisterEnd extends AbstractMigration
{

    public function up()
    {

        $this->table('courses')
            ->addColumn('register_end', 'datetime', [
                'after' => 'uploads_end',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {

        $this->table('courses')
            ->removeColumn('register_end')
            ->update();
    }
}

