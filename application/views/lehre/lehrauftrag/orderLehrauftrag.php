<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag bestellen',
        'jquery3' => true,
        'jqueryui1' => true,
        'jquerycheckboxes1' => true,
        'bootstrap3' => true,
        'fontawesome6' => true,
        'sbadmintemplate3' => true,
        'tabulator5' => true,
        'tabulator5JQuery' => true,
        'momentjs2' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array(
                'lehrauftraegeBestellen',
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
		        'neuerLehrauftragOhneLektorVerplant',
		        'neuerLehrauftragWartetAufBestellung',
		        'letzterStatusBestellt',
		        'letzterStatusErteilt',
		        'letzterStatusAngenommen',
                'nachAenderungStundensatzStunden',
                'vorAenderungStundensatzStunden'
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
                'lehrauftraegeBestellen',
                'lehrauftraegeBestellenText',
                'lehrauftraegeBestellenKlickStatusicon',
                'lehrauftraegeBestellenLehrauftraegeWaehlen',
                'lehrauftraegeBestellenMitKlickBestellen',
                'lehrauftraegeBestellenVertragWirdAngelegt',
                'geaenderteLehrauftraege',
                'geaenderteLehrauftraegeText',
                'lehrauftraegeNichtAuswaehlbar',
                'lehrauftraegeNichtAuswaehlbarText',
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
                'public/js/lehre/lehrauftrag/orderLehrauftrag.js'
        )
    )
);
?>


    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>
    <div id="page-wrapper">
        <div class="container-fluid">
			
		<!-- title & helper link -->
        <div class="row">
            <div class="col-lg-12 page-header">
				<a class="pull-right" data-toggle="collapse" href="#collapseHelp" aria-expanded="false" aria-controls="collapseHelp">
					<?php echo $this->p->t('ui', 'hilfeZuDieserSeite'); ?>
				</a>
                <h3>
                    <?php echo ucfirst($this->p->t('global', 'lehrauftraegeBestellen')); ?>
                </h3>
            </div>
        </div>

		<!-- helper collapse module -->
		<div class="row">
			<div class="col-lg-12 collapse" id="collapseHelp">
				<div class="well">
					<?php $this->load->view('lehre/lehrauftrag/orderLehrauftragHelp') ?>
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
                            'Studiengang_widget',
                            array(
                                DropdownWidget::SELECTED_ELEMENT => $studiengang_selected,
                                'studiengang' => $studiengang
                            ),
                            array(
                                'name' => 'studiengang',
                                'id' => 'studiengang'
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
                                'number_semester' => 10
                            ),
                            array(
                                'name' => 'ausbildungssemester',
                                'id' => 'ausbildungssemester'
                            )
                        );
                        ?>
                    </div>
			<button type="submit" name="submit" value="anzeigen" class="btn btn-default form-group">
				<?php echo ucfirst($this->p->t('ui', 'anzeigen')); ?>
			</button>
                </form>
            </div>
        </div>

		<!-- tabulator data table -->
		<div class="tabulator-initialfontsize">
		<?php $this->load->view('lehre/lehrauftrag/orderLehrauftragData.php'); ?>
		</div>

		<!-- filter buttons & bestell-button -->
        <div class="row">
            <div class="col-xs-12">
                <button id="order-lehrauftraege" class="btn btn-primary pull-right" data-toggle="tooltip" data-placement="left" title=""><?php echo ucfirst($this->p->t('global', 'lehrauftraegeBestellen')); ?></button>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-all" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'alleAnzeigen'); ?>"><i class='fa fa-users'></i></button>
                        <button id="show-newAndChanged" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'neuUndGeaenderteAnzeigen'); ?>"><i style="margin-right:10px" class='fa fa-user-plus'></i><i style="margin-right:10px" class="fa fa-ellipsis-vertical"></i>&nbsp;<i class='fa fa-user-pen'></i></button>
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-new" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurNeueAnzeigen'); ?>"><i class='fa fa-user-plus'></i></button>
                        <button id="show-ordered" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurBestellteAnzeigen'); ?>"><i class='fa fa-user-tag'></i></button>
                        <button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurErteilteAnzeigen'); ?>"><i class='fa fa-user-check'></i></button>
                        <button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurAngenommeneAnzeigen'); ?>"><i class='fa-regular fa-handshake'></i></button>
                        <button id="show-changed" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurGeaenderteAnzeigen'); ?>"><i class='fa fa-user-pen'></i></button></button>
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

<?php $this->load->view('templates/FHC-Footer'); ?>

