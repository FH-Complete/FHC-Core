<?php

$includesArray = array(
    'title' => $this->p->t('anrechnung', 'anrechnungenPruefen'),
    'jquery3' => true,
    'jqueryui1' => true,
    'bootstrap5' => true,
    'fontawesome4' => true,
    'tabulator5' => true,
    'tabulator5JQuery' => true,
    'cis' => true,
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
    'customCSSs' => array(
        'public/css/lehre/anrechnung.css'
    ),
    'customJSs' => array(
        'public/js/bootstrapper.js',
        'public/js/lehre/anrechnung/reviewAnrechnungUebersicht.js'
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
                <h3 class="fw-normal">
                    <?php echo $this->p->t('anrechnung', 'anrechnungenPruefen'); ?>
                    <small class="text-secondary fs-6">|
                        <?php echo ucfirst($this->p->t('global', 'uebersicht')); ?></small>
                </h3>
            </div>
        </div>
        <!-- dropdown studiensemester -->
        <div class="row">
            <div class="col-lg-12">
                <form id="formReviewAnrechnungUebersicht" class="row align-items-center" action="" method="get">
                    <div class="col-auto">
                        <?php
                        echo $this->widgetlib->widget(
                            'Studiensemester_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
                            ),
                            array(
                                'name' => 'studiensemester',
                                'id' => 'studiensemester',
                                'class' => 'form-select w-auto ',
                            )
                        );
                        ?>
                    </div>
                    <button type="submit"
                        class="btn btn-outline-secondary col-auto"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
                </form>
            </div>
        </div>
        <!-- Tabelle -->
        <div class="row">
            <div class="col-12">
                <?php $this->load->view('lehre/anrechnung/reviewAnrechnungUebersichtData.php'); ?>
            </div>
        </div>
        <!-- Empfehlung / Nicht Empfehlung Panel -->
        <div class="row">
            <div class="border border-1 mb-4" style="display: none" id="reviewAnrechnungUebersicht-begruendung-panel">
                <div class="mb-4 col-12">
                    <h4 class="card card-body border-danger text-danger my-3">
                        <?php echo $this->p->t('anrechnung', 'empfehlungenNegativQuestion'); ?>
                    </h4>
                    <div class="mb-4">
                        <b><span>&ensp;<?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?></span>
                            <span class="text-danger">
                                <?php echo $this->p->t('anrechnung', 'begruendungWirdFuerAlleUebernommen'); ?>
                            </span></b>
                    </div>

                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <span><?php echo $this->p->t('anrechnung', 'empfehlungNegativPruefungNichtMoeglich'); ?></span>
                            <span class="btn-copyIntoTextarea float-end" data-bs-toggle="tooltip"
                                data-bs-placement="right" title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li class="list-group-item list-group-item-secondary">
                            <?php echo $this->p->t('anrechnung', 'empfehlungNegativKenntnisseNichtGleichwertigWeilHinweis'); ?>
                        </li>
                    </ul>
                    <textarea class="form-control" name="begruendung" id="reviewAnrechnungUebersicht-begruendung"
                        rows="2"
                        placeholder="<?php echo $this->p->t('anrechnung', 'textUebernehmenOderEigenenBegruendungstext'); ?>"
                        required></textarea>
                </div>

                <!-- Action Button Abbrechen & Bestaetigen-->
                <div class=" mb-4 d-flex justify-content-end">
                    <button id="reviewAnrechnungUebersicht-begruendung-abbrechen"
                        class="me-1 btn btn-outline-secondary btn-w200" type="reset">
                        <?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="reviewAnrechnungUebersicht-dont-recommend-anrechnungen-confirm"
                        class="btn btn-primary btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
            <div class="border border-1 mb-4" style="display: none" id="reviewAnrechnungUebersicht-empfehlung-panel">
                <div class="mb-4">
                    <h4 class="card card-body border-success text-success my-3">
                        <?php echo $this->p->t('anrechnung', 'empfehlungenPositivQuestion'); ?>
                    </h4>
                    <div class="ps-2 mb-4"><?php echo $this->p->t('anrechnung', 'empfehlungenPositiv'); ?></div>
                </div>

                <!-- Action Button 'Abbrechen'-->
                <div class="mb-4 d-flex justify-content-end">
                    <button id="reviewAnrechnungUebersicht-empfehlung-abbrechen"
                        class="me-1 btn btn-outline-secondary btn-w200" type="reset">
                        <?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="reviewAnrechnungUebersicht-recommend-anrechnungen-confirm"
                        class="btn btn-primary btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
        </div>
        <!-- Filter buttons / Submit buttons-->
        <div class="row">
            <!-- Filter buttons -->
            <div class="col-3">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-need-recommendation" class="btn btn-outline-secondary btn-clearfilter"
                            type="button" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true"
                            title="<?php echo $this->p->t('ui', 'nurFehlendeEmpfehlungenAnzeigen'); ?>"><i
                                class='fa fa-eye'></i>
                        </button>
                        <button id="show-recommended" class="btn btn-outline-secondary btn-clearfilter" type="button"
                            data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true"
                            title="<?php echo $this->p->t('ui', 'nurEmpfohleneAnzeigen'); ?>"><i
                                class='fa fa-thumbs-o-up'></i>
                        </button>
                        <button id="show-not-recommended" class="btn btn-outline-secondary btn-clearfilter"
                            type="button" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true"
                            title="<?php echo $this->p->t('ui', 'nurNichtEmpfohleneAnzeigen'); ?>"><i
                                class='fa fa-thumbs-o-down'></i>
                        </button>
                        <button id="show-approved" class="btn btn-outline-secondary btn-clearfilter" type="button"
                            data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true"
                            title="<?php echo $this->p->t('ui', 'nurGenehmigteAnzeigen'); ?>"><i
                                class='fa fa-check'></i>
                        </button>
                        <button id="show-rejected" class="btn btn-outline-secondary btn-clearfilter" type="button"
                            data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true"
                            title="<?php echo $this->p->t('ui', 'nurAbgelehnteAnzeigen'); ?>"><i
                                class='fa fa-times'></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons 'Empfehlen', 'Nicht empfehlen'-->
            <div class="col-9">
                <div class="d-flex justify-content-end">
                    <button id="reviewAnrechnungUebersicht-dont-recommend-anrechnungen-ask"
                        class="me-1 btn btn-danger btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'nichtEmpfehlen')); ?></button>
                    <button id="reviewAnrechnungUebersicht-recommend-anrechnungen-ask" class="btn btn-primary btn-w200"
                        type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'empfehlen')); ?></button>
                </div>
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
