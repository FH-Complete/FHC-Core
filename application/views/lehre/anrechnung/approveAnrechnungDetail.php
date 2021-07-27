<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenGenehmigen'),
		'jquery' => true,
		'jqueryui' => true,
		'bootstrap' => true,
		'fontawesome' => true,
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
				'nichtSelektierbarAufgrundVon',
				'systemfehler',
				'bitteMindEinenAntragWaehlen',
				'bitteBegruendungAngeben',
				'empfehlungWurdeAngefordert',
				'anrechnungenWurdenGenehmigt',
				'anrechnungenWurdenAbgelehnt'
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

<body>
<div id="page-wrapper">
    <div class="container-fluid">
        <!-- title -->
        <div class="row">
            <div class="col-lg-12 page-header">
                <h3>
					<?php echo $this->p->t('anrechnung', 'anrechnungenGenehmigen'); ?>
                    <small>| <?php echo $this->p->t('global', 'detailsicht'); ?></small>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-8">
                <div class="panel panel-default">
                    <div class="panel-body">

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
                                    <table class="panel-body table table-bordered table-condensed">
                                        <tbody>
                                        <tr>
                                            <td><?php echo ucfirst($this->p->t('person', 'student')); ?></td>
                                            <td><?php echo $antragData->vorname . ' ' . $antragData->nachname; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('person', 'personenkennzeichen'); ?></td>
                                            <td><?php echo $antragData->matrikelnr ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo ucfirst($this->p->t('lehre', 'studiensemester')); ?></td>
                                            <td><?php echo $antragData->studiensemester_kurzbz ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo ucfirst($this->p->t('lehre', 'studiengang')); ?></td>
                                            <td><?php echo $antragData->stg_bezeichnung ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('lehre', 'lehrveranstaltung'); ?></td>
                                            <td><?php echo $antragData->lv_bezeichnung ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('lehre', 'ects'); ?></td>
                                            <td><?php echo $antragData->ects ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('lehre', 'lektorInnen'); ?></td>
                                            <td>
												<?php $len = count($antragData->lektoren) - 1 ?>
												<?php foreach ($antragData->lektoren as $key => $lektor): ?>
													<?php echo $lektor->vorname . ' ' . $lektor->nachname;
													echo $key === $len ? '' : ', ' ?>
												<?php endforeach; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('anrechnung', 'herkunftDerKenntnisse'); ?></td>
                                            <td><?php echo $anrechnungData->anmerkung ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $this->p->t('anrechnung', 'nachweisdokumente'); ?></td>
                                            <td>
                                                <a href="<?php echo current_url() . '/download?dms_id=' . $anrechnungData->dms_id; ?>"
                                                   target="_blank"><?php echo htmlentities($anrechnungData->dokumentname) ?></a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Empfehlungsdaten -->
                        <div class="row">
                            <div class="col-lg-12">
                                <form>
                                    <input type="hidden" name="anrechnung_id"
                                           value="<?php echo $anrechnungData->anrechnung_id ?>">
                                    <div class="panel panel-default" id="test">

                                        <div class="panel-heading">
                                            <span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'empfehlung'); ?></b></span>&emsp;
