<?php
/** @var \App\View\AppView $this*/
?>

<div class="row">
    <div class="col-lg-2 col-md-3" id="sidebar-container">
        <div id="sidebar" data-spy="affix" data-offset-top="60">
            <?php if($this->exists('actions')): ?>
            <h3>Aktionen</h3>
            <ul class="nav nav-pills nav-stacked">
                <?= $this->fetch('actions') ?>
            </ul>
            <?php endif; ?>
            <?php if($this->exists('info-block')): ?>
            <h3>Informationen</h3>
                <?= $this->fetch('info-block') ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-10 col-md-9">
        <?php if($this->exists('title')): ?>
            <h2 class="edit-page-header"><?= $this->fetch('title') ?></h2>
        <?php endif; ?>
        <?= $this->fetch('content') ?>
    </div>
</div>