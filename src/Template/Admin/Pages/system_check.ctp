<?php
/** @var \App\View\AppView $this */

$this->extend('/Common/page');
$this->assign('title', 'System Check');
?>

<?php Cake\Error\Debugger::checkSecurityKeys(); ?>
<?php if (version_compare(PHP_VERSION, '5.6', '>=')): ?>
    <p class="alert alert-success">Your version of PHP is 5.6 or higher (<?= PHP_VERSION ?>).</p>
<?php else: ?>
    <p class="alert alert-danger">Your version of PHP is too low. You need PHP 5.4.16 or higher to use CakePHP.</p>
<?php endif; ?>

<?php if (extension_loaded('mbstring')): ?>
    <p class="alert alert-success">Your version of PHP has the mbstring extension loaded.</p>
<?php else: ?>
    <p class="alert alert-danger">Your version of PHP does NOT have the mbstring extension loaded.</p>;
<?php endif; ?>

<?php if (extension_loaded('openssl')): ?>
    <p class="alert alert-success">Your version of PHP has the openssl extension loaded.</p>
<?php elseif (extension_loaded('mcrypt')): ?>
    <p class="alert alert-success">Your version of PHP has the mcrypt extension loaded.</p>
<?php else: ?>
    <p class="alert alert-danger">Your version of PHP does NOT have the openssl or mcrypt extension loaded.</p>
<?php endif; ?>

<?php if (extension_loaded('gd')): $gdInfo = gd_info(); ?>
    <p class="alert alert-success">Your version of PHP has the gd (<?= $gdInfo['GD Version'] ?>) extension loaded.</p>
    <?php if ($gdInfo['PNG Support']): ?>
        <p class="alert alert-success">Your version of PHP the gd extension has PNG support.</p>
    <?php else: ?>
        <p class="alert alert-danger">Your version of PHP the gd extension does NOT have PNG support.</p>
    <?php endif; ?>
    <?php if ($gdInfo['FreeType Support']): ?>
        <p class="alert alert-success">Your version of PHP the gd extension has FreeType (<?= $gdInfo['FreeType Linkage'] ?>) support.</p>
    <?php else: ?>
        <p class="alert alert-danger">Your version of PHP the gd extension does NOT have PNG support.</p>
    <?php endif; ?>
<?php else: ?>
    <p class="alert alert-danger">Your version of PHP does NOT have the gd extension loaded.</p>
<?php endif; ?>

<?php if (extension_loaded('intl')): ?>
    <p class="alert alert-success">Your version of PHP has the intl extension loaded.</p>
<?php else: ?>
    <p class="alert alert-danger">Your version of PHP does NOT have the intl extension loaded.</p>
<?php endif; ?>

<?php if (extension_loaded('zip')): ?>
    <p class="alert alert-success">Your version of PHP has the zip extension loaded.</p>
<?php else: ?>
    <p class="alert alert-warning">Your version of PHP does NOT have the zip extension loaded, the XLSX export will not work.</p>
<?php endif; ?>

<?php if (is_writable(TMP)): ?>
    <p class="alert alert-success">Your tmp directory is writable.</p>
<?php else: ?>
    <p class="alert alert-danger">Your tmp directory is NOT writable.</p>
<?php endif; ?>

<?php if (is_writable(LOGS)): ?>
    <p class="alert alert-success">Your logs directory is writable.</p>
<?php else: ?>
    <p class="alert alert-danger">Your logs directory is NOT writable.</p>
<?php endif; ?>

<?php if (is_writable(ROOT . DS .\Cake\Core\Configure::read('App.uploads'))): ?>
    <p class="alert alert-success">Your uploads directory is writable.</p>
<?php else: ?>
    <p class="alert alert-danger">Your uploads directory is NOT writable.</p>
<?php endif; ?>

<?php $settings = Cake\Cache\Cache::getConfig('_cake_core_'); ?>
<?php if (!empty($settings)): ?>
    <p class="alert alert-success">The <em><?= $settings['className'] ?>Engine</em> is being used for core caching. To change the config edit config/app.php</p>
<?php else: ?>
    <p class="alert alert-danger">Your cache is NOT working. Please check the settings in config/app.php</p>
