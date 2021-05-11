<?php
/** @var \App\View\AppView $this*/
?>
<?php if($this->exists('title')): ?>
    <div class="page-header">
        <h1><?= $this->fetch('title') ?></h1>
    </div>
<?php endif; ?>

<?= $this->fetch('content') ?>
