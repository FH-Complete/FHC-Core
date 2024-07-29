<?php
$this->load->config('anrechnung');
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenGenehmigen'),
		'jquery3' => true,
		'jqueryui1' => true,
		'bootstrap3' => true,
		'fontawesome4' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'phrases' => array(
			'global' => array(
				'anerkennungNachgewiesenerKenntnisse',
				'antragStellen',
                'begruendung'
			),
			'ui' => array(
				'hilfeZuDieserSeite',
				'hochladen',
				'nichtSelektierbarAufgrundVon',
				'nichtSelektierbarAufgrundVon',
				'systemfehler',
				'bitteMindEinenAntragWaehlen',
				'bitteBegruendungAngeben',
                'bitteBegruendungVervollstaendigen',
				'empfehlungWurdeAngefordert',
				'anrechnungenWurdenGenehmigt',
				'anrechnungenWurdenAbgelehnt',
                'nurLeseberechtigung'
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
                'genehmigungAblehnungWirklichZuruecknehmen',
                'empfehlungsanforderungWirklichZuruecknehmen',
                'erfolgreichZurueckgenommen',
                'empfehlungPositivConfirmed',
                'empfehlungNegativConfirmed',
                'anrechnungEctsTooltipTextBeiUeberschreitung'
            )
		),
		'customCSSs' => array(
			'public/css/Tabulator.css'
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/approveAnrechnungDetail.js'

		)
	)
);
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <!-- header  -->
        <div class="row">
            <div class="col-lg-12 page-header">
                <h3>
					<?php echo $this->p->t('anrechnung', 'anrechnungenGenehmigen'); ?>
                    <small>| <?php echo $this->p->t('global', 'detailsicht'); ?></small>
                </h3>
            </div>
        </div>
		<!--end header -->
		
        <div class="row" id="approveAnrechnungDetail-generell" data-readonly="<?php echo json_encode($hasReadOnlyAccess)?>">
            <div class="col-xs-8">
				<!-- Antragsdaten -->
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'antrag'); ?></b></span>&emsp;
								<span class="approveAnrechnungDetail-anrechnungInfoTooltip"
									  data-toggle="tooltip" data-placement="right"
									  title="<?php echo $this->p->t('anrechnung', 'anrechnungInfoTooltipText'); ?>">
													<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
												</span>
								<span class="pull-right"><?php echo $this->p->t('anrechnung', 'antragdatum'); ?>: <span
											id="approveAnrechnung-status"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-4">
										<table class="table table-bordered table-condensed table-fixed">
											<tbody>
											<tr>
												<th class="col-xs-5"><?php echo ucfirst($this->p->t('person', 'studentIn')); ?></th>
												<td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?></td>
											</tr>
											<tr>
												<th class="col-xs-5"><?php echo $this->p->t('person', 'personenkennzeichen'); ?></th>
												<td><?php echo $antragData->matrikelnr ?></td>
											</tr>
											<tr>
												<th class="col-xs-5"><?php echo ucfirst($this->p->t('lehre', 'studiensemester')); ?></th>
												<td><?php echo $antragData->studiensemester_kurzbz ?></td>
											</tr>
											<tr>
												<th class="col-xs-5"><?php echo ucfirst($this->p->t('lehre', 'studiengang')); ?></th>
												<td><?php echo $antragData->stg_bezeichnung ?></td>
											</tr>
											<tr>
												<th class="col-xs-5"><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?></th>
												<td><?php echo $antragData->lv_bezeichnung ?></td>
											</tr>
                                            <tr>
                                                <th class="col-xs-5"><?php echo $this->p->t('lehre', 'lektorInnen'); ?></th>
                                                <td>
													<?php $len = count($antragData->lektoren) - 1 ?>
													<?php foreach ($antragData->lektoren as $key => $lektor): ?>
														<?php echo $lektor->vorname . ' ' . $lektor->nachname;
														echo $key === $len ? '' : ', ' ?>
													<?php endforeach; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="col-xs-5"><?php echo $this->p->t('lehre', 'ects'); ?></th>
                                                <td><span id="ects"><?php echo $antragData->ects ?></span></td>
                                            </tr>
                                            <tr>
                                                <th class="col-xs-5">
													<?php echo $this->p->t('anrechnung', 'bisherAngerechneteEcts'); ?>
                                                    <span class="approveAnrechnungDetail-anrechnungEctsTooltip"
                                                          data-toggle="tooltip" data-placement="right"
                                                          title="<?php echo $this->p->t('anrechnung', 'anrechnungEctsTooltipText'); ?>">
													<i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
												</span>
                                                </th>
                                                <td>
                                                    Total: <span id="sumEctsTotal"><?php echo number_format($antragData->sumEctsSchulisch + $antragData->sumEctsBeruflich, 1) ?></span>
                                                    [Schulisch: <span id="sumEctsSchulisch" value="<?php echo $antragData->sumEctsSchulisch ?>"><?php echo $antragData->sumEctsSchulisch ?></span> /
                                                    Beruflich: <span id="sumEctsBeruflich" value="<?php echo $antragData->sumEctsBeruflich ?>"><?php echo $antragData->sumEctsBeruflich ?></span> ]
                                                    <span id="sumEctsMsg"></span>
                                                </td>
                                            </tr>
											
											</tbody>
										</table>
									</div>
									<div class="col-lg-8">
										<table class="table table-bordered table-condensed table-fixed">
											<tbody>

											<tr>
												<th class="col-xs-3"><?php echo ucfirst($this->p->t('global', 'zgv')); ?></th>
												<td><?php echo $antragData->zgv ?></td>
											</tr>
											<tr>
												<th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?></th>
												<td><?php echo $anrechnungData->anmerkung ?></td>
											</tr>
											<tr>
												<th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?></th>
												<td>
													<a href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
													   target="_blank"><?php echo htmlentities($anrechnungData->dokumentname) ?></a>
												</td>
											</tr>
                                            <tr>
                                                <th class="col-xs-4"><?php echo $this->p->t('global', 'begruendung'); ?></th>
                                                <td><span id="begruendung_id" data-begruendung_id="<?php echo $anrechnungData->begruendung_id ?>" ><?php echo $anrechnungData->begruendung ?></span></td>
                                            </tr>
											<?php if ($this->config->item('explain_equivalence')): ?>
                                                <tr>
                                                    <th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'begruendungEctsLabel'); ?></th>
                                                    <td><span><?php echo $anrechnungData->begruendung_ects ?></span></td>
                                                </tr>
                                                <tr>
                                                    <th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'begruendungLvinhaltLabel'); ?></th>
                                                    <td><span><?php echo $anrechnungData->begruendung_lvinhalt ?></span></td>
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
						<div class="panel panel-default" id="approveAnrechnungDetail-empfehlung"
								 data-empfehlung="<?php echo json_encode($empfehlungData->empfehlung) ?>">
								<div class="panel-heading">
									<span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'empfehlung'); ?></b></span>&emsp;
									<div class="pull-right">
										<?php echo $this->p->t('anrechnung', 'empfehlungVon'); ?>:
										<span id="approveAnrechnungDetail-empfehlungVon"><?php echo $empfehlungData->empfehlung_von ?></span>
										&emsp;|&emsp;
										<?php echo $this->p->t('anrechnung', 'empfehlungdatum'); ?>:
										<span id="approveAnrechnungDetail-empfehlungAm"><?php echo $empfehlungData->empfehlung_am ?></span>
									</div>
								</div>
								<div class="panel-body">
									<div class="row">
										<div class="col-lg-6">
											<form id="form-empfehlung">
												<input type="hidden" name="anrechnung_id" value="<?php echo $anrechnungData->anrechnung_id ?>">
                                                <input type="hidden" name="ects" value="<?php echo $antragData->ects ?>">
                                                <input type="hidden" name="sumEctsSchulisch" value="<?php echo $antragData->sumEctsSchulisch ?>">
                                                <input type="hidden" name="sumEctsBeruflich" value="<?php echo $antragData->sumEctsBeruflich ?>">
												<table class="table table-bordered table-condensed table-fixed">
													<tbody>
													<tr>
														<th class="col-xs-4"><?php echo ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAm')); ?></th>
														<td id="approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAm">
															<?php echo $empfehlungData->empfehlungsanfrageAm; ?>
														</td>
													</tr>
													<tr>
														<th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'empfehlungsanfrageAn'); ?></th>
														<td id="approveAnrechnungDetail-empfehlungDetail-empfehlungsanfrageAn">
															<?php echo $empfehlungData->empfehlungsanfrageAn; ?>
														</td>
													</tr>
													<tr>
														<th class="col-xs-4"><?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAm')); ?></th>
														<td><?php echo $empfehlungData->empfehlung_am ?></td>
													</tr>
													<tr>
														<th class="col-xs-4"><?php echo ucfirst($this->p->t('anrechnung', 'empfehlungVon')); ?></th>
														<td><?php echo $empfehlungData->empfehlung_von ?></td>
													</tr>
													<tr>
														<th class="col-xs-4"><?php echo $this->p->t('anrechnung', 'empfehlung'); ?></th>
														<td id="approveAnrechnungDetail-empfehlungDetail-empfehlung"></td>
													</tr>
													<tr>
														<th class="col-xs-4"><?php echo $this->p->t('global', 'begruendung'); ?></th>
														<td id="approveAnrechnungDetail-empfehlungDetail-begruendung">
															<?php echo htmlentities($empfehlungData->begruendung) ?>
														</td>
													</tr>
													</tbody>
												</table>
											</form>
										</div>
										<div class="col-lg-6">
											<form id="form-empfehlungNotiz">
												<input type="hidden" name="anrechnung_id"
													   value="<?php echo $anrechnungData->anrechnung_id ?>">
												<input type="hidden" name="notiz_id"
													   value="<?php echo $empfehlungData->notiz_id ?>">
												<div class="form-group">
													<label class="text-center">
														<?php echo ucfirst($this->p->t('global', 'notiz')); ?>
													</label>
													<textarea name="empfehlungText" rows="4"><?php echo htmlentities($empfehlungData->notiz) ?></textarea>
												</div>
												<input type="submit" class="btn btn-default pull-right" value="<?php echo ucfirst($this->p->t('ui', 'speichern')); ?>">
											</form>
										</div>
									</div>
								</div>
							</div>
					  
						<div class="pull-right">
							<button id="approveAnrechnungDetail-withdraw-request-recommedation"
									class="btn btn-default btn-w200 <?php echo (is_null($empfehlungData->empfehlung) &&
										$anrechnungData->status_kurzbz == 'inProgressLektor') ? '' : 'hidden' ?>"
									type="button">
								<?php echo ucfirst($this->p->t('global', 'zuruecknehmen')); ?>
							</button>
							<button id="approveAnrechnungDetail-request-recommendation" class="btn btn-primary btn-w200"
								<?php echo is_null($empfehlungData->empfehlung) && $anrechnungData->status_kurzbz == 'inProgressDP' ? '' : 'disabled' ?>>
								<?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAnfordern')); ?>
							</button>
						</div>
					</div>
				</div>
				<br><br>
				<!-- Genehmigungssdaten -->
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'genehmigung'); ?></b></span>&emsp;
								<div class="pull-right">
									<?php echo $this->p->t('anrechnung', 'abgeschlossenVon'); ?>:
									<span id="approveAnrechnungDetail-abgeschlossenVon"><?php echo $genehmigungData->abgeschlossen_von ?></span>
									&emsp;|&emsp;
									<?php echo $this->p->t('anrechnung', 'abschlussdatum'); ?>:
									<span id="approveAnrechnungDetail-abgeschlossenAm"><?php echo $genehmigungData->abgeschlossen_am ?></span>
								</div>
							</div>
							<div class="panel-body" id="approveAnrechnungDetail-genehmigungDetail">
								<!-- Infopanel: Noch nicht genehmigt / abgelehnt -->
								<div class="panel panel-default panel-body <?php echo is_null($genehmigungData->genehmigung) ? '' : 'hidden' ?>"
									 id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsNull">
									<?php echo $this->p->t('anrechnung', 'nochKeineGenehmigung'); ?>
								</div>
								<!-- Infopanel: Genehmigt -->
								<div class="alert alert-success <?php echo $genehmigungData->genehmigung === true ? '' : 'hidden' ?>" id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv">
									<b><?php echo $this->p->t('anrechnung', 'genehmigungPositiv'); ?></b>
								</div>
								<!-- Infopanel: Abgelehnt -->
								<div class="<?php echo $genehmigungData->genehmigung === false ? '' : 'hidden' ?>" id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ">
									<div class="alert alert-danger">
										<b><?php echo $this->p->t('anrechnung', 'genehmigungNegativ'); ?></b>
									</div>
									<div class="well"><b><?php echo $this->p->t('global', 'begruendung'); ?>
											: </b>
										<span id="approveAnrechnungDetail-genehmigungDetail-begruendung"><?php echo htmlentities($genehmigungData->notiz) ?></span>
									</div>
								</div>
							
							<form>
								<input type="hidden" name="anrechnung_id" value="<?php echo $anrechnungData->anrechnung_id ?>">
								<!-- Ablehnen -->
								<div style="display: none" id="approveAnrechnungDetail-begruendung-panel">
									<div>
										<h4 class="panel panel-body panel-danger text-danger"><?php echo $this->p->t('anrechnung', 'genehmigungNegativQuestion'); ?></h4>
                                        <ul class="list-group">
											<li class="list-group-item">
												<span><?php echo $this->p->t('anrechnung', 'genehmigungNegativPruefungNichtMoeglich'); ?></span>
												<span class="btn-copyIntoTextarea pull-right" data-toggle="tooltip"
													  data-placement="right"
													  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
											</li>
                                            <li class="list-group-item">
                                                <span><?php echo $this->p->t('anrechnung', 'genehmigungNegativEctsHoechstgrenzeUeberschritten'); ?></span>
                                                <span class="btn-copyIntoTextarea pull-right" data-toggle="tooltip"
                                                      data-placement="right"
                                                      title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
                                            </li>
											<li class="list-group-item">
												<span><?php echo $this->p->t('anrechnung', 'genehmigungNegativKenntnisseNichtGleichwertigWeil'); ?></span>
												<span class="btn-copyIntoTextarea pull-right" data-toggle="tooltip"
													  data-placement="right"
													  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
											</li>
											<li class="list-group-item">
												<span><?php echo $this->p->t('anrechnung', 'genehmigungNegativEmpfehlungstextUebernehmen'); ?></span>
												<span id="empfehlungstextUebernehmen" class="btn-copyIntoTextarea pull-right" data-toggle="tooltip"
													  data-placement="right"
													  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
													<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
												</span>
											</li>
                                            <li class="list-group-item" onclick="{ $(this).closest('div').find('textarea').val('').focus()}">
                                                <span><?php echo $this->p->t('anrechnung', 'andereBegruendung'); ?></span>
                                            </li>
										</ul>
										<textarea class="form-control" name="begruendung"
												  id="approveAnrechnungDetail-begruendung"
												  rows="2"
                                                  placeholder="<?php echo $this->p->t('anrechnung', 'textUebernehmenOderEigenenBegruendungstext'); ?>" required></textarea>
									</div>
									<br>
									<!-- Action Button 'Abbrechen'-->
									<div class="pull-right">
										<button id="approveAnrechnungDetail-begruendung-abbrechen"
												class="btn btn-default btn-w200" type="reset">
											<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
										</button>
										<button id="approveAnrechnungDetail-reject-anrechnung-confirm"
												class="btn btn-primary btn-w200" type="button">
											<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
										</button>
									</div>
								</div>
								<!-- Genehmigen -->
								<div style="display: none" id="approveAnrechnungDetail-genehmigung-panel">
									<div>
										<h4 class="panel panel-body panel-success text-success"><?php echo $this->p->t('anrechnung', 'genehmigungPositivQuestion'); ?></h4>
					&ensp;					<?php echo $this->p->t('anrechnung', 'genehmigungPositivSubquestion'); ?>
									</div>
									<br>
									<!-- Action Button 'Abbrechen'-->
									<div class="pull-right">
										<button id="approveAnrechnungDetail-genehmigung-abbrechen"
												class="btn btn-default btn-w200" type="reset">
											<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
										</button>
										<button id="approveAnrechnungDetail-approve-anrechnung-confirm"
												class="btn btn-primary btn-w200" type="button">
											<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
										</button>
									</div>
								</div>
							</form>
							</div>
						</div>
						<!-- Button Gehenhmigen / Ablehnen / Zurücknehmen -->
						<div class="pull-right">
							<button id="approveAnrechnungDetail-withdraw-anrechnung-approvement"
									class="btn btn-default btn-w200 <?php echo ($anrechnungData->status_kurzbz == 'approved' ||
										$anrechnungData->status_kurzbz == 'rejected') ? '' : 'hidden' ?>"
									type="button">
								<?php echo ucfirst($this->p->t('global', 'zuruecknehmen')); ?>
							</button>
							<button id="approveAnrechnungDetail-reject-anrechnung-ask" class="btn btn-danger btn-w200"
									type="button"
								<?php echo $anrechnungData->status_kurzbz == 'inProgressDP' ? '' : 'disabled' ?>>
								<?php echo ucfirst($this->p->t('global', 'ablehnen')); ?>
							</button>
							<button id="approveAnrechnungDetail-approve-anrechnung-ask" class="btn btn-primary btn-w200"
									type="button"
								<?php echo $anrechnungData->status_kurzbz == 'inProgressDP' ? '' : 'disabled' ?>>
								<?php echo ucfirst($this->p->t('global', 'genehmigen')); ?>
							</button>
						</div>
					</div>
				</div>
				<br><br><br><br>
            </div>

            <div class="col-xs-4">
				<!-- -Statusleiste -->
                <div class="alert text-center">
                    Status: <b><span class="text-uppercase" id="approveAnrechnungDetail-status_kurzbz"
                             data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
                            <?php echo $anrechnungData->status; ?>
                        </span></b>
                </div>
                <br>
				<!-- Infopanels -->
				<?php $this->load->view('lehre/anrechnung/reviewAnrechnungInfo'); ?>
            </div>
        </div>

    </div><!--end container-fluid-->
</div><!--end page-wrapper-->

<?php $this->load->view('templates/FHC-Footer'); ?>
