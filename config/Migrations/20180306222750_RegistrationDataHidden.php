<?php
use Migrations\AbstractMigration;

class RegistrationDataHidden extends AbstractMigration
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
		$table->addColumn('reg_data_hidden', 'boolean', [
			'after' => 'visible',
			'default' => false,
			'limit' => null,
			'null' => false
		])
		->update();
	}
}
