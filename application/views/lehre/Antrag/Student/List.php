<?php
$sitesettings = array(
	'title' => 'Antrag auf Änderung des Studierendenstatus',
	'cis' => true,
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'phrases' => array(
	),
	'customJSModules' => array('public/js/apps/lehre/Antrag/Student.js'),
	'customCSSs' => array(
		'public/css/Fhc.css',
		'public/css/components/primevue.css',
	),
	'customJSs' => array(
	)
);

if(defined('CIS4')){
	$this->load->view(
		'templates/CISVUE-Header',
		$sitesettings
	);
}else{
	$this->load->view(
		'templates/FHC-Header',
		$sitesettings
	);
}
?>

<div id="wrapper">

	<div class="fhc-header">
		<h1 class="h2"><?= $this->p->t('studierendenantrag', 'antrag_header'); ?></h1>
	</div>

	<div class="fhc-container row">
		<div class="col-xs-8">
			<?php if ($antraege) { ?>
				<?php foreach($antraege as $prestudent_id => $array){ ?>
					<h4><?= $array['bezeichnungStg']; ?> (<?= $array['bezeichnungOrgform']; ?>)</h4>

					<?php foreach ($array['allowedNewTypes'] as $type) { ?>
						<div class="alert alert-secondary">
							<p><?= $this->p->t('studierendenantrag', 'calltoaction_' . $type); ?></p>
							<hr>
							<a 
								href="<?= site_url('lehre/Studierendenantrag/' . strtolower($type) . '/' . $prestudent_id); ?>" 
								class="btn btn-outline-secondary"
								>
								<i class="fa fa-plus"></i> <?= $this->p->t('studierendenantrag', 'antrag_typ_' . $type); ?>
							</a>
						</div>
					<?php } ?>

					<table class="table">
						<thead>
							<tr>
								<th>#</th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_typ'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_status'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_studiensemester'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_erstelldatum'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_datum_wiedereinstieg'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_grund'); ?></th>
								<th><?= $this->p->t('studierendenantrag', 'antrag_dateianhaenge'); ?></th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($array['antraege'] as $antrag){ ?>
							<tr>
								<td><?= $antrag->studierendenantrag_id; ?></td>
								<td><?= $this->p->t('studierendenantrag', 'antrag_typ_' . $antrag->typ); ?></td>
								<td>
									<?=
										(
											$antrag->status == Studierendenantragstatus_model::STATUS_PAUSE
											&& $antrag->status_insertvon == Studierendenantragstatus_model::INSERTVON_DEREGISTERED
										)
										? $this->p->t('studierendenantrag', 'status_stop')
										: $antrag->status_bezeichnung;
									?>
								</td>
								<td><?= $antrag->studiensemester_kurzbz; ?></td>
								<td><?= (new DateTime($antrag->datum))->format('d.m.Y'); ?></td>
								<td><?= $antrag->datum_wiedereinstieg ? (new DateTime($antrag->datum_wiedereinstieg))->format('d.m.Y') : ''; ?></td>
								<td><!-- Button trigger modal -->
									<?php if($antrag->grund){ ?>
									<a href="#modalgrund<?= $antrag->studierendenantrag_id; ?>" data-bs-toggle="modal">
										<?= $this->p->t('studierendenantrag', 'antrag_grund'); ?>
									</a>

									<!-- Modal -->
									<div
										class="modal fade"
										id="modalgrund<?= $antrag->studierendenantrag_id; ?>"
										tabindex="-1"
										aria-labelledby="modalgrundLabel<?= $antrag->studierendenantrag_id; ?>"
										aria-hidden="true"
										>
										<div class="modal-dialog modal-dialog modal-lg">
											<div class="modal-content">
												<div class="modal-header">
													<h5
														class="modal-title"
														id="modalgrundLabel<?= $antrag->studierendenantrag_id; ?>"
														>
														<?= $this->p->t('studierendenantrag', 'antrag_grund'); ?>
													</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
													<textarea
														class="form-control"
														style="width: 100%; height: 250px;"
														readonly
														>
														<?= $antrag->grund; ?>
													</textarea>
												</div>
											</div>
										</div>
									</div>
									<?php } ?>
								</td>
								<td>
									<?php if($antrag->dms_id) {?>
									<a
										class="text-decoration-none"
										href="<?= site_url('lehre/Antrag/Attachment/show/' . $antrag->dms_id) ?>"
										target="_blank">
										<i class="fa fa-paperclip" aria-hidden="true"></i> <?= $this->p->t('studierendenantrag', 'antrag_anhang'); ?>
									</a>
									<?php } ?>
								</td>
								<td>
									<a
										href="<?= site_url('lehre/Studierendenantrag/' .
											strtolower($antrag->typ) .
											'/' .
											$antrag->prestudent_id .
											'/' .
											$antrag->studierendenantrag_id); ?>"
										>
										<i class="fa-solid fa-pen" title="<?= $this->p->t('studierendenantrag', 'btn_edit'); ?>"></i>
									</a>
									<?php
									$allowed = [];
									switch ($antrag->typ) {
										case Studierendenantrag_model::TYP_ABMELDUNG:
											$allowed = [
												Studierendenantragstatus_model::STATUS_APPROVED
											];
											break;
										case Studierendenantrag_model::TYP_ABMELDUNG_STGL:
											$allowed = [
												Studierendenantragstatus_model::STATUS_OBJECTION_DENIED,
												Studierendenantragstatus_model::STATUS_DEREGISTERED
											];
											break;
										case Studierendenantrag_model::TYP_UNTERBRECHUNG:
											$allowed = [
												Studierendenantragstatus_model::STATUS_APPROVED,
												Studierendenantragstatus_model::STATUS_REMINDERSENT
											];
											break;
										case Studierendenantrag_model::TYP_WIEDERHOLUNG:
											$allowed = [
												Studierendenantragstatus_model::STATUS_DEREGISTERED
											];
											break;
									}
									if (in_array($antrag->status, $allowed)) { ?>
										<a
											class="ms-2"
											target="_blank"
											href="<?= base_url('cis/private/pdfExport.php?xml=Antrag' .
											$antrag->typ .
											'.xml.php&xsl=Antrag' .
											$antrag->typ .
											'&id=' .
											$antrag->studierendenantrag_id .
											'&uid=' .
											getAuthUID()); ?>"
											>
											<i
												class="fa-solid fa-download"
												title="<?= $this->p->t('studierendenantrag', 'btn_download_antrag'); ?>"
												>
											</i>
										</a>
									<?php } ?>
									<?php if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG
										&& $antrag->status == Studierendenantragstatus_model::STATUS_APPROVED
									) { ?>
										<a
											class="ms-2"
											href="#modallv<?= $antrag->studierendenantrag_id; ?>"
											data-bs-toggle="modal"
											>
											<?= $this->p->t('studierendenantrag', 'btn_show_lvs'); ?>
										</a>
										<lv-popup
											id="modallv<?= $antrag->studierendenantrag_id; ?>"
											antrag-id = "<?= $antrag->studierendenantrag_id; ?>"
											>
											<?= $this->p->t('studierendenantrag', 'my_lvs'); ?>
										</lv-popup>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } ?>
			<?php } else { ?>
				<p class="alert alert-danger" role="alert">
					<?= $this->p->t('studierendenantrag', 'error_no_student'); ?>
				</p>
			<?php } ?>
		</div>
		<div class="col-xs-4">
		</div>
	</div>
</div>

<?php
if (defined('CIS4')) {
	$this->load->view(
		'templates/CISVUE-Footer',
		$sitesettings
	);
} else {
	$this->load->view(
		'templates/FHC-Footer',
		$sitesettings
	);
}
