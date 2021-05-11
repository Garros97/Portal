<?php
use App\Model\Entity\CustomField;
use App\Model\Entity\CustomFieldType;
use Cake\Collection\Collection;
use Cake\Core\Configure;
use \Cake\Filesystem\Folder;

/** @var \App\Model\Entity\CustomField $customField */
/** @var \App\Model\Entity\Project $project */
/** @var \App\View\AppView $this */
/** @var \App\Form\FileUploadForm $uploadForm */

$this->extend('/Common/edit');
?>
<!-- Modal for CustomField/add -->
<?= $this->Modal->create('Zusatzfeld hinzufügen', ['id' => 'customFieldAddModal']) ?>
<?= $this->Form->create($customField, ['horizontal' => true, 'url' => ['controller' => 'CustomFields', 'action' => 'add']]); ?>
<fieldset>
    <?php
    echo $this->Form->hidden('project_id', ['value' => $project->id]);
    echo $this->Form->control('name', [
        'label' => 'Name'
    ]);
    echo $this->Form->control('section', [
        'label' => 'Abschnitt'
    ]);
    echo $this->Form->control('type', [
        'label' => 'Typ',
        'options' => [ //looks a bit bad, but other ways are more complicated
            CustomFieldType::Text => CustomField::getTypeName(CustomFieldType::Text),
            CustomFieldType::Checkbox => CustomField::getTypeName(CustomFieldType::Checkbox),
            CustomFieldType::AgbCheckbox => CustomField::getTypeName(CustomFieldType::AgbCheckbox),
            CustomFieldType::Number => CustomField::getTypeName(CustomFieldType::Number),
            CustomFieldType::Dropdown => CustomField::getTypeName(CustomFieldType::Dropdown)
        ]
    ]);
    echo $this->Form->control('backend_only', [
        'label' => 'Nur im Admin-Bereich anzeigen',
        'title' => 'Wenn die Option gewählt wird, wird der Wert nicht bei der Anmeldung abgefragt und ist nur intern sichtbar.'
    ]);
    echo $this->Form->control('combo_box_values', [
        'label' => 'Werte für Auswahlfeld',
        'type' => 'text',
        'title' => 'Wenn der Typ des Feldes "Auswahlfeld" ist, müssen hier die Wahlmöglichkeiten einegetragen werden.
                    Die Werte werden mit einem Komma getrennt. Beispiel: <i>alpha,beta,gamma</i> oder <i>---,Ja,Nein</i>'
    ])
    ?>
</fieldset>
    <p><b>Hinweis:</b> Felder deren Name mit einem Stern (*) endet, müssen bei der Anmeldung ausgefüllt werden. Der Wert
    "---" wird in Auswahlfeldern als "ungültiger Wert" verwendet und darf von Benutzer nicht gewählt werden, wenn das Feld
    als verplichtend gekennzeichnet ist (s.o.)</p>
<?= $this->Modal->end([
    $this->Form->button('Absenden', ['bootstrap-type' => 'primary']),
    $this->Form->button('Abbrechen', ['data-dismiss' => 'modal'])
]) ?>
<?= $this->Form->end() ?>
<!-- end of modal -->

<!-- Modal for uploading confirmation appendix files -->
<?= $this->Modal->create('Anhang hinzufügen', ['id' => 'appendixAddModal']) ?>
<?= $this->Form->create($uploadForm, ['horizontal' => 'true', 'type' => 'file']); ?>
<?= $this->Form->control('file', [
    'type' => 'file',
    'label' => 'Datei'
]) ?>
<?= $this->Modal->end([
    $this->Form->button('Upload', ['bootstrap-type' => 'primary']),
    $this->Form->button('Abbrechen', ['data-dismiss' => 'modal'])
]) ?>
<?= $this->Form->end() ?>
<!-- end of modal -->
<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Alle Projekte', ['action' => 'index']);
if ($project->requiresGroupRegistration()) {
    echo $this->Chell->actionLink('list-alt', 'Gruppenliste', ['controller' => 'Groups', 'action' => 'index', $project->id]);
}
echo $this->Chell->actionLink('user', 'Teilnahmeliste', ['controller' => 'Registrations', 'action' => 'index', $project->id]);
echo $this->Chell->actionLink('trash', 'Projekt löschen', ['action' => 'delete', $project->id], ['confirm' => sprintf('Projekt %d wirklich löschen?', $project->id)]);
echo $this->Chell->actionLink('th', 'Übersicht Ausschlussgruppen', ['action' => 'exgroupsOverview', $project->id]);
echo $this->Chell->actionLink('stats', 'Statistiken', ['controller' => 'Stats', 'action' => 'index', $project->id]);
echo $this->Chell->actionLink('eye-open', 'Anmeldung ansehen', ['prefix' => false, 'controller' => 'Registrations', 'action' => 'registerForProject', $project->urlname], ['target' => '_blank']);
if ($project->hasTag('hasLottery') && $project->getTagValue('hasLottery') == 1) echo $this->Chell->actionLink('random', 'Teilnehmer auslosen', ['action' => 'drawParticipants', $project->id], ['confirm' => 'Teilnehmer wirklich auslosen? Dies kann eine Weile dauern. Eine Sicherungskopie wird automatisch erstellt.']);
if ($project->hasTag('backupOf')) echo $this->Chell->actionLink('floppy-open', 'Backup wiederherstellen', ['action' => 'restore', $project->id, $project->getTagValue('backupOf')], ['confirm' => 'Backup wirklich wiederherstellen? Dies kann eine Weile dauern.']);
$this->end();
$this->start('info-block');
$link = $this->Url->build(['prefix' => false, 'controller' => 'Registrations', 'action' => 'registerForProject', $project->urlname, '_full' => true]);
echo "<b>Anmeldelink:</b> <a target='_blank' href=\"$link\">$link</a>";
$this->end();
?>

