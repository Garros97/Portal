<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Scales Controller
 *
 * @property \App\Model\Table\ScalesTable $Scales
 */
class ScalesController extends AppController
{
    public $rights = [
        'edit' => ['MANAGE_PROJECTS/?'],
        'delete' => ['MANAGE_PROJECTS/?']
    ];

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->Scales->get($request->getParam('pass')[0], ['contain' => 'Courses'])->course->project_id;
    }


    /**
     * Edit method
     *
     * @param string|null $id Scale id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $scale = $this->Scales->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $scale = $this->Scales->patchEntity($scale, $this->request->getData());
            if ($this->Scales->save($scale)) {
                $this->Flash->success('Die Skala wurde gespeichert.');
                return $this->redirect(['controller' => 'courses', 'action' => 'edit', $scale->course_id]);
            } else {
                $this->Flash->error('Die Skala konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('scale'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Scale id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $scale = $this->Scales->get($id);
        if ($this->Scales->delete($scale)) {
            $this->Flash->success("Die Skala {$scale->name} wurde gelöscht.");
        } else {
            $this->Flash->error('Die Skala konnte nicht gelöscht werden. Bitte versuchen Sie er erneut.');
        }
        return $this->redirect(['controller' => 'Courses', 'action' => 'edit', $scale->course_id]);
    }
}
