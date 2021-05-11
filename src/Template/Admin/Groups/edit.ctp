<?php
/** @var \App\Model\Entity\Group $group */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $group->project_id]);
echo $this->Chell->actionLink('list', 'Alle Gruppen', ['action' => 'index', $group->project_id]);
echo $this->Chell->actionLink('trash', 'Gruppe löschen', ['action' => 'delete', $group->id], ['confirm' => sprintf('Gruppe %s wirklich löschen?', $group->name)]);
$this->end();
?>

<?= $this->Form->create($group, ['horizontal' => true]) ?>
<fieldset>
    <legend>Gruppe bearbeiten</legend>
    <?php
        echo $this->Form->control('name', [
            'label' => 'Gruppenname'
        ]);
        echo $this->Form->control('project.name', [
            'label' => 'Projekt',
            'type' => 'static'
        ]);
        echo $this->Form->control('password', [
            'label' => 'Gruppenpasswort',
            'type' => 'text'
        ]);
    ?>
</fieldset>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<h3>Teilnehmende in dieser Gruppe</h3>
<?php if(count($group->users)): ?>
<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Accountname</th>
        <th>E-Mail</th>
        <th>Vorname></th>
        <th>Nachname</th>
        <th class="actions">Aktionen</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($group->users as $user): ?>
        <tr>
            <td><?= $user->id ?></td>
            <td><?= h($user->username) ?></td>
            <td><?= h($user->email) ?></td>
            <td><?= h($user->first_name) ?></td>
            <td><?= h($user->last_name) ?></td>
            <td class="actions">
                <?= $this->Html->link($this->Html->icon('pencil'), ['controller' => 'Users', 'action' => 'edit', $user->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten']) ?>
                <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'remove_from_group', $group->id, $user->id], ['confirm' => sprintf('Account %s wirklich aus Gruppe %s entfernen?', $user->username, $group->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Aus Gruppe entfernen']) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h4>E-Mail-Adressen der Teilnehmer</h4>
<?php
$mails = join('; ', collection($group->users)->map(function ($user) { return $user->email; })->toArray())
?>
<div class="well">
    <?= $mails ?>
</div>
<?= $this->Html->link($this->Html->icon('send') . ' E-Mail an alle Teilnehmenden senden (per BCC)', "mailto:?bcc=$mails", ['class' => 'btn btn-default', 'escapeTitle' => false]) ?>
<?php else: ?>
<p>Diese Gruppe ist leer</p>
<?php endif; ?>