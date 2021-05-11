<?php
namespace App\Model\Entity;

/**
 * Project Entity
 *
 * @property int $id
 * @property string $name
 * @property string $urlname
 * @property \Cake\I18n\Time $register_start
 * @property \Cake\I18n\Time $register_end
 * @property bool $visible
 * @property bool $reg_data_hidden
 * @property string $logo_name
 * @property string $short_description
 * @property string $long_description
 * @property string $registration_note
 * @property string $confirmation_mail_template
 * @property int $min_group_size
 * @property int $max_group_size
 * @property int $min_course_count
 * @property int $max_course_count
 * @property int $max_unregister_days
 *
 * @property \App\Model\Entity\Course[] $courses
 * @property \App\Model\Entity\CustomField[] $custom_fields
 * @property \App\Model\Entity\Group[] $groups
 * @property \App\Model\Entity\Registration[] $registrations
 * @property \App\Model\Entity\Tag[] $tags
 */
class Project extends Entity
{
    use TagTrait;
    use TagAsFieldTrait;

    protected $_tagFields = [
        'confirmAltContact',
        'confirmAltSenderAddress',
        'confirmAltReturnAddress',
        'confirmAltFax',
        'confirmAltClosing',
        'maxUnregisterDays' => 0,
        'hideFreeSlots' => false,
        'photoPermissionNeeded' => false
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
        'id' => false,
        'urlname' => false
    ];

    protected function _getShowRegisterOtherUserLink()
    {
        return $this->hasTag('showRegisterOtherUserLink');
    }

    protected function _setShowRegisterOtherUserLink($value)
    {
        if ($value)
            $this->addTag('showRegisterOtherUserLink');
        else
            $this->removeTag('showRegisterOtherUserLink');
        return $value;
    }

    protected function _getEnableMailOnPayment()
    {
        return $this->hasTag('notifyChange_Bezahlt');
    }

    protected function _setEnableMailOnPayment($value)
    {
        if ($value)
            $this->addTag('notifyChange_Bezahlt', 'Ja:confirm_payment,1:confirm_payment');
        else
            $this->removeTag('notifyChange_Bezahlt');
        return $value;
    }

    protected function _getEnableMailOnReceiveConfirmation()
    {
        return $this->hasTag('notifyChange_Bestätigung erhalten');
    }

    protected function _setEnableMailOnReceiveConfirmation($value)
    {
        if ($value)
            $this->addTag('notifyChange_Bestätigung erhalten', 'Ja:confirm_receive_confirmation,1:confirm_receive_confirmation');
        else
            $this->removeTag('notifyChange_Bestätigung erhalten');
        return $value;
    }

    protected function _getEnableMailOnReceiveDocuments()
    {
        return $this->hasTag('notifyChange_Unterlagen erhalten');
    }

    protected function _setEnableMailOnReceiveDocuments($value)
    {
        if ($value)
            $this->addTag('notifyChange_Unterlagen erhalten', 'Ja:confirm_receive_documents,1:confirm_receive_documents');
        else
            $this->removeTag('notifyChange_Unterlagen erhalten');
        return $value;
    }

    protected function _getHasLottery()
    {
        return $this->hasTag('hasLottery') && $this->getTagValue('hasLottery') > 0;
    }

    protected function _setHasLottery($value)
    {
        if ($value) {
            $this->addTag('hasLottery', 1);
            if (!$this->hasTag('hideFreeSlots')) $this->addTag('hideFreeSlots', 1);
        } else {
            // do not allow to revert hasLottery if participants have already been drawn
            if ($this->getTagValue('hasLottery') == 1) $this->removeTag('hasLottery');
        }
        return $value;
    }

    /**
     * Return whenever a group registration is required. If false
     * is returned a single-user registration is required.
     * @return bool
     */
    public function requiresGroupRegistration()
    {
        return $this->max_group_size > 0;
    }

    public function isActive()
    {
        return $this->register_start < new \DateTime('now') && $this->register_end > new \DateTime('now');
    }

    public function addConditionalCustomField($togglerId, $toggledId)
    {
        $pairs = $this->getConditionalCustomFields();
        $pairs[$togglerId][] = $toggledId;
        $this->setConditionalCustomFields($pairs);
    }

    public function removeConditionalCustomField($togglerId, $toggledId)
    {
        $pairs = $this->getConditionalCustomFields();
        $toggler = $pairs[$togglerId];
        unset($toggler[array_search($toggledId, $toggler)]);
        $pairs[$togglerId] = $toggler;
        $this->setConditionalCustomFields($pairs);
    }

    public function getConditionalCustomFields()
    {
        $pairs = [];
        if ($this->hasTag('conditional-cf')) {
            $tagValue = $this->getTagValue('conditional-cf');
            $customFields = collection($this->custom_fields)->indexBy('id')->toArray();
            foreach (explode(',', $tagValue) as $pair) {
                list($toggler, $toggledIds) = explode('-', $pair);
                if (strpos($toggler, ':') !== false) {
                    $togglerVals = explode('&', explode(':', $toggler)[1]);
                    $toggler = explode(':', $toggler)[0];
                }
                foreach (explode('+', $toggledIds) as $key => $toggledId) {
                    if ($customFields[$toggler]['type'] == 5) {
                        $pairs[$toggler]['checkbox'] = false;
                        $pairs[$toggler][$togglerVals[$key]] = $toggledId;
                    } else {
                        $pairs[$toggler][] = $toggledId;
                    }
                }
            }
        }
        return $pairs;
    }

    public function setConditionalCustomFields($data)
    {
        $fields = [];
        foreach ($data as $id => $rules) {
            $left = array_key_exists('checkbox', $rules)  ? join(':', [$id, join('&', array_keys(array_filter($rules)))]) : $id;
            $right = join('+', array_filter($rules));
            $fields[] =  join('-', [$left, $right]);
        }
        $this->setTagValue('conditional-cf', join(',', $fields));
    }
}
