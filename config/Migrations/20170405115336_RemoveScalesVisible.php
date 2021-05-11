<?php
use Migrations\AbstractMigration;

class RemoveScalesVisible extends AbstractMigration
{

    public function up()
    {

        $this->table('scales')
            ->removeColumn('user_visible')
            ->update();
    }

    public function down()
    {

        $this->table('scales')
            ->addColumn('user_visible', 'boolean', [
                'after' => 'hint',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->update();
    }
}

