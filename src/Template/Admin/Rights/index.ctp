<?php
/** @var \App\Model\Entity\Right[] $rights */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
$this->assign('title', 'Rechte Übersicht');
?>

<?= $this->Modal->create('Hilfe zu Rechten', ['id' => 'helpRightsModal', 'size' => 'large']) ?>
<p>
    Dies ist eine List aller im System verfügbaren Rechte. Klicken Sie auf <?= $this->Html->icon('list') ?> neben einem
    Recht, um alle Accounts zu sehen, die über dieses Recht verfügen.
</p>
<p>
    Ein Account kann ein Recht entweder uneingeschränkt besitzen, oder aber mit Einschränkung auf einige "Subresourcen". So
    kann ein Account der das Recht <em>RATE</em> uneingeschränkt besitzt beispielweise alle Projekte bewerten. Ein Account, der
    dieses Recht mit der Einschränkung auf die Subresourcen 4, 7 und 8 besitzt, kann nur die Projekte mit den IDs 4, 7 und 8
    bewerten. Was genau die Subresourcen darstellen ist abhänhängig von dem jeweiligen Recht (Projekt-IDs, Account-IDs, etc.)
</p>
<p>
    Ein Account, der das Recht <em>GRANT_RIGHTS</em> hat, kann seine Rechte (oder einen Teil davon), an andere Nutzer weitergeben.
    Wenn ein Account über ein uneingeschränktes Recht verfügt kann er optional auch nur ein Recht mit Einschränkung weitergeben.
    Auch das Recht <em>GRANT_RIGHTS</em> kann weitergegeben werden, um dem Account zu erlauben, seine Rechte selber weiter zu
    geben. Auf diese Weise kann Arbeit delegiert werden.
</p>
<p>
    <b>Beispiel</b>: Peter ist Administrator und legt zwei neue Projekte mit den IDs 5 und 6 an. Marie ist für die Organisation verantwortlich,
    daher gibt er Marie die Rechte <em>MANAGE_PROJECTS</em> und <em>RATE</em> mit der Einschränkung auf die IDs 5 und 6. Außerdem
    gibt er Marie das Recht <em>GRANT_RIGHTS</em>. Marie erledigt die Bewertung nicht selber, sondern überlässt das Klaus und Anna.
    Klaus ist für die Bewertung von Projekt 5 zuständig, Anna für Projekt 6. Marie gibt nun Klaus das Recht <em>RATE</em> mit der
    Einschränkung auf Projekt 5 und Anna mit Einschränkung auf Projekt 6. Marie konnte ihre Rechte weitergeben, da sie das Recht
    <em>GRANT_RIGHTS</em> hatte. Anna und Klaus können ihre Rechte nicht weitergeben.
</p>
<p>
    Besonderheiten:
    <ul>
    <li>Das Recht <em>SUPERADMIN</em> kann nicht weitergegeben werden.</li>
    <li>Ein Account mit dem Recht <em>SUPERADMIN</em> kann jedem Account (auch sich selbst) jederzeit jedes Recht geben.</li>
    <li>Nur Accounts mit dem Recht <em>REVOKE_RIGHTS</em> können Accounts Rechte wieder entziehen.</li>
    <li>Einige Rechte können nur uneingeschränkt verteilt werden.</li>
    </ul>
</p>
<?= $this->Modal->end() ?>

<?php
$this->start('actions');
//trigger is wired up via JS, does not for using data attributes for some reason
echo $this->Chell->actionLink('question-sign', 'Hilfe zu Rechten', '', ['id' => 'helpRightsModalLink']);
$this->end();
?>

<table class="table table-striped">
<thead>
    <tr>
        <th><?= $this->Paginator->sort('id', 'ID') ?></th>
        <th><?= $this->Paginator->sort('name', 'Name') ?></th>
        <th><?= $this->Paginator->sort('description', 'Beschreibung') ?></th>
        <th><?= $this->Paginator->sort('suports_subresources', 'Einschränkbar?') ?></th>
        <th class="actions">Aktionen</th>
    </tr>
</thead>
<tbody>
<?php foreach ($rights as $right): ?>
    <tr>
        <td><?= $right->id ?></td>
        <td><?= h($right->name) ?></td>
        <td><?= h($right->description) ?></td>
        <td><?= $right->supports_subresources ? $this->Html->icon('ok') : '' ?><span class="sr-only"><?= $right->supports_subresources ? 'Ja' : 'Nein' ?></span></td>
        <td class="actions">
            <?= $this->Html->link($this->Html->icon('list'), ['controller' => 'Users', 'action' => 'list_users_with_right', $right->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Accounts auflisten']) ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>

<?php
$this->Html->scriptBlock(<<<'JS'
    $(function() {
        "use strict";
        $('#helpRightsModalLink').on('click', function(e) {
            e.preventDefault();
            $('#helpRightsModal').modal('toggle');
        });
    });
JS
    , ['block' => true]);
?>
