<?php
/** @var \App\Model\Entity\Course $course */
/** @var \App\View\AppView $this */
/** @var string[] $usedExgroups */
/** @var string[] $usedFilters */
/** @var \Cake\Collection\CollectionInterface $tags */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Zum Projekt', ['controller' => 'Projects', 'action' => 'edit', $course->project_id]);
echo $this->Chell->actionLink('trash', 'Kurs löschen', ['action' => 'delete', $course->id], ['confirm' => sprintf('Wirklich Kurs "%s" löschen?', $course->name)]);
echo $this->Chell->actionLink('user', 'Teilnahmeliste', ['controller' => 'Registrations', 'action' => 'index', $course->project_id, $course->id]);
$this->end();
?>

<?= $this->Form->create($course, ['horizontal' => true]); ?>
<fieldset>
    <legend>Kurs bearbeiten</legend>
    <?php
        echo $this->Form->control('name', [
            'label' => 'Name'
        ]);
        echo $this->Form->control('sort', [
            'label' => 'Sortierfeld',
            'title' => 'Nach diesem Feld werden die Kurse bei der Ausgabe lexikographisch sortiert.'
        ]);
        echo $this->Form->control('description', [
            'label' => 'Beschreibung',
            'data-provide' => 'rich-text-editor'
        ]);
        $this->assign('has-rich-text-editor', true);
        echo $this->Form->control('max_users', [
            'label' => 'Teilnehmerzahl',
            'min' => 0,
            'title' => 'Ein Wert von "0" bedeutet "keine Beschränkung"'
        ]);
        echo $this->Form->control('waiting_list_length', [
            'label' => 'Plätze Warteliste',
            'min' => -1,
            'title' => 'Ein Wert von "-1" bedeutet "keine Beschränkung"'
        ]);
    ?>
    <?= $this->Panel->startGroup(['open' => 1]) ?>
    <?= $this->Panel->create('Weitere Optionen') ?>
        <?php $enableUploads = $course->project->requiresGroupRegistration();?>
        <div class="form-group">
            <label class="col-md-2 control-label">Uploadzeitraum</label>
            <div class="col-md-6">
                <?php
                echo $this->Form->control('uploads_allowed', [
                    'label' => 'Uploads aktivieren',
                    'disabled' => !$enableUploads && !$course->isDirty('uploads_allowed')
                ]);
                echo $this->Form->control('uploads_start', [
                    'label' => 'Start',
                    'type' => 'datePicker',
                    'showTime' => true,
                    'disabled' => !$enableUploads && !$course->isDirty('uploads_start')
                ]);
                echo $this->Form->control('uploads_end', [
                    'label' => 'Ende',
                    'type' => 'datePicker',
                    'showTime' => true,
                    'disabled' => !$enableUploads && !$course->isDirty('uploads_end')
                ]);
                ?>
            </div>
        </div>
        <?php if (!$enableUploads): ?>
            <p><b>Hinweis</b>: Uploads sind nicht verfügbar, da für das Projekt keine Gruppenanmeldung vorgesehen ist.</p>
        <?php endif; ?>
        <?php
        echo $this->Form->control('register_end', [
            'label' => 'Anmeldung bis',
            'type' => 'datePicker',
            'showTime' => true,
            'title' => 'Wenn aktiviert kann dieser Kurs nur bis zu dem angegebenen Zeitpunkt gewählt werden.<br>
                        Bitte diese Funktion in Kombination mit Ausschlussgruppen und verplichtenden Kursen mit Bedacht nutzen.',
            'data-default-date' => $course->project->register_end->format('d.m.Y H:m'),
            'prepend' => $this->Form->control('register_end_active', [
                'type' => 'checkbox',
                'label' => false,
                'templates' => ['checkboxContainerHorizontal' => '{{content}}']
            ])
        ]);
        echo $this->Form->control('exgroups', [
            'label' => 'Ausschlussgruppen',
            'title' => 'Zwei Kurse, die <i>mindestens eine</i> gemeinsamme Ausschlussgruppe haben
                        können nicht gleichzeitig gewählt werden. Dies eignet sich besonders
                        für Kurse, die sich zeitlich oder inhaltlich überschneiden.',
            'help' => 'Bis jetzt verwendete Ausschlussgruppen: ' . (implode(', ', collection($usedExgroups)->map(function($eg) {
                return $this->Html->link($eg, '#', ['class' => 'add-exgroup-link']);
            })->toArray()) ?: '<i>(keine)</i>')
        ]);
        echo $this->Form->control('filters', [
            'label' => 'Filterkriterien',
            'title' => 'Wenn Filterkriterien vergeben sind muss bei der Anmeldung ein Kriterium ausgewählt
                        werden und es können dann nur Kurse gewählt werden, die in diesem Kriterium liegen.
                        <ul>
                        <li>Kurse ohne Filtekriterium sind immer wählbar</li>
                        <li>Kurse können mehr als einem Kriterium zugeordnet sein</li>
                        </ul>',
            'help' => 'Bis jetzt verwendete Filterkriterien: ' . (implode(', ', collection($usedFilters)->map(function($filter) {
                    return $this->Html->link($filter, '#', ['class' => 'add-filter-link']);
                })->toArray()) ?: '<i>(keine)</i>')
        ]);
        echo $this->Form->control('info_after_reg', [
            'label' => 'Information nach Anmeldung',
            'title' => 'Diese Information erhalten die Teilnehmenden auf der Anmeldebestätitung,
                        aber nur, wenn sie <b>nicht</b> auf der Warteliste stehen (also wirklich teilnehmen).
                        Hier können z.B. "geheime" Rauminformationen angegeben werden.'
        ]);
        echo $this->Form->control('forced', [
            'label' => 'Verpflichtender Kurs',
            'title' => 'Ein <b>verpflichtender Kurs</b> muss immer gewählt werden. (Der Haken wird bei der Anmeldung automatisch gesetzt)
                        Ein solcher Kurs kann keine Ausschlussgruppen oder Filterkriterien enthalten.',
            'type' => 'checkbox'
        ]);
        echo $this->Form->control('hide_free_slots', [
            'label' => 'Platzanzahl ausblenden',
            'title' => 'Wenn diese Option aktiviert ist, wird bei der Anmeldung die freie Platzanzahl für diesen Kurs nicht angezeigt.',
            'type' => 'checkbox'
        ]);
        ?>
        <?= $this->element('tag-form', ['tags' => $course->tags, 'modelPrimaryKey' => $course->id, 'usedTags' => $tags]) ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->create(['body' => true]) ?>
    <?= $this->Panel->header('Bewertungsskalen') ?>
    <table class="table table-striped" id="course-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Hinweis</th>
            <th>Sichtbar?</th>
            <th>Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($course->scales as $scale): ?>
            <tr>
                <td><?= $scale->id ?></td>
                <td><?= h($scale->name) ?></td>
                <td><?= h($this->Text->truncate($scale->hint, 70)) ?></td>
                <td><?= $scale->user_visible ? 'Ja' : 'Nein' ?></td>
                <td>
                    <?= $this->Html->link($this->Html->icon('pencil'), ['controller' => 'scales', 'action' => 'edit', $scale->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Bearbeiten'])?>
                    <?= $this->Form->postLink($this->Html->icon('trash'), ['controller' => 'scales', 'action' => 'delete', $scale->id], ['confirm' => sprintf('Skala %s wirklich löschen?', $scale->name), 'escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Löschen', 'block' => true]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->Panel->body() ?>
    <?php if ($course->project->requiresGroupRegistration()): ?>
    <?= $this->Html->icon('plus') ?>
    <?= $this->Form->label('add-scale-count', 'Anzahl Kurse', ['class' => 'sr-only']) ?>
    <?= $this->Form->control('add-scale-count', [
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
    <span id="add-scale-text">neue Skal(a/en) anlegen</span>
    <?= $this->Form->button('OK', ['formaction' => $this->Url->build(['action' => 'addScales', $course->id])]) ?>
    <?php else: ?>
        <p><b>Hinweis</b>: Für dieses Projekt ist keine Gruppenanmeldung vorgesehen, daher können keine Bewertungsskalen hinzugefügt werden.</p>
    <?php endif; ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->endGroup() ?>
</fieldset>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?= $this->fetch('postLink') ?>

<?php
$this->Html->scriptBlock(<<<'JS'
(function(){
    "use strict";
    function addListEntry(listName, name) {
        var list = $('#' + listName);
        if (!list.val().match(/^\s*$/))
            list.val(list.val() + ', ' + name);
        else
            list.val(name);
    }

    function updateEnableDisable() {
        $('#uploads-start, #uploads-end').prop('disabled', !$('#uploads-allowed').prop('checked'));
    }

    function handleRegisterEndActive() {
        var isEnabled = $('#register-end-active').prop('checked');
        var regEnd = $('#register-end');
        regEnd.prop('disabled', !isEnabled).prop('required', isEnabled); //it is required if the user explicitly checked the box
        if (!isEnabled) {
            regEnd.val('');
        } else if (!regEnd.val()) {
            regEnd.val(regEnd.data('default-date'));
        }
    }

    $(function() {
        updateEnableDisable();
        $('#uploads-allowed').on('change', updateEnableDisable);
        handleRegisterEndActive();
        $('#register-end-active').on('change', handleRegisterEndActive);
        $('a.add-exgroup-link').on('click', function(e) {
            e.preventDefault();
            addListEntry('exgroups', $(this).text())
        })
        $('a.add-filter-link').on('click', function(e) {
            e.preventDefault();
            addListEntry('filters', $(this).text())
        })
    });

    function updateAddScaleText() {
        if ($('#add-scale-count').val() > 1)
            $('#add-scale-text').html('neue Skalen anlegen');
        else
            $('#add-scale-text').html('neue Skala anlegen');
    }
    updateAddScaleText();
    $('#add-scale-count').on('change', updateAddScaleText);
}());
JS
    , ['block' => true]);
?>
