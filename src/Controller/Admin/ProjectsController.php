<?php
namespace App\Controller\Admin;

use App\Form\QisUploadForm;
use App\Model\Entity\ComboBoxValue;
use App\Model\Entity\Course;
use App\Model\Entity\CustomField;
use App\Model\Entity\CustomFieldType;
use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use DateTime;
use App\Form\FileUploadForm;
use Cake\Filesystem\File;

/**
 * Projects Controller
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 */
class ProjectsController extends TagAwareController
{
	public $rights = [
        'index' => ['MANAGE_PROJECTS/any'],
        'add' => ['MANAGE_PROJECTS'],
        'edit' => ['MANAGE_PROJECTS/$0'],
        'addCourses' => ['MANAGE_PROJECTS/$0'],
        'delete' => ['MANAGE_PROJECTS/$0', 'DELETE_PROJECTS'],
        'qisImportUpload' => ['MANAGE_PROJECTS/$0', 'QISIMPORT'],
        'qisImportExecute' => ['MANAGE_PROJECTS/$0', 'QISIMPORT'],
        'addDefaultCustomFields' => ['MANAGE_PROJECTS/$0'],
        'deleteTag' => ['MANAGE_PROJECTS/$0', 'EDIT_TAGS'],
        'previewConfirmation' => ['MANAGE_PROJECTS/$0'],
        'exgroupsOverview' => ['MANAGE_PROJECTS/$0'],
        'duplicate' => ['MANAGE_PROJECTS'],
        'makeRater' => ['GRANT_RIGHTS', 'RATE/$0'],
        'drawParticipants' => ['MANAGE_PROJECTS/$0', 'MANAGE_USERS'],
        'restore' => ['MANAGE_PROJECTS/$0'],
        'removeConfirmationAppendix' => ['MANAGE_PROJECTS/$0']
    ];

    public function initialize()
    {
        if ($this->request->getParam('action') == 'previewConfirmation') {
            $this->loadComponent('RequestHandler'); //for pdf view
        }
        parent::initialize();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Courses', 'Groups', 'Registrations'],
            'order' => ['register_end' => 'desc']
        ];

        $accessibleSubresources = $this->Auth->userGetAccessibleSubresourceIds('MANAGE_PROJECTS');
        if ($accessibleSubresources !== true) { //no global access
            $this->paginate += ['conditions' => ['id IN' => $accessibleSubresources]];
        }

        /*
         * Sort projects: first, sort by register_end using paginate options (above). Then, use a custom usort to sort
         * the active projects of the current page so that the most current projects are displayed on top.
         * This still has to apply the sorting which has already been done by paginate, because
         * usort doesn't keep the order (even if returning 0 which should do nothing).
         */
        $projects_arr = $this->paginate($this->Projects)->toArray();
        usort($projects_arr, function($a, $b) {
            if ($b->isActive()) {
                if ($a->isActive()) {
                    if ($a->register_end < $b->register_end) {
                        return -1;
                    } else {
                        return 1;
                    }
                } else {
                    return 1;
                }
            } else if ($a->register_end < $b->register_end) {
                return 1;
            } else {
                return -1;
            }
        });

        $this->set('projects',$projects_arr);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $project = $this->Projects->newEntity();
        $project->setAccess('urlname', true); //allow setting urlname for add
        if ($this->request->is('post')) {
            $project = $this->Projects->patchEntity($project, $this->request->getData(), ['fieldList' => ['name', 'urlname']]);
            $project = $this->Projects->patchEntity($project, [ //set some default values
                'register_start' => new DateTime('+1 week'),
                'register_end' => new DateTime('+2 week')
            ],
                ['fieldList' => ['register_start', 'register_end'], 'validate' => false]); //don't validate here, entity is still new => name/urlname would be required
            if ($this->Projects->save($project)) {
                $this->Flash->success('Das Projekt wurde anglegt, Sie können jetzt weitere Einstellungen ändern.');
                return $this->redirect(['action' => 'edit', $project->id]);
            } else {
                $this->Flash->error('Das Projekt konnte nicht angelegt werden. Bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('project'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Project id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $project = $this->Projects->get($id, [
            'contain' => [
                'Courses' => function ($q) {
                    /** @var Query $q */
                    return $q
                        ->select(['scale_cnt' => $q->func()->count('Scales.id'), 'Courses.project_id'])
                        ->leftJoinWith('Scales')
                        ->group('Courses.id')
                        ->enableAutoFields();
                },
                'CustomFields' => [
                    'sort' => ['CustomFields.section']
                ]
            ]
        ]);
        $customField = $this->Projects->CustomFields->newEntity();
        $uploadForm = new FileUploadForm();
        $requestData = $this->request->getData();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $project = $this->Projects->patchEntity($project, $this->request->getData());

            $this->_handleNewTagRequest($project);

            if ($this->request->getData('file') !== null) { // user tries to upload appendix for confirmation template
                $requestData['file']['appendix'] = true;
                if ($uploadForm->execute($requestData)) {
                    $project->addTag('confirmationAppendix', $uploadForm->getFileName());
                    if ($this->Projects->save($project)) {
                        $this->Flash->success('Der Anhang wurde hinzugefügt.');
                    } else {
                        $this->Flash->error('Fehler beim Speichern des Anhangs. Bitte versuchen Sie es erneut.');
                    }
                } else {
                    $uploadForm->validate($requestData); // somehow automatic validation does not work here: execute validate() so the errors are returned back to the form
                    $this->Flash->error('Fehler beim Upload des Anhangs. Bitte versuchen Sie es erneut.');
                }
            } else if ($this->Projects->save($project)) {
                $this->Flash->success('Das Projekt wurde gespeichert.');
            } else {
                $this->Flash->error('Das Projekt konnte nicht gespeichert werden, bitte versuchen Sie es erneut.');
            }
        }

