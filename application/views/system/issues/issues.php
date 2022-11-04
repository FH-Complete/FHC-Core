<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Fehler Monitoring',
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
		'customCSSs' => array('public/css/issues/issuesDataset.css', 'public/css/sbadmin2/tablesort_bootstrap.css'),
		'customJSs' => array('public/js/issues/issuesDataset.js', 'public/js/bootstrapper.js'),
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
						<?php echo $this->p->t('fehlermonitoring', 'fehlerMonitoring') ?>
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
