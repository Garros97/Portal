<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\UploadedFile[] $files */
/** @var \App\Model\Entity\Group $group */
/** @var \App\Model\Entity\Course $course */
/** @var \App\Model\Entity\Rating[] $ratings */

$this->extend('/Common/edit');
$this->assign('title', '');
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zur Übersicht', ['action' => 'index', $course->project_id]);
echo $this->Chell->actionLink('remove-circle', 'Mit Nullen aufüllen', '#', ['id' => 'all-zeros-button']);
$this->end();
?>
<h2>Bewerten <small><?= "{$group->name} &mdash; {$course->name}" ?></small></h2>
<h3>Hochgeladene Dateien</h3>
<table class="table table-striped">
    <?= $this->Html->tableHeaders(['Dateiname', 'Datum', 'Eigentümer']) ?>
    <?php foreach($files as $file): ?>
        <tr>
            <td>
                <?php
                $content = $this->Html->link($file->original_filename, ['prefix' => false, 'controller' => 'Registrations', 'action' => 'viewFile', $file->id]);
                if ($file->is_deleted) {
                    echo "<del>{$content}</del>";
                } else {
                    echo $content;
                }
                ?>
            </td>
            <td><?= h($file->created) ?></td>
            <td><?= h($file->user->username) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<p><b>Hinweis</b>: Durchgestrichene <del>Dateien</del> wurde vom Besitzer gelöscht und sollten normalerweise bei der Bewertung nicht berücksichtigt werden.</p>

<h3>Bewertung</h3>
<?= $this->Form->create($ratings, ['horizontal' => true]); ?>
<?php foreach ($ratings as $rating): ?>
<fieldset>
    <legend><?= h($rating->scale->name) ?></legend>
    <p><?= nl2br(h($rating->scale->hint)) ?></p>
    <?php
    echo $this->Form->control($rating->scale_id . '.value', [
        'label' => 'Bewertung',
        'required' => false,
        ]);
    echo $this->Form->control($rating->scale_id . '.comment', [
        'label' => 'Kommentar',
    ]);
    echo $this->Form->control('', [
        'type' => 'static',
        'label' => 'Bewertung durch',
        'value' => $rating->isNew() ? '(noch nicht bewertet)' : $rating->rater_user->full_name
    ]);
    ?>
</fieldset>
<?php endforeach; ?>
<?= $this->Form->button('Speichern', ['class' => 'col-md-offset-3 col-lg-offset-2' ,'bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
<p>
    <b>Hinweis</b>: Kommentare sind für die Teilnehmenden nicht sichtbar.
</p>
<?php
$this->Html->scriptBlock(<<<'JS'
$(function() {
    "use strict";
    $('#all-zeros-button').click(function (e) {
        $('input[type=number]').filter(function() { return $(this).val() == ""}).val(0)
    })
}())
JS
    , ['block' => true]);
?>