        $tags = $this->Users->Tags->find()->innerJoinWith('Projects')->distinct()->extract('name');
        $this->set(compact('project', 'customField', 'tags', 'uploadForm'));
    }

    public function addCourses($id = null)
    {
        $count = $this->request->getData('add-course-count');
        $courseTable = TableRegistry::get('Courses');

        for ($i = 1; $i <= $count; $i++) { //this loops runs from 1 to count + 1 for better names
            $course = $courseTable->newEntity([
                'name' => "Neuer Kurs $i",
                'project_id' => $id,
            ]);
            $courseTable->save($course);
        }

        $msg = $count . ($count == 1 ? ' neuer Kurs' : ' neue Kurse');
        $this->Flash->success("$msg wurden angelegt, Sie können diese nun bearbeiten.");
        return $this->redirect(['action' => 'edit', $id]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Project id.
     * @return void Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $project = $this->Projects->get($id);
        if ($this->Projects->delete($project)) {
            $this->Flash->success('Das Projekt wurde erfolgreich gelöscht.');
        } else {
            $this->Flash->error('Das Projekt konnte nicht gelöscht werden. Bitte versuchen Sie es erneut.');
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Preview Confirmation method
     *
     * @param string|null $id Project id.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function previewConfirmation($id = null, $sendMail = false)
    {
        $project = $this->Projects->get($id);
        $registration = $this->Projects->Registrations->newEntity([
            'id' => 0,
            'project_id' => $project->id,
            'user' => [
                'id' => 0,
                'username' => 'test-user',
                'email' => 'test@mail.de',
                'first_name' => 'Test',
                'last_name' => 'Benutzer',
                'sex' => 'm',
                'Street' => 'Teststraße',
                'house_number' => '123',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'birthday' => new Time('now'),
                'created' => new Time('now'),
                'modified' => new Time('now'),
                'last_login' => new Time('now'),
                'groups' => [
                    [
                        'id' => 0,
                        'name' => 'Testgruppe',
                        'project_id' => $project->id,
                        'password' => 'test',
                    ]
                ]
            ],
            'created' => new Time('now'),
            'courses' => [
                [
                    'project_id' => $project->id,
                    'name' => 'Test-Kurs',
                    'descriptipon' => 'Beschreibung des Test-Kurses',
                    'sort' => 'x',
                    'max_users' => 10,
                    'waiting_list_length' => 2, //TODO: One course where the user is on the waiting list to show the notice?
                    'uploads_allowed' => true,
                    'uploads_start' => new Time('-10 min'),
                    'uploads_end' => new Time('+10 min')
                ]
            ],
            'custom_fields' => [
                [
                    'project_id' => $project->id,
                    'section' => 'Test-Abschnitt',
                    'name' => 'Test-Feld',
                    'help_text' => 'Dies ist ein Test-Feld',
                    'type' => CustomFieldType::Text,
                    'backend_only' => false,
                    '_joinData' => [
                        'value' => 'Test-Wert'
                    ]
                ]
            ]
        ], ['accessibleFields' => ['id' => true], 'validate' => false]);
        $registration->project = $project;

        if ($sendMail) {
            $user = TableRegistry::get('Users')->get($this->Auth->user('id'));
            $this->getMailer('User')->send('registrationComplete', [$user, $registration]);
            return $this->redirect($this->referer());
        }

        $this->set(compact('registration'));
        $this->viewBuilder()
            ->setTemplatePath('Registrations')
            ->setLayout('confirmation')
            ->setTemplate("confirmations/{$project->confirmation_mail_template}");
    }

    /**
     * QisImportUpload method
     *
     * @param string|null $id Project id.
     */
    public function qisImportUpload($id = null)
    {
        $uploadForm = new QisUploadForm();
        if ($this->request->is('post')) {
            if ($uploadForm->execute($this->request->getData())) {
                $this->Flash->success('Die Datei wurde erfolgreich hochgeladen!');
            } else {
                $this->Flash->error('Es gab einen Fehler beim Upload. Bitte versuchen Sie es erneut.');
            }
        }
        $this->set(compact('uploadForm', 'id'));
    }

    /**
     * QisImportExecute method
     *
     * @param string|null $id Project id.
     * @param string|null $filename Filename of XML file.
     * @return void Redirects to edit
     */
    public function qisImportExecute($id = null, $filename = null)
    {
        $project = $this->Projects->get($id);

        //get courses currently in the project
        //there is one main problem: We need the QisId to match the courses with the XML file.
        //but courses not imported from the QIS might not have an QisId, but we need something
        //to identify them. So we prefix the QisIds with 'q' and the normal course Ids with 'c'.
        $coursesInProject = $this->Projects->Courses->find()->where(['project_id' => $project->id])->contain(['Tags'])->combine(
            function($entity) {
                $qisid = $entity->getTagValue('qisid', 0);
                if ($qisid === 0)
                    return 'c' . $entity->id;
                else
                    return 'q' . $qisid;
            },
            'name')->toArray();

        //get the courses in the file
        $coursesInFile = array();
        $xmlDoc = new \DOMDocument();
        $xmlDoc->load(ROOT . DS . Configure::read('App.uploads') . DS . 'qis_import' . DS . $filename);

        foreach($xmlDoc->getElementsByTagName('Veranstaltung') as $course)
        {
            /** @var \DOMElement $course */
            if($course->getElementsByTagName('VTyp')[0]->textContent === 'TU')
                continue; //Taken from the original import script, don't know the reason for this

            $courseName = $course->getElementsByTagName('VName')[0]->textContent;
            $courseId = $course->getElementsByTagName('VeranstNummer')[0]->textContent;

            $coursesInFile['q' . $courseId] = $courseName;
        }

        $newCourses = array_diff_key($coursesInFile, $coursesInProject);
        $deletedCourses = array_diff_key($coursesInProject, $coursesInFile);

        //handle POST data, if present
        if ($this->request->is(['post'])) {
            $courseTable = TableRegistry::get('Courses');
            $qisidTag = TableRegistry::get('Tags')->findOrCreate(['name' => 'qisid']);
            //insert new courses
            if ($this->request->getData('new') !== null) {
                foreach((new Collection($this->request->getData('new')))->filter(function($x) {
                    return $x === '1';
                }) as $qisid => $_) {
                    /** @var Course $course */
                    $course = $courseTable->newEntity([
                        'name' => $newCourses[$qisid],
                        'tags' => [[
                            'id' => $qisidTag->id,
                            '_joinData' => [
                                'value' => substr($qisid, 1) //cut off the 'q'
                            ]]
                        ],
                        'project_id' => $id
                    ]);
                    $courseTable->save($course);
                }
            }

            //delete old courses
            if ($this->request->getData('old') !== null) {
                foreach ((new Collection($this->request->getData('old')))->filter(function ($x) {
                    return $x === '1';
                }) as $cid => $_) {
                    switch (substr($cid, 0, 1)) {
                        case 'c':
                            $courseTable->delete($courseTable->get(substr($cid, 1)));
                            break;
                        case 'q':
                            $courseTable->delete($courseTable->find()->innerJoinWith('Tags', function ($q) use ($cid) {
                                return $q->where(['Tags._joinData' => substr($cid, 1)]);
                            })->
                            firstOrFail());
                            break;

                        default:
                            throw new Exception("Invalid course id $id (did not start with q/c");
                    }
                }
            }

            $this->Flash->success('Import erfolgreich');
            return $this->redirect(['action' => 'edit', $id]);
        }

        $this->set(compact('project', 'filename', 'newCourses', 'deletedCourses'));
    }

    /**
     * AddDefaultCustomFields method
     *
     * Add the custom fields "Name der Schule/PLZ/Ort/Klassenstufe,Wie haben Sie von dem Projekt erfahren"
     * to the project.
     *
     * @param string|null $id Project id.
     * @return void Redirects to edit.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function addDefaultCustomFields($id = null)
    {
        $this->request->allowMethod('post');

        $project = $this->Projects->get($id);

        $defaults = [
            'backend_only' => false, //Let users see their own input
            'section' => 'Angaben zur Schule',
        ];

        $field1 = new CustomField([
            'name' => 'Name der Schule*',
            'type' => CustomFieldType::Text,
        ] + $defaults);
        $field2 = new CustomField([
            'name' => 'PLZ der Schule*',
            'type' => CustomFieldType::Number,
        ] + $defaults);
        $field3 = new CustomField([
            'name' => 'Ort der Schule*',
            'type' => CustomFieldType::Text,
        ] + $defaults);
        $field4 = new CustomField([
                'name' => 'Klassenstufe*',
                'type' => CustomFieldType::Dropdown,
                'combo_box_values' => '---, 8, 9, 10, 11, 12, 13',
            ] + $defaults);
        $field5 = new CustomField([
                'name' => 'Wie haben Sie von dem Projekt erfahren?',
                'type' => CustomFieldType::Dropdown,
                'combo_box_values' => '---,Lehrer(in),Mitschüler(in),Eltern,Flyer,Artikel in einer Tageszeitung,Website uniKIK Schulprojekte,Uni-Website,'
                    .'andere Website,Soziale Netzwerke (Facebook oder Twitter),E-Mail/Weckruf,Nachrichtenbrief,Inforveranstaltung an der Schule,IdeenExpo,Bildungsmesse,'
                    .'Beratungsgespräch,Zentrale Studienberatung,Sonstiges',
            'backend_only' => false
            ]);

        //Note: This will _not_ delete the other entries, has-many entries are _never_ deleted, just added or modified
        //see http://book.cakephp.org/3.0/en/orm/saving-data.html#saving-hasmany-associations
        $project->custom_fields = [$field1, $field2, $field3, $field4, $field5];
        $project->setDirty('custom_fields', true);
        $this->Projects->save($project);

        $this->Flash->success('Die Zusatzfelder für die Schule wurden angelegt.');
        return $this->redirect(['action' => 'edit', $id]);
    }

    public function exgroupsOverview($id = null)
    {
        $project = $this->Projects->get($id);

        $tags = TableRegistry::get('Tags')->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ]);
        $tags = $tags
            ->innerJoinWith('Courses', function ($q) use ($id) {
                return $q->where(['Courses.project_id' => $id])
                    ->applyOptions(['noAutoContainTags' => true]);
            })
            ->where(['Tags.name LIKE' => 'exgroup_%'])
            ->select(['id', 'name' => $tags->func()->substring(['Tags.name' => 'literal', 9 => 'literal'])], true) //strlen('exgroup_') === 8, MySQL is one-based
            ->enableHydration(false)
            ->toArray();

        $dataQuery = $this->Projects->Courses->find()
            ->where(['project_id' => $id])
            ->select('name')
            ->applyOptions(['noAutoContainTags' => true, 'noLoadRegistrationCount' => true])
            ->enableHydration(false);

        $tagsCoursesTable = TableRegistry::get('TagsCourses');

        foreach ($tags as $tagId => $tagName) {
            $subquery = $tagsCoursesTable->find();
            $subquery = $subquery
                ->select(['cnt' => $subquery->func()->count('1')])
                ->where(['tag_id' => $tagId, 'TagsCourses.course_id = Courses.id']);
            $dataQuery->select(["tag_$tagId" => $subquery]);
        }

        $data = $dataQuery->toArray();

        $this->set(compact('project', 'tags', 'data'));
    }

    public function duplicate($id = null)
    {
        $this->request->allowMethod(['post']);

        $newEntity = $this->Projects->duplicateEntity($id);

        $newEntity->name = $this->request->getData('new_name');
        $newEntity->urlname = $this->request->getData('new_urlname');

        if ($this->Projects->save($newEntity)) {
            $this->Flash->success('Das Projekt wurde kopiert, Sie können jetzt weitere Einstellungen ändern.');
            return $this->redirect(['action' => 'edit', $newEntity->id]);
        } else {
            $this->Flash->error('Das Projekt konnte nicht kopiert werden. Bitte versuchen Sie es erneut.');
            return $this->redirect(['action' => 'index']);
        }
    }

    public function makeRater($projectId = null)
    {
        //this method will require GRANT_RIGHTS and RATE/$0 privileges, so we don't have to check this here
        $this->request->allowMethod(['post', 'put', 'patch']);

        $username = $this->request->getData('make-rater-user');
        $user = $this->Projects->Registrations->Users->findByUsername($username)->first();

        if (!$user) {
            $this->Flash->error("Der Benutzer <i>$username</i> konnte nicht gefunden werden.", ['escape' => false]);
            return $this->redirect($this->request->referer());
        }

        $right1 = $this->Projects->Registrations->Users->Rights->findByName('RATE')->firstOrFail();
        $right1->_joinData = new \Cake\ORM\Entity(['subresource' => $projectId, 'right_id' => $right1->id, 'user_id' => $user->id]); //we need to be explicit here, see https://github.com/cakephp/cakephp/issues/10665

        $right2 = $this->Projects->Registrations->Users->Rights->findByName('ADMIN')->firstOrFail();
        $right2->_joinData = new \Cake\ORM\Entity(['subresource' => $projectId, 'right_id' => $right2->id, 'user_id' => $user->id]);

        if (TableRegistry::get('Users')->getAssociation('rights')->link($user, [$right1, $right2])) {
            $this->Flash->success('Das Recht wurde dem Account zugewiesen. Hinweis: Dies wird erst beim nächsten Login des Benutzer wirksam.');
        } else {
            $this->Flash->error('Das Recht konnte dem Account nicht zugewiesen werden, bitte versuchen Sie es erneut.');
        }

        return $this->redirect($this->request->referer());
    }

    public function drawParticipants($projectId = null)
    {
        $this->backup($projectId); // check backup afterwards?

        $courses = $this->Projects->Courses->find()
            ->contain(['Registrations'])
            ->where(['Courses.project_id' => $projectId])
            ->all()
            ->sortBy(function($course) {
                if ($course->max_users == 0) return 1; // this should not happen - see check afterwards
                return ($course->registration_count / $course->max_users);
            }, SORT_ASC, SORT_NUMERIC)
            ->indexBy('id');

        $maxUsersSet = $courses->every(function ($course) {
            return $course->max_users != 0;
        });

        if (!$maxUsersSet) {
            $this->Flash->error('Bitte stellen Sie zuerst für alle Kurse die Teilnehmerzahl ein.');
            return $this->redirect($this->referer());
        }

        $project = $this->Projects->get($projectId);
        $this->Projects->patchEntity($project, ['reg_data_hidden' => true, 'courses' => []]); // set courses to empty array so the RulesChecker in ProjectsTable doesn't complain
        $project->setTagValue('hasLottery', 2);
        $this->Projects->save($project);


        $all = [];
        $drawn = [];
        foreach ($courses as $course) {
            $courseEntity = $this->Projects->Courses->get($course->id); // TODO: speed up drawing by querying and saving all courses at once?
            $registrations = collection($course->registrations);
            $all = $all + $registrations->indexBy('id')->toArray(); // fill up $all so we don't have to query all registrations at once again; index everything by RID so we can use array_key_exists afterwards
            $drawnTmp = [];
            if ($course->max_users > 0) {
                $drawnTmp = $registrations->filter(function ($registration) use ($drawn) { // remove already drawn users from collection to draw from
                    return !array_key_exists($registration->id, $drawn);
                })
                ->sample($course->max_users) // drawing takes place here
                ->indexBy('id')->toArray();
                $drawn = $drawn + $drawnTmp;
            }
            $notDrawnTmp = $registrations->filter(function ($registration) use ($drawnTmp) { // create list of not drawn users in course to unlink them
                return !array_key_exists($registration->id, $drawnTmp);
            })->toList();

            $this->Projects->Courses->Registrations->unlink($courseEntity, $notDrawnTmp);
            $courseEntity->waiting_list_length = 0;
            $this->Projects->Courses->save($courseEntity);
        }

        $notDrawn = array_filter($all, function ($registration) use ($drawn) {
            return !array_key_exists($registration['id'], $drawn);
        });

        // create new courses which contain drawn and not drawn users
        $courseData = [
            'project_id' => $projectId,
            'name' => 'nicht ausgelost',
            'description' => 'Dieser Kurs enthält alle nicht ausgelosten Teilnehmer.',
            'max_users' => count($notDrawn),
            'registration_count' => count($notDrawn),
            'tags' => []
        ];
        $notDrawnCourse = $this->Projects->Courses->newEntity($courseData);
        $courseData = ['name' => 'ausgelost', 'description' => 'Dieser Kurs enthält alle ausgelosten Teilnehmer.', 'registration_count' => count($drawn), 'max_users' => count($drawn)] + $courseData;
        $drawnCourse = $this->Projects->Courses->newEntity($courseData);
        $this->Projects->Courses->save($notDrawnCourse);
        $this->Projects->Courses->save($drawnCourse);
        $this->Projects->Courses->Registrations->link($notDrawnCourse, $notDrawn);
        $this->Projects->Courses->Registrations->link($drawnCourse, $drawn);

        $this->Flash->success('Teilnehmer wurden erfolgreich ausgelost.');

        return $this->redirect($this->request->referer());
    }

    public function backup($projectId = null)
    {
        // duplicate project - delete old backup first if one exists
        $project = $this->Projects->get($projectId);
        $oldBackup = $this->Projects->find()->where(['urlname' => $project->urlname.'_bak'])->first();
        if ($oldBackup != null) {
            $courses = $this->Projects->Courses->find()->where(['project_id' => $oldBackup->id])->all();
            foreach ($courses as $course) $this->Projects->Courses->delete($course);
            $this->Projects->delete($oldBackup);
        }
        $duplicateConfig = $this->Projects->behaviors()->get('Duplicatable')->config();
        $this->Projects->behaviors()->get('Duplicatable')->config([
            'contain' => ['Courses', 'CustomFields', 'Tags'], // copy courses automatically
            'append' => ['name' => ' - Backup', 'urlname' => '_bak']
        ]);
        $newProject = $this->Projects->duplicateEntity($projectId);
        $this->Projects->behaviors()->get('Duplicatable')->config($duplicateConfig); // set default config again
        $newProject->reg_data_hidden = true;
        $newProject->visible = false;
        $newProject->addTag('backupOf', $projectId);
        $this->Projects->save($newProject);
        $newProjectId = $newProject->id;

        $this->copyRegistrations($projectId, $newProjectId);
    }

    public function restore($backupId = null, $targetId = null)
    {
        // unlink all registrations from courses
        $courses = $this->Projects->Courses->find()->contain(['Registrations'])->where(['project_id' => $targetId])->all();
        foreach ($courses as $course) {
            $this->Projects->Courses->Registrations->unlink($course, $course->registrations);
        }
        $this->Projects->Registrations->deleteAll(['project_id' => $targetId]);

        // restore registrations - use existing courses
        $this->copyRegistrations($backupId, $targetId);

        $target = $this->Projects->get($targetId);
        if ($target->hasTag('hasLottery')) $target->setTagValue('hasLottery', 1);
        $this->Projects->save($target);
        return $this->redirect($this->referer());
    }

    protected function copyRegistrations($from = null, $to = null)
    {
        // create new registrations
        $newRegistrations = $this->Projects->Registrations->newEntities(
            $this->Projects->Registrations->find()
                ->select($this->Projects->Registrations)->select(['project_id' => $to]) // set project id to target id using select so we don't have to patch entities
                ->where(['Registrations.project_id' => $from])
                ->enableHydration(false)->toArray());
        $this->Projects->Registrations->saveMany($newRegistrations);

        // link registrations to courses
        $courses = $this->Projects->Courses->find()->contain(['Registrations'])->where(['project_id' => $from])->toArray();
        foreach ($courses as $course) {
            $targetCourse = $this->Projects->Courses->find()->where(['project_id' => $to, 'name' => $course['name']])->firstOrFail();
            $uids = array_map(function($e) {
                return $e->user_id;
            }, $course['registrations']);
            $inCourse = array_filter($newRegistrations, function($registration) use ($uids) {
                return in_array($registration->user_id, $uids);
            });
            $this->Projects->Courses->Registrations->link($targetCourse, $inCourse);
        }
    }

    function paginateArray($arr, $page, $limit)
    {
        return array_slice($arr, ($page-1)*$limit, $limit);
    }

    /**
     * @param string|null $projectId
     */
    public function removeConfirmationAppendix($projectId = null) {
        $project = $this->Projects->get($projectId);
        $fileName = $project->getTagValue('confirmationAppendix');
        $appendixFile = new File(ROOT . DS . Configure::read('App.uploads') . DS . 'confirmation_appendices' . DS . $fileName);
        $project->removeTag('confirmationAppendix');
        if ($appendixFile->delete() && $this->Projects->save($project)) {
            $this->Flash->success('Der Anhang wurde erfolgreich gelöscht.');
        } else {
            $this->Flash->error('Fehler beim Löschen des Anhangs. Bitte versuchen Sie es erneut.');
        }
        $this->redirect($this->referer());
    }
}
