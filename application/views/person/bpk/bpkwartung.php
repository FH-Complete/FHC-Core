<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'bPK Wartung',
			'jquery3' => true,
			'jqueryui1' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'tablesorter2' => true,
			'ajaxlib' => true,
			'filterwidget' => true,
			'navigationwidget' => true,
			'phrases' => array(
				'ui' => array('bitteEintragWaehlen')
			),
			'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
			'customJSs' => array('public/js/bootstrapper.js')
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
							bPK <?php echo ucfirst($this->p->t('global', 'uebersicht')); ?>
						</h3>
					</div>
				</div>
				<div>
					Bei folgenden Personen mit Matrikelnummer konnte kein bPK ermittelt werden.
					Es ist die Namensschreibweise zu prüfen und ggf zu korrigieren.
					Falls die Person keine Meldeadresse hat, ist eine Eintragung der
					Person in das "Ergänzungsregister für natürliche Personen" notwendig.
					<br /><br />
					<?php $this->load->view('person/bpk/bpkData.php'); ?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
