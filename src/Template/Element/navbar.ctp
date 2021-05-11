<?php
/**
 * @var \App\View\AppView $this
 * @var string $logoutUrl
 * @var string $loginUrl
 */

$session = $this->request->getSession();
$isAdminPage = $this->request->getParam('prefix') === 'admin';
/** @var \Bootstrap\View\Helper\NavbarHelper $nav */
$nav = $this->Navbar;

//echo $nav->create($isAdminPage ? 'ZSB-Portal (Admin)' : 'Portal uniKIK Schulprojekte', ['inverse' => $isAdminPage, 'responsive' => true]);
$luhLogoText = 'Zur zentralen Website der Leibniz Universität Hannover';
echo $nav->create(['name' => $this->Html->image('luh_logo.jpg', ['alt' => $luhLogoText, 'title' => $luhLogoText]), 'url' => "https://uni-hannover.de"], [ 'inverse' => $isAdminPage, 'responsive' => true, 'fluid' => true]);

echo $this->element('top-menu');

?>
<div class="navbar-right">
<?php
if ($session->check('Auth.User')) { //is logged in
    echo $nav->text("Angemeldet als <b>{$session->read('Auth.User.username')}</b>");
    echo $this->Html->link('Abmelden', $logoutUrl, ['class' => 'btn btn-default navbar-btn']); //for some reason neither $nav->button() nor $nav->link fit here...
    if (in_array('ADMIN', $session->read('Auth.User.rights'))) {
        echo ' '; //a space here makes this better looking...
        if (!$isAdminPage) {
            echo $this->Html->link('Admin-Bereich',
                ['controller' => 'Pages', 'action' => 'display', 'prefix' => 'admin', 'home'],
                ['class' => 'btn btn-danger navbar-btn']);
        } else {
            echo $this->Html->link('Zur Startseite',
                ['controller' => 'Pages', 'action' => 'display', 'prefix' => false, 'home'],
                ['class' => 'btn btn-info navbar-btn']);
        }
    }
    if ($session->check('login_as.old_uid')) { //impersonation active
        echo ' ';
        echo $this->Html->link($this->Html->icon('transfer') . ' Zurück als <b>' . h($session->read('login_as.old_username')) . '</b>', ['controller' => 'Users', 'action' => 'revertLoginAs', 'prefix' => false], ['class' => 'btn btn-warning navbar-btn', 'escape' => false]);
    }
} else { //not logged in
    echo $this->Html->link('Anmelden', $loginUrl, ['class' => 'btn btn-default navbar-btn']);
}
?>
</div>
<?php
echo $nav->end();
