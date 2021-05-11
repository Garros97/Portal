<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \App\Model\Entity\Project $project
 * @var string $fieldName
 * @var string $newFieldValue
 */
$this->assign('title', $project->name . ' - Bezahlung eingegangen');
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Ihre Bezahlung in dem Projekt "<?= $project->name ?>" ist bei uns eingegangen.

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
