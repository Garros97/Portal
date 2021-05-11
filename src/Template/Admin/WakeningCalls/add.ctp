<?php
/** @var \App\Model\Entity\WakeningCall $wakeningCall */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Zur Weckruf-Übersicht', ['action' => 'index']);
$this->end();
?>

<?= $this->Form->create($wakeningCall, ['horizontal' => true]) ?>
<fieldset>
    <legend>Weckruf hinzufügen</legend>
    <?php
    echo $this->Form->control('name', [
        'label' => 'Name'
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Weckruf hinzufügen', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>