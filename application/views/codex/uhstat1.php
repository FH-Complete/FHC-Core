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
<?php
// init data
$mutter_geburtsjahr = isset($uhstatData->mutter_geburtsjahr) ? $uhstatData->mutter_geburtsjahr : set_value('mutter_geburtsjahr');
$mutter_geburtsstaat = isset($uhstatData->mutter_geburtsstaat) ? $uhstatData->mutter_geburtsstaat : set_value('mutter_geburtsstaat');
$mutter_bildungsstaat = isset($uhstatData->mutter_bildungsstaat) ? $uhstatData->mutter_bildungsstaat : set_value('mutter_bildungsstaat');
$mutter_bildungmax = isset($uhstatData->mutter_bildungmax) ? $uhstatData->mutter_bildungmax : set_value('mutter_bildungmax');
$vater_geburtsjahr = isset($uhstatData->vater_geburtsjahr) ? $uhstatData->vater_geburtsjahr : set_value('vater_geburtsjahr');
$vater_geburtsstaat = isset($uhstatData->vater_geburtsstaat) ? $uhstatData->vater_geburtsstaat : set_value('vater_geburtsstaat');
$vater_bildungsstaat = isset($uhstatData->vater_bildungsstaat) ? $uhstatData->vater_bildungsstaat : set_value('vater_bildungsstaat');
$vater_bildungmax = isset($uhstatData->vater_bildungmax) ? $uhstatData->vater_bildungmax : set_value('vater_bildungmax');
$disabled = $readonly === true ? ' disabled' : '';
?>
<div class="container">
	<div class="tab-content">
		<h3>
			<?php echo $this->p->t('uhstat', 'uhstat1AnmeldungUeberschrift') ?>
		</h3>
		<p>
			<?php echo $this->p->t('uhstat', 'uhstat1AnmeldungEinleitungstext') ?>
		</p>
		<br>
		<form class="form-horizontal" method="POST" action="<?php echo site_url('codex/UHSTAT1/saveUHSTAT1Data').'?person_id='.$formMetaData['person_id'] ?>">
			<fieldset>
				<legend><?php echo $this->p->t('uhstat', 'angabenErziehungsberechtigte') ?></legend>
				<p>
					<?php echo $this->p->t('uhstat', 'angabenErziehungsberechtigteEinleitungstext') ?>
				</p>
				<br>
				<h4><?php echo $this->p->t('uhstat', 'erziehungsberechtigtePersonEins') ?></h4>
				<div class="form-group">
					<label for="mutter_geburtsjahr" class="col-sm-3 control-label"><?php echo ucfirst($this->p->t('uhstat', 'geburtsjahr')) ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_geburtsjahr" id="mutter_geburtsjahr" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['jahre'] as $jahr): ?>
								<option
									value="<?php echo $jahr ?>"
									<?php echo $jahr == $mutter_geburtsjahr ? " selected" : "" ?>>
									<?php echo $jahr ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_geburtsjahr'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_geburtsstaat" class="col-sm-3 control-label">
						<?php echo ucfirst($this->p->t('uhstat', 'geburtsstaat')) ?>
						<br>
						<?php echo '('.ucfirst($this->p->t('uhstat', 'inDenHeutigenGrenzen')).')' ?>
					</label>
					<div class="col-sm-9">
						<select type="text" name="mutter_geburtsstaat" id="mutter_geburtsstaat" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo $mutter_geburtsstaat == $nation->nation_code ? " selected" : "" ?>>
									<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_geburtsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_bildungsstaat" class="col-sm-3 control-label">
						<?php echo ucfirst($this->p->t('uhstat', 'hoechsterAbschlussStaat')) ?>
						<br>
						<?php echo '('.ucfirst($this->p->t('uhstat', 'inDenHeutigenGrenzen')).')' ?>
					</label>
					<div class="col-sm-9">
						<select type="text" name="mutter_bildungsstaat" id="mutter_bildungsstaat" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo $mutter_bildungsstaat == $nation->nation_code ? " selected" : "" ?>>
									<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_bildungsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="mutter_bildungmax" class="col-sm-3 control-label"><?php echo ucfirst($this->p->t('uhstat', 'hoechsterAbschluss')) ?></label>
					<div class="col-sm-9">
						<select type="text" name="mutter_bildungmax" id="mutter_bildungmax" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formMetaData['abschluss_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo $mutter_bildungmax == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung ?>
								</option>
							<?php endforeach; ?>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussNichtInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formMetaData['abschluss_nicht_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo $mutter_bildungmax == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('mutter_bildungmax'); ?>
					</div>
				</div>
				<br>
				<h4><?php echo $this->p->t('uhstat', 'erziehungsberechtigtePersonZwei') ?></h4>
				<div class="form-group">
					<label for="vater_geburtsjahr" class="col-sm-3 control-label"><?php echo ucfirst($this->p->t('uhstat', 'geburtsjahr')) ?></label>
					<div class="col-sm-9">
						<select type="text" name="vater_geburtsjahr" id="vater_geburtsjahr" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['jahre'] as $jahr): ?>
								<option
									value="<?php echo $jahr ?>"
									<?php echo $vater_geburtsjahr == $jahr ? " selected" : "" ?>>
										<?php echo $jahr ?>
									</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_geburtsjahr'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_geburtsstaat" class="col-sm-3 control-label">
						<?php echo ucfirst($this->p->t('uhstat', 'geburtsstaat')) ?>
						<br>
						<?php echo '('.ucfirst($this->p->t('uhstat', 'inDenHeutigenGrenzen')).')' ?>
					</label>
					<div class="col-sm-9">
						<select type="text" name="vater_geburtsstaat" id="vater_geburtsstaat" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo $vater_geburtsstaat == $nation->nation_code ? " selected" : "" ?>>
										<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_geburtsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_bildungsstaat" class="col-sm-3 control-label">
						<?php echo $this->p->t('uhstat', 'hoechsterAbschlussStaat') ?>
						<br>
						<?php echo '('.ucfirst($this->p->t('uhstat', 'inDenHeutigenGrenzen')).')' ?>
					</label>
					<div class="col-sm-9">
						<select type="text" name="vater_bildungsstaat" id="vater_bildungsstaat" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<?php foreach ($formMetaData['nation'] as $nation): ?>
								<option
									value="<?php echo $nation->nation_code ?>"
									<?php echo $vater_bildungsstaat == $nation->nation_code ? " selected" : "" ?>>
										<?php echo $nation->nation_text ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_bildungsstaat'); ?>
					</div>
				</div>
				<div class="form-group">
					<label for="vater_bildungmax" class="col-sm-3 control-label">
						<?php echo ucfirst($this->p->t('uhstat', 'hoechsterAbschluss')) ?>
					</label>
					<div class="col-sm-9">
						<select type="text" name="vater_bildungmax" id="vater_bildungmax" class="form-control" <?php echo $disabled ?>>
							<option disabled selected value=""><?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?></option>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formMetaData['abschluss_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo $vater_bildungmax == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung ?>
								</option>
							<?php endforeach; ?>
							<optgroup label="<?php echo $this->p->t('uhstat', 'wennAbschlussNichtInOesterreich') ?>">
								<?php echo $this->p->t('uhstat', 'bitteAuswaehlen') ?>
							</optgroup>
							<?php foreach ($formMetaData['abschluss_nicht_oesterreich'] as $abschluss): ?>
								<option
									value="<?php echo $abschluss->ausbildung_code ?>"
									<?php echo $vater_bildungmax == $abschluss->ausbildung_code ? " selected" : "" ?>>
									<?php echo $abschluss->bezeichnung ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php echo form_error('vater_bildungmax'); ?>
					</div>
				</div>
			</fieldset>
			<br>
			<fieldset>
				<div class="form-group">
					<div class="col-sm-12 text-right">
						<?php if (isset($successMessage) && !isEmptyString($successMessage)): ?>
							<div class='alert alert-success text-center' role='alert'><?php echo $successMessage ?></div>
						<?php endif; ?>
						<?php if (isset($errorMessage) && !isEmptyString($errorMessage)): ?>
							<div class='alert alert-danger text-center'><?php echo $errorMessage ?></div>
						<?php endif; ?>
						<?php if (!$readonly): ?>
							&nbsp;
							<button class="btn btn-success btn-md" type="submit">
								<?php echo $this->p->t('uhstat', 'pruefenUndSpeichern') ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<?php $this->load->view('templates/FHC-Footer'); ?>
