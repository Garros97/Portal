<?php
namespace App\Model\Table;

use App\Model\Entity\CustomField;
use App\Model\Entity\CustomFieldType;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CustomFields Model
 *
 * @property \App\Model\Table\ProjectsTable|\Cake\ORM\Association\BelongsTo $Projects
 * @property \App\Model\Table\RegistrationsTable|\Cake\ORM\Association\BelongsToMany $Registrations
 *
 * @method \App\Model\Entity\CustomField get($primaryKey, $options = [])
 * @method \App\Model\Entity\CustomField newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CustomField[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomField|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomField[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomField findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \App\Model\Behavior\DefaultableBehavior
 */
class CustomFieldsTable extends Table
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

        $this->setTable('custom_fields');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Projects', [
            'foreignKey' => 'project_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Registrations', [
            'foreignKey' => 'custom_field_id',
            'targetForeignKey' => 'registration_id',
            'joinTable' => 'custom_fields_registrations'
        ]);

        $this->addBehavior('Defaultable', [
            'defaults' => [
                'help_text' => '',
            ]
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
            ->notBlank('name')
            ->requirePresence('name', 'create');

        $validator
            ->inList('type', [
                CustomFieldType::Text,
                CustomFieldType::Checkbox,
                CustomFieldType::AgbCheckbox,
                CustomFieldType::Number,
                CustomFieldType::Dropdown
            ])
            ->requirePresence('type', 'create');

        $validator
            ->boolean('backend_only')
            ->notEmpty('backend_only');

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

        //TODO: These cause problems with the registration controller.
        //(This is old code, cbvalues used to be separate entities!)
		/*$rules->add(function ($entity) {
			//this works, the rules are checked after the beforeSave callback
			return empty($entity->combo_box_values) || $entity->type === CustomFieldType::Dropdown;
		}, 'no-cbvalue-for-non-dropdowns', [
			'errorField' => 'type',
			'message' => 'Der Typ des Zusatzfeldes muss "Auswahlfeld" sein, wenn das Feld "Werte für Auswahlfeld" benutzt wird'
		]);*/

		/*$rules->add(function ($entity) {
			//this works, the rules are checked after the beforeSave callback
			if (!$entity->dirty('type') && !$entity->dirty('combo_box_values'))
                return true; //if neither type nor CbValues have been changed return true. This avoids problems when the entity is only partially hydrated.
			return count($entity->combo_box_values) > 1 || $entity->type !== CustomFieldType::Dropdown;
		}, 'min-one-cbvalue-for-dropdowns', [
			'errorField' => 'type',
			'message' => 'Für Auswahlfelder muss das Feld "Werte für Auswahlfeld" gefüllt werden'
		]);*/

        return $rules;
    }
}
