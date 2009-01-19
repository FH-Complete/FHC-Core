<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltungskategorie_default anzeigen Termin Kalender 
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Kalender
*
*/
function jahresplan_veranstaltungskategorie_default($oJahresplan)
{
	$showHTML='';
	if (!is_array($oJahresplan->veranstaltungskategorie)) // Keine Daten
		return $showHTML;

	// Veranstaltungskategorie
	$oJahresplan->classJahresplan->InitVeranstaltung();
	
	// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
	$oJahresplan->classJahresplan->setVeranstaltungskategorieMitarbeiter($oJahresplan->is_lector);
	// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	
	if (!$oJahresplan->Wartungsberechtigt)	
		$oJahresplan->classJahresplan->setFreigabe(true);
	else
		$oJahresplan->classJahresplan->setFreigabe(false);
		
	$oJahresplan->classJahresplan->setVeranstaltungskategorie_kurzbz($oJahresplan->veranstaltungskategorie_kurzbz);
	$oJahresplan->classJahresplan->setVeranstaltung_id($oJahresplan->veranstaltung_id);
	$oJahresplan->classJahresplan->setSuchtext($oJahresplan->Suchtext);

	if (!empty($oJahresplan->Suchtext))
		$oJahresplan->Monat='';
		
	// Plausib Datum
	if (empty($oJahresplan->veranstaltung_id))
	{
		if (empty($oJahresplan->Jahr))
			$oJahresplan->Jahr=date("Y", mktime(0,0,0,date("m"),date("d"),date("y")));	
		if (!empty($oJahresplan->Woche))
		{
			$iTmpMinKW=date("W",mktime(0, 0, 0,(empty($oJahresplan->Monat) || $oJahresplan->Monat>12?'01':$oJahresplan->Monat),1, $oJahresplan->Jahr));
			$iTmpMaxKW=date("W",mktime(0, 0, 0,(empty($oJahresplan->Monat) || $oJahresplan->Monat>12?'01':$oJahresplan->Monat),$iTmpMaxTage, $oJahresplan->Jahr));
			$iTmpMaxKW=number_format($iTmpMaxKW);
			if ($iTmpMaxKW<2 && $iTmpMonat==12)
				$iTmpMaxKW=53;
			$oJahresplan->classJahresplan->setStart_jahr_woche($oJahresplan->Jahr.$iTmpMinKW);
			$oJahresplan->classJahresplan->setEnde_jahr_woche($oJahresplan->Jahr.$iTmpMaxKW);
		}
		elseif (!empty($oJahresplan->Monat))
		{
			$oJahresplan->classJahresplan->setStart_jahr_monat($oJahresplan->Jahr.(empty($oJahresplan->Monat) || $oJahresplan->Monat>12?'01':$oJahresplan->Monat));
			$oJahresplan->classJahresplan->setEnde_jahr_monat($oJahresplan->Jahr.(empty($oJahresplan->Monat) || $oJahresplan->Monat>12?'01':$oJahresplan->Monat));
		}	
		else
			$oJahresplan->classJahresplan->setStart_jahr($oJahresplan->Jahr);
	}	
	// Selektions - Daten lesen
	$veranstaltung=array();
	if ($oJahresplan->classJahresplan->loadVeranstaltung())
		$oJahresplan->veranstaltung=$oJahresplan->classJahresplan->getVeranstaltung();
	else
		$oJahresplan->veranstaltung=array();
	// Check ob ein Fehler war bei der DB Verarbeitung
	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
	if (!isset($oJahresplan->veranstaltung[0]))
		return "keine Daten gefunden ".(!empty($oJahresplan->Suchtext)? ' Suchtext '.$oJahresplan->Suchtext:'' ).(!empty($oJahresplan->veranstaltung_id)? ' ID '.$oJahresplan->veranstaltung_id:'' );
	
	// Pruefen ob die Kalenderanzeige erfolgen soll, oder bei Eingabe ID,Suchtext,Kategorie erfolgt sie nicht.
	$keinKalender=$oJahresplan->veranstaltung_id.$oJahresplan->Suchtext.$oJahresplan->veranstaltungskategorie_kurzbz;
	// DatenArray erweitern	mit Zusatzdaten wie Bilder, Reservierung
	jahresplan_funk_veranstaltung_extend($oJahresplan,$keinKalender);
	
	// Anzeige-Variante (Liste,Detailansicht)
	if ($keinKalender)
	{
		if (!empty($oJahresplan->veranstaltung_id) )
			return $showHTML.=jahresplan_funk_show_veranstaltung_detail($oJahresplan->veranstaltung[0],$oJahresplan);
		if (!empty($oJahresplan->Suchtext) || !empty($oJahresplan->veranstaltungskategorie_kurzbz))
			return jahresplan_veranstaltung_listenanzeige($oJahresplan);
		return "Fehlende Funktion f&uuml;r keine Kalenderanzeige";
	}	

	$iTmpMinMonate=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]["VerarbeitenMonate"][0];
	$iTmpMinTage=1;
	$iTmpMaxMonate=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]["VerarbeitenMonate"][count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]["VerarbeitenMonate"])-1];
	$iTmpMaxTage=strftime("%d",mktime(0, 0, 0,($iTmpMaxMonate + 1), 0, $oJahresplan->Jahr));
	if (isset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]["VerarbeitenMonate"]));
		unset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]["VerarbeitenMonate"]);
	
	$showHTML.='<table style="border:0;width:100%;" summary="Kategorieauswahl '.$oJahresplan->veranstaltungskategorie_kurzbz.'">';
	for ($iTmpMonat=$iTmpMinMonate;$iTmpMonat<=$iTmpMaxMonate;$iTmpMonat++)
	{
		// Je Monat beg. Woche und Ende KW ermitteln
		$nowMonat=date("m", mktime(0,0,0,date("m"),date("d"),date("y")));
		$iTmpMinKW=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat][0];
		$iTmpMaxKW=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat][count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat])-1];
	
		// Monatsheader			
		$iTmpAnzahlDaten="keine Veranstaltungen";
		if (isset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['Monat'][$iTmpMonat]))
			$iTmpAnzahlDaten=count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['Monat'][$iTmpMonat])." Veranstaltungen";
		else
			$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['Monat'][$iTmpMonat]=array();
			
		$cTmpInfoMonat=strftime (constDatumKalenderHead, mktime(0, 0, 0, $iTmpMonat, 1, $oJahresplan->Jahr));
		if (!empty($oJahresplan->Monat) 
		|| count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['Monat'][$iTmpMonat])>0)
		{
			$cTmpStyleTableOn="<span title=\"ausblenden ".$cTmpInfoMonat."\"  onclick=\"show_layer('showMonat".$iTmpMonat."_infozeile');hide_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOn".$iTmpMonat."');show_layer('showMonatOff".$iTmpMonat."');\" style=\"cursor:pointer;\" id=\"showMonatOn".$iTmpMonat."\">&nbsp;<span style=\"cursor: pointer;\">&laquo;</span>&nbsp;</span>";
			$cTmpStyleTableOff="<span title=\"anzeigen ".$cTmpInfoMonat."\"  onclick=\"hide_layer('showMonat".$iTmpMonat."_infozeile');show_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOff".$iTmpMonat."');show_layer('showMonatOn".$iTmpMonat."');\" style=\"display:none;cursor:pointer;\" id=\"showMonatOff".$iTmpMonat."\">&nbsp;<span style=\"cursor: pointer;\">&raquo;</span>&nbsp;</span>";
			$showHTML.='<tr><td><h1 style="text-align:left;padding: 1px 1px 1px 1px;">'.$cTmpStyleTableOn.$cTmpStyleTableOff.$cTmpInfoMonat.'</h1></td></tr>';
			$showHTML.='<tr><td style="display:none;" id="showMonat'.$iTmpMonat.'_infozeile">'.$iTmpAnzahlDaten.'</td></tr>';
		}	
		else
		{
			$cTmpStyleTableOn="<span title=\" ausblenden ".$cTmpInfoMonat."\"  onclick=\"show_layer('showMonat".$iTmpMonat."_infozeile');hide_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOn".$iTmpMonat."');show_layer('showMonatOff".$iTmpMonat."');\" id=\"showMonatOn".$iTmpMonat."\"  style=\"display:none;cursor:pointer;\">&nbsp;<span style=\"cursor: pointer;\">&laquo;</span>&nbsp;</span>";
			$cTmpStyleTableOff="<span title=\"anzeigen ".$cTmpInfoMonat."\"  onclick=\"hide_layer('showMonat".$iTmpMonat."_infozeile');show_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOff".$iTmpMonat."');show_layer('showMonatOn".$iTmpMonat."');\" id=\"showMonatOff".$iTmpMonat."\"  style=\"cursor:pointer;\" >&nbsp;<span style=\"cursor: pointer;\">&raquo;&nbsp;</span></span>";
			$showHTML.='<tr><td><h1 style="text-align:left;padding: 1px 1px 1px 1px;">'.$cTmpStyleTableOn.$cTmpStyleTableOff.$cTmpInfoMonat.'</h1></td></tr>';
			$showHTML.='<tr><td id="showMonat'.$iTmpMonat.'_infozeile">'.$iTmpAnzahlDaten.'</td></tr>';
		}

		// Monatsanzeige
		if (!empty($oJahresplan->Monat) || count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['Monat'][$iTmpMonat])>0)
			$showHTML.='<tr><td style="vertical-align:top;" id="showMonat'.$iTmpMonat.'">';
		else
			$showHTML.='<tr><td style="vertical-align:top;display:none;" id="showMonat'.$iTmpMonat.'">';
	
		$showHTML.='
			<table style="border:0;width:100%;" summary="'.$cTmpInfoMonat.' Tagesinformation">';

		$showHTML.='<tr>';
				$showHTML.='<td style="text-align:right;color:silver;background-color:#FFFFFF;" colspan="9">Zum Anzeigen bzw. Ausblenden einer Kalenderwoche klicken Sie bitte auf den Pfeil.</td>';
		$showHTML.='</tr>';
			
		$showHTML.='
			<tr style="border:1px solid #FFFFFF;background-color:#E5E5E5;">
				<td colspan="8"><table cellspacing="1"><tr>
			';
		$showHTML.='
						<td style="width:43px;">KW</td>
						<td style="width:112px;">Montag</td>
						<td style="width:112px;">Dienstag</td>
						<td style="width:112px;">Mittwoch</td>
						<td style="width:112px;">Donnerstag</td>
						<td style="width:112px;">Freitag</td>
						<td style="width:112px;">Samstag</td>
						<td style="width:112px;">Sonntag</td>
';
		$showHTML.='
			</tr></table></td>
			</tr>';
			
		// Wochenanzeige
		$iTmpMinKW=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat][0];
		$iTmpMaxKW=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat][count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr]['VerarbeitenWochen'][$iTmpMonat])-1];
	  	for ($iTmpWoche=$iTmpMinKW;$iTmpWoche<=$iTmpMaxKW;$iTmpWoche++)
		{			
			$nowWeek=date("W", mktime(0,0,0,date("m"),date("d"),date("y")));	
			// Fuer alle WochenTage das Script Anzeige,Verstecken erzeugen
			$cTmpStyleTableOn="";
			$cTmpStyleTableOff="";			
			for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{	
				$cTmpStyleTableOn.="show_layer('week_".$iTmpMonat.$iTmpWoche.$iTmpTag."');";
				$cTmpStyleTableOff.="hide_layer('week_".$iTmpMonat.$iTmpWoche.$iTmpTag."');";
			}
// Wochenzeile Start
			$showHTML.='<tr style="vertical-align:top;background-color:#E5E5E5;">';
			if (!isset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche]))
				$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche]=array();
			$iTmpAnzahlDaten='';

//		Wochenanzeigen ON OFF
		$showHTML.='<td style="width:40px;border:1px outset #EFEFEF;vertical-align:top;">
			<table summary="Show '.$iTmpMonat.$iTmpWoche.'"><tr>';
			if (count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche])>0 
			&& ( empty($oJahresplan->Monat) || (!empty($oJahresplan->Monat) && $iTmpMonat==$oJahresplan->Monat && $iTmpMonat!=$nowMonat && $nowWeek!=$iTmpWoche) 
			  || ($iTmpMonat==$nowMonat && $nowWeek==$iTmpWoche) )) 
			{
				$showHTML.='<td title="Anzeigen '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="on_'.$iTmpMonat.$iTmpWoche.'"  style="cursor:pointer;display:none;vertical-align:top;" onclick="hide_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOn.'" ><span style="font-size:17px;cursor: pointer;">&raquo;</span>&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="check_on_'.$iTmpMonat.$iTmpWoche.'" /></td>';
				$showHTML.='<td title="Ausblenden '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="off_'.$iTmpMonat.$iTmpWoche.'"  style="cursor:pointer;vertical-align:top;" onclick="hide_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOff.'" ><span style="font-size:17px;cursor: pointer;">&laquo;</span>&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="check_off_'.$iTmpMonat.$iTmpWoche.'" /></td>';
			}
			else
			{
				$showHTML.='<td title="Anzeigen '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="on_'.$iTmpMonat.$iTmpWoche.'" style="cursor:pointer;vertical-align:top;" onclick="hide_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOn.'" ><span style="font-size:17px;cursor: pointer;">&raquo;</span>&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="check_on_'.$iTmpMonat.$iTmpWoche.'" /></td>';
				$showHTML.='<td title="Ausblenden '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="off_'.$iTmpMonat.$iTmpWoche.'" style="cursor:pointer;display:none;vertical-align:top;" onclick="hide_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOff.'" ><span style="font-size:17px;cursor: pointer;">&laquo;</span>&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="check_off_'.$iTmpMonat.$iTmpWoche.'" /></td>';
			}
		$showHTML.='</tr></table></td>';

		// Kalendertage			
		$showHTML.='<td style="border:1px inset  #EFEFEF;">';
			$showHTML.='<table id="week_'.$iTmpMonat.$iTmpWoche.'"><tr>';
			// Tage je Woche	
		  	for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{
				if (!isset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag]))
				 	continue;	
					
				$iTmpTimeStamp=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag];
				
				if (isset($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['WochenTag'][$iTmpWoche][$iTmpTag]))
					$iTmpStartTagErgebniss=$oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['WochenTag'][$iTmpWoche][$iTmpTag];
				else
					$iTmpStartTagErgebniss=array();
					
				$cTmpPruef1=date("Y",$iTmpTimeStamp);
				$cTmpPruef2=$oJahresplan->Jahr;
				
				if (!empty($oJahresplan->Monat))
				{
					$cTmpPruef1=date("Ym",$iTmpTimeStamp);
					$cTmpPruef2=$oJahresplan->Jahr.(strlen($oJahresplan->Monat)<2?'0'.$oJahresplan->Monat:$oJahresplan->Monat);
				}	
				else if (!empty($oJahresplan->Woche))
				{
					$cTmpPruef1=date("YW",$iTmpTimeStamp);
					$cTmpPruef2=$oJahresplan->Jahr.(strlen($oJahresplan->Woche)<2?'0'.$oJahresplan->Woche:$oJahresplan->Woche);
				}			
				else 
				{
					$cTmpPruef1=date("Ym",$iTmpTimeStamp);
					$cTmpPruef2=$oJahresplan->Jahr.($iTmpMonat<10?"0".$iTmpMonat:$iTmpMonat);
				}		
						
				$showHTML.='<td title="'.strftime(constDatumLang,$iTmpTimeStamp).'" style="width:110px;vertical-align:top;border:0px outset #000000;background-color:#FFFFFF;">';
				
				if ($cTmpPruef1!=$cTmpPruef2) // Nicht mehr im Aktuellen Monat
					$showHTML.='<div style="width:98%;text-align:left;border:1px outset #EBEBEB;color:silver;	font-size : small;">';
				elseif (count($iTmpStartTagErgebniss)<1)
					$showHTML.='<div style="width:98%;text-align:left;border:1px inset #B2B2B2;color:gray;	background-color:#E5E5E5;font-size : small;font-weight: lighter;">';
				else
					$showHTML.='<div onclick="hide_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOn.'" style="width:98%;text-align:left;border:1px inset  #B2B2B2;color:black;	background-color:#E5E5E5;font-size : small;font-weight: bold;">';
				$showHTML.='&nbsp;'.strftime (constDatumKalender, $iTmpTimeStamp).'&nbsp;</div>';
					
				if (count($oJahresplan->veranstaltung_kalender[$oJahresplan->Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche])>0 
				&& ( empty($oJahresplan->Monat) || (!empty($oJahresplan->Monat) && $iTmpMonat==$oJahresplan->Monat && $iTmpMonat!=$nowMonat && $nowWeek!=$iTmpWoche) 
				  || ($iTmpMonat==$nowMonat && $nowWeek==$iTmpWoche) ) )
					$showHTML.='<table id="week_'.$iTmpMonat.$iTmpWoche.$iTmpTag.'" style="border:0px;" cellpadding="0" cellspacing="0" summary="'.$cTmpInfoMonat.' Tagesinformation Veranstaltung">';
				else
					$showHTML.='<table id="week_'.$iTmpMonat.$iTmpWoche.$iTmpTag.'" style="display:none;border:0px;" cellpadding="0" cellspacing="0" summary="'.$cTmpInfoMonat.' Tagesinformation Veranstaltung">';
					
				if (count($iTmpStartTagErgebniss)<1)
				{
					$showHTML.='<tr style="vertical-align:top;font-size:small;">';
						$showHTML.='<td style="vertical-align:top;" rowspan="2">
							<table summary="blank'.$iTmpMonat.$iTmpWoche.$iTmpTag.'" style="border:0px;vertical-align:top;text-align:left;" cellpadding="0" cellspacing="0">
								<tr style="vertical-align:top;"><td>&nbsp;</td></tr>
							</table>
						</td>
						<td style="font-size:small;text-align:left;width:100%;">&nbsp;</td>';
					$showHTML.='</tr>';
					
					$showHTML.='<tr>';
						$showHTML.='<td style="text-align:left;">';
					if ($oJahresplan->Wartungsberechtigt)			
					{
						$cTmpJavaWartung="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanDetailVERANSTALTUNG."&amp;veranstaltung_id=0'";
						$cTmpJavaWartung="show_layer('".constPopUpName."');callAjax(".$cTmpJavaWartung.",'".constPopUpName."');" ;	
						$cTmpScriptWartung=" onclick=\"".$cTmpJavaWartung."\"";
						unset($cTmpJavaWartung);	
						$showHTML.='<span '.$cTmpScriptWartung.'><img title="Neuanlage" src="../../../skin/images/edit.png" alt="Wartung Veranstaltung" border="0" /></span>';
		
					}
					if (isset($cTmpJavaWartung)) unset($cTmpJavaWartung);	
				
						$showHTML.='</td>';
					$showHTML.='</tr>';
					
					$showHTML.='<tr><td colspan="2">
						<div style="position: absolute;z-index:50; padding: 1px 1px 1px 1px;display:none;border:2px outset #CCCCCC;background-color:#F5F5F5;">
							<div style="cursor: pointer;text-align:right;border:1px solid #CCCCCC;background-color:#CCCCCC;"></div>
						</div>
					</td></tr>';				
				}
				
				
			  	for ($iTmpVeranstaltung=0;$iTmpVeranstaltung<count($iTmpStartTagErgebniss);$iTmpVeranstaltung++)
				{
					$cTmpJavaWartung=' onclick="show_layer(\'kal'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');"  ondblclick="hide_layer(\'kal'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ';
					$showHTML.='<tr '.$cTmpJavaWartung.' style="vertical-align:top; font-size:small;background-color:#'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';">';
						$showHTML.='<td style="vertical-align:top;" rowspan="2" title="Veranstaltung ID '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].' '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['bezeichnung'].', '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'].'">
										<table  summary="blank'.$iTmpMonat.$iTmpWoche.$iTmpTag.'" style="border:0px;vertical-align:top;text-align:left;" cellpadding="0" cellspacing="0">
											<tr style="vertical-align:top;"><td>'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['bild_image'].'</td></tr>
										</table>
									</td>
									<td style="font-size:small;text-align:left;width:100%;" title="Veranstaltung ID '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id']." ".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'].'">'.(strlen($iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'])>11?substr($iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'],0,11)."..." :$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel']).'</td>';
					$showHTML.='</tr>';
				
					$showHTML.='<tr id="kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'" style="font-size:smaller;background-color:#'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';">';
						$showHTML.='<td style="text-align:right;">';

					if (isset($iTmpStartTagErgebniss[$iTmpVeranstaltung]['reservierung_id']) && !empty($iTmpStartTagErgebniss[$iTmpVeranstaltung]['reservierung_titel']) )			
					{
						$showHTML.='
							<img onclick="show_layer(\'DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ondblclick="hide_layer(\'DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" title="Reservierung"  height="14px"  src="../../../skin/images/image_legend0.gif" alt="Detailansicht der Reservierung" border="0" />
						';
					}
					$showHTML.='
							<img title="Detailansicht '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'].'"  height="14px" '.$cTmpJavaWartung.'  src="../../../skin/images/icon_voransicht.gif" alt="Detailansicht der Veranstaltung" border="0" />
							<img title="Druckansicht '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'].'"  height="14px" onclick="callWindows(\'\',\'Veranstaltungsdetail\',\'kal'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\',true);" src="../../../skin/images/printbutton.gif" alt="Druckansicht der Veranstaltung" border="0" />
							';
							
					if ($oJahresplan->Wartungsberechtigt)			
					{
						$showHTML.='<hr />';
						$cTmpJavaWartung="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanDetailVERANSTALTUNG."&amp;veranstaltung_id=".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id']."'";
						$cTmpJavaWartung="show_layer('".constPopUpName."');callAjax(".$cTmpJavaWartung.",'".constPopUpName."');" ;	
						$cTmpScriptWartung=" onclick=\"".$cTmpJavaWartung."\"";
	
						$showHTML.=' <img title="pflege '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'].'" height="14px" '.$cTmpScriptWartung.' src="../../../skin/images/edit.png" alt="pflege Veranstaltung" border="0" />';

						$cTmpJavaWartung="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanDeleteVERANSTALTUNG."&amp;veranstaltung_id=".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id']."'";
						$cTmpJavaWartung="hide_layer('kalinfo".$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung."');callAjax(".$cTmpJavaWartung.",'".constPopUpName."');" ;	
						
						$cTmpScriptWartung=" onclick=\"if (confirm('L&ouml;schen ID ".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id']." ".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel']."?')) {".$cTmpJavaWartung."}\"  ";
							
						$showHTML.=' <img title="enfernen '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['titel'].'" height="14px" '.$cTmpScriptWartung.' src="../../../skin/images/edit_trash.png" alt="entfernen Veranstaltung" border="0" />';
						
						if (empty($iTmpStartTagErgebniss[$iTmpVeranstaltung]['freigabeamum']))
							$showHTML.='<img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="keine Freigabe" border="0" />';

						if (stristr($iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltungskategorie_kurzbz'],'*'))
							$showHTML.='<img title="Anzeige nur fuer Mitarbeiter" height="14px" src="../../../skin/images/personen_liste.gif" alt="Anzeige nur fuer Mitarbeiter" border="0" />';
					}

					if (isset($cTmpJavaWartung)) unset($cTmpJavaWartung);	
				
						$showHTML.='</td>';
					$showHTML.='</tr>';
				
					if (isset($iTmpStartTagErgebniss[$iTmpVeranstaltung]['reservierung_id']) && !empty($iTmpStartTagErgebniss[$iTmpVeranstaltung]['reservierung_titel']) )			
					{
					$showHTML.='<tr><td colspan="2">
						<div style="position: absolute;z-index:13; padding: 10px 10px 10px 10px;display:none;border:2px outset #CCCCCC;background-color:#F5F5F5;"  id="DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'">
							<div onclick="hide_layer(\'DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" style="cursor: pointer;text-align:right;border:1px solid #CCCCCC;background-color:#CCCCCC;">schliessen<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="schliessen_DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'" />[x]</div>
								'.jahresplan_funk_show_reservierung_detail($iTmpStartTagErgebniss[$iTmpVeranstaltung]['reservierung'],$oJahresplan).'
							</div>
					</td></tr>';
					}						
					
					$showHTML.='<tr><td colspan="2">
						<div style="position: absolute;z-index:50; padding: 10px 10px 10px 10px;display:none;border:2px outset #CCCCCC;background-color:#F5F5F5;" id="kal'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'">
							<div onclick="hide_layer(\'kal'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" style="cursor: pointer;text-align:right;border:1px solid #CCCCCC;background-color:#CCCCCC;">schliessen<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="schliessen_DetailReservierung'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'2" />[x]</div>
						';
						$showHTML.=jahresplan_funk_show_veranstaltung_detail($iTmpStartTagErgebniss[$iTmpVeranstaltung],$oJahresplan,"$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung");
					$showHTML.='</div>

					</td></tr>';
				}			
				
				$showHTML.='</table>';
				$showHTML.='</td>';
		
				if (isset($iTmpStartTagErgebniss))
					unset($iTmpStartTagErgebniss);
								
			}

			$showHTML.='</tr></table></td>';
		$showHTML.='</tr>';
		}		
// TagesContainer Ende
		$showHTML.='
			</table>
		</td></tr>';
	}
	if (isset($veranstaltung)) unset($veranstaltung);

	$showHTML.='<tr><td style="border:1px inset silver;">Legende :
					Detail <img title="Detailansicht" height="14px"  src="../../../skin/images/icon_voransicht.gif" alt="Legende Detailansicht der Veranstaltung" border="0" />
					, Druck <img title="Druckansicht"  height="14px"  src="../../../skin/images/printbutton.gif" alt="Legende Druckansicht der Veranstaltung" border="0" />
					, Reservierung <img title="Reservierungsdetail"  height="14px" src="../../../skin/images/image_legend0.gif" alt="Druckansicht der Veranstaltung" border="0" />					
					';
					if ($oJahresplan->Wartungsberechtigt)			
					{
						$showHTML.=', Datenwartung (Neuanlage, &Auml;nderung) <img title="Wartung" height="14px" src="../../../skin/images/edit.png" alt="Legende Wartung Veranstaltung" border="0" />';
						$showHTML.=', Entfernen <img title="Enfernen" height="14px" src="../../../skin/images/edit_trash.png" alt="Legende Entfernen Veranstaltung" border="0" />';

						$showHTML.=', keine Freigabe <img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="Legende keine Freigabe" border="0" />';
						$showHTML.=', nur Mitarbeiter <img title="Anzeige nur fuer Mitarbeiter" height="14px" src="../../../skin/images/personen_liste.gif" alt="Legende Anzeige nur fuer Mitarbeiter" border="0" />';
					}
	$showHTML.='<br /><b>Bei Fragen geben Sie bitte immer die Veranstaltungs ID an.</b>';	
	$showHTML.='</td></tr>';
	$showHTML.='</table>';
return $showHTML;
}
?>
