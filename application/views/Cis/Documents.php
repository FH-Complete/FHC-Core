<?php
$includesArray = array();

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Documents</h2>
	<hr>
	<div class="mb-3">
		<select class="form-select" onchange="location = this.value">
			<?php foreach ($stsemArray as $sem) { ?>
				<option value="<?= site_url('Cis/Documents/Semester/' . $sem); ?>"<?= $sem == $stsem ? ' selected': ''; ?>>
					<?= $sem; ?>
				</option>
			<?php } ?>
		</select>
	</div>

	<?php if ($hasSemester) { ?>
		<h3><?= $this->p->t('tools', 'inskriptionsbestaetigung'); ?></h3>
		<ul class="list-unstyled">
			<?php foreach ($inskriptionsbestaetigungen as $stg => $hasPaid) { ?>
				<?php if (count($studiengaenge) != 1) { ?>
					<li>
						<b><?= $studiengaenge[$stg]->bezeichnung; ?></b>
					</li>
				<?php } ?>
				<?php if ($hasPaid) { ?>
					<li>
						<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Inskription&xml=student.rdf.php&ss=' . $stsem . '&uid=' . $uid . '&xsl_stg_kz=' . $stg); ?>">
							<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'inskriptionsbestaetigung') . ' ' . $stsem; ?>
						</a>
					</li>
				<?php } else { ?>
					<li class="text-danger">
						<?= $this->p->t('tools', 'studienbeitragFuerSSNochNichtBezahlt', ['stsem' => $stsem]); ?>
					</li>
				<?php } ?>
			<?php } ?>
		</ul>
		<?php if ($studienbuchblatt) { ?>
			<h3><?= $this->p->t('tools', 'studienbuchblatt'); ?></h3>
			<ul class="list-unstyled">
				<?php foreach ($inskriptionsbestaetigungen as $stg => $hasPaid) { ?>
					<?php if (count($studiengaenge) != 1) { ?>
						<li>
							<b><?= $studiengaenge[$stg]->bezeichnung; ?></b>
						</li>
					<?php } ?>
					<?php if ($hasPaid) { ?>
						<li>
							<?php /* TODO(chris): studiengang_kz? */ ?>
							<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Studienblatt&xml=studienblatt.xml.php&ss=' . $stsem . '&uid=' . $uid); ?>">
								<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienbuchblatt') . ' ' . $stsem; ?>
							</a>
						</li>
					<?php } else { ?>
						<li class="text-danger">
							<?= $this->p->t('tools', 'studienbeitragFuerSSNochNichtBezahlt', ['stsem' => $stsem]); ?>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php } ?>
		<?php if ($studienerfolgsbestaetigung) { ?>
			<h3><?= $this->p->t('tools', 'studienerfolgsbestaetigung'); ?></h3>
			<ul class="list-unstyled">
				<?php foreach ($studiengaenge as $stg => $bezeichnung) { ?>
					<?php if (count($studiengaenge) != 1) { ?>
						<li>
							<b><?= $bezeichnung; ?></b>
						</li>
					<?php } ?>
					<?php foreach (['' => '&typ=finanzamt', ' (' . $this->p->t('tools', 'vorlageWohnsitzfinanzamt') . ')' => ''] as $finanz_bez => $finanz_param) { ?>
						<?php foreach (['de' => [$this->p->t('public', 'deutsch'), 'Studienerfolg'], 'en' => [$this->p->t('public', 'englisch'), 'StudienerfolgEng']] as $lang_kurz => $lang) { ?>
							<?php foreach ([$stsem => $stsem, $this->p->t('tools', 'alleStudiensemester') => 'alle&all=1'] as $sem_bez => $sem_param) { ?>
								<li>
									<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=' . $lang[1] . '&xml=studienerfolg.rdf.php&ss=' . $sem_param . '&uid=' . $uid . $finanz_param); ?>">
										<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienerfolgsbestaetigung') . ' ' . $sem_bez . ' ' . $lang[0] . $finanz_bez; ?>
									</a>
								</li>
								<?php /* TODO(chris): Finanzamt $this->p->t('tools/vorlageWohnsitzfinanzamt') */ ?>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php } ?>
	<?php } else { ?>
		<div class="alert alert-danger" role="alert">
			<?= $this->p->t('tools', 'keinStatusImStudiensemester', ['stsem' => $stsem]); ?>
		</div>
	<?php } ?>
	TODO(chris): IMPLEMENT!
	
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
