<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * CoursesRegistrations Model
 *
 * @property \App\Model\Table\CoursesTable|\Cake\ORM\Association\BelongsTo $Courses
 * @property \App\Model\Table\RegistrationsTable|\Cake\ORM\Association\BelongsTo $Registrations
 *
 * @method \App\Model\Entity\CoursesRegistration get($primaryKey, $options = [])
 * @method \App\Model\Entity\CoursesRegistration newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CoursesRegistration[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CoursesRegistration|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CoursesRegistration patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CoursesRegistration[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CoursesRegistration findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CoursesRegistrationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('courses_registrations');
        $this->setDisplayField('course_id');
        $this->setPrimaryKey(['course_id', 'registration_id']);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Courses', [
            'foreignKey' => 'course_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Registrations', [
            'foreignKey' => 'registration_id',
            'joinType' => 'INNER'
        ]);
    }

    public function beforeFind(Event $event, Query $query, \ArrayObject $options, $primary)
    {
        if (!Hash::get($options, 'noLoadListPos', false) && !Configure::read('noAutoLoad')) {
            /*
             * If the option "noLoadList" is not set to true, we modify the query
             * to include a field called "list_pos", showing the position on the
             * registration list.
             * Please note that the field is included in the *Course* entity, not
             * the _joinData/CoursesRegistration entity. This is due to the name/alias
             * of the selected field ("Courses__list_pos"). To put the field in the
             * _joinData entity the name must be "Courses_CJoin__list_pos".
             * This code is here and not in the CoursesTable class, because we can
             * only load the listPos, if Courses is joined with Registrations (and
             * not loaded alone). When such a join is present, this code will be
             * called, injecting the field.
             * This looks a bit strange, and I'm not completely sure if this is the
             * way it is intended to work, but it works quite well.
             */
            $table = TableRegistry::get('CoursesRegistrations');
            $subquery = $table->query();
            $subquery
                ->from(['cr' => $table->getTable()])
                ->select([
                    'cnt' => $subquery->newExpr()
                        ->add($subquery->func()->count('*'))
                        ->add(' + 1')
                        ->setConjunction('')
                ])
                ->where('CoursesRegistrations.course_id = cr.course_id') //the first one is from the outer query
                ->andWhere('CoursesRegistrations.created > cr.created')
                ->applyOptions(['noLoadListPos' => true]); //avoid endless loop
            $query
                ->select(['Courses__list_pos' => $subquery])
                ->enableAutoFields();
        }
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
        $rules->add($rules->existsIn(['course_id'], 'Courses'));
        $rules->add($rules->existsIn(['registration_id'], 'Registrations'));

        return $rules;
    }
}
