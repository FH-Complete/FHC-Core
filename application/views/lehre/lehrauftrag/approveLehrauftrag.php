<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag erteilen',
        'jquery3' => true,
        'jqueryui1' => true,
        'jquerycheckboxes1' => true,
        'bootstrap3' => true,
        'fontawesome4' => true,
        'sbadmintemplate3' => true,
        'tabulator4' => true,
        'momentjs2' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array(
                'lehrauftraegeErteilen',
	            'mehrHilfe',
	            'weitereInformationenUnter'
            ),
	        'ui' => array(
		        'anzeigen',
		        'alleAnzeigen',
		        'nurNeueAnzeigen',
		        'nurBestellteAnzeigen',
		        'nurErteilteAnzeigen',
		        'nurAngenommeneAnzeigen',
		        'nurGeaenderteAnzeigen',
		        'nurDummiesAnzeigen',
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
                'stundenStundensatzGeaendert',
                'neuerLehrauftragOhneLektorVerplant',
                'wartetAufBestellung',
                'wartetAufErneuteBestellung',
                'neuerLehrauftragWartetAufBestellung',
                'letzterStatusBestellt',
                'letzterStatusErteilt',
                'letzterStatusAngenommen',
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
            ),
	        'lehre' => array(
		        'lehrauftragStandardBestellprozess',
		        'lehrauftragStandardBestellprozessBestellen',
		        'lehrauftragStandardBestellprozessErteilen',
		        'lehrauftragStandardBestellprozessAnnehmen',
		        'lehrauftraegeErteilen',
		        'lehrauftraegeErteilenText',
		        'lehrauftraegeErteilenKlickStatusicon',
		        'lehrauftraegeErteilenLehrauftraegeWaehlen',
		        'lehrauftraegeErteilenMitKlickErteilen',
		        'geaenderteLehrauftraege',
		        'geaenderteLehrauftraegeTextBeiErteilung',
		        'lehrauftraegeNichtAuswaehlbar',
		        'lehrauftraegeNichtAuswaehlbarTextBeiErteilung',
		        'filterAlle',
		        'filterNeu',
		        'filterBestellt',
		        'filterErteilt',
		        'filterAngenommen',
		        'filterGeaendert',
		        'filterDummies'
            )
        ),
        'customJSs' => array(
                'public/js/bootstrapper.js',
                'public/js/lehre/lehrauftrag/approveLehrauftrag.js'
        )
    )
);
?>

<body>
    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>
    <div id="page-wrapper">
    	<div class="container-fluid">

		<!-- title & helper link -->
        <div class="row">
            <div class="col-lg-12 page-header">
				<a class="pull-right" data-toggle="collapse" href="#collapseHelp" aria-expanded="false" aria-controls="collapseExample">
					<?php echo $this->p->t('ui', 'hilfeZuDieserSeite'); ?>
				</a>
                <h3>
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraegeErteilen')); ?>
                </h3>
            </div>
        </div>

		<!-- helper collapse module -->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseHelp">
				<div class="well">
					<?php $this->load->view('lehre/lehrauftrag/approveLehrauftragHelp') ?>
				</div>
			</div>
		</div>

		<!-- dropdown widgets -->
        <div class="row">
            <div class="col-lg-12">
                <form id="formLehrauftrag" class="form-inline" action="" method="get">
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
                    <div class="form-group">
                        <?php
                        echo $this->widgetlib->widget(
                            'Organisationseinheit_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $organisationseinheit_selected,
                                'organisationseinheit' => $organisationseinheit
                            ),
                            array(
                                'name' => 'organisationseinheit',
                                'id' => 'organisationseinheit'
                            )
                        );
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                        echo $this->widgetlib->widget(
                            'Ausbildungssemester_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $ausbildungssemester_selected,
                                'number_semester' => 6
                            ),
                            array(
                                'name' => 'ausbildungssemester',
                                'id' => 'ausbildungssemester'
                            )
                        );
                        ?>
                    </div>
                    <button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group"><?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?></button>
                </form>
            </div>
        </div>

		<!-- tabulator data table -->
        <div class="row">
            <div class="col-lg-12">
                <?php $this->load->view('lehre/lehrauftrag/approveLehrauftragData.php'); ?>
            </div>
        </div>
        <br>

		<!-- filter buttons & erteil-button -->
        <div class="row">
            <div class="col-xs-12">
                <button id="approve-lehrauftraege" class="btn btn-primary pull-right"><?php echo ucfirst($this->p->t('global', 'lehrauftraegeErteilen')); ?></button>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-all" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'alleAnzeigen'); ?>"><i class='fa fa-users'></i></button>
                        <button id="show-new" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurNeueAnzeigen'); ?>"><i class='fa fa-user-plus'></i></button>
                        <button id="show-ordered" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurBestellteAnzeigen'); ?>"></button><!-- png img set in javascript -->
                        <button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurErteilteAnzeigen'); ?>"></button><!-- png img set in javascript -->
                        <button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurAngenommeneAnzeigen'); ?>"><i class='fa fa-handshake-o'></i></button>
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-changed" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurGeaenderteAnzeigen'); ?>"></button><!-- png img set in javascript -->
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-dummies" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurDummiesAnzeigen'); ?>"><i class='fa fa-user-secret'></i></button>
                    </div>
                </div>
            </div>
        </div>
		
    	</div><!-- end container -->
    </div><!-- end page-wrapper -->
	<br>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

