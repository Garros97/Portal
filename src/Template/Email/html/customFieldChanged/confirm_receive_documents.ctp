<?php
//IMPORTANT: Keep in sync with TEXT version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \App\Model\Entity\Project $project
 * @var string $fieldName
 * @var string $newFieldValue
 */
$this->assign('title', $project->name . ' - Anmeldebestätigung erhalten');
?>
<b><?= h("{$user->greeting} {$user->full_name},") ?></b>
<br><br>
Ihre Unterlagen für das Projekt "<?= $project->name ?>" sind bei uns eingegangen.
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
