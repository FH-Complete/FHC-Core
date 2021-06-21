<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('anrechnung', 'anrechnungenGenehmigen'),
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
				'anrechnungenWurdenAbgelehnt'
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
			'public/js/lehre/anrechnung/approveAnrechnungUebersicht.js'
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
				<?php $this->load->view('lehre/anrechnung/approveAnrechnungUebersichtData.php'); ?>
            </div>
        </div>
        <!-- Genehmigen / Ablehnen Panel -->
        <div class="row">
            <div class="panel panel-default panel-body" style="display: none"
                 id="approveAnrechnungUebersicht-begruendung-panel">
                <div>
                    <h4 class="panel panel-body panel-danger text-danger"><?php echo $this->p->t('anrechnung', 'genehmigungenNegativQuestion'); ?></h4>
                    &nbsp;<b><?php echo $this->p->t('anrechnung', 'bitteBegruendungAngeben'); ?></b><br><br>
                    <ol>
                        <li><?php echo $this->p->t('anrechnung', 'genehmigungNegativPruefungNichtMoeglich'); ?>
                            <span class="btn-copyIntoTextarea" data-toggle="tooltip" data-placement="left"
                               title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li><?php echo $this->p->t('anrechnung', 'genehmigungNegativKenntnisseNichtGleichwertig'); ?>
                            <span class="btn-copyIntoTextarea" data-toggle="tooltip" data-placement="left"
                               title="<?php echo $this->p->t('ui', 'textUebernehmen'); ?>">
                                <i class="fa fa-clipboard" aria-hidden="true"></i>
                            </span>
                        </li>
                        <li><?php echo $this->p->t('anrechnung', 'andereBegruendung'); ?></li>
                    </ol>
                    <br>
                    <span class="text-danger">
                        <?php echo $this->p->t('anrechnung', 'begruendungWirdFuerAlleUebernommen'); ?>
                    </span><br><br>
                    <textarea class="form-control" name="begruendung" id="approveAnrechnungUebersicht-begruendung"
                              rows="2" required></textarea>
                </div>
                <br>
                <!-- Action Button 'Abbrechen'-->
                <div class="pull-right">
                    <button id="approveAnrechnungUebersicht-begruendung-abbrechen"
                            class="btn btn-default btn-w200" type="reset">
						<?php echo ucfirst($this->p->t('ui', 'abbrechen')); ?>
                    </button>
                    <button id="approveAnrechnungUebersicht-reject-anrechnungen-confirm"
                            class="btn btn-primary btn-w200" type="button">
						<?php echo ucfirst($this->p->t('ui', 'bestaetigen')); ?>
                    </button>
                </div>
            </div>
            <div class="panel panel-default panel-body" style="display: none"
                 id="approveAnrechnungUebersicht-genehmigung-panel">
                <div>
                    <h4 class="panel panel-body panel-success text-success"><?php echo $this->p->t('anrechnung', 'genehmigungenPositivQuestion'); ?></h4>
                    &ensp;<?php echo $this->p->t('anrechnung', 'genehmigungenPositiv'); ?><br><br>
                </div>
                <br>
                <!-- Action Button 'Abbrechen'-->
                <div class="pull-right">
                    <button id="approveAnrechnungUebersicht-empfehlung-abbrechen"
                            class="btn btn-default btn-w200" type="reset">
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
            <div class="col-xs-4 col-md-5">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-inProgressDP" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'alleInBearbeitungSTGL'); ?>">
                                    <i class='fa fa-eye'></i>
                        </button>
                        <button id="show-inProgressLektor" class="btn btn-default btn-clearfilter" type="button"
                                data-toggle="tooltip" data-placement="left"
                                title="<?php echo $this->p->t('ui', 'alleInBearbeitungLektor'); ?>"><i
                                    class='fa fa-clock-o'></i>
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
						<a type="button" class="btn btn-default" style="margin-left: 20px;" href='<?php echo site_url('lehre/anrechnung/createAnrechnung') ?>' target='_blank'>
							<i class='fa fa-plus' aria-hidden='true'></i> <?php echo $this->p->t('global', 'antragAnlegen'); ?>
						</a>
				</div>
            </div>
            <!-- Action Buttons 'Genehmigen', Ablehnen, 'Empfehlung anfordern'-->
            <div class="col-xs-8 col-md-7">
                <div class="pull-right">
                    <button id="approveAnrechnungUebersicht-request-recommendation"
                            class="btn btn-default btn-w200 btn-mr50" type="button">
                        <?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAnfordern')); ?></button>
                    <button id="approveAnrechnungUebersicht-reject-anrechnungen-ask"
                            class="btn btn-danger btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('global', 'ablehnen')); ?></button>
                    <button id="approveAnrechnungUebersicht-approve-anrechnungen-ask"
                            class="btn btn-primary btn-w200" type="button">
                        <?php echo ucfirst($this->p->t('global', 'genehmigen')); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
