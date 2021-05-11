<?php
//IMPORTANT: Keep in sync with TEXT version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $source
 * @var string $password
 */
$this->assign('title', 'Account erstellt');
?>
<b><?= h("{$user->greeting} {$user->full_name},") ?></b>
<br><br>
Es wurde ein neuer Account für Sie erstellt.
<br><br>
Accountname: <b><?= h($user->username) ?></b><br>
Passwort: <i><?= ($source == 'admin') ? h($password) : '(von Ihnen gewählt)' ?></i>
<br>
Hier geht es <?= $this->Html->link('direkt zum Login', $this->Url->build(['controller' => 'users', 'action' => 'login', 'prefix' => false], ['fullBase' => true])) ?>.
<br><br>
Wenn Sie sich gerade für ein Projekt angemeldet haben, erhalten Sie die <b>Anmeldebestätigung</b> mit einer <b>weiteren Mail</b>.
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
