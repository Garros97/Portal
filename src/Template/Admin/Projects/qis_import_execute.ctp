<?php
use Cake\Core\Configure;

/** @var \App\View\AppView $this */
/** @var array $newCourses */
/** @var array $deletedCourses */
/** @var \App\Model\Entity\Project $project */

$this->extend('/Common/page');
$this->assign('title', "QIS-Import <small><em>{$project->name}</em> &mdash; <em>{$filename}</em></small>");
?>

<h2>Neue Kurse</h2>
<p>Diese Kurse sind in der Datei, aber nicht im Projekt vorhanden. Ein Haken vor dem Kurs importiert diesen Kurs.</p>
<?php
echo $this->Form->create(null);
foreach($newCourses as $id => $name) {
    echo $this->Form->control("new.$id", [
        'label' => $name,
        'type' => 'checkbox',
        'checked' => true,
    ]);
}
?>
<h2>Gelöschte Kurse</h2>
<p>Diese Kurse sind im Projekt, nicht aber in der Datei vorhanden. Ein Haken vor dem Kurs <em>löscht</em> diesen Kurs (Nur möglich, wenn Kurs leer).</p><?php //TODO: Überprüfung auf leer einbauen ?>
<?php
foreach($deletedCourses as $id => $name) {
    echo $this->Form->control("old.$id", [
        'label' => $name,
        'type' => 'checkbox',
        'checked' => false,
    ]);
}
echo $this->Form->button('Ausführen', ['bootstrap-type' => 'primary']);
echo $this->Form->end();
?>
