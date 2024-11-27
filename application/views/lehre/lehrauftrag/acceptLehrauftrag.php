<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Lehrauftrag annehmen',
		'jquery3' => true,
		'jqueryui1' => true,
		'jquerycheckboxes1' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'sbadmintemplate' => false,
		'tabulator5' => true,
        'tabulator5JQuery' => true,
        'cis'=>true,
		'momentjs2' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'tablewidget' => true,
		'phrases' => array(
			'global' => array(
				'lehrauftraegeAnnehmen',
                'dokumentePDF',
                'PDFLehrauftraegeFH',
                'PDFLehrauftraegeLehrgaenge'
            ),
			'ui' => array(
				'anzeigen',
				'alleAnzeigen',
				'nurBestellteAnzeigen',
				'nurErteilteAnzeigen',
				'nurAngenommeneAnzeigen',
				'nurStornierteAnzeigen',
				'hilfeZuDieserSeite',
				'alleAuswaehlen',
				'alleAbwaehlen',
				'ausgewaehlteZeilen',
				'hilfe',
				'tabelleneinstellungen',
				'keineDatenVorhanden',
				'spaltenEinstellen',
				'bestelltVon',
				'erteiltVon',
				'angenommenVon',
				'storniertVon',
				'lehrauftragInBearbeitung',
				'wartetAufErteilung',
				'wartetAufErneuteErteilung',
				'letzterStatusBestellt',
				'letzterStatusErteilt',
				'letzterStatusAngenommen',
				'vertragWurdeStorniert',
				),
			'password' => array('password'),
			'dms' => array('informationsblattExterneLehrende'),
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
			),
             'lehre' => array(
		        'lehrauftraegeAnnehmen',
		        'lehrauftraegeAnnehmenText',
		        'lehrauftraegeAnnehmenKlickStatusicon',
		        'lehrauftraegeAnnehmenLehrauftraegeWaehlen',
		        'lehrauftraegeAnnehmenMitKlickAnnehmen',
		        'lehrauftraegeNichtAuswaehlbar',
		        'lehrauftraegeNichtAuswaehlbarTextBeiAnnahme',
		        'filterAlleBeiAnnahme',
		        'filterErteiltBeiAnnahme',
		        'filterAngenommen'
            )
		),
		'customJSs' => array(
				'public/js/bootstrapper.js',
				'public/js/lehre/lehrauftrag/acceptLehrauftrag.js')
	)
);
?>


