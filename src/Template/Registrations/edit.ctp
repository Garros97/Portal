<?php
use App\Model\Entity\CustomFieldType;

/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Registration $registration */
/** @var \App\Model\Entity\Group[] $groups */
/** @var \App\Model\Entity\User $user */
/** @var \Cake\Database\Query $groupsInProject */
/** @var \Cake\ORM\Entity $groupData */

$this->extend('/Common/page');
$this->assign('title', 'Ihre Anmeldung <small>' . h($registration->project->name) . '</small>');
?>
<?php if (!$registration->project->reg_data_hidden): ?>
<p>Sie sind aktuell für dieses Projekt angemeldet. Auf dieser Seite können Sie Ihre Angaben zur Anmeldung einsehen. Sie haben sich am <?= $registration->created ?> angemeldet.</p>

<h2>Angemeldete Module</h2>
<p>Sie sind aktuell für folgende Module angemeldet:</p>
<table class="table table-striped table-hover">
    <thead>
        <?= $this->Html->tableHeaders(['Name', 'Status', 'Aktionen', 'Anmeldedatum']) ?>
    </thead>
    <tbody>
        <?php foreach($registration->courses as $course) : ?>
        <tr>
            <td><?= h($course->name) ?></td>
            <td><?= ($course->isListPosOnWaitingList($course->list_pos)) ? '<span class="text-warning">Warteliste</span>' : 'Angemeldet' ?></td>
            <td>
				<?php $hasActions = false; if ($course->uploads_allowed): $hasActions = true; ?>
					<a class="btn btn-default btn-sm" href="<?= $this->Url->build(['action' => 'uploadFiles', $registration->id, $course->id]) ?>"><?= $this->Html->icon('upload') ?> Dateien hochladen</a>
				<?php endif; ?>
                <?php if (count($course->scales)): $hasActions = true; ?>
                    <!--<a class="btn btn-default btn-sm" href="<?= $this->Url->build(['action' => 'showRatings', $registration->id, $course->id]) ?>"><?= $this->Html->icon('check') ?> Bewertungen anzeigen</a>-->
				<?php endif; ?>
                <?php if (!$hasActions): ?>
                    <span class="text-muted">(keine Aktionen verfügbar)</span>
                <?php endif; ?>
            </td>
            <td><?= $course->_joinData->created ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (count($registration->custom_fields)): ?>
<h2>Weitere Angaben zur Anmeldung</h2>
<p>Hier sehen Sie alle weiteren Angaben, die Sie während der Anmeldung getätigt haben, sowie eventuelle Informationen die wir mit Ihrer Anmeldung verwalten.</p>
<table class="table table-striped table-hover">
    <thead>
    <?= $this->Html->tableHeaders(['Name', 'Wert']) ?>
    </thead>
    <tbody>
    <?php foreach($registration->custom_fields as $field) : ?>
        <tr>
            <td><?= h($field->name) ?></td>
            <td>
                <?php
                if (in_array($field->type, [CustomFieldType::AgbCheckbox, CustomFieldType::Checkbox])) {
                    echo $field->_joinData->value == '1' ? $this->Html->icon('check') . '<span class="sr-only">Ausgewählt</span>' : $this->Html->icon('unchecked') . '<span class="sr-only">Nicht ausgewählt</span>';
                } else {
                    echo h($field->_joinData->value);
                }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p>Wenn eine dieser Angaben geändert werden soll, <?= $this->Html->link('kontaktieren Sie uns bitte', ['controller' => 'Pages', 'action' => 'display', 'imprint']) ?>.</p>
<?php endif; ?>

<?php if ($registration->project->requiresGroupRegistration()): ?>
    <h2>Gruppen</h2>
    <?php if($user->is_teacher): //teachers can have more than one group, normal users not ?>
        <table class="table table-striped table-hover">
            <thead>
            <?= $this->Html->tableHeaders(['Name', 'Teilnehmer', 'Status Teilnehmer', 'Passwort']) ?>
            </thead>
            <tbody>
            <?php foreach($groups as $group): ?>
                <?php
                $usersString = join(', ', collection($group->users)->extract(function($user) {
                    $muted = $user->is_teacher ? 'text-muted' : '';
                    return "<span class='$muted'>{$user->full_name} (<i>{$user->username}</i>)</span>";
                })->toArray());
                $userCount = $group->getNonTeacherMemberCount();
                $statusString = "OK";
                if ($userCount > $registration->project->max_group_size) {
                    $statusString = "Zu viele! Max. <b>{$registration->project->max_group_size}</b>";
                }
                if ($userCount < $registration->project->min_group_size) {
                    $statusString = "Zu wenige! Min. <b>{$registration->project->min_group_size}</b>";
                }
                if ($statusString === "OK") {
                    $statusString = "<span class='label label-success'>{$statusString}</span>";
                } else {
                    $statusString = "<span class='label label-warning'>{$statusString}</span>";
                }
                ?>
                <tr>
                    <td><?= $group->name ?></td>
                    <td><?= $usersString ?></td>
                    <td><?= $statusString ?></td>
                    <td><code><?= $group->password ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>Das Gruppenpasswort wird benötigt, um sich in einer Gruppe anzumelden.</p>
        <h3>Gruppe beitreten</h3>
        <p>Als Lehrer können Sie in mehr als einer Gruppe sein. Wenn Sie einer weiteren Gruppe beitreten wollen, können Sie das hier tun. Sie können auch
        eine neue Gruppe erstellen.</p>
        <?= $this->Form->create($groupData, ['context' => ['table' => 'Groups']]) ?>
        <?= $this->element('group-selector', ['groups' => $groupsInProject]) ?>
        <?= $this->Form->button('Gruppe beitreten/erstellen', ['bootstrap-type' => 'primary', 'name' => 'submitButton', 'value' => 'changeGroups']) ?>
        <?= $this->Form->end() ?>
        <p>Wenn Sie Ihre Gruppe(n) wechseln wollen, melden Sie sich bitte bei uns.</p>
    <?php else: ?>
        <?php
        $group = $groups->first(); //the one and only group
        if ($group != null):
            $usersString = join(', ', collection($group->users)->extract(function($user) {
                $muted = $user->is_teacher ? 'text-muted' : '';
                return "<span class='$muted'>{$user->full_name} (<i>{$user->username}</i>)</span>";
            })->toArray());
            $userCount = $group->getNonTeacherMemberCount();
            ?>
            <p>Sie sind in der Gruppe <b><?= $group->name ?></b> angemeldet. Mitglieder Ihrer Gruppe sind <b><?= $usersString ?></b>.</p>

            <?php if ($userCount > $registration->project->max_group_size): ?>
                <div class="alert alert-warning" role="alert">
                    <b>Achtung:</b> In Ihrer Gruppe sind zu viele Teilnehmer! Ihre Gruppe hat <b><?= $userCount ?></b> Teilnehmer, erlaubt sind maximal <b><?= $registration->project->max_group_size ?></b>.
                </div>
            <?php elseif ($userCount < $registration->project->min_group_size): ?>
                <div class="alert alert-warning" role="alert">
                    <b>Achtung:</b> In Ihrer Gruppe sind zu wenige Teilnehmer! Ihre Gruppe hat <b><?= $userCount ?></b> Teilnehmer, benötigt werden aber mindestens <b><?= $registration->project->min_group_size ?></b>.
                </div>
            <?php endif; ?>

            <p>Das Passwort Ihrer Gruppe lautet <code><?= $group->password ?></code>. Dieses Passwort wird benötigt, um sich in diese Gruppe anzumelden.</p>
            <p>Wenn Sie Ihre Gruppe wechseln wollen, melden Sie sich bitte bei uns.</p>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                <b>Achtung:</b> Sie sind in keiner Gruppe angemeldet, für dieses Projekt ist aber nur eine Teilnahme in Gruppen vorgesehen. Hierbei handelt es sich scheinbar um einen Fehler, bitte
                melden Sie sich bei <a href="mailto:dev@unikik.de">dev@unikik.de</a>.
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<h2>Modulwahl ändern</h2>
<?php if($registration->userMayUnregister()): //TODO: Is this the correct timeframe? ?>
    <p>Sie können Ihre Modulwahl an dieser Stelle noch bis zum <?= $registration->unregister_end_date ?> ändern.</p>
    <?= $this->Form->create($registration) ?>
    <?php $checkedCourses = (array)$this->Form->context()->val('courses._ids'); ?>
    <?= $this->element('course-selector', ['courses' => $registration->project->courses, 'checkedCourses' => $checkedCourses, 'hideFreeSlots' => $registration->project->getTagValue('hideFreeSlots')]) ?>
    <?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary', 'name' => 'submitButton', 'value' => 'changeCourses']) ?>
    <?= $this->Form->end() ?>
<?php else: ?>
    <p>Sie können Ihre Modulwahl seit dem <?= $registration->unregister_end_date ?> nicht mehr ändern.</p>
<?php endif; ?>

<h2>Weitere Optionen</h2>
<div style="padding-bottom: 1em">
    <a class="btn btn-default" href="<?= $this->Url->build(['_ext' => 'pdf', 'action' => 'getConfirmation', $registration->id]) ?>" role="button" target="_blank"><?= $this->Html->icon('file') ?>&nbsp;Anmeldebestätigung generieren</i></a>
    <?php if($registration->userMayUnregister()): ?>
    <?= $this->Form->postButton($this->Html->icon('remove') . '&nbsp;Aus Projekt austragen',  ['action' => 'delete', $registration->id], [
        'form' => [
            'onsubmit' => 'return confirm("Wollen Sie sich wirklich aus diesem Projekt austragen? Diese Aktion kann nicht rückgängig gemacht werden!")',
            'style' => 'display: inline'
        ],
        'class' => 'btn btn-default',
        'style' => 'margin-bottom: 0',
        'method' => 'delete'
    ]) ?>
    <?php endif; ?>
</div>
<?php else: ?>
    <p>Sie sind aktuell für dieses Projekt angemeldet.<br><b>Hinweis:</b> Dieses Projekt wird derzeit administrativ verwaltet, bitte versuchen Sie es zu einem späteren Zeitpunkt erneut.</p>
<?php endif; ?>
