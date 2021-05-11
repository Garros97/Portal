<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Form $form
 */
$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Zur Formulare-Übersicht', ['action' => 'index']);
$this->end();
?>

<?= $this->Form->create($form, ['horizontal' => true]) ?>
    <fieldset>
        <legend>Formular hinzufügen</legend>
        <?php
        echo $this->Form->control('title', [
            'label' => 'Titel'
        ]);
        echo $this->Form->control('urlname', [
            'label' => 'Kurzname',
            'title' => 'Diese Kurzfassung ("Slug") wird in Links zu diesem Formular verwendet. Beispiel: <em>dasu47</em> für das 47. DASU-Symposium.<br />Nur die Zeichen <em>a-z,A-Z,0-9,-,_</em> sind erlaubt.'

        ]);
        ?>
    </fieldset>
<?= $this->Form->button('Formular hinzufügen', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>