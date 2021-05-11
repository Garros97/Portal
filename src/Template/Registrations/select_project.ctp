<?php
/** @var \App\View\AppView $this */
/** @var \App\Form\SelectProjectForm $selectProjectForm */
/** @var array $validProjects */

$this->extend('/Common/page');
$this->assign('title', 'Für ein Projekt anmelden');
?>
<p>
    Bitte wählen Sie aus der Liste das Projekt, für das Sie sich anmelden möchten.<br>
    Ist das anzumeldende Projekt nicht aufgelistet, wenden Sie sich bitte an
    <a href="javascript:mailto('info','schulprojekte.uni-hannover.de')">info<img src="" alt="@" />schulprojekte.uni-hannover.de</a>.
</p>
<?= $this->Form->create($selectProjectForm, ['horizontal' => true]) ?>
<fieldset>
    <legend>Projekt auswählen</legend>
    <?php
    echo $this->Form->control('project', [
        'label' => 'Projekt',
        'options' => $validProjects
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Auswählen', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
