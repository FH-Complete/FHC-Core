<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Fehler Zuständigkeiten',
		'jquery3' => true,
		'jqueryui1' => true,
		'jquerycheckboxes1' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'tablesorter2' => true,
		'ajaxlib' => true,
		'filterwidget' => true,
		'navigationwidget' => true,
		'dialoglib' => true,
		'phrases' => array(
			'ui',
			'fehlermonitoring'
		),
		'customCSSs' => array('public/css/issues/issuesZustaendigkeiten.css', 'public/css/sbadmin2/tablesort_bootstrap.css'),
		'customJSs' => array('public/js/issues/issuesZustaendigkeiten.js', 'public/js/bootstrapper.js')
	)
);
?>

<body>
<div id="wrapper">

	<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						<?php echo $this->p->t('fehlermonitoring', 'fehlerZustaendigkeiten') ?>
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4">
					<div class="panel panel-default">
						<table class="table table-bordered" id="fehlercodeSelectTable">
							<tr>
								<td class="tableCellNoRightBorder">
									<label for="fehlerappSelect">App</label>
									<select class="form-control" name="fehlerappSelect" id="fehlerappSelect">
									</select>
								</td>
								<td class="tableCellNoLeftBorder tableCellNoRightBorder">
									<label for="fehlercodeSelect"><?php echo $this->p->t('fehlermonitoring', 'fehlercode') ?></label>
									<select class="form-control" name="fehlercodeSelect" id="fehlercodeSelect">
									</select>
								</td>
								<td class="tableCellNoLeftBorder" id="fehlercodeInfoCell"><i class="fa fa-info-circle"></i></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-lg-8">
					<div class="panel panel-default">
						<table class="table table-bordered">
							<tr>
								<td>
									<label for="mitarbeiterSelect"><?php echo $this->p->t('fehlermonitoring', 'zustaendigerMitarbeiter') ?></label>
									<input type="text" class="form-control" name="mitarbeiterSelect" id="mitarbeiterSelect">
									<input type="hidden" name="mitarbeiter_person_id" id="mitarbeiter_person_id">
								</td>
								<td align="center">
									<?php echo $this->p->t('fehlermonitoring', 'oder') ?>
									<br>
									<i class="fa fa-arrows-h"></i>
								</td>
								<td class="tableCellNoRightBorder">
									<label for="oeSelect"><?php echo $this->p->t('fehlermonitoring', 'organisationseinheit') ?></label>
									<select class="form-control" name="oeSelect" id="oeSelect">
									</select>
								</td>
								<td class="tableCellNoLeftBorder">
									<label for="funktionSelect"><?php echo $this->p->t('fehlermonitoring', 'funktion') ?></label>
									<select class="form-control" name="funktionSelect" id="funktionSelect">
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-offset-3 col-lg-9">
					<button class="btn btn-default" id="assignZustaendigkeit">
						<i class="fa fa-angle-double-right"></i>&nbsp;
						<?php echo $this->p->t('fehlermonitoring', 'zustaendigkeitZuweisen') ?>
					</button>
				</div>
			</div>
			<div>
				<?php $this->load->view('system/issues/issuesZustaendigkeitenData.php'); ?>
			</div>
			<div class="modal fade" id="fehlerInfo" tabindex="-1"
				 role="dialog"
				 aria-labelledby="fehlerInfoLabel"
				 aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close"
									data-dismiss="modal"
									aria-hidden="true">&times;
							</button>
							<h4 class="modal-title" id="fehlerInfoLabel">
							</h4>
						</div>
						<div class="modal-body" id="fehlerInfoContent">
							<table class="table table-condensed table-bordered">
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'fehlercode')) ?></b>
									</td>
									<td>
										<span id="fehlercodeInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'fehlerkurzbz')) ?></b>
									</td>
									<td>
										<span id="fehlerkurzbzInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'fehlertyp')) ?></b>
									</td>
									<td>
										<span id="fehlertypInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'fehlercodeExtern')) ?></b>
									</td>
									<td>
										<span id="fehlercodeExternInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'fehlertext')) ?></b>
									</td>
									<td>
										<span id="fehlertextInfo"></span>
									</td>
								</tr>
							</table>
						</div>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal-fade -->
		</div>
	</div>

</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
