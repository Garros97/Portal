<?php
namespace App\Model\Entity;

use Cake\Collection\Collection;

/**
 * CustomField Entity
 *
 * @property int $id
 * @property int $project_id
 * @property string $section
 * @property string $name
 * @property string $help_text
 * @property int $type
 * @property bool $backend_only
 * @property string combo_box_values
 * @property bool $is_required
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Registration[] $registrations
 */
class CustomField extends Entity
{
    use EnsureLoadedTrait;

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
     * Return all Combobox values as array.
     *
	 * @param string|null extraOption A single extra option to include (e.g. current value), null to disable.
	 * 
     * @return string[]
     */
	public function getComboBoxOptions($extraOption = null)
	{
		$a = explode(',', $this->combo_box_values);
		if ($extraOption !== null) {
			$a[] = $extraOption;
		}
		$a = array_unique(array_map('trim', $a));
        natcasesort($a); //operates in place
        return $a;
	}

	protected function _getIsRequired()
	{
	    return substr($this->name, -1) === "*"; //field is required iff. name ends with a star
	}

    public function validateValue()
    {
        //This function somehow mimics Cake's build in validation. We cannot use the build in variant because
        //we would need to generate a dynamic validator, which seems to be quite complicated.
		if (!$this->_joinData->isDirty('value')) {
			return true; //the current value is always valid (don't force the admin to fix invalid values that somehow slipped in)
		}
		
        $val = $this->_joinData->value;

        if (strlen($val) === 0 && $this->isRequired) {
            $this->_joinData->errors('value', 'Dieses Feld muss ausgefüllt werden.');
            return false;
        }

        switch($this->type) {
            case CustomFieldType::Text:
                return true; //A text field is always valid
            case CustomFieldType::Checkbox:
                if (!in_array($val, ['0', '1'])) {
                    $this->_joinData->errors('value', 'Interner Fehler: Der Wert der Checkbox ist ungültig.');
                    return false;
                }
                return true;
            case CustomFieldType::AgbCheckbox:
                if ($val !== '1') {
                    $this->_joinData->errors('value', 'Sie müssen diesen Haken setzen, um fortzufahren.');
                    return false;
                }
                return true;
            case CustomFieldType::Number:
                if (!preg_match('/^[0-9]*$/', $val)) {
                    $this->_joinData->errors('value', 'Bitte geben Sie nur Zahlen ein.');
                    return false;
                }
                return true;
            case CustomFieldType::Dropdown:
                if ($this->backend_only && $val === '') {
                    return true; //special case for default values, see below
                }

                if ($val === "---" && $this->isRequired) { //the value "---" is considered as invalid when the field is required
                    $this->_joinData->errors('value', 'Bitte wählen Sie einen gültigen Wert.');
                    return false;
                }
                if (!in_array($val, $this->getComboBoxOptions())) {
                    $this->_joinData->errors('value', 'Interner Fehler: Der ausgewählte Wert ist nicht gültig.');
                    return false;
                }
                return true;
            default:
                throw new \LogicException('Invalid custom field type');
        }
    }

    /**
     * Returns a suitable (valid) default value for this type of field.
     *
     * @remarks This is currently used to populate backend-only field
     * during registration.
     *
     * @return string
     */
    public function getDefaultValue()
    {
        switch($this->type) {
            case CustomFieldType::Text:
                return '';
            case CustomFieldType::Checkbox:
                return '0';
            case CustomFieldType::AgbCheckbox:
                return '1';
            case CustomFieldType::Number:
                return '';
            case CustomFieldType::Dropdown:
                /**
                 * We could return something like the first value from the allowed set, but this
                 * could cause trouble if someone uses this for "Yes/No" style fields and "Yes" is
                 * the first value in the set (but the default should be "No").
                 * We just allow empty values for backend-only fields to mitigate this.
                 * The UI will show values which a not in the set of possible options just fine, but
                 * you can't reassign such a value when you assigned a valid one (as expected)
                 * */
                return '';
            default:
                throw new \LogicException('Invalid custom field type');
        }
    }

    /**
     * Get a human readable description for a type of a custom field.
     * @param int $type The type. Use the values from CustomFieldType
     * @return string A human readable name.
     */
    public static function getTypeName($type)
    {
        switch($type) {
            case CustomFieldType::Text:
                return 'Text';
            case CustomFieldType::Checkbox:
                return 'Checkbox';
            case CustomFieldType::AgbCheckbox:
                return 'AGB Checkbox';
            case CustomFieldType::Number:
                return 'Zahl';
            case CustomFieldType::Dropdown:
                return 'Auswahlfeld';
            default:
                throw new \LogicException('Invalid custom field type');
        }
    }
}
