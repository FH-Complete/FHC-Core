<?php

$includesArray = array(
	'title' => $this->p->t('anrechnung', 'neueAnrechnung'),
	'jquery3' => true,
	'jqueryui1' => true,
	'bootstrap5' => true,
	'fontawesome4' => true,
	'ajaxlib' => true,
	'dialoglib' => true,
	'tabulator5' => true,
	'tabulator5JQuery' => true,
	'cis' => true,
	'tablewidget' => true,
	'phrases' => array(
		'global' => array(
			'anerkennungNachgewiesenerKenntnisse',
			'antragWurdeGestellt',
			'antragBereitsGestellt',
			'antragBearbeiten'
		),
		'ui' => array(
			'hochladen'
		),
		'lehre' => array(
			'studiensemester',
			'studiengang',
			'lehrveranstaltung'
		)
	),
	'customJSs' => array(
		'public/js/bootstrapper.js',
		'public/js/lehre/anrechnung/createAnrechnung.js'
	),
	'customCSSs' => array(
		'public/css/lehre/anrechnung.css'
	)
);

if (defined("CIS4")) {
	$this->load->view(
		'templates/CISVUE-Header',
		$includesArray
	);
} else {
	$this->load->view(
		'templates/FHC-Header',
		$includesArray
	);
}
?>

<div id="page-wrapper">
	<div class="container-fluid">

		<!-- Title -->
		<div class="row  my-4">
			<div class="col-lg-12 border-bottom">
				<h3 class="fw-normal">
					<?php echo $this->p->t('anrechnung', 'neueAnrechnung'); ?>
					<small class="text-secondary fs-6">| <?php echo $this->p->t('global', 'antragAnlegen'); ?></small>
				</h3>
			</div>
		</div>

		<!-- Studiensemester Dropdown -->
		<div class="row my-4">
			<div class="col-12">
				<form class="row align-items-center" action="" method="get">
					<div class="col-auto">
						<?php
						echo $this->widgetlib->widget(
							'Studiensemester_widget',
							array(
								DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
							),
							array(
								'name' => 'studiensemester',
								'id' => 'studiensemester',
								'class' => 'form-select w-auto ',
							)
						);
						?>
					</div>
					<button type="submit" class="btn btn-outline-secondary col-auto">
						<?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- StudentInnen Table -->
		<div class="row  my-4">
			<div class="col-lg-10">
				<?php $this->load->view('lehre/anrechnung/createAnrechnungData.php'); ?>
			</div>
		</div>
		<!-- FORM START ------------------------------------------------------------------------------------------------------->
		<form id="createAnrechnung-form">
			<input name="prestudent_id" id="prestudent_id" type="hidden" value="" data-prestudent_id="">
			<input name="studiensemester_kurzbz" id="studiensemester_kurzbz" type="hidden"
				value="<?php echo $studiensemester_selected ?>">
			<div class="row my-4">
				<div class="col-lg-10">
					<table class="table table-condensed table-bordered mb-0">
						<!-- StudentIn -->
						<tr>
							<th class="col-5 col-lg-2"><label><?php echo $this->p->t('person', 'studentIn'); ?></label>
							</th>
							<td><span id="student" class="ps-3"></span></td>
						</tr>
						<!-- Selectmenu Lehrveranstaltungen -->
						<tr>
							<th class="col-5 col-lg-2"><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?> *</th>
							<td>
								<select name="lehrveranstaltung_id" id="select-lehrveranstaltung"
									class="form-select select-w500">
									<option value="" <?php echo set_select('lehrveranstaltung', '', true); ?>>
										<?php echo $this->p->t('ui', 'bitteWaehlen'); ?>
									</option>
								</select>
							</td>
						</tr>
						<!-- Select Anrechnungbegruendungen -->
						<tr>
							<th class="col-5 col-lg-2"><?php echo $this->p->t('global', 'begruendung'); ?> *</th>
							<td>
								<select name="begruendung_id" id="select-begruendung" class="form-select select-w500">
									<option value="" <?php echo set_select('begruendung', '', true); ?>>
										<?php echo $this->p->t('ui', 'bitteWaehlen'); ?>
									</option>
									<?php foreach ($begruendungen as $begruendung): ?>
										<option value="<?php echo $begruendung->begruendung_id ?>" <?php echo set_select('begruendung', $begruendung->begruendung_id); ?>>
											<?php echo ucfirst($begruendung->bezeichnung) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<!-- Submit Herkunft der Kenntnisse -->
						<tr>
							<th class="col-5 col-lg-2"><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?>
							</th>
							<td>
								<?php echo form_textarea(
									array(
										'name' => 'herkunftKenntnisse',
										'rows' => 1
									)
								); ?>
							</td>
						</tr>
						<!-- Submit Upload Nachweisdokumente -->
						<tr>
							<th class="col-5 col-lg-2 "><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?> *
							</th>
							<td class="d-flex input-group align-items-center">

								<input class="form-control flex-fill" type="file" id="requestAnrechnung-uploadfile"
									name="uploadfile" accept=".pdf" size="50"
									data-maxsize="<?php echo (int) ini_get('upload_max_filesize') * 1024 * 1024 ?>"
									required>

							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="row my-4">
				<div class="col-lg-10">
					<!-- Submit Button -->
					<button class="btn btn-primary btn-w200 float-end" id="createAnrechnung-submit" type="submit"
						value="submit">
						<?php echo $this->p->t('global', 'antragAnlegen'); ?>
					</button>
					<!-- Open new Anrechnung Button (hidden by default) -->
					<a type="button" class="btn btn-outline-secondary me-1 float-end visually-hidden"
						id="createAnrechnung-openAnrechnung" target="_blank"></a>
				</div>
			</div>

		</form>
		<!-- FORM END --------------------------------------------------------------------------------------------------------->

	</div>
</div>
<?php
if (defined("CIS4")) {
	$this->load->view(
		'templates/CISVUE-Footer',
		$includesArray
	);
} else {
	$this->load->view(
		'templates/FHC-Footer',
		$includesArray
	);
}
?>
