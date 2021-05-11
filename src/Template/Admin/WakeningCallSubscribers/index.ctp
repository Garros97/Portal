<?php
/** @var \App\Model\Entity\WakeningCall $wakeningCall */
/** @var \App\Model\Entity\WakeningCallSubscriber[] $wakeningCallSubscribers */
/** @var \App\View\AppView $this */
$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Weckruf', ['controller' => 'WakeningCalls', 'action' => 'edit', $wakeningCall->id]);
$this->end();
?>

<h2>Empfängerliste <small><?= h($wakeningCall->name)?></small></h2>
<table class="table table-striped table-hover">
<thead>
    <tr>
        <th class="col-md-1">SID</th>
        <th>E-Mail-Adresse</th>
        <th class="col-md-1 actions">Aktionen</th>
    </tr>
</thead>
<tbody>
<?php foreach ($wakeningCallSubscribers as $subscriber): ?>
    <tr>
        <td><?= h($subscriber->id) ?></td>
        <td><?= h($subscriber->email) ?></td>
        <td class="actions">
            <?= $this->Form->postLink($this->Html->icon('remove'), ['action' => 'delete', $subscriber->id],
			['confirm' => 'Wollen Sie diesen Eintrag wirklich löschen? Dadurch wird der Empfänger von diesem Weckruf abgemeldet.', 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Eintrag löschen']) ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>