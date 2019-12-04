<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag annehmen',
        'jquery' => true,
        'jqueryui' => true,
        'jquerycheckboxes' => true,
        'bootstrap' => true,
        'fontawesome' => true,
        'sbadmintemplate' => false,
        'tabulator' => true,
        'momentjs' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array('lehrauftraegeAnnehmen'),
        ),
        'customJSs' => array(
                'public/js/bootstrapper.js',
                'public/js/lehre/lehrauftrag/acceptLehrauftrag.js')
    )
);

?>

<body>
<div id="page-wrapper">
    <div class="container-fluid">

		<!-- title & helper link -->
        <div class="row">
            <div class="col-lg-12 page-header">
				<a class="pull-right" data-toggle="collapse" href="#collapseHelp" aria-expanded="false" aria-controls="collapseExample">
					Hilfe zu dieser Seite
				</a>
				<h3>
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?>
                </h3>
			</div>
        </div>

		<!-- helper collapse module -->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseHelp">
				<div class="well">
					<h4>Wie nehme ich Lehraufträge an?</h4>
					<div class="panel panel-body">
					<p>
						Sobald Ihnen ein oder mehrere Lehraufträge erteilt wurden, können Sie diese annehmen.
						<ol>
							<li>Klicken Sie unten auf das Status-Icon 'Nur erteilte anzeigen' oder 'Alle anzeigen'</li>
							<li>Wählen Sie einzelne Lehraufträge mit Klick auf die Zeilen oder alle über den Button 'Alle auswählen'.</li>
							<li>Geben Sie Ihr CIS-Passwort ein und klicken auf Lehrauftrag annehmen.</li>
						</ol>
					</p>
					</div>
					<br>
					<h4>Warum kann ich manche Lehraufträge nicht auswählen?</h4>
					<div class="panel panel-body">
					<p>
						Nur Lehraufträge mit dem Status 'erteilt' können gewählt werden.<br>
						Angenommene Lehraufträge oder Lehraufträge in Bearbeitung werden nur zu Ihrer Information angezeigt.
					</p>
					</div>
				</div>
			</div>
		</div>

		<!-- dropdown widgets -->
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
                    <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">Anzeigen</button>
                </form>
            </div>
        </div>

		<!-- tabulator data table -->
        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragData.php'); ?>
            </div>
        </div>
        <br>

		<!-- filter buttons & password field & akzeptieren-button -->
        <div class="row">
            <div class="col-xs-6">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-all" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="Alle anzeigen"><i class='fa fa-users'></i></button>
                        <button id="show-ordered" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur bestellte anzeigen"></button><!-- png img set in javascript -->
                        <button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur erteilte anzeigen"></button><!-- png img set in javascript -->
                        <button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="Nur angenommene anzeigen"><i class='fa fa-handshake-o'></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-offset-2 col-md-4 col-xs-6">
                <div class="input-group">
                    <input id="password" type="password" class="form-control" placeholder="CIS-Passwort">
                    <span class="input-group-btn">
                        <button id="accept-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag annehmen</button>
                    </span>
                </div>
            </div>
        </div>
		
    </div><!-- end container -->
</div><!-- end page-wrapper -->
<br>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
