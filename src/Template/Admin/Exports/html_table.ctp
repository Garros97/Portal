<?php
/** @var \App\View\AppView $this */
/** @var \Cake\ORM\Query $query */
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $title !== null ? $title : 'Datenexport' ?>
    </title>
    <?php
    $this->Html->meta('icon', ['block' => true]);

    $min = \Cake\Core\Configure::read('debug') ? '' : '.min';

    $this->Html->css("//cdn.datatables.net/1.10.12/css/jquery.dataTables$min.css", [
        'block' => true,
        'integrity' => 'sha384-P+WS5pp0ZFKWzz+jS2p79OLvoELB6xhOd6pFdmitzQ/LEssVfyVDb3MkdptpZz9g sha384-YcTv91pbdpZ4It88TK5bVHIGTuPqoSi0CpPF9UA9eRicGHEJ3lpQZajpytN4rLkp',
        'crossorigin' => 'anonymous'
    ]);

    $this->Html->script("//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery$min.js", [
        'block' => 'libs',
        'integrity' => 'sha384-KcyRSlC9FQog/lJsT+QA8AUIFBgnwKM7bxm7/YaX+NTr4D00npYawrX0h+oXI3a2 sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ',
        'crossorigin' => 'anonymous'
    ]);
    $this->Html->script("//cdn.datatables.net/1.10.12/js/jquery.dataTables$min.js",[
        'block' => true,
        'integrity' => 'sha384-89aj/hOsfOyfD0Ll+7f2dobA15hDyiNb8m1dJ+rJuqgrGR+PVqNU8pybx4pbF3Cc sha384-B1mNYJZ3S0vNakgQJosO3/e5TA1SKAqSlESNbXWK5tinm0eNnDw60LfFZI33E2Q2',
        'crossorigin' => 'anonymous'
    ]);
    ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script_head') ?>
</head>
<body>
    <table id="export-data" class="cell-border">
        <thead>
            <?= $this->Html->tableHeaders(array_keys($query->all()->first())) ?>
        </thead>
        <tbody>
            <?php foreach ($query as $row) {
                echo $this->Html->tableCells(array_values($row));
            }
            ?>
        </tbody>
<?php
$this->Html->scriptBlock(<<<'JS'
    $(function() {
        "use strict";
        $('#export-data').DataTable({
            "lengthMenu": [[50, 100, 200 , -1], [50, 100, 200, "Alle"]],
            "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
        }
        });
    });
JS
    , ['block' => true]);
?>
    <?= $this->fetch('libs') //make sure libs are always loaded first, some other (inline-)scripts depend on it ?>
    <?= $this->fetch('script') ?>
    </table>
</body>
</html>