<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Plausichecks',
		'jquery3' => true,
		'jqueryui1' => true,
		'jquerycheckboxes' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'sbadmintemplate3' => true,
		'ajaxlib' => true,
		'navigationwidget' => true,
		'dialoglib' => true,
		'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css'),
		'customJSs' => array('public/js/issues/plausichecks.js')
	)
);
?>

<div id="wrapper">

	<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						Plausichecks
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-2 form-group">
					<label>Studiensemester</label>
					<select class="form-control" name="studiensemester" id="studiensemester">
						<?php
						foreach ($semester as $sem):
							$selected = $sem->studiensemester_kurzbz === $currsemester[0]->studiensemester_kurzbz ? ' selected=""' : '';
							?>
							<option value="<?php echo $sem->studiensemester_kurzbz ?>"<?php echo $selected ?>>
								<?php echo $sem->studiensemester_kurzbz ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-xs-4 form-group">
					<label>Studiengang</label>
					<select class="form-control" name="studiengang_kz" id="studiengang_kz">
						<option value="" selected="selected">Alle</option>';
						<?php
						$typ = '';
						foreach ($studiengaenge as $studiengang):
							if ($typ != $studiengang->typ || $typ == '')
							{
								if ($typ != '')
									echo '</optgroup>';

								echo '<optgroup label = "'.($studiengang->typbezeichnung !== '' ? $studiengang->typbezeichnung : $studiengang).'">';
							}
							$typ = $studiengang->typ;
							?>
							<option value="<?php echo $studiengang->studiengang_kz ?>">
								<?php echo $studiengang->kuerzel . ' - ' . $studiengang->bezeichnung ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-xs-3 col-lg-4 form-group">
					<label>Fehler</label>
					<select class="form-control" name="fehler_kurzbz" id="fehler_kurzbz">
						<option value="" selected="selected">Alle</option>';
						<?php foreach ($fehlerKurzbzCodeMappings as $fehler_kurzbz => $fehlercode):?>
							<option value="<?php echo $fehler_kurzbz ?>">
								<?php echo $fehler_kurzbz.' ('.$fehlercode.')' ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-xs-3 col-lg-2 form-group text-right">
					<label>&nbsp;</label>
					<br />
					<button class="btn btn-default" id="plausistart">Checks starten</button>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<div class="well well-sm wellminheight">
						<h4 class="text-center">Output:</h4>
						<div id="plausioutput" class="panel panel-body">
							<div id="plausioutput-text">-</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/FHC-Footer'); ?>
