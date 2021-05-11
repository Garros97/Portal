<?php
namespace App\Model\Table;

use App\Model\Entity\TagsUser;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TagsUsers Model
 *
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsTo $Tags
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\TagsUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\TagsUser newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TagsUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TagsUser|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TagsUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TagsUser[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TagsUser findOrCreate($search, callable $callback = null, $options = [])
 */
class TagsUsersTable extends Table
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

        $this->setTable('tags_users');
        $this->setDisplayField('tag_id');
        $this->setPrimaryKey(['tag_id', 'user_id']);

        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->allowEmpty('value');

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
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
