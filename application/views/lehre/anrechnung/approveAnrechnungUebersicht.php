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
                'begruendung'
            ),
            'anrechnung' => array(
                'nachweisdokumente',
                'empfehlung'
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
					<small>| <?php echo $this->p->t('global', 'uebersicht'); ?></small>
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
                    <button type="submit" class="btn btn-default form-group"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
                </form>
            </div>
        </div>
        <!-- Tabelle -->
        <div class="row">
            <div class="col-xs-12">
	            <?php $this->load->view('lehre/anrechnung/approveAnrechnungUebersichtData.php'); ?>
            </div>
        </div>
        <!-- Action Buttons 'Genehmigen', 'Empfehlung anfordern'-->
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right">
                    <button id="request-recommendation" class="btn btn-default btn-w200 btn-mr50"><?php echo ucfirst($this->p->t('anrechnung', 'empfehlungAnfordern')); ?></button>
                    <button id="reject-anrechnungen" class="btn btn-danger btn-w200"><?php echo ucfirst($this->p->t('global', 'ablehnen')); ?></button>
                    <button id="approve-anrechnungen" class="btn btn-primary btn-w200"><?php echo ucfirst($this->p->t('global', 'genehmigen')); ?></button>
                </div>
                </div>
        </div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

