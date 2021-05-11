<?php
/**
 * Note: This control must only be used once per page, otherwise the JavaScript
 * will break.
 */
/** @var \App\Model\Entity\Course[] $courses */
/** @var int[] $checkedCourses */
/** @var \App\View\AppView $this */
/** @var bool $hideFreeSlots */

$usedFilters = collection($courses)->map(function ($elm) {
    /** @var \App\Model\Entity\Course $elm */
    return $elm->getTagNamesByPrefix('filter_');
})->reduce(function ($acc, $elm) {
    return array_unique(array_merge($acc, $elm));
}, []);
sort($usedFilters, SORT_NATURAL);

if ($usedFilters):
?>
    <p>Bitte wählen Sie eine der folgenden Kategorien. Sie können nur Kurse aus einer Kategorie wählen.</p>
    <?= $this->Form->control('filter', [
        'label' => 'Kategorie',
        'options' => array_merge(['please_select' => '--- Bitte wählen ---'], array_combine($usedFilters, $usedFilters)),
    ]) ?>
<?php endif; ?>
<noscript>
    <div class="alert alert-warning">
        <?= $this->Html->icon('exclamation-sign') ?>
        <b>Hinweis:</b> Sie haben JavaScript deaktivert. Dadurch können Sie eine ungültige Kurswahl treffen. In diesem Fall können wir Ihre
        Anmeldung leider nicht berücksichtigen.
        <a href="http://www.enable-javascript.com/de/" target="_blank">Anleitung wie Sie JavaScript in Ihrem Browser einschalten</a>.
    </div>
</noscript>
<table class="table table-hover" id="modules">
    <thead>
    <?= $this->Html->tableHeaders(array_merge(['#', 'Name'], $hideFreeSlots ? [] : ['Freie Plätze'])) ?>
    </thead>
    <tbody>
    <?php
    echo $this->Form->control('courses._ids', [
        'type' => 'hidden',
        'value' => ''
    ]);
    $rows = collection($courses)->map(function ($elm) use ($checkedCourses, $hideFreeSlots) {
        /** @var \App\Model\Entity\Course $elm */
        $forced = $elm->hasTag('forced');
        return [
            'checkbox' => $this->Form->checkbox('_unused', [ //this somehow simulates Form->control('courses._ids', ['multiple' => 'checkbox]), generating a marshaller-compatible form, but is more flexible
                'name' => 'courses[_ids][]',
                'value' => $elm->id,
                'hiddenField' => $forced ? $elm->id : false, //disabled fields will not be POSTed, so we need a hidden filed here
                'checked' => in_array($elm->id, $checkedCourses) || $forced,
                'class' => $forced ? 'make-readonly' : '', //setting "disabled => true" will cause the hidden field to become disabled, too :( so we do this later in JS
                'disabled' => !$elm->isInRegisterTimeframe() && !in_array($elm->id, $checkedCourses) //unselected, closed courses can be disabled
            ]),
            'id' => $elm->id,
            'name' => $elm->name,
            'desc' => $elm->getDescriptionForDisplay(),
            'free' => $elm->getNextFreeSlotInfo(),
            'isChecked' => in_array($elm->id, $checkedCourses),
            'exgroups' => $elm->getTagNamesByPrefix('exgroup_'),
            'filters' => $elm->getTagNamesByPrefix('filter_'),
            'hideFreeSlots' => $hideFreeSlots == true ? true : $elm->getTagValue('hideFreeSlots')
        ];
    })->toArray();
    $freeInfoMap = [
        'normal' => 'Noch Plätze frei',
        'waiting_list' => '<span class="text-warning">Warteliste</span>',
        'full' => '<span class="text-danger">Alle Plätze belegt</span>',
        'closed' => '<span class="text-muted">Anmeldung abgelaufen</span>'
    ];
    foreach($rows as $row) : ?>
        <tr class="<?= $row['isChecked'] ? 'info' : '' ?>"
            data-exgroups="<?= implode(',', $row['exgroups']) ?>" data-cid="<?= $row['id'] ?>" data-filters="<?= implode(',', $row['filters']) ?>">
            <td><?= $row['checkbox'] /* no h(), this is HTML */ ?></td>
            <td>
                <b><?= h($row['name']) ?></b>
                <?= $row['desc'] ?>
            </td>
            <td><?php if ($row['hideFreeSlots'] != true) echo $freeInfoMap[$row['free']]; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php
    /*echo $this->Form->control('courses._ids', [ //"official" version
        'multiple' => 'checkbox',
        'options' => $courses
    ]);*/
    ?>
    </tbody>
</table>
<?php
$this->Html->scriptBlock(<<<'JS'
(function(){
"use strict";
    $(function() {
        var table = $("#modules tbody");
        var exgroups = {};
        var filters = {};

        $(':checkbox.make-readonly').attr('disabled', true);

        function applyExGroup(id) {
            table.find("tr").removeClass("danger");

            var exgroupsForCurrentRow = exgroups[id];
            var showNotice = false;
            if (table.find("tr[data-cid="+id+"] :checkbox").prop("checked")) {
                for (var cid in exgroups) {
                    var currentCheckbox = table.find("tr[data-cid="+cid+"] :checkbox");
                    if (id != cid && currentCheckbox.prop("checked")) {
                        var intersection = $(exgroupsForCurrentRow).filter($(exgroups[cid]));
                        if (intersection.length > 0) {
                            currentCheckbox.prop("checked", false);
                            currentCheckbox.closest("tr").removeClass("info").addClass("danger");
                            showNotice = true;
                        }
                    }
                }
            }
            if (showNotice) {
                alert("Achtung: Überschneidung von Modulen! Das abgewählte Modul ist rot markiert. Bitte prüfen Sie Ihre Auswahl.");
            }
        }

        table.find("tr").click(function(event) {
            if (event.target.type !== 'checkbox') {
                $(':checkbox:not([disabled])', this).trigger('click');
            }
        }).each(function() {
            exgroups[$(this).data('cid')] = $(this).data('exgroups').split(',').filter(function (e) {
                return e.length > 0;
            });
            filters[$(this).data('cid')] = $(this).data('filters').split(',').filter(function (e) {
                return e.length > 0;
            });
        });
        table.find("input[type='checkbox']").change(function() {
            var tr = $(this).closest("tr");
            tr.toggleClass("info", $(this).prop("checked"));
            applyExGroup(tr.data("cid"));
        });
        $('#filter').on('change', function() {
            var val = $(this).val();

            if (val == "please_select") { //hide everything when "please select an entry" was selected
                table.find("tr").css({display: 'none'});
                table.find("tr :checkbox:not([disabled])").prop('checked', false).trigger('change');
                return;
            }

            $.each(filters, function (cid, filters) {
                var currentRow = table.find("tr[data-cid="+cid+"]");
                if (filters.length == 0 || filters.indexOf(val) >= 0) {
                    currentRow.css({display: ''});
                } else {
                    currentRow.find(':checkbox:not([disabled])').prop('checked', false).trigger('change'); //deselect when hiding
                    currentRow.css({display: 'none'});
                }
            });
        }).trigger('change');
    })
}())
JS
    , ['block' => true]);
?>