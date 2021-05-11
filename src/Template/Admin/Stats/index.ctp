<?php
/**
 * @var \App\View\AppView $this
 * @var array $projects
 * @var array $userSexCounts
 * @var array $teacherCount
 * @var int $groupCount
 * @var float $avgGroupSize
 * @var array $historyData
 * @var array $projectId
 * @var bool $globalStats
 * @var array $coursesData
 * @var array $uploadsData
 * @var array $customFieldsData
 */

use App\Model\Entity\CustomFieldType;

$this->extend('/Common/page');

if ($globalStats) {
    $this->assign('title', 'Statistik <small>Übersicht</small>');
} else {
    $titleString = (count($projectId) > 1 ? 'PIDs ' : 'PID') . join(', ', $projectId);
    $this->assign('title', "Statistik <small>$titleString</small>"); //TODO: Show name of projects here
}

$this->assign('has-charts', true);

$this->Html->setConfig([
    'templates' => [
        'progressBarInner' => '<span>{{width}}%</span>',
        'progressBarContainer' => '<div class="progress progress-text-centered{{attrs.class}}"{{attrs}}>{{content}}</div>',
    ]
]);

//general
$usersTotal = array_sum($userSexCounts);
$usersMale = $userSexCounts['m'];
$usersFemale = $userSexCounts['f'];
$usersOther = $usersTotal - $usersFemale - $usersMale;
$usersNonTeacher = $usersTotal - $teacherCount;
?>

<?= $this->Panel->create('Projektauswahl', ['collapsible' => true, 'open' => false, 'class' => 'show-chevron']) ?>
<p xmlns="http://www.w3.org/1999/html">Projekte wählen, für welche die Statistiken angezeigt werden sollen.</p>
<?php
echo $this->Form->create(null);
echo $this->Form->control('projectSelector', [
    'label' => 'Projekte',
    'options' => $projects,
    'multiple' => true,
    'value' => $projectId
]);
echo $this->Form->submit('Absenden');
echo $this->Form->end();
?>
<p><i>Hinweis: Es können mehrere Projekte gleichzeitig ausgewählt werden. Wenn kein Projekt ausgewählt wird, wird eine Statistik über alle Projekte ausgegeben.</i></p>
<?= $this->Panel->end() ?>

<?php if (!$globalStats && count($projectId) > 1): ?>
<div class="alert alert-warning"><b>Achtung</b>: Es wurde mehr als ein Projekt ausgewählt. Die <i>Anteile</i> der Abgaben und Zusatzfelder werden dadurch aktuell <b>nicht</b> korrekt berechnet!</div>
<?php endif; ?>

<?= $this->Panel->create('Allgemein') ?>
<div class="row">
    <div class="col-lg-8">
        <table class="table table-striped table-hover">
            <?= $this->Html->tableCells([
                ['Anzahl Teilnehmende', $usersTotal],
                ['...davon männlich', "$usersMale ({$this->Number->toPercentage(100 * $usersMale/$usersTotal)})"],
                ['...davon weiblich', "$usersFemale ({$this->Number->toPercentage(100 * $usersFemale/$usersTotal)})"],
                ['...davon sonstiges', "$usersOther ({$this->Number->toPercentage(100 * $usersOther/$usersTotal)})"],
                ['...davon Lehrkräfte', "$teacherCount ({$this->Number->toPercentage(100 * $teacherCount/$usersTotal)})"],
                ['Anzahl Gruppen', $this->Number->format($groupCount)],
                ['Durchschnittliche Gruppengröße', $this->Number->format($avgGroupSize, ['places' => 2])]
            ]) ?>
        </table>
    </div>
    <div class="col-lg-4">
        <canvas id="chartUsersSex"></canvas>
        <canvas id="chartUsersTeachers"></canvas>
    </div>
</div>
<?= $this->Panel->end(); ?>
<?= $this->Panel->create('Verlauf') ?>
<div class="row">
    <div class="col-lg-8">
        <?php
        function cumSum($a) {
            $ret = [];
            $runningSum = 0;
            foreach ($a as $x) {
                $runningSum += $x;
                $ret[] = $runningSum;
            }
            return $ret;
        }
        $historyData = [
            'labels' => json_encode(array_keys($historyData)),
            'data' => json_encode(cumSum(array_values($historyData)))
        ];
        ?>
        <canvas id="chartHistory"></canvas>
    </div>
</div>
<?= $this->Panel->end(); ?>
<?php if (!$globalStats): ?>
    <?= $this->Panel->create('Kurse', ['body' => false]) ?>
    <table class="table">
        <thead>
        <?= $this->Html->tableHeaders(['Kurs', 'Teilnehmende', 'Limit', 'Belegung', 'Geschlechter']) ?>
        </thead>
        <tbody>
        <?php foreach ($coursesData as $courseData): ?>
            <tr>
                <td><?= h($courseData['name']) ?></td>
                <td><?= $courseData['users_cnt'] ?></td>
                <?php $wlLength = $courseData['waiting_list_length']; if ($wlLength == -1) $wlLength = "&infin;";?>
                <td title="<?= "{$courseData['max_users']} Plätze + {$wlLength} Warteliste" ?>"><?= "{$courseData['max_users']}+{$wlLength}" ?></td>
                <?php
                $occupiedPercentage = $courseData['max_users'] == 0 ? 0 : round(($courseData['users_cnt'] / $courseData['max_users']) * 100);
                $occupiedPercentageText = $courseData['max_users'] == 0 ? '&infin;' : "$occupiedPercentage%";
                $malePercentage = $courseData['users_cnt'] == 0 ? 0 : round(($courseData['male_users_cnt'] / $courseData['users_cnt']) * 100);
                $femalePercentage = $courseData['users_cnt'] == 0 ? 0 : round(($courseData['female_users_cnt'] / $courseData['users_cnt']) * 100);
                ?>
                <td>
                    <div class="progress progress-text-centered">
                        <div class="progress-bar" role="progressbar" aria-valuenow="<?= $occupiedPercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $occupiedPercentage ?>%;">
                            <span><?= $occupiedPercentageText ?></span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="progress">
                        <div class="progress-bar progress-bar-male" role="progressbar" aria-valuenow="<?= $malePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $malePercentage ?>%">
                            <span><?= $malePercentage ?>% ♂</span>
                        </div>
                        <div class="progress-bar progress-bar-female" role="progressbar" aria-valuenow="<?= $femalePercentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $femalePercentage ?>%">
                            <span><?= $femalePercentage ?>% ♀</span>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->Panel->end(); ?>

    <?= $this->Panel->create('Abgaben', ['body' => false]) ?>
    <table class="table">
        <thead>
        <?= $this->Html->tableHeaders(['Aufgabe', 'Abgaben', 'Anteil']) ?>
        </thead>
        <tbody>
        <?php foreach ($uploadsData as $name => $cnt): ?>
            <tr>
                <td><?= h($name) ?></td>
                <td><?= $cnt ?></td>
                <td><?= $this->Html->progress($groupCount == 0 ? 0 : round(($cnt / $groupCount) * 100), ['display' => true, 'class' => 'progress-text-centered']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->Panel->end() ?>
<?php endif; ?>
<?= $this->Panel->create('Herkunftskarte', ['collapsible' => true, 'open' => false, 'class' => 'show-chevron']) ?>
<div class="row">
    <div class="col-lg-8">
        <?php
        $defaultDotSize = 42;
        $defaultRegion = 'germany';
        ?>
        <img id="originMap" src="<?= $this->Url->build(['action' => 'getMap', '?' => ['region' => $defaultRegion, 'dotSize' => $defaultDotSize]]) ?>" width="100%" />
    </div>
    <div class="col-lg-4">
        <?php
        echo $this->Form->create(null);
        ?><legend>Einstellungen</legend><?php
        echo $this->Form->control('mapDotSize', [
            'label' => 'Radius',
            'id' => 'mapDotSize',
            'options' => [12 => '12', 22 => '22', 32 => '32', 42 => '42', 52 =>'52'],
            'value' => $defaultDotSize
        ]);
        echo $this->Form->control('mapRegion', [
            'label' => 'Gebiet',
            'id' => 'mapRegion',
            'options' => ['germany' => 'Deutschland', 'lower-saxony' => 'Niedersachsen', 'nrw' => 'NRW'],
            'value' => $defaultRegion
        ]);
        echo $this->Form->end();
        ?>
        <?= $this->Html->link('In neuem Fenster', ['action' => 'getMap', '?' => ['region' => 'germany', 'dotSize' => 42]], ['target' => '_blank']) ?>
    </div>
</div>
<?= $this->Panel->end() ?>
<?php if (!$globalStats): ?>
    <?= $this->Panel->create('Zusatzfelder', ['body' => false]) ?>
    <table class="table table-striped table-hover">
        <thead>
        <?= $this->Html->tableHeaders(['Wert', 'Anzahl', 'Anteil']) ?>
        </thead>
        <tbody>
        <?php foreach ($customFieldsData as $customField => $data): ?>
            <tr>
                <td colspan="3"><b><?= h($customField) ?></b></td>
            </tr>
            <?php foreach ($data as $item): ?>
                <tr>
                    <?php
                        //TODO: Show section?
                        $val = h($item['value']);
                        switch ($item['type']) {
                            case CustomFieldType::Checkbox:
                            case CustomFieldType::AgbCheckbox:
                                if ($val == 0) {
                                    continue 2; //exclude "unchecked", it's obvious from "checked", "2" because for php "switch" is a loop...
                                } else if ($val == 1) {
                                    $val = "{$this->Html->icon('check')} (Ausgewählt)";
                                }
                                break;
                            default:
                                if ($val === '') {
                                    $val = '<i>(Kein Wert)</i>';
                                }
                                break;
                        }
                        echo "<td>$val</td>";
                    ?>
                    <td><?= $item['cnt'] ?></td>
                    <td><?= $this->Html->progress(round($item['cnt'] / $usersTotal * 100), ['class' => 'progress-text-centered']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $this->Panel->end() ?>
<?php endif; ?>
<?php
if (\Cake\Core\Configure::read('debug')) {
    $this->Html->scriptBlock(<<<JS
    $(function() {
        "use strict";
        Chart.defaults.global.animation.duration = 0; //disable anoing animations (at least) in debug mode
    });
JS
        , ['block' => true]);
}
$this->Html->scriptBlock(<<<JS
    $(function() {
        "use strict";
        //charts
        moment.locale('de')
        var defaultColors = [ '#97BBCD', '#DCDCDC', '#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360'];
        new Chart($('#chartUsersSex'), {
            type: 'doughnut',
            data: {
                labels: ['Männlich', 'Weiblich', 'Sonstiges'],
                datasets: [
                    {
                        data: [$usersMale, $usersFemale, $usersOther],
                        backgroundColor: ['#69c', '#9fc777'],
                    }
                ]
            }
        });
        new Chart($('#chartUsersTeachers'), {
            type: 'doughnut',
            data: {
                labels: ['Schülerinnen und Schüler', 'Lehrkräfte'],
                datasets: [
                    {
                        data: [$usersNonTeacher, $teacherCount],
                        backgroundColor: defaultColors,
                    }
                ]
            }
        });
        new Chart($('#chartHistory'), {
            type: 'line',
            options: {
                legend: {display: false},
                title: {display: true, text: 'Anmeldungen (kummuliert)'},
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {parser: 'YYYY-MM-DD'}
                    }],
                    yAxes: [{
                        position: 'left',
                        ticks: {beginAtZero: true}
                    }]
                }
            },
            data: {
                labels: {$historyData['labels']},
                datasets: [
                    {
                        data: {$historyData['data']},
                        label: 'Anmeldungen',
                        lineTension: 0.2 //0 for straight line, 10 for funny charts ;)
                    }
                ]
            }
        })
    });
    //maps
    $('#mapDotSize, #mapRegion').on('change', function (e) {
        var dotSize = $('#mapDotSize').val();
        var region = $('#mapRegion').val();
        var baseUrl = '{$this->Url->build(['action' => 'getMap'])}';
        $('#originMap').attr('src', baseUrl + '?region=' + region + '&dotSize=' + dotSize);
    })
JS
    , ['block' => true]);
?>
