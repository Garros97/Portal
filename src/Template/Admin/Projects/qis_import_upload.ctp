<?php
use Cake\Core\Configure;
use Cake\Filesystem\Folder;

/** @var \App\View\AppView $this */
/** @var int $id */
/** @var \App\Form\QisUploadForm $uploadForm */

$this->extend('/Common/page');
$this->assign('title', 'QIS-Import');
?>

<h2>Beschreibung</h2>
<p>
    Um Kurse aus dem QIS in das Portal zu importieren, muss zunächst eine XML-Datei mit den entsprechenden Kurses aus dem Vorlesungsverzeichnis exportiert werden.
    Dazu mit einem Account mit den entsprechenden Rechten im QIS anmelden, und den Button "PDF" für zu exportierenden Vorlesungen (typischerweise Juniorstudium) wählen.
    <strong>Hinweis:</strong> Bitte nicht das <em>gesamte</em> Vorlesungsverzeichnis exportieren!
</p>
<?= $this->Html->image('qis-import-1.png', ['alt' => 'Schritt 1 des QIS-Imports', 'class' => 'img-responsive center-block']) ?>
<p>
    Im nächsten Dialog dann das Format "XML" und auch die Druckvorlage "Uni Hannover (XML)" wählen. Nach einem Klick auf "Druckvorlage verwenden und drucken" wird nach einiger Wartezeit
    eine XML-Datei zum Download angeboten. Diese Datei muss zunächst auf dem Computer gespeichet werden, dazu mit der rechten Maustaste auf den Link klicken und "Speichern unter" auswählen.
</p>
<?= $this->Html->image('qis-import-2.png', ['alt' => 'Schritt 2 des QIS-Imports', 'class' => 'img-responsive center-block']) ?>
<h2>Upload</h2>
<p>
    Im nächsten Schritt muss die eben heruntergeladene Datei mit dem nachfolgenden Formular hochgeladen werden.
</p>
<?= $this->Form->create($uploadForm, ['horizontal' => 'true', 'type' => 'file']); ?>
<?= $this->Form->control('qisfile', [
    'type' => 'file',
    'label' => 'Datei'
]) ?>
<?= $this->Form->button('Upload', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>
<h2>Datei-Auswahl</h2>
<p>
    Wenn der Upload erfolgreich war, sollte die Datei in der folgenden Liste auftauchen. Dort bitte die Datei auswählen:
</p>
<ul>
    <?php foreach((new Folder(ROOT . DS . Configure::read('App.uploads') . DS . 'qis_import'))->find() as $file): ?>
        <li><a href="<?= $this->Url->build(['action' => 'qisImportExecute', $id, $file]) ?>"><?= $file ?></a></li>
    <?php endforeach; ?>
</ul>
