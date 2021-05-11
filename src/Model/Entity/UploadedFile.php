<?php
namespace App\Model\Entity;

use Cake\ORM\TableRegistry;

/**
 * UploadedFile Entity
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property string $disk_filename
 * @property string $original_filename
 * @property string $mime_type
 * @property \Cake\I18n\Time $created
 * @property bool $is_deleted
 *
 * @property \App\Model\Entity\Course $course
 * @property \App\Model\Entity\User $user
 */
class UploadedFile extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    public function mayUserViewFile($userId)
    {
        if ($this->mayUserDeleteFile($userId)) {
            return true; //when you can delete it, you can view it
        }
        $hasRateRight = (bool)TableRegistry::get('Users')->find()
            ->where(['Users.id' => $userId])
            ->innerJoinWith('Rights', function ($q) {
                return $q->where(['Rights.name' => 'RATE']);
            });
        if ($hasRateRight) { //raters can view "all" files (but cannot delete them!)
            return true;
        }
        return false;
    }

    public function mayUserDeleteFile($userId)
    {
        if ($this->user_id === $userId) {
            return true; //you can always view your own files
        }
        $isFileFromGroupMate = (bool)TableRegistry::get('Groups')->find()
            ->innerJoinWith('Users', function ($q) use ($userId) {
                return $q
                    ->where(['Users.id' => $userId]) //the user in question is in the group
                    ->where(['Users.id' => $this->user_id]); //and the file owner is in the group
            })
            ->innerJoinWith('Projects.Courses', function ($q) {
                return $q->where(['Courses.id' => $this->course_id]); //and the groups is from a project containing this course
            })
            ->count();
        if ($isFileFromGroupMate) {
            return true; //you can view files from your group members, when you are in the same group for this project!
        }
        return false;
    }
}
