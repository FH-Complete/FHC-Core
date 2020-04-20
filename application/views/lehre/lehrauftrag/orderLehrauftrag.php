<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag bestellen',
        'jquery' => true,
        'jqueryui' => true,
        'jquerycheckboxes' => true,
        'bootstrap' => true,
        'fontawesome' => true,
        'sbadmintemplate' => true,
        'tabulator' => true,
        'momentjs' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'tablewidget' => true,
        'navigationwidget' => true,
        'phrases' => array(
            'global' => array('lehrauftraegeBestellen'),
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
            )
        ),
        'customJSs' => array(
                'public/js/bootstrapper.js',
                'public/js/lehre/lehrauftrag/orderLehrauftrag.js'
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
					<?php echo _getHelptext(getUserLanguage()); ?>
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
		<?php $this->load->view('lehre/lehrauftrag/orderLehrauftragData.php'); ?>

		<!-- filter buttons & bestell-button -->
        <div class="row">
            <div class="col-xs-12">
                <button id="order-lehrauftraege" class="btn btn-primary pull-right" data-toggle="tooltip" data-placement="left" title=""><?php echo ucfirst($this->p->t('global', 'lehrauftraegeBestellen')); ?></button>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button id="show-all" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'alleAnzeigen'); ?>"><i class='fa fa-users'></i></button>
                        <button id="show-new" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurNeueAnzeigen'); ?>"><i class='fa fa-user-plus'></i></button>
                        <button id="show-ordered" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurBestellteAnzeigen'); ?>"></button><!-- png img set in javascript -->
                        <button id="show-approved" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurErteilteAnzeigen'); ?>"></button><!-- png img set in javascript -->
                        <button id="show-accepted" class="btn btn-default btn-lehrauftrag" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurAngenommeneAnzeigen'); ?>"><i class='fa fa-handshake-o'></i></button>
                    </div>
                    <div class="btn-group" role="group" style="margin-left: 20px;">
                        <button id="show-changed" class="btn btn-default btn-lehrauftrag active focus" type="button" data-toggle="tooltip" data-placement="left" title="<?php echo $this->p->t('ui', 'nurGeaenderteAnzeigen'); ?>"></button><!-- png img set in javascript -->
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

<?php

function _getHelptext($user_language)
{
	$html = '';

	if ($user_language == 'German')
	{
		$html = <<<EOT
        <h4>Lehrauftrag Standard-Bestellprozess</h4>
        <div class="panel panel-body">
            <table>
                <tr class="text-center">
                    <td><img src="../../../public/images/icons/fa-user-tag.png" style="height: 60px; width: 60px;"></td>
                    <td><i class='fa fa-2x fa-long-arrow-right'></i></td>
                    <td><img src="../../../public/images/icons/fa-user-check.png" style="height: 60px; width: 60px;"></td>
                    <td><i class='fa fa-2x fa-long-arrow-right'></i></td>
                    <td><i class='fa fa-2x fa-handshake-o'></i></td>
                </tr>
                <tr class="text-center">
                    <td><b>BESTELLEN<br>(Studiengangsleitung)</b></td>
                    <td></td>
                    <td class="text-muted">ERTEILEN<br>(Department-/Kompetenzfeldleitung)</td>
                    <td></td>
                    <td class="text-muted">ANNEHMEN<br>(LektorIn)</td>
                </tr>
            </table>
        </div>
        <br>
        
        <h4>Lehraufträge bestellen</h4>
        <div class="panel panel-body">
            Sobald im FAS ein Lehrauftrag/eine Projektbetreuung angelegt wurde, können Sie diese hier bestellen.<br>
            Bestellte Lehraufträge sind zur Erteilung freigegeben.<br>
        <ol>
            <li>Klicken Sie unten auf das Status-Icon 'Nur neue anzeigen', 'Nur geänderte anzeigen' oder 'Alle anzeigen'</li>
            <li>Wählen Sie die zu bestellenden Lehraufträge selbst oder über den Button 'Alle auswählen'.</li>
            <li>Klicken Sie auf Lehrauftrag bestellen.</li>
        </ol>
            Für jeden bestellten Lehrauftrag legt das System einen Vertrag an.
        </div>
        <br>
        
        <h4>Geänderte Lehraufträge</h4>
        <div class="panel panel-body">
            Im FAS können Änderungen an Stunden/Stundensatz eines Lehrauftrags durchgeführt werden, solange dieser nicht vom Lehrenden angenommen wurde.<br>
            Diese müssen dann erneut bestellt werden.<br><br>
            Wenn Änderungen an bereits bestellten oder erteilten Lehraufträgen vorgenommen wurden, werden diese in einem tooltip angezeigt.<br>
            Fahren Sie dazu mit der Maus über dem Status-Icon am Beginn der Zeile.<br>
        </div>
        <br>
        
        <h4>Warum kann ich manche Lehraufträge nicht auswählen?</h4>
        <div class="panel panel-body">
            Nur Lehraufträge mit dem Status 'neu' und 'geändert' können bestellt werden.<br>
            Erteilte oder akzeptierte Lehraufträge werden nur zu Ihrer Information angezeigt und sind daher NICHT wählbar.
        </div>
        <br>

        <h4>Filter</h4>
        <div class="panel panel-body">
            <table class="table table-bordered">
                <tr class="text-center">
                    <td class="col-xs-1"><i class='fa fa-users'></i></td>
                    <td class="col-xs-1"><i class='fa fa-user-plus'></i></td>
                    <td class="col-xs-1"><img src="../../../public/images/icons/fa-user-tag.png" style="height: 30px; width: 30px;"></td>
                    <td class="col-xs-1"><img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px;"></td>
                    <td class="col-xs-1"><i class='fa fa-handshake-o'></i></td>
                    <td class="col-xs-1"><img src="../../../public/images/icons/fa-user-edit.png" style="height: 30px; width: 30px;"></td>
                    <td class="col-xs-1"><i class='fa fa-user-secret'></i></td>

                </tr>
                <tr class="text-center">
                    <td><b>Alle</b><br>Alle Lehraufträge mit jedem Status, auch geänderte und Dummy-Aufträge</td>
                    <td><b>Neu</b><br>Nur Lehraufträge, die im FAS über die Zuteilung eines Lehrenden zu einer Lehreinheit/einem Projekt angelegt und noch nicht bestellt worden sind</td>
                    <td><b>Bestellt</b><br>Nur bestellte UND geänderte bestellte Lehraufträge</td>
                    <td><b>Erteilt</b><br>Nur erteilte UND geänderte erteilte Lehraufträge</td>
                    <td><b>Angenommen</b><br>Nur vom Lehrenden angenommene Lehraufträge</td>
                    <td><b>Geändert</b><br>Nur Lehraufträge, die geändert wurden, nachdem sie bereits bestellt oder erteilt worden sind</td>
                    <td><b>Dummies</b><br>Nur Lehraufträge, die mit einem Dummylektor angelegt sind</td>
                </tr>
            </table>
        </div>
        <br>

        <h4>Mehr Hilfe?</h4>
        <div class="panel panel-info panel-body">
        Weitere Informationen unter <a href="https://wiki.fhcomplete.org/doku.php?id=fhc:lehrauftraege" target="_blank">FH Complete WIKI</a> 
        </div><br>
EOT;
	}
    elseif ($user_language == 'English')
	{
		$html = <<<EOT
		<h4>Standard Ordering Process for Teaching Lectureships</h4>
        <div class = "panel panel-body">
        <table>
        <tr class = "text-center">
        <td><img src = "../../../public/images/icons/fa-user-tag.png" style = "height: 60px; width: 60px;"></td>
        <td><i class = 'fa fa-2x fa-long-arrow-right'> </i> </td>
        <td><img src = "../../../public/images/icons/fa-user-check.png" style = "height: 60px; width: 60px;"></td>
        <td><i class = 'fa fa-2x fa-long-arrow-right'></i></td>
        <td><i class = 'fa fa-2x fa-handshake-o'></i></td>
        </tr>
        <tr class = "text-center">
        <td><b>ORDER<br>(Study course Director)</b></td>
        <td> </td>
        <td class = "text-muted">APPROVEMENT<br>(Department- / Competence field Manager)</td>
        <td></td>
        <td class = "text-muted">ACCEPTANCE<br>(Lecturer)</td>
        </tr>
        </table>
        </div>
        <br>
        
         <h4>Need more Help?</h4>
        <div class="panel panel-info panel-body">
        For further information please go to <a href="https://wiki.fhcomplete.org/doku.php?id=fhc:lehrauftraege" target="_blank">FH Complete WIKI</a> 
        </div><br>
EOT;
	}
	return $html;
}

?>