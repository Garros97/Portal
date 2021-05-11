<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Exports Controller
 */
class ExportsController extends AppController
{
    public $rights = [
        'participants' => ['MANAGE_PROJECTS/?'],
        'groups' => ['MANAGE_PROJECTS/?'],
        'ratings' => ['MANAGE_PROJECTS/?']
    ];

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler', [
            'viewClassMap' => [
                'xls' => 'Excel95Table',
                'xlsx' => 'Excel2007Table',
                'csv' => 'CsvTable',
                'json' => 'Json' //TODO: Needs more thinking!
            ]
        ]);
    }

    public function getRequiredSubresourceIds($right, $request)
    {
        return TableRegistry::get('Projects')->findByUrlname($request->getParam('pass')[0])->id;
    }

    public function beforeFilter(Event $event)
    {
        Configure::write('noAutoLoad', true); //disable all automatic query modification for the whole request

        if (in_array($this->request->getParam('_ext'), ['', 'html'])) {
            $this->viewBuilder()
                ->setTemplate('html_table')
                ->enableAutoLayout(false);
        }

        $this->getEventManager()->on('Controller.startup', function () { // workaround for "extension not configured" error
            if ($this->RequestHandler->ext === 'html') {
                $this->RequestHandler->ext = null;
            }
        });
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        //$this->set('_serialize', 'data');
    }


    public function participants()
    {
        $urlName = $this->request->getQuery('urlname', '');
        $courseId = $this->request->getQuery('cid', null); // export only participants of the appropriate course if course id is passed
        $courseData = null;

        $project = TableRegistry::get('Projects')->find()->where(['urlname' => $urlName])->firstOrFail();

        // use map/reduce algorithm to sort custom field data by registration
        $mapper = function ($entry, $key, $mapReduce) {
            $cfr = $entry['_matchingData']['CustomFieldsRegistrations'];
            $data = ['rid' => $cfr['registration_id'], 'name' => $entry['name'], 'value' => $cfr['value']];
            $mapReduce->emitIntermediate($data, $entry['id']);
        };
        $reducer = function($registrations, $customFieldId, $mapReduce) {
            $data = ['name' => $registrations[0]['name']];
            foreach ($registrations as $registration) {
                $data[$registration['rid']] = $registration['value'];
            }
            $mapReduce->emit($data, $customFieldId);
        };
        $customFieldData = TableRegistry::get('CustomFields')->find('all')
            ->select(['CustomFields.id', 'CustomFields.name','CustomFieldsRegistrations.registration_id', 'CustomFieldsRegistrations.value'])
            ->enableHydration(false)
            ->mapReduce($mapper, $reducer);

        $data = TableRegistry::get('Users')->find();
        if ($courseId == null) {
            $data = $data->innerJoinWith('Registrations.Projects')->where(['Projects.urlname' => $urlName]);
            $customFieldData = $customFieldData->innerJoinWith('Registrations.Projects')->where(['Projects.urlname' => $urlName])->toArray();
            $courseData = TableRegistry::get('Courses')->find()
                ->leftJoinWith('Registrations')->leftJoinWith('Projects')
                ->group(['Courses.id'])
                ->select(['cid' => 'Courses.id', 'name', 'rids' => 'group_concat(Registrations.id)']) // also export all groups the user is member of
                ->where(['Projects.urlname' => $urlName])
                ->enableHydration(false)->applyOptions(['noAutoContainTags' => true])
                ->indexBy('cid')->toArray();
        } else {
            $data = $data->innerJoinWith('Registrations.Courses')->where(['course_id' => $courseId]);
            $customFieldData = $customFieldData->leftJoinWith('Registrations.Courses')->where(['course_id' => $courseId])->toArray();
        }

        $data = $data->select(['UID' => 'Users.id','RID' => 'Registrations.id', 'Accountname' => 'Users.username',
            'Anrede' => $data->newExpr()->addCase([
                $data->newExpr()->add(['Users.sex' => 'm']),
                $data->newExpr()->add(['Users.sex' => 'f'])
            ], ['Herr', 'Frau', 'k.A.']),
            'Vorname' => 'Users.first_name', 'Nachname' => 'Users.last_name','E-Mail' => 'Users.email',
            'Adresse' => 'concat(Users.street, " ",Users.house_number)',
            'PLZ' => 'Users.postal_code', 'Stadt' => 'Users.city'
        ]);
        if ($project->requiresGroupRegistration()) {
            $data = $data->innerJoinWith('Groups')->select(['Gruppe' => 'Groups.name'])
                ->where(['Groups.project_id' => $project->id])->order(['Groups.id']);
        }
        // use formatResults for the output so we don't have to use a too complicated query
        $data = $data->formatResults(function (CollectionInterface $results) use ($customFieldData, $courseData) {
            return $results->map(function ($row) use ($customFieldData, $courseData) {
                $rid = $row['RID'];
                foreach ($customFieldData as $entry) {
                    $row[$entry['name']] = array_key_exists($rid, $entry) ? $entry[$rid] : '';
                }
                if ($courseData !== null) {
                    $courseCount = 0;
                    $row['#Kurse'] = 0;
                    foreach ($courseData as $course) {
                        $val = '';
                        if (in_array($rid, explode(',', $course['rids']))) {
                            $courseCount++;
                            $val = 'x'; // use a cross to mark courses per registration; otherwise the fields will be empty
                        }
                        $row[$course['name']] = $val;
                    }
                    $row['#Kurse'] = $courseCount;
                }
                return $row;
            });
        })
        ->enableHydration(false)
        ->applyOptions(['noAutoContainTags' => true]);

        $this->set('query', $data);
        $this->set('title', 'Teilnehmer');
        $this->set('export-type', 'participants');
    }

    public function groups($projectUrlName)
    {
        $groupsQuery = TableRegistry::get('Groups')->find();
        $data = $groupsQuery
            ->innerJoinWith('Projects', function ($q) use ($projectUrlName) {
                return $q->where(['Projects.urlname' => $projectUrlName])
                    ->applyOptions(['noAutoContainTags' => true]);
            })
            ->innerJoinWith('Users', function ($q) {
                return $q->applyOptions(['noAutoContainTags' => true]);
            })
            ->group('Groups.id')
            ->select([
                'ID' => 'Groups.id',
                'Name' => 'Groups.name',
                'Mitglieder' => $groupsQuery->func()->count('Users.id'),
                'E-Mails' => $groupsQuery->func()->group_concat(['Users.email SEPARATOR \';\'' => 'literal']) //TODO: This is MySQL specific, see PR#9536 on GitHub
            ])
            ->enableHydration(false)
            ->applyOptions(['noAutoContainTags' => true]);
        $this->set('query', $data);
        $this->set('title', 'Gruppen');
        $this->set('export-type', 'groups');
    }

    public function ratings ($requestedUrl) {
        $courseId = $this->request->getQuery('cid', null);

        $ids = TableRegistry::get('scales')->find()->where(['course_id' => $courseId])->select(['id'])->extract('id')->toArray();
        $mapper = function ($row, $key, $mapReduce) {
            $group_name = $row['Gruppenname'];
            $mapReduce->emitIntermediate($row, $group_name); // sort ratings by group name which will later be the table row 'key'
        };

        $reducer = function ($row, $group_name, $mapReduce) {
            $mapReduce->emit($row, $group_name);
        };
        $data = TableRegistry::get('ratings')->find()
            ->leftJoin('groups', ['ratings.group_id = groups.id'])
            ->leftJoin('scales',['ratings.scale_id = scales.id'])
            ->where(['ratings.scale_id in' => $ids], ['ratings.scale_id' => 'integer[]'])
            ->select(['Gruppenname' => 'groups.name', 'ratings.scale_id', 'ratings.value', 'Teilaufgabe' => 'scales.name'])
            ->order(['groups.name','ratings.scale_id'])
            ->mapReduce($mapper, $reducer)
            ->enableHydration(false)->applyOptions(['noAutoContainTags' => true]);

        $this->set('query',$data);
        $this->set('title','Bewertungen');
        $this->set('export-type','ratings');
    }
}
