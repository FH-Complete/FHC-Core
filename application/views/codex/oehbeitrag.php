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
							<?php foreach ($oehbeitraege as $oehbeitrag): ?>
							<tr>
								<td><?php echo date_format(date_create($oehbeitrag->von_datum), 'd.m.Y') . '/' . $oehbeitrag->von_studiensemester_kurzbz ?>
									<i class="fa fa-edit editVonStudiensemester" id="edit_von_studiensemester_<?php echo $oehbeitrag->oehbeitrag_id ?>"></i>
								</td>
								<td><?php echo $oehbeitrag->bis_studiensemester_kurzbz == null ? 'unbeschr&aumlnkt' : (date_format(date_create($oehbeitrag->bis_datum), 'd.m.Y') . '/' .  $oehbeitrag->bis_studiensemester_kurzbz) ?>
									<i class="fa fa-edit editBisStudiensemester" id="edit_bis_studiensemester_<?php echo $oehbeitrag->oehbeitrag_id ?>"></i>
								</td>
								<td><?php echo number_format($oehbeitrag->studierendenbeitrag, 2, ',', '.') ?>
									<i class="fa fa-edit editStudierendenbeitrag" id="edit_studierendenbeitrag_<?php echo $oehbeitrag->oehbeitrag_id ?>"></i>
								</td>
								<td><?php echo number_format($oehbeitrag->versicherung, 2, ',', '.') ?>
									<i class="fa fa-edit editVersicherung" id="edit_versicherung_<?php echo $oehbeitrag->oehbeitrag_id ?>"></i>
								</td>
								<td><button class="btn btn-default deleteBtn" id="delete_<?php echo $oehbeitrag->oehbeitrag_id ?>">L&ouml;schen</button></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
