<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Registration[] $registrations */

$this->extend('/Common/page');
$this->assign('title', 'Meine Anmeldungen');
?>
<p>
    Für die folgenden Projekte sind Sie aktuell angemeldet. Klicken Sie auf ein Projekt um weitere Details zu sehen,
    Ihre Modulwahl zu ändern, Ihre Anmeldebestätigung anzuzeigen, Dateien einzusenden usw.
</p>
<a class="btn btn-primary btn-lg" href="<?= $this->Url->build(['controller' => 'Registrations', 'action' => 'selectProject']) ?>" role="button">Für ein Projekt anmelden&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
<?php if ($registrations->count()): ?>
    <div class="col-md-6">
        <div class="list-group">
            <?php foreach($registrations as $registration): ?>
                <?php if (!$registration->project->hasTag('backupOf')) echo $this->Html->link("<b>{$registration->project->name}</b> (Angemeldet am {$registration->created})", ['action' => 'edit', $registration->id], ['escape' => false, 'class' => 'list-group-item']) ?>
            <?php endforeach ?>
        </div>
    </div>
<?php else: ?>
    <p class="lead">Sie sind derzeit für keine Projekte angemeldet.</p>
<?php endif; ?>