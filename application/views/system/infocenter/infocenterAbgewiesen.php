<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Info Center',
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
				'person' => array('vorname', 'nachname'),
				'global' => array('mailAnXversandt'),
				'ui' => array('bitteEintragWaehlen')
			),
			'customCSSs' => array('public/css/sbadmin2/tablesort_bootstrap.css', 'public/css/infocenter/infocenterPersonDataset.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/infocenter/infocenterPersonDataset.js')
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
							Abgewiesene
						</h3>
					</div>
				</div>
				<div>
					<?php $this->load->view('system/infocenter/infocenterAbgewiesenData.php'); ?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