<div id="page-wrapper">
	<div class="container-fluid">

		<!-- title & helper link -->
		<div class="row">
			<div class="col-lg-12 page-header">
				<a class="float-end" data-bs-toggle="collapse" href="#collapseHelp" aria-expanded="false" aria-controls="collapseExample">
					<?php echo $this->p->t('ui', 'hilfeZuDieserSeite'); ?>
				</a>
				<h3>
					<?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?>
				</h3>
			</div>
		</div>

		<!-- helper collapse module -->
		<div class="row">
			<div class="col-lg-12 collapse my-4" id="collapseHelp">
				<div class="card p-3">
					
					<?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragHelp') ?>
				</div> 
			</div>
		</div>

		<!-- dropdown widgets -->
		<div class="row">
			<div class="col-lg-12">
				<form id="formLehrauftrag" class="row align-items-center" action="" method="get">
					<input type="hidden" id="uid" name="uid" value="<?php echo getAuthUID(); ?>">
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
					<button type="submit" name="submit" value="anzeigen" class="btn btn-outline-secondary col-auto"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
				</form>
			</div>
		</div>

		<!-- tabulator data table 'Lehrauftraege annehmen'-->
		<div class="row">
			<div class="col-lg-12">
				<?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragData.php'); ?>
			</div>
		</div>
		<br>

		<!-- link for external lectors 'Informationsblatt fuer externe Lehrende'. Show only for external lecturers -->

		<div class="row">
			<div class="col-12">
				<span class="float-start"><?php echo $this->p->t('ui' , 'hinweistextLehrauftrag'); ?></span>
				<?php if ($is_external_lector): ?>
					<span class="float-start"><?php echo $this->p->t('dms' , 'informationsblattExterneLehrende'); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<br>


		<!-- filter buttons & PDF downloads & password field & akzeptieren-button -->
		<div class="row">
			<div class="col-5 col-md-4">
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group" role="group">
						<button id="show-all" class="btn btn-outline-secondary btn-lehrauftrag active focus" type="button"
								data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo $this->p->t('ui', 'alleAnzeigen'); ?>"><i class='fa fa-users'></i>
						</button>
						<button id="show-approved" class="btn btn-outline-secondary btn-lehrauftrag" type="button"
								data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo $this->p->t('ui', 'nurErteilteAnzeigen'); ?>"><i class='fa fa-user-check'></i>
						</button>
						<button id="show-accepted" class="btn btn-outline-secondary btn-lehrauftrag" type="button"
								data-bs-toggle="tooltip" data-bs-placement="left" title="<?php echo $this->p->t('ui', 'nurAngenommeneAnzeigen'); ?>"><i class='fa-regular fa-handshake'></i>
						</button>
					</div>

					<button id="show-cancelled" class="btn btn-outline-secondary btn-lehrauftrag" type="button" style="margin-left: 20px;"
							data-bs-toggle="collapse" data-placement="left" title="<?php echo $this->p->t('ui', 'nurStornierteAnzeigen'); ?>"
							data-bs-target ="#collapseCancelledLehrauftraege" aria-expanded="false" aria-controls="collapseExample"><i class="fa fa-user-xmark"></i>
					</button>
				</div>
			</div>


			<div class="col-3 offset-md-2 col-md-2">
					<div class="btn-group dropup float-end">
					<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php echo $this->p->t('global', 'dokumentePDF'); ?>&nbsp;&nbsp;<i class="fa fa-arrow-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="caret"></span>
					</button>
					<ul id="ul-download-pdf" class="dropdown-menu">
						<li value="etw"><a class="dropdown-item" href="#"><?php echo $this->p->t('global', 'PDFLehrauftraegeFH'); ?></a></li>
						<li value="lehrgang"><a class="dropdown-item" href="#"><?php echo $this->p->t('global', 'PDFLehrauftraegeLehrgaenge'); ?></a></li>
					</ul>
				</div>
			</div>

			<div class="col-4 offset-md-0 col-md-4">
				<div class="input-group">
					<input id="username" autocomplete="username" style="position: absolute; opacity: 0;"><!-- this is to prevent Chrome autofilling a random input field with the username-->
					<input id="password" type="password" autocomplete="new-password" class="form-control" placeholder="CIS-<?php echo ucfirst($this->p->t('password', 'password')); ?>">
						<span class="input-group-btn">
							<button id="accept-lehrauftraege" class="btn btn-primary float-end"><?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?></button>
						</span>
				</div>
			</div>
		</div>
		<br>
		<br>

		<!-- collapse module with data table 'Stornierte Lehrauftraege' (collapsed by default until opened on buttonclick)-->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseCancelledLehrauftraege">
				<h4>
					<?php echo ucfirst($this->p->t('global', 'stornierteLehrauftraege')); ?>:
					<small>
						<abbr title="Anderes Studiensemester? Bitte oben im Dropdown wählen." >
							<?php echo $studiensemester_selected ?>
						</abbr>
					</small>
				</h4>
				<div class="row">
					<div class="col-lg-12">
						<?php $this->load->view('lehre/lehrauftrag/cancelledLehrauftragData.php'); ?>
					</div>
				</div>
				<br>
			</div>
		</div>
	</div><!-- end container -->
</div><!-- end page-wrapper -->
<br>

<?php $this->load->view('templates/FHC-Footer'); ?>

