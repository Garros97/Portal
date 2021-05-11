<?php
/** @var \App\View\AppView $this */
/** @var \Cake\ORM\Query $groups*/
?>

<?= $this->Panel->startGroup() ?>
<?= $this->Panel->create('Bestehende Gruppe wählen') ?>
<?php
echo $this->Form->control('group', [
    'label' => 'Gruppe',
    'options' => [-1 => '-- bitte wählen --'] + $groups->toArray()
]);
echo $this->Form->control('group_password', [
    'label' => 'Gruppenpasswort',
    'maxlength' => 20,
    'placeholder' => 'Erhalten Sie von den anderen Gruppenteilnehmern'
]);
?>
<?= $this->Panel->end() ?>
<?= $this->Panel->create('Neue Gruppe erstellen') ?>
<?php
echo $this->Form->control('newgroup_name', [
    'label' => 'Gruppenname',
]);
echo $this->Form->control('newgroup_password', [
    'label' => 'Gruppenpasswort',
    'maxlength' => 20,
]);
?>
<?= $this->Panel->end() ?>
<?= $this->Panel->endGroup() ?>
<?php
$this->Html->scriptBlock(<<<'JS'
$(function() {
    "use strict";

    $(function() {
        $('#collapse-1').on('show.bs.collapse', function() {
            $('#group').val(-1);
        });
    })
}())
JS
    , ['block' => true]);
?>
