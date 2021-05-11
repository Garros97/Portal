<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Group;

/**
 * Groups Controller
 *
 * @property \App\Model\Table\GroupsTable $Groups
 */
class GroupsController extends AppController
{
    public $rights = [
        'index' => ['MANAGE_PROJECTS/$0'],
        'delete' => ['MANAGE_PROJECTS/?'],
        'edit' => ['MANAGE_PROJECTS/?'],
        'removeFromGroup' => ['MANAGE_PROJECTS/?']
    ];

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->Groups->get($request->getParam('pass')[0])->project_id;
    }


    /**
     * Index method
     *
     * @param string|null $projectId The project id to list the groups for.
     * @return \Cake\Http\Response|null
     */
    public function index($projectId = null)
    {
        $this->Groups->hasMany('GroupsUsers');
        $project = $this->Groups->Projects->get($projectId);
        $query = $this->Groups->find();
        $query
            ->where(['project_id' => $projectId])
            ->select(['id', 'name',
                'user_count' => $query->func()->count('GroupsUsers.user_id')
            ])
            ->leftJoinWith('GroupsUsers')
            ->group('Groups.id');

        $groups = $this->paginate($query);

        $this->set(compact('groups', 'project'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Group id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        /** @var Group $group */
        $group = $this->Groups->get($id, [
            'contain' => ['Projects', 'Users']
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $group = $this->Groups->patchEntity($group, $this->request->getData());
            if ($this->Groups->save($group)) {
                $this->Flash->success('Die Gruppe wurde gespeichert.');
                return $this->redirect(['action' => 'index', $group->project_id]);
            } else {
                $this->Flash->error('Die Gruppe konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('group'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Group id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->Groups->Users->find()->innerJoinWith('Groups', function($q) use($id) {
            return $q->where(['Groups.id' => $id]);
        })->count() > 0) {
            $this->Flash->error('Die Gruppe ist nicht leer und kann daher nicht gelöscht werden.');
            return $this->redirect($this->referer());
        }
        else {
            $group = $this->Groups->get($id);
            if ($this->Groups->delete($group)) {
                $this->Flash->success('Die Gruppe wurde gelöscht.');
            } else {
                $this->Flash->error('Die konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.');
            }

            return $this->redirect(['controller' => 'Groups', 'action' => 'index', $group->project_id]);
        }
    }

    public function removeFromGroup($groupId = null, $userId = null)
    {
        $group = $this->Groups->get($groupId);
        $user = $this->Groups->Users->get($userId);
        $this->Groups->Users->unlink($group, [$user]);
        return $this->redirect(['action' => 'edit', $group->id]);
    }
}
