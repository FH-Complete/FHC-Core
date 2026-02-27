<?php
$includesArray = array(
	'title' => 'Documents',
	'tabulator5' => true,
	'customJSModules' => ['public/js/apps/Cis/Documents.js']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<div class="fhc-header">
		<h1><?= $this->p->t('tools', 'dokumente'); ?><small><?= $this->p->t('tools', 'bestaetigungenZeugnisse'); ?></small></h1>
	</div>
	
	<div class="row">
	
		<div class="order-2 col-lg-8">
			<div class="fhc-table mb-3">
				<div class="fhc-table-header d-flex align-items-center mb-2 gap-2">
					<h3 class="h5 col m-0"><?= $this->p->t('tools', 'inskriptionsbestaetigung'); ?><?= $studienbuchblatt ? ' & ' . $this->p->t('tools', 'studienbuchblatt') : ''; ?></h3>
					<?php if (count($stgs) != 1) { ?>
						<div class="col-auto">
							<select class="form-select" @input="changeFilter('inscriptiontable', 'Stg', $event)">
								<option value="">Alle</option>
								<?php foreach ($stgs as $stg) { ?>
									<option value="<?= $stg->bezeichnung; ?>">
										<?= $stg->bezeichnung; ?>
									</option>
								<?php } ?>
							</select>
						</div>
					<?php } ?>
					<div class="col-auto">
						<select class="form-select" @input="changeFilter('inscriptiontable', 'Stsem', $event)">
							<option value="">Alle</option>
							<?php foreach ($stsemArray as $sem) { ?>
								<option value="<?= $sem; ?>">
									<?= $sem; ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<table ref="inscriptiontable">
					<thead>
						<tr>
							<th tabulator-formatter="html">Dokument</th>
							<?php if (count($stgs) != 1) { ?>
								<th tabulator-field="Stg">Studiengang</th>
							<?php } ?>
							<th tabulator-field="Stsem">Studiensemester</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($stgs as $stg) { ?>
							<?php foreach ($stg->studiensemester as $stsem => $sem) { ?>
								<?php if (true && $sem->inskriptionsbestaetigung) { ?>
									<tr>
										<td>
												<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Inskription&xml=student.rdf.php&ss=' . $stsem . '&uid=' . $uid . '&xsl_stg_kz=' . $stg->studiengang_kz); ?>">
													<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'inskriptionsbestaetigung'); ?>
												</a>
										</td>
										<?php if (count($stgs) != 1) { ?>
											<td><?= $stg->bezeichnung; ?></td>
										<?php } ?>
										<td><?= $stsem; ?></td>
									</tr>
									<?php if ($studienbuchblatt) { ?>
										<tr>
											<td>
												<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=Studienblatt&xml=studienblatt.xml.php&ss=' . $stsem . '&uid=' . $uid); ?>">
													<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienbuchblatt'); ?>
												</a>
											</td>
											<?php if (count($stgs) != 1) { ?>
												<td><?= $stg->bezeichnung; ?></td>
											<?php } ?>
											<td><?= $stsem; ?></td>
										</tr>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<?php if ($studienerfolgsbestaetigung) { ?>
				<div class="fhc-table mb-3">
					<div class="fhc-table-header d-flex align-items-center mb-2 gap-2">
						<h3 class="h5 col m-0"><?= $this->p->t('tools', 'studienerfolgsbestaetigung'); ?></h3>
						<?php if (count($stgs) != 1) { ?>
							<div class="col-auto">
								<select class="form-select" @input="changeFilter('studienerfolgsbestaetigungtable', 'Stg', $event)">
									<option value="">Alle</option>
									<?php foreach ($stgs as $stg) { ?>
										<option value="<?= $stg->bezeichnung; ?>">
											<?= $stg->bezeichnung; ?>
										</option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>
						<div class="col-auto">
							<select class="form-select" @input="changeFilter('studienerfolgsbestaetigungtable', 'Stsem', $event)">
								<option value="<?= $this->p->t('tools', 'alleStudiensemester'); ?>"><?= $this->p->t('tools', 'alleStudiensemester'); ?></option>
								<?php foreach ($stsemArray as $sem) { ?>
									<option value="<?= $sem; ?>">
										<?= $sem; ?>
									</option>
								<?php } ?>
							</select>
						</div>
						<div class="col-auto">
							<select class="form-select" @input="changeFilter('studienerfolgsbestaetigungtable', 'Lang', $event)">
								<option value="">Alle</option>
								<option value="<?= $this->p->t('global', 'deutsch'); ?>"><?= $this->p->t('global', 'deutsch'); ?></option>
								<option value="<?= $this->p->t('global', 'englisch'); ?>"><?= $this->p->t('global', 'englisch'); ?></option>
							</select>
						</div>
					</div>
					<table ref="studienerfolgsbestaetigungtable">
						<thead>
							<tr>
								<th tabulator-formatter="html">Dokument</th>
								<?php if (count($stgs) != 1) { ?>
									<th tabulator-field="Stg">Studiengang</th>
								<?php } ?>
								<th tabulator-field="Stsem">Studiensemester</th>
								<th tabulator-field="Lang">Sprache</th>
								<th tabulator-field="Finance" tabulator-formatter="tickCross"><?= $this->p->t('tools', 'vorlageWohnsitzfinanzamt'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach (['Studienerfolg' => $this->p->t('global', 'deutsch'), 'StudienerfolgEng' => $this->p->t('global', 'englisch')] as $lang_xsl => $lang) { ?>
								<?php foreach ([true, false] as $finance) { ?>
									<?php foreach ($stgs as $stg) { ?>
										<tr>
											<td>
												<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=' . $lang_xsl . '&xml=studienerfolg.rdf.php&ss=alle&all=1&uid=' . $uid . ($finance ? '&typ=finanzamt' : '')); ?>">
													<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienerfolgsbestaetigung'); ?>
												</a>
											</td>
											<?php if (count($stgs) != 1) { ?>
												<td><?= $stg->bezeichnung; ?></td>
											<?php } ?>
											<td><?= $this->p->t('tools', 'alleStudiensemester'); ?></td>
											<td><?= $lang; ?></td>
											<td><?= $finance; ?></td>
										</tr>
										<?php foreach ($stg->studiensemester as $stsem => $sem) { ?>
											<tr>
												<td>
													<a class="text-decoration-none" target="_blank" href="<?= base_url('cis/private/pdfExport.php?xsl=' . $lang_xsl . '&xml=studienerfolg.rdf.php&ss=' . $stsem . '&uid=' . $uid . ($finance ? '&typ=finanzamt' : '')); ?>">
														<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $this->p->t('tools', 'studienerfolgsbestaetigung'); ?>
													</a>
												</td>
												<?php if (count($stgs) != 1) { ?>
													<td><?= $stg->bezeichnung; ?></td>
												<?php } ?>
												<td><?= $stsem; ?></td>
												<td><?= $lang; ?></td>
												<td><?= $finance; ?></td>
											</tr>
										<?php } ?>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
			<?php if ($selfservice !== null) { ?>
				<div class="fhc-table mb-3">
					<div class="fhc-table-header d-flex align-items-center mb-2 gap-2">
						<h3 class="h5 col m-0"><?= $this->p->t('tools', 'abschlussdokumente'); ?></h3>
					</div>
					<table ref="abschlussdokumentetable">
						<thead>
							<tr>
								<th tabulator-formatter="html">Dokument</th>
								<th tabulator-field="Date">Datum</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($selfservice as $row) { ?>
								<tr>
									<td>
										<a class="text-decoration-none" target="_blank" href="<?= site_url('Cis/Documents/download/' . $row->akte_id . ($row->person_id != getAuthPersonId() ? '/' . $uid : '')); ?>">
										<img class="align-baseline" src="<?= base_url('skin/images/pdfpic.gif'); ?>" alt="PDF"> <?= $row->bezeichnung; ?>
									</a>
									<td><?= (new DateTime($row->erstelltam))->format('d.m.Y'); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
		<?php if ($selfservice) { ?>
			<div class="order-1 order-lg-3 col-lg-4">
				<div class="alert alert-warning" role="alert">
					<?= $this->p->t('tools', 'warnungDruckDigitaleSignatur'); ?>
				</div>
			</div>
		<?php } ?>
		
	</div>
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
