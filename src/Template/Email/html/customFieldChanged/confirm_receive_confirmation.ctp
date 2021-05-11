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
Ihre Anmeldebestätigung für das Projekt "<?= $project->name ?>" ist bei uns eingegangen.
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
