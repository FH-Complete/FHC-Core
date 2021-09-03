<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'neueAnrechnung'),
		'jquery' => true,
		'jqueryui' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'tabulator' => true,
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
	)
);
?>

<body>
<div id="page-wrapper">
<div class="container-fluid">

<!-- Title -->
<div class="row">
	<div class="col-lg-12 page-header">
		<h3>
			<?php echo $this->p->t('anrechnung', 'neueAnrechnung'); ?>
			<small>| <?php echo $this->p->t('global', 'antragAnlegen'); ?></small>
		</h3>
	</div>
</div>

<!-- Studiensemester Dropdown -->
<div class="row">
	<div class="col-xs-12">
		<form class="form-inline" action="" method="get">
			<div class="form-group">
				<?php
				echo $this->widgetlib->widget(
					'Studiensemester_widget',
					array(
						DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
					),
					array(
						'name' => 'studiensemester',
						'id' => 'studiensemester'
					)
				);
				?>
			</div>
			<button type="submit"
					class="btn btn-default form-group">
				<?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?>
			</button>
		</form>
	</div>
</div>

<!-- StudentInnen Table -->
<div class="row">
	<div class="col-lg-10">
		<?php $this->load->view('lehre/anrechnung/createAnrechnungData.php'); ?>
	</div>
</div>
<br><br>
<!-- FORM START ------------------------------------------------------------------------------------------------------->
<form id="createAnrechnung-form">
<input name="prestudent_id" id="prestudent_id" type="hidden" value="" data-prestudent_id = "" >
<input name="studiensemester_kurzbz" id="studiensemester_kurzbz" type="hidden" value="<?php echo $studiensemester_selected ?>">
<div class="row">
	<div class="col-lg-10">
		<table class="table table-condensed table-bordered">
			<!-- StudentIn -->
			<tr>
				<th class="col-xs-5 col-lg-2"><label><?php echo $this->p->t('person', 'studentIn'); ?></label></th>
				<td><span id="student" class="pl-15"></span></td>
			</tr>
			<!-- Selectmenu Lehrveranstaltungen -->
			<tr>
				<th class="col-xs-5 col-lg-2"><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?> *</th>
				<td>
					<select name="lehrveranstaltung_id" id="select-lehrveranstaltung" class="form-control select-w500">
						<option value="" <?php echo set_select('lehrveranstaltung', '', TRUE); ?> >
							<?php echo $this->p->t('ui', 'bitteWaehlen'); ?>
						</option>
					</select>
				</td>
			</tr>
			<!-- Select Anrechnungbegruendungen -->
			<tr>
				<th class="col-xs-5 col-lg-2"><?php echo $this->p->t('global', 'begruendung'); ?> *</th>
				<td>
					<select name="begruendung_id" id="select-begruendung" class="form-control select-w500">
						<option value="" <?php echo set_select('begruendung', '', TRUE); ?> >
							<?php echo $this->p->t('ui', 'bitteWaehlen'); ?>
						</option>
						<?php foreach ($begruendungen as $begruendung) : ?>
							<option value="<?php echo $begruendung->begruendung_id ?>"
								<?php echo set_select('begruendung', $begruendung->begruendung_id); ?> >
								<?php echo ucfirst($begruendung->bezeichnung) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<!-- Submit Herkunft der Kenntnisse -->
			<tr>
				<th class="col-xs-5 col-lg-2"><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?></th>
				<td>
					<?php echo form_textarea(array(
						'name' => 'herkunftKenntnisse',
						'rows' => 1
					)); ?>
				</td>
			</tr>
			<!-- Submit Upload Nachweisdokumente -->
			<tr>
				<th class="col-xs-5 col-lg-2"><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?> *</th>
				<td>
					<div><?php echo form_upload(array(
							'name' => 'uploadfile',
							'accept' => '.pdf',
							'size' => '50',
							'required' => 'required',
							'enctype' => "multipart/form-data"
							)); ?>
					</div>
					<a class="pull-right" id="download-nachweisdokumente"></a>
				</td>
			</tr>
		</table>
	</div>
</div>

<div class="row">
	<div class="col-lg-10">
		<!-- Submit Button -->
		<button class="btn btn-primary btn-w200 pull-right" id="createAnrechnung-submit" type="submit" value="submit">
			<?php echo $this->p->t('global', 'antragAnlegen'); ?>
		</button>
		<!-- Open new Anrechnung Button (hidden by default) -->
		<a type="button" class="btn btn-default btn-mr10 pull-right hidden" id="createAnrechnung-openAnrechnung" target="_blank"></a>
	</div>
</div>

</form>
<!-- FORM END --------------------------------------------------------------------------------------------------------->

</div>
</div>
</body>
