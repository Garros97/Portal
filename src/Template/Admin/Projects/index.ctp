<?php
/** @var \App\Model\Entity\Project[] $projects */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
$this->assign('title', 'Projekte Übersicht');
?>

<!-- Modal for duplicate -->
<?= $this->Modal->create('Projekt kopieren', ['id' => 'duplicateModal', 'size' => 'large']) ?>
<?= $this->Form->create(null, ['horizontal' => true, 'url' => ['controller' => 'Projects', 'action' => 'duplicate', '{ID}']]); ?>
    <p>
        Bitte geben Sie einen neuen Namen und ein neuen Kurznamen für die Kopie des Projektes ein. Alle weiteren Einstellungen
        können Sie im nächsten Schritt bearbeiten.
    </p>
    <p>
        Kopiert werden:
        <ul>
            <li>Alle Einstellungen des Projektes (Anmeldefristen, Beschreibung, etc.)</li>
            <li>Zusatzfelder</li>
        </ul>
    </p>
    <p>
        <b>Nicht</b> kopiert werden:
        <ul>
            <li>Kurse</li>
            <li>Registrierungen</li>
        </ul>
    </p>
    <fieldset>
        <?php
        echo $this->Form->control('new_name', [
            'id' => 'duplicate-name',
            'label' => 'Name'
        ]);
        echo $this->Form->control('new_urlname', [
            'id' => 'duplicate-urlname',
            'label' => 'Kurzname',
            'title' => 'Diese Kurzfassung ("Slug") wird in Links zu diesem Projekt verwendet. Beispiel: <em>unifit15</em> für uni:fit im Jahr 2015.<br />Nur die Zeichen <em>a-z,A-Z,0-9,-,_</em> sind erlaubt.',
            'help' => 'Der Kurzname muss eindeutig sein.'
        ]);
        ?>
    </fieldset>
<?= $this->Modal->end([
    $this->Form->button('Absenden', ['bootstrap-type' => 'primary']),
    $this->Form->button('Abbrechen', ['data-dismiss' => 'modal'])
]) ?>
<?= $this->Form->end() ?>
<!-- end of modal -->

<?php
$this->start('actions');
echo $this->Chell->actionLink('home', 'Neues Projekt', ['controller' => 'Projects', 'action' => 'add']);
$this->end();
?>

<?php foreach ($projects as $project): ?>
    <?php
    $header_style = 'default';
    $register_timespan_text = ' ist ungültig (?)';
    if ($this->Time->isFuture($project->register_start)) {
        $register_timespan_text = 'beginnt in ' . $this->Time->timeAgoInWords($project->register_start, ['end' => '100 years']);
        $header_style = 'info';
    } else if ($this->Time->isFuture($project->register_end)) {
        $register_timespan_text = 'läuft noch ' . $this->Time->timeAgoInWords($project->register_end, ['end' => '100 years']);
        $header_style = 'primary';
    } else if ($this->Time->isPast($project->register_end)) {
        $register_timespan_text = 'endete ' . $this->Time->timeAgoInWords($project->register_end, ['end' => '100 years']);
    }
    ?>
    <div class="panel panel-<?= $header_style ?>">
        <div class="panel-heading">
           <?= $this->Html->link(h($project->name), ['action' => 'edit', $project->id], ['class' => 'link-no-style']) ?> <span class="text-muted"><small>[<?= h($project->urlname) ?>]</small></span>
            <?php
            if (!$project->visible) {
                echo $this->Html->icon('eye-close', ['title' => 'Projekt nicht sichtbar']);
            }
            if ($project->requiresGroupRegistration()) {
                echo $this->Html->icon('list', ['title' => 'Gruppenprojekt']);
            }
            ?>
        </div>
        <div class="panel-body">
            Anmeldezeitraum <?= $register_timespan_text ?>
            <br>
            <?= $this->Html->link(count($project->courses) . ' Kurse', ['controller' => 'Projects', 'action' => 'edit', $project->id, '#' => 'course-table']) ?> &bullet;
            <?= $this->Html->link(count($project->registrations) . ' Teilnehmende', ['controller' => 'Registrations', 'action' => 'index', $project->id]) ?>
            <?= $project->max_group_size > 0 ? '&bullet; ' . $this->Html->link(count($project->groups) . ' Gruppen', ['controller' => 'Groups', 'action' => 'index', $project->id]) : '' ?>
            <div class="pull-right">
                <?= $this->Html->link($this->Html->icon('pencil'), ['action' => 'edit', $project->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Bearbeiten']) ?>
                <?= $this->Html->link($this->Html->icon('stats'), ['controller' => 'Stats', 'action' => 'index', $project->id], ['escape' => false, 'class' => 'btn btn-default', 'title' => 'Statistiken']) ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Datenexport">
                        <?= $this->Html->icon('list')?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right export-dropdown">
                        <li class="dropdown">
                            <?php
                            $exports = ['Gruppen' => 'groups', 'Teilnehmende' => 'participants'];
                            $options = ['data-toggle' => 'tooltip', 'class' => 'btn btn-default', 'escapeTitle' => false];
                            foreach ($exports as $name => $action):
                            ?>
                            <div class="dropdown-entry">
                                <span><?= $name ?></span>
                                <div class="btn-group btn-group-xs" role="group">
                                    <?= $this->Html->link($this->Html->icon('list'), ['controller' => 'exports', 'action' => $action, '_ext' => 'xls', $project->urlname], $options + ['title' => 'XLS']) ?>
                                    <?= $this->Html->link($this->Html->icon('list-alt'), ['controller' => 'exports', 'action' => $action, '_ext' => 'xlsx', $project->urlname], $options + ['title' => 'XLSX']) ?>
                                    <?= $this->Html->link($this->Html->icon('file'), ['controller' => 'exports', 'action' => $action, '_ext' => 'csv', $project->urlname], $options + ['title' => 'CSV']) ?>
                                    <?= $this->Html->link($this->Html->icon('eye-open'), ['controller' => 'exports', 'action' => $action, '_ext' => 'html', $project->urlname], $options + ['title' => 'Im Browser', 'target' => '_blank']) ?>
                                </div>
                            </div>
                            <?php endforeach; unset($exports, $options) ?>
                        </li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <?= $this->Html->icon('cog') ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><?= $this->Html->link($this->Html->icon('list-alt') . '&nbsp;Gruppenliste', ['controller' => 'Groups', 'action' => 'index', $project->id], ['escape' => false]) ?></li>
                        <li role="separator" class="divider"></li>
                        <li><?= $this->Html->link($this->Html->icon('duplicate') . '&nbsp;Projekt kopieren', '#', [
                                'data-toggle' => 'modal',
                                'data-target' => '#duplicateModal',
                                'data-project-id' => $project->id,
                                'data-project-name' => $project->name,
                                'data-project-urlname' => $project->urlname,
                                'escape' => false
                            ]) ?></li>
                        <li><?= $this->Form->postLink($this->Html->icon('trash') . '&nbsp;Projekt löschen', ['action' => 'delete', $project->id], ['confirm' => sprintf('Projekt %s wirklich löschen?', $project->id), 'escape' => false]) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<nav>
    <?= $this->Paginator->numbers(['prev' => '« zurück', 'next' => 'vor »']) ?>
</nav>
<?php
$this->Html->scriptBlock(<<<'JS'
    $(function() {
        "use strict";
        $('[data-toggle="tooltip"]').tooltip({container: "body"})
        $('#duplicateModal').on('show.bs.modal', function (event) {
            var link = $(event.relatedTarget);
            var modal = $(this);
            modal.find('form').attr('action', function (i, v) { return v.replace(encodeURIComponent('{ID}'), link.data('project-id')) });
            modal.find('#duplicate-name').val(link.data('project-name'));
            modal.find('#duplicate-urlname').val(link.data('project-urlname'));
        })
    });
JS
    , ['block' => true]);
?>