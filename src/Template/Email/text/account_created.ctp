<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $source
 * @var string $password
 */
$this->assign('title', 'Account erstellt');
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Es wurde ein neuer Account für Sie erstellt.

Accountname: <?= h($user->username) ?>

Passwort: <?= ($source == 'admin') ? h($password) : '(von Ihnen gewählt)' ?>

Hier geht es direkt zum Login: <?= $this->Url->build(['controller' => 'users', 'action' => 'login', 'prefix' => false], ['fullBase' => true]) ?>.

Wenn Sie sich gerade für ein Projekt angemeldet haben, erhalten Sie die *Anmeldebestätigung* mit einer *weiteren Mail*.

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
