<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Course $course
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', h("Von der Warteliste aufgerückt: {$course->name}"))
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Sie sind in dem Kurs "<?= h($course->name) ?>" in dem Projekt "<?= h($course->project->name) ?>" von der
Warteliste aufgerückt. Sie nehmen jetzt an dem Kurs teil.
<?php if ($course->hasTag('infoAfterReg')): ?>

Informationen zu diesem Kurs: <?= h($course->getTagValue('infoAfterReg')) ?>
<?php endif; ?>

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
