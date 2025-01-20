<?php

$includesArray = array(
	'title' => $this->p->t('anrechnung', 'anrechnungenGenehmigen'),
	'jquery3' => true,
	'jqueryui1' => true,
	'bootstrap5' => true,
	'fontawesome4' => true,
	'tabulator5' => true,
    'tabulator5JQuery' => true,
	'ajaxlib' => true,
	'dialoglib' => true,
    'cis'=>true,
	'tablewidget' => true,
	'phrases' => array(
		'global' => array(
			'begruendung',
            'zgv'
		),
		'anrechnung' => array(
			'nachweisdokumente',
			'empfehlung',
			'empfehlungsanfrageAn',
			'empfehlungsanfrageAm',
			'confirmTextAntragHatBereitsEmpfehlung',
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
			'nichtSelektierbarAufgrundVon',
			'systemfehler',
			'bitteMindEinenAntragWaehlen',
			'bitteBegruendungAngeben',
			'empfehlungWurdeAngefordert',
			'empfehlungWurdeAngefordertAusnahmeWoKeineLektoren',
			'anrechnungenWurdenGenehmigt',
			'anrechnungenWurdenAbgelehnt',
            'nurLeseberechtigung'
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
		//'public/js/bootstrapper5.js',
		'public/js/lehre/anrechnung/approveAnrechnungUebersicht.js'
	),
	'customCSSs' => array(
		'public/css/lehre/anrechnung.css'
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
        <!-- header -->
        <div class="row">
            <div class="col-lg-12 my-4 border-bottom">
                <h3 class="fw-normal ">
					<?php echo $this->p->t('anrechnung', 'anrechnungenGenehmigen'); ?>
                    <small class="text-secondary fs-6">| <?php echo ucfirst($this->p->t('global', 'uebersicht')); ?></small>
                </h3>
            </div>
        </div>
    
        <!-- end header -->

        <!-- dropdown studiensemester -->
        <div class="row">
            <div class="col-lg-12">
                <form id="formApproveAnrechnungUebersicht" class="row align-items-center" action="" method="get" data-readonly="<?php echo json_encode($hasReadOnlyAccess)?>" data-createaccess="<?php echo json_encode($hasCreateAnrechnungAccess)?>">
                    <div class="col-auto">
						<?php
						echo $this->widgetlib->widget(
							'Studiensemester_widget',
							array(
								DropdownWidget::SELECTED_ELEMENT => $studiensemester_selected
							),
							array(
								'name' => 'studiensemester',
								'id'=>'studiensemester',
                                'class'=>'form-select w-auto ',
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
        <div class="row mb-4">
            <div class="col-12">
                <?php $this->load->view('lehre/anrechnung/approveAnrechnungUebersichtData.php'); ?>
            </div>
        </div>
        <!-- Genehmigen / Ablehnen Panel -->
        <div class="row">
            <div  class="border border-1 mb-4" style="display: none"
                 id="approveAnrechnungUebersicht-begruendung-panel">
                <div class="mb-4 col-12">
                
                    <h4 class="card card-body border-danger text-danger my-3"><?php echo $this->p->t('anrechnung', 'genehmigungenNegativQuestion'); ?></h4>
                    <div class="mb-4">
                        <b><?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?>
                        <span class="text-danger">
                            <?php echo $this->p->t('anrechnung', 'begruendungWirdFuerAlleUebernommen'); ?>
                        </span></b>
                    </div>
                    <ol class="list-group mb-4">
                        <li class="list-group-item"><?php echo $this->p->t('anrechnung', 'genehmigungNegativPruefungNichtMoeglich'); ?>
                            <span role="button" class="btn-copyIntoTextarea float-end" data-bs-toggle="tooltip" data-bs-placement="left"
                               title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li class="list-group-item"><?php echo $this->p->t('anrechnung', 'genehmigungNegativEctsHoechstgrenzeUeberschritten'); ?>
                            <span role="button" class="btn-copyIntoTextarea float-end" data-bs-toggle="tooltip" data-bs-placement="left"
                                  title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard fa-lg" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li class="list-group-item  list-group-item-secondary"><?php echo $this->p->t('anrechnung', 'genehmigungNegativKenntnisseNichtGleichwertigWeilHinweis'); ?></li>
                    </ol>
                    <textarea class="form-control" name="begruendung" id="approveAnrechnungUebersicht-begruendung"
                              rows="2"
                              placeholder="<?php echo $this->p->t('anrechnung', 'textUebernehmenOderEigenenBegruendungstext'); ?>" required></textarea>
                </div>
               
                <!-- Action Button 'Abbrechen'-->
                <div class="mb-4 d-flex justify-content-end" >
                    <button id="approveAnrechnungUebersicht-begruendung-abbrechen"
                            class="me-1 btn btn-outline-secondary btn-w200 " type="reset">
						<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="approveAnrechnungUebersicht-reject-anrechnungen-confirm"
                            class="btn btn-primary btn-w200 " type="button">
						<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
            
            <div class="border border-1 mb-4" style="display: none"
                 id="approveAnrechnungUebersicht-genehmigung-panel">
                <div class="mb-4">
                    <h4 class="card card-body border-success text-success my-3"><?php echo $this->p->t('anrechnung', 'genehmigungenPositivQuestion'); ?></h4>
                    <div class="ps-2 mb-4"><?php echo $this->p->t('anrechnung', 'genehmigungenPositiv'); ?></div>
                </div>
                
                <!-- Action Button 'Abbrechen'-->
                <div class="mb-4 d-flex justify-content-end">
                    <button id="approveAnrechnungUebersicht-empfehlung-abbrechen"
                            class="me-1 btn btn-outline-secondary btn-w200" type="reset">
						<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="approveAnrechnungUebersicht-approve-anrechnungen-confirm"
                            class="btn btn-primary btn-w200" type="button">
						<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
        </div>
        <!-- Filter buttons / Submit buttons-->
        <div class="row">
            <!-- Filter buttons -->
            <div class="col-12 col-md-5">
                <div class="btn-toolbar " role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-inProgressDP" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'alleInBearbeitungSTGL'); ?>">
                                    <i class='fa fa-eye'></i>
                        </button>
                        <button id="show-inProgressLektor" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'alleInBearbeitungLektor'); ?>"><i
                                    class='fa fa-clock-o'></i>
                        </button>
                        <button id="show-recommended" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurEmpfohleneAnzeigen'); ?>"><i
                                    class='fa fa-thumbs-o-up'></i>
                        </button>
                        <button id="show-not-recommended" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurNichtEmpfohleneAnzeigen'); ?>"><i
                                    class='fa fa-thumbs-o-down'></i>
                        </button>
                        <button id="show-approved" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurGenehmigteAnzeigen'); ?>"><i
                                    class='fa fa-check'></i>
                        </button>
                        <button id="show-rejected" class="btn btn-outline-secondary btn-clearfilter" type="button"
                                data-bs-toggle="tooltip" data-bs-placement="left"
                                title="<?php echo $this->p->t('ui', 'nurAbgelehnteAnzeigen'); ?>"><i
                                    class='fa fa-times'></i>
                        </button>
                    </div>
						<a type="button" id="approveAnrechnungUebersicht-create-anrechnung" class="btn btn-outline-secondary ms-4"  href='<?php echo site_url('lehre/anrechnung/createAnrechnung') ?>' target='_blank'>
							<i class='fa fa-plus' aria-hidden='true'></i> <?php echo $this->p->t('global', 'antragAnlegen'); ?>
						</a>
				</div>
            </div>
            <!-- Action Buttons 'Genehmigen', Ablehnen, 'Empfehlung anfordern'-->
            <div class="col-12 col-md-7">
                <div class="d-flex ">
                    <button id="approveAnrechnungUebersicht-request-recommendation"
                            class="btn btn-outline-secondary btn-w200 me-5" type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAnfordern')); ?></button>
                    
                    <button id="approveAnrechnungUebersicht-reject-anrechnungen-ask"
                            class="btn btn-danger btn-w200 me-1" type="button">
                        <?php echo ucfirst($this->p->t('global', 'ablehnen')); ?></button>
                    <button id="approveAnrechnungUebersicht-approve-anrechnungen-ask"
                            class="btn btn-primary btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('global', 'genehmigen')); ?></button>       
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
