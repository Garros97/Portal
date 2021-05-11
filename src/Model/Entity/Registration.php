<?php
namespace App\Model\Entity;

use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Registration Entity
 *
 * @property int $id
 * @property int $project_id
 * @property int $user_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $unregister_end_date
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Course[] $courses
 * @property \App\Model\Entity\CustomField[] $custom_fields
 * @property \App\Model\Entity\Tag[] $tags
 * @property \App\Model\Entity\Registration|bool $editAllowed
 */
class Registration extends Entity
{
    use TagTrait;
    use TagAsFieldTrait;

    protected $_tagFields = [
        'filter'
    ];

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

    public function userMayUnregister()
    {
        return Time::now() <= $this->unregister_end_date;
    }

    protected function _getUnregisterEndDate()
    {
        $end = null;
        if ($this->project->max_unregister_days > 0) {
            $end = $this->created->addDays($this->project->max_unregister_days);
        } else {
            $end = Date::maxValue();
        }

        return $this->project->register_end->min($end);
    }
}
