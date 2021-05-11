<?php
//IMPORTANT: Keep in sync with HTML version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', h("Anmeldebestätigung - {$registration->project->name}"))
?>
<?= h("{$user->greeting} {$user->full_name},") ?>

Sie haben sich erfolgreich für das Projekt "<?= h($registration->project->name) ?>" angemeldet.
Die Anmeldebestätigung mit allen weiteren Informationen finden Sie im Anhang.

Mit freundlichen Grüßen
das Team uniKIK Schulprojekte
