<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WakeningCalls Model
 *
 * @property \App\Model\Table\WakeningCallsTable|\Cake\ORM\Association\HasMany $WakeningCallSubscribers
 *
 * @method \App\Model\Entity\WakeningCall get($primaryKey, $options = [])
 * @method \App\Model\Entity\WakeningCall newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\WakeningCall[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCall|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WakeningCall patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCall[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\WakeningCall findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \App\Model\Behavior\DefaultableBehavior
 * @mixin \Duplicatable\Model\Behavior\DuplicatableBehavior
 */
class WakeningCallsTable extends Table
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

        $this->setTable('wakening_calls');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('WakeningCallSubscribers', [
            'foreignKey' => 'wakening_call_id',
            'dependent' => true,
        ]);

        $this->addBehavior('Defaultable', [
            'defaults' => [
                'state' => 1,
                'permanent' => false,
                'email_from' => '',
                'email_subject' => '',
                'message' => ''
            ]
        ]);

        $this->addBehavior('Duplicatable.Duplicatable', [
            'contain' => ['WakeningCallSubscribers'],
            'remove' => ['state'],
            'append' => ['name' => ' - Kopie'] //this is usually overwritten before saving
        ]);
    }

    /*
    public function beforeSave(Event $event, WakeningCall $entity, \ArrayObject $options)
    {
        if ($entity->isDirty('name')) {
            $entity->urlname = Text::slug(strtolower($entity->name));
        }
    }
    */

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
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->scalar('urlname')
            ->requirePresence('urlname')
            ->notEmpty('urlname');

        $validator
            ->email('email_from')
            ->allowEmpty('email_from')
            ->maxLength('email_from', 100);

        $validator->allowEmpty('email_subject')
            ->maxLength('email_subject', 100);

        $validator->allowEmpty('message');

        return $validator;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['urlname'],
            'Es existiert schon ein Weckruf mit dem selben oder einem ähnlichen Namen. Bitte wählen Sie einen anderen Namen, damit auch die URL-Namen unterscheidbar sind.'),
            ['errorField' => 'name']
        );

        return $rules;
    }
}