<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Form $form
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Form'), ['action' => 'edit', $form->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Form'), ['action' => 'delete', $form->id], ['confirm' => __('Are you sure you want to delete # {0}?', $form->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Forms'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Form'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="forms view large-9 medium-8 columns content">
    <h3><?= h($form->title) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Title') ?></th>
            <td><?= h($form->title) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Urlname') ?></th>
            <td><?= h($form->urlname) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Header Image') ?></th>
            <td><?= h($form->header_image) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($form->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('State') ?></th>
            <td><?= $this->Number->format($form->state) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($form->description)); ?>
    </div>
</div>
