<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class QuicksearchController extends AppController
{
    public $rights = [
        'searchUsers' => [],
        'searchRegistrations' => [],
        'projectsPrefetch' => []
    ];

    public function initialize()
    {
        $this->loadComponent('RequestHandler');
        parent::initialize();
    }

    public function beforeRender(Event $event)
    {
        $this->RequestHandler->renderAs($this, 'json'); //also render as json when this is a non-ajax request, for easier debugging
        parent::beforeRender($event);
    }

    /**
     * AJAX search function used by Quicksearch.
     *
     * @return void
     */
    public function searchUsers()
    {
        $q = $this->request->getQuery('q', '');

        if (strlen($q) < 3)
            throw new \InvalidArgumentException('Query too short!');

        $usersTable = TableRegistry::get('Users');
        $query = $usersTable->find()
            ->select(['id', 'username', 'first_name', 'last_name', 'email'])
            ->applyOptions(['noAutoContainTags' => true]);

        if (ctype_digit((string)$q)) { //only numbers
            $query->where(['id' => (int)$q]);
        } else {
            $query->where(['OR' => ['username LIKE' => "%$q%",
                'first_name LIKE' => "%$q%",
                'last_name LIKE' => "%$q%",
                'email LIKE' => "%$q%",
                $query->newExpr()->add([$query->func()->concat(['first_name' => 'identifier', ' ', 'last_name' => 'identifier']), "'%$q%'"])->setConjunction('LIKE')
            ]]);
        }

        $query->limit(50);

        $query = $query->map(function ($e) {
            $e->url = Router::url(['controller' => 'users', 'action' => 'edit', $e->id]);
            return $e;
        });

        $this->set('users', $query->toArray());
        $this->set('_serialize', 'users');
    }

    /**
     * AJAX search function used by Quicksearch.
     *
     * We currently only do the search if a number is entered and just
     * look for a registrations with that RID.
     * TODO: Also find registrations by username?
     *
     * @return void
     */
    public function searchRegistrations()
    {
        $q = $this->request->getQuery('q', '');

        if (strlen($q) < 3)
            throw new \InvalidArgumentException('Query too short!');

        $result = [];

        if (ctype_digit((string)$q)) { //only numbers
            $registrationsTable = TableRegistry::get('Registrations');
            $query = $registrationsTable->find()
                ->select([
                    'Registrations.id',
                    'user:username' => 'Users.username',
                    'user:first_name' => 'Users.first_name',
                    'user:last_name' => 'Users.last_name',
                    'project:name' => 'Projects.name'])
                ->contain([
                    'Users' => function ($q) {
                        return $q->applyOptions(['noAutoContainTags' => true]);
                    },
                    'Projects' => function ($q) {
                        return $q->applyOptions(['noAutoContainTags' => true]);
                    }
                ])
                ->applyOptions(['noAutoContainTags' => true])
                ->where(['Registrations.id' => (int)$q])
                ->limit(50)
                ->map(function ($e) {
                    $e->url = Router::url(['controller' => 'registrations', 'action' => 'edit', $e->id]);
                    return $e;
                });
            $result = $query->toArray();
        }

        $this->set('registrations', $result);
        $this->set('_serialize', 'registrations');
    }

    /**
     * Generates an array of all projects, used by Quicksearch.
     *
     * Due to the low count of projects, all data is prefetched rather
     * then doing a real AJAX serach like for the users and registrations.
     *
     * @return void
     */
    public function projectsPrefetch()
    {
        $projectsTable = TableRegistry::get('Projects');
        $query = $projectsTable->find()
            ->select(['id', 'name', 'urlname'])
            ->applyOptions(['noAutoContainTags' => true])
            ->map(function ($e) {
                $e->url = Router::url(['controller' => 'projects', 'action' => 'edit', $e->id]);
                return $e;
            });

        $this->set('projects', $query->toArray());
        $this->set('_serialize', 'projects');
    }
}