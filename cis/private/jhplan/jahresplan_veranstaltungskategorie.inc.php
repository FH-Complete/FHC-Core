<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltungskategorie Insert,Update einer bestimmten Jahresplankategorien
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML des Ergebnisses der Jahresplankategorie verarbeitung
*
*/
function jahresplan_veranstaltungskategorie($oJahresplan)
{
	$showHTML='';
	if (!$oJahresplan->classJahresplan)
		return $showHTML;
	if($veranstaltungskategorie=$oJahresplan->veranstaltungskategorie=$oJahresplan->classJahresplan->saveVeranstaltungskategorie($_REQUEST))
	{
		if (isset($_REQUEST['tabindex']) && $_REQUEST['tabindex']!='' 
		&& (!isset($_REQUEST['veranstaltungskategorie_kurzbz_old']) || $_REQUEST['veranstaltungskategorie_kurzbz_old']=='') )
		{
			$showHTML.='<input style="display:none;" name="veranstaltungskategorie_kurzbz_old'.$_REQUEST['tabindex'].'" value="'.$oJahresplan->veranstaltungskategorie[0]['veranstaltungskategorie_kurzbz'].'">';
		}
		else if (!isset($_REQUEST['veranstaltungskategorie_kurzbz_old']) || $_REQUEST['veranstaltungskategorie_kurzbz_old']=='' )
		{
			$showHTML.='<input style="display:none;" name="veranstaltungskategorie_kurzbz_old" value="'.$oJahresplan->veranstaltungskategorie[0]['veranstaltungskategorie_kurzbz'].'">';
		}
		jahresplan_funk_veranstaltungskategorie_load_kpl($oJahresplan);					
		return $showHTML.='gespeichert';
	}			
	// Fehler - Error Ausgabe
	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
	return $showHTML;	
}
?>
