<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Fehler Monitoring',
		'jquery' => true,
		'jqueryui' => true,
		'jquerycheckboxes' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'sbadmintemplate' => true,
		'tablesorter' => true,
		'ajaxlib' => true,
		'filterwidget' => true,
		'navigationwidget' => true,
		'dialoglib' => true,
		'phrases' => array(
			'ui' => array('bitteEintragWaehlen')
		),
		'customCSSs' => array('public/css/issues/issuesDataset.css', 'public/css/sbadmin2/tablesort_bootstrap.css'),
		'customJSs' => array('public/js/issues/issuesDataset.js', 'public/js/bootstrapper.js')
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
						Fehler Monitoring
					</h3>
				</div>
			</div>
			<div>
				<?php $this->load->view('system/issues/issuesData.php'); ?>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
