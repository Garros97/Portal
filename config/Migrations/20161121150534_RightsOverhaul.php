<?php
use Migrations\AbstractMigration;

class RightsOverhaul extends AbstractMigration
{

    public function up()
    {

        $this->table('rights_users')
            ->addColumn('subresource', 'integer', [
                'default' => 0,
                'limit' => 10,
                'null' => false,
            ])
            ->update();

        $this->table('rights')
            ->addColumn('supports_subresources', 'boolean', [
                'default' => 0,
                'length' => null,
                'null' => false,
            ])
            ->update();

        $dbname = $this->getAdapter()->getOption('name');
        //there is no special command for this
        $this->execute("ALTER TABLE `$dbname`.`rights_users` DROP PRIMARY KEY, ADD PRIMARY KEY (`right_id`, `user_id`, `subresource`) USING BTREE;");
    }

    public function down()
    {

        $this->table('rights_users')
            ->removeColumn('subresource')
            ->update();

        $this->table('rights')
            ->removeColumn('supports_subresources')
            ->update();

        $dbname = $this->getAdapter()->getOption('name');
        $this->execute("ALTER TABLE `$dbname`.`rights_users` DROP PRIMARY KEY, ADD PRIMARY KEY (`right_id`, `user_id`) USING BTREE;");
    }
}

