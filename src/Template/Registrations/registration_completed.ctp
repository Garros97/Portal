<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project $project */

$this->extend('/Common/page');
$this->assign('title', 'Anmeldung abgeschlossen');
?>
<p>
    Ihre Registrierung wurde erfolgreich gespeichert. <b>Eine Bestätigung wurde an Ihre E-Mail-Adresse geschickt.</b>
</p>
<p>
    Je nach Projekt, für das Sie sich angemeldet haben, können Sie möglichweise unter "Meine Anmeldungen" noch Änderungen
    an Ihrer Modulwahl oder weiteren Daten vornehmen.
</p>
<p>
    <a class="btn btn-primary" href="<?= $this->Url->build(['controller' => 'Registrations', 'action' => 'index']) ?>" role="button">Weiter zu "Meine Anmeldungen"&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
</p>
<?php if ($project->hasTag('showRegisterOtherUserLink')): ?>
<h3>Weiteren Teilnehmende anmelden</h3>
<p>
    Möchten Sie einen weiteren Teilnehmenden für dieses Projekt anmelden? Dann klicken Sie bitten auf die nachfolgende Schaltfläche.
    <b>Hinweis:</b> Jeder Teilnehmende benötigt einen <i>eigenen</i> Account in unserem Portal. Melden Sie sich daher bitte auf der
    folgenden Seite mit dem Account des nächsten Teilnehmenden an, bzw. legen Sie einen neuen Account an.
</p>
<p>
    <a class="btn btn-primary" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => $this->Url->build(['controller' => 'Registrations', 'action' => 'registerForProject', $project->urlname, '_base' => false])]]) ?>" role="button">Weiteren Teilnehmer anmelden&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
</p>
<?php endif ?>
