<?php
/** @var \App\View\AppView $this*/
/** @var \App\Model\Entity\Registration $registration */
/** @var \App\Model\Entity\Course $course */
/** @var \App\Form\FileUploadForm $uploadForm */
/** @var \App\Model\Entity\UploadedFile[] $files */
/** @var boolean $allowUploadsOutsideTimeframe */

$this->extend('/Common/page');
$this->assign('title', 'Datei-Upload <small>' . h($course->name) . ' (' . h($registration->project->name) . ')</small>');
?>
<?php if(!$course->isInUploadTimeframe()): ?>
    <?php if ($allowUploadsOutsideTimeframe): ?>
        <div class="alert alert-warning">
            Uploads sind in diesem Kurse eigentlich noch nicht/nicht mehr möglich, aber Sie können dennoch Dateien im Namen
            des Teilnehmenden hochladen.
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Uploads sind in diesem Kurse noch nicht/nicht mehr möglich.</div>
    <?php endif; ?>
<?php endif; ?>
<?php if($course->isInUploadTimeframe() || $allowUploadsOutsideTimeframe): ?>
    <h2>Neue Datei hochladen</h2>
    <?= $this->Form->create($uploadForm, ['horizontal' => 'true', 'type' => 'file']); ?>
    <?= $this->Form->control('file', [
        'type' => 'file',
        'label' => 'Datei'
    ]) ?>
    <?= $this->Form->button('Upload', ['bootstrap-type' => 'primary']) ?>
    <?= $this->Form->end() ?>
    <p>Maximale Dateigröße: <b><?= ini_get('upload_max_filesize') . 'B' ?></b></p>
<?php endif; ?>

<h2>Bisher hochgeladene Dateien:</h2>
<?php if ($files->count()): ?>
    <table class="table table-striped">
    <?= $this->Html->tableHeaders(['Dateiname', 'Datum', 'Eigentümer', 'Aktionen']) ?>
    <?php foreach($files as $file): ?>
    <tr>
        <td><?= $this->Html->link($file->original_filename, ['action' => 'viewFile', $file->id]) ?></td>
        <td><?= h($file->created) ?></td>
        <td><?= '<i>' . h($file->user->username) . '</i> (' . h($file->user->full_name) . ')' ?></td>
        <td>
            <?= $this->Html->link($this->Html->icon('eye-open'), ['action' => 'viewFile', $file->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Ansehen']) ?>
            <?php if($course->isInUploadTimeframe()): ?>
            <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'deleteFile', $file->id], ['confirm' => sprintf('Datei "%s" wirklich löschen?', $file->original_filename), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen']) ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
<?php else: ?>
<p class="lead">Es wurden bis jetzt noch keine Dateien hochgeladen.</p>
<?php endif; ?>