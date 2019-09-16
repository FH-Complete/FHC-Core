<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'JobsViewer',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'tablesorter' => true,
			'ajaxlib' => true,
			'filterwidget' => true,
			'navigationwidget' => true,
			'phrases' => array(
				'global' => array('mailAnXversandt'),
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
							JobsViewer
						</h3>
					</div>
				</div>
				<div>
					<?php $this->load->view('system/jobs/jobsViewerData.php'); ?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
