<?php
use Migrations\AbstractMigration;

class SimplerCbValues extends AbstractMigration
{

    public function up()
    {
        $this->table('custom_fields')
            ->addColumn('combo_box_values', 'text', [
                'after' => 'backend_only',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->update();

        $this->table('combo_box_values')
            ->drop();
    }

    public function down()
    {
        $this->table('combo_box_values')
            ->addColumn('custom_field_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addIndex(
                [
                    'custom_field_id',
                ]
            )
            ->create();

        $this->table('custom_fields')
            ->removeColumn('combo_box_values')
            ->update();
    }
}

