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
            'global' => array('lehrauftraege'),
        ),
        'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
        'customJSs' => array('public/js/bootstrapper.js')
    )
);
?>

<body>
<div id="page-wrapper">
    <div class="container-fluid">
        <form id="formLehrauftrag" class="form-inline" action="" method="get">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">
                        <?php echo ucfirst($this->p->t('global', 'lehrauftraege')); ?>
                    </h3>
                </div>
                <div class="col-lg-12">
                   <!-- <form id="formLehrauftrag" class="form-inline" action="" method="get">-->
                        <div class="form-group">
                            <?php
                            echo $this->widgetlib->widget(
                                'Studiensemester_widget',
                                array(
                                    DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
                                ),
                                array(
                                    'name' => 'studiensemester',
                                    'id' => 'studiensemester'
                                )
                            );
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            echo $this->widgetlib->widget(
                                'Studiengang_widget',
                                array(
                                    DropdownWidget::SELECTED_ELEMENT => $studiengang_selected,
                                    'studiengang' => $studiengang
                                ),
                                array(
                                    'name' => 'studiengang',
                                    'id' => 'studiengang'
                                )
                            );
                            ?>
                        </div>
                        <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">Anzeigen</button>
                    <!--</form>-->
                </div>
            </div>

            <div class="row">
                <?php $this->load->view('lehre/lehrauftrag/lehrauftragData.php'); ?>
            </div>

            <div class="row">
                <div class="col-lg-12">
                   <!-- <form id="formLehrauftragBestellen" class="form-inline" action="" method="get">-->
                        <button type="submit" name="submit" value="bestellen" class="btn btn-default form-group pull-right">Lehrauftrag bestellen</button>
                   <!-- </form>-->
                </div>
            </div>
        </form>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
