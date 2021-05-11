<?php
/** @var \App\Model\Entity\WakeningCall $wakeningCall */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
$this->assign('title', 'Weckrufe Übersicht');
?>

<!-- Modal for duplicate -->
<?= $this->Modal->create('Weckruf kopieren', ['id' => 'duplicateModal', 'size' => 'large']) ?>
<?= $this->Form->create(null, ['horizontal' => true, 'url' => ['controller' => 'WakeningCalls', 'action' => 'duplicate', '{ID}']]); ?>
<p>
    Bitte geben Sie einen neuen Namen für die Kopie des Weckrufs ein. Alle weiteren Einstellungen
    können Sie im nächsten Schritt bearbeiten.
</p>
<p>
    Kopiert werden:
<ul>
    <li>alle Einstellungen des Weckrufs (bis auf den Status)</li>
    <li>alle Einträge</li>
</ul>
</p>
<fieldset>
    <?php
    echo $this->Form->control('new_name', [
        'id' => 'duplicate-name',
        'label' => 'Name'
    ]);
    ?>
</fieldset>
<?= $this->Modal->end([
    $this->Form->button('Absenden', ['bootstrap-type' => 'primary']),
    $this->Form->button('Abbrechen', ['data-dismiss' => 'modal'])
]) ?>
<?= $this->Form->end() ?>
<!-- end of modal -->
<?php
$this->start('actions');
echo $this->Chell->actionLink('plus', 'Neuer Weckruf', ['action' => 'add']);
$this->end();
?>

<?php foreach ($wakeningCalls as $wakeningCall): ?>
    <?php
    $header_style = 'default';
    if ($wakeningCall->isActive()) {
        $header_style = 'primary';
    } else if ($wakeningCall->isHidden()) {
        $header_style = 'info';
    }
    $url = $wakeningCall->getUrl();
    ?>
    <div class="panel panel-<?= $header_style ?>">
        <div class="panel-heading">
           <?= $this->Html->link(h($wakeningCall->name), ['action' => 'edit', $wakeningCall->id], ['class' => 'link-no-style']) ?> <span class="text-muted"><small>[<?= $this->Html->link(h($url), h($url), ['class' => 'link-no-style']) ?>]</small></span>
            <?php
            if ($wakeningCall->isPermanent()) {
                echo $this->Html->icon('repeat', ['title' => 'permanenter Weckruf']);
            }
            echo ' ';
            if (!$wakeningCall->isComplete()) {
                echo $this->Html->icon('exclamation-sign', ['title' => 'noch nicht alle Felder ausgefüllt']);
            }
            echo ' ';
            if (!$wakeningCall->isActive() &&  !$wakeningCall->isSent()) {
                echo $this->Html->icon('eye-close', ['title' => 'Weckruf nicht online']);
            }
            echo ' ';
            if ($wakeningCall->isSent()) {
                echo $this->Html->icon('ok', ['title' => 'Weckruf versendet']);
            }
            $entryCount = count($wakeningCall->wakening_call_subscribers);
            ?>
        </div>
        <div class="panel-body">
            <?= $this->Html->link($entryCount . ($entryCount == 1 ? ' Eintrag' : ' Einträge'), ['controller' => 'WakeningCallSubscribers', 'action' => 'index', $wakeningCall->id]) ?>
            <div class="pull-right">
                <?php if ($wakeningCall->isComplete() && $entryCount > 0 && $wakeningCall->isClosed() && !$wakeningCall->isSent()) echo $this->Form->postLink($this->Html->icon('send'), ['action' => 'send', $wakeningCall->id], ['confirm' => sprintf('Weckruf %s wirklich versenden?', $wakeningCall->id), 'escape' => false, 'class' => 'btn btn-default', 'title' => 'Versenden']); ?>
                <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $wakeningCall->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Bearbeiten']) ?>
                <?php if (!$wakeningCall->isClosed() && !$wakeningCall->isSent()) echo $this->Html->link($this->Html->icon($wakeningCall->isActive() ? 'lock' : 'eye-open'), ['action' => 'toggleVisibility', $wakeningCall->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Weckruf '.($wakeningCall->isActive() ? 'schließen' : 'aktivieren')]); ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?= $this->Html->icon('cog') ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><?= $this->Html->link($this->Html->icon('duplicate') . '&nbsp;Weckruf kopieren', '#', [
                                'data-toggle' => 'modal',
                                'data-target' => '#duplicateModal',
                                'data-wakeningcall-id' => $wakeningCall->id,
                                'data-wakeningcall-name' => $wakeningCall->name,
                                'escape' => false
                            ]) ?></li>
                        <li><?php if ($entryCount > 0) echo $this->Form->postLink($this->Html->icon('remove') . '&nbsp;Daten löschen', ['action' => 'deleteData', $wakeningCall->id], ['confirm' => sprintf('Daten des Weckrufs %s wirklich löschen?', $wakeningCall->id), 'escape' => false]); ?></li>
                        <li><?= $this->Form->postLink($this->Html->icon('trash') . '&nbsp;Weckruf löschen', ['action' => 'delete', $wakeningCall->id], ['confirm' => sprintf('Weckruf %s wirklich löschen?', $wakeningCall->id), 'escape' => false]) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
$this->Html->scriptBlock(<<<'JS'
    $(function() {
        "use strict";
        $('[data-toggle="tooltip"]').tooltip({container: "body"})
        $('#duplicateModal').on('show.bs.modal', function (event) {
            var link = $(event.relatedTarget);
            var modal = $(this);
            modal.find('form').attr('action', function (i, v) { return v.replace(encodeURIComponent('{ID}'), link.data('wakeningcall-id')) });
            modal.find('#duplicate-name').val(link.data('wakeningcall-name'));
        })
    });
JS
    , ['block' => true]);
?>