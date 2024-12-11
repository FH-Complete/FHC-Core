<?php
$this->load->config('anrechnung');

$includesArray = array(
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenPruefen'),
		'jquery3' => true,
		'jqueryui1' => true,
		'bootstrap5' => true,
		'cis' => true,
		'fontawesome4' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'phrases' => array(
			'global' => array(
				'anerkennungNachgewiesenerKenntnisse',
				'antragStellen'
			),
			'ui' => array(
				'hilfeZuDieserSeite',
				'hochladen',
				'nichtSelektierbarAufgrundVon',
				'systemfehler',
				'bitteMindEinenAntragWaehlen',
				'bitteBegruendungAngeben',
				'bitteBegruendungVervollstaendigen',
				'anrechnungenWurdenEmpfohlen',
				'anrechnungenWurdenNichtEmpfohlen'
			),
			'person' => array(
				'student',
				'personenkennzeichen'
			),
			'lehre' => array(
				'studiensemester',
				'studiengang',
				'lehrveranstaltung',
				'ects',
				'lektor',
			),
			'anrechnung' => array(
				'empfehlungPositivConfirmed',
				'empfehlungNegativConfirmed'
			)
		),
		'customCSSs' => array(
			'public/css/lehre/anrechnung.css'
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/reviewAnrechnungDetail.js'
		)
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
		<!-- title -->
		<div class="row">
			<div class="col-lg-12 my-4 border-bottom">
				<h3 class="fw-normal ">
					<?php echo $this->p->t('anrechnung', 'anrechnungenPruefen'); ?>
					<small class="text-secondary fs-6">| <?php echo $this->p->t('global', 'detailsicht'); ?></small>
				</h3>
			</div>
		</div>

		<div class="row">
			<div class="col-8">
				<!-- Antragsdaten -->
				<div class="row mb-4">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<span
									class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'antrag'); ?></b></span>
								</span>&emsp;
								<span class="reviewAnrechnungDetail-anrechnungInfoTooltip" data-bs-toggle="tooltip"
									data-bs-placement="right" data-bs-html="true"
									title="<?php echo $this->p->t('anrechnung', 'anrechnungInfoTooltipText'); ?>">
									<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
								</span>
								<span class="float-end"><?php echo $this->p->t('anrechnung', 'antragdatum'); ?>: <span
										id="reviewAnrechnung-status"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-lg-4">
										<table class="table table-bordered table-condensed table-fixed mb-0">
											<tbody>
												<tr>
													<th class="col-5">
														<?php echo ucfirst($this->p->t('person', 'studentIn')); ?>
													</th>
													<td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?>
													</td>
												</tr>
												<tr>
													<th class="col-5">
														<?php echo $this->p->t('person', 'personenkennzeichen'); ?>
													</th>
													<td><?php echo $antragData->matrikelnr ?></td>
												</tr>
												<tr>
													<th class="col-5">
														<?php echo ucfirst($this->p->t('lehre', 'studiensemester')); ?>
													</th>
													<td><?php echo $antragData->studiensemester_kurzbz ?></td>
												</tr>
												<tr>
													<th class="col-5">
														<?php echo ucfirst($this->p->t('lehre', 'studiengang')); ?>
													</th>
													<td><?php echo $antragData->stg_bezeichnung ?></td>
												</tr>
												<tr>
													<th class="col-5">
														<?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?>
													</th>
													<td><?php echo $antragData->lv_bezeichnung ?></td>
												</tr>
												<tr>
													<th class="col-5"><?php echo $this->p->t('lehre', 'ects'); ?></th>
													<td><?php echo $antragData->ects ?></td>
												</tr>
												<tr>
													<th class="col-5"><?php echo $this->p->t('lehre', 'lektorInnen'); ?>
													</th>
													<td>
														<?php $len = count($antragData->lektoren) - 1 ?>
														<?php foreach ($antragData->lektoren as $key => $lektor): ?>
															<?php echo $lektor->vorname . ' ' . $lektor->nachname;
															echo $key === $len ? '' : ', ' ?>
														<?php endforeach; ?>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-lg-8">
										<table class="table table-bordered table-condensed table-fixed mb-0">
											<tbody>

												<tr>
													<th class="col-4">
														<?php echo ucfirst($this->p->t('global', 'zgv')); ?>
													</th>
													<td><?php echo $antragData->zgv ?></td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?>
													</th>
													<td><?php echo $anrechnungData->anmerkung ?></td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?>
													</th>
													<td>
														<a href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
															target="_blank"><?php echo htmlentities($anrechnungData->dokumentname) ?></a>
													</td>
												</tr>
											<?php if ($this->config->item('explain_equivalence')): ?>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'begruendungEctsLabel'); ?>
													</th>
													<td><span><?php echo $anrechnungData->begruendung_ects ?></span>
													</td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'begruendungLvinhaltLabel'); ?>
													</th>
													<td><span><?php echo $anrechnungData->begruendung_lvinhalt ?></span>
													</td>
												</tr>
											<?php endif; ?>
											</tbody>
										</table>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Empfehlungsdaten -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card mb-4" id="reviewAnrechnungDetail-empfehlung"
							data-empfehlung="<?php echo json_encode($empfehlungData->empfehlung) ?>">
							<div class="card-header">
								<span
									class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'empfehlung'); ?></b></span>
								<div class="float-end">
									<?php echo $this->p->t('anrechnung', 'empfehlungVon'); ?>:
									<span
										id="reviewAnrechnungDetail-empfehlungVon"><?php echo $empfehlungData->empfehlung_von ?></span>
									&emsp;|&emsp;
									<?php echo $this->p->t('anrechnung', 'empfehlungdatum'); ?>:
									<span
										id="reviewAnrechnungDetail-empfehlungAm"><?php echo $empfehlungData->empfehlung_am ?></span>

								</div>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-lg-6">
										<table class="table table-bordered table-condensed table-fixed mb-0">
											<tbody>
												<tr>
													<th class="col-4">
														<?php echo ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAm')); ?>
													</th>
													<td
														id="reviewAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAm">
														<?php echo $empfehlungData->empfehlungsanfrageAm; ?>
													</td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'empfehlungsanfrageAn'); ?>
													</th>
													<td
														id="reviewAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAn">
														<?php echo $empfehlungData->empfehlungsanfrageAn; ?>
													</td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAm')); ?>
													</th>
													<td id="reviewAnrechnungDetail-empfehlungDetail-empfehlungAm">
														<?php echo $empfehlungData->empfehlung_am ?>
													</td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo ucfirst($this->p->t('anrechnung', 'empfehlungVon')); ?>
													</th>
													<td id="reviewAnrechnungDetail-empfehlungDetail-empfehlungVon">
														<?php echo $empfehlungData->empfehlung_von ?>
													</td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('anrechnung', 'empfehlung'); ?>
													</th>
													<td id="reviewAnrechnungDetail-empfehlungDetail-empfehlung"></td>
												</tr>
												<tr>
													<th class="col-4">
														<?php echo $this->p->t('global', 'begruendung'); ?>
													</th>
													<td id="reviewAnrechnungDetail-empfehlungDetail-begruendung">
														<?php echo htmlentities($empfehlungData->begruendung) ?>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-lg-6">
										<div>
											<label class="text-center mb-2">
												<b><?php echo ucfirst($this->p->t('global', 'notizDerSTGL')); ?></b>
											</label>
											<textarea rows="4"
												readonly><?php echo htmlentities($empfehlungData->notiz) ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<form>
								<input type="hidden" name="anrechnung_id"
									value="<?php echo $anrechnungData->anrechnung_id ?>">
								<!-- Nicht empfehlen panel (hidden) -->
								<div class="card card-body" style="display: none"
									id="reviewAnrechnungDetail-begruendung-panel">
									<div class="mb-4">
										<h4 class="card card-body bg-danger-subtle my-3">
											<?php echo $this->p->t('anrechnung', 'empfehlungNegativQuestion'); ?>
										</h4>
										<ul class="list-group mb-4">
											<li class="list-group-item">
												<span><?php echo $this->p->t('anrechnung', 'empfehlungNegativPruefungNichtMoeglich'); ?></span>&emsp;
												<span class="btn-copyIntoTextarea float-end" data-bs-toggle="tooltip"
													data-bs-placement="right" data-bs-html="true"
													title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
											</li>
											<li class="list-group-item">
												<span><?php echo $this->p->t('anrechnung', 'empfehlungNegativKenntnisseNichtGleichwertigWeil'); ?></span>&emsp;
												<span class="btn-copyIntoTextarea float-end" data-bs-toggle="tooltip"
													data-bs-placement="right" data-bs-html="true"
													title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
											</li>
											<li class="list-group-item"
												onclick="{ $(this).closest('div').find('textarea').val('').focus()}">
												<span><?php echo $this->p->t('anrechnung', 'andereBegruendung'); ?></span>
											</li>
										</ul>
										<textarea class="my-3 form-control" name="begruendung"
											id="reviewAnrechnungDetail-begruendung" rows="2"
											placeholder="<?php echo $this->p->t('anrechnung', 'textUebernehmenOderEigenenBegruendungstext'); ?>"
											required></textarea>
									</div>

									<!-- Button Abbrechen & Bestaetigen-->
									<div class="d-flex justify-content-end">
										<button id="reviewAnrechnungDetail-begruendung-abbrechen"
											class="me-1 btn btn-outline-secondary btn-w200" type="reset">
											<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
										</button>
										<button id="reviewAnrechnungDetail-dont-recommend-anrechnung-confirm"
											class="btn btn-primary btn-w200" type="button">
											<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
										</button>
									</div>
								</div>
								<!-- Empfehlen panel (hidden)-->
								<div class="card card-body" style="display: none"
									id="reviewAnrechnungDetail-empfehlung-panel">
									<div class="my-3">
										<h4 class="card card-body bg-success-subtle">
											<?php echo $this->p->t('anrechnung', 'empfehlungPositivQuestion'); ?>
										</h4>
										<span><?php echo $this->p->t('anrechnung', 'empfehlungPositivSubquestion'); ?>
										</span>
									</div>

									<!-- Action Button Abbrechen & Bestaetigen-->
									<div class="d-flex justify-content-end">
										<button id="reviewAnrechnungDetail-empfehlung-abbrechen"
											class="me-1 btn btn-outline-secondary btn-w200" type="reset">
											<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
										</button>
										<button id="reviewAnrechnungDetail-recommend-anrechnung-confirm"
											class="btn btn-primary btn-w200" type="button">
											<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
										</button>
									</div>
								</div>
							</form>
						</div>
						<!-- Button Empfehlen / Nicht Empfehlen -->
						<div class="row justify-content-center justify-content-sm-end align-items-center mb-5">
							<div class="col-auto mb-1">
							<button id="reviewAnrechnungDetail-dont-recommend-anrechnung-ask"
								class=" btn btn-danger btn-w200" type="button" <?php echo (is_null($empfehlungData->empfehlung) && $isEmpfehlungsberechtigt) ? '' : 'disabled' ?>>
								<?php echo ucfirst($this->p->t('anrechnung', 'nichtEmpfehlen')); ?>
							</button>
							</div>
							<div class="col-auto mb-1">
							<button id="reviewAnrechnungDetail-recommend-anrechnung-ask"
								class="btn btn-primary btn-w200" type="button" <?php echo (is_null($empfehlungData->empfehlung) && $isEmpfehlungsberechtigt) ? '' : 'disabled' ?>>
								<?php echo ucfirst($this->p->t('anrechnung', 'empfehlen')); ?>
							</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-4 ">
				<div class="mb-5 alert text-center">
					Status:
					<b><span class="text-uppercase" id="reviewAnrechnungDetail-status_kurzbz"
							data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
							<?php echo $anrechnungData->status; ?>
						</span></b>
				</div>

				<?php $this->load->view('lehre/anrechnung/reviewAnrechnungInfo'); ?>

			</div>
		</div>

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
