<?php
/** @var \Cake\View\View $this */
/** @var \App\Model\Entity\Registration $registration */

$this->set('title', $registration->project->name.' - Anmeldung zum Losverfahren');
$this->set('showReturnPage', false);
$this->extend('confirmations/normal');
?>