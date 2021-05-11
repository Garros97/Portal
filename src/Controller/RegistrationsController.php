<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Form\FileUploadForm;
use App\Form\SelectProjectForm;
use App\Model\Entity\Course;
use App\Model\Entity\Group;
use App\Model\Entity\Project;
use App\Model\Entity\Registration;
use App\Model\Entity\UploadedFile;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Database\Query;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Psr\Log\LogLevel;
use Cake\Utility\Hash;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\UploadedFilesTable $UploadedFiles
 */
class RegistrationsController extends AppController
{
    use MailerAwareTrait;

    public $rights = [
        'registerForProject' => [],
        'registrationCompleted' => [],
        'index' => [],
        'delete' => [],
        'edit' => [],
        'getConfirmation' => [],
        'uploadFiles' => [],
        'viewFile' => [],
        'deleteFile' => [],
    ];

    public function initialize()
    {
        parent::initialize();
        if ($this->request->getParam('action') == 'getConfirmation') {
            $this->loadComponent('RequestHandler'); //for PDFs
        }
        $this->loadModel('Projects');
    }

    public function beforeFilter(Event $event)
    {
        $this->Auth->allow('selectProject'); //this can be done without login, we ask for that later
        if ($this->request->action === 'registerForProject') {
            $this->Auth->setConfig('authError', false); //We will show a nice custom message in this case, so hide the default "you may not access this page".
        }
        parent::beforeFilter($event);
    }

    public function selectProject()
    {
        $validProjects = $this->Projects->find('active')->find('list')->where(['visible' => true])->toArray();
        $form = new SelectProjectForm(array_keys($validProjects));

        if ($this->request->is('post')) {
            if (!$form->execute($this->request->getData())) {
                $this->Flash->error('Das gewählte Projekt ist nicht gülitg. Bitte wählen Sie ein gültiges Projekt!');
                $this->log('Invalid project in selectProject. This should not happen. Request data: ' . json_encode($this->request->getData()), LogLevel::WARNING);
            }
            return $this->redirect(['action' => 'registerForProject', $this->Projects->get($this->request->getData('project'))->urlname]);
        }

        $this->set('selectProjectForm', $form);
        $this->set('validProjects', $validProjects);
    }