<!--                                            <span class="approveAnrechnungDetail-empfehlungInfoTooltip"-->
<!--                                                  data-toggle="tooltip" data-placement="right"-->
<!--                                                  title="--><?php //echo $this->p->t('anrechnung', 'empfehlungInfoTooltipText'); ?><!--">-->
<!--                                                            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>-->
<!--                                                        </span>-->
                                            <div class="pull-right">
												<?php echo $this->p->t('anrechnung', 'empfehlungVon'); ?>:
                                                <span id="approveAnrechnungDetail-empfehlungVon"><?php echo $empfehlungData->empfehlung_von ?></span>
                                                &emsp;|&emsp;
												<?php echo $this->p->t('anrechnung', 'empfehlungdatum'); ?>:
                                                <span id="approveAnrechnungDetail-empfehlungAm"><?php echo $empfehlungData->empfehlung_am ?></span>
                                            </div>
                                        </div>

                                        <div class="panel-body" id="approveAnrechnungDetail-empfehlungDetail">

                                            <div class="panel panel-default panel-body
                                                <?php echo is_null($empfehlungData->empfehlung) && $anrechnungData->status_kurzbz != 'inProgressLektor' ? '' : 'hidden' ?>"
                                                 id="approveAnrechnungDetail-empfehlungDetail-empfehlungIsNull">
												<?php echo $this->p->t('anrechnung', 'keineEmpfehlungAngefordert'); ?>
                                            </div>

                                            <div class="panel panel-default panel-body <?php echo
											is_null($empfehlungData->empfehlung) && $anrechnungData->status_kurzbz == 'inProgressLektor'
												? '' : 'hidden' ?>"
                                                 id="approveAnrechnungDetail-empfehlungDetail-empfehlungIsAngefordert">
												<?php echo $this->p->t('anrechnung', 'empfehlungAngefordertNochKeineEmpfehlung'); ?>
                                                <span id="approveAnrechnungDetail-empfehlungDetail-empfehlungAngefordertAm">
                                                        <?php echo $empfehlungData->empfehlung_angefordert_am ?>
                                                    </span>.
                                            </div>

                                            <div class="alert alert-success <?php echo $empfehlungData->empfehlung === true ? '' : 'hidden' ?>"
                                                 id="approveAnrechnungDetail-empfehlungDetail-empfehlungIsTrue">
                                                <b><?php echo $this->p->t('anrechnung', 'empfehlungPositivConfirmed'); ?></b>
                                            </div>

                                            <div class="<?php echo $empfehlungData->empfehlung === false ? '' : 'hidden' ?>"
                                                 id="approveAnrechnungDetail-empfehlungDetail-empfehlungIsFalse">
                                                <div class="alert alert-danger">
                                                    <b><?php echo $this->p->t('anrechnung', 'empfehlungNegativConfirmed'); ?></b>
                                                </div>
                                                <div class="well"><b><?php echo $this->p->t('global', 'begruendung'); ?>
                                                        : </b>
                                                    <span id="approveAnrechnungDetail-empfehlungDetail-begruendung"><?php echo htmlentities($empfehlungData->notiz) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="pull-right">
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
<!--                                        <span class="approveAnrechnungDetail-genehmigungInfoTooltip"-->
<!--                                              data-toggle="tooltip" data-placement="right"-->
<!--                                              title="--><?php //echo $this->p->t('anrechnung', 'genehmigungInfoTooltipText'); ?><!--">-->
<!--                                                            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>-->
<!--                                                        </span>-->
                                        <div class="pull-right">
											<?php echo $this->p->t('anrechnung', 'abgeschlossenVon'); ?>:
                                            <span id="approveAnrechnungDetail-abgeschlossenVon"><?php echo $genehmigungData->abgeschlossen_von ?></span>
                                            &emsp;|&emsp;
											<?php echo $this->p->t('anrechnung', 'abschlussdatum'); ?>:
                                            <span id="approveAnrechnungDetail-abgeschlossenAm"><?php echo $genehmigungData->abgeschlossen_am ?></span>

                                        </div>
                                    </div>

                                    <div class="panel-body" id="approveAnrechnungDetail-genehmigungDetail">

                                        <div class="panel panel-default panel-body <?php echo is_null($genehmigungData->genehmigung) ? '' : 'hidden' ?>"
                                             id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsNull">
											<?php echo $this->p->t('anrechnung', 'nochKeineGenehmigung'); ?>
                                        </div>

                                        <div class="alert alert-success <?php echo $genehmigungData->genehmigung === true ? '' : 'hidden' ?>"
                                             id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsPositiv">
                                            <b><?php echo $this->p->t('anrechnung', 'genehmigungPositiv'); ?></b>
                                        </div>

                                        <div class="<?php echo $genehmigungData->genehmigung === false ? '' : 'hidden' ?>"
                                             id="approveAnrechnungDetail-genehmigungDetail-genehmigungIsNegativ">
                                            <div class="alert alert-danger">
                                                <b><?php echo $this->p->t('anrechnung', 'genehmigungNegativ'); ?></b>
                                            </div>
                                            <div class="well"><b><?php echo $this->p->t('global', 'begruendung'); ?>
                                                    : </b>
                                                <span id="approveAnrechnungDetail-genehmigungDetail-begruendung"><?php echo htmlentities($genehmigungData->notiz) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <form>
                                            <input type="hidden" name="anrechnung_id"
                                                   value="<?php echo $anrechnungData->anrechnung_id ?>">
                                            <div class="panel panel-default panel-body" style="display: none"
                                                 id="approveAnrechnungDetail-begruendung-panel">
                                                <div>
                                                    <h4 class="panel panel-body panel-danger text-danger"><?php echo $this->p->t('anrechnung', 'genehmigungNegativQuestion'); ?></h4>
                                                    <b>&nbsp;<?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?></b><br><br>
                                                    <ul>
                                                        <li>
                                                            <span><?php echo $this->p->t('anrechnung', 'genehmigungNegativPruefungNichtMoeglich'); ?></span>
                                                            <span class="btn-copyIntoTextarea" data-toggle="tooltip"
                                                                  data-placement="right"
                                                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                                                            </span>
                                                        </li>
                                                        <li>
                                                            <span><?php echo $this->p->t('anrechnung', 'genehmigungNegativKenntnisseNichtGleichwertig'); ?></span>
                                                            <span class="btn-copyIntoTextarea" data-toggle="tooltip"
                                                                  data-placement="right"
                                                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                                                            </span>
                                                        </li>
                                                        <li><?php echo $this->p->t('anrechnung', 'andereBegruendung'); ?></li>
                                                    </ul>
                                                    <br>
                                                    <textarea class="form-control" name="begruendung"
                                                              id="approveAnrechnungDetail-begruendung"
                                                              rows="2" required></textarea>
                                                </div>
                                                <br>
                                                <!-- Action Button 'Abbrechen'-->
                                                <div class="pull-right">
                                                    <button id="approveAnrechnungDetail-begruendung-abbrechen"
                                                            class="btn btn-default btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                                                    </button>
                                                    <button id="approveAnrechnungDetail-reject-anrechnung-confirm"
                                                            class="btn btn-primary btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="panel panel-default panel-body" style="display: none"
                                                 id="approveAnrechnungDetail-genehmigung-panel">
                                                <div>
                                                    <h4 class="panel panel-body panel-success text-success"><?php echo $this->p->t('anrechnung', 'genehmigungPositivQuestion'); ?></h4>
                                                    &ensp;<?php echo $this->p->t('anrechnung', 'genehmigungPositivSubquestion'); ?>
                                                    <br><br>

                                                </div>
                                                <br>
                                                <!-- Action Button 'Abbrechen'-->
                                                <div class="pull-right">
                                                    <button id="approveAnrechnungDetail-genehmigung-abbrechen"
                                                            class="btn btn-default btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                                                    </button>
                                                    <button id="approveAnrechnungDetail-approve-anrechnung-confirm"
                                                            class="btn btn-primary btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="pull-right">
                    <button id="approveAnrechnungDetail-reject-anrechnung-ask" class="btn btn-danger btn-w200"
						<?php echo $anrechnungData->status_kurzbz == 'inProgressDP' ? '' : 'disabled' ?>>
						<?php echo ucfirst($this->p->t('global', 'ablehnen')); ?>
                    </button>
                    <button id="approveAnrechnungDetail-approve-anrechnung-ask" class="btn btn-primary btn-w200"
						<?php echo $anrechnungData->status_kurzbz == 'inProgressDP' ? '' : 'disabled' ?>>
						<?php echo ucfirst($this->p->t('global', 'genehmigen')); ?>
                    </button>
                </div>

            </div>

            <div class="col-xs-4">
                <div class="alert text-center">
                    Status:
                    <b><span class="text-uppercase" id="approveAnrechnungDetail-status_kurzbz"
                             data-status_kurzbz="<?php echo $anrechnungData->status_kurzbz ?>">
                            <?php echo $anrechnungData->status; ?>
                        </span></b>
                </div>
                <br>
				<?php $this->load->view('lehre/anrechnung/reviewAnrechnungInfo'); ?>
            </div>
        </div>

    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
