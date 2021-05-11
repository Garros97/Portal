<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

/**
 * WakeningCalls Controller
 *
 * @property \App\Model\Table\WakeningCallsTable $WakeningCalls
 * @property \App\Model\Table\WakeningCallSubscribersTable $WakeningCallSubscribers
 */

class WakeningCallsController extends AppController
{
    public $rights = [
        'index' => ['MANAGE_PROJECTS'],
        'add' => ['MANAGE_PROJECTS'],
        'edit' => ['MANAGE_PROJECTS'],
        'delete' => ['MANAGE_PROJECTS'],
        'deleteData' => ['MANAGE_PROJECTS'],
        'duplicate' => ['MANAGE_PROJECTS'],
        'toggleVisibility' => ['MANAGE_PROJECTS'],
        'sendTestMail' => ['MANAGE_PROJECTS'],
        'send' => ['MANAGE_PROJECTS']
    ];

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('WakeningCallSubscribers');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['WakeningCallSubscribers'],
            'order' => ['state' => 'asc', 'id' => 'desc']
        ];
        $wakeningCalls = $this->paginate($this->WakeningCalls)->toArray();
        $this->set(compact('wakeningCalls'));
    }

    public function add()
    {
        $wakeningCall = $this->WakeningCalls->newEntity();
        if ($this->request->is('post')) {
            $wakeningCall = $this->WakeningCalls->patchEntity($wakeningCall, $this->request->getData(), ['fieldList' => ['name']]);
            $wakeningCall->urlname = strtolower(Text::slug($wakeningCall->name));
            if ($this->WakeningCalls->save($wakeningCall)) {
                $this->Flash->success('Der Weckruf wurde angelegt, Sie können jetzt weitere Einstellungen ändern.');
                return $this->redirect(['action' => 'edit', $wakeningCall->id]);
            } else {
                $this->Flash->error('Der Weckruf konnte nicht angelegt werden. Bitte versuchen Sie es erneut.');

            }
        }
        $this->set(compact('wakeningCall'));
    }

    public function edit($id = null)
    {
        $wakeningCall = $this->WakeningCalls->find()->contain(['WakeningCallSubscribers'])->where(['id' => $id])->firstOrFail();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $wakeningCall = $this->WakeningCalls->patchEntity($wakeningCall, $this->request->getData());
            $wakeningCall->urlname = strtolower(Text::slug($wakeningCall->name));
            if ($this->WakeningCalls->save($wakeningCall)) {
                $this->Flash->success('Der Weckruf wurde gespeichert.');
            } else {
                $this->Flash->error('Der Weckruf konnte nicht gespeichert werden, bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('wakeningCall'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $wakeningCall = $this->WakeningCalls->get($id);
        if ($this->WakeningCalls->delete($wakeningCall)) {
            $this->Flash->success('Der Weckruf wurde erfolgreich gelöscht.');
        } else {
            $this->Flash->error('Der Weckruf konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function deleteData($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        if ($this->WakeningCallSubscribers->deleteAll(['wakening_call_id' => $id])) {
            $this->Flash->success('Daten des Weckrufs wurden erfolgreich gelöscht.');
        } else {
            $this->Flash->error('Daten des Weckrufs konnten nicht gelöscht werden. Bitte versuchen Sie es erneut.');
        }
        return $this->redirect($this->referer());
    }

    public function toggleVisibility($id = null)
    {
        $wakeningCall = $this->WakeningCalls->get($id);
        $wakeningCall->toggleVisibility();
        $this->WakeningCalls->save($wakeningCall);
        return $this->redirect(['action' => 'index']);
    }

    public function sendTestMail($id = null)
    {
        $wakeningCall = $this->WakeningCalls->get($id);
        $userEmail = TableRegistry::get('Users')->get($this->Auth->user('id'))->get('email');
        $testMail = new Email('default');
        $testMail->setTo($userEmail)
            ->setFrom($wakeningCall->email_from)
            ->setSubject($wakeningCall->email_subject);

        if ($testMail->send($wakeningCall->message)) {
            $this->Flash->success('Testnachricht an <i>'.$userEmail.'</i> erfolgreich versendet.', ['escape' => false]);
        } else {
            $this->Flash->error('Testnachricht konnte nicht versendet werden.');
        }

        return $this->redirect($this->referer());
    }

    public function send($id = null)
    {
        $this->request->allowMethod(['post']);
        $wakeningCall = $this->WakeningCalls->find()->contain(['WakeningCallSubscribers'])->where(['id' => $id])->firstOrFail();
        $userEmail = TableRegistry::get('Users')->get($this->Auth->user('id'))->get('email');

        $emailAdresses = array_column($wakeningCall->wakening_call_subscribers, 'email');
        $email = new Email('default');
        $email->addBcc($userEmail) // send one copy to current user
            ->addBcc($emailAdresses)
            ->setFrom($wakeningCall->email_from) //
            ->setSubject($wakeningCall->email_subject);

        $wakeningCall->setSent();
        if ($email->send($wakeningCall->message) && $this->WakeningCalls->save($wakeningCall)) { // the save function will not be called if send() already fails; save() should not fail here
            $this->Flash->success('Weckruf erfolgreich versendet.');
        } else {
            $this->Flash->error('Weckruf konnte nicht versendet werden.');
        }

        return $this->redirect($this->referer());
    }

    public function duplicate($id = null)
    {
        $this->request->allowMethod(['post']);

        $oldEntity = $this->WakeningCalls->get($id);
        $newEntity = $this->WakeningCalls->duplicateEntity($id);
        $newName = $this->request->getData('new_name');

        if ($oldEntity->name !== $newName) $newEntity->name = $newName; // allow Duplicatable plugin to actually append to the name if it hasn't been changed
        $newEntity->urlname = strtolower(Text::slug($newEntity->name));

        if ($this->WakeningCalls->save($newEntity)) {
            $this->Flash->success('Der Weckruf wurde kopiert, Sie können jetzt weitere Einstellungen ändern.');
            return $this->redirect(['action' => 'edit', $newEntity->id]);
        } else {
            $this->Flash->error('Der Weckruf konnte nicht kopiert werden. Bitte versuchen Sie es erneut.');
            return $this->redirect(['action' => 'index']);
        }
    }
}
