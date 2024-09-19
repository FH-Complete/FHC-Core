<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Lehrauftrag bestellen',
		'jquery3' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'ajaxlib' => true,
		'navigationwidget' => true,
	)
);
?>

<body>
<?php echo $this->widgetlib->widget('NavigationWidget'); ?>
	<div id="page-wrapper">
		<div class="container-fluid">

			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						Lehraufträge - Lehrendenübersicht
					</h3>
				</div>
			</div>

			<div>
				<iframe src="<?php echo base_url() . '/addons/reports/cis/vorschau.php?statistik_kurzbz=LehrauftraegeOeUebersicht'; ?>"
						style="height: 950px; width: 100%; border: none">
				</iframe>
			</div>

		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
