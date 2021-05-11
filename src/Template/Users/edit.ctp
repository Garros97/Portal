<?php
/** @var \App\Model\Entity\User $user */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php $this->start('info-block'); ?>
<p><b>Account-ID:</b> <?= $user->id ?></p>
<p><b>Letzter Login:</b><br /><?= $user->last_login ?></p>
<?php $this->end(); ?>

<?= $this->Form->create($user, ['horizontal' => true]); ?>
<fieldset>
    <legend>Account bearbeiten</legend>
    <?php
    echo $this->Form->control('username', [
        'type' => 'static',
        'label' => 'Accountname'
    ]);
    echo $this->Form->control('email', [
        'label' => 'E-Mail',
    ]);
    ?>
    <?php
    echo $this->Form->control('sex', [
        'label' => 'Geschlecht',
        'options' => ['m' => 'Herr', 'f' => 'Frau', 'x' => 'k.A./anderes']
    ]);
    echo $this->Form->control('first_name', [
        'label' => 'Vorname',
        'div' => false
    ]);
    echo $this->Form->control('last_name', [
        'label' => 'Nachname'
    ]);
    echo $this->Form->control('street', [
        'label' => 'Straße'
    ]);
    echo $this->Form->control('house_number', [
        'label' => 'Hausnummer'
    ]);
    echo $this->Form->control('postal_code', [
        'label' => 'PLZ'
    ]);
    echo $this->Form->control('city', [
        'label' => 'Stadt'
    ]);
    echo $this->Form->control('is_teacher',[
        'label' => 'Funktionen für Lehrkräfte aktivieren',
        'type' => 'checkbox',
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Daten speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?= $this->Form->create(null, ['horizontal' => true, 'url' => ['action' => 'changePassword']]) ?>
<fieldset>
    <legend>Passwort ändern</legend>
    <?php
    echo $this->Form->hidden('uid', ['value' => $user->id]);
    echo $this->Form->control('old_password', [
        'type' => 'password',
        'label' => 'Altes Passwort',
        'placeholder' => '••••••••••••••••'
    ]);
    echo $this->Form->control('new_password1', [
        'type' => 'password',
        'label' => 'Neues Passwort',
        'placeholder' => '••••••••••••••••'
    ]);
    echo $this->Form->control('new_password2', [
        'type' => 'password',
        'label' => 'Wiederholung',
        'placeholder' => '••••••••••••••••'
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Passwort ändern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
<h4>Account löschen</h4>
<p>
    Wenn Sie Ihren Account löschen möchten, wenden Sie sich bitte per E-Mail <?= $this->Html->link('an uns', ['controller' => 'pages', 'action' => 'display', 'imprint']) ?>.
</p>
<h4>Nachrichtenbrief</h4>
<p>
    Wir laden Sie herzlich ein, unseren Nachrichtenbrief zu abonnieren,
	um immer über aktuelle Projekte informiert zu sein. Sie können sich auf der Seite des Nachrichtenbrief eingenständig
	<?= $this->Html->link('anmelden', $newsletterSignupUrl, ['target' => '_blank']) ?> oder <?= $this->Html->link('abmelden', $newsletterUnsubUrl, ['target' => '_blank']) ?>.
	Außerdem finden Sie in jeder E-Mail einen Link um sich abzumelden. 
</p>