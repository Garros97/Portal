<?php
namespace App\Shell\Task;

use Cake\Console\Shell;
use Cake\ORM\Entity;

/**
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\RightsTable $Rights
 */
class MakeSuperAdminTask extends Shell
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Users');
        $this->loadModel('Rights');
    }

    public function main($username)
    {
        $user = $this->Users->findByUsername($username)->contain('Rights')->first();

        if ($user === null) {
            $this->abort("The user $username could not be found.");
        }

        $rightNames = ['SUPERADMIN', 'ADMIN', 'GRANT_RIGHTS', 'REVOKE_RIGHTS', 'MANAGE_USERS'];
        foreach ($rightNames as $rightName) {
            $right = $this->Rights->findByName($rightName)->first();
            if ($right === null) {
                $this->abort("The $rightName right could not be found. Maybe your database is empty?");
            }
            $right->_joinData = new Entity(['subresource' => 0]);
            $user->rights[] = $right;
        }

        $user->dirty('rights', true);
        $this->Users->save($user);
        $this->out("User $username promoted to super admin.");
    }

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser
            ->setDescription('Grants some basic administrative rights to the given user, including the SUPERADMIN right.')
            ->addArgument('username', ['help' => 'Username of the user', 'required' => true]);

        return $parser;
    }
}