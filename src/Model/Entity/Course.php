<?php
namespace App\Model\Entity;

use Cake\Collection\Collection;
use Cake\I18n\Time;
use Cake\Utility\Inflector;

/**
 * Course Entity
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string $description
 * @property string $sort
 * @property int $max_users
 * @property int $waiting_list_length
 * @property bool $uploads_allowed
 * @property bool $forced
 * @property \Cake\I18n\Time $uploads_start
 * @property \Cake\I18n\Time $uploads_end
 * @property \Cake\I18n\Time $register_end
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Scale[] $scales
 * @property \App\Model\Entity\UploadedFile[] $uploaded_files
 * @property \App\Model\Entity\Registration[] $registrations
 * @property \App\Model\Entity\Tag[] $tags
 */
class Course extends Entity
{
    use TagTrait;
    use TagAsFieldTrait;

    protected $_tagFields = [
        'infoAfterReg',
        'hideFreeSlots' => false
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

    /**
     * Returns the number of free slots in this course, including the
     * waiting list. Returns -1 if the number of participants or the
     * waiting list is unlimited.
     *
     * Call getNextFreeSlotInfo() for information about whenever the next
     * free slot is on the waiting list etc.
     *
     * @return int
     */
    protected function _getFreeSlots()
    {
        if(!isset($this->registration_count)) {
            throw new \LogicException('Registration count was not loaded!');
        }

        if ($this->max_users === 0 || $this->waiting_list_length === -1 || ($this->project != null && $this->project->hasTag('hideFreeSlots') && $this->project->getTagValue('hideFreeSlots'))) {
            return -1;
        }
        return $this->max_users + $this->waiting_list_length - $this->registration_count;
    }

    public function hasFreeSlot()
    {
        if(!isset($this->registration_count)) {
            throw new \LogicException('Registration count was not loaded!');
        }

        $slots = $this->_getFreeSlots();
        return $slots === -1 || $slots > 0;
    }

    /**
     * Returns information about the next free slot in this course.
     * - "waiting_list" when the slot is on the waiting list
     * - "normal" when the slot is in the regular continent
     * - "full" if there are no free slots
     * - "closed" when the registration is closed
     */
    public function getNextFreeSlotInfo()
    {
        if(!isset($this->registration_count)) {
            throw new \LogicException('Registration count was not loaded!');
        }

        if (!$this->isInRegisterTimeframe()) {
            return "closed";
        }

        if ($this->max_users === 0 || $this->max_users > $this->registration_count) {
            return 'normal';
        } else if ($this->waiting_list_length === -1 || $this->max_users + $this->waiting_list_length > $this->registration_count) {
            return "waiting_list";
        } else {
            return "full";
        }
    }

    /**
     * @param int $listPos
     * @return bool
     */
    public function isListPosOnWaitingList($listPos)
    {
        if ($this->max_users === 0) {
            return false;
        }
        return $listPos > $this->max_users;
    }

    protected function _getForced()
    {
        if (isset($this->_properties['forced']))
            return $this->_properties['forced'];

        return $this->hasTag('forced');
    }

    protected function _setForced($value)
    {
        if ($value)
            $this->addTag('forced');
        else
            $this->removeTag('forced');
        return $value;
    }

    protected function _getExgroups()
    {
        return $this->_readListFromTags('exgroup');
    }

    protected function _getFilters()
    {
        return $this->_readListFromTags('filter');
    }

    protected function _getRegisterEndActive()
    {
        if (isset($this->_properties['register_end_active'])) {
            return $this->_properties['register_end_active'];
        }
        return $this->register_end != null;
    }

    protected function _readListFromTags($name)
    {
        $propName = Inflector::pluralize($name);

        if (isset($this->_properties[$propName])) {
            return $this->_properties[$propName];
        }

        if ($this->tags != null) {
            $this->_properties[$propName] = implode(', ', $this->getTagNamesByPrefix($name . '_'));
            return $this->_properties[$propName];
        }

        return [];
    }

    public function isInUploadTimeframe()
    {
        return $this->uploads_start < Time::now() && $this->uploads_end > Time::now();
    }

    public function isInRegisterTimeframe()
    {
        return $this->register_end === null || $this->register_end > Time::now();
    }

    /**
     * Returns a string with HTML containing the description with some fixes applied.
     *
     * "Empty" descriptions (like <p><br></p>) are omitted, wrapping <p>'s are
     * added as needed.
     *
     * @return string
     */
    public function getDescriptionForDisplay()
    {
        $desc = strip_tags($this->description) === '' ? '' : $this->description; //strip "only junk" descriptions
        if (!$desc) {
            return "";
        }
        return "<p class=\"course-selector-fixup\">$desc";
        //no h(), this is HTML. The <p> wil be auto-closed if desc start with another <p>,
        //otherwise by the </td> and will then contain the desc. The css class is for fixing margins.
    }
}
