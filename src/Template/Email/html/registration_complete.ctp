<?php
//IMPORTANT: Keep in sync with TEXT version!
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', h("Anmeldebestätigung - {$registration->project->name}"))
?>
<b><?= h("{$user->greeting} {$user->full_name},") ?></b>
<br><br>
Sie haben sich erfolgreich für das Projekt "<?= h($registration->project->name) ?>" angemeldet.
Die Anmeldebestätigung mit allen weiteren Informationen finden Sie im Anhang.
<br><br>
Mit freundlichen Grüßen<br>
das Team uniKIK Schulprojekte
