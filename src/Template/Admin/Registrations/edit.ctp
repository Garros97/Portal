<?php
use App\Model\Entity\CustomFieldType;
use Cake\Utility\Hash;

/** @var \App\Model\Entity\Registration $registration */
/** @var \App\View\AppView $this */
/** @var \Cake\Collection\CollectionInterface $tags */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('user', 'Zum Account', ['controller' => 'Users', 'action' => 'edit', $registration->user_id]);
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $registration->project_id]);
echo $this->Chell->actionLink('file', 'Anmelde&shy;bestätigung', ['controller' => 'Registrations', 'action' => 'getConfirmation', 'prefix' => false, '_ext' => 'pdf', $registration->id]);
echo $this->Chell->actionLink('envelope', 'Anmelde&shy;bestätigung erneut versenden', ['controller' => 'Registrations', 'action' => 'resendConfirmation', $registration->id]);
echo $this->Chell->actionLink('trash', 'Account abmelden', ['action' => 'delete', $registration->id], ['confirm' => 'Wollen Sie diese Registrierung wirklich löschen? Dadurch wird der Teilnehmende von diesem Projekt angemeldet.']);
$this->end();

$this->start('info-block');
?>
    <p><b>RID:</b> <?= $registration->id ?></p>
    <p><b>Anmeldedatum:</b><br /><?= $registration->created ?></p>
    <p><b>Projektname:</b><br /><?= $registration->project->name ?></p>
    <p><b>Account:</b><br /><?= $registration->user->fullName ?> (<?= $registration->user->username ?>)</p>
<?php $this->end(); ?>

<div class="page-header"><!-- TODO: Header for more pages (use $this->set()?) -->
    <h2>Registrierung #<?= $registration->id ?> <small><?= $registration->project->name . ' &mdash; ' . $registration->user->username ?></small></h2>
</div>

    <?= $this->Form->create($registration, ['horizontal' => true]); ?>
    <h3>Zusatzfelder</h3>
    <?= $this->element('custom-fields-form', ['customFields' => $registration->custom_fields]); ?>
    <?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>

    <h3>Gewählte Module</h3>
    <?php
    $checkedCourses = $this->request->getData('courses._ids');
    if ($checkedCourses === null || $checkedCourses === '') //the latter might happen is no course is selected
        $checkedCourses = [];
    $checkedCourses += Hash::extract($registration->courses, '{n}.id');
    ?>
    <?= $this->element('course-selector', ['courses' => $registration->project->courses, 'checkedCourses' => $checkedCourses, 'hideFreeSlots' => false]) ?>

    <?= $this->Panel->startGroup(['open' => -1]) ?>
    <?= $this->Panel->create('Weitere Optionen', ['collapsible' => true]) ?>
        <?= $this->element('tag-form', ['tags' => $registration->tags, 'modelPrimaryKey' => $registration->id, 'usedTags' => $tags]) ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->endGroup() ?>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?php if ($registration->project->requiresGroupRegistration()): ?>
<h3>Gruppen</h3>
<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th class="actions">Aktionen</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($registration->user->groups as $group): ?>
        <tr>
            <td><?= $group->id ?></td>
            <td><?= h($group->name) ?></td>
            <td class="actions">
                <?= $this->Html->link($this->Html->icon('pencil'), ['controller' => 'Groups' ,'action' => 'edit', $group->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten']) ?>
                <?= $this->Form->postLink($this->Html->icon('trash'), ['action' => 'delete', $group->id], ['confirm' => sprintf('Gruppe %s wirklich löschen?', $group->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen']) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
