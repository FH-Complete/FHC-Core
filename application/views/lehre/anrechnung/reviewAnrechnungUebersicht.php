<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenPruefen'),
		'jquery' => true,
		'jqueryui' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'tabulator' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'tablewidget' => true,
		'phrases' => array(
			'global' => array(
				'begruendung',
				'zgv'
			),
			'anrechnung' => array(
				'nachweisdokumente',
				'empfehlung',
				'herkunft'
			),
			'ui' => array(
				'anzeigen',
				'alleAnzeigen',
				'hilfeZuDieserSeite',
				'hochladen',
				'spaltenEinstellen',
				'hilfeZuDieserSeite',
				'alleAuswaehlen',
				'alleAbwaehlen',
				'ausgewaehlteZeilen',
				'hilfe',
				'tabelleneinstellungen',
				'keineDatenVorhanden',
				'spaltenEinstellen',
				'ja',
				'nein',
				'nichtSelektierbarAufgrundVon',
				'systemfehler',
				'bitteMindEinenAntragWaehlen',
				'bitteBegruendungAngeben',
				'anrechnungenWurdenEmpfohlen',
				'anrechnungenWurdenNichtEmpfohlen'
			),
			'person' => array(
				'student',
				'personenkennzeichen',
				'vorname',
				'nachname'
			),
			'lehre' => array(
				'studiensemester',
				'studiengang',
				'lehrveranstaltung',
				'ects',
				'lektor',
			),
			'table' => array(
				'spaltenEinAusblenden',
				'spaltenEinAusblendenMitKlickOeffnen',
				'spaltenEinAusblendenAufEinstellungenKlicken',
				'spaltenEinAusblendenMitKlickAktivieren',
				'spaltenEinAusblendenMitKlickSchliessen',
				'spaltenbreiteVeraendern',
				'spaltenbreiteVeraendernText',
				'spaltenbreiteVeraendernInfotext',
				'zeilenAuswaehlen',
				'zeilenAuswaehlenEinzeln',
				'zeilenAuswaehlenBereich',
				'zeilenAuswaehlenAlle'
			)
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/reviewAnrechnungUebersicht.js'
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
                    <small>| <?php echo ucfirst($this->p->t('global', 'uebersicht')); ?></small>
                </h3>
            </div>
        </div>
        <!-- dropdown studiensemester -->
        <div class="row">
            <div class="col-lg-12">
                <form id="formApproveAnrechnungUebersicht" class="form-inline" action="" method="get">
                    <div class="form-group">
						<?php
						echo $this->widgetlib->widget(
							'Studiensemester_widget',
							array(
								DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
							),
							array(
								'name' => 'studiensemester',
								'id' => 'studiensemester'
							)
						);
						?>
                    </div>
                    <button type="submit"
                            class="btn btn-default form-group"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
                </form>
            </div>
        </div>
        <!-- Tabelle -->
        <div class="row">
            <div class="col-xs-12">
				<?php $this->load->view('lehre/anrechnung/reviewAnrechnungUebersichtData.php'); ?>
            </div>
        </div>
        <!-- Empfehlung / Nicht Empfehlung Panel -->
        <div class="row">
            <div class="panel panel-default panel-body" style="display: none"
                 id="reviewAnrechnungUebersicht-begruendung-panel">
                <div>
                    <h4 class="panel panel-body panel-danger text-danger"><?php echo $this->p->t('anrechnung', 'empfehlungenNegativQuestion'); ?></h4>
                    <b>&ensp;<?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?></b><br><br>
                    <ul>
                        <li>
                            <span><?php echo $this->p->t('anrechnung', 'empfehlungNegativPruefungNichtMoeglich'); ?></span>
                            <span class="btn-copyIntoTextarea" data-toggle="tooltip" data-placement="right"
                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li>
                            <span><?php echo $this->p->t('anrechnung', 'empfehlungNegativKenntnisseNichtGleichwertig'); ?></span>
                            <span class="btn-copyIntoTextarea" data-toggle="tooltip" data-placement="right"
                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li><?php echo $this->p->t('anrechnung', 'andereBegruendung'); ?></li>
                    </ul>
                    <br>
                    <span class="text-danger">
                        <?php echo $this->p->t('anrechnung', 'begruendungWirdFuerAlleUebernommen'); ?>
                    </span><br><br>
                    <textarea class="form-control" name="begruendung" id="reviewAnrechnungUebersicht-begruendung"
                              rows="2" required></textarea>
                </div>
                <br>
                <!-- Action Button Abbrechen & Bestaetigen-->
                <div class="pull-right">
                    <button id="reviewAnrechnungUebersicht-begruendung-abbrechen"
                            class="btn btn-default btn-w200" type="reset">
						<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="reviewAnrechnungUebersicht-dont-recommend-anrechnungen-confirm"
                            class="btn btn-primary btn-w200" type="button">
						<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
            <div class="panel panel-default panel-body" style="display: none"
                 id="reviewAnrechnungUebersicht-empfehlung-panel">
                <div>
                    <h4 class="panel panel-body panel-success text-success"><?php echo $this->p->t('anrechnung', 'empfehlungenPositivQuestion'); ?></h4>
                    &ensp;<?php echo $this->p->t('anrechnung', 'empfehlungenPositiv'); ?><br><br>
                </div>
                <br>
                <!-- Action Button 'Abbrechen'-->
                <div class="pull-right">
                    <button id="reviewAnrechnungUebersicht-empfehlung-abbrechen"
                            class="btn btn-default btn-w200" type="reset">
						<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="reviewAnrechnungUebersicht-recommend-anrechnungen-confirm"
                            class="btn btn-primary btn-w200" type="button">
						<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- Filter buttons -->
            <div class="col-xs-3">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-need-recommendation" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurFehlendeEmpfehlungenAnzeigen'); ?>"><i
                                    class='fa fa-eye'></i>
                        </button>
                        <button id="show-recommended" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurEmpfohleneAnzeigen'); ?>"><i
                                    class='fa fa-thumbs-o-up'></i>
                        </button>
                        <button id="show-not-recommended" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurNichtEmpfohleneAnzeigen'); ?>"><i
                                    class='fa fa-thumbs-o-down'></i>
                        </button>
                        <button id="show-approved" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurGenehmigteAnzeigen'); ?>"><i
                                    class='fa fa-check'></i>
                        </button>
                        <button id="show-rejected" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurAbgelehnteAnzeigen'); ?>"><i
                                    class='fa fa-times'></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons 'Empfehlen', 'Nicht empfehlen'-->
            <div class="col-xs-9">
                <div class="pull-right">
                    <button id="reviewAnrechnungUebersicht-dont-recommend-anrechnungen-ask"
                            class="btn btn-danger btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'nichtEmpfehlen')); ?></button>
                    <button id="reviewAnrechnungUebersicht-recommend-anrechnungen-ask"
                            class="btn btn-primary btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'empfehlen')); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

