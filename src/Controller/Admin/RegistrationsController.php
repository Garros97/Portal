<?php
namespace App\Controller\Admin;

use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class RegistrationsController extends TagAwareController
{
    use MailerAwareTrait;

    public $rights = [
        'index' => ['MANAGE_PROJECTS/$0'],
        'edit' => ['MANAGE_PROJECTS/?'],
        'delete' => ['MANAGE_PROJECTS/?'],
        'deleteTag' => ['MANAGE_PROJECTS/$0', 'EDIT_TAGS'],
        'resendConfirmation' => ['MANAGE_PROJECTS/?'],
        'registerUserForProject' => ['MANAGE_PROJECTS/?', 'MANAGE_USERS']
    ];

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->Registrations->get($request->getParam('pass')[0])->project_id;
    }

    public function index($projectId = null, $courseId = null)
    {
        $this->set('project', $this->Registrations->Projects->get($projectId));
        $query = $this->Registrations->find()->contain(['Users'])->where(['Registrations.project_id' => $projectId]);
        $this->paginate = [
            'order' => ['created' => 'asc'], //default ordering
            'sortWhitelist' => ['id', 'Users.username', 'Users.email', 'Users.first_name', 'Users.last_name', 'created'], //needed here to allow sorting on assoc data
        ];

        if ($courseId) {
            $query->matching('Courses', function ($q) use ($courseId) {
                return $q->where(['Courses.id' => $courseId]);
            });
            $this->paginate= [
                'order' => ['CoursesRegistrations.created' => 'asc'], //default ordering
                'sortWhitelist' => ['CoursesRegistrations.created'],
            ];
        }

        $mapper = function ($registration, $key, $mapReduce) use ($courseId) {
            $status = 'registered';
            if ($courseId !== null) {
                $course = $registration->_matchingData['Courses'];
                if ($course->isListPosOnWaitingList($course->list_pos)) {
                    $status = 'waitingList';
                }
            }
            $mapReduce->emitIntermediate($registration, $status);
        };
        $reducer = function ($registrations, $status, $mapReduce) {
            $mapReduce->emit(collection($registrations)->extract('user.email')->toArray(), $status);
        };
        $emails = $this->Registrations->find()->contain(['Users'])->where(['Registrations.project_id' => $projectId]);
        if ($courseId !== null) {
            $emails = $emails->matching('Courses', function ($q) use ($courseId) {
                return $q->where(['Courses.id' => $courseId]);
            });
        }
        $emails = $emails->mapReduce($mapper, $reducer)->toArray();

        $this->set('registrations', $this->paginate($query));
        $this->set('emails', $emails);
        $this->set('course', $this->Registrations->Courses->findById($courseId)->first());
        $this->set('courses', $this->Registrations->Courses->find('list')->where(['project_id' => $projectId])->applyOptions(['noAutoContainTags' => true, 'noLoadRegistrationCount' => true])->toArray());
    }

    /**
     * Edit method
     *
     * @param string|null $id Registration id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $registration = $this->_getRegistration($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $registration = $this->Registrations->patchEntity($registration, $this->request->getData());
            $this->_handleNewTagRequest($registration);

            $deferredMails = [];
            foreach ($registration->custom_fields as $field) {
                if ($field->_joinData->isDirty('value') &&  $registration->project->hasTag('notifyChange_' . $field->name)) {
                    //convert list in form "a:b,c:d" to array in form [a => b, c => d];
                    $vals = explode(',', $registration->project->getTagValue('notifyChange_' . $field->name));
                    array_walk($vals, function(&$val) { $val = explode(':', $val, 2); });
                    $vals = array_combine(array_column($vals, 0), array_column($vals, 1));
                    if (isset($vals[$field->_joinData->value])) {
                        $mailTemplate = $vals[$field->_joinData->value];
                        $userMailer = $this->getMailer('User');
                        $deferredMails[] = function() use ($userMailer, $registration, $field, $mailTemplate) { //enqueue lamda function
                            $userMailer->send('notifyCustomFieldChanged', [$registration, $mailTemplate, $field->name, $field->_joinData->value]);
                        };
                    }
                }
            }

            $registration->editAllowed = ($this->Auth->userHasRight('ADMIN') && $this->Auth->userHasRight('MANAGE_PROJECTS')); // allow admins to edit registrations after register_end

            if ($this->Registrations->save($registration)) {
                $this->Flash->success('Die Änderungen wurden gespeichert.');
                //saving was sucessfull, now send mails. (Note that we can check for isDirty() here anymore!)
                foreach($deferredMails as $mail) {
                    $mail();
                }
            } else {
                $this->Flash->error('Die Änderungen konnten nicht gespeichert werden, bitte versuchen Sie es erneut.');
            }
            $registration = $this->_getRegistration($id); //Reload registration to include updated counters
        }

        $tags = $this->Registrations->Tags->find()->innerJoinWith('Registrations')->distinct()->extract('name');
        $this->set(compact('registration', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $registration = $this->Registrations->get($id);
        if ($this->Registrations->delete($registration)) {
            $this->Flash->success('Der Account wurde erfolgreich abgemeldet.');
        } else {
            $this->Flash->error('Der Account konnte nicht abgemeldet werden, bitte versuchen Sie erneut.');
        }
        $editUrl = Router::url(['controller' => 'Registrations', 'action' => 'edit', $id]);
        if (substr($this->referer(), -strlen($editUrl)) === $editUrl) return $this->redirect(['controller' => 'Registrations', 'action' => 'index', $registration->project_id]); // check only suffix because CakePHP functions do not return equal formats
        return $this->redirect($this->referer());
    }

    public function resendConfirmation($id = null) {
        $registration = $this->Registrations->find()
            ->where(['Registrations.id' => $id])
            ->contain(['Courses', 'Projects', 'Users'])->firstOrFail();
        $this->getMailer('User')->send('registrationComplete', [$registration->user, $registration]);
        return $this->redirect($this->referer());
    }

    public function registerUserForProject($userId = null, $projectId = null)
    {
        $urlname = TableRegistry::get('Projects')->get($projectId)->get('urlname');
        $newUser = $this->Users->find('auth')->where(['id' => $userId])->firstOrFail();
        if (!collection($newUser->rights)->every(function ($r) {
            return $this->Auth->userHasRight($r) || $this->Auth->userHasRight(explode('/', $r, 2)[0]);
        })) {
            $this->Flash->error('Der Benutzer, den Sie für ein Projekt anmelden möchten, hat mehr Rechte als Sie. Dies ist nicht erlaubt.');
            return $this->redirect($this->request->referer());
        }
        $this->request->getSession()->write('login_as.old_uid', $this->Auth->user('id'));
        $this->request->getSession()->write('login_as.old_username', $this->Auth->user('username'));
        $this->Auth->setUser($newUser->toArray());
        $this->request->getSession()->write('activeRegistrationForUser', $urlname);
        return $this->redirect(['prefix' => false, 'controller' => 'Registrations', 'action' => 'registerForProject', $urlname]);
    }

    /**
     * Extracted common code.
     *
     * @param $id
     * @return \Cake\Datasource\EntityInterface|mixed
     */
    protected function _getRegistration($id)
    {
        $registration = $this->Registrations->get($id, [
            'contain' => [
                'Courses' => function ($q) {
                    return $q->applyOptions(['loadRegistrationCount' => true]);
                },
                'CustomFields',
                'Projects',
                'Projects.CustomFields',
                'Users',
                'Projects.Courses' => function ($q) {
                    return $q->applyOptions(['loadRegistrationCount' => true]);
                },
                'Users.Groups'
            ]
        ]);

        return $registration;
    }
}