<?= $this->Form->create($project, ['horizontal' => true]) ?>
<fieldset>
    <legend>Projekt bearbeiten</legend>
    <?php
    echo $this->Form->control('name', [
        'label' => 'Name'
    ]);
    echo $this->Form->control('urlname', [
        'type' => 'static',
        'label' => 'Kurzname'
    ]);
    ?>
    <div class="form-group">
        <label class="col-md-2 control-label">Registrierungszeit</label>
        <div class="col-md-6">
        <?php
        echo $this->Form->control('register_start', [
            'label' => 'Anfang',
            'type' => 'datePicker',
            'showTime' => true,
        ]);
        echo $this->Form->control('register_end', [
            'label' => 'Ende',
            'type' => 'datePicker',
            'showTime' => true,
        ]);
        ?>
        </div>
    </div>
    <?php
    echo $this->Form->control('visible', [
        'label' => 'Projekt sichtbar'
    ]);
    echo $this->Form->control('reg_data_hidden', [
        'label' => 'Anmeldedaten für Benutzer verstecken'
    ]);
    $logos = (new Collection((new Folder(Folder::addPathElement(WWW_ROOT, ['img', 'logos'])))->find('.*', true)))->map(function($elm){
        return ['value' => $elm, 'text' => $elm, 'data-image' => $this->Url->assetUrl('logos/'. $elm, ['pathPrefix' => Configure::read('App.imageBaseUrl')])];
    });
    echo $this->Form->control('logo_name', [
        'label' => 'Logo',
        'options' => $logos,
        'empty' => '(keines)',
        'data-provide' => 'dropdown'
    ]);
    $this->assign('has-rich-dropdown', true);
    echo $this->Form->control('short_description', [
        'label' => 'Kurzbeschreibung'
    ]);
    echo $this->Form->control('long_description', [
        'label' => 'Infotext (wird bei der Anmeldung und in der Anmeldebestätigung angezeigt)',
        'data-provide' => 'rich-text-editor',
    ]);
    echo $this->Form->control('registration_note', [
        'label' => 'Hinweistext für Anmeldung (wird <i>nur</i> bei der Anmeldung angezeigt)',
        'escape' => false,
        'data-provide' => 'rich-text-editor',
    ]);
    $this->assign('has-rich-text-editor', true);
    ?>
    <!-- <small>unterstützte Platzhalter (%REG_ID% -> [Anmeldenummer]) werden automatisch ersetzt.</small> -->
    <?= $this->Panel->startGroup(['open' => -1]) ?>
    <?= $this->Panel->create('Weitere Optionen', ['collapsible' => true]) ?>
        <?= $this->Form->control('max_unregister_days', [
            'label' => 'Widerrufsfrist',
            'type' => 'number',
            'min' => 0,
            'title' => 'Maximale Anzahl an Tagen nach denen sich ein Teilnehmender noch selbst abmelden kann. Bei 0 ist die Abmeldung unbeschränkt.<br>
                            <b>Hinweis:</b> Ein Abmeldung ist generell nicht möglich wenn die Anmeldefrist abgelaufen ist.',
            'append' => 'Tage'
        ]) ?>
        <div class="form-group">
            <label class="col-md-2 control-label">Gruppengröße</label>
            <div class="col-md-6">
                <?php
                echo $this->Form->control('min_group_size', [
                    'label' => 'Minimum',
                    'min' => '0'
                ]);
                echo $this->Form->control('max_group_size', [
                    'label' => 'Maximum',
                    'min' => '0'
                ]);
                ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">wählbare Kursanzahl</label>
            <div class="col-md-6">
                <?php
                echo $this->Form->control('min_course_count', [
                    'label' => 'Minimum',
                    'min' => '1',
                ]);
                echo $this->Form->control('max_course_count', [
                    'label' => 'Maximum',
                    'min' => '0'
                ]);
                ?>
            </div>
        </div>
        <?php
        echo $this->Form->control('show_register_other_user_link', [
            'label' => 'Link "weiteren Teilnehmenden anmelden" zeigen',
            'title' => 'Wenn aktiviert wird am Ende der Anmeldung ein Link angezeigt, mit dem ein weiterer Teilnehmender angemldet werden kann (mit einem eigenen Account)',
            'type' => 'checkbox'
        ]);
        echo $this->Form->control('enable_mail_on_payment', [
            'label' => 'Teilnehmenden benachrichtigen, wenn <b>Bezahlung</b> eingeht.',
            'title' => 'Wenn diese Funktion aktiviert ist, wird dem Benutzer eine Mail geschickt, wenn ein Zusatzfeld mit dem Namen "Bezahlt" auf den Wert "Ja" geändert wird. (Typ "Auswahlfeld")',
            'type' => 'checkbox',
            'escape' => false
        ]);
        echo $this->Form->control('enable_mail_on_receive_confirmation', [
            'label' => 'Teilnehmenden benachrichtigen, wenn <b>Bestätigung</b> eingeht.',
            'title' => 'Wenn diese Funktion aktiviert ist, wird dem Benutzer eine Mail geschickt, wenn ein Zusatzfeld mit dem Namen "Bestätigung erhalten" auf den Wert "Ja" geändert wird. (Typ "Auswahlfeld")',
            'type' => 'checkbox',
            'escape' => false
        ]);
        echo $this->Form->control('enable_mail_on_receive_documents', [
            'label' => 'Teilnehmenden benachrichtigen, wenn <b>Unterlagen</b> eingehen.',
            'title' => 'Wenn diese Funktion aktiviert ist, wird dem Benutzer eine Mail geschickt, wenn ein Zusatzfeld mit dem Namen "Unterlagen erhalten" auf den Wert "Ja" geändert wird. (Typ "Auswahlfeld")',
            'type' => 'checkbox',
            'escape' => false
        ]);
        echo $this->Form->control('photo_permission_needed', [
            'label' => 'Erlaubnis für Fotos benötigt',
            'title' => 'Wenn diese Option aktiviert ist, wird bei der Anmeldung ein verpflichtendes Auswahlfeld für die Zustimmung/Ablehnung zu Fotos angezeigt.',
            'type' => 'checkbox',
            'escape' => false
        ]);
        echo $this->Form->control('hide_free_slots', [
            'label' => 'Platzanzahl ausblenden',
            'title' => 'Wenn diese Option aktiviert ist, wird bei der Anmeldung die freie Platzanzahl für alle Kurse ausgeblendet.',
            'type' => 'checkbox',
            'escape' => false
        ]);
        echo $this->Form->control('has_lottery', [
                'label' => 'Projekt hat ein Losverfahren',
                'title' => 'Wenn diese Option aktiviert ist, wird ein Knopf zum Auslosen der Teilnehmer angezeigt und die Platzanzahl ausgeblendet.',
                'type' => 'checkbox',
        ]);
        if (in_array('GRANT_RIGHTS', $this->request->getSession()->read('Auth.User.rights'))) {
            echo $this->Form->control('make-rater-user', [
                'type' => 'text',
                'label' => 'Benutzer zum Bewerter machen',
                'placeholder' => 'Benutzername',
                'title' => 'Hier kann ein Benutzername eingetragen werden, dieser Benutzer erhält dann die Rechte dieses Projekt zu bewerten.<br>
                            <i>Hinweis:</i> Dies ist nur eine Abkürzung, die gleichen Rechte könenn auch auf der Benutzerseite vergeben werden.',
                'append' => $this->Form->button('OK',
                    ['formaction' => $this->Url->build(['action' => 'makeRater', $project->id])])
            ]);
        }
        echo $this->element('tag-form', ['tags' => $project->tags, 'modelPrimaryKey' => $project->id, 'usedTags' => $tags]);
        ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->create('Anmeldebestätigung') ?>
    <?php
    $confirm_templates = (new Collection((new Folder(Folder::addPathElement(APP, ['Template', 'Registrations', 'pdf', 'confirmations'])))->find('.*\\.ctp', true)))->map(function($elm){
        $val = substr($elm, 0, -4);
        return ['value' => $val, 'text' => $val];
    });
    echo $this->Form->control('confirmation_mail_template', [
        'label' => 'Vorlage Bestätigungsmail',
        'options' => $confirm_templates
    ]);
    echo $this->Form->control('confirm_alt_contact',[
        'label' => 'Alternativer Ansprechpartner',
        'title' => 'Wenn dieser Wert nicht leer ist, wird er in der Anmeldebestätigung als Ansprechpartner statt "Team uniKIK Schulprojekte" benutzt.',
        'placeholder' => 'Leer lassen für Standard'
    ]);
    echo $this->Form->control('confirm_alt_closing',[
        'label' => 'Alternative Grußformel',
        'title' => 'Wenn dieser Wert nicht leer ist, wird er in der Anmeldebestätigung als Grußformel statt "Ihr [Ansprechpartner]" benutzt.',
        'placeholder' => 'Leer lassen für Standard'
    ]);
    echo $this->Form->control('confirm_alt_sender_address',[
        'label' => 'Alternative Absendeadresse',
        'type' => 'textarea',
        'title' => 'Wenn dieser Wert nicht leer ist, wird er in der Anmeldebestätigung als Absendeadresse statt "uniKIK Schulprojekte, Welfengarten 1" benutzt.',
        'placeholder' => 'Leer lassen für Standard',
        'maxlength' => 190 //for some reason 200 chars are to much for a VARCHAR(200)...
    ]);
    echo $this->Form->control('confirm_alt_return_address',[
        'label' => 'Alternative Rücksendeadresse',
        'type' => 'textarea',
        'title' => 'Wenn dieser Wert nicht leer ist, wird er in der Anmeldebestätigung als Rücksendeadresse statt "uniKIK Schulprojekte, Welfengarten 1" benutzt.',
        'placeholder' => 'Leer lassen für Standard',
        'maxlength' => 190
    ]);
    echo $this->Form->control('confirm_alt_fax',[
        'label' => 'Alternative Faxnummer',
        'title' => 'Wenn dieser Wert nicht leer ist, wird er in der Anmeldebestätigung als Faxnummer benutzt. Wenn hier eine 0 eingetragen wird, wird keine Faxnummer angezeigt.',
        'placeholder' => 'Leer lassen für Standard'
    ]);
    ?>
    <div class="col-md-offset-2">
        <?= $this->Form->button($this->Html->icon('file') . '&nbsp;Anhang '. ($project->hasTag('confirmationAppendix') ? 'ersetzen' : 'hinzufügen'), ['type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#appendixAddModal']) ?>
        <?php if ($project->hasTag('confirmationAppendix')) echo $this->Form->postLink($this->Html->icon('trash') . '&nbsp;Anhang löschen', ['action' => 'removeConfirmationAppendix', $project->id], ['confirm' => 'Anhang wirklich löschen?', 'escape' => false, 'class' => 'btn btn-default', 'title' => 'Anhang löschen', 'block' => true]) ?>
    </div>
    <div class="col-md-offset-2">
        <?php
        $extraClass = '';
        if ($project->isNew()) {
            $extraClass .= 'disabled';
        }
        ?>
        <?= $this->Html->link($this->Html->icon('search') . ' Vorschau zeigen', ['action' => 'preview_confirmation', '_ext' => 'pdf', $project->id], ['class' => 'btn btn-default ' . $extraClass, 'escape' => false, 'target' => '_blank']) ?>
        <?= $this->Html->link($this->Html->icon('envelope') . ' Test-Anmeldebestätigung versenden', ['action' => 'preview_confirmation', $project->id, true], ['class' => 'btn btn-default ' . $extraClass, 'escape' => false]) ?>
        <p><b>Hinweise</b>:<br>
            Die Vorschau der Anmeldebestätigung wird erst nach dem Speichern des Projektes aktualisiert.<br>
            Der Anhang wird, falls vorhanden, nur in den per E-Mail verschickten Anmeldebestätigungen hinzugefügt.<br>
            Die Test-E-Mail wird an die E-Mail-Adresse verschickt, mit der Sie sich im Portal angemeldet haben.</p>
    </div>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->endGroup() ?>
    <?= $this->Panel->create(['body' => false]) ?>
    <?= $this->Panel->header('Kurse') ?>
        <table class="table table-striped" id="course-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Aktionen</th>
                <th style="white-space: nowrap;">Belegung <?= $this->Html->icon('info-sign', ['title' => "Teilnehmende/Kapazität+Warteliste", 'class' => 'text-muted']) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
                foreach ($project->courses as $course):
                    $courseName = h($course->name);
                    $courseTags = implode(',', $course->getTagNamesByPrefix('filter_'));
                    if ($courseTags) {
                        $courseName .= " <small class='text-muted'>[<span class='sr-only'>Filter</span>{$this->Html->icon('filter', ['title' => 'Filterkriteria'])} <i>{$courseTags}</i>]</small>";
                    }
                    if ($course->hasTag('qisid')) {
                        $courseName .= " <small class='text-muted'>[QIS <a href=\"https://qis.verwaltung.uni-hannover.de/qisserver/servlet/de.his.servlet.RequestDispatcherServlet?state=verpublish&status=init&vmfile=no&publishid={$course->getTagValue('qisid')}&moduleCall=webInfo&publishConfFile=webInfo&publishSubDir=veranstaltung\" target='_blank'>{$course->getTagValue('qisid')}</a>]</small>";
                    }
            ?>
                <tr>
                    <td><?= $course->id ?></td>
                    <td><?= $courseName ?></td>
                    <td style="white-space: nowrap">
                        <?= $this->Html->link($this->Html->icon('user'), ['controller' => 'Registrations', 'action' => 'index', $course->project_id, $course->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Teilnahmeliste']) ?>
                        <?= $this->Html->link($this->Html->icon('pencil'), ['controller' => 'courses', 'action' => 'edit', $course->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten'])?>
                        <?= $this->Form->postLink($this->Html->icon('trash'), ['controller' => 'courses', 'action' => 'delete', $course->id], ['confirm' => sprintf('Kurse %s wirklich löschen?', $course->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen', 'block' => true]) ?>
                    </td>
                    <td>
                        <?php
                        $regCount = $course->registration_count;
                        if ($regCount >= $course->max_users + $course->waiting_list_length && $course->max_users != 0 && $course->waiting_list_length != -1) {
                            $regCount = "<span class='text-danger'>$regCount</span>";
                        } elseif ($regCount >= $course->max_users && $course->max_users != 0) {
                            $regCount = "<span class='text-warning'>$regCount</span>";
                        }

                        $maxUsers = $course->max_users;
                        $wlLength = "";
                        if ($maxUsers == 0) {
                            $maxUsers = "&infin;";
                        } else {
                            $wlLength = '+'.$course->waiting_list_length;
                            if ($course->waiting_list_length == -1) {
                                $wlLength = "+&infin;";
                            }
                        }
                        echo "<span class='registration-count'><span class='registration-count-regCount'>$regCount</span> / $maxUsers$wlLength</span>";
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?= $this->Panel->body() ?>
        <div class="btn-group pull-right">
            <button id="courseOptions" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Weitere Optionen <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="courseOptions">
                <li><?= $this->Html->link('Kurse aus dem QIS importieren', ['action' => 'qisImportUpload', $project->id]) ?></li>
                <li class="divider" role="separator"></li>
                <li><?= $this->Html->link('Übersicht Ausschlussgruppen', ['action' => 'exgroupsOverview', $project->id]) ?></li>
            </ul>
        </div>
        <?= $this->Html->icon('plus') ?>
        <?= $this->Form->label('add-scale-count', 'Anzahl Kurse', ['class' => 'sr-only']) ?>
        <?= $this->Form->control('add-course-count', [
            'type' => 'number',
            'min' => 1,
            'value' => 1,
            'label' => false,
            'style' => 'display: inline; width: 10em; margin-right: 0.5em; margin-left: 0.5em;',
            'templates' => [
                    'inputContainer' => '{{content}}',
                    'formGroupHorizontal' => '{{input}}'
            ]]
        ) ?>
        <span id="add-course-text">neue(n) Kurs(e) anlegen</span>
        <?= $this->Form->button('OK', ['formaction' => $this->Url->build(['action' => 'addCourses', $project->id])]) ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->create(['body' => false]) ?>
    <?= $this->Panel->header('Zusatzfelder') ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Abschnitt</th>
                <th>Typ</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($project->custom_fields as $field): ?>
                <tr>
                    <td><?= h($field->name) ?></td>
                    <td><?= h($field->section) ?></td>
                    <td><?= CustomField::getTypeName($field->type) ?></td>
                    <td>
                        <?= $this->Html->link($this->Html->icon('pencil'), ['controller' => 'CustomFields', 'action' => 'edit', $field->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten'])?>
                        <?= $this->Form->postLink($this->Html->icon('trash'), ['controller' => 'CustomFields', 'action' => 'delete', $field->id], ['confirm' => sprintf('Feld "%s" wirklich löschen?', $field->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen', 'block' => true]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?= $this->Panel->body() ?>
        <div class="btn-group pull-right">
            <button id="cfieldOptions" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Weitere Optionen <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="cfieldOptions">
                <li><?= $this->Form->postLink('Standard-Zusatzfelder anlegen', ['action' => 'addDefaultCustomFields', $project->id], ['block' => true]) ?></li>
            </ul>
        </div>
        <?= $this->Form->button($this->Html->icon('plus') . '&nbsp;Feld hinzufügen', ['type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#customFieldAddModal']) ?>
        &nbsp;<b>Hinweis:</b> Weitere Optionen können unter "Bearbeiten" (<?= $this->Html->icon('pencil') ?>) eingestellt werden. Felder deren Name mit einem Stern (*) endet,
        müssen bei der Anmeldung ausgefüllt werden.
    <?= $this->Panel->end() ?>
</fieldset>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?= $this->fetch('postLink') ?>

<?php
$this->Html->scriptBlock(<<<'JS'
    $(function() {
        "use strict";
        function updateAddCoursesText() {
            if ($('#add-course-count').val() > 1)
                $('#add-course-text').html('neue Kurse anlegen');
            else
                $('#add-course-text').html('neuen Kurs anlegen');
        }
        updateAddCoursesText();
        $('#add-course-count').on('change', updateAddCoursesText);
    });
JS
, ['block' => true]);
if (count($uploadForm->getErrors()) > 0) $this->Html->scriptBlock('$(document).ready(function() { $("#appendixAddModal").modal("show"); });', ['block' => true]); // show appendix upload modal to display upload errors
?>
