<?php
use Migrations\AbstractMigration;

class CreateWakeningCalls extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('wakening_calls')
            ->addColumn('name', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('urlname', 'string', [
                'limit' => 50,
                'null' => false
            ])
            ->addColumn('state', 'integer', [
                'default' => 1,
                'length' => 1,
                'null' => false,
            ])
            ->addColumn('permanent', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('email_from', 'string', [
                'default' => '',
                'length' => 100,
                'null' => false
            ])
            ->addColumn('email_subject', 'string', [
                'default' => '',
                'length' => 100,
                'null' => false
            ])
            ->addColumn('message', 'text', [
                'limit' => null,
                'null' => false
            ])
            ->create();
        $this->table('wakening_call_subscribers')
            ->addColumn('wakening_call_id', 'integer',[
                    'default' => null,
                    'null' => false,
                ]
            )
            ->addForeignKey(
                'wakening_call_id',
                'wakening_calls',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addIndex(['wakening_call_id', 'email'], ['unique' => true])
            ->create();
    }
}
