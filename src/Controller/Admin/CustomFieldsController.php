<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Entity;
use Cake\ORM\TableRegistry;

/**
 * CustomFields Controller
 *
 * @property \App\Model\Table\CustomFieldsTable $CustomFields
 */
class CustomFieldsController extends AppController
{
	public $rights = [
		'add' => ['MANAGE_PROJECTS/?'],
		'edit' => ['MANAGE_PROJECTS/?'],
		'delete' => ['MANAGE_PROJECTS/?']
	];

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->CustomFields->get($request->getParam('pass')[0])->project_id;
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $customField = $this->CustomFields->newEntity();
        if ($this->request->is('post')) {
            $customField = $this->CustomFields->patchEntity($customField, $this->request->getData());
            // link new custom fields to existing registrations for consistency
            $registrations = TableRegistry::get('Registrations')->find()->where(['project_id' => $customField->project_id])->toArray();
            foreach ($registrations as $registration) $registration->_joinData = new Entity(['value' => ''], ['markNew' => true]);
            if ($this->CustomFields->save($customField) && $this->CustomFields->Registrations->link($customField, $registrations)) {
                $this->Flash->success('Das Zusatzfeld wurde gespeichert.');
                return $this->redirect(['controller' => 'Projects', 'action' => 'edit', $customField->project_id]);
            } else {
                $this->Flash->error('Das Zusatzfeld konnte nicht gespeichert werden, bitte versuchen Sie es erneut.');
            }
        }
        $this->viewBuilder()->setTemplate('edit');
        $this->set(compact('customField'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Custom Field id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $customField = $this->CustomFields->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $customField = $this->CustomFields->patchEntity($customField, $this->request->getData());
            if ($this->CustomFields->save($customField)) {
                $this->Flash->success('Das Zusatzfeld wurde gespeichert.');
                return $this->redirect(['controller' => 'Projects', 'action' => 'edit', $customField->project_id]);
            } else {
                $this->Flash->error('Das Zusatzfeld konnte nicht gespeichert werden, bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('customField'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Custom Field id.
     * @return \Cake\Http\Response|null Redirects to edit form of project.
     * @throws \Cake\Http\Exception\ForbiddenException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $customField = $this->CustomFields->get($id);
        if ($this->CustomFields->delete($customField)) {
            $this->Flash->success('Das Feld wurde gelöscht.');
        } else {
            $this->Flash->error('Das Feld konnte nicht gelöscht werden, bitte versuchen Sie es erneut');
        }
        return $this->redirect(['controller' => 'Projects', 'action' => 'edit', $customField->project_id]);
    }
}