    public function registerForProject($urlname)
    {
        $user = $this->Users->get($this->Auth->user('id'));
        if ($user->hasTag('details_missing')) {
            $this->Flash->warning('Ihre persönlichen Daten sind noch nicht vollständig. Bitte vervollständigen Sie diese, um die Projektanmeldung vorzunehmen und klicken Sie auf „Daten speichern“');
            $this->request->getSession()->write('activeRegistration', $urlname); //will be cleared on each login
            return $this->redirect(['controller' => 'Users', 'action' => 'edit']);
        }

        /** @var Registration $registration */
        $registration = $this->Registrations->newEntity();
        /** @var Project $project */
        $project = $this->Projects->find()
            ->where(['urlname' => $urlname])
            ->contain([
                'Courses' => function ($q) {
                    return $q->applyOptions(['loadRegistrationCount' => true]);
                },
                'Courses.Tags',
                'CustomFields' => function(Query $q) {
                    return $q->where(['backend_only' => false])
                        ->order('section');
                }
            ])
            ->firstOrFail();

        if ($this->Registrations->findByUserIdAndProjectId($this->Auth->user('id'), $project->id)->count() > 0) {
            if (Configure::read('debug')) {
                $this->Flash->warning('Achtung: Sie sind in diesem Projekt schon angemeldet. Dies ist nur erlaubt, weil aktuell der Debug-Modus aktiv ist.');
            } else {
                $this->Flash->error('Sie sind in diesem Projekt schon angemeldet. Sie könenn Daten zu Ihrer Anmeldung unter "Meine Anmeldungen" ändern.
                Wenn Sie eine weitere Person anmelden möchten, erstellen Sie bitte einen neuen Account.');
                return $this->redirect('/');
            }
        }

        if (!$project->isActive()) {
            $this->Flash->warning('<b>Achtung:</b> Für dieses Projekt ist eine Anmeldung noch nicht/nicht mehr möglich.', ['escape' => false]);
        }

        if ($this->request->is('post')) {
			/* We insert (empty) data for all backend-only fields in the DB. If we don't do this the registration
			 * will not be assosiated with these custom fields, which causes them to disapper in the edit section. (Fixing
			 * it there is way more complicated.)
			 */
            $activeRegistrationForUser = $this->request->getSession()->consume('activeRegistrationForUser');
			$backendFields = $this->Registrations->CustomFields->find()->where(['backend_only' => true, 'project_id' => $project->id]);
			$backendFields = $backendFields->map(function ($x) {
				return ['id' => $x->id, '_joinData' => ['value' => $x->getDefaultValue()]]; //fake entity data
			})->toArray();

            $patchData = $this->request->getData();
            if (!isset($patchData['custom_fields'])) {
                $patchData['custom_fields'] = [];
            }
            $patchData['custom_fields'] = array_merge($patchData['custom_fields'], $backendFields);

            $registration = $this->Registrations->patchEntity($registration, $patchData);
            $registration->project_id = $project->id;
            $registration->project = $project;
            $registration->user_id = $user->id;

            $groupsTable = TableRegistry::get('Groups');
            $group = null;
            if ($project->requiresGroupRegistration()) {
                $group = $this->_createOrJoinGroup($registration, $user);
            }

            $ok = $this->Registrations->getConnection()->transactional(function () use ($groupsTable, $group, $registration, $project, $activeRegistrationForUser) {
                if ($this->Registrations->save($registration)) {
                    //$this->Registrations->loadInto($registration, ['Projects', 'Users', 'Courses']); //why does this not work?
                    $registration->user = $this->Registrations->Users->get($registration->user_id);
                    $registration->courses = $this->Registrations->Courses->find()->innerJoinWith('Registrations',
                        function ($q) use ($registration) {
                            return $q->where(['Registrations.id' => $registration->id]);
                        });
                    //success message is delayed!
                } else {
                    $errors = $registration->getError('_message');
                    $messages = '';
                    if (!empty($errors)) {
                        $messages = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
                    }
                    $this->Flash->error("Die Anmeldung konnte nicht durchgeführt werden, bitte kontrollieren Sie Ihre Eingaben. $messages", ['escape' => false]);
                    return false; //rollback transaction
                }

                //still here, so save was successful!
                if ($project->requiresGroupRegistration()) {
                    $isNewGroup = $group->isNew();
                    if ($groupsTable->save($group)) {
                        if ($activeRegistrationForUser) {
                            $this->Flash->success('Die Anmeldung wurde gespeichert!');
                        } else {
                            $this->Flash->success('Ihre Anmeldung wurde gespeichert!');
                        }
                        if ($isNewGroup) {
                            $this->Flash->success("Die Gruppe {$group->name} wurde angelegt!");
                        } else {
                            if (!$activeRegistrationForUser) {
                                $this->Flash->success("Sie wurden in die Gruppe {$group->name} eingetragen!");
                            }
                        }
                    } else {
                        if ($isNewGroup) {
                            $this->Flash->error('Die Gruppe konnte nicht angelegt werden, bitte kontrollieren Sie Ihre Eingaben.');
                        } else {
                            if ($activeRegistrationForUser) {
                                $this->Flash->error('Der Benutzer konnte nicht in die Gruppe eingetragen werden. Bitte melden Sie sich bei uns.');
                            } else {
                                $this->Flash->error('Sie konnten nicht in die Gruppe eingetragen werden. Bitte melden Sie sich bei uns.');

                            }
                        }
                        return false; //rollback transaction
                    }
                } else { //show delayed message from above
                    if ($activeRegistrationForUser) {
                        $this->Flash->success('Die Anmeldung wurde gespeichert!');
                    } else {
                        $this->Flash->success('Ihre Anmeldung wurde gespeichert!');
                    }
                }

                return true; //save transaction
            });

            if ($ok) {
                $this->Registrations->Users->dispatchEvent('Model.User.afterRegisterForProject',
                    ['user' => $user, 'registration' => $registration], $user);

                if ($activeRegistrationForUser) {
                    $uid = $this->Auth->user('id');
                    $oldUid = $this->request->getSession()->read('login_as.old_uid');
                    $this->request->getSession()->delete('login_as');
                    $this->Auth->setUser($this->Users->find('auth')->where(['id' =>$oldUid])->firstOrFail()->toArray());
                    return $this->redirect(['prefix' => 'admin', 'controller' => 'Users', 'action' => 'edit', $uid]);
                }
                return $this->redirect(['action' => 'registrationCompleted', $project->id]);
            }
        }

        $courses = $this->Projects->Courses->find('list')->where(['project_id' => $project->id]);
        $groups = $this->Projects->Groups->find('list')->where(['project_id' => $project->id]);
        $this->set(compact('project', 'registration', 'courses', 'groups', 'user'));
    }

    public function registrationCompleted($projectId)
    {
        $project = $this->Projects->get($projectId);
        $this->set(compact('project'));
    }

    public function index()
    {
        $this->set('registrations', $this->Registrations->find()
            ->where(['user_id' => $this->Auth->user('id')])
            ->contain(['Projects']));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $registration = $this->Registrations->get($id, ['contain' => ['Projects','Projects.Groups', 'Users']]);
        if ($registration->user_id != $this->Auth->user('id')) {
            throw new ForbiddenException('You may only delete your own registrations!');
        }
        if (!$registration->userMayUnregister()) {
            throw new ForbiddenException('The time for de-registration is over.');
        }

        if ($this->Registrations->delete($registration)) {
            //remove user from all groups in this project
            if (!$this->Users->Groups->unlink($registration->user, $registration->project->groups)) {
                $this->Flash->error('Sie konnten nicht aus allen Gruppen ausgetragen werden. Bitte wenden Sie sich an uns.');
            }

            $this->Flash->success('Sie wurden erfolgreich aus dem Projekt ausgetragen!');
        } else {
            $this->Flash->error('Sie konnten nicht aus dem Projekt ausgetragen werden. Bitte versuchen Sie es später erneut.');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function edit($id = null)
    {
        /** @var Registration $registration */
        $registration = $this->Registrations->get($id, [
            'contain' => [
                'Projects.Courses',
                'Courses',
                'CustomFields' => function ($q) {
                    return $q->where(['CustomFields.backend_only' => false]);
                },
                'Courses.Scales.Ratings',
                'Projects'
            ]
        ]);

        if ($registration->user_id != $this->Auth->user('id')) {
            throw new ForbiddenException('You may only edit your own registrations.');
        }

        /** @var User $user */
        $user = $this->Registrations->Users->get($this->Auth->user('id'));
        $groups = $this->Projects->Groups->find('forProjectContainingUser', ['user_id' => $user->id, 'project_id' => $registration->project_id])
            ->contain('Users');
        $groupsInProject = $this->Projects->Groups->find('list')
            ->where(['project_id' => $registration->project_id])
            ->notMatching('Users', function($q) {
                return $q->where(['Users.id' => $this->Auth->user('id')]);
            });

        $groupData = new Entity(); //not a specific entity here, but this way we can show errors in the form

        if ($this->request->is(['put', 'patch', 'post'])) {
            $selectedButton = $this->request->getData('submitButton');
            if ($selectedButton === 'changeGroups') {
                if (!$registration->project->requiresGroupRegistration() || !$user->is_teacher) {
                    throw new ForbiddenException('You are either not a teacher or this projects does not contain groups');
                }
                $groupData->set($this->request->getData() + [
                        'user_id' => $this->Auth->user('id'),
                        'project_id' => $registration->project_id
                    ]); //we don't have a table, so no patchEntity here
                $group = $this->_createOrJoinGroup($groupData, $user);
                if ($group && $group->isDirty()) TableRegistry::get('Groups')->save($group);
            } else if ($selectedButton === 'changeCourses') {
                $registration = $this->Registrations->patchEntity($registration, $this->request->getData());
                if ($this->Registrations->save($registration)) {
                    $this->Flash->success('Ihre Modulwahl wurde gespeichert.');
                } else {
                    $errors = $registration->getError('_message');
                    $messages = '';
                    if (!empty($errors)) {
                        $messages = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
                    }
                    $this->Flash->error("Die Modulwahl konnte nicht geändert werden. $messages", ['escape' => false]);
                    //clear/reset courses selection so it does not look like it was saved
                    //$this->request = $this->request->withData('courses._ids', null);
                }
                $registration = $this->Registrations->loadInto($registration, ['Courses']); //reload courses with updated registration count
				//This will set $registration->project to null. I really don't understand how loadInto() is supposed to work.
				//The next line works at least.
				//$registration = $this->Registrations->loadInto($registration, ['Projects.Courses']);
				$registration->project = $this->Registrations->Projects->get($registration->project_id, ['contain' => 'Courses']);
            }
        }

        $this->set(compact('registration', 'user', 'groups', 'groupsInProject', 'groupData'));
    }

    public function getConfirmation($id = null)
    {
        $registration = $this->Registrations->get($id, [
            'contain' => [
                'Projects',
                'Courses' => function ($q) {
                    return $q->applyOptions(['loadListPos' => true]);
                },
                'CustomFields' => function ($q) {
                    return $q->where(['CustomFields.backend_only' => false]);
                },
                'Users'
            ]
        ]);

        if ($registration->user_id != $this->Auth->user('id') && !$this->Auth->userHasRight('ADMIN')) {
            throw new ForbiddenException('You may only view your own registrations.');
        }

        $this->set(compact('registration'));
        $this->viewBuilder()
            ->setLayout('confirmation')
            ->setTemplate("confirmations/{$registration->project->confirmation_mail_template}");
    }

    public function uploadFiles($registrationId = null, $courseId = null) //TODO: Move the rest of the methods to UploadedFilesController?
    {
        /** @var Registration $registration */
        $registration = $this->Registrations->get($registrationId, [
            'conditions' => ['user_id' => $this->Auth->user('id')],
            'contain' => [
                'Projects',
            ]
        ]);

        if (!$registration->project->requiresGroupRegistration()) {
            throw new \LogicException('Upload are currently only supported for projects with group registration!');
        }

        /** @var Course $course */
        $course = $this->Registrations->Courses->find()
            ->where(['Courses.id' => $courseId])
            ->innerJoinWith('Registrations', function ($q) use ($registrationId) {
                return $q->where(['Registrations.id' => $registrationId]);
            })
            ->firstOrFail();
            
        if (!$course->uploads_allowed) {
            throw new NotFoundException('This course does not allow uploads');
        }
        
        if ($registration->user_id != $this->Auth->user('id')) {
            throw new ForbiddenException('You may only upload files for your own registrations!');
        }
        if ($registration->project_id != $course->project_id) {
            throw new NotFoundException('Internal error: This course is not part of this project!');
        }

        $allowUploadsOutsideTimeframe = false;
        if (!$course->isInUploadTimeframe()) {
            $oldUid = $this->request->getSession()->read('login_as.old_uid');
            if ($oldUid != null) {
                $this->loadModel('Users');

                $oldUserHasUploadRight = (bool)$this->Users->find()->where(['Users.id' => $oldUid])->innerJoinWith('Rights', function ($q) {
                    return $q->where(['name' => 'UPLOAD_FOR_USER']);
                })->enableHydration(false)->count();
                if ($oldUserHasUploadRight) {
                    $allowUploadsOutsideTimeframe = true;
                }
            }
        }

        $uploadForm = new FileUploadForm();

        $this->loadModel('UploadedFiles');
        
        if ($this->request->is('post')) {
            if (!$course->isInUploadTimeframe() && !$allowUploadsOutsideTimeframe) {
                throw new ForbiddenException('Uploads in this course are currently not possible');
            }
            if ($uploadForm->execute($this->request->getData())) {
                //TODO: Provide a "vacuum" shell, removing all files that are not recorded in the database
                /** @var UploadedFile $fileInfo */
                $fileInfo = $this->UploadedFiles->newEntity();
                $uploadForm->patchEntity($fileInfo);
                $fileInfo->course_id = $courseId;
                $fileInfo->user_id = $this->Auth->user('id');
                $fileInfo->is_deleted = false;
                if ($this->UploadedFiles->save($fileInfo)) {
                    $this->Flash->success('Die Datei wurde erfolgreich hochgeladen!');
                }
                else {
                    $this->Flash->error("Es gab einen Fehler beim Speichern der Metadaten. Ihre Datei wurde nicht korrekt gespeichert. Bitte melden Sie sich bei uns mit dem Code {$fileInfo->disk_filename}");
                }
            } else {
                $this->Flash->error('Es gab einen Fehler beim Upload. Bitte versuchen Sie es erneut.');
            }
        }

        $groupIds = $this->Projects->Groups->find('forProjectContainingUser', ['user_id' => $this->Auth->user('id'), 'project_id' => $registration->project_id])
            ->extract('id')
            ->toArray();

        $files = $this->UploadedFiles->find('ownedByGroups', ['groups' => $groupIds]) //this call must be after the post handler to include the newly uploaded file
            ->where(['course_id' => $courseId, 'is_deleted' => false])
            ->contain('Users');

        $this->set(compact('registration', 'course', 'uploadForm', 'files', 'uploadsActive', 'allowUploadsOutsideTimeframe'));
    }
    
    public function viewFile($id = null)
    {
        $this->loadModel('UploadedFiles');
        $file = $this->UploadedFiles->get($id);
        
        if (!$file->mayUserViewFile($this->Auth->user('id')))
            throw new ForbiddenException('You may only view your own/groups members/ratees files');
        
        if ($file->is_deleted)
            throw new NotFoundException();
        
        return $this->response
            ->withType($file->mime_type)
            ->withFile(ROOT . DS . Configure::read('App.uploads') . DS . 'user_uploads' . DS . $file->disk_filename, ['name' => $file->original_filename, 'download' => true]);
    }
    
    public function deleteFile($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->loadModel('UploadedFiles');
        /** @var UploadedFile $file */
        $file = $this->UploadedFiles->get($id, ['contain' => 'Courses']);
        
        if (!$file->mayUserDeleteFile($this->Auth->user('id')))
            throw new ForbiddenException('You may only delete your own/groups members files');

        if (!$file->course->isInUploadTimeframe())
            throw new ForbiddenException('File deletions are currently not possible.');
        
        $file->is_deleted = true;
        if ($this->UploadedFiles->save($file)) {
            $this->Flash->success('Die Datei wurde erfolgreich gelöscht!');
        }
        else {
            $this->Flash->error("Es gab einen Fehler beim Löschen der Datei. Bitte versuchen Sie es erneut.");
        }
        return $this->redirect($this->Auth->request->referer(true));
    }

    /**
     * @param EntityInterface $groupData
     * @param User $user
     * @return Group The *unsaved* group
     */
    protected function _createOrJoinGroup($groupData, $user)
    {
        $groupsTable = TableRegistry::get('Groups');
        if ($groupData->group == -1) { //a new group is being created
            /** @var Group $group */
            $group = $groupsTable->newEntity();
            $group = $groupsTable->patchEntity($group, [
                'name' => $groupData->newgroup_name,
                'password' => $groupData->newgroup_password,
                'project_id' => $groupData->project_id,
                'users' => ['_ids' => [$groupData->user_id]]
            ], ['accessibleFields' => ['project_id' => true]]);
            return $group;
        } else { //join an existing group
            /** @var Group $group */
            $group = $groupsTable->get($groupData->group, ['contain' => ['Users', 'Projects']]);
            if ($group->password != $groupData->group_password) {
                $groupData->setError('group_password', 'Das Passwort ist nicht korrekt');
                return false;
            } else if ($group->getNonTeacherMemberCount() >= $group->project->max_group_size && !$user->is_teacher) {
                $groupData->setError('group', 'Diese Gruppe ist schon voll');
                return false;
            } else {
                //join the group
                $group->users[] = $user;
                $group->setDirty('users', true);
                return $group;
            }
        }
    }
}
