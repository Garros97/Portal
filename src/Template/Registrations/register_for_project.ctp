<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project $project */
/** @var \App\Model\Entity\Registration $registration */
/** @var \Cake\ORM\Query $groups */
/** @var \App\Model\Entity\User $user */

$this->extend('/Common/page');
$this->assign('title', 'Anmeldung für ' . h($project->name));

$base_text = '<b>Achtung:</b> Wählen Sie bitte <b>%s Modul%s</b> aus.';
$str = '';
$pl = ($project->min_course_count > 1 && $project->max_course_count > 1) ? 'e' : '';
if ($project->max_course_count == 0) { // only minimum course count is set
    $str = sprintf("mindestens %d", $project->min_course_count);
} else if ($project->min_course_count < $project->max_course_count && $project->min_course_count > 0) { // range
    $pl = "e";
    $str = sprintf("zwischen %d und %d", $project->min_course_count, $project->max_course_count);
} else if ($project->min_course_count == $project->max_course_count && $project->min_course_count > 0) { // fixed course count
    $str = sprintf("%d", $project->min_course_count);
} else {
    $base_text = '';
}
$course_restriction_text = sprintf($base_text, $str, $pl);

?>
<?= $this->Form->create($registration, ['horizontal' => true]) ?>
<h2>Informationen</h2>
<p>
    <?= str_replace("%REG_ID%","[Anmeldenummer]", $project->long_description); /* no h() here, html is intended and considered safe */ ?>
</p>
<?php if (strip_tags($project->registration_note) !== ''): ?>
    <h2>Hinweise</h2>
    <?= $project->registration_note ?>
<?php endif; ?>
<?php
if(count($project->custom_fields)): ?>
    <h2>Angaben zur Anmeldung</h2>
    <p>Bitte geben Sie die folgenden Informationen an:</p>
    <?= $this->element('custom-fields-form', ['customFields' => $project->custom_fields]); ?>
<?php endif; ?>
<?php if($project->requiresGroupRegistration()): ?>
    <h2>Gruppe auswählen</h2>
    <p>
        In diesem Projekt ist eine Teilnahne in Gruppen vorgesehen. Eine Gruppe muss dabei aus <?= $project->min_group_size ?> - <?= $project->max_group_size ?> Teilnehmern
        bestehen. (Eventuelle Betreuungslehrer zählen nicht dazu). Sie können eine neue Gruppe gründen, oder eine bestehenden beitreten. Wenn Sie einer Gruppe beitreten möchten
        müssen Sie das Gruppenpasswort kennen. Die anderen Gruppenmitglieder können das Gruppenpasswort in den Mitgliedschafteinstellungen sehen und/oder ändern.
    </p>
    <?php if ($user->is_teacher): ?>
        <p><b>Hinweis:</b> Als Lehrer haben Sie später die Möglichkeit, weitere Gruppen zu erstellen, bzw. weiteren Gruppen beizutreten.</p>
    <?php endif; ?>
    <?= $this->element('group-selector') ?>
<?php endif; ?>
<h2>Wählbare Module</h2>
<div>
    Bitte wählen Sie aus den zur Verfügung stehenden Modulen die, an denen Sie teilnehmen möchten. <?= $course_restriction_text ?>
    <?php
    $checkedCourses = $this->request->getData('courses._ids');
    if ($checkedCourses === null || $checkedCourses === '') //the latter might happen if no course is selected
        $checkedCourses = [];
    ?>
    <?= $this->element('course-selector', ['courses' => $project->courses, 'checkedCourses' => $checkedCourses, 'hideFreeSlots' => $project->getTagValue('hideFreeSlots')]) ?>
</div>
<?= $this->Form->button('Absenden', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?php
/**
 * When a project has a tag "conditional-cf:[a]-[b]", the field with *position* (not ID, zero-based!) [b] will
 * only be visible, when the filed with position [a] is checked. (a should be a checkbox and b should
 * not be required :) ). The filed is only hidden in the frontend via JS.
 * This has been extended to also accept any count of pairs as in "conditional-cf:[a]-[b],[c]-[d],[...]"
 */
if ($project->hasTag('conditional-cf')) {
    $pairs = explode(',', $project->getTagValue('conditional-cf'));
    foreach ($pairs as $pair) {
        list($togglerId, $toggledId) = explode('-', $pair);
        echo $this->Html->scriptBlock(<<<JS
$(function() {
    "use strict";
    $('#custom-fields-$togglerId-joindata-value').on('change', function() {
        $('#custom-fields-$toggledId-joindata-value').parent().parent().toggle(this.checked);
    });
    $('#custom-fields-$toggledId-joindata-value').parent().parent().hide();
});
JS
        , ['block' => true]);
    }
}
?>