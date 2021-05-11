<?php
namespace App\Model\Table;

use App\Model\Entity\UploadedFile;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UploadedFiles Model
 *
 * @property \App\Model\Table\CoursesTable|\Cake\ORM\Association\BelongsTo $Courses
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UploadedFile get($primaryKey, $options = [])
 * @method \App\Model\Entity\UploadedFile newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\UploadedFile[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UploadedFile|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UploadedFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UploadedFile[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\UploadedFile findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UploadedFilesTable extends Table
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

        $this->setTable('uploaded_files');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Courses', [
            'foreignKey' => 'course_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
    }

    public function findOwnedByGroups(Query $query, array $options)
    {
        $groups = $options['groups'];
        if (!is_array($groups)) {
            $groups = [$groups];
        }
        return $query->innerJoinWith('Users.Groups', function ($q) use ($groups) {
            return $q->where(['Groups.id IN' => $groups + [-1]]); //the -1 fixes bugs when $groups is empty (yes, this is not so optimal...)
        })->distinct();
    }

    //no validation rules, this entity is never created from user input

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    public function afterDelete($event, $entity)
    {
        $uploadFolder = new Folder(ROOT . DS . Configure::read('App.uploads') . DS . 'user_uploads', true, 0777);
        unlink($uploadFolder->path . DS . $entity->disk_filename);
    }
}
