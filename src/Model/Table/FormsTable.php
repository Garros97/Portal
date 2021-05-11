<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Forms Model
 *
 * @method \App\Model\Entity\Form get($primaryKey, $options = [])
 * @method \App\Model\Entity\Form newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Form[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Form|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Form patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Form[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Form findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \App\Model\Behavior\DefaultableBehavior
 */
class FormsTable extends Table
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

        $this->setTable('forms');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->hasMany('FormEntries', [
            'foreignKey' => 'form_id',
            'dependent' => true
        ]);

        $this->addBehavior('Defaultable', [
            'defaults' => [
                'state' => 1,
                'description' => '',
            ]
        ]);

        //TODO: duplicatable behavior
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
            ->scalar('title')
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->requirePresence('urlname', 'create')
            ->notEmpty('urlname')
            ->add('urlname', 'valid', ['rule' => ['custom', '/^[A-Za-z0-9_-]*$/']]);

        /*
        $validator
            ->integer('state')
            ->requirePresence('state', 'create')
            ->notEmpty('state');

        */

        $validator
            ->scalar('description')
            ->allowEmpty('description', 'create');

        /*
        $validator
            ->scalar('header_image')
            ->requirePresence('header_image', 'create')
            ->notEmpty('header_image');
        */

        return $validator;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->addUpdate(function ($entity) {
            return !$entity->dirty('urlname');
        }, 'no-urlname-modification', [
            'errorField' => 'urlname',
            'message' => 'Der URL-Name darf nicht geändert werden.'
        ]);

        $rules->add($rules->isUnique(['urlname'], 'Der URL-Name ist schon vergeben.'));
    }
}
