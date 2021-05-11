<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>Anmeldebestätigung</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        html {
            zoom: 70%; /* HACK for phantomjs */
        }

        p.pagebreak {
            page-break-after: always;
        }

        p, td, th, div, ul, ol, li {
            font-size: 10pt;
        }

        p {
            width: 18cm;
        }

        body {
            margin-top: 0;
            margin-left: 1cm;
        }

        div.info {
            text-align: right;
            color: gray;
        }

        .anschrift {
            margin-top: 2.5cm;
            height: 4cm;
            width: 6.5cm;
        }

        .absender {
            font-size: 7pt;
        }

        .adresse {
            margin-top: 0.5cm;
        }

        .kopf {
            margin-top: 1cm;
            width: 18cm;
            margin-bottom: 1cm;
        }

        .kopf th {
            font-size: 16pt;
        }

        .kopf td {
            font-size: 12pt;
        }

        .kopf th {
            text-align: left;
        }

        table.meinedaten {
            border-collapse: collapse;
            width: 17cm;
        }

        table.meinedaten td, table.meinedaten th {
            border: solid 1px grey;
            padding: 2px;
        }

        table.meinedaten th {
            text-align: right;
            width: 5cm;
        }

        /* from chell.css */
        p.course-selector-fixup + p {
            margin: -10px 0 10px;
        }

        #course-list li {
            padding-bottom: 7px;
        }

    </style>
</head>
<body>
    <?= $this->fetch('content') ?>
</body>
</html>
