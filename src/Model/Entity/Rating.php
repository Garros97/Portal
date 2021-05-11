<?php
namespace App\Model\Entity;

/**
 * Rating Entity
 *
 * @property int $id
 * @property int $scale_id
 * @property int $rater
 * @property int $group_id
 * @property float $value
 * @property string $comment
 * @property \Cake\I18n\Time $created
 *
 * @property \App\Model\Entity\Scale $scale
 * @property \App\Model\Entity\Group $group
 * @property \App\Model\Entity\User $rater_user
 */
class Rating extends Entity
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
}
