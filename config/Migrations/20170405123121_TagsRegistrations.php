<?php
use Migrations\AbstractMigration;

class TagsRegistrations extends AbstractMigration
{

    public function up()
    {

        $this->table('tags_registrations', ['id' => false, 'primary_key' => ['tag_id', 'registration_id']])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('registration_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 200,
                'null' => true,
            ])
            ->addIndex(
                [
                    'registration_id',
                ]
            )
            ->addIndex(
                [
                    'tag_id',
                ]
            )
            ->create();

        $this->table('tags_registrations')
            ->addForeignKey(
                'registration_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'tag_id',
                'tags',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();
    }

    public function down()
    {
        $this->table('tags_registrations')
            ->dropForeignKey(
                'registration_id'
            )
            ->dropForeignKey(
                'tag_id'
            );

        $this->dropTable('tags_registrations');
    }
}

