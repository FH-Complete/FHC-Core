<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'ÖH-Beitragsverwaltung',
		'jquery3' => true,
		'jqueryui1' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'tablesorter2' => true,
		'dialoglib' => true,
		'ajaxlib' => true,
		'navigationwidget' => true,
		'phrases' => array(
			'person' => array('vorname', 'nachname'),
			'global' => array('unbeschraenkt'),
			'ui' => array('bearbeiten', 'loeschen', 'speichern', 'entfernen'),
			'oehbeitrag' => array('oehbeitraegeFestgelegt', 'fehlerHolenOehbeitraege', 'fehlerHolenSemester',
				'fehlerHinzufuegenOehbeitrag', 'fehlerAktualisierenOehbeitrag',
				'fehlerLoeschenOehbeitrag')
		),
		'customCSSs' => array('public/css/sbadmin2/tablesort_bootstrap.css', 'public/css/codex/oehbeitrag.css'),
		'customJSs' => array('public/js/tablesort/tablesort.js', 'public/js/codex/oehbeitrag.js')
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
						<?php echo $this->p->t('oehbeitrag', 'oehbeitragsVerwaltung') ?>
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<button class="btn btn-default" id="addNewOeh"><?php echo $this->p->t('oehbeitrag', 'oehbeitragHinzufuegen') ?></button>
					<br />
					<br />
					<table class="table table-bordered table-condensed" id="oehbeitraegeTbl">
						<thead>
							<tr>
								<th><?php echo ucfirst($this->p->t('global', 'gueltigVon')) ?></th>
								<th><?php echo ucfirst($this->p->t('global', 'gueltigBis')) ?></th>
								<th><?php echo ucfirst($this->p->t('oehbeitrag', 'studierendenbetrag')) ?></th>
								<th><?php echo ucfirst($this->p->t('oehbeitrag', 'versicherungsbetrag')) ?></th>
								<th id="actionHeading"><?php echo ucfirst($this->p->t('ui', 'aktion')) ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
