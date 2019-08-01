<?php
// TODO: phrasen anpassen
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag',
        'jquery' => true,
        'jqueryui' => true,
        'jquerycheckboxes' => true,
        'bootstrap' => true,
        'fontawesome' => true,
        'sbadmintemplate' => true,
        'tabulator' => true,
        'momentjs' => true,
        'ajaxlib' => true,
        'filterwidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'person' => array('vorname', 'nachname'),
            'global' => array('mailAnXversandt'),
            'ui' => array('bitteEintragWaehlen')
        ),
        'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
        'customJSs' => array('public/js/bootstrapper.js', '')
    )
);
?>

<body>

<div class="row">
    <form id="formLehrauftragBestellen" class="form-inline" action="" method="get">
        <div class="col-xs-3 form-group">
            <label for="studiengang">Studiengang</label>
            <?php
            echo $this->widgetlib->widget(
                'Studiengang_widget',
                array(
                    DropdownWidget::SELECTED_ELEMENT => $studiengang[0],
                    'studiengang' => $studiengang
                ),
                array(
                    'name' => 'studiengang',
                    'id' => 'studiengang',
                    'class' => 'form-control'
                )
            );
            ?>
        </div>
        <div class="col-xs-2 form-group">
            <label for="studiensemester">Studiensemester</label>
            <?php
            echo $this->widgetlib->widget(
                'Studiensemester_widget',
                array(
                    DropdownWidget::SELECTED_ELEMENT => $studiensemester
                ),
                array(
                    'name' => 'studiensemester',
                    'id' => 'studiensemester',
                    'class' => 'form-control'
                )
            );
            ?>
        </div>
    </form>
</div>
<div class="row">
    <?php $this->load->view('lehre/lehrauftrag/lehrauftragData.php'); ?>
</div>

</body>
<?php $this->load->view('templates/FHC-Footer'); ?>
