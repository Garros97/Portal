<?php
/** @var \App\View\AppView $this */
/** @var \Bootstrap\View\Helper\NavbarHelper $nav */

$nav = $this->Navbar;

if ($this->request->getParam('prefix') !== 'admin') { //user menu
    echo $nav->beginMenu();
    echo $nav->link("uniKIK-Portal",'/');
    echo $nav->link('Meine Anmeldungen', ['controller' => 'Registrations', 'action' => 'index']);
    echo $nav->link('Meine Daten', ['controller' => 'Users', 'action' => 'edit']);
    echo $nav->endMenu();
}
else { //admin menu
    echo $nav->beginMenu();
    echo $nav->link('<i aria-hidden="true" class="glyphicon glyphicon-home"></i>','/');

    echo $nav->beginMenu('Projekte');
    echo $nav->link('Übersicht', ['controller' => 'Projects', 'action' => 'index']);
    echo $nav->divider();
    echo $nav->link('Neu anlegen', ['controller' => 'Projects', 'action' => 'add']);
    echo $nav->endMenu();

    echo $nav->beginMenu('Accounts');
    echo $nav->link('Übersicht', ['controller' => 'Users', 'action' => 'index']);
    echo $nav->divider();
    echo $nav->link('Suche', ['controller' => 'Search', 'action' => 'index']);
    echo $nav->divider();
    echo $nav->link('Neu anlegen', ['controller' => 'Users', 'action' => 'add']);
    echo $nav->endMenu();

    echo $nav->beginMenu('Sonstiges');
    echo $nav->link('Rechte', ['controller' => 'Rights', 'action' => 'index']);
    echo $nav->divider();
    echo $nav->link('Bewertungen', ['controller' => 'Ratings', 'action' => 'chooseProject']);
    echo $nav->divider();
    echo $nav->link('Weckrufe', ['controller' => 'WakeningCalls', 'action' => 'index']);
    echo $nav->divider();
    echo $nav->link('Statistik', ['controller' => 'Stats', 'action' => 'index']);
    echo $nav->endMenu();

    echo $nav->endMenu();
}
