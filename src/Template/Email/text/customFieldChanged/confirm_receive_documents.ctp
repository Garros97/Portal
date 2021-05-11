<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \App\Model\Entity\Project $project
 * @var string $fieldName
 * @var string $newFieldValue
 */
$this->assign('title', $project->name . ' - Anmeldebestätigung erhalten');
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Ihre Unterlagen für das Projekt "<?= $project->name ?>" sind bei uns eingegangen.

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
