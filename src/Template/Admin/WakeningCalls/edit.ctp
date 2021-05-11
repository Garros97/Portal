<?php
/** @var \App\Model\Entity\WakeningCall $wakeningCall */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
$entryCount = count($wakeningCall->wakening_call_subscribers);
?>
<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Alle Weckrufe', ['action' => 'index']);
echo $this->Chell->actionLink('user', 'Empfängerliste', ['controller' => 'WakeningCallSubscribers', 'action' => 'index', $wakeningCall->id]);
if ($entryCount > 0) echo $this->Chell->actionLink('remove', 'Daten löschen', ['action' => 'deleteData', $wakeningCall->id], ['confirm' => sprintf('Daten des Weckrufs wirklich löschen?', $wakeningCall->id)]);
echo $this->Chell->actionLink('trash', 'Weckruf löschen', ['action' => 'delete', $wakeningCall->id], ['confirm' => sprintf('Weckruf %d wirklich löschen?', $wakeningCall->id)]);
if ($wakeningCall->isComplete()) echo $this->Chell->actionLink('envelope', 'Testnachricht versenden', ['action' => 'sendTestMail', $wakeningCall->id]);
if ($wakeningCall->isComplete() && $entryCount > 0 && $wakeningCall->isClosed() && !$wakeningCall->isSent()) echo $this->Chell->actionLink('send', 'Weckruf versenden', ['action' => 'send', $wakeningCall->id], ['confirm' => sprintf('Weckruf %d wirklich versenden?', $wakeningCall->id)]);

$this->end();
$this->start('info-block');
$url = $wakeningCall->getUrl();
echo "<b>".count($wakeningCall->wakening_call_subscribers)."</b>".($entryCount == 1 ? ' Eintrag' : ' Einträge');
echo "<br>";
echo "<b>Anmeldelink:</b> ".($this->Html->link(h($url), h($url)));

$this->end();
?>

<?= $this->Form->create($wakeningCall, ['horizontal' => true]) ?>
<fieldset>
    <legend>Weckruf bearbeiten</legend>
    <?php
    echo $this->Form->control('name', [
        'label' => 'Name'
    ]);
    echo $this->Form->control('state', [
        'label' => 'Status',
        'options' => [
                1 => "unsichtbar",
                0 => "aktiv",
                2 => "geschlossen",
                3 => "versendet"
        ],
        'disabled' => ($wakeningCall->isSent() ? [0,1,2] : []),
        'data-provide' => 'dropdown'
    ]);
    echo $this->Form->control('permanent', ['label' => 'permanent', 'type' => 'checkbox']);
    echo $this->Form->control('email_from', [
        'label' => 'Absender'
    ]);
    echo $this->Form->control('email_subject', [
        'label' => 'Betreff'
    ]);
    echo $this->Form->control('message', [
        'label' => 'Nachrichtentext',
        'data-provide' => 'rich-text-editor'
    ]);
    $this->assign('has-rich-text-editor', true);
    ?>
</fieldset>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?= $this->fetch('postLink') ?>