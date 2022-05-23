<?php
	$includesArray = array(
		'title' => 'Logs Viewer',
		'jquery3' => true,
		'jqueryui1' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'tablesorter2' => true,
		'ajaxlib' => true,
		'bootstrapper' => true, // to be used only if you know what you are doing!
		'filterwidget' => true,
		'navigationwidget' => true,
		'phrases' => array(
			'global' => array('mailAnXversandt'),
			'ui' => array('bitteEintragWaehlen')
		),
		'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css'
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="wrapper">

		<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">
							Job Logs Viewer
						</h3>
					</div>
				</div>
				<div>
					<?php $this->load->view('system/logs/logsViewerData.php'); ?>
				</div>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

