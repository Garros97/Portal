<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class SearchController extends AppController
{
    public $rights = [
        'index' => ['MANAGE_PROJECTS', 'MANAGE_USERS'],
        'searchAll' => ['MANAGE_PROJECTS', 'MANAGE_USERS']
    ];

    public function initialize()
    {
        //$this->loadComponent('RequestHandler');
        parent::initialize();
    }

    public function beforeRender(Event $event)
    {
        //$this->RequestHandler->renderAs($this, 'json');
        parent::beforeRender($event);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index() {
        $ref = $this->request->getQuery('ref','');
        $this->set('ref', $ref);
        $this->set('projects', $this->getProjects());
    }

    protected  function getProjects() {
        $projects = $this->loadModel('Projects')
            ->query()->select(['id', 'name', 'register_start', 'register_end'])
            ->applyOptions(['noAutoContainTags' => true])
            ->toArray();
        usort($projects, function($a, $b) { // TODO: better way to make sorting reusable?
            if ($b->isActive()) {
                if ($a->isActive()) {
                    return strcmp($a->name, $b->name);
                } else {
                    return 1;
                }
            } else if (!$a->isActive() && !$b->isActive()) {
                return strcmp($a->name, $b->name);
            }
        });
        return $projects;
    }

    public function searchAll() { // search users, registrations and groups
        $limit = 20;
        $q = $this->request->getQuery('q','');
        $offset = $this->request->getQuery('o');
        $pid = $this->request->getQuery('pid','');
        $groupsTable = TableRegistry::get('groups');
        $query = $groupsTable->find()
            ->innerJoin('groups_users', ['groups.id = groups_users.group_id'])
            ->rightJoin('registrations', ['registrations.project_id = groups.project_id', 'registrations.user_id = groups_users.user_id'])
            ->rightJoin('users', ['registrations.user_id = users.id'])
            ->leftJoin('projects', ['registrations.project_id = projects.id'])
            ->select(['uid' => 'users.id', 'rid' => 'registrations.id', 'pname' => 'projects.name',
                'users.username', 'users.email', 'users.first_name', 'users.last_name',
                'gid' => 'groups.id', 'gname' => 'groups.name'
            ])
            ->order(['uid' => 'ASC', 'rid' => 'ASC'])
            ->limit($limit)
            ->applyOptions(['noAutoContainTags' => true])
            ->enableHydration(false);
        if ($pid != 0) $query = $query->where(['projects.id' => $pid]);
        if ($offset != 0) {
            $query = $query->offset($offset);
        }
        if (ctype_digit((string) $q)) { //TODO: also search group id  and / or project id?
            $pidCond = ($pid == 0) ? ['projects.id' => (int) $q] : [];
            $query = $query->where([
                'OR' => array_merge(['users.id' => (int) $q, 'registrations.id' => (int) $q], $pidCond)
            ]);
        } else {
            if (strlen($q) < 3) throw new \InvalidArgumentException('Query too short!');
            $query = $query->where(['OR' => ['users.username LIKE' => "%$q%",
                'users.email LIKE' => "%$q%",
                'users.first_name LIKE' => "%$q%",
                'users.last_name LIKE' => "%$q%",
                $query->newExpr()->add([$query->func()->concat(['users.first_name' => 'identifier', ' ', 'users.last_name' => 'identifier']), "'%$q%'"])->setConjunction('LIKE'),
                'groups.name like' => "%$q%"
            ]]);
        }

        $tmp = $query->toArray();
        $results = array();
        foreach ($tmp as $row) {
            $uid = $row['uid'];
            $rid = $row['rid'];
            $gid = $row['gid'];
            if (!array_key_exists($uid, $results)) {
                foreach ($row['users'] as $key => $value) {
                    $results[$uid][$key] = $value;
                }
            }
            if (!is_null($rid)) {
                $results[$uid]['registrations'][$rid]['pname'] = $row['pname'];
                if (strlen($gid) > 0) {
                    $results[$uid]['registrations'][$rid]['groups'][$gid]['gname'] = $row['gname'];
                } else {
                    $results[$uid]['registrations'][$rid]['groups'][-1] = 0;
                }
            }
        }
        $m = 0;
        if (count($tmp) == $limit) $m = count(array_pop($results)['registrations']);
        $r_count = count($tmp) - $m;
        $data = array('count' => $r_count, 'results' => $results);
        return $this->response->withStringBody(json_encode($data));
    }
}
/*
     <tr>
        <td><?= $user->id ?></td>
        <td><?= h($user->username) ?></td>
        <td><?= h($user->email) ?></td>
        <td><?= h($user->first_name) ?></td>
        <td><?= h($user->last_name) ?></td>
        <td class="actions">
            <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $user->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten']) ?>
            <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'delete', $user->id], ['confirm' => sprintf('Account %s wirklich löschen?', $user->username), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen']) ?>
        </td>
    </tr>
 */