<?php
/** @var \Cake\View\View $this */
/** @var \App\Model\Entity\Registration $registration */
?>

<?php
$fax = $registration->project->getTagValue('confirmAltFax', '0511/762-2851');
if ($fax === '0') {
    $fax = '';
} else {
    $fax = "oder per Fax an $fax";
}
$contact = $registration->project->getTagValue('confirmAltContact', 'Team der uniKIK Schulprojekte');
$senderAddress = nl2br($registration->project->getTagValue('confirmAltSenderAddress', 'Leibniz Universit&auml;t Hannover<br> uniKIK Schulprojekte<br>Welfengarten 1<br>30167 Hannover'));
$returnAddress = nl2br($registration->project->getTagValue('confirmAltReturnAddress', "Leibniz Universität Hannover<br>uniKIK Schulprojekte<br>Stichwort {$registration->project->name}<br>Welfengarten 1<br>30167 Hannover"));
$closing = $registration->project->getTagValue('confirmAltClosing', 'Ihr '.$contact);

$hideFreeSlots = $registration->project->hasTag('hideFreeSlots') ? (bool) $registration->project->getTagValue('hideFreeSlots') : false;
?>

<div class="info">Für Sie</div>
<div class="anschrift">
    <div class="absender"><?= $senderAddress //no h() ?></div>
    <div class="adresse">
        <?= h($registration->user->first_name) ?> <?= h($registration->user->last_name) ?><br />
        <?= h($registration->user->street) ?> <?= h($registration->user->house_number) ?><br />
        <?= h($registration->user->postal_code) ?> <?= h($registration->user->city) ?>
    </div>
</div>

<?php $title = $this->get('title', 'Anmeldebestätigung für '.$registration->project->name) ?>
<table class="kopf">
    <tr><th><?= $title ?></th>
        <td style="text-align:right;"><?= new \Cake\I18n\Date('now') ?></td></tr>
</table>

<p>Die Anmeldung erfolgte für:</p>
<ul id="course-list">
    <?php $showWaitinglistHelpText = false; foreach ($registration->courses as $course): ?>
    <li>
        <?php if ($course->isListPosOnWaitingList($course->list_pos) && !$hideFreeSlots): ?>
            <b>Warteliste:</b>
        <?php $showWaitinglistHelpText = !$hideFreeSlots; endif; ?>
        <?= h($course->name) ?>
        <?= $course->getDescriptionForDisplay() ?>
        <?php if($course->hasTag('infoAfterReg')): ?>
        <?php if (!$course->isListPosOnWaitingList($course->list_pos) || $hideFreeSlots): ?>
            <p><b>Informationen zu diesem Kurs:</b> <?= h($course->getTagValue('infoAfterReg')) ?></p>
        <?php else: ?>
            <p><b>Informationen zu diesem Kurs:</b> <i>Nicht verfügbar, da Sie auf der Warteliste stehen.</i></p>
        <?php endif; ?>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>

<?php if($showWaitinglistHelpText): ?>
<p>
    <b>Hinweis:</b> Bei mindestens einem Kurs stehen Sie momentan auf der Warteliste. Sie nehmen an diesen Kursen aktuell <i>nicht</i> teil.
    Sobald Sie von der Warteliste nachrücken, werden wir Sie per E-Mail benachrichtigen.
</p>
<?php endif; ?>

<p>Bitte beachten Sie, dass Sie Ihre Auswahl im Anmeldeportal noch bis zum <?= $registration->project->register_end ?> ändern können.</p>

<?php if($this->exists('extraInfotext')): ?>
<p><?= $this->get('extraInfotext') ?></p>
<?php endif; ?>

<p><?= str_replace("%REG_ID%", $registration->id, $registration->project->long_description); //no h() ?></p>

<?php if ($this->get('showReturnPage', true)): ?>
<p><b>Bitte senden Sie die letzte Seite dieses Dokumentes unterschrieben auf dem Postweg <?= $fax ?> zurück, um Ihre Anmeldung verbindlich abzuschließen.</b></p>
<?php endif; ?>

<p>
    Mit freundlichen Gr&uuml;&szlig;en<br /><br /><br /><br />
    <?= h($closing) ?>
</p>

<?php if ($this->get('showReturnPage', true)): ?>
<p class="pagebreak"></p>

<div class="info">Für <?= $contact ?></div>
<div class="anschrift">
    <div class="absender">
        <?= h($registration->user->first_name) ?> <?= h($registration->user->last_name) ?>,
        <?= h($registration->user->street) ?> <?= h($registration->user->house_number) ?>,
        <?= h($registration->user->postal_code) ?> <?= h($registration->user->city) ?>
    </div>
    <div class="adresse">
        <?= $returnAddress //no h() ?>
    </div>
</div>

<p style="margin-top: 3cm;">Sehr geehrtes <?= h($contact) ?>,</p>
<p>hiermit melde ich mich verbindlich zu dem Projekt <i><?= h($registration->project->name) ?></i> an.</p>
<p>Meine Daten:</p>
<table class="meinedaten">
    <tr>
        <th>Name:</th>
        <td><?= h($registration->user->first_name) ?> <?= h($registration->user->last_name) ?></td>
    </tr>
    <tr>
        <th>Anschrift:</th>
        <td>
            <?= h($registration->user->street) ?> <?= h($registration->user->house_number) ?><br />
            <?= h($registration->user->postal_code) ?> <?= h($registration->user->city) ?>
        </td>
    </tr>
    <tr>
        <th>E-Mail:</th>
        <td><?= h($registration->user->email) ?></td>
    </tr>
    <tr>
        <th>Portal-Accountname:</th>
        <td><?= h($registration->user->username) ?></td>
    </tr>
    <tr>
        <th>User-ID:</th>
        <td><?= $registration->user->id ?></td>
    </tr>
    <tr>
        <th>RID:</th>
        <td><?= $registration->id ?></td>
    </tr>
</table>

<table class="unterschriften" style="margin-top: 4cm; width: 15cm;">
    <tr><td style="border-bottom: solid 1px black;"></td><td></td><td style="border-bottom: solid 1px black;"></td><td></td><td style="border-bottom: solid 1px black;"></td></tr>
    <tr><td>Ort, Datum</td><td></td><td>Unterschrift</td><td></td><td>Unterschrift eines Erziehungsberechtigten<br>(nur für minderjährige Teilnehmende)</td></tr>
</table>
<?php endif; ?>

<?php if ($registration->project->hasTag('photoPermissionNeeded') && $registration->project->getTagValue('photoPermissionNeeded') == true): ?>
    <p class="pagebreak"></p>
    <div class="info">Für <?= $contact ?></div>
    <h4 style="margin-top: 2cm;">Einverständniserklärung zur Veröffentlichung von Fotos im Internet</h4>
    <h5>
        Veranstaltung / Projekt: <i><?= $registration->project->name ?></i><br>
        Datum: <?= new \Cake\I18n\Date('now') ?>
    </h5>
    <p>
        uniKIK - die Schnittstelle zwischen Schule und Universität der Leibniz Universität Hannover - beabsichtigt, zur Selbstdarstellung auf ihrer
        Website (<a href="www.uni-hannover.de/leibniz4school">www.uni-hannover.de/leibniz4school</a>) Fotos zu veröffentlichen, auf denen auch ich
        zu sehen sein kann. Dabei erfolgt keine Nennung der Namen der abgebildeten Personen und es werden nicht gezielt einzelne Personen vorgestellt.
    </p>
    <p>
        Ich willige ein, dass zu diesem Zweck Fotos, auf denen ich zu sehen bin, ins Internet eingestellt werden dürfen. Soweit sich aus dem Foto Hinweise
        auf die ethnische Herkunft, Religion oder Gesundheit ergeben (z. B. Hautfarbe, Kopfbedeckung, Brille), bezieht sich die vorliegende Einwilligung
        auch auf diese Angaben.
    </p>
    <p>
        Ich habe zur Kenntnis genommen, dass Informationen im Internet weltweit zugänglich sind, mit Suchmaschinen gefunden und mit anderen Informationen
        verknüpft werden können, woraus sich unter Umständen Persönlichkeitsprofile über mich erstellen lassen. Mir ist bewusst, dass ins Internet gestellte
        Informationen einschließlich Fotos problemlos kopiert und weiterverbreitet werden können und dass es spezialisierte Archivierungsdienste gibt, deren
        Ziel es ist, den Zustand bestimmter Websites zu bestimmten Terminen dauerhaft zu dokumentieren. Dies kann dazu führen, dass im Internet veröffentlichte
        Informationen auch nach ihrer Löschung auf der Ursprungs-Seite weiterhin aufzufinden sind.
    </p>
    <p>
        Die Fotos dürfen auch auf der Facebook-Präsenz der Schülerprojekte
        (<a href="www.facebook.com/LeibnizUni.Schuelerprojekte">www.facebook.com/LeibnizUni.Schuelerprojekte</a>) veröffentlicht werden. Mir ist
        bekannt, dass nach den derzeit bekannten Informationen Fotos und Daten bei Facebook.com überhaupt nicht mehr gelöscht werden können, sondern nur nicht
        mehr öffentlich gezeigt werden. Über die interne Nutzung von Fotos und Daten durch Facebook.com - etwa zur Bildung von Persönlichkeitsprofilen - gibt
        es derzeit keine ausreichenden Informationen.
    </p>
    <p>
        Diese Einwilligung ist freiwillig. Ich kann sie ohne Angabe von Gründen verweigern, ohne dass ich deswegen Nachteile zu befürchten hätte. Ich kann diese
        Einwilligung zudem jederzeit schriftlich widerrufen. Fotos, die im Wesentlichen nur mich zeigen, werden dann innerhalb von maximal zwei Wochen aus dem
        Internetangebot der Leibniz Universität Hannover entfernt und nicht mehr für neue Veröffentlichungen verwendet. Sofern ich auf dem Foto zusammen mit
        anderen Personen abgebildet bin, muss das Foto nicht entfernt werden, sondern es genügt, wenn ich innerhalb von zwei Wochen auf dem Foto unkenntlich
        gemacht werde (z. B. durch Verpixelung). Sind auf dem Foto auch andere Personen abgebildet und möchte die Leibniz Universität die Möglichkeit zur
        Verpixelung nicht nutzen, sondern es direkt durch ein neues Foto ersetzen (etwa weil das Foto eine besondere Bedeutung für die Website hat), beträgt die
        Frist für den Austausch des Fotos einen Monat. Diese Einwilligungserklärung gilt ab dem Datum der Unterschrift bis zu einem etwaigen Widerruf.
    </p>

    <table class="unterschriften" style="margin-top: 4cm; width: 15cm;">
        <tr><td style="border-bottom: solid 1px black;"></td></tr>
        <tr><td>Name der Teilnehmerin/des Teilnehmers</td></tr>
    </table>
    <table class="unterschriften" style="margin-top: 1cm; width: 15cm;">
        <tr><td style="border-bottom: solid 1px black;"></td></tr>
        <tr><td>Ort, Datum, Unterschrift (der Teilnehmerin/des Teilnehmers oder bei Minderjährigen der/des Sorgeberechtigten)</td></tr>
    </table>
<?php endif; ?>