<?php endif; ?>
<?php
try {
    /** @var \Cake\Database\Connection $connection */
    $connection = \Cake\Datasource\ConnectionManager::get('default');
    $connected = $connection->connect();
} catch (Exception $connectionError) {
    $connected = false;
    $errorMsg = $connectionError->getMessage();
    if (method_exists($connectionError, 'getAttributes')):
        $attributes = $connectionError->getAttributes();
        if (isset($errorMsg['message'])):
            $errorMsg .= '<br />' . $attributes['message'];
        endif;
    endif;
}
?>
<?php if ($connected): ?>
    <p class="alert alert-success">CakePHP is able to connect to the database.</p>
<?php else: ?>
    <p class="alert alert-danger">CakePHP is NOT able to connect to the database.<br /><br /><?= $errorMsg ?></p>
<?php endif; ?>
<?php
try {
    $geonamesCount = -1;
    if ($connected) {
        $query = $connection->newQuery();
        $query->select(['cnt' => $query->func()->count('1')])->from(\Cake\Core\Configure::read('Misc.geonamesDbName') . '.postal_codes');
        $geonamesCount = $query->execute()->fetch()[0];
    }
}
catch (Exception $err) {
    $geonamesCount = -1;
    $errorMsg = $err->getMessage();
}
?>
<?php if ($geonamesCount > 0): ?>
    <p class="alert alert-success">The <i>geonames</i> database was found, it contains <?= $geonamesCount ?> rows.</p>
<?php else: ?>
    <p class="alert alert-danger">The <i>geonames</i> database was NOT found<br /><br /><?= $errorMsg ?></p>
<?php endif; ?>
<?php
try {
    $cakePdf = new \CakePdf\Pdf\CakePdf();
    $cakePdf->template('system_test');
    $pdfData = $cakePdf->output();
    $pdfOk = true;
}
catch (Exception $err) {
    $pdfOk = false;
    $errorMsg = $err->getMessage();
}
?>
<?php if ($pdfOk): ?>
    <p class="alert alert-success">Your PDF configuration seems to work.</p>
<?php else: ?>
    <p class="alert alert-danger">Your PDF config seems not to work. Please check config/app.php<br /><br /><?= $errorMsg ?></p>
<?php endif; ?>
<?php if (!$this->request->is('post')): ?>
    <div class="alert alert-warning">
        <?php
        echo $this->Form->create(null, ['class' => 'form-inline']);
        echo 'Enter a mail address to send a test mail: ';
        echo $this->Form->control('test-mail-addr', [
            'type' => 'email',
            'label' => false,
            'placeholder' => 'test@example.com'
        ]);
        echo $this->Form->submit('Send', ['bootstrap-type' => 'primary']);
        echo $this->Form->end();
        ?>
    </div>
<?php else:
    try {
        \Cake\Mailer\Email::deliver($this->request->getData('test-mail-addr'), 'Test-Mail', 'This is a test mail send by the Chell System Test');
        $mailOk = true;
    }
    catch (Exception $err) {
        $mailOk = false;
        $errorMsg = $err->getMessage();
    }
if ($mailOk): ?>
    <p class="alert alert-success">Your Mail configuration seems to work mail was send to <i><?= $this->request->getData('test-mail-addr') ?></i></p>
<?php else: ?>
    <p class="alert alert-danger">Your Mail config seems not to work. Please check config/app.php<br /><br /><?= $errorMsg ?></p>
<?php endif; endif; ?>

<p class="alert alert-info">The current server time is <i><?= $this->Time->format(Cake\I18n\Time::now()) ?></i></p>

<?php
$url = \Cake\Core\Configure::read('App.newsletterUrl');
if ($url) {
	$http = new \Cake\Http\Client();
	$resp = $http->get($url);
	if ($resp->isOk()):
	?>
		<p class="alert alert-success">Can access the newsletter page via HTTP.</p>
	<?php else: ?>
		<p class="alert alert-danger">Cannot access the newsletter page via HTTP. Response: <i><?= "{$resp->getStatusCode()}: {$resp->getReasonPhrase()}" ?></i></p>
	<?php endif;
} else {
?>
	<p class="alert alert-info">Newsletter Auto-registration is disabled in config. Check <i>App.newsletterUrl</i></p>
<?php } ?>