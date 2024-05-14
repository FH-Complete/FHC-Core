<?php
	$includesArray = array(
		'title' => 'FH-Complete',
		'jquery3' => true,
		'jqueryui1' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'ajaxlib' => true,
		'bootstrapper' => true, // to be used only if you know what you are doing!
		'addons' => true,
		'navigationwidget' => true
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="wrapper">
	
		<?php echo $this->widgetlib->widget('NavigationWidget'); ?>
	
		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">FH-Complete</h3>
					</div>
				</div>
				<span>
					<div id="dashboard"></div>
			</span>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

