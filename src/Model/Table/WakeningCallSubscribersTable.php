<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WakeningCallSubscribers Model
 *
 * @property \App\Model\Table\WakeningCallsTable|\Cake\ORM\Association\BelongsTo $WakeningCalls
 *
 * @method \App\Model\Entity\WakeningCallSubscriber get($primaryKey, $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCallSubscriber findOrCreate($search, callable $callback = null, $options = [])
 */
class WakeningCallSubscribersTable extends Table
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

        $this->setTable('wakening_call_subscribers');
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');

        $this->belongsTo('WakeningCalls', [
            'foreignKey' => 'wakening_call_id',
            'joinType' => 'INNER'
        ]);
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
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

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
        $rules->add($rules->isUnique(['wakening_call_id', 'email']));
        $rules->add($rules->existsIn(['wakening_call_id'], 'WakeningCalls'));

        return $rules;
    }
}