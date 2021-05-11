<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Form[]|\Cake\Collection\CollectionInterface $forms
 */

$this->extend('/Common/edit');
$this->assign('title', 'Formulare Übersicht');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('plus', 'Neues Formular', ['action' => 'add']);
$this->end();
?>
<?php foreach ($forms as $form): ?>
    <?php
    $header_style = 'default';
    if ($form->isOnline()) {
        $header_style = 'primary';
    } else if ($form->isOnline()) {
        $header_style = 'info';
    }
    $url = $form->getUrl();
    ?>
    <div class="panel panel-<?= $header_style ?>">
        <div class="panel-heading">
            <?= $this->Html->link(h($form->title), ['action' => 'edit', $form->id], ['class' => 'link-no-style']) ?> <span class="text-muted"><small>[<?= $this->Html->link(h($url), h($url), ['class' => 'link-no-style']) ?>]</small></span>
            <?php
            echo ' ';
            echo ' ';
            /*
            if (!$form->isOnline() &&  !$wakeningCall->isSent()) {
                echo $this->Html->icon('eye-close', ['title' => 'Weckruf nicht online']);
            }
            echo ' ';
            if ($wakeningCall->isSent()) {
                echo $this->Html->icon('ok', ['title' => 'Weckruf versendet']);
            }
            */
            $entryCount = count($form->form_entries);
            ?>
        </div>
        <div class="panel-body">
            <?= $this->Html->link($entryCount . ($entryCount == 1 ? ' Eintrag' : ' Einträge'), ['controller' => 'WakeningCallSubscribers', 'action' => 'index', $form->id]) ?>
            <div class="pull-right">
                <?php // if ($wakeningCall->isComplete() && $entryCount > 0 && $wakeningCall->isClosed() && !$wakeningCall->isSent()) echo $this->Form->postLink($this->Html->icon('send'), ['action' => 'send', $wakeningCall->id], ['confirm' => sprintf('Weckruf %s wirklich versenden?', $wakeningCall->id), 'escape' => false, 'class' => 'btn btn-default', 'title' => 'Versenden']); ?>
                <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $form->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Bearbeiten']) ?>
                <?php //if (!$wakeningCall->isClosed() && !$wakeningCall->isSent()) echo $this->Html->link($this->Html->icon($wakeningCall->isActive() ? 'lock' : 'eye-open'), ['action' => 'toggleVisibility', $wakeningCall->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Weckruf '.($wakeningCall->isActive() ? 'schließen' : 'aktivieren')]); ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?= $this->Html->icon('cog') ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><?= $this->Html->link($this->Html->icon('duplicate') . '&nbsp;Formular kopieren', '#', [
                                'data-toggle' => 'modal',
                                'data-target' => '#duplicateModal',
                                'data-form-id' => $form->id,
                                'data-form-title' => $form->title,
                                'escape' => false
                            ]) ?></li>
                        <li><?php if ($entryCount > 0) echo $this->Form->postLink($this->Html->icon('remove') . '&nbsp;Daten löschen', ['action' => 'deleteData', $form->id], ['confirm' => sprintf('Daten des Formulars %s wirklich löschen?', $form->id), 'escape' => false]); ?></li>
                        <li><?= $this->Form->postLink($this->Html->icon('trash') . '&nbsp;Formular löschen', ['action' => 'delete', $form->id], ['confirm' => sprintf('Formular %s wirklich löschen?', $form->id), 'escape' => false]) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

