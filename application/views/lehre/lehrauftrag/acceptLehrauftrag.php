<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Lehrauftrag annehmen',
		'jquery' => true,
		'jqueryui' => true,
		'jquerycheckboxes' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'sbadmintemplate' => false,
		'tabulator' => true,
		'momentjs' => true,
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

<body>
<div id="page-wrapper">
	<div class="container-fluid">

		<!-- title & helper link -->
		<div class="row">
			<div class="col-lg-12 page-header">
				<a class="pull-right" data-toggle="collapse" href="#collapseHelp" aria-expanded="false" aria-controls="collapseExample">
					<?php echo $this->p->t('ui', 'hilfeZuDieserSeite'); ?>
				</a>
				<h3>
					<?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?>
				</h3>
			</div>
		</div>

		<!-- helper collapse module -->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseHelp">
				<div class="well">
					<?php $this->load->view('lehre/lehrauftrag/acceptLehrauftragHelp') ?>
				</div> <!--./well-->
			</div>
		</div>

		<!-- dropdown widgets -->
		<div class="row">
			<div class="col-lg-12">
				<form id="formLehrauftrag" class="form-inline" action="" method="get">
					<input type="hidden" id="uid" name="uid" value="<?php echo getAuthUID(); ?>">
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
					<button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
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
		<?php if ($is_external_lector): ?>
		<div class="row">
			<div class="col-xs-12">
				<span class="pull-right"><?php echo $this->p->t('dms' , 'informationsblattExterneLehrende'); ?></span>
			</div>
		</div>
		<br>
		<?php endif; ?>

		<!-- filter buttons & PDF downloads & password field & akzeptieren-button -->
		<div class="row">
			<div class="col-xs-5 col-md-4">
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group" role="group">
						<button id="show-all" class="btn btn-default btn-lehrauftrag active focus" type="button"
								data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'alleAnzeigen'); ?>"><i class='fa fa-users'></i>
						</button>
						<button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button"
								data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurErteilteAnzeigen'); ?>">
						</button><!-- png img set in javascript -->
						<button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button"
								data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurAngenommeneAnzeigen'); ?>"><i class='fa fa-handshake-o'></i>
						</button>
					</div>

					<button id="show-cancelled" class="btn btn-default btn-lehrauftrag" type="button" style="margin-left: 20px;"
							data-toggle="collapse" data-placement="left" title="<?php echo $this->p->t('ui', 'nurStornierteAnzeigen'); ?>"
							data-target ="#collapseCancelledLehrauftraege" aria-expanded="false" aria-controls="collapseExample">
					</button><!-- png img set in javascript -->
				</div>
			</div>


			<div class="col-xs-3 col-md-offset-2 col-md-2">
					<div class="btn-group dropup pull-right">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php echo $this->p->t('global', 'dokumentePDF'); ?>&nbsp;&nbsp;<i class="fa fa-arrow-down"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="caret"></span>
					</button>
					<ul id="ul-download-pdf" class="dropdown-menu">
						<li value="etw"><a href="#"><?php echo $this->p->t('global', 'PDFLehrauftraegeFH'); ?></a></li>
						<li value="lehrgang"><a href="#"><?php echo $this->p->t('global', 'PDFLehrauftraegeLehrgaenge'); ?></a></li>
					</ul>
				</div>
			</div>

			<div class="col-xs-4 col-md-offset-0 col-md-4">
				<div class="input-group">
					<input id="username" autocomplete="username" style="position: absolute; opacity: 0;"><!-- this is to prevent Chrome autofilling a random input field with the username-->
					<input id="password" type="password" autocomplete="new-password" class="form-control" placeholder="CIS-<?php echo ucfirst($this->p->t('password', 'password')); ?>">
						<span class="input-group-btn">
							<button id="accept-lehrauftraege" class="btn btn-primary pull-right"><?php echo ucfirst($this->p->t('global', 'lehrauftraegeAnnehmen')); ?></button>
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
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
