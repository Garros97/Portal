<?php
use Cake\Core\Configure;

/** @var \App\Model\Entity\User $user */
/** @var \Cake\Collection\CollectionInterface $tags */
/** @var \Cake\Collection\CollectionInterface $rights */
/** @var \Cake\Collection\CollectionInterface $projects */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Alle Accounts', ['action' => 'index']);
echo $this->Chell->actionLink('trash', 'Account löschen', ['action' => 'delete', $user->id], ['confirm' => sprintf('Account %s wirklich löschen?\nDie ist nur möglich, wenn der Benutzer in keinem Projekt angemeldet ist!', $user->username)]);
echo $this->Chell->actionLink('remove', 'Daten löschen', ['action' => 'deleteData', $user->id], ['confirm' => sprintf('Alle persönlichen Daten von Account %s löschen?', $user->username)]);
if ($user->id != $this->request->getSession()->read('Auth.User.id')) {
    echo $this->Chell->actionLink('transfer', 'Anmelden als <b>' . h($user->username) . '</b>' , ['prefix' => false, 'action' => 'loginAs', $user->id]);
}
echo $this->Chell->actionLink('repeat', 'Passwort zurücksetzen', ['action' => 'resetPassword', $user->id], ['confirm' => sprintf('Passwort von Account %s wirklich zurücksetzen?', $user->username)]);
$this->end();

$this->start('info-block');
?>
<p><b>Account-ID:</b> <?= $user->id ?></p>
<p><b>Letzter Login:</b><br /><?= $user->last_login ? $user->last_login: '<i>Noch kein Login</i>' ?></p>
<?php $this->end(); ?>

<?php if ($user->id == $this->request->getSession()->read('Auth.User.id')): ?>
    <div class="alert alert-warning"><b>Hinweis</b>: Dies ist ihr eigener Account.</div>
<?php endif; ?>
<?= $this->Form->create($user, ['horizontal' => true]); ?>
<fieldset>
    <legend>Account bearbeiten</legend>
    <?php
    echo $this->Form->control('username', [
        'label' => 'Accountname'
    ]);
    $button = '<span class="input-group-btn">' . $this->Form->button($this->Html->icon('envelope') . '<span class="sr-only">E-Mail an Account senden</span>', [
        'type' => 'button',
        'onclick' => 'location.href=\'mailto:' . $user->email . '\'',
        'title' => 'E-Mail an Account senden'
    ]) . '</span>';
    echo $this->Form->control('email', [
        'label' => 'E-Mail',
        'templates' => [
            'input' => $this->Html->div('input-group', '<input type="{{type}}" name="{{name}}" class="form-control{{attrs.class}}" {{attrs}} />' . $button),
        ]
    ]);
    echo $this->Form->control('is_teacher',[
        'label' => 'Ist Lehrer',
        'type' => 'checkbox',
    ]);
    echo $this->Form->control('sex', [
        'label' => 'Geschlecht',
        'options' => ['m' => 'Herr', 'f' => 'Frau']
    ]);
    echo $this->Form->control('first_name', [
        'label' => 'Vorname',
        'div' => false
    ]);
    echo $this->Form->control('last_name', [
        'label' => 'Nachname'
    ]);
    echo $this->Form->control('street', [
        'label' => 'Straße'
    ]);
    echo $this->Form->control('house_number', [
        'label' => 'Hausnummer'
    ]);
    echo $this->Form->control('postal_code', [
        'label' => 'PLZ'
    ]);
    echo $this->Form->control('city', [
        'label' => 'Stadt'
    ]);
    ?>
    <?= $this->Panel->startGroup(['open' => -1]) ?>
    <?= $this->Panel->create('Weitere Optionen') ?>
    <h4>Rechte</h4>
    Aktuelle Rechte des Accounts:
    <table class="table table-striped table-condensed table-hover">
    <thead>
        <?= $this->Html->tableHeaders(['Name', 'Einschränkungen', 'Beschreibung', 'Aktionen']) ?>
    </thead>
    <tbody>
        <?= $this->Html->tableCells(collection($user->rights)->map(function ($r) use ($user) {
            return [
                $r->name,
                $r->getHumanReadableSubresource(),
                $r->description,
                $this->Form->postLink($this->Html->icon('trash'), ['controller' => 'rights', 'action' => 'revoke', $r->id, $user->id, $r->_joinData->subresource], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Recht entziehen', 'confirm' => sprintf('Wirklich dem Account %s das Recht %s entziehen?', $user->username, $r->name), 'block' => true])
            ];
        })->toArray()) ?>
    </tbody>
    </table>
    <?php if (in_array('GRANT_RIGHTS', $this->request->getSession()->read('Auth.User.rights'))): ?>
        Neues Recht hinzufügen:
        <div class="form-inline">
            <?= $this->Form->control('new-right-id', [
                'label' => false,
                'options' => $rights->combine('id', 'name') //TODO: Show only the rights the user has?
            ]) ?>
            <?= $this->Form->control('new-right-subresource', [
                'label' => false,
                'placeholder' => 'Einschränkung',
                'value' => 0,
                'title' => '<p>Der Wert <b>0</b> steht für "keine Einschränkung.</p><p>Bei Rechten, die nicht eingeschränkt werden können, hat dieses Feld keine Funktion.</p>'
            ]) ?>
            <?= $this->Form->control('new-right-subresource-project', [
                'label' => false,
                'placeholder' => 'Einschränkung',
                'value' => 0,
                'options' => [0 => '(alle)'] + $projects->map(function ($v, $k) { return "$v [PID $k]"; })->toArray(),
                'style' => 'display: none'
            ]) ?>
            <?= $this->Form->button('OK', ['formaction' => $this->Url->build(['controller' => 'rights', 'action' => 'add_right', $user->id])]) ?>
        </div>
    <?php endif; ?>
    <b>Hinweis:</b> Geänderte Rechte eines Accounts werden erst beim nächsten Login übernommen.
    <h4>Tags</h4>
    <?= $this->element('tag-form', ['tags' => $user->tags, 'modelPrimaryKey' => $user->id, 'usedTags' => $tags]); ?>
    <?= $this->Panel->end() ?>
    <?= $this->Panel->endGroup() ?>
</fieldset>
<?= $this->Form->button('Speichern', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>

<?= $this->fetch('postLink') ?>

<h3>Projektanmeldungen</h3>
<?php if ($user->id != $this->request->getSession()->read('Auth.User.id')): ?>
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="registerUser" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            Benutzer für Projekt anmelden
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="registerUser">
            <?php foreach ($projects->find('active')->toArray() as $pid => $project) : ?>
                <li><?= $this->Html->link($project, ['controller' => 'Registrations', 'action' => 'registerUserForProject', $user->id, $pid]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<table class="table table-striped table-hover">
    <thead>
    <?= $this->Html->tableHeaders(['RID', 'Projekt', 'Aktionen']) ?>
    </thead>
    <tbody>
    <?php foreach($user->registrations as $registration) : ?>
        <tr>
            <td><?= h($registration->id) ?></td>
            <td><?= h($registration->project->name) ?></td>
            <td>
				<?= $this->Html->link($this->Html->icon('eye-open'), ['controller' => 'registrations', 'action' => 'edit', $registration->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Details'])?>
				<?= $this->Form->postLink($this->Html->icon('remove'), ['controller' => 'registrations', 'action' => 'delete', $registration->id], ['escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => 'Details', 'confirm' => 'Wollen Sie diese Registrierung wirklich löschen? Dadurch wird der Teilnehmende von diesem Projekt angemeldet.'])?>
			</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php
$rightsData = json_encode($rights->indexBy('id'));
$this->Html->scriptBlock(<<<JS
    $(function() {
        "use strict";
        var rights = {$rightsData};
        //this JS will transform the textbox for the subresource with a select,
        //when some rights are selected, for easier entry
        //we always create the options, it won't hurt when it is a type=text
        var subresourceInput = $('#new-right-subresource');
        var subresourceProjectInput = $('#new-right-subresource-project');

        $('#new-right-id').on('change', function () {
            var right = rights[$(this).val()];
            subresourceInput.prop('disabled', !right['supports_subresources']);
            if (!right['supports_subresources']) {
                subresourceInput.val(0);
            }
            var isProjectRight = right['name'] == 'MANAGE_PROJECTS' || right['name'] == 'RATE';
            subresourceInput.toggle(!isProjectRight);
            subresourceProjectInput.toggle(isProjectRight);
        }).trigger('change');
        subresourceProjectInput.on('change', function () {
            subresourceInput.val($(this).val()); //the select is frontend-only, store the value in the textbox for the backend
        });
    });
JS
    , ['block' => true]);
?>

