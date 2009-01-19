<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_liste_del loeschen einer Veranstaltungen
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Wartungsanzeige der Ergebnisse der Veranstaltungen
*
*/
function jahresplan_veranstaltung_liste_del($oJahresplan)
{
	$showHTML='';
	if (!$oJahresplan->classJahresplan)
		return $showHTML;
	if($oJahresplan->veranstaltung=$oJahresplan->classJahresplan->deleteVeranstaltung($_REQUEST))
		$showHTML.='Fehler ';
	else
		$showHTML.='gel&ouml;scht ';
	// Fehler - Error Ausgabe
	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
	return $showHTML;	
}
?>
