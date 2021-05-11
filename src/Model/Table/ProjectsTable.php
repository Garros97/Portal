<?php
namespace App\Model\Table;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Projects Model
 *
 * @property \App\Model\Table\CoursesTable|\Cake\ORM\Association\HasMany $Courses
 * @property \App\Model\Table\CustomFieldsTable|\Cake\ORM\Association\HasMany $CustomFields
 * @property \App\Model\Table\GroupsTable|\Cake\ORM\Association\HasMany $Groups
 * @property \App\Model\Table\RegistrationsTable|\Cake\ORM\Association\HasMany $Registrations
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Project get($primaryKey, $options = [])
 * @method \App\Model\Entity\Project newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Project[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Project|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Project patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Project[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Project findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \App\Model\Behavior\DefaultableBehavior
 * @mixin \Duplicatable\Model\Behavior\DuplicatableBehavior
 */
class ProjectsTable extends Table
{
    use AutoContainTagsTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('projects');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Courses', [
            'foreignKey' => 'project_id',
            'dependent' => true
        ]);
        $this->hasMany('CustomFields', [
            'foreignKey' => 'project_id',
            'dependent' => true
        ]);
        $this->hasMany('Groups', [
            'foreignKey' => 'project_id',
            'dependent' => true,
        ]);
        $this->hasMany('Registrations', [
            'foreignKey' => 'project_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'project_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'tags_projects',
            'through' => 'TagsProjects'
        ]);

        $this->addBehavior('Defaultable', [
            'defaults' => [
                'long_description' => '',
                'registration_note' => '',
                'multireg_infotext' => '',
            ]
        ]);

        $this->addBehavior('Duplicatable.Duplicatable', [
            'contain' => ['CustomFields', 'Tags'],
            'append' => ['name' => ' - Kopie', 'urlname' => '_copy'] //this is usually overwritten before saving
        ]);
    }

    /**
     * Custom finder. Find all projects that are currently active, i.e. open
     * for registration. (now >= register_start && now <= register_end)
     */
    public function findActive(Query $query, array $options)
    {
        return $query->where(['register_start <=' => new \DateTime('now'), 'register_end >=' => new \DateTime('now')]);
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
            ->requirePresence('urlname', 'create')
            ->notEmpty('urlname')
            ->add('urlname', 'valid', ['rule' => ['custom', '/^[A-Za-z0-9_-]*$/']]);

        $validator
            ->dateTime('register_start', 'dmy')
            ->notEmpty('register_start');

        $validator
            ->dateTime('register_end', 'dmy')
            ->notEmpty('register_end');

        $validator
            ->boolean('visible')
            ->notEmpty('visible');

        $validator
            ->boolean('reg_data_hidden')
            ->notEmpty('reg_data_hidden');

        $validator
            ->allowEmpty('logo_name');

        $validator
            ->notEmpty('short_description');

        $validator
            ->allowEmpty('long_description');

        $validator
            ->allowEmpty('registration_note');

        $validator
            ->add('confirmation_mail_template', 'valid', ['rule' => ['custom', '/^\w+$/']])
            ->notEmpty('confirmation_mail_template');

        $validator
            ->nonNegativeInteger('min_group_size')
            ->notEmpty('min_group_size');

        $validator
            ->nonNegativeInteger('max_group_size')
            ->notEmpty('max_group_size');

        $validator
            ->nonNegativeInteger('min_course_count')
            ->notEmpty('min_course_count');

        $validator
            ->nonNegativeInteger('max_course_count')
            ->notEmpty('max_course_count');

        $validator
            ->numeric('max_unregister_days')
            ->notEmpty('max_unregister_days');

        return $validator;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->addUpdate(function($entity) {
            return !$entity->dirty('urlname');
        }, 'no-urlname-modification', [
            'errorField' => 'urlname',
            'message' => 'Der URL-Name darf nicht geändert werden.'
        ]);

        $rules->add($rules->isUnique(['urlname'], 'Der URL-Name ist schon vergeben.'));

        $rules->add(function($entity) {
            return $entity->register_start < $entity->register_end;
        }, 'register-start-valid', [
            'errorField' => 'register_start',
            'message' => 'Der Anfang des Registrierungszeitraums muss vor dem Ende liegen.'
        ]);

        $rules->add(function($entity) {
            $logo = $entity->logo_name;
            return $logo == '' || (new File(Folder::addPathElement(WWW_ROOT, ['img', 'logos', $logo])))->exists();
        }, 'logo-filename', [
            'errorField' => 'logo_name',
            'message' => 'Das gewählte Logo konnte nicht gefunden werden.'
        ]);

        $rules->add(function($entity) {
            return $entity->min_group_size <= $entity->max_group_size;
        }, 'group-size-valid', [
            'errorField' => 'min_group_size',
            'message' => 'Die minimale Gruppengröße muss größer oder gleich der maximalen sein.'
        ]);

        $rules->addUpdate(function ($entity) {
            return ($entity->requiresGroupRegistration() || $entity->courses == null || count($entity->courses) === 0 ||
                collection($entity->courses)->every(function ($e) {
                    return !$e->uploads_allowed;
            }));
        }, 'uploads-only-for-group-projects', [
            'errorField' => 'min_group_size',
            'message' => 'Min. 1 Kurs erlaubt Uploads. Umschalten auf Einzelregistrierung nicht möglich.'
        ]);

        $rules->addUpdate(function ($entity) {
            return $entity->requiresGroupRegistration() || $entity->course == null || count($entity->courses) === 0 || collection($entity->courses)->every(function ($e) {
                return $e->scale_cnt === 0;
            });
        }, 'scales-only-for-group-projects', [
            'errorField' => 'min_group_size',
            'message' => 'Min. 1 Kurs enthält Bewertungsskalen. Umschalten auf Einzelregistrierung nicht möglich.'
        ]);

        $rules->addUpdate(function($entity) {
            return ($entity->min_course_count <= $entity->max_course_count || $entity->max_course_count == 0);
        }, 'course-count-valid', [
            'errorField' => 'min_course_count',
            'message' => 'Die minimale Kursanzahl muss größer oder gleich der maximalen sein.'
        ]);

        $rules->addUpdate(function ($entity) {
            return ($entity->courses == null || $entity->min_course_count <= count($entity->courses));
        }, 'min-course-count-lte-course-count',[
            'errorField' => 'min_course_count',
            'message' => 'Die minimale Kursanzahl darf nicht größer als die Kursanzahl des Projekts sein.'
        ]);

        return $rules;
    }


}
