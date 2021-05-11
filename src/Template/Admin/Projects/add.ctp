<?php
/** @var \App\Model\Entity\Project $project */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Zur Projektliste', ['action' => 'index']);
$this->end();
?>

<?= $this->Form->create($project, ['horizontal' => true]) ?>
<fieldset>
    <legend>Projekt hinzufügen</legend>
    <?php
    echo $this->Form->control('name', [
        'label' => 'Name'
    ]);
    echo $this->Form->control('urlname', [
        'label' => 'Kurzname',
        'title' => 'Diese Kurzfassung ("Slug") wird in Links zu diesem Projekt verwendet. Beispiel: <em>unifit15</em> für uni:fit im Jahr 2015.<br />Nur die Zeichen <em>a-z,A-Z,0-9,-,_</em> sind erlaubt.'
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Projekt hinzufügen', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
