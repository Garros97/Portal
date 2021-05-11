<?php
//IMPORTANT: Keep in sync with TEXT version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Course $course
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', h("Von der Warteliste aufgerückt: {$course->name}"))
?>
<b><?= h("{$user->greeting} {$user->full_name},") ?></b>
<br><br>
Sie sind in dem Kurs "<?= h($course->name) ?>" in dem Projekt "<?= h($course->project->name) ?>" von der
Warteliste aufgerückt. <b>Sie nehmen jetzt an dem Kurs teil.</b>
<?php if ($course->hasTag('infoAfterReg')): ?>
    <br><br>
    Informationen zu diesem Kurs: <?= h($course->getTagValue('infoAfterReg')) ?>
<?php endif; ?>
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
