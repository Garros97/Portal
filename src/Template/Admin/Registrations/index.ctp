<?php
/** @var \App\Model\Entity\Registration[] $registrations */
/** @var \App\Model\Entity\Project $project */
/** @var \App\Model\Entity\Course $course */
/** @var \App\Model\Entity\Course[] $courses */
/** @var string[][] $emails */
$this->extend('/Common/edit');
?>

<?php
$isSingleCourse = $course !== null;
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $project->id]);
$courseId = null;
if ($isSingleCourse) {
    echo $this->Chell->actionLink('home', 'Zum Kurs', ['controller' => 'Courses', 'action' => 'edit', $course->id]);
    $courseId = $course->id;
}
// pass filename as separate argument to controller so the files are easier to recognize when downloaded; request data will not appear in filename
$requestData = array_merge(['urlname' => $project->urlname], $courseId == null ? [] : ['cid' => $courseId]);
$fileName = $project->urlname . ($courseId == null ? '' : '_'.$courseId);
echo $this->Chell->actionLink('list', 'Als XLS-Datei', ['controller' => 'exports', 'action' => 'participants', '_ext' => 'xls', '?' => $requestData, $fileName]);
echo $this->Chell->actionLink('list-alt', 'Als XLSX-Datei', ['controller' => 'exports', 'action' => 'participants', '_ext' => 'xlsx', '?' => $requestData, $fileName]);
echo $this->Chell->actionLink('file', 'Als CSV-Datei', ['controller' => 'exports', 'action' => 'participants', '_ext' => 'csv', '?' => $requestData, $fileName]);
echo $this->Chell->actionLink('eye-open', 'Im Browser ansehen', ['controller' => 'exports', 'action' => 'participants', '_ext' => 'html', '?' => $requestData], ['target' => '_blank']);
$this->end();
?>

<h2>Teilnahmeliste <small><?= h($project->name) ?></small></h2>
<p>
    <?= $this->Form->create(null, ['horizontal' => true]); ?>
    <?= $this->Form->control('course-filter', [ //needs JS
        'id' => 'course-filter',
        'label' => 'Gewählter Kurs',
        'options' => [0 => '(Alle Kurse)'] + $courses,
        'val' => $isSingleCourse ? $course->id : 0
    ]); ?>
    <?= $this->Form->end(); ?>
</p>
<table class="table table-striped table-hover">
<thead>
    <tr>
        <?php
        if (!$isSingleCourse) {
            $makeHeader = function ($name, $label) {
                return $this->Paginator->sort($name, $label);
            };
        } else {
            $makeHeader = function ($name, $label) {
                return $label;
            };
        }
        ?>
        <?php if ($isSingleCourse) : ?>
            <th>#</th>
        <?php endif; ?>
        <th><?= $makeHeader('id', 'RID') ?></th>
        <th><?= $makeHeader('Users.username', 'Accountname') ?></th>
        <th><?= $makeHeader('Users.email', 'E-Mail') ?></th>
        <th><?= $makeHeader('Users.first_name', 'Vorname') ?></th>
        <th><?= $makeHeader('Users.last_name', 'Nachname') ?></th>
        <?php if (!$isSingleCourse) : ?>
            <th><?= $this->Paginator->sort('created', 'Anmeldedatum Projekt')?></th>
        <?php else: ?>
            <th>Anmeldedatum Kurs <?= $this->Html->icon('menu-down') ?></th>
        <?php endif; ?>
        <th class="actions">Aktionen</th>
    </tr>
</thead>
<tbody>
<?php
    $lastOnWaitingList = false;
    foreach ($registrations as $registration):
    if ($isSingleCourse) {
        $course = $registration->_matchingData['Courses'];
        $currOnWaitingList = $course->isListPosOnWaitingList($course->list_pos);
        if (!$lastOnWaitingList && $currOnWaitingList) { //waiting list begins here
            echo '<tr class="table-separator-waiting-list"><td colspan="8"><span>'.$this->Html->icon('menu-down').'&nbsp;Warteliste&nbsp;'.$this->Html->icon('menu-down').'</span></td></tr>';
        }
        $lastOnWaitingList = $currOnWaitingList;
    }
?>
    <tr>
        <?php if ($isSingleCourse) : ?>
            <td><?= $this->Number->ordinal($course->list_pos) ?></td>
        <?php endif; ?>
        <td><?= $registration->id ?></td>
        <td><?= h($registration->user->username) ?></td>
        <td><?= h($registration->user->email) ?></td>
        <td><?= h($registration->user->first_name) ?></td>
        <td><?= h($registration->user->last_name) ?></td>
        <td><?= $isSingleCourse ? $registration->_matchingData['CoursesRegistrations']->created->nice() : $registration->created->nice() ?></td>
        <td class="actions">
            <?= $this->Html->link($this->Html->icon('eye-open'), ['action' => 'edit', $registration->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Anzeigen']) ?>
            <?= $this->Html->link($this->Html->icon('user'), ['controller' => 'Users', 'action' => 'edit', $registration->user->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Zum Account']) ?>
			<?= $this->Form->postLink($this->Html->icon('remove'), ['controller' => 'Registrations', 'action' => 'delete', $registration->id],
			['confirm' => 'Wollen Sie diese Registrierung wirklich löschen? Dadurch wird der Teilnehmende von diesem Projekt abgemeldet.', 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Teilnehmenden abmelden']) ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>

<?php
if (!array_key_exists('registered', $emails)) $emails['registered'] = [];
if (!array_key_exists('waitingList', $emails)) $emails['waitingList'] = [];
$mailsNoWaitingList = join(';', $emails['registered']);
$mailsWaitingList = join(';', $emails['waitingList']);
$mails = join(';', array_merge($emails['registered'], $emails['waitingList']));
?>
<h4>E-Mail</h4>
<div class="well well-sm
">
    <?= $this->Html->link($this->Html->icon('send') . '&nbsp;Mail an alle <b>mit Warteliste</b> (BCC)', "mailto:?bcc=$mails", ['class' => 'btn btn-default', 'escape' => false]) ?>
    <?= $this->Html->link($this->Html->icon('send') . '&nbsp;Mail an alle <b>ohne Warteliste</b> (BCC)', "mailto:?bcc=$mailsNoWaitingList", ['class' => 'btn btn-default', 'escape' => false]) ?>
    <?= $this->Html->link($this->Html->icon('send') . '&nbsp;Mail an Warteliste (BCC)', "mailto:?bcc=$mailsWaitingList", ['class' => 'btn btn-default', 'escape' => false]) ?>
</div>

<?php
$this->Html->scriptBlock(<<<JS
    $(function() {
        "use strict";
        $('#course-filter').on('change', function() {
            var id = $(this).val();
            var url = '{$this->Url->build([$project->id])}';
            if (id != 0) {
                 url += '/' + id; //this depends on current routing situation!
            }
            window.location.href = url;
        });
    });
JS
    , ['block' => true]);
?>

