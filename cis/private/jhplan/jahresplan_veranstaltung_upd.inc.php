<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_upd Datenwartung Veranstaltungsen 
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Wartungsanzeige der Ergebnisse der Veranstaltungen
*
*/
function jahresplan_veranstaltung_upd($oJahresplan)
{
	$showHTML=' ';
	if (!$oJahresplan->classJahresplan)
		return $showHTML;
	if($oJahresplan->veranstaltung=$oJahresplan->classJahresplan->saveVeranstaltung($_REQUEST))
	{
		$showHTML.='<input style="display:none;font-size:small;" name="veranstaltung_id" type="text" value="'.(isset($oJahresplan->veranstaltung[0]['veranstaltung_id'])?$oJahresplan->veranstaltung[0]['veranstaltung_id']:$oJahresplan->classJahresplan->getVeranstaltung_id()).'" />';
		$showHTML.='<input style="display:none;font-size:small;" name="veranstaltung_id_old" type="text" value="'.(isset($oJahresplan->veranstaltung[0]['veranstaltung_id'])?$oJahresplan->veranstaltung[0]['veranstaltung_id']:$oJahresplan->classJahresplan->getVeranstaltung_id()).'" />';

		return $showHTML.=($oJahresplan->classJahresplan->getNewRecord()?' angelegt':' gespeichert') .' Veranstaltung ID ' . (isset($oJahresplan->veranstaltung[0]['veranstaltung_id'])?$oJahresplan->veranstaltung[0]['veranstaltung_id']:$oJahresplan->classJahresplan->getVeranstaltung_id());
	}	
	// Fehler - Error Ausgabe

	$showHTML.='<input style="display:none;font-size:small;" name="veranstaltung_id" type="text" value="'.(isset($_REQUEST['veranstaltung_id'])?$_REQUEST['veranstaltung_id']:'').'" />';
	$showHTML.='<input style="display:none;font-size:small;" name="veranstaltung_id_old" type="text" value="'.(isset($_REQUEST['veranstaltung_id'])?$_REQUEST['veranstaltung_id']:'').'" />';
	$showHTML.=(isset($_REQUEST['veranstaltung_id'])?$_REQUEST['veranstaltung_id']:'ID ').' ';

	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
	return $showHTML;	
}
?>
