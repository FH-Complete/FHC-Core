<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'ÖH-Beitragsverwaltung',
		'jquery' => true,
		'jqueryui' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'sbadmintemplate' => true,
		'tablesorter' => true,
		'dialoglib' => true,
		'ajaxlib' => true,
		'navigationwidget' => true,
		'customCSSs' => array('public/css/sbadmin2/tablesort_bootstrap.css', 'public/css/codex/oehbeitrag.css'),
		'customJSs' => array('public/js/tablesort/tablesort.js', 'public/js/codex/oehbeitrag.js')
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
						&Ouml;hbeitragsverwaltung
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<button class="btn btn-default" id="addNewOeh">Neuen &Ouml;hbeitrag hinzuf&uuml;gen</button>
					<br />
					<br />
					<table class="table table-bordered table-condensed" id="oehbeitraegeTbl">
						<thead>
							<tr>
								<th>G&uuml;ltig von</th>
								<th>G&uuml;ltig bis</th>
								<th>Studierendenbetrag</th>
								<th>Versicherungsbetrag</th>
								<th id="actionHeading">Aktion</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
