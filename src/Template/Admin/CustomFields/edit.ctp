<?php
use App\Model\Entity\CustomField;
use App\Model\Entity\CustomFieldType;

/** @var \App\Model\Entity\CustomField $customField */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $customField->project_id]);
echo $this->Chell->actionLink('trash', 'Zusatzfeld löschen', ['action' => 'delete', $customField->id], ['confirm' => sprintf('Zusatzfeld "%s" wirklich löschen?', $customField->name)]);
$this->end();
?>

<?= $this->Form->create($customField, ['horizontal' => true]); ?>
<fieldset>
    <legend>Zusatzfeld bearbeiten</legend>
    <?php
        echo $this->Form->hidden('project_id');
        echo $this->Form->control('name', [
            'label' => 'Name'
        ]);
        echo $this->Form->control('section', [
            'label' => 'Abschnitt'
        ]);
        echo $this->Form->control('type', [
            'label' => 'Typ',
            'options' => [ //looks a bit bad, but other ways are more complicated
                CustomFieldType::Text => CustomField::getTypeName(CustomFieldType::Text),
                CustomFieldType::Checkbox => CustomField::getTypeName(CustomFieldType::Checkbox),
                CustomFieldType::AgbCheckbox => CustomField::getTypeName(CustomFieldType::AgbCheckbox),
                CustomFieldType::Number => CustomField::getTypeName(CustomFieldType::Number),
                CustomFieldType::Dropdown => CustomField::getTypeName(CustomFieldType::Dropdown)
            ]
        ]);
        echo $this->Form->control('backend_only', [
            'label' => 'Nur im Admin-Bereich anzeigen',
            'title' => 'Wenn die Option gewählt wird, wird der Wert nicht bei der Anmeldung abgefragt und ist nur intern sichtbar.'
        ]);
        echo $this->Form->control('combo_box_values', [
            'label' => 'Werte für Auswahlfeld',
            'type' => 'text',
            'title' => 'Wenn der Typ des Feldes "Auswahlfeld" ist, müssen hier die Wahlmöglichkeiten einegetragen werden.
                        Die Werte werden mit einem Komma getrennt. Beispiel: <i>alpha,beta,gamma</i> oder <i>---,Ja,Nein</i>'
        ])
    ?>
    <?= $this->Panel->startGroup(['open' => -1]) ?>
    <?= $this->Panel->create('Weitere Optionen') ?>
        <?php
        echo $this->Form->control('help_text', [
            'label' => 'Kurzhilfe'
        ]);
        ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->endGroup() ?>
</fieldset>
<p><b>Hinweis:</b> Felder deren Name mit einem Stern (*) endet, müssen bei der Anmeldung ausgefüllt werden. Der Wert
"---" wird in Auswahlfeldern als "ungültiger Wert" verwendet und darf von Benutzer nicht gewählt werden, wenn das Feld
als verplichtend gekennzeichnet ist (s.o.)</p>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
