<?php
$this->load->view('templates/FHC-Header',
	array(
		'title' => 'FH-Complete',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'sbadmintemplate' => true,
		'ajaxlib' => true,
		'addons' => true,
		'navigationwidget' => true
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
					<h3 class="page-header">FH-Complete</h3>
				</div>
			</div>
			<span>
				<div id="dashboard"></div>
		</span>
		</div>
	</div>
</div>
<script>
	//javascript hacks for bootstrap
	$("select").addClass("form-control");
	$("input[type=text]").addClass("form-control");
	$("input[type=button]").addClass("btn btn-default");
	$("#tableDataset").addClass('table-bordered');
</script>
</body>
<?php $this->load->view('templates/FHC-Footer'); ?>
