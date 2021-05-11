<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Rights Controller
 *
 * @property \App\Model\Table\RightsTable $Rights
 */
class RightsController extends AppController
{
    public $rights = [
        'index' => [],
        'revoke' => ['REVOKE_RIGHTS'],
        'addRight' => ['GRANT_RIGHTS']
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('rights', $this->paginate($this->Rights));
    }

    //TODO: Destroy all sessions of this user now? (Is this possible?)
    public function revoke($rightId, $userId, $subresource)
    {
        $this->request->allowMethod(['post', 'delete']);

        $table = TableRegistry::get('RightsUsers');
        $entity = $table->get([$rightId, $userId, $subresource]); //we use the entity from the join table, unlink() will remove all subresources!
        if ($table->delete($entity)) {
            $this->Flash->success('Das Recht wurde dem Account entzogen. Hinweis: Dies wird erst beim nächsten Login wirksam.');
        } else {
            $this->Flash->error('Das Recht konnte dem Account nicht entzogen werden, bitte versuchen Sie es erneut.');
        }

        return $this->redirect(['controller' => 'users', 'action' => 'edit', $userId]);
    }

    public function addRight($userId)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $rightId = $this->request->getData('new-right-id');
        $right = $this->Rights->get($rightId);

        if ($right->name === 'SUPERADMIN') {
            throw new ForbiddenException('The SUPERADMIN right cannot be given using the web interface. Use bin/cake chell makeSuperadmin instead.');
        }

        $subresource = $this->request->getData('new-right-subresource');
        if ($subresource === null) { //will not be posted if disabled by JS!
            $subresource = 0;
        }
        $fullname = $right->name;
        if ($subresource != 0) {
            $fullname = $right->name .'/' . $subresource;
        }

        if (!$this->Auth->userHasRight($fullname) && !$this->Auth->userHasRight($right->name) && !$this->Auth->userHasRight('SUPERADMIN')) {
            $this->Flash->error('Sie selbst verfügen nicht über das Recht, das Sie vergeben möchten. Das ist nicht erlaubt.');
        } else {
            $user = $this->Rights->Users->get($userId);
            $right->_joinData = new Entity(['subresource' => $subresource, 'right_id' => $right->id, 'user_id' => $user->id]); //we need to be explicit here, see https://github.com/cakephp/cakephp/issues/10665
            if (TableRegistry::get('Users')->getAssociation('rights')->link($user, [$right])) {
                if ($userId === $this->Auth->user('id') && Configure::read('debug')) {
                    $this->Auth->reloadCurrentUser();
                    $this->Flash->warning('Debug-Modus: Die Rechte des aktuellen Accounts wurden neu geladen. Ein erneuter Login ist nicht nötig.');
                }
                $this->Flash->success('Das Recht wurde dem Account zugewiesen. Hinweis: Dies wird erst beim nächsten Login wirksam.');
            } else {
                $this->Flash->error('Das Recht konnte dem Account nicht zugewiesen werden, bitte versuchen Sie es erneut.');
            }
        }

        return $this->redirect(['controller' => 'users', 'action' => 'edit', $userId]);
    }
}
