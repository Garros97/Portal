<?php
/**
 * Copyright (c) 2012-2013 InterNations GmbH
 *
 * Licensed under The MIT License
 *
 * Adapted for Chell by uniKIK & ZSB 2016
 *
 * @var \App\View\AppView $this
 */
?>
<?php
use \Cake\Core\Configure;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <title><?= $this->fetch('title') ?></title>

    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
        }
        table td {
            border-collapse: collapse;
        }
        .ExternalClass {
            width: 100%;
        }
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }
        .ReadMsgBody {
            width: 100%;
            background-color: #ebebeb;
        }
        table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }
        .yshortcuts a {
            border-bottom: none !important;
        }
        @media screen and (max-width: 599px) {
            .force-row,
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        @media screen and (max-width: 400px) {
            .container-padding {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }
        .ios-footer a {
            color: #aaaaaa !important;
            text-decoration: underline;
        }
    </style>
</head>

<body style="margin:0; padding:0;" bgcolor="#F0F0F0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0">
    <tr>
        <td align="center" valign="top" bgcolor="#F0F0F0" style="background-color: #F0F0F0;">
            <br>
            <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" style="width:600px;max-width:600px">
                <tr>
                    <td class="container-padding header" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:24px;font-weight:bold;padding-bottom:12px;color:##337AB7;padding-left:24px;padding-right:24px">
                        <?= $this->Html->image('zsb_logo_70.jpg', ['height' => '70px', 'alt' => 'Logo Zentrale Studienberatung', 'fullBase' => true]) ?>
                        <?= Configure::read('App.name') ?>
                    </td>
                </tr>
                <tr>
                    <td class="container-padding content" align="left" style="padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff">
                        <br>
                        <div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:18px;font-weight:600;color:#374550"><?= $this->fetch('title') ?></div>
                        <br>
                        <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333">
                            <?= $this->fetch('content') ?>
                        </div>

                    </td>
                </tr>
                <tr>
                    <td class="container-padding footer-text" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:16px;color:#aaaaaa;padding-left:24px;padding-right:24px">
                        <br><br>
                        Dies ist eine automatisch generierte E-Mail, bitte antworten Sie nicht auf diese E-Mail.<br>
                        Bei Fragen wenden Sie sich bitte an <a href="mailto:info@schulprojekte.uni-hannover.de">info@schulprojekte.uni-hannover.de</a>.
                        <br><br>
                        <span class="ios-footer">
                          Leibniz Universität Hannover<br>
                          uniKIK Schulprojekte<br>
                          Welfengarten 1<br>
                          30167 Hannover<br>
                          Tel.: 0511 762 2020<br>
                        </span>
                        <a href="<?= $this->Url->build('/', ['fullBase' => true]) ?>" style="color:#aaaaaa"><?= $this->Url->build('/', ['fullBase' => true]) ?></a><br>
                        <a href="https://www.lehrerbildung.uni-hannover.de/de/schulprojekte/" style="color:#aaaaaa">https://www.lehrerbildung.uni-hannover.de/de/schulprojekte/</a><br>
                        <br><br>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
