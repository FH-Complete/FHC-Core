<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Fehler Konfiguration',
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
		'customCSSs' => array('public/css/issues/issuesKonfiguration.css', 'public/css/sbadmin2/tablesort_bootstrap.css'),
		'customJSs' => array('public/js/issues/issuesKonfiguration.js', 'public/js/bootstrapper.js')
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
						<?php echo $this->p->t('fehlermonitoring', 'fehlerKonfiguration') ?>
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4">
					<div class="panel panel-default">
						<table class="table table-bordered" id="fehlercodeSelectTable">
							<tr>
								<td>
									<label for="fehlerappSelect">App</label>
									<select class="form-control" name="fehlerappSelect" id="fehlerappSelect">
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col-lg-8">
					<div class="panel panel-default">
						<table class="table table-bordered">
							<tr>
								<td class="tableCellNoRightBorder">
									<label for="konfigSelect"><?php echo $this->p->t('fehlermonitoring', 'konfigurationstyp') ?></label>
									<select class="form-control" name="konfigSelect" id="konfigSelect">
									</select>
									<i class="fa fa-info-circle" id="konfigurationstypInfoIcon"></i>
								</td>
								<td class="tableCellNoLeftBorder">
									<label for="fehlercodeSelect"><?php echo $this->p->t('fehlermonitoring', 'fehlercode') ?></label>
									<select class="form-control" name="fehlercodeSelect" id="fehlercodeSelect">
									</select>
									<i class="fa fa-info-circle" id="fehlercodeInfoIcon"></i>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12 text-center">
					<div class="input-group">
						<input type="text" class="form-control" name="konfigurationsWert" id="konfigurationsWert"
							placeholder="<?php echo $this->p->t('fehlermonitoring', 'konfigurationswertPlatzhalter') ?>">
						<div class="input-group-btn">
							<button class="btn btn-default" id="assignKonfiguration">
								<?php echo $this->p->t('fehlermonitoring', 'konfigurationswertZuweisen') ?>
							</button>
							<button class="btn btn-default" id="deleteKonfiguration">
								<?php echo $this->p->t('fehlermonitoring', 'konfigurationswertLoeschen') ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div>
				<?php $this->load->view('system/issues/issuesKonfigurationData.php'); ?>
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
			<div class="modal fade" id="konfigurationsInfo" tabindex="-1"
				 role="dialog"
				 aria-labelledby="konfigurationstypInfoLabel"
				 aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close"
									data-dismiss="modal"
									aria-hidden="true">&times;
							</button>
							<h4 class="modal-title" id="konfigurationstypInfoLabel">
							</h4>
						</div>
						<div class="modal-body" id="konfigurationstypInfoContent">
							<table class="table table-condensed table-bordered">
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'konfigurationstyp')) ?></b>
									</td>
									<td>
										<span id="konfigurationstypInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'konfigurationsbeschreibung')) ?></b>
									</td>
									<td>
										<span id="konfigurationsbeschreibungInfo"></span>
									</td>
								</tr>
								<tr>
									<td>
										<b><?php echo ucfirst($this->p->t('fehlermonitoring', 'konfigurationsdatentyp')) ?></b>
									</td>
									<td>
										<span id="konfigurationsdatentypInfo"></span>
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
