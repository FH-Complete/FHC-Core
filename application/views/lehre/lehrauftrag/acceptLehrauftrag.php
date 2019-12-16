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
						Sobald Ihnen ein oder mehrere Lehraufträge erteilt wurden, können Sie diese annehmen.
						<ol>
							<li>Klicken Sie unten auf das Status-Icon 'Nur erteilte anzeigen' oder 'Alle anzeigen'</li>
							<li>Wählen Sie die Lehraufträge, die Sie annehmen möchten, selbst oder alle über den Button 'Alle auswählen'.</li>
							<li>Geben Sie Ihr CIS-Passwort ein und klicken auf Lehrauftrag annehmen.</li>
						</ol>
					</div>
					<br>
					
					<h4>Warum kann ich manche Lehraufträge nicht auswählen?</h4>
					<div class="panel panel-body">
						Nur Lehraufträge mit dem Status 'erteilt' können gewählt werden.<br>
						Angenommene Lehraufträge oder Lehraufträge in Bearbeitung werden nur zu Ihrer Information angezeigt.
					</div>
					<br>
					
					<h4>Filter</h4>
					<div class="panel panel-body">
						<div class="col-xs-12 col-md-8 col-lg-6">
						<table class="table table-bordered">
							<tr class="text-center">
								<td class="col-xs-1"><i class='fa fa-users'></i></td>
								<td class="col-xs-1"><img src="../../../public/images/icons/fa-user-tag.png" style="height: 30px; width: 30px;"></td>
								<td class="col-xs-1"><img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px;"></td>
								<td class="col-xs-1"><i class='fa fa-handshake-o'></i></td>
							</tr>
							<tr class="text-center">
								<td><b>Alle</b><br>Alle Lehraufträge mit jedem Status</td>
								<td><b>Bestellt</b><br>Nur bestellte UND bestellte Lehraufträge, die in Bearbeitung sind</td>
								<td><b>Erteilt</b><br>Nur erteilte UND geänderte Lehraufträge, die in Bearbeitung sind</td>
								<td><b>Angenommen</b><br>Nur von Ihnen angenommene Lehraufträge</td>
							</tr>
						</table>
						</div>
					</div>
					<br>

					<h4>Auswahl</h4>
					<div class="panel panel-body">
						<ul>
							<li>Einzeln auswählen: <kbd>Strg</kbd> + Klick auf einzelne Zeile(n)</li>
							<li>Bereich auswählen: <kbd>Shift</kbd> + Klick auf Anfangs- und Endzeile</li>
							<li>Alle auswählen: Button 'Alle auswählen'</li>
						</ul>
					</div>
					<br>

					<h4>Ansicht</h4>
					<div class="panel panel-body">
						<b>Spaltenbreite verändern</b>
						<p>
							Um die Spaltenbreite zu verändern, fährt man im Spaltenkopf langsam mit dem Mauszeiger auf
							den rechten Rand der entprechenden Spalte. <br>
							Sobald sich der Mauszeiger in einen Doppelpfeil verwandelt, wird die Maustaste geklickt und
							mit gedrückter Maustaste die Spalte nach rechts erweitert oder nach links verkleinert.
						</p>
					</div>
					<br>
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
