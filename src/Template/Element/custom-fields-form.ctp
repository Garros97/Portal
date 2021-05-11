<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\Entity $entity
 * @var \App\Model\Entity\CustomField[] $customFields
 */
use App\Model\Entity\CustomFieldType;

$lastSection = null;
$i = 0;
$showRequiredNote = false;

foreach($customFields as $field) {
    if ($lastSection !== $field->section) {
        $lastSection = $field->section;
        if (trim($field->section) !== '') {
            echo "<h4".($i > 0 ? " class='cf-section'" : "").">{$field->section}</h4>"; // don't add space above for the first section
        }
    }
    $opts = [];
    $helpText = '';
    switch ($field->type) {
        case CustomFieldType::Text:
            break;
        /** @noinspection PhpMissingBreakStatementInspection */
        case CustomFieldType::AgbCheckbox:
            $opts['required'] = true;
        //no break
        case CustomFieldType::Checkbox:
            $opts['type'] = 'checkbox';
            break;
        case CustomFieldType::Number:
            $opts['type'] = 'text'; //'number' will make the browser display a spinner, which might be odd for things like a Postleitzahl
            $opts['pattern'] = '\d+';
            $opts['title'] = 'Bitte geben Sie nur Zahlen ein';
            break;
        case CustomFieldType::Dropdown:
            $opts['type'] = 'select';
			$currentVal = null;
			if ($field->_joinData != null) {
				$currentVal = $field->_joinData->value;
			}
            $a = $field->getComboBoxOptions($currentVal);
            $opts['options'] = array_combine($a, $a);
            break;
        default:
            throw new LogicException('Invalid custom field type');
    }
    if ($field->is_required) {
        $opts['required'] = true;
        $showRequiredNote = true;
    }
    if ($field->help_text !== '') {
        $opts['escape'] = false;
        $helpText = ' '.$this->Html->icon('question-sign', ['title' => $field->help_text, 'class' => 'text-muted']);
    }

    echo $this->Form->hidden("custom_fields.$i.id", ['value' => $field->id]);
    echo $this->Form->control("custom_fields.$i._joinData.value", [
            'label' => $field->name.$helpText
        ] + $opts);
    $i++;
}

if ($showRequiredNote): ?>
    <p>Mit einem Stern (*) markierte Felder müssen ausgefüllt werden.</p>
<?php endif; ?>
