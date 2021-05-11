<?php
/** @var \App\Model\Entity\User $user */
/** @var \App\View\AppView $this */

$this->extend('/Common/edit');
?>

<?php
$this->start('actions');
echo $this->Chell->actionLink('list', 'Alle Accounts', ['action' => 'index']);
$this->end();
?>

<?= $this->Form->create($user, ['horizontal' => true]); ?>
<fieldset>
    <legend>Account hinzufügen</legend>
    <?php
    echo $this->Form->control('sex', [
        'label' => 'Anrede',
        'options' => ['m' => 'Herr', 'w' => 'Frau', 'x' => 'k.A./anderes']
    ]);
    echo $this->Form->control('first_name', [
        'label' => 'Vorname',
    ]);
    echo $this->Form->control('last_name', [
        'label' => 'Nachname',
    ]);
    echo $this->Form->control('username', [
        'label' => 'Accountname',
    ]);
    echo $this->Form->control('email', [
        'label' => 'E-Mail',
    ]);
    $button = '<span class="input-group-btn">' . $this->Form->button($this->Html->icon('refresh') . '<span class="sr-only">Neues Passwort generieren</span>', [
        'type' => 'button',
        'onclick' => 'randomPassword()',
        'title' => 'Neues Passwort generieren'
    ]) . '</span>';
    echo $this->Form->control('password', [
        'label' => 'Passwort',
        'type' => 'text', //don't hide the password here
        'templates' => [
            'input' => $this->Html->div('input-group', '<input type="{{type}}" name="{{name}}" class="form-control{{attrs.class}}" {{attrs}} />' . $button),
        ]
    ]);
    ?>
</fieldset>
<?= $this->Form->button('Absenden', ['bootstrap-type' => 'primary']) ?>
<?= $this->Form->end() ?>


<?php
$this->Html->scriptBlock(<<<'JS'
$(function() {
    "use strict";
    var updateUsername = true;

    function randomPassword() {
        var keylist="abcdefghkmnpqrstwxyzACEFHJKLMNPRTWXY3479"; //no ambiguous chars

        var pw = '';
        for (var i = 0; i < 8; i++)
            pw += keylist.charAt(Math.floor(Math.random() * keylist.length))

        $('#password').val(pw);
    }

    window.randomPassword = randomPassword;

    $(function() {
        randomPassword();

        $('#first-name, #last-name').on('change keyup paste', function() {
            if (!updateUsername)
                return;

            var username = $('#first-name').val().toLowerCase();
            var lastname = $('#last-name').val().toLowerCase();
            if (lastname.length != 0)
                username += '.' + lastname;
            $('#username').val(username);
        });

        $('#username').on('change', function() {
            if ($(this).val().length == 0)
                updateUsername = true;
            else
                updateUsername = false;
        });
    })
}())
JS
    , ['block' => true]);
?>
