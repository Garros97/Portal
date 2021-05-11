<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $newPassword
 */
$this->assign('title', 'Ihr neues Passwort');
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Sie haben Ihr Passwort für das <?= \Cake\Core\Configure::read('App.name') ?> zurückgesetzt. Ihre neuen Login-Daten lauten:

Accountname: <?= h($user->username) ?>

Passwort: <?= h($newPassword) ?>


Hier geht es direkt zum Login: <?= $this->Url->build(['controller' => 'users', 'action' => 'login'], ['fullBase' => true]) ?>.

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
