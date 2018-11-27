<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Reihungstest',
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
				'ui' => array('bitteEintragWaehlen')
			),
			'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
			'customJSs' => array(
				'public/js/bootstrapper.js',
				'public/js/reihungstest/reihungstest.js')
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
							Reihungstest <?php echo ucfirst($this->p->t('global', 'uebersicht')); ?>
						</h3>
					</div>
				</div>
				<div>
					<?php $this->load->view('organisation/reihungstest/ReihungstestUebersichtData.php'); ?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
