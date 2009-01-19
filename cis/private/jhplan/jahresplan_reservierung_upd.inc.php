<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_reservierung_upd aendern der Reservierung hinzufuegen oder entfernen der VeranstaltungsID
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Detail des Ergebnisse der Jahresplanveranstaltung Reservierung - anederungen
*
*/
#-------------------------------------------------------------------------------------------	
function jahresplan_reservierung_upd($oJahresplan)
{
	$showHTML='';
	$oJahresplan->classJahresplan->InitReservierung();
	$oJahresplan->classJahresplan->setReservierung_id($oJahresplan->reservierung_id);
	
	$oJahresplan->reservierung=array();
	if ($oJahresplan->classJahresplan->saveReservierung($_REQUEST))
	{
		$oJahresplan->reservierung=$oJahresplan->classJahresplan->getReservierung();
		if (is_array($oJahresplan->reservierung) && count($oJahresplan->reservierung)>0 && isset($oJahresplan->reservierung[0]['veranstaltung_id']) && !empty($oJahresplan->reservierung[0]['veranstaltung_id']) )
			$showHTML.='zugeordnet ';
		else
			$showHTML.='aufgehoben ';			
	}	
	else
		$showHTML.='ge&auml;ndert';
	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
	return $showHTML;
}

?>
