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
		'phrases' => array(
			'global' => array('lehrauftraegeAnnehmen'),
			'dms' => array('informationsblattExterneLehrende')
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
								<td class="col-xs-1"><img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px;"></td>
								<td class="col-xs-1"><i class='fa fa-handshake-o'></i></td>
							</tr>
							<tr class="text-center">
								<td><b>Alle</b><br>Alle Lehraufträge mit jedem Status</td>
								<td><b>Erteilt</b><br>Nur erteilte UND geänderte Lehraufträge, die in Bearbeitung sind</td>
								<td><b>Angenommen</b><br>Nur von Ihnen angenommene Lehraufträge</td>
							</tr>
						</table>
						</div>
					</div>
					<br>
				</div>
			</div>
		</div>

		<!-- dropdown widgets -->
		<div class="row">
			<div class="col-lg-12">
				<form id="formLehrauftrag" class="form-inline" action="" method="get">
					<input type="hidden" id="uid" name="uid" value="<?php echo getAuthUID(); ?>">
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

		<!-- tabulator data table 'Lehrauftraege annehmen'-->
		<div class="row">
			<div class="col-lg-12">
				<?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragData.php'); ?>
			</div>
		</div>
		<br>

		<!-- link for external lectors 'Informationsblatt fuer externe Lehrende'. Show only for external lecturers -->
		<?php if ($is_external_lector): ?>
		<div class="row">
			<div class="col-xs-12">
				<span class="pull-right"><?php echo $this->p->t('dms' , 'informationsblattExterneLehrende'); ?></span>
			</div>
		</div>
		<br>
		<?php endif; ?>

		<!-- filter buttons & password field & akzeptieren-button -->
		<div class="row">
			<div class="col-xs-5 col-md-4">
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group" role="group">
						<button id="show-all" class="btn btn-default btn-lehrauftrag active focus" type="button"
								data-toggle="tooltip" data-placement="left" title="Alle anzeigen"><i class='fa fa-users'></i>
						</button>
						<button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button"
								data-toggle="tooltip" data-placement="left" title="Nur erteilte anzeigen">
						</button><!-- png img set in javascript -->
						<button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button"
								data-toggle="tooltip" data-placement="left" title="Nur angenommene anzeigen"><i class='fa fa-handshake-o'></i>
						</button>
					</div>

					<button id="show-cancelled" class="btn btn-default btn-lehrauftrag" type="button" style="margin-left: 20px;"
							data-toggle="collapse" data-placement="left" title="Stornierte anzeigen"
							data-target ="#collapseCancelledLehrauftraege" aria-expanded="false" aria-controls="collapseExample">
					</button><!-- png img set in javascript -->
				</div>
			</div>


			<div class="col-xs-3 col-md-offset-2 col-md-2">
					<div class="btn-group dropup pull-right">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Dokumente PDF&nbsp;&nbsp;<i class="fa fa-arrow-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="caret"></span>
					</button>
					<ul id="ul-download-pdf" class="dropdown-menu">
						<li value="etw"><a href="#">PDF Lehrauftr&auml;ge FH</a></li>
						<li value="lehrgang"><a href="#">PDF Lehrauftr&auml;ge Lehrg&auml;nge</a></li>
					</ul>
				</div>
			</div>

			<div class="col-xs-4 col-md-offset-0 col-md-4">
				<div class="input-group">
					<input id="username" type="hidden" value=""><!-- this is to prevent Chrome autofilling a random input field with the username-->
					<input id="password" type="password" autocomplete="new-password" class="form-control" placeholder="CIS-Passwort">
						<span class="input-group-btn">
							<button id="accept-lehrauftraege" class="btn btn-primary pull-right">Lehrauftrag annehmen</button>
						</span>
				</div>
			</div>
		</div>
		<br>
		<br>

		<!-- collapse module with data table 'Stornierte Lehrauftraege' (collapsed by default until opened on buttonclick)-->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseCancelledLehrauftraege">
				<h4>
					<?php echo ucfirst($this->p->t('global', 'stornierteLehrauftraege')); ?>:
					<small>
						<abbr title="Anderes Studiensemester? Bitte oben im Dropdown wählen." >
							<?php echo $studiensemester_selected ?>
						</abbr>
					</small>
				</h4>
				<div class="row">
					<div class="col-lg-12">
						<?php $this->load->view('lehre/lehrauftrag/cancelledLehrauftragData.php'); ?>
					</div>
				</div>
				<br>
			</div>
		</div>
	</div><!-- end container -->
</div><!-- end page-wrapper -->
<br>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
