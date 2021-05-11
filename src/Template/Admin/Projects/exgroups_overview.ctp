<?php
/** @var \App\Model\Entity\Project $project */
/** @var \App\View\AppView $this */
/** @var array $tags */
/** @var array $data */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['action' => 'edit', $project->id]);
echo $this->Chell->actionLink('refresh', 'Aktualisieren', []);
$this->end();
?>

<div class="page-header">
    <h2>Auschlussgruppen <small><?= h($project->name) ?></small></h2>
</div>

<table id="exgroups-overview-table" class="table table-striped table-hover">
    <colgroup>
        <col style="width: 30%">
        <col span="<?= count($tags) ?>">
    </colgroup>
    <thead>
    <?php $header = array_values($tags); array_unshift($header, 'Kurs'); ?>
    <?= $this->Html->tableHeaders($header) ?>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
        <?php foreach ($row as $key => $entry): ?>
            <?php if ($key === 'name'): ?>
                <td><?= $entry ?></td>
            <?php else: ?>
                <td class="exgroup-entry" data-content="<?= $entry ?>"><span class="sr-only"><?= $entry == '1' ? 'Ja' : 'Nein' ?></span></td>
            <?php endif; ?>
        <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p>
    Diese Tabelle stellt eine Übersicht über alle Ausschlussgruppen in diesem Projekt dar. Zwei Kurse
    können nicht gleichzeitig gewählt werden, wenn sie in mindestens einer Spalte beide eine rote Markierung haben.
</p>
<p>
    <b>Tipp</b>: Bei der Arbeit an Ausschlussgruppen kann es nützlich sein, diese Seite in einem eigenen Browser-Tab zu öffnen
    und gelegentlich zu aktualisieren, um zu kontrollieren, ob die Auschlussgruppen korrekt eingestellt sind.
</p>