<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

/**
 * @property \App\Model\Table\TagsTable $Tags
 * @property \App\Model\Table\TagsUsersTable $TagsUsers
 */
class CleanTagsTask extends Shell
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Tags');
        $this->loadModel('TagsUsers');
        $this->loadModel('TagsCourses');
    }

    public function main()
    {
        $this->Tags->query()
            ->delete()
            ->where(['id NOT IN' => $this->TagsUsers->find()->select('tag_id')])
            ->where(['id NOT IN' => $this->TagsCourses->find()->select('tag_id')])
            ->execute();
        //TODO: Is this faster with a RIGHT JOIN and IS NULL? (This is probably fast enough)
        //TODO: Can we somehow automatically get all Models that "have many" Tags?
        //TODO: Display rows affected?
        $this->out("Cleaning done.");
    }

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription('Cleans the database from unused tags.');

        return $parser;
    }
}