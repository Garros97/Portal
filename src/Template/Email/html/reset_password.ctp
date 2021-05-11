<?php
//IMPORTANT: Keep in sync with TEXT version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $newPassword
 */
$this->assign('title', 'Ihr neues Passwort');
?>
<b><?= h("{$user->greeting} {$user->full_name},") ?></b>
<br><br>
Sie haben Ihr Passwort für das <?= \Cake\Core\Configure::read('App.name') ?> zurückgesetzt. Ihre neuen Login-Daten lauten:
<br><br>
Accountname: <b><?= h($user->username) ?></b><br>
Passwort: <b><?= h($newPassword) ?></b><br>
<br>
Hier geht es <?= $this->Html->link('direkt zum Login', $this->Url->build(['prefix' => false, 'controller' => 'users', 'action' => 'login'], ['fullBase' => true])) ?>.
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
