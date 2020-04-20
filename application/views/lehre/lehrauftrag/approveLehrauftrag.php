<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag erteilen',
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
            'global' => array('lehrauftraegeErteilen'),
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
                <td class="text-muted">BESTELLEN<br>(Studiengangsleitung)</td>
                <td></td>
                <td><b>ERTEILEN<br>(Department-/Kompetenzfeldleitung)</b></td>
                <td></td>
                <td class="text-muted">ANNEHMEN<br>(LektorIn)</td>
            </tr>
        </table>
        </div>
        <br>
    
        <h4>Lehraufträge erteilen</h4>
        <div class="panel panel-body">
            Sobald Lehraufträge bestellt wurden, können Sie diese hier erteilen.<br>
            Erteilte Lehraufträge können von den Lehrenden angenommen werden.<br>
            <ol>
                <li>Klicken Sie unten auf das Status-Icon 'Nur bestellte anzeigen' oder 'Alle anzeigen'</li>
                <li>Wählen Sie die zu erteilenden Lehraufträge selbst oder alle über den Button 'Alle auswählen'.</li>
                <li>Klicken Sie auf Lehrauftrag erteilen.</li>
            </ol>
        </div>
        <br>
        
        <h4>Geänderte Lehraufträge</h4>
        <div class="panel panel-body">
            Im FAS können Änderungen an Stunden/Stundensatz eines Lehrauftrags durchgeführt werden, solange dieser nicht vom Lehrenden angenommen wurde.<br>
            Wenn Änderungen an bereits bestellten oder erteilten Lehraufträgen vorgenommen wurden, müssen diese vom Studiengang erneut bestellt werden.<br>
            Bei bereits erteilten Lehraufträgen wird zusätzlich der Status 'erteilt' zurückgesetzt.
        </div>
        <br>
        
        <h4>Warum kann ich manche Lehraufträge nicht auswählen?</h4>
        <div class="panel panel-body">
            Nur Lehraufträge mit dem Status 'bestellt' können gewählt werden.<br>
            Neue, Bestellte, Akzeptierte oder geänderte Lehraufträge werden nur zu Ihrer Information angezeigt und sind daher NICHT wählbar.
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
        <td class = "text-muted"><b>ORDER<br> (Study course Director)</b></td>
        <td></td>
        <td>APPROVEMENT<br> (Department- / Competence field Manager)</td>
        <td></td>
        <td class = "text-muted">ACCEPTANCE<br> (Lecturer)</td>
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
