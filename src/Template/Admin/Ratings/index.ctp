<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Project $project */
/** @var \App\Model\Entity\Group[] $groups */
/** @var \App\Model\Entity\Course[] $courses */
/** @var int[][] $tabRatingCount */
/** @var int[] $tabScaleCount */

$this->extend('/Common/page');
$this->assign('title', "Bewertungen <small>{$project->name}</small>");

?>
<p>Klicken Sie auf einen Eintrag in der Tabelle, um die entsprechenden Bewertungen einzutragen.</p>
<div id="color_legend" class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" href="#collapse1" data-parent="#color_legend" aria-expanded="false">Farblegende</a>
            </h4>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
            <table class="table">
                <tbody>
                    <tr><td><i>noch keine Skalen eingetragen</i></td></tr>
                    <tr><td class="danger"><i>Skalen eingetragen, aber noch keine Dateien hochgeladen</i></td></tr>
                    <tr><td class="warning"><i>Dateien hochgeladen, aber noch keine Aufgaben bewertet</i></td></tr>
                    <tr><td class="info"><i>Aufgaben teilweise bewertet</i></td></tr>
                    <tr><td class="success"><i>alle Aufgaben bewertet</i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<table class="table table-striped table-hover table-condensed ratings-table">
    <thead>
        <?= $this->Html->tableHeaders(array_merge(['Gruppe'], $courses->extract('name')->toArray())) ?>
    </thead>
    <tbody>
        <?php $totalRatings = 0; $totalScales = 0; ?>
        <?php foreach ($groups as $group): ?>
            <tr>
                <td><?= h($group->name) ?></td>
                <?php
                foreach ($courses as $course) {
                    $r = $tabRatingCount[$group->id][$course->id];
                    $s = $tabScaleCount[$course->id];
                    $f = $uploadedFilesCount[$group->id][$course->id];
                    $totalRatings += $r;
                    $totalScales += $s;
                    if ($r === 0 && $s === 0) {
                        $class = '';
                    }  else if ($r === 0 && $s !== 0 && $f === 0) {
                        $class = 'danger';
                    } else if ($r === 0 && $s !== 0 && $f !== 0) {
                        $class = 'warning';
                    } else if ($r !== 0 && $r < $s) {
                        $class = 'info';
                    } else {
                        $class = 'success';
                    }
                    $content = $this->Html->link("{$r}/{$s}", ['action' => 'edit', $group->id, $course->id]);
                    echo "<td class='{$class}'>{$content}</td>";
                }
                ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot >
        <tr>
            <td><b>Exportieren als CSV</b></td>
            <?php
            foreach ($courses as $course) {
                $filename = $project->urlname."-".$course->id;
                echo "<td>";
                $action_link = strip_tags(
                    $this->Chell->actionLink('file', 'Download',
                        ['controller' => 'exports', 'action' => 'ratings', '_ext' => 'csv', '?' => ['cid' => $course->id], $filename]), "<a><i>");
                echo $action_link;
                echo "</td>";
            }
            ?>
        </tr>
        <tr>
            <td><b>Exportieren als XLSX</b></td>
            <?php
            foreach ($courses as $course) {
                $filename = $project->urlname."-".$course->id;
                echo "<td>";
                $action_link = strip_tags(
                    $this->Chell->actionLink('file', 'Download',
                        ['controller' => 'exports', 'action' => 'ratings', '_ext' => 'xlsx', '?' => ['cid' => $course->id], $filename]), "<a><i>");
                echo $action_link;
                echo "</td>";
            }
            ?>
        </tr>
    </tfoot>
</table>
<?php if ($totalScales > 0): ?>
<b>Summe</b>: <?= "{$totalRatings}/{$totalScales} ({$this->Number->toPercentage(100 * $totalRatings/$totalScales, 0)})" ?>
<?php else: ?>
<div class="alert alert-warning"><b>Hinweis</b>: In diesem Projekt sind keine Skalen vorhanden.</div>
<?php endif; ?>
