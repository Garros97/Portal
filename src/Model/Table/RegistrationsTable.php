<?php
namespace App\Model\Table;

use App\Model\Entity\Course;
use App\Model\Entity\Registration;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\EntityInterface;

/**
 * Registrations Model
 *
 * @property ProjectsTable|BelongsTo $Projects
 * @property UsersTable|BelongsTo $Users
 * @property CoursesTable|BelongsToMany $Courses
 * @property CustomFieldsTable|BelongsToMany $CustomFields
 * @property TagsTable|BelongsToMany $Tags
 *
 * @method Registration get($primaryKey, $options = [])
 * @method Registration newEntity($data = null, array $options = [])
 * @method Registration[] newEntities(array $data, array $options = [])
 * @method Registration|bool save(EntityInterface $entity, $options = [])
 * @method Registration patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Registration[] patchEntities($entities, array $data, array $options = [])
 * @method Registration findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class RegistrationsTable extends Table
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

        $this->setTable('registrations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Courses', [
            'foreignKey' => 'registration_id',
            'targetForeignKey' => 'course_id',
            'joinTable' => 'courses_registrations'
        ]);
        $this->belongsToMany('CustomFields', [
            'foreignKey' => 'registration_id',
            'targetForeignKey' => 'custom_field_id',
            'joinTable' => 'custom_fields_registrations'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'registration_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'tags_registrations',
            'through' => 'TagsRegistrations'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $isNewGroup = function($context) {
            if (!isset($context['data']['group']))
                return false;
            return $context['data']['group'] == -1;
        };

        $isExistingGroup = function($context) {
            if (!isset($context['data']['group']))
                return false;
            return $context['data']['group'] != -1;
        };

        //Note: This list should be synced with GroupsTable!
        $validator
            ->requirePresence('newgroup_name', $isNewGroup)
            ->notEmpty('newgroup_name', null, $isNewGroup);

        $validator
            ->requirePresence('newgroup_password', $isNewGroup)
            ->notEmpty('newgroup_password', null, $isNewGroup);

        $validator
            ->requirePresence('group_password', $isExistingGroup)
            ->notEmpty('group_password', null, $isExistingGroup);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['project_id'], 'Projects'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        //Check exgroups
        $rules->add(function ($entity) {
            if ($entity->courses == null) {
                return true; //no courses selected or they are somehow missing from the entity. This can't do any harm ;)
            }
            $exgroupsTaken = [];
            foreach ($entity->courses as $course) {
                foreach ($course->getTagNamesByPrefix('exgroup_') as $exgroup) {
                    if (in_array($exgroup, $exgroupsTaken)) {
                        $n = h($course->name);
                        return "Der Kurs <i>$n</i> überschneidet sich mit einem anderen Kurs. Bitte kontrollieren Sie Ihre Kurswahl";
                    }
                    $exgroupsTaken[] = $exgroup;
                }
            }
            return true;
        }, 'exgroups-ok', [
            'errorField' => '_message' //this is a dummy field used for communication with the controller. Messages without fields are dropped :(
        ]);

        //Check filters
        $rules->add(function ($entity) {
            if ($entity->courses == null) {
                return true;
            }
            $filterPossible = null;
            $filterUsed = false;
            foreach ($entity->courses as $course) {
                $filter = $course->getTagNamesByPrefix('filter_');
                if (count($filter) == 0) {
                    continue; //courses without filter are valid for every set of filters by definition
                }
                if (!$filterUsed) { //first selected course with filter
                    $filterPossible = $filter; //init with filters from first course
                }
                $filterUsed = true;
                $filterPossible = array_intersect($filterPossible, $filter);
            }
            if ($filterUsed && count($filterPossible) == 0) {
                return "Ihre gewählten Kurse gehören nicht alle zum gleichen Filterkriterium. Bitte kontrollieren Sie Ihre Kurswahl!"; //Should never happen if frontend is not broken
            }
            return true;
        }, 'filters-ok', [
            'errorField' => '_message'
        ]);

        //Check free slots
        $rules->add(function ($entity) {
            /** @var Registration $entity */
            if ($entity->courses == null) {
                return true;
            }
            $originalIds = $entity->isNew() ? [] : collection($entity->getOriginal('courses'))->extract('id')->toArray(); //getOriginal will just return the current value for new entities
            foreach($entity->courses as $course) {
                if (in_array($course->id, $originalIds)) {
                    continue; //this course was already taken before (can happen if user changes courses)
                }
                if (!$course->hasFreeSlot()) {
                    $n = h($course->name);
                    return "Der Kurs <i>$n</i> verfügt über keine freien Plätze mehr!";
                }
            }
            return true;
        },
            'enough-free-slots', [
                'errorField' => '_message'
            ]);

        //Check that the courses belong to the project
        $rules->add(function ($entity) {
            if ($entity->courses == null) {
                return true;
            }
            foreach($entity->courses as $course) {
                if ($course->project_id != $entity->project_id)
                    return false;
            }
            return true;
        },
            'project-matches', [
                'message' => 'Interner Fehler: Dieser Kurs gehört nicht zu diesem Projekt.',
                'errorField' => '_message'
            ]);

        //Check the custom fields
        $rules->add(function ($entity) {
            if ($entity->custom_fields == null) {
                return true;
            }
            $result = true;
            if ($entity->custom_fields) {
                foreach ($entity->custom_fields as $customField) {
                    $result = $customField->validateValue() && $result; //beware of short circuit execution!
                }
            }
            return $result;
        }, 'custom-fields-valid', [
            'message' => 'Die Angaben in den Zusatzfeldern sind nicht gültig.',
            'errorField' => '_message'
        ]);

        //Check forced courses
        $rules->add(function ($entity) {
            if ($entity->project == null || $entity->project->courses == null) {
                return true;
            }
            return collection($entity->project->courses)->every(function ($elm) use ($entity) {
                return !$elm->hasTag('forced') || in_array($elm, $entity->courses);
            });
        }, 'forced-courses-selected', [
            'message' => 'Sie haben erforderliche Kurse nicht gewählt!',
            'errorField' => '_message'
        ]);

        //Check timeframe
        $rules->add(function ($entity) {
            return $entity->project == null || $entity->project->isActive() || $entity->editAllowed;
        }, 'registration-timeframe', [
            'message' => 'Für dieses Projekt ist eine Anmeldung nicht mehr/noch nicht möglich.',
            'errorField' => '_message'
        ]);

        //Check timeframe per course
        //Note: This rule currently allows users to deselect courses with closed registration so that they can not
        //re-register for them. I think this is good, because a registration may close a few days before the start
        //and this way people from the waiting list can still move up. For registrations in general it is implemented
        //the other way round: You cannot deregister from a project once the registration is closed. This makes
        //sense because we don't want people to deregister in retrospective, which would manipulate our statistics.
        $rules->add(function ($entity) {
            if ($entity->courses == null) {
                return true;
            }
            $originalIds = $entity->isNew() ? [] : collection($entity->getOriginal('courses'))->extract('id')->toArray(); //getOriginal will just return the current value for new entities
            foreach($entity->courses as $course) {
                /** @var $course Course */
                if (in_array($course->id, $originalIds)) {
                    continue; //this course was already taken before (can happen if user changes courses), so don't check date
                }
                if (!$course->isInRegisterTimeframe()) {
                    $n = h($course->name);
                    return "Für den Kurs <i>$n</i> ist eine Anmeldung nicht mehr möglich!";
                }
            }
            return true;
        }, 'per-course-timeframe', [
            'errorField' => '_message'
        ]);

        $rules->add(function ($entity) {
            if ($entity->courses === null) {
                return true;
            }
            $courseCount = count($entity->courses);
            return ($courseCount >= $entity->project->min_course_count && ($courseCount <= $entity->project->max_course_count || $entity->project->max_course_count == 0));
        }, 'course-count', [
            'message' => 'Sie haben entweder zu wenige oder zu viele Kurse ausgewählt.',
            'errorField' => '_message'
        ]);

        //$rules->addCreate(function() {return false; }, 'auto-fail', ['errorField' => 'autoFail']); //This is for debugging.

        return $rules;
    }

    public function afterSaveCommit(Event $event, Registration $entity, ArrayObject $options) // TODO: also send movedUp e-mail when someone unregisters from a project?
    {
        if ($entity->courses == null) {
            return;
        }

        //A user changed it's module selection for a project. This could mean that someone moved up
        //from the waiting list! Compute the courses the user *de*selected:
        $newIds = collection($entity->courses)->extract('id')->toArray();
        $oldIds = collection($entity->getOriginal('courses'))->extract('id')->toArray();
        $removedCourses = array_values(array_diff($oldIds, $newIds));

        //For each course: Find the user that is *now* the last one that is not on the waiting list
        //(this is the user that moved up because the user deregistering is already removed)
        if (!$entity->project->hasTag('hideFreeSlots') || !$entity->project->getTagValue('hideFreeSlots')) {
            foreach ($removedCourses as $cid) {
                $user = $this->Users->find()
                    ->matching('Registrations.Courses', function ($q) use ($cid) {
                        return $q->where(['Courses.id' => $cid]);
                    })
                    ->having('Courses__list_pos = Courses__max_users') //use a having so that we can use the alias "Courses__list_pos"
                    ->first();
                if ($user) {
                    $this->Users->dispatchEvent('Model.User.movedUp', ['cid' => $cid, 'user' => $user], $user);
                }
            }
        }
    }
}
