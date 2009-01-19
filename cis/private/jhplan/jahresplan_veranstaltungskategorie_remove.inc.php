<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltungskategorie_remove entfernt eine bestimmte Jahresplankategorien
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML  Ergebnisse des Kategorie entfernen 
*
*/
function jahresplan_veranstaltungskategorie_remove($oJahresplan)
{
	$showHTML='';
	if (!$oJahresplan->classJahresplan)
		return $showHTML;
	if(!$oJahresplan->veranstaltungskategorie=$oJahresplan->classJahresplan->deleteVeranstaltungskategorie($_REQUEST))
	{	
		// Fehler - Error Ausgabe
		$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
		return $showHTML;
	}
	jahresplan_funk_veranstaltungskategorie_load_kpl($oJahresplan);					
	return $showHTML.='gel&ouml;scht';
}
?>
