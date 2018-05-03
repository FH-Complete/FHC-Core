<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Info Center',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'tablesorter' => true,
			'customCSSs' => 'skin/tablesort_bootstrap.css',
			'customJSs' => array('include/js/bootstrapper.js', 'include/js/infocenter/infocenterPersonDataset.js')
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
						<h3 class="page-header">Infocenter &Uuml;bersicht</h3>
					</div>
				</div>
				<div>
					<?php
						$this->load->view('system/infocenter/infocenterData.php', array('fhc_controller_id' => $fhc_controller_id));
					?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
