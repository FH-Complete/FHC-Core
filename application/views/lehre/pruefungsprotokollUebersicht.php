<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Prüfungsprotokoll',
			'jquery' => true,
			'jqueryui' => true,
			'jquerycheckboxes' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'tablesorter' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'tablewidget' => true,
			'phrases' => array(
				'ui' => array(
					'keineDatenVorhanden',
					)
			),
			'customCSSs' => array('public/css/sbadmin2/tablesort_bootstrap.css'),
			'customJSs' => array('public/js/bootstrapper.js')
		)
	);
?>

<body>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h3 class="page-header">
					<?php echo $this->p->t('abschlusspruefung','pruefungsprotokoll'); ?>
				</h3>
			</div>
		</div>
		<?php echo $this->p->t('abschlusspruefung','einfuehrungstext'); ?>
		<div class="row">
			<div class="col-lg-12">
			<?php $this->load->view('lehre/pruefungsprotokollUebersichtData.php'); ?>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
