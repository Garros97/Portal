<?php
/** @var \App\View\AppView $this */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= strip_tags($this->fetch('title')) . ' :: uniKIK-Portal' ?>
    </title>
    <?php
    $this->Html->meta('icon', ['block' => true]);

    $min = \Cake\Core\Configure::read('debug') ? '' : '.min';

    $this->Html->css('cake-error.css', ['block' => true]);
    $this->Html->css("//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap$min.css", [
        'block' => true,
        'integrity' => 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u sha384-yzOI+AGOH+8sPS29CtL/lEWNFZ+HKVVyYxU0vjId0pMG6xn7UMDo9waPX5ImV0r6',
        'crossorigin' => 'anonymous'
    ]);
    $this->Html->css('chell.css', ['block' => true]);

    $this->Html->script("//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery$min.js", [
        'block' => 'libs',
        'integrity' => 'sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ sha384-KcyRSlC9FQog/lJsT+QA8AUIFBgnwKM7bxm7/YaX+NTr4D00npYawrX0h+oXI3a2',
        'crossorigin' => 'anonymous'
    ]);
    $this->Html->script("//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap$min.js", [
        'block' => true,
        'integrity' => 'sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa sha384-OkuKCCwNNAv3fnqHH7lwPY3m5kkvCIUnsHbjdU7sN022wAYaQUfXkqyIZLlL0xQ/',
        'crossorigin' => 'anonymous'
    ]);

    if ($this->fetch('has-datepicker')) {
        $this->Html->script("bootstrap-datetimepicker$min.js", ['block' => true]);
        $this->Html->css("bootstrap-datetimepicker$min.css", ['block' => true]);
    }

    if ($this->fetch('has-datepicker') || $this->fetch('has-charts')) {
        $this->Html->script("moment-with-locales$min.js", ['block' => 'libs']);
    }

    if ($this->fetch('has-rich-dropdown')) {
        $this->Html->script("jquery.dd$min.js", ['block' => true]);
        $this->Html->css('dd.css', ['block' => true]);
    }

    if ($this->fetch('has-rich-text-editor')) {
        $this->Html->script("summernote$min.js", ['block' => true]);
        $this->Html->script('summernote-de-DE.js', ['block' => true]);
        $this->Html->css('summernote.css', ['block' => true]);
    }

    if ($this->fetch('has-typeahead') || $this->request->getParam('prefix') === 'admin') { //"has-typeahead" will be set to late in the navbar, so check for admin pages here
        $this->Html->script("typeahead.bundle$min.js", ['block' => true]);
    }

    if ($this->fetch('has-charts')) {
        $this->Html->script("//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.6/Chart$min.js", [
            'block' => true,
            'integrity' => 'sha384-ce3p5u2hWy6o19pvYPE3Ytw0cl7hIyokJdu7amporrs/bRx8qXlYhQXwyg4lO4gw sha384-n8Idhf4AYWgEY5snIfKm5wEMziZwThHvMt1XTB14/PJnNys3BLDeyNfZdeYKS6eA',
            'crossorigin' => 'anonymous'
        ]);
    }

    $this->Html->script('chell.js', ['block' => true]);
    $this->Html->script("//oss.maxcdn.com/html5shiv/3.7.3/html5shiv$min.js", [
        'block' => 'ie8scripts',
        'integrity' => 'sha384-qFIkRsVO/J5orlMvxK1sgAt2FXT67og+NyFTITYzvbIP1IJavVEKZM7YWczXkwpB sha384-RPXhaTf22QktT8KTwZ6bUz/C+7CnccaIw5W/y/t0FW5WSDGj3wc3YtRIJC0w47in',
        'crossorigin' => 'anonymous'
    ]);
    $this->Html->script("//oss.maxcdn.com/respond/1.4.2/respond.min.js", [ //there is no un-minified version
        'block' => 'ie8scripts',
        'integrity' => 'sha384-ZoaMbDF+4LeFxg6WdScQ9nnR1QC2MIRxA1O9KWEXQwns1G8UNyIEZIQidzb0T1fo ',
        'crossorigin' => 'anonymous'
    ]);
?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script_head') ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <?= $this->fetch('ie8scripts') ?>
    <![endif]-->
</head>
<body>
    <a href="#content" class="sr-only sr-only-focusable">Zum Inhalt springen</a>

    <?= $this->element('navbar') ?>

    <div class="container" id="content" tabindex="-1">
        <?= $this->Flash->render() ?>

        <?= $this->fetch('content') ?>
    </div>
    <div id="footer">
        <?= $this->element('footer') ?>
    </div>
    <?= $this->fetch('libs') //make sure libs are always loaded first, some other (inline-)scripts depend on it ?>
    <?= $this->fetch('script') ?>
</body>
</html>
