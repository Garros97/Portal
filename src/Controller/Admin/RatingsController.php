<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Course;
use Cake\ORM\TableRegistry;

/**
 * Ratings Controller
 *
 * @property \App\Model\Table\RatingsTable $Ratings
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \App\Model\Table\GroupsTable $Groups
 * @property \App\Model\Table\UploadedFilesTable $UploadedFiles
 */
class RatingsController extends AppController
{
    public $rights = [
        'chooseProject' => ['RATE/any'],
        'index' => ['RATE/$0'],
        'edit' => ['RATE/?']
    ];

    public function initialize()
    {
        $this->loadModel('Projects');
        $this->loadModel('Groups');
        parent::initialize();
    }

    /**
     * Choose Project method
     *
     * @return \Cake\Http\Response|null
     */
    public function chooseProject()
    {
        $ids = $this->Ratings->Scales->find()
            ->join(['table' => 'courses', 'type'=>'left', 'conditions' => 'Scales.course_id = courses.id'])
            ->select(['courses.project_id'])->distinct()->extract('courses')->extract('project_id')->toArray();
        $projects = $this->Projects->find()->where(['Projects.id in' => $ids], ['Projects.id' => 'integer[]'])
            ->select(['Projects.name','Projects.id'])->distinct();

        $accessibleSubresources = $this->Auth->userGetAccessibleSubresourceIds('RATE');
        if ($accessibleSubresources !== true) { //no global access
            $projects->where(['Projects.id IN' => $accessibleSubresources]);
        }

        $this->set(compact('projects'));
    }

    public function getRequiredSubresourceIds($right, $request)
    {
        return $this->Groups->get($request->getParam('pass')[0])->project_id;
    }

    /**
     * Index method
     *
     * @param int|null $projectId
     * @return \Cake\Http\Response|null
     */
    public function index($projectId = null)
    {
        $project = $this->Projects->get($projectId, ['contain' => 'Courses']);
        $courses = $this->Projects->Courses->find();
        $courses
            ->where(['Courses.project_id' => $projectId])
            ->select(['Courses.id', 'Courses.name', 'scale_count' => $courses->func()->count('Scales.id')])
            ->leftJoinWith('Scales')
            ->group('Courses.id')
            ->applyOptions(['noLoadRegistrationCount' => true]);
        $ratings = $this->Ratings->find()->innerJoinWith('Scales.Courses', function ($q) use ($projectId) {
            return $q->where(['Courses.project_id' => $projectId]);
        });

        $groups = $this->Groups->find()->where(['project_id' => $projectId])->order('name')->toArray();

        $fileCountQuery = TableRegistry::get('projects')->find()
            ->where(['projects.id' => $projectId])
            ->innerJoin('courses',['projects.id = courses.project_id'])
            ->innerJoin('groups',['projects.id = groups.project_id'])
            ->leftJoin('groups_users', ['groups.id = groups_users.group_id'])
            ->leftJoin('users', ['groups_users.user_id = users.id'])
            ->leftJoin('uploaded_files', ['users.id = uploaded_files.user_id', 'courses.id = uploaded_files.course_id'])
            ->group(['groups.id', 'courses.id'])
            ->enableHydration(false);
        $fileCountQuery = $fileCountQuery->select(['gid' => 'groups.id', 'cid' => 'courses.id',
            'fcount' => $fileCountQuery->func()->count('uploaded_files.id')]);

        $tabRatingCount = [];
        $tabScaleCount = [];
        $uploadedFilesCount = [];

        foreach ($courses as $course) {
            $tabScaleCount[$course->id] = $course->scale_count;
            foreach ($groups as $group) {
                $tabRatingCount[$group->id][$course->id] = 0;
            }
        }
        foreach ($ratings as $rating) {
            $tabRatingCount[$rating->group_id][$rating->scale->course_id]++;
        }

        foreach($fileCountQuery->toArray() as $row) {
            $uploadedFilesCount[$row['gid']][$row['cid']] = $row['fcount'];
        }

        $this->set(compact('project', 'groups', 'courses', 'tabRatingCount', 'tabScaleCount', 'uploadedFilesCount'));
    }

    /**
     * Edit method
     *
     * @param string|null $groupId Group id.
     * @param string|null $courseId Course id.
     * @return \Cake\Http\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($groupId = null, $courseId = null)
    {
        $group = $this->Groups->get($groupId);
        /** @var Course $course */
        $course = $this->Projects->Courses->get($courseId, ['contain' => 'Scales']);

        $this->loadModel('UploadedFiles');
        $files = $this->UploadedFiles->find()
            ->where(['course_id' => $courseId])
            ->innerJoinWith('Users.Groups', function ($q) use ($groupId) {
                return $q->where(['Groups.id' => $groupId]);
            })
            ->order(['UploadedFiles.created', 'original_filename']);

        $scaleIds = collection($course->scales)->extract('id')->toArray();
        $ratings = $this->Ratings->find('list', [
                'keyField' => 'scale_id',
                'valueField' => function ($e) { return $e; } //whole entity as value
            ])
            ->contain(['Scales', 'Users' /* raters */])
            ->where(['scale_id IN' => $scaleIds, 'group_id' => $groupId])
            ->map(function ($e) {
                $e->rater = $this->Auth->user('id'); //set the rater to the current user
                $e->dirty('rater', false); //but don't mark this as dirty
                return $e;
            })
            ->toArray();

        foreach ($course->scales as $scale) {
            if (!isset($ratings[$scale->id])) {
                $rating = $this->Ratings->newEntity([
                    'scale_id' => $scale->id,
                    'group_id' => $groupId,
                ], ['validate' => false]);
                $rating->scale = $scale;
                $rating->rater = $this->Auth->user('id');
                $ratings[$scale->id] = $rating;
            }
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            foreach ($this->request->getData() as $scaleId => $data) {
                $rating = $ratings[$scaleId];
                $rating = $this->Ratings->patchEntity($rating, $data);

                if ($rating->value === null) {
                    $rating->setError('value', [], true); //remove the validation error on 'value' here, we skip this instance anyway
                    continue; //skip entries with no value, you don't have to rate all
                    //please note: This does not allow to "take back" ratings to unrated, for this we had to delete entities
                }

                if ($rating->isNew()) {
                    //$this->Ratings->loadInto($rating, ['Scales', 'Users']); //TODO: Why does this not work?
                    $rating->scale = $this->Ratings->Scales->get($rating->scale_id);
                    $rating->rater_user = $this->Ratings->Users->get($rating->rater);
                }
                if ($rating->isDirty()) {
                    $rating->setDirty('rater', true); //only modify the rater if there are any other modifications
                    $rating->rater_user = $this->Ratings->Users->get($rating->rater); //reload rater
                }
                if ($this->Ratings->save($rating)) {
                    $this->Flash->success("Die Bewertung für {$rating->scale->name} wurde gespeichert.");
                } else {
                    $this->Flash->error("Die Bewertung für {$rating->scale->name} konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.");
                }
            }
        }

        $this->set(compact('files', 'group', 'course', 'ratings'));
    }
}
