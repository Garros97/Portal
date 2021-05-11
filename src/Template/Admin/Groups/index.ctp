<?php
/** @var \App\Model\Entity\Group[] $groups */
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project $project */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $project->id]);
echo $this->Chell->actionLink('list', 'Als XLS-Datei', ['controller' => 'exports', 'action' => 'groups', '_ext' => 'xls', $project->urlname]);
echo $this->Chell->actionLink('list-alt', 'Als XLSX-Datei', ['controller' => 'exports', 'action' => 'groups', '_ext' => 'xlsx', $project->urlname]);
echo $this->Chell->actionLink('file', 'Als CSV-Datei', ['controller' => 'exports', 'action' => 'groups', '_ext' => 'csv', $project->urlname]);
echo $this->Chell->actionLink('eye-open', 'Im Browser ansehen', ['controller' => 'exports', 'action' => 'groups', '_ext' => 'html', $project->urlname], ['target' => '_blank']);

$this->end();
?>

<h2>Gruppenliste <small><?= h($project->name) ?></small></h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th><?= $this->Paginator->sort('id', 'ID') ?></th>
            <th><?= $this->Paginator->sort('name', 'Name') ?></th>
            <th>Anzahl Mitglieder</th>
            <th class="actions">Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($groups as $group): ?>
        <tr>
            <td><?= $group->id ?></td>
            <td><?= h($group->name) ?></td>
            <td><?= $group->user_count ?></td>
            <td class="actions">
                <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $group->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten']) ?>
                <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'delete', $group->id], ['confirm' => sprintf('Gruppe %s wirklich löschen?', $group->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>
