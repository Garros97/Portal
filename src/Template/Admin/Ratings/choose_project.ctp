<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project[] $projects */

$this->extend('/Common/page');
$this->assign('title', 'Projekt auswählen');
?>
<p class="lead">
    Bitte wählen Sie aus der Liste das Projekt, das Sie bewerten wollen.
</p>
<div class="row">
    <div class="col-md-6">
        <div class="list-group">
            <?php foreach ($projects as $project): ?>
            <a href="<?= $this->Url->build(['action' => 'index', $project->id]) ?>" class="list-group-item"><?= h($project->name) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>