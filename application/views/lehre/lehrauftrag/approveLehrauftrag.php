<?php
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
        'dialoglib' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array('lehrauftraegeErteilen'),
        ),
        'customJSs' => array(
                'public/js/bootstrapper.js',
                'public/js/lehre/lehrauftrag/approveLehrauftrag.js'
        )
    )
);

?>

<body>
    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>

    <div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraegeErteilen')); ?>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form id="formLehrauftrag" class="form-inline" action="" method="get">
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
                            'Organisationseinheit_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $organisationseinheit_selected,
                                'organisationseinheit' => $organisationseinheit
                            ),
                            array(
                                'name' => 'organisationseinheit',
                                'id' => 'organisationseinheit'
                            )
                        );
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        echo $this->widgetlib->widget(
                            'Ausbildungssemester_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $ausbildungssemester_selected,
                                'number_semester' => 6
                            ),
                            array(
                                'name' => 'ausbildungssemester',
                                'id' => 'ausbildungssemester'
                            )
                        );
                        ?>
                    </div>
                    <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">Anzeigen</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lehrauftrag/approveLehrauftragData.php'); ?>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-xs-12">
                <button id="approve-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag erteilen</button>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-all" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Alle anzeigen"><i class='fa fa-users'></i></button>
                        <button id="show-new" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur neue anzeigen"><i class='fa fa-user-plus'></i></button>
                        <button id="show-ordered" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="Nur bestellte anzeigen"></button><!-- png img set in javascript -->
                        <button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur erteilte anzeigen"></button><!-- png img set in javascript -->
                        <button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur angenommene anzeigen"><i class='fa fa-handshake-o'></i></button>
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-changed" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur geänderte anzeigen"></button><!-- png img set in javascript -->
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-dummies" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur verplante ohne Lektor anzeigen (Dummies)"><i class='fa fa-user-secret'></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
