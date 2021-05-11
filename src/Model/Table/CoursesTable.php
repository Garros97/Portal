<?php
namespace App\Model\Table;

use App\Model\Entity\Course;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * Courses Model
 *
 * @property \App\Model\Table\ProjectsTable|\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\ScalesTable|\Cake\ORM\Association\HasMany $Scales
 * @property \App\Model\Table\UploadedFilesTable|\Cake\ORM\Association\HasMany $UploadedFiles
 * @property \App\Model\Table\RegistrationsTable|\Cake\ORM\Association\BelongsToMany $Registrations
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Course get($primaryKey, $options = [])
 * @method \App\Model\Entity\Course newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Course[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Course|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Course patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Course[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Course findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \App\Model\Behavior\DefaultableBehavior
 */
class CoursesTable extends Table
{
    use AutoContainTagsTrait {
        beforeFind as autoContainTagsBeforeFind;
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('courses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Scales', [
            'foreignKey' => 'course_id',
            'dependent' => true
        ]);
        $this->hasMany('UploadedFiles', [
            'foreignKey' => 'course_id',
            'dependent' => true,
            'cascadeCallbacks' => true //we need to delete the files in disk
        ]);
        $this->belongsToMany('Registrations', [
            'foreignKey' => 'course_id',
            'targetForeignKey' => 'registration_id',
            'joinTable' => 'courses_registrations'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'course_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'tags_courses'
        ]);

        $this->addBehavior('Defaultable', [
            'defaults' => [
                'description' => '',
            ]
        ]);
    }

    public function beforeSave(Event $event, Entity $entity, \ArrayObject $options)
    {
        if ($entity->uploads_start === null) {
            $entity->uploads_start = Time::now();
        }

        if ($entity->uploads_end === null) {
            $entity->uploads_end = Time::now();
        }

        if ($entity->exgroups !== null) {
            $this->_processTagList('exgroup', $entity);
        }

        if ($entity->filters !== null) {
            $this->_processTagList('filter', $entity);
        }
    }

    public function beforeFind(Event $event, Query $query, \ArrayObject $options, $primary)
    {
        $query->order(['Courses.sort', 'Courses.name']);
        $this->autoContainTagsBeforeFind($event, $query, $options, $primary);

        if (!Hash::get($options, 'noLoadRegistrationCount', false) && !Configure::read('noAutoLoad')) {
            /*
             * If the option "noLoadRegistrationCount" is not set to true, we modify all
             * queries to Courses to include a field called "registration_count" which
             * contains the total number of registrations in this course. This is archived
             * using a subquery.
             * Please note that loading "list_pos" in done in CoursesRegistrationsTable.
             */
            /*$query
                ->select(['registration_count' => $query->func()->count('Registrations.id')])
                ->autoFields(true)
                ->leftJoinWith('Registrations')
                ->group('Courses.id');*/
            //TODO: The code above uses no subquery, but I'm not sure if it's always safe to use
            $table = TableRegistry::get('CoursesRegistrations');
            $subquery = $table->query();
            $subquery
                ->from(['cr' => $table->getTable()])
                ->select(['count' => $subquery->func()->count('*')])
                ->where('Courses.id = cr.course_id')
                ->applyOptions(['noLoadListPos' => true]); //Don't trigger the code for list_pos for the subquery
            $query
                ->select(['Courses__registration_count' => $subquery])
                ->enableAutoFields();
        }
    }

    /**
     * Processes an entity and converts a list of entries to the correct tags.
     * @param Course $entity The course
     * @param string $name Singular name of the property
     */
    protected function _processTagList($name, $entity)
    {
        $propName = Inflector::pluralize($name);

        $list = array_unique(array_map('trim', explode(',', $entity->get($propName) ?: '')));

        $entity->removeTagsWithPrefix($name . '_');

        foreach ($list as $item) {
            if (strlen($item) > 0) {
                $entity->addTag($name . '_' . $item); //will be ignored, if this tag already exists
            }
        }
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->allowEmpty('description');

        $validator
            ->alphaNumeric('sort')
            ->allowEmpty('sort');

        $validator
            ->nonNegativeInteger('max_users')
            ->notEmpty('max_users');

        $validator
            ->greaterThanOrEqual('waiting_list_length',-1)
            ->notEmpty('waiting_list_length');

        $validator
            ->boolean('uploads_allowed')
            ->notEmpty('uploads_allowed');

        $validator
            ->dateTime('uploads_start', 'dmy')
            ->notEmpty('uploads_start');

        $validator
            ->dateTime('uploads_end', 'dmy')
            ->notEmpty('uploads_end');

        $validator
            ->dateTime('register_end', 'dmy')
            ->notEmpty('register_end');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['project_id'], 'Projects'));

        $rules->add(function($entity) {
            return $entity->uploads_start <= $entity->uploads_end;
        }, 'uploads-start-valid', [
            'errorField' => 'uploads_start',
            'message' => 'Der Anfang des Uploadszeitraum muss vor dem Ende liegen.'
        ]);

        $rules->add(function ($entity) {
            return !$entity->uploads_allowed || $entity->project->requiresGroupRegistration();
        }, 'uploads-only-for-group-projects', [
            'errorField' => 'uploads_allowed',
            'message' => 'Uploads werden nur für Projekte mit Gruppenanmeldung unterstützt'
        ]);

        //Don't allow exgroups or filters on forced courses. This would be way to complicated and
        //not very useful (e.g. a course conflicting with a forced course could be selected by no-one...)
        $rules->add(function ($entity) {
            return !$entity->hasTag('forced') || (empty($entity->exgroups) && empty($entity->filter));
        }, 'forced-courses-no-exgroup-and-filter', [
            'errorField' => 'forced',
            'message' => 'Ein verpflichtender Kurs kann keine Filterkriterien und/oder Ausschlussgruppen enthalten.'
        ]);

        return $rules;
    }
}
