<?php
use Migrations\AbstractMigration;

class RemoveMultireg extends AbstractMigration
{

    public function up()
    {

        $this->table('projects')
            ->removeColumn('allow_multireg')
            ->removeColumn('multireg_infotext')
            ->update();
    }

    public function down()
    {

        $this->table('projects')
            ->addColumn('allow_multireg', 'boolean', [
                'after' => 'max_group_size',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->addColumn('multireg_infotext', 'text', [
                'after' => 'allow_multireg',
                'default' => null,
                'length' => null,
                'null' => false,
            ])
            ->update();
    }
}

