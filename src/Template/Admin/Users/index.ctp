<?php
/** @var \App\Model\Entity\User[] $users */
/** @var \App\View\AppView $this */
/** @var string $filter */
$this->extend('/Common/edit');
$this->assign('title', 'Account Übersicht');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('plus', 'Neuer Account', ['action' => 'add']);
$this->end();
?>
<table class="table table-striped">
<thead>
    <tr>
        <th><?= $this->Paginator->sort('id', 'ID') ?></th>
        <th><?= $this->Paginator->sort('username', 'Accountname') ?></th>
        <th><?= $this->Paginator->sort('email', 'E-Mail') ?></th>
        <th><?= $this->Paginator->sort('first_name', 'Vorname') ?></th>
        <th><?= $this->Paginator->sort('last_name', 'Nachname') ?></th>
        <th class="actions">Aktionen</th>
    </tr>
</thead>
<tbody class="scontainer"></tbody>
<tbody class="ovcontainer">
<?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user->id ?></td>
        <td><?= h($user->username) ?></td>
        <td><?= h($user->email) ?></td>
        <td><?= h($user->first_name) ?></td>
        <td><?= h($user->last_name) ?></td>
        <td class="actions">
            <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $user->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten']) ?>
            <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'delete', $user->id], ['confirm' => sprintf('Account %s wirklich löschen?', $user->username), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen']) ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>
<?php if($filter): ?>
    <div class="alert alert-info">Die Ergebnise wurden gefiltert nach: <i><?= h($filter) ?></i></div>
<?php endif; ?>

