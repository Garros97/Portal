<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Psr\Log\LogLevel;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Controller\Component\NewsletterSignupComponent $NewsletterSignup
 */
class UsersController extends AppController
{
    public $rights = [
        'logout' => [],
        'edit' => [],
        'loginAs' => ['LOGIN_AS'],
        'revertLoginAs' => [], //this does _not_ require the LOGIN_AS right, as the rights for this are checked as the impersonated user!
        'changePassword' => [],
    ];
	
	public function initialize()
    {
        parent::initialize();
        $this->loadComponent('NewsletterSignup');
    }

    public function beforeFilter(Event $event)
    {
        $this->Auth->allow(['logout', 'add', 'resetPassword']); //actions everybody may use (public)
        parent::beforeFilter($event);
    }

    public function login()
    {
        if ($this->request->is('post')) //TODO: Don't populate login form from failed register attempt?
        {
            $user = $this->Auth->identify();
            if ($user)
            {
                $this->Auth->setUser($user);
                $userEntity = $this->Users->get($user['id']);
                $this->Users->dispatchEvent('Model.User.login', [$userEntity], $userEntity);

                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $userEntity->password = $this->request->getData('password');
                }

                $this->Users->save($userEntity); //user might got updated in events
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error('Accountname oder Passwort sind ungültig');
        }
        $user = $this->Users->newEntity(); //for the integrated sign-up form
        $willRedirectToRegisterForProject = $this->_willRedirectToRegisterForProject();
        $this->set(compact('user') + ['showSelectAccountNotice' => $willRedirectToRegisterForProject, 'showConfirmDuplicateEmail' => false]);
    }

    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function add()
    {
		$showConfirmDuplicateEmail = false;
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fieldList' => ['sex', 'first_name', 'last_name', 'username', 'password', 'email'],
                'validate' => 'signup'
            ]);
            $user->last_login = Time::now();
            $user->addTag('details_missing');
            if ($this->Users->save($user)) {
                $this->Users->dispatchEvent('Model.User.afterRegister', ['entity' => $user, 'source' => 'self'], $this);
                $this->log('User created.', LogLevel::INFO, ['username' => $user->username, 'id' => $user->id, 'source' => 'self']);

                $user = $this->Auth->identify(); //the registration form can actually be used as a login form, at least in terms of compatible field names :) This is an easy way to get the $user aray in the correct form
                if ($user) {
                    $this->Auth->setUser($user); //new user is now logged, for convenience
                    $this->Flash->success('Ihr Account wurde angelegt. Sie können nun fortfahren.');
                } else {
                    $this->Flash->error('Der Account wurde angelegt, Sie konnten aber nicht automatisch eingeloggt werden. Bitte loggen Sie sich ein, um fortzufahren.');
                }

                if ($this->request->getData('newsletter')) {
                    if ($this->NewsletterSignup->signup($this->request->getData('email'))) {
                        $this->Flash->success('Sie haben eine Einladung zum Nachrichtenbrief per E-Mail erhalten. Bitte klicken Sie auf den enthaltenen Link.');
                    } else {
                        $this->Flash->error("Leider konnten Sie nicht automatisch für den Nachrichtenbrief angemeldet werden.
                            Bitte tragen Sie sich manuell <a href=\"{$this->NewsletterSignup->getSignupLink()}\" target=\"_blank\">auf der Anmeldeseite</a> ein.", ['escape' => false]);
                    }
                }

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error('Der Account konnte nicht angelegt werden. Bitte versuchen Sie es erneut.');
            }
        }
        $this->viewBuilder()->setTemplate('login'); //the add form is integrated in the login page
        $this->set(compact('user', 'showConfirmDuplicateEmail') + ['showSelectAccountNotice' => $this->_willRedirectToRegisterForProject()]);
    }

    public function edit()
    {
        $user = $this->Users->get($this->Auth->user('id'));

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($user->isDirty('username')) {
                $this->Flash->error('Sie können Ihren Accountnamen nicht ändern.');
                $originalUsername = $user->getOriginal('username');
                $user->username = $originalUsername;
                $this->request = $this->request->withData('username', $originalUsername);
                $user->setError('username', 'Sie können Ihren Accountnamen nicht ändern.');
                $this->log("Disallowed change of username detected, UID={$user->id}", LogLevel::WARNING);

            } else {
                $user->removeTag('details_missing');

                if ($this->Users->save($user)) {
                    $this->Flash->success('Ihre Daten wurden gespeichert.');
                    $activeRegistration = $this->request->getSession()->consume('activeRegistration');
                    if ($activeRegistration) {
                        return $this->redirect(['controller' => 'Registrations', 'action' => 'registerForProject', $activeRegistration]);
                    }
                } else {
                    $this->Flash->error('Fehler beim Speichern Ihrer Daten. Bitte versuchen Sie es erneut.');
                }
            }
        }

        $this->set(compact('user'));
		$this->set([
			'newsletterSignupUrl' => $this->NewsletterSignup->getSignupLink(),
			'newsletterUnsubUrl' => $this->NewsletterSignup->getUnsubscribeLink(),
		]);
    }

    public function loginAs($id = null)
    {
        $newUser = $this->Users->find('auth')->where(['id' => $id])->firstOrFail();
        if (!collection($newUser->rights)->every(function ($r) {
            //has right with exact name or general form of right
            return $this->Auth->userHasRight($r) || $this->Auth->userHasRight(explode('/', $r, 2)[0]);
        })) {
            $this->Flash->error('Der Account zu dem Sie welchsen möchten, hat mehr Rechte als Sie. Dies ist nicht erlaubt.');
            return $this->redirect($this->request->referer());
        }

        $this->request->getSession()->write('login_as.old_uid', $this->Auth->user('id'));
        $this->request->getSession()->write('login_as.old_username', $this->Auth->user('username'));
        $this->Auth->setUser($newUser->toArray());
        return $this->redirect(['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'home']);
    }

    public function revertLoginAs()
    {
        $oldUid = $this->request->getSession()->read('login_as.old_uid');
        $this->request->getSession()->delete('login_as');
        $this->Auth->setUser($this->Users->find('auth')->where(['id' =>$oldUid])->firstOrFail()->toArray());
        return $this->redirect(['prefix' => 'admin', 'controller' => 'Pages', 'action' => 'display', 'home']);
    }

    public function resetPassword()
    {
        if ($this->request->is('post')) {
            $usernameOrEmail = $this->request->getData('username_or_email');
            /** @var Query $users */
            $users = $this->Users->findByUsernameOrEmail($usernameOrEmail, $usernameOrEmail);
            $cnt = $users->count();
            $usernameOrEmail = h($usernameOrEmail);
            if ($cnt === 0) {
                $this->Flash->error("Es konnte kein Account mit dem Accountnamen bzw. der E-Mail-Adresse <i>$usernameOrEmail</i> gefunden werden.", ['escape' => false]);
            } else if ($cnt > 1) {
                $imprintUrl = Router::url(['controller' => 'Pages', 'action' => 'display', 'imprint']);
                $this->Flash->error("Es wurde mehr als ein Account mit dem Accountnamen bzw. der E-Mail-Adresse <i>$usernameOrEmail</i> gefunden. <a href=\"$imprintUrl\">Bitte wenden Sie sich an uns</a>.", ['escape' => false]);
            } else {
                $user = $users->firstOrFail();
                $user->resetPassword();
                $this->Flash->success('Ein neues Passwort wurde an Ihre hinterlegte E-Mail-Adresse verschickt.');
            }
        }
    }

    public function changePassword()
    {
        if ($this->request->allowMethod(['patch', 'post', 'put']));

        $id = $this->request->getData('uid');
        if ($this->Auth->user('id') != $id) {
            //for changing the password, the old password has to be known, so the user can login anyway. But we might
            //introduce any kind of login restrictions, which might be undermined by this. (And why would you change
            //a password without logging in first?)
            throw new ForbiddenException('Please login as the user for which you wan\'t to change the password.');
        }
        $user = $this->Users->get($id);
        if ($user->checkPassword($this->request->getData('old_password'))) {
            $pw1 = $this->request->getData('new_password1');
            $pw2 = $this->request->getData('new_password2');
            if (!empty($pw1) && $pw1 === $pw2) {
                $user->password = $pw1;
                if ($this->Users->save($user)) {
                    $this->Flash->success('Ihr Passwort wurde erfolgreich geändert!');
                } else {
                    $this->Flash->error('Ihr Passwort konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.');
                }
            } else {
                $this->Flash->error('Die angegebenen Passworter stimmen nicht überein. Bitte versuchen Sie es erneut.');
            }
        } else {
            $this->Flash->error('Das alte Passwort ist nicht korrekt. Bitte versuchen Sie es erneut.');
        }
        return $this->redirect(['action' => 'edit', $user->id]);
    }

    /**
     * Returns true, when the user will be redirected to a project registration after signup/login.
     *
     * @return bool
     */
    protected function _willRedirectToRegisterForProject()
    {
        $route = Router::parseRequest(new ServerRequest($this->Auth->redirectUrl()));
        return $route['controller'] === 'Registrations' && $route['action'] === 'registerForProject';
    }
}
