<?php
use Migrations\AbstractMigration;

class CourseCountRestriction extends AbstractMigration
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
        $table = $this->table('projects');
        $table->addColumn('min_course_count', 'integer', [
            'default' => 1,
            'length' => 3,
            'null' => false,
        ])
        ->addColumn('max_course_count', 'integer', [
            'default' => 0,
            'length' => 3,
            'null' => false,
        ])
        ->update();
    }
}
