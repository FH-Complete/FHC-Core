<?php
$includesArray = array();

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<div class="fhc-header">
		<h1><?= $this->p->t('global', 'dokumente'); ?><small><?= $this->p->t('tools', 'bestaetigungenZeugnisse'); ?></small></h1>
	</div>
	
	<div class="row mb-3 justify-content-end">
		<div class="col-auto">
			<select class="form-select" onchange="location = this.value">
				<?php foreach ($stsemArray as $sem) { ?>
					<option value="<?= site_url('Cis/Documents/Semester/' . $sem); ?>"<?= $sem == $stsem ? ' selected': ''; ?>>
						<?= $sem; ?>
					</option>
				<?php } ?>
			</select>
		</div>
	</div>

	<?php if ($hasSemester) { ?>
		<div class="card mb-3">
			<h3 class="card-header h5"><?= $this->p->t('tools', 'inskriptionsbestaetigung'); ?></h3>
			<ul class="list-group list-group-flush">
				<?php foreach ($inskriptionsbestaetigungen as $stg => $hasPaid) { ?>
					<?php if (count($studiengaenge) != 1) { ?>
						<li class="list-group-item fw-bold">
							<?= $studiengaenge[$stg]->bezeichnung; ?>
						</li>
					<?php } ?>
					<?php if ($hasPaid) { ?>
						<li class="list-group-item">
							<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Inskription&xml=student.rdf.php&ss=' . $stsem . '&uid=' . $uid . '&xsl_stg_kz=' . $stg); ?>">
								<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'inskriptionsbestaetigung') . ' ' . $stsem; ?>
							</a>
						</li>
					<?php } else { ?>
						<li class="list-group-item list-group-item-danger">
							<?= $this->p->t('tools', 'studienbeitragFuerSSNochNichtBezahlt', ['stsem' => $stsem]); ?>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
		<?php if ($studienbuchblatt) { ?>
			<div class="card mb-3">
				<h3 class="card-header h5"><?= $this->p->t('tools', 'studienbuchblatt'); ?></h3>
				<ul class="list-group list-group-flush">
					<?php foreach ($inskriptionsbestaetigungen as $stg => $hasPaid) { ?>
						<?php if (count($studiengaenge) != 1) { ?>
							<li class="list-group-item fw-bold">
								<?= $studiengaenge[$stg]->bezeichnung; ?>
							</li>
						<?php } ?>
						<?php if ($hasPaid) { ?>
							<li class="list-group-item">
								<?php /* TODO(chris): studiengang_kz? */ ?>
								<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Studienblatt&xml=studienblatt.xml.php&ss=' . $stsem . '&uid=' . $uid); ?>">
									<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienbuchblatt') . ' ' . $stsem; ?>
								</a>
							</li>
						<?php } else { ?>
							<li class="list-group-item list-group-item-danger">
								<?= $this->p->t('tools', 'studienbeitragFuerSSNochNichtBezahlt', ['stsem' => $stsem]); ?>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>
		<?php if ($studienerfolgsbestaetigung) { ?>
			<div class="card mb-3">
				<h3 class="card-header h5"><?= $this->p->t('tools', 'studienerfolgsbestaetigung'); ?></h3>
				<ul class="list-group list-group-flush">
					<?php foreach ($studiengaenge as $stg => $bezeichnung) { ?>
						<?php if (count($studiengaenge) != 1) { ?>
							<li class="list-group-item fw-bold">
								<?= $bezeichnung; ?>
							</li>
						<?php } ?>
						<?php foreach (['' => '&typ=finanzamt', ' (' . $this->p->t('tools', 'vorlageWohnsitzfinanzamt') . ')' => ''] as $finanz_bez => $finanz_param) { ?>
							<?php foreach (['de' => [$this->p->t('public', 'deutsch'), 'Studienerfolg'], 'en' => [$this->p->t('public', 'englisch'), 'StudienerfolgEng']] as $lang_kurz => $lang) { ?>
								<?php foreach ([$stsem => $stsem, $this->p->t('tools', 'alleStudiensemester') => 'alle&all=1'] as $sem_bez => $sem_param) { ?>
									<li class="list-group-item">
										<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=' . $lang[1] . '&xml=studienerfolg.rdf.php&ss=' . $sem_param . '&uid=' . $uid . $finanz_param); ?>">
											<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienerfolgsbestaetigung') . ' ' . $sem_bez . ' ' . $lang[0] . $finanz_bez; ?>
										</a>
									</li>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>
	<?php } else { ?>
		<div class="alert alert-danger" role="alert">
			<?= $this->p->t('tools', 'keinStatusImStudiensemester', ['stsem' => $stsem]); ?>
		</div>
	<?php } ?>

	<?php if ($selfservice !== null) { ?>
		<div class="card mb-3">
			<h3 class="card-header h5"><?= $this->p->t('tools', 'abschlussdokumente'); ?></h3>
			<ul class="list-group list-group-flush">
				<?php if (count($selfservice)) { ?>
					<li class="list-group-item list-group-item-warning">
						<?= $this->p->t('tools', 'warnungDruckDigitaleSignatur'); ?>
					</li>
					<?php foreach ($selfservice as $row) { ?>
						<li class="list-group-item">
							<a class="text-decoration-none" target="_blank" href="<?= base_url('dokumente.php?action=download&id='.$row->akte_id.'&uid='.$uid); ?>">
								<?php /* TODO(chris): datum & link */ ?>
								<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $row->bezeichnung; ?>
							</a>
						</li>
					<?php } ?>
				<?php } else { ?>
					<li class="list-group-item list-group-item-danger">
						<?= $this->p->t('tools', 'nochKeineAbschlussdokumenteVorhanden'); ?>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
