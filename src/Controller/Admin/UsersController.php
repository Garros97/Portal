<?php
namespace App\Controller\Admin;

use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Psr\Log\LogLevel;
use Cake\Routing\Router;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends TagAwareController
{
    public $rights = [ //TODO: Allow subresource access to users? (When?)
        'index' => ['MANAGE_USERS'],
        'add' => ['MANAGE_USERS'],
        'edit' => ['MANAGE_USERS'],
        'delete' => ['MANAGE_USERS'],
        'listUsersWithRight' => ['MANAGE_USERS'],
        'deleteTag' => ['MANAGE_USERS', 'EDIT_TAGS'],
        'resetPassword' => ['MANAGE_USERS'], //this does not allow an admin to login as this user, so no special rights needed (anyone can do this from the frontend!)
        'deleteData' => ['MANAGE_USERS']
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->_showList($this->Users->find());
    }

    /**
     * List all users with the given right in the index view.
     *
     * @param string $right The right.
     */
    public function listUsersWithRight($right = null)
    {
        $rightName = $this->Users->Rights->get($right)->name;

        $this->_showList($this->Users->find('all')->innerJoinWith('Rights', function($q) use ($right) {
            return $q->where(['Rights.id' => $right]);
        }), "Account hat Recht $rightName");
    }

    /**
     * Shows the index view with the given data.
     *
     * @param Query $query A ORM query to show.
     * @param null|string $description A description to show for this filter.
     */
    protected function _showList(Query $query, $description = null)
    {
        $this->set('users', $this->paginate($query));
        $this->set('filter', $description);
        $this->set('_serialize', ['users']);
        $this->viewBuilder()->setTemplate('index');
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fieldList' => ['sex', 'first_name', 'last_name', 'username', 'email', 'password'],
                'validate' => 'signup'
            ]);
            if ($this->Users->save($user)) {
                $this->Users->dispatchEvent('Model.User.afterRegister', ['entity' => $user, 'source' => 'admin', 'password' => $this->request->getData('password')], $this);
                $this->log('User created.', LogLevel::INFO, ['username' => $user->username, 'id' => $user->id, 'source' => 'admin']);

                $this->Flash->success('Der Account wurde angelegt, Sie können jetzt weitere Daten des Accounts ändern.');
                return $this->redirect(['action' => 'edit', $user->id]);
            } else {
                $this->Flash->error('Der Account konnte nicht angelegt werden. Bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->Users->getValidator('default')->setProvider('passed', [
            'admin' => true
        ]);
        $user = $this->Users->get($id, [
            'contain' => ['Groups', 'Rights', 'Tags', 'Registrations.Projects']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            //check for the EDIT_RIGHTS right if the rights of the user where changed
            if ($user->isDirty('rights')) {
                throw new ForbiddenException('You may not edit your rights this way!');
            }

            $this->_handleNewTagRequest($user);

            if ($this->Users->save($user)) {
                $this->Flash->success('Der Account wurde gespeichert.');
                return $this->redirect(['action' => 'edit', $user->id]);
            } else {
                $this->Flash->error('Fehler beim Speichern des Accounts. Bitte versuchen Sie es erneut.');
            }
        }
        $rights = $this->Users->Rights->find('all'); //Find all here, we need to extract the description later
        $tags = $this->Users->Tags->find()->innerJoinWith('Users')->distinct()->extract('name');
        $projects = TableRegistry::get('Projects')->find('list')->applyOptions(['noAutoContainTags' => true]);
        $this->set(compact('user', 'rights', 'tags', 'projects'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id, ['contain' => 'Registrations']);
        if (count($user->registrations) > 0) {
            $this->Flash->error('Der Benutzer ist noch in Projekten angemeldet und kann daher nicht gelöscht werden.');
            return $this->redirect($this->referer());
        }
        if ($this->Users->delete($user)) {
            $this->Flash->success('Der Account wurde gelöscht.');
        } else {
            $this->Flash->error('Fehler beim Löschen des Accounts, bitte versuchen Sie es erneut.');
        }
        $editUrl = Router::url(['controller' => 'Users', 'action' => 'edit', $id]);
        if (substr($this->referer(), -strlen($editUrl)) === $editUrl) return $this->redirect(['controller' => 'Search', 'action' => 'index']); // check only suffix because CakePHP functions do not return equal formats
        return $this->redirect($this->referer());
    }

    /**
     * Deletes all personal data for this user (Note: Not for assosicated registrations!)
     *
     * @param string|null $id User id.
     * @return void Redirects back.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function deleteData($id = null)
    {  
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        
        $user->username = "[DELETED_{$user->id}]";
        $user->email = "[DELETED_{$user->id}]@deleted.del";
        $user->password = md5(time()); //lock account by selecting a random password. //TODO: This is kind of a hack
        $user->first_name = "[DELETED_{$user->id}]";
        $user->last_name = "[DELETED_{$user->id}]";
        $user->street = "[DELETED_{$user->id}]";
        $user->house_number = "0";
        $user->city = "[DELETED_{$user->id}]";
        //the rest of the information is kept for statistics

        if ($this->Users->save($user)) {
            $this->Flash->success('Alle persönlichen Daten wurden gelöscht.');
        } else {
            $this->Flash->error('Fehler beim Löschen der Daten, bitte versuchen Sie er erneut.');
        }
        return $this->redirect($this->referer());
    }

    public function resetPassword($id = null)
    {
        if ($this->request->is('post')) {
            $user = $this->Users->get($id);
            $user->resetPassword();
            $this->Flash->success('Ein neues Passwort wurde an die hinterlegte E-Mail-Adresse verschickt.');
            return $this->redirect(['action' => 'edit', $user->id]);
        }
    }
}
