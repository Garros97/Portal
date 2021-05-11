<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="container">
    <p class="text-muted">
        &copy; <?= date('Y') ?> <?= $this->Html->link('uniKIK', 'https://www.lehrerbildung.uni-hannover.de/de/schulprojekte/', ['target' => '_blank']) ?> &mdash;
        <?php if (\Cake\Core\Configure::read('debug')): ?>
            <b>Entwicklungsversion</b> &mdash; the cake is a <span style="vertical-align: -10%">l</span><span style="vertical-align: -15%">i</span><span style="vertical-align: -23%">e</span> &mdash;
        <?php endif; ?>
        <span><?= $this->Html->link('Impressum', ['controller' =>  'Pages', 'action' => 'display', 'prefix' => false,  'imprint']) ?> &mdash;</span>
        <span> <?= $this->Html->link('Datenschutz', ['controller' =>  'Pages', 'action' => 'display', 'prefix' => false,  'dse']) ?></span>
    </p>
</div>
