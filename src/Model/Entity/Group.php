<?php
namespace App\Model\Entity;

/**
 * Group Entity
 *
 * @property int $id
 * @property string $name
 * @property int $project_id
 * @property string $password
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Rating[] $ratings
 * @property \App\Model\Entity\User[] $users
 */
class Group extends Entity
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
        'id' => false,
        'project_id' => false
    ];

    public function getNonTeacherMemberCount()
    {
        return count(collection($this->users)->reject(function ($u) {
            return $u->is_teacher;
        })->toArray());
    }
}
