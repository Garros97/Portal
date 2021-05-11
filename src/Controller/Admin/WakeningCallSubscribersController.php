<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * WakeningCallSubscribers Controller
 *
 * @property \App\Model\Table\WakeningCallSubscribersTable $WakeningCallSubscribers
 *
 * @method \App\Model\Entity\WakeningCallSubscriber[] paginate($object = null, array $settings = [])
 */
class WakeningCallSubscribersController extends AppController
{
    public $rights = [
        'index' => ['MANAGE_PROJECTS'],
        'delete' => ['MANAGE_PROJECTS']
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index($wakeningCallId = null)
    {
        $this->paginate = [
            'order' => ['id'],
            'conditions' => ['wakening_call_id' => $wakeningCallId],
        ];

        $wakeningCall = TableRegistry::get('WakeningCalls')->get($wakeningCallId);
        $wakeningCallSubscribers = $this->paginate($this->WakeningCallSubscribers);

        $this->set(compact('wakeningCall', 'wakeningCallSubscribers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Wakening Call Subscriber id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $wakeningCallSubscriber = $this->WakeningCallSubscribers->get($id);
        if ($this->WakeningCallSubscribers->delete($wakeningCallSubscriber)) {
            $this->Flash->success(__('Der Weckruf-Teilnehmer wurde gelöscht.'));
        } else {
            $this->Flash->error(__('Der Weckruf-Teilnehmer konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.'));
        }

        return $this->redirect($this->referer());
    }
}