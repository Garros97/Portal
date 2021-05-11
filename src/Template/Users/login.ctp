<?php
/** @var \App\Model\Entity\User $user */
/** @var \App\View\AppView $this */
/** @var bool $showSelectAccountNotice */
/** @var bool $showConfirmDuplicateEmail */

$this->extend('/Common/page');
?>

<?php $this->assign('title', 'Login') ?>

<?php if ($showSelectAccountNotice): ?>
    <div class="col-sm-10 col-sm-offset-1">
        <?= $this->Panel->create('Account benötigt', ['class' => 'panel-info']) ?>
        <p>
            Um sich für ein Projekt anzumelden, benötigen Sie einen <b>Account in unserem Portal</b>. Mit diesem Account können Sie alle Ihre Anmeldungen zu
            Projekten verwalten und je nach Projekt später noch Ihre Kurswahlen ändern, Lösungen abgeben, usw.
        </p>
        <p>
            Sie können sich mit einem bestehenden Account einloggen oder einen neuen Account erstellen.
        </p>
        <p>
            <b>Sind Sie sich nicht sicher, ob Sie schon einen Account bei uns haben?</b> Verwenden Sie die
            <?= $this->Html->link('"Passwort zurücksetzen"-Funktion', ['action' => 'resetPassword'], ['target' => '_blank']) ?>, um nachzuschlagen, ob für Ihre E-Mail-Adresse ein Account
            hinterlegt ist. Sie erhalten dann Ihren Accountnamen und ein neues Passwort per E-Mail, mit dem Sie sich einloggen können.
        </p>
        <?= $this->Panel->end(); ?>
    </div>
<?php endif; ?>

<div class="col-sm-5 col-sm-offset-1">
    <?= $this->Panel->create('Login') ?>
        <?= $this->Form->create() ?>
        <?= $this->Form->control('username', [
            'autofocus',
            'label' => 'Accountname',
            'placeholder' => 'Accountname'
        ]) ?>
        <?= $this->Form->control('password', [
            'label' => 'Passwort',
            'placeholder' => '••••••••••••••••',
            'value' => '' //never populate the password field from POST data //TOOD: Auto-focus this if login failed
        ]) ?>
        <?= $this->Form->button('Login', ['bootstrap-type' => 'primary']) ?>
        <?= $this->Form->end() ?>

    <?= $this->Panel->footer("Probleme beim Einloggen? Sie können {$this->Html->link('Ihr Passwort zurücksetzen', ['action' => 'resetPassword'])}.") ?>
    <?= $this->Panel->end(); ?>
</div>
<div class="col-sm-5">
    <?php
    $firstNamesFemale = [
        'Anna', 'Lea', 'Sarah', 'Hannah', 'Michelle', 'Laura', 'Lisa', 'Lara', 'Lena', 'Julia', 'Johanna', 'Marie', 'Leonie',
        'Annika', 'Katharina', 'Sophie', 'Antonia', 'Emily', 'Alina', 'Melina', 'Jasmin', 'Nina', 'Emma', 'Lina', 'Celina',
        'Luisa', 'Jacqueline', 'Alexandra', 'Carolin', 'Kim', 'Nele', 'Sofia', 'Vanessa', 'Paula', 'Melissa'
	];
	$firstNamesMale = [
        'Lukas', 'Jan', 'Tim', 'Finn', 'Leon', 'Niklas', 'Tom', 'Jonas', 'Jannik', 'Luca', 'Philipp', 'Alexander', 'Marvin', 'Maximilian',
        'Daniel', 'Julian', 'Jakob', 'Kevin', 'Lennard', 'Paul', 'Florian', 'Felix', 'Moritz', 'Nico', 'Simon', 'Tobias', 'Jonathan', 'Max',
        'David', 'Fabian', 'Justin', 'Ole', 'Marcel', 'Dominik', 'Nick'
    ];
    $lastNames = [
        'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Schulz', 'Hoffmann', 'Schäfer', 'Koch', 'Bauer',
        'Richter', 'Klein', 'Wolf', 'Schröder', 'Neumann', 'Schwarz', 'Zimmermann', 'Braun', 'Krüger', 'Hofmann', 'Hartmann', 'Lange'
    ];
	$sex = rand() % 2 == 0 ? 'f' : 'm';
	$arr = $sex == 'f' ? $firstNamesFemale : $firstNamesMale;
    $firstName = $arr[array_rand($arr)];
	unset($arr);
    $lastName = $lastNames[array_rand($lastNames)];
    ?>

    <?= $this->Panel->create('Neuen Account erstellen') ?>
        <?= $this->Form->create($user, ['url' => ['action' => 'add', '?' => $this->request->getQueryParams()]]) ?>
        <?php
        echo $this->Form->control('sex', [
            'label' => 'Anrede',
            'options' => ['m' => 'Herr', 'f' => 'Frau', 'x' => 'k.A./anderes'],
			'default' => $sex
        ]);
        echo $this->Form->control('first_name', [
            'label' => 'Vorname',
            'placeholder' => $firstName
        ]);
        echo $this->Form->control('last_name', [
            'label' => 'Nachname',
            'placeholder' => $lastName
        ]);
        echo $this->Form->control('username', [
            'label' => 'Accountname',
            'placeholder' => 'Accountname'
        ]);
        echo $this->Form->control('password', [
            'label' => 'Passwort',
            'placeholder' => '••••••••••••••••'
        ]);
        echo $this->Form->control('password2', [
            'label' => 'Passwort (Wiederholung)',
            'placeholder' => '••••••••••••••••',
            'type' => 'password'
        ]);
        echo $this->Form->control('email', [
            'label' => 'E-Mail',
            'placeholder' => strtolower($firstName[0]) . '.' . strtolower($lastName) . '@email.de'
        ]);
        echo $this->Form->control('email2', [
            'label' => 'E-Mail (Wiederholung)',
            'placeholder' => strtolower($firstName[0]) . '.' . strtolower($lastName) . '@email.de',
            'type' => 'email'
        ]);
		$infoLink = $this->Html->link($this->Html->icon('info-sign') . '<span class="sr-only">Informationen</span>', '#', [
			'escape' => false,
			'tabindex' => 0,
			'role' => 'button',
			'data-toggle' => 'popover',
			'data-trigger' => 'focus',
			'data-placement' => 'top',
			'title' => 'Nachrichtenbrief',
			'data-content' => 'Der Nachrichtenbrief enthält Informationen zu Schüler- und Lehrerprojekten an der
				Leibniz Universität Hannover. Er wird in unregelmäßigen Abständen gesendet. Sie können den Nachrichtenbrief jederzeit
				mittels eines Links in der E-Mail wieder abbestellen.'
		]);
		echo $this->Form->control('newsletter', [
            'label' => "Für den Nachrichtenbrief anmelden
				{$infoLink}",
            'type' => 'checkbox',
			'escape' => false
        ]);
        echo $this->Form->button('Weiter', ['bootstrap-type' => 'primary']) ?>
        <?= $this->Form->end() ?>

    <?= $this->Panel->end(); ?>
</div>