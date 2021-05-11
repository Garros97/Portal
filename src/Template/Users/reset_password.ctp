<?php
/** @var \App\View\AppView $this*/

$this->extend('/Common/page');
?>

<div class="col-sm-6 col-sm-offset-3">
    <?= $this->Panel->create('Passwort zurücksetzen') ?>
    Sie haben ihr Passwort vergessen? Bitte geben Sie einen Accountnamen oder eine E-Mail-Adresse ein, um ein neues Passwort anzufordern. Wir schicken das neue Passwort
    an die im Account hinterlegte E-Mail-Adresse.
    <?= $this->Form->create(null) ?>
    <?= $this->Form->control('username_or_email', [
        'autofocus',
        'label' => 'Accountname oder E-Mail-Adresse',
        'placeholder' => 'Accountname oder E-Mail'
    ]) ?>
    <?= $this->Form->button('Absenden', ['bootstrap-type' => 'primary']) ?>
    <?= $this->Form->end() ?>

    <?= $this->Panel->end(); ?>
</div>