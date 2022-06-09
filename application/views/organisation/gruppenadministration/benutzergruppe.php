<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Benutzer in Gruppe',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'tablesorter' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'filterwidget' => true,
			'navigationwidget' => true,
			'phrases' => array(
				'ui' => array('bitteEintragWaehlen')
			),
			'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/tablesort/tablesort.js', 'public/js/organisation/benutzergruppe.js')
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
							<?php echo ucfirst($this->p->t('gruppenadministration', 'benutzergruppe')).' '.$gruppe_kurzbz ?>
						</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="form-inline">
							<div class="input-group" id="absgstatusgrselect_137998">
								<!-- <label for="mitarbeiterSelect"><php echo $this->p->t('benutzergruppe', 'zustaendigerMitarbeiter') ></label> -->
								<input type="text" class="form-control" name="teilnehmerSelect" id="teilnehmerSelect">
								<input type="hidden" name="teilnehmer_uid" id="teilnehmer_uid">
								<input type="hidden" name="gruppe_kurzbz" id="gruppe_kurzbz" value="<?php echo $gruppe_kurzbz ?>">
								<span class="input-group-btn">
									<button type="button" class="btn btn-default" id="teilnehmerHinzufuegen">
										<?php echo $this->p->t('ui', 'hinzufuegen') ?>
									</button>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table class="table table-bordered table-condensed table-responsive" id="benutzer-table">
							<thead>
								<th>Uid</th>
								<th><?php echo ucfirst($this->p->t('global', 'vorname')); ?></th>
								<th><?php echo ucfirst($this->p->t('global', 'nachname')); ?></th>
								<th><?php echo ucfirst($this->p->t('ui', 'loeschen')); ?></th>
							</thead>
							<tbody>
							<?php foreach ($benutzer as $ben): ?>
								<tr>
									<td><?php echo $ben->uid; ?></td>
									<td><?php echo $ben->vorname; ?></td>
									<td><?php echo $ben->nachname; ?></td>
									<td>
										<button class="btn btn-default benutzerLoeschen" id="benutzerLoeschen">
										<?php echo ucfirst($this->p->t('ui', 'loeschen')); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
