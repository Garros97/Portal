<?php
/** @var \App\View\AppView $this */

$this->assign('title', 'Admin-Bereich');
?>

<div class="jumbotron">
    <h1>Portal der <b>uniKIK Schulprojekte</b></h1>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <?php if(in_array('MANAGE_PROJECTS', $this->request->getSession()->read('Auth.User.rights'))): ?>
                <h3>Aktuelle Projekte</h3>
                <div class="list-group">
                    <?php
                    $projectsTable = \Cake\ORM\TableRegistry::get('Projects');
                    foreach($projectsTable->find()->where(['register_end >=' => new \Cake\I18n\Date('-1 week')]) as $project):
                    ?>
                        <a class="list-group-item" href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'edit', $project->id]) ?>"><?= h($project->name) ?></a>
                    <?php endforeach; ?>
                    <a class="list-group-item" href="<?= $this->Url->build(['controller' => 'Projects', 'action' => 'index']) ?>"><i>... alle Projekte</i></a>
                </div>
            <?php else: ?>
                <h3><?= $this->Html->icon('lock') ?> Beschränkter Account</h3>
                <p>
                    Ihr Account wurde auf die Verwaltung einiger spezieller Projekte eingeschränkt. Sie können daher nicht auf auf alle
                    Funktionen des Portals zugreifen. Sollten Ihnen Zugriffsrechte fehlen, <?= $this->Html->link('melden Sie bitte bei uns', ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'imprint'])?>!
                </p>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php if (file_exists(ROOT . DS . 'motd.txt')): ?>
                <h3>Aktuelle Hinweise</h3>
                <pre><?= h(file_get_contents(ROOT . DS . 'motd.txt')) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>
-->
<?php
$this->Html->scriptBlock(<<<'JS'
(function(){
    "use strict";
    $('#quicksearch-box').attr('placeholder', function(i, v) {
        var lbl = this.accessKeyLabel;
        if (lbl) {
            return v + ' (' + lbl + ')';
        }
        return v;
    });
}());
JS
    , ['block' => true]);
?>
