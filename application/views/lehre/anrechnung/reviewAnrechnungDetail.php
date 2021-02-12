<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenPruefen'),
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
				'systemfehler',
				'bitteMindEinenAntragWaehlen',
				'bitteBegruendungAngeben',
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
			)
		),
		'customCSSs' => array(
			'public/css/Tabulator.css'
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/reviewAnrechnungDetail.js'

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
					<?php echo $this->p->t('anrechnung', 'anrechnungenPruefen'); ?>
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
                                        <span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'antrag'); ?></b></span>
                                        </span>&emsp;
                                        <span class="reviewAnrechnungDetail-anrechnungInfoTooltip" data-toggle="tooltip"
                                              data-placement="right"
                                              title="<?php echo $this->p->t('anrechnung', 'anrechnungInfoTooltipText'); ?>">
                                                            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>
                                                        </span>
                                        <span class="pull-right"><?php echo $this->p->t('anrechnung', 'antragdatum'); ?>: <span
                                                    id="reviewAnrechnung-status"><?php echo !empty($anrechnungData->anrechnung_id) ? $anrechnungData->insertamum : '-' ?></span></span>
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
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <span class="text-uppercase"><b><?php echo $this->p->t('anrechnung', 'empfehlung'); ?></b></span>
                                        <div class="pull-right">
											<?php echo $this->p->t('anrechnung', 'empfehlungVon'); ?>:
                                            <span id="reviewAnrechnungDetail-empfehlungVon"><?php echo $empfehlungData->empfehlung_von ?></span>
                                            &emsp;|&emsp;
											<?php echo $this->p->t('anrechnung', 'empfehlungdatum'); ?>:
                                            <span id="reviewAnrechnungDetail-empfehlungAm"><?php echo $empfehlungData->empfehlung_am ?></span>

                                        </div>
                                    </div>
                                    <div class="panel-body" id="reviewAnrechnungDetail-empfehlungDetail">

                                        <div class="panel panel-default panel-body <?php echo is_null($empfehlungData->empfehlung) ? '' : 'hidden' ?>"
                                             id="reviewAnrechnungDetail-empfehlungDetail-empfehlungIsNull">
											<?php echo $this->p->t('anrechnung', 'nochKeineEmpfehlung'); ?>
                                        </div>

                                        <div class="alert alert-success <?php echo $empfehlungData->empfehlung === true ? '' : 'hidden' ?>"
                                             id="reviewAnrechnungDetail-empfehlungDetail-empfehlungIsTrue">
                                            <b><?php echo $this->p->t('anrechnung', 'empfehlungPositivConfirmed'); ?></b>
                                        </div>

                                        <div class="<?php echo $empfehlungData->empfehlung === false ? '' : 'hidden' ?>"
                                             id="reviewAnrechnungDetail-empfehlungDetail-empfehlungIsFalse">
                                            <div class="alert alert-danger">
                                                <b><?php echo $this->p->t('anrechnung', 'empfehlungNegativConfirmed'); ?></b>
                                            </div>
                                            <div class="well"><b><?php echo $this->p->t('global', 'begruendung'); ?>
                                                    : </b>
                                                <span id="reviewAnrechnungDetail-empfehlungDetail-begruendung"><?php echo htmlentities($empfehlungData->notiz) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <form>
                                            <input type="hidden" name="anrechnung_id"
                                                   value="<?php echo $anrechnungData->anrechnung_id ?>">
                                            <div class="panel panel-default panel-body" style="display: none"
                                                 id="reviewAnrechnungDetail-begruendung-panel">
                                                <div>
                                                    <h4 class="panel panel-body panel-danger text-danger"><?php echo $this->p->t('anrechnung', 'empfehlungNegativQuestion'); ?></h4>
                                                    &ensp;
                                                    <b>&ensp;<?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?></b><br><br>
                                                    <ul>
                                                        <li>
                                                            <span><?php echo $this->p->t('anrechnung', 'empfehlungNegativPruefungNichtMoeglich'); ?></span>&emsp;
                                                            <span class="btn-copyIntoTextarea" data-toggle="tooltip"
                                                                  data-placement="right"
                                                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                                                            </span>
                                                        </li>
                                                        <li>
                                                            <span><?php echo $this->p->t('anrechnung', 'empfehlungNegativKenntnisseNichtGleichwertig'); ?></span>&emsp;
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
                                                              id="reviewAnrechnungDetail-begruendung"
                                                              rows="2" required></textarea>
                                                </div>
                                                <br>
                                                <!-- Action Button Abbrechen & Bestaetigen-->
                                                <div class="pull-right">
                                                    <button id="reviewAnrechnungDetail-begruendung-abbrechen"
                                                            class="btn btn-default btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                                                    </button>
                                                    <button id="reviewAnrechnungDetail-dont-recommend-anrechnung-confirm"
                                                            class="btn btn-primary btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="panel panel-default panel-body" style="display: none"
                                                 id="reviewAnrechnungDetail-empfehlung-panel">
                                                <div>
                                                    <h4 class="panel panel-body panel-success text-success"><?php echo $this->p->t('anrechnung', 'empfehlungPositivQuestion'); ?></h4>
                                                    &ensp;<?php echo $this->p->t('anrechnung', 'empfehlungPositivSubquestion'); ?>
                                                    <br><br>
                                                </div>
                                                <br>
                                                <!-- Action Button Abbrechen & Bestaetigen-->
                                                <div class="pull-right">
                                                    <button id="reviewAnrechnungDetail-empfehlung-abbrechen"
                                                            class="btn btn-default btn-w200">
														<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                                                    </button>
                                                    <button id="reviewAnrechnungDetail-recommend-anrechnung-confirm"
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
                    <button id="reviewAnrechnungDetail-dont-recommend-anrechnung-ask" class="btn btn-danger btn-w200"
						<?php echo is_null($empfehlungData->empfehlung) ? '' : 'disabled' ?>>
						<?php echo ucfirst($this->p->t('anrechnung', 'nichtEmpfehlen')); ?>
                    </button>
                    <button id="reviewAnrechnungDetail-recommend-anrechnung-ask" class="btn btn-primary btn-w200"
						<?php echo is_null($empfehlungData->empfehlung) ? '' : 'disabled' ?>>
						<?php echo ucfirst($this->p->t('anrechnung', 'empfehlen')); ?>
                    </button>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="alert text-center">
                    Status:
                    <b><span class="text-uppercase" id="reviewAnrechnungDetail-status_kurzbz"
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
