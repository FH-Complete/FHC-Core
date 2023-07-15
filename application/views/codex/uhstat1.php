<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'UHSTAT1Formular',
		'jquery3' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'dialoglib' => true,
		'phrases' => array(
			'ui' => array('speichern')
		)
	)
);
?>

<div class="container">
	<div class="tab-content">
		<h3>
			<?php echo $this->p->t('uhstat', 'uhstat1AnmeldungUeberschrift') ?>
		</h3>
		<form class="form-horizontal" method="POST" action="<?php echo site_url('codex/UHSTAT1/saveUHSTAT1Data').'?person_id='.$formData['person_id'] ?>">
			<fieldset>
				<legend><?php echo $this->p->t('uhstat', 'persoenlicheAngaben') ?></legend>
				<div class="form-group">
					<label for="geburtsstaat" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'geburtsstaat') ?></label>
					<div class="col-sm-9">
						<select type="text" name="geburtsstaat" id="geburtsstaat" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo set_value('geburtsstaat') == $nation->nation_code ? " selected" : "" ?>>
									<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('geburtsstaat'); ?>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend><?php echo $this->p->t('uhstat', 'angabenErziehungsberechtigte') ?></legend>
				<h4><?php echo $this->p->t('uhstat', 'erziehungsberechtigtePersonEins') ?></h4>
				<div class="form-group">
					<label for="mutter_geburtsjahr" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'mutterGeburtsjahr') ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_geburtsjahr" id="mutter_geburtsjahr" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['jahre'] as $jahr): ?>
								<option
									value="<?php echo $jahr ?>"
									<?php echo set_value('mutter_geburtsjahr') == $jahr ? " selected" : "" ?>>
									<?php echo $jahr ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_geburtsjahr'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_geburtsstaat" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'mutterGeburtsstaat') ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_geburtsstaat" id="mutter_geburtsstaat" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo set_value('mutter_geburtsstaat') == $nation->nation_code ? " selected" : "" ?>>
									<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_geburtsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_bildungsstaat" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'hoechsterAbschlussStaat') ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_bildungsstaat" id="mutter_bildungsstaat" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo set_value('mutter_bildungsstaat') == $nation->nation_code ? " selected" : "" ?>>
									<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_bildungsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_bildungmax" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'hoechsterAbschluss') ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_bildungmax" id="mutter_bildungmax" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formData['abschluss_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo set_value('mutter_bildungmax') == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung[$formData['languageIdx']] ?>
								</option>
							<?php endforeach; ?>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussNichtInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formData['abschluss_nicht_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo set_value('mutter_bildungmax') == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung[$formData['languageIdx']] ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_bildungmax'); ?>
					</div>
				</div>
				<h4><?php echo $this->p->t('uhstat', 'erziehungsberechtigtePersonZwei') ?></h4>
				<div class="form-group">
					<label for="vater_geburtsjahr" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'vaterGeburtsjahr') ?></label>
					<div class="col-sm-9">
						<select type="text" name="vater_geburtsjahr" id="vater_geburtsjahr" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['jahre'] as $jahr): ?>
								<option
									value="<?php echo $jahr ?>"
									<?php echo set_value('vater_geburtsjahr') == $jahr ? " selected" : "" ?>>
										<?php echo $jahr ?>
									</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_geburtsjahr'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_geburtsstaat" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'vaterGeburtsstaat') ?></label>
					<div class="col-sm-9">
						<select type="text" name="vater_geburtsstaat" id="vater_geburtsstaat" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo set_value('vater_geburtsstaat') == $nation->nation_code ? " selected" : "" ?>>
										<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_geburtsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_bildungsstaat" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'hoechsterAbschlussStaat') ?></label>
					<div class="col-sm-9">
						<select type="text" name="vater_bildungsstaat" id="vater_bildungsstaat" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo set_value('vater_bildungsstaat') == $nation->nation_code ? " selected" : "" ?>>
										<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_bildungsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_bildungmax" class="col-sm-3 control-label"><?php echo $this->p->t('uhstat', 'hoechsterAbschluss') ?></label>
					<div class="col-sm-9">
						<select type="text" name="vater_bildungmax" id="vater_bildungmax" class="form-control">
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formData['abschluss_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo set_value('vater_bildungmax') == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung[$formData['languageIdx']] ?>
								</option>
							<?php endforeach; ?>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussNichtInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formData['abschluss_nicht_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo set_value('vater_bildungmax') == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung[$formData['languageIdx']] ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_bildungmax'); ?>
					</div>
				</div>
			</fieldset>
			<div class="text-right">
				<?php if (isset($successMessage)): ?>
					<span class='text-success'><?php echo $successMessage ?></span>
				<?php endif; ?>
				<?php if (isset($errorMessage)): ?>
					<span class='text-danger'><?php echo $errorMessage ?></span>
				<?php endif; ?>
				<button class="btn btn-success" type="submit">
					<?php echo $this->p->t('ui', 'speichern') ?>
				</button>
			</div>
		</form>
	</div>

</div>

<?php $this->load->view('templates/FHC-Footer'); ?>
