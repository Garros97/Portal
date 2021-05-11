<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php $this->assign('title', 'Startseite') ?>
<div class="jumbotron">
    <h1>Herzlich willkommen im <br><b>uniKIK-Portal</b>!</h1>
    <p>
        Auf dieser Seite können Sie sich für <a href="https://www.uni-hannover.de/uniKIK/" target="_blank">uniKIK</a>-Projekte der
        <a href="https://uni-hannover.de" target="_blank">Leibniz Universität Hannover</a> sowie für Projekte externer Kooperationspartner anmelden.
    </p>
    <p>
        <a class="btn btn-primary btn-lg" href="<?= $this->Url->build(['controller' => 'Registrations', 'action' => 'selectProject']) ?>" role="button">Für ein Projekt anmelden&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
        <?php if (!$this->request->getSession()->check('Auth.User')): ?>
            <a class="btn btn-default btn-lg" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>" role="button">Einloggen&nbsp;<?= $this->Html->icon('menu-right') ?></a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg" href="<?= $this->Url->build(['controller' => 'Registrations', 'action' => 'index']) ?>" role="button">Meine Anmeldungen&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
            <a class="btn btn-primary btn-lg" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'edit']) ?>" role="button">Meine Daten&nbsp;<?= $this->Html->icon('menu-right') ?></i></a>
        <?php endif; ?>
    </p>
</div>
