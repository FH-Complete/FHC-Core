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
			'customJSs' => array('include/js/infocenterPersonDataset.js', 'include/js/bootstrapper.js')
		)
	);
?>

<body>
	<div id="wrapper">
		<?php
			echo $this->widgetlib->widget(
				'NavigationWidget',
				array(
					'navigationHeader' => $navigationHeaderArray,
					'navigationMenu' => $navigationMenuArray
				)
			);
		?>
		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">Infocenter &Uuml;bersicht</h3>
					</div>
				</div>
				<div>
					<?php
						$this->load->view('system/infocenter/infocenterData.php');
					?>
				</div>
			</div>
		</div>
	</div>
	<script>
		$("#tableDataset").addClass('table table-bordered table-responsive');
	</script>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
