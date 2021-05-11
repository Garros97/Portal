<?php
/** @var \App\View\AppView $this */
$this->extend('/Common/page');
$this->assign('title', 'Suche');
?>
<?php if (strlen($ref) > 0): ?>
<div>
    <?= $this->Html->link($this->Html->icon('arrow-left') . ' ' .__('Zurück zu vorheriger Seite') , $ref, ['escape' => false, 'class' => 'btn btn-md btn-default', 'title' => 'Zurück']) ?>
</div>
<br>
<?php endif; ?>
<div class="qs-box col-sm-3">
    <input type="text" autocomplete="off" placeholder="Suchen ..." autofocus>
</div>
<form class="form-inline">
    <label for="project-selector" id="project-selector-label">in</label>
    <div class="form-group">
            <select class="form-control" id="project-selector">
                <option value="0">allen Projekten</option>
                <option disabled>aktive Projekte:</option>
                <?php foreach ($projects as $project): ?>
                    <?php
                    if (isset($lastProject) && $lastProject->isActive() && !$project->isActive()) {
                        echo "<option disabled>inaktive Projekte:</option>";
                    }
                    $lastProject = $project;
                    ?>
                    <option value="<?= $project->id ?>"><?= $project->name ?></option>
                <?php endforeach; ?>
            </select>
    </div>
</form>
<table id="search-table" class="table">
    <thead>
    <tr>
        <th class="text-center">UID</th> <th>RID</th> <th>Projektname</th> <th>Accountname</th> <th>E-Mail</th> <th>Vorname</th> <th>Nachname</th> <th>Gruppen</th> <th>Aktionen</th>
    </tr>
    </thead>
    <tbody class="scontainer"></tbody>
</table>