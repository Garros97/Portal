<?php
/** @var \App\Model\Entity\Scale $scale */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Kurs', ['controller' => 'Courses', 'action' => 'edit', $scale->course_id]);
echo $this->Chell->actionLink('trash', 'Skala löschen', ['action' => 'delete', $scale->id], ['confirm' => sprintf('Skala %s wirklch löschen?', $scale->name)]);
$this->end();
?>

<?= $this->Form->create($scale, ['horizontal' => true]); ?>
<fieldset>
    <legend><?= 'Skala bearbeiten' ?></legend>
    <?php
        echo $this->Form->control('name', [
            'label' => 'Name'
        ]);
        echo $this->Form->control('hint', [
            'label' => 'Hinweis',
            'title' => 'Hier können Anmerkungen für den Korrektor o.ä. eingegeben werden.'
        ]);
    ?>
</fieldset>
<?= $this->Form->button('Speichern', ['class' => 'col-md-offset-3 col-lg-offset-2' ,'bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
