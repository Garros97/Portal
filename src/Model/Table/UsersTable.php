<?php
namespace App\Model\Table;

use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\Log\LogTrait;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Psr\Log\LogLevel;

/**
 * Users Model
 *
 * @property \App\Model\Table\RegistrationsTable|\Cake\ORM\Association\HasMany $Registrations
 * @property \App\Model\Table\UploadedFilesTable|\Cake\ORM\Association\HasMany $UploadedFiles
 * @property \App\Model\Table\GroupsTable|\Cake\ORM\Association\BelongsToMany $Groups
 * @property \App\Model\Table\RightsTable|\Cake\ORM\Association\BelongsToMany $Rights
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always',
                ],
                'Model.User.login' => [
                    'last_login' => 'always'
                ]
            ]
        ]);

        $this->hasMany('Registrations', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('UploadedFiles', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('Groups', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'group_id',
            'joinTable' => 'groups_users'
        ]);
        $this->belongsToMany('Rights', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'right_id',
            'joinTable' => 'rights_users'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'tags_users',
            'through' => 'TagsUsers'
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
            ->requirePresence('username', 'create')
            ->notEmpty('username')
            ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table'])
            ->ascii('username', 'Der Benutzername enthält ungültige Zeichen.')
            ->add('username', 'no-whitespace', [
                'rule' => function ($value, $context) {
                    return (strpos($value, ' ') === false);
                },
                'message' => 'Der Benutzername darf keine Leerzeichen enthalten.'
            ])
            ->lengthBetween('username', [4, 20], 'Der Benutzername muss zwischen 4 und 20 Zeichen lang sein.');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->allowEmpty('password');

        $validator
            ->requirePresence('first_name', 'create')
            ->notEmpty('first_name');

        $validator
            ->requirePresence('last_name', 'create')
            ->notEmpty('last_name');

        $validator
            ->inList('sex', ['m', 'f', 'x']);

        // allow admins to save empty fields: useful for changing username when the user has not yet entered all details
        $calledByAdmin = function($context) {
            if (array_key_exists('passed', $context['providers'])
                && array_key_exists('admin', $context['providers']['passed'])
                && $context['providers']['passed']['admin']) {
                return true;
            }
            return false;
        };

        $validator
            ->allowEmpty('street', $calledByAdmin);

        $validator
            ->allowEmpty('house_number', $calledByAdmin);

        $validator
            ->allowEmpty('postal_code', $calledByAdmin);

        $validator
            ->allowEmpty('city', $calledByAdmin);

        $validator
            ->date('birthday', 'dmy')
            ->allowEmpty('birthday', $calledByAdmin);

        return $validator;
    }

    /**
     * Validation rules during signup.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationSignup(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('username')
            ->notEmpty('username')
            ->ascii('username', 'Der Benutzername enthält ungültige Zeichen.')
            ->add('username', 'no-whitespace', [
                'rule' => function ($value, $context) {
                    return (strpos($value, ' ') === false);
                },
                'message' => 'Der Benutzername darf keine Leerzeichen enthalten.'
            ])
            ->lengthBetween('username', [4, 20], 'Der Benutzername muss zwischen 4 und 20 Zeichen lang sein.');

        $validator
            ->email('email')
            ->requirePresence('email')
            ->notEmpty('email')
            ->add('email2', 'confirm-email', [
                'rule' => ['compareWith', 'email'],
                'message' => 'E-Mail-Adressen stimmen nicht überein.',
            ]);

        $validator
            ->requirePresence('password')
            ->minLength('password', 1)
            ->add('password2', 'confirm-password', [
                'rule' => ['compareWith', 'password'],
                'message' => 'Passwörter stimmen nicht überein.',
            ]);

        $validator
            ->requirePresence('first_name')
            ->notEmpty('first_name');

        $validator
            ->requirePresence('last_name')
            ->notEmpty('last_name');

        $validator
            ->requirePresence('sex')
            ->inList('sex', ['m', 'f', 'x']);

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
        $rules->add($rules->isUnique(['username'], 'Dieser Accountname ist schon vergeben.'));
        $rules->add($rules->isUnique(['email'], 'Es existiert bereits ein Account mit dieser E-Mail-Adresse. Bei Fragen schreiben Sie uns bitte eine Mail.'));

        return $rules;
    }

    public function findAuth(Query $query, array $options)
    {
        return $query
            ->select(['id', 'username', 'password'])
            ->contain(['Rights'])
            ->formatResults(function (ResultSetInterface $results) {
                return $results->map(function ($row) {
                    $row->rights = Hash::map($row->rights, '{n}', function($right) { //flatten rights to array
                        return $right->getNameWithSubresource();
                    });
                    $row->tags = Hash::combine($row->tags, '{n}.name', '{n}._joinData.value');
                    return $row;
                });
            });
    }
}
