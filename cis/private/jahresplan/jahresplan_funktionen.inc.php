<?php
//-------------------------------------------------------------------------------------------------
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */

 // Globale Einstellungen
	setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
// ---------------- Konstante
	if (!defined('constEingabeFehlt')) define('constEingabeFehlt','Eingabe !' );	
	if (!defined('constZeitDatumJJJJMMTT')) define('constZeitDatumJJJJMMTT','%Y%m%d' );	

#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_detailanzeige anzeige einer Veranstaltungen in Detailform 
*
* @param $conn Aktuelle Datenbankverbindung
* @param $veranstaltung Veranstaltung 
* @param $wartungsberechtigt Anzeige fuer Admin und Wartungsberechtigte 
*
* @return HTML Detailansicht der Veranstaltungen
*
*/
function jahresplan_veranstaltung_detailanzeige($conn,$veranstaltung,$wartungsberechtigt=false)
{
	if (!defined('constZeitKalenderPopUp')) define('constZeitKalenderPopUp','%a, %d.%m.%Y' );	  
	if (!defined('constZeitKalenderPopUp_zeit')) define('constZeitKalenderPopUp_zeit','%H:%M' );	  

	if (!is_array($veranstaltung))
	{
		return 'keine Veranstaltung ';
	}

	// Pruefen ob die Detail-Array in einer weiteren Array liegt (Verarbeitet wird ein Flaches Array mit Veranstaltungen)	
	if (is_array($veranstaltung[0]) && isset($veranstaltung[0]["veranstaltung_id"]))
	{
		$veranstaltung=$veranstaltung[0];
	}
	// Plausib Veranstaltungsdaten vorhanden
	if (!is_array($veranstaltung))
	{
		return 'keine Veranstaltung ';
	}
	
	// Veranstaltung in Verarbeitungstabelle uebertragen
	$veranstaltung_detail=jahresplan_funk_veranstaltung_extend($veranstaltung);

	// Initialisieren HTML Code Ausgabe
	$showHTML='<div id="news">'; 
	// Start Detailanzeige
	$showHTML.='<table class="news" cellpadding="6" cellspacing="1" title="Veranstaltungsdetail ID '.$veranstaltung_detail["veranstaltung_id"].'">';
		
		// Kategorie
		$showHTML.='<tr style="background-color:#'.$veranstaltung_detail['farbe'].';"><th>&nbsp;'.$veranstaltung_detail['bild_image'].'&nbsp;'.$veranstaltung_detail['bezeichnung'].'&nbsp;</th></tr>';
		$showHTML.='<tr><td><b>'.nl2br($veranstaltung_detail['beschreibung']).'</b></td></tr>';
		// Veranstaltungstermin - Block			
		// Anzeige Veranstaltungsdatum - Unterschiedlich wenn Start und Ende Datum gleich sind
			$showHTML.='<tr><td><table border="0" cellpadding="0" cellspacing="0">';	
				if (strftime(constZeitDatumJJJJMMTT,$veranstaltung_detail["start_timestamp"])==strftime(constZeitDatumJJJJMMTT,$veranstaltung_detail["ende_timestamp"]))
				{
					$showHTML.='<tr><td>Uhrzeit:&nbsp;'.strftime (constZeitKalenderPopUp_zeit,$veranstaltung_detail['start_timestamp']).'&nbsp;-&nbsp;'.strftime(constZeitKalenderPopUp_zeit,$veranstaltung_detail['ende_timestamp']).'&nbsp;Uhr</td></tr>';
					$showHTML.='<tr><td>Datum:&nbsp;'.strftime(constZeitKalenderPopUp,$veranstaltung_detail['start_timestamp']).'</td></tr>';
				}
				else	//  Ende Datum und Zeit 
				{
					$showHTML.='<tr><td><table>';			
					$showHTML.='
						<tr><td>Uhrzeit:&nbsp;'.strftime (constZeitKalenderPopUp_zeit,$veranstaltung_detail['start_timestamp']).'&nbsp;Uhr</td><td>&nbsp;-&nbsp;</td><td>'. strftime (constZeitKalenderPopUp_zeit,$veranstaltung_detail['ende_timestamp']).'&nbsp;Uhr</td></tr>';
					$showHTML.='
						<tr><td>Datum:&nbsp;'.strftime(constZeitKalenderPopUp,$veranstaltung_detail['start_timestamp']).'</td><td>&nbsp;-&nbsp;</td><td>'. strftime (constZeitKalenderPopUp,$veranstaltung_detail['ende_timestamp']).'</td></tr>';
					$showHTML.='</table></td></tr>';			
				}						
			$showHTML.='</table></td></tr>';


		// Veranstaltungs Inhalt und Beschreibung
			$showHTML.='<tr><td>'.(!empty($veranstaltung_detail['inhalt'])?'<b>Details</b><br>':'').nl2br($veranstaltung_detail['inhalt']).'</td></tr>';
			$showHTML.='<tr><td>&nbsp;</td></tr>';		
		// Reservierung 
			$Jahresplan = new jahresplan($conn);
			$Jahresplan->InitReservierung();	
			$Jahresplan->loadReservierung('',$veranstaltung_detail["veranstaltung_id"]);
			if ($res=$Jahresplan->getReservierung())
			{

				$showHTML.='<tr><td style="border:2px solid #CCC;" ><table>';
			
				$showHTML.='<tr>';
					$showHTML.='<td><h3>Raumreservierungen:</h3></td>';
				$showHTML.='</tr>';
			
				$showHTML.='<tr><td><table>';
				reset($res);
				$checkReservierung=null;
				for ($iTmpZehler=0;$iTmpZehler<count($res);$iTmpZehler++)
				{			
					$readReservierung=(isset($res[$iTmpZehler]['ort_kurzbz'])?$res[$iTmpZehler]['ort_kurzbz']:$res[$iTmpZehler]['reservierung_ort_kurzbz']).(isset($res[$iTmpZehler]['titel'])?$res[$iTmpZehler]['titel']:$res[$iTmpZehler]['reservierung_titel']).(isset($res[$iTmpZehler]['beschreibung'])?$res[$iTmpZehler]['beschreibung']:$res[$iTmpZehler]['reservierung_beschreibung']);
					if ($checkReservierung!=$readReservierung)
					{
						$checkReservierung=$readReservierung;

						// nach einer Reservierung eine Leerzeile einfuegen zur besseren Trennung
						$showHTML.=($iTmpZehler!=0?'<tr><td>&nbsp;</td></tr>':'');
						$unicode=null;
						$userNAME=$res[$iTmpZehler]["uid"];;
						$pers = new benutzer($conn,$userNAME,$unicode); // Lesen Person - Benutzerdaten
						if (isset($pers->nachname))
						{
							$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
							$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
							$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
							$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
							if ($pers->foto)
							{
								$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
								$res[$iTmpZehler]["bild"]='<img width="16" border="0" title="'.$userNAME.'" alt="Reservierung von Benutzer" src="'.$cURL.'" >';
							}
						}
				
						$showHTML.='<tr>';
							$showHTML.='<td>Titel:</td><td>'.(isset($res[$iTmpZehler]['titel'])?$res[$iTmpZehler]['titel']:$res[$iTmpZehler]['reservierung_titel']).'</td>';
						$showHTML.='</tr>';
			
						$showHTML.='<tr>';
							$showHTML.='<td>Ort:</td><td>'.(isset($res[$iTmpZehler]['ort_kurzbz'])?$res[$iTmpZehler]['ort_kurzbz']:$res[$iTmpZehler]['reservierung_ort_kurzbz']).'</td>';
						$showHTML.='</tr>';

						// Suchen zu dieser Reservierung den letzten Eintrag						
						$lastReservierung=jahresplan_veranstaltung_zusammenfassen($res,$iTmpZehler,$checkReservierung);
						$showHTML.='<tr>';
							$showHTML.='<td>Datum/Uhrzeit:</td><td>'.(isset($res[$iTmpZehler]['datum_anzeige'])?$res[$iTmpZehler]['datum_anzeige']:$res[$iTmpZehler]['res_datum_anzeige']);
							if (isset($res[$iTmpZehler]['beginn']))	
								$showHTML.=' / '.$res[$iTmpZehler]['beginn_anzeige'].' - '. (isset($lastReservierung['ende_anzeige'])?$lastReservierung['ende_anzeige']:$res[$iTmpZehler]['ende_anzeige']);
							$showHTML.='</td>';
						$showHTML.='</tr>';
			
						$showHTML.='<tr>';
							$showHTML.='<td>Anlage:</td><td>'.$userNAME.'</td><td valign="top" rowspan="2">'.(isset($res[$iTmpZehler]["bild"])?$res[$iTmpZehler]["bild"]:'').'</td>';
						$showHTML.='</tr>';
	
						$showHTML.='<tr>';
							$showHTML.='<td>Beschreibung:</td><td>'.(isset($res[$iTmpZehler]['beschreibung'])?$res[$iTmpZehler]['beschreibung']:$res[$iTmpZehler]['reservierung_beschreibung']).'</td>';
						$showHTML.='</tr>';
						}
				}			
				$showHTML.='</table></td></tr></table></td></tr>';
			}
			elseif ($Jahresplan->getError())
			{
				$showHTML.='<tr><td>'.$Jahresplan->getError().'</td></tr>';
			}
			$showHTML.='<tr><td><span class="footer_zeile">Bei Fragen geben Sie bitte immer die Veranstaltungs ID '.$veranstaltung_detail["veranstaltung_id"].' an.</span></td></tr>';
	$showHTML.='</table>';
	$showHTML.='</div>';

	// Admin Info AenderungsAnwender
	if (!$wartungsberechtigt)
	{	
		return $showHTML;
	}	
	$showHTML.=jahresplan_veranstaltung_detail_user($conn,$veranstaltung,$wartungsberechtigt);
	return $showHTML;
}	
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_zusammenfassen suchen die letzte Veranstaltungen zu einer Reservierung 
*
* @param $res Tabelle der Reservierungen
* @param $iTmpZehler Startindex in der Tabelle 
*
* @return HTML Detailansicht der Veranstaltungen
*
*/
function jahresplan_veranstaltung_zusammenfassen($res,$iZehler)
{
		reset($res);
		$checkReservierung=(isset($res[$iZehler]['ort_kurzbz'])?$res[$iZehler]['ort_kurzbz']:$res[$iZehler]['reservierung_ort_kurzbz']).(isset($res[$iZehler]['titel'])?$res[$iZehler]['titel']:$res[$iZehler]['reservierung_titel']).(isset($res[$iZehler]['beschreibung'])?$res[$iZehler]['beschreibung']:$res[$iZehler]['reservierung_beschreibung']);
		$gefReservierung=$res[$iZehler];
		for ($iTmpZehler=$iZehler;$iTmpZehler<count($res);$iTmpZehler++)
		{			
			$readReservierung=(isset($res[$iTmpZehler]['ort_kurzbz'])?$res[$iTmpZehler]['ort_kurzbz']:$res[$iTmpZehler]['reservierung_ort_kurzbz']).(isset($res[$iTmpZehler]['titel'])?$res[$iTmpZehler]['titel']:$res[$iTmpZehler]['reservierung_titel']).(isset($res[$iTmpZehler]['beschreibung'])?$res[$iTmpZehler]['beschreibung']:$res[$iTmpZehler]['reservierung_beschreibung']);
			if ($checkReservierung==$readReservierung)
			{
				$checkReservierung=$readReservierung;
				$gefReservierung=$res[$iTmpZehler];
			}
			else
			{
				$iTmpZehler=9999999;
			}
		}		
		return $gefReservierung;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltungskategorie_kalenderanzeige anzeigen Termin Kalender 
*
* @param $conn Aktuelle Datenbankverbindung
* @param $veranstaltung Veranstalltungstabelle mit allen Daten zur Selektion 
* @param $wartungsberechtigt Aktueller Anwender darf Daten warten
* @param $Jahr Selektions Jahr
* @param $Monat Selektions Monat
*
* @return HTML Kalender
*
*/
function jahresplan_veranstaltungskategorie_kalenderanzeige($conn,$veranstaltung,$wartungsberechtigt,$Jahr,$Monat)
{
	// Kalender	
	if (!defined('constKalenderDatumHead')) define('constKalenderDatumHead','%B  %Y' );	
	if (!defined('constKalenderDatumLang')) define('constKalenderDatumLang','%a, %d %B %G' );	  
	if (!defined('constKalenderDatum')) define('constKalenderDatum','%d ' );	  
	if (!defined('constKalenderDetailDatumZeit')) define('constKalenderDetailDatumZeit','%a, %d.%m.%Y  %R' );	  
	if (!defined('constKalenderZeit')) define('constKalenderZeit','%H:%M' );	  
	
	// Plausib
	$veranstaltung_kalender=array();
	if (is_array($veranstaltung))
	{
		reset($veranstaltung);
		// Daten in Work Array uebertragen 
		$veranstaltung_tabelle=$veranstaltung;
  		for ($iTmpZehler=0;$iTmpZehler<count($veranstaltung);$iTmpZehler++)
		{
			//  Moderator,Bild-Icon ermitteln und Leerzeichen aus Textfelder entfernen
			$veranstaltung_tabelle[$iTmpZehler]=jahresplan_funk_veranstaltung_extend($veranstaltung_tabelle[$iTmpZehler]);
		}
		reset($veranstaltung_tabelle);
		$veranstaltung_kalender=jahresplan_veranstaltungskategorie_kalendererzeugen($veranstaltung_tabelle,$Jahr,$Monat);
	}
	// Kalender - Startdatum initialisieren
	$iTmpMinMonate=$veranstaltung_kalender[$Jahr]["VerarbeitenMonate"][0];
	$iTmpMinTage=1;

	$iTmpMaxMonate=$veranstaltung_kalender[$Jahr]["VerarbeitenMonate"][count($veranstaltung_kalender[$Jahr]["VerarbeitenMonate"])-1];
	$iTmpMaxTage=strftime("%d",mktime(0, 0, 0,($iTmpMaxMonate + 1), 0, $Jahr));

	// Kalenderanzeige Erzeugen 
	$showHTML='';
	$showHTML.='<table class="tabcontent">';
	for ($iTmpMonat=$iTmpMinMonate;$iTmpMonat<=$iTmpMaxMonate;$iTmpMonat++)
	{
		// Je Monat begin der Woche und Ende KW ermitteln
		$nowMonat=(int)date("m", mktime(0,0,0,date("m"),date("d"),date("y")));
#echo Test($veranstaltung_kalender[$Jahr]);		
		$iTmpMinKW=(isset($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])?$veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat][0]:1);
		$iTmpMaxKW=(isset($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])?$veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat][count($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])-1]:1);
		
		// Monatsheader	- Ausgabeinformationen aufbereiten
		if (isset($veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat]))
		{
			$iTmpAnzahlDaten=count($veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat])." Veranstaltungen";
		}	
		else
		{
			$iTmpAnzahlDaten='keine Veranstaltungen';
			$veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat]=array();
		}	
		// Monatsheader - Name
		$cTmpInfoMonatHeaderzeile=strftime (constKalenderDatumHead, mktime(0, 0, 0, $iTmpMonat, 1, $Jahr));
		$cTmpInfoMonat=strftime ('%Y%m', mktime(0, 0, 0, $iTmpMonat, 1, $Jahr));

		// Monatsheader - ein, ausblenden der Monatsansicht
		if (!empty($Monat) 
		|| count($veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat])>0)
		{
			$cTmpStyleTableOn="<span class=\"cursor_hand\" title=\"ausblenden ".$iTmpMonat."\"  onclick=\"show_layer('anzahlMonat".$iTmpMonat."');hide_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOn".$iTmpMonat."');show_layer('showMonatOff".$iTmpMonat."');\" id=\"showMonatOn".$iTmpMonat."\"><span ><img title='schliessen - close' src='../../../skin/images/bullet_arrow_down.png' alt='close' border='0'></span>&nbsp;</span>";
			$cTmpStyleTableOff="<span class=\"cursor_hand\" title=\"anzeigen ".$iTmpMonat."\"  onclick=\"hide_layer('anzahlMonat".$iTmpMonat."');show_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOff".$iTmpMonat."');show_layer('showMonatOn".$iTmpMonat."');\" style=\"display:none;\" id=\"showMonatOff".$iTmpMonat."\"><span ><img title='&ouml;ffnen - open' src='../../../skin/images/bullet_arrow_right.png' alt='open' border='0'></span>&nbsp;</span>";

			$showHTML.='<tr><td><h2>&nbsp;'.$cTmpStyleTableOn.$cTmpStyleTableOff.$cTmpInfoMonatHeaderzeile.'&nbsp;</h2></td></tr>';
			$showHTML.='<tr><td class="ausblenden" id="anzahlMonat'.$iTmpMonat.'">'.$iTmpAnzahlDaten.'</td></tr>';
		}	
		else
		{
			$cTmpStyleTableOn="<span class=\"cursor_hand\" title=\" ausblenden ".$iTmpMonat."\"  onclick=\"show_layer('anzahlMonat".$iTmpMonat."');hide_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOn".$iTmpMonat."');show_layer('showMonatOff".$iTmpMonat."');\" id=\"showMonatOn".$iTmpMonat."\" style=\"display:none;\"><span ><img title='schliessen - close' src='../../../skin/images/bullet_arrow_down.png' alt='close' border='0'></span>&nbsp;</span>";
			$cTmpStyleTableOff="<span class=\"cursor_hand\" title=\"anzeigen ".$iTmpMonat."\"  onclick=\"hide_layer('anzahlMonat".$iTmpMonat."');show_layer('showMonat".$iTmpMonat."');hide_layer('showMonatOff".$iTmpMonat."');show_layer('showMonatOn".$iTmpMonat."');\" id=\"showMonatOff".$iTmpMonat."\" ><span ><img title='&ouml;ffnen - open' src='../../../skin/images/bullet_arrow_right.png' alt='open' border='0'>&nbsp;</span></span>";

			$showHTML.='<tr><td><h2>&nbsp;'.$cTmpStyleTableOn.$cTmpStyleTableOff.$cTmpInfoMonatHeaderzeile.'&nbsp;</h2></td></tr>';
			$showHTML.='<tr><td id="anzahlMonat'.$iTmpMonat.'">'.$iTmpAnzahlDaten.'</td></tr>';
		}
		
		// Monatsanzeige - nicht aktuelle werden ausgeblendet
		if (!empty($Monat) || count($veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat])>0)
		{
			$showHTML.='<tr><td id="showMonat'.$iTmpMonat.'">';
		}	
		else
		{
			$showHTML.='<tr><td style="display:none;" id="showMonat'.$iTmpMonat.'">';
		}	

		$showHTML.='<table class="kalender_kpl_monat" cellpadding="0" cellspacing="0">';
		
		$showHTML.='<tr>
				<th>KW</th>
				<th>Montag</th>
				<th>Dienstag</th>
				<th>Mittwoch</th>
				<th>Donnerstag</th>
				<th>Freitag</th>
				<th>Samstag</th>
				<th>Sonntag</th>
			</tr>';
			
		// Wochenanzeige
		$alleKWanzeigen="";
		$alleKWausblenden="";
		
		$iTmpMinKW=(isset($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])?$veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat][0]:1);
		$iTmpMaxKW=(isset($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])?$veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat][count($veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat])-1]:1);
	  	for ($iTmpWoche=$iTmpMinKW;$iTmpWoche<=$iTmpMaxKW;$iTmpWoche++)
		{			
			$nowWeek=(int)date("W", mktime(0,0,0,date("m"),date("d"),date("y")));	

			// Fuer alle WochenTage das Script Anzeige,Verstecken erzeugen
			$cTmpStyleTableOn="";
			$cTmpStyleTableOff="";			
			for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{	
				$cTmpStyleTableOn.="show_layer('week_".$iTmpMonat.$iTmpWoche.$iTmpTag."');";
				$cTmpStyleTableOff.="hide_layer('week_".$iTmpMonat.$iTmpWoche.$iTmpTag."');";
			}
			$alleKWanzeigen.=$cTmpStyleTableOn;
			$alleKWausblenden.=$cTmpStyleTableOff;
			
		// Wochenzeile Start
			$showHTML.='<tr>';
		
				// Die KW hat keine Daten - Initialisieren mit Array
				if (!isset($veranstaltung_kalender[$Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche]))
				{
					$veranstaltung_kalender[$Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche]=array();
				}	

		// Wochenanzeigen ON OFF
				$iTmpAnzahlDaten='';
				
			$cTmpStyleTableOn2='hide_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');';
			$cTmpStyleTableOff2='hide_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');';
			
			$alleKWanzeigen.=$cTmpStyleTableOn2.$cTmpStyleTableOn;
			$alleKWausblenden.=$cTmpStyleTableOff2.$cTmpStyleTableOff;			
				
				$showHTML.='<td class="kalender_woche_on_of_container">
						<table cellpadding="0" cellspacing="0"><tr>';
						if (count($veranstaltung_kalender[$Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche])>0 
						&& ( empty($Monat) || (!empty($Monat) && $iTmpMonat==$Monat && $iTmpMonat!=$nowMonat && $nowWeek!=$iTmpWoche) 
						  || ($iTmpMonat==$nowMonat && $nowWeek==$iTmpWoche) )) 
						{
							$showHTML.='<td class="kalender_woche_verbergen" title="Anzeigen '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="on_'.$iTmpMonat.$iTmpWoche.'" onclick="'.$cTmpStyleTableOn2.$cTmpStyleTableOn.'" >&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<img title="&ouml;ffnen - open" src="../../../skin/images/bullet_arrow_right.png" alt="open" border="0"></td>';
							$showHTML.='<td class="kalender_woche_anzeigen" title="Ausblenden '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="off_'.$iTmpMonat.$iTmpWoche.'" onclick="'.$cTmpStyleTableOff2.$cTmpStyleTableOff.'" >&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<img title="schliessen - close" src="../../../skin/images/bullet_arrow_down.png" alt="close" border="0"></td>';
						}
						else
						{
							$showHTML.='<td class="kalender_woche_anzeigen" title="Anzeigen '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="on_'.$iTmpMonat.$iTmpWoche.'" onclick="'.$cTmpStyleTableOn2.$cTmpStyleTableOn.'" >&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<img title="&ouml;ffnen - open" src="../../../skin/images/bullet_arrow_right.png" alt="open" border="0"></td>';
							$showHTML.='<td class="kalender_woche_verbergen" title="Ausblenden '.$cTmpInfoMonat.', Woche '.$iTmpWoche.' " id="off_'.$iTmpMonat.$iTmpWoche.'"  onclick="'.$cTmpStyleTableOff2.$cTmpStyleTableOff.'" >&nbsp;'.$iTmpAnzahlDaten.($iTmpWoche>52?1:$iTmpWoche).'<img title="schliessen - close" src="../../../skin/images/bullet_arrow_down.png" alt="close" border="0"></td>';
						}
				$showHTML.='</tr></table></td>';
		// Kalendertage			
		  	for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{
				if (!isset($veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag]))
				 	continue;	
				$iTmpTimeStamp=$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag];
				
				if (isset($veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTag'][$iTmpWoche][$iTmpTag]))
					$iTmpStartTagErgebniss=$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTag'][$iTmpWoche][$iTmpTag];
				else
					$iTmpStartTagErgebniss=array();
					
				$cTmpPruef1=date("Y",$iTmpTimeStamp);
				$cTmpPruef2=$Jahr;
				
				if (!empty($Monat))
				{
					$cTmpPruef1=date("Ym",$iTmpTimeStamp);
					$cTmpPruef2=$Jahr.(strlen($Monat)<2?'0'.$Monat:$Monat);
				}	
#				else if (!empty($oJahresplan->Woche))
#				{
#					$cTmpPruef1=date("YW",$iTmpTimeStamp);
#					$cTmpPruef2=$Jahr.(strlen($oJahresplan->Woche)<2?'0'.$oJahresplan->Woche:$oJahresplan->Woche);
#				}			
				else 
				{
					$cTmpPruef1=date("Ym",$iTmpTimeStamp);
					$cTmpPruef2=$Jahr.($iTmpMonat<10?"0".$iTmpMonat:$iTmpMonat);
				}		
		// Tage je Woche	
				$showHTML.='<td  onclick="'.$cTmpStyleTableOn2.$cTmpStyleTableOn.'" class="kalender_woche_tages_kpl_container">';
				$showHTML.='<table cellpadding="1" cellspacing="1" class="kalender_woche_tages_container">
					<tr class="kalender_woche_tages_container"><td class="kalender_woche_tages_container">';
				
				// Tagesdatum - Header				
				if ($cTmpPruef1!=$cTmpPruef2) // Nicht mehr im Aktuellen Monat
					$showHTML.='<div class="kalender_woche_tag_falscher_monat">';
				elseif (count($iTmpStartTagErgebniss)<1)
					$showHTML.='<div class="kalender_woche_tag_ohne_termin">';
				else
					$showHTML.='<div class="kalender_woche_tag_mit_termin" onclick="hide_layer(\'on_'.$iTmpMonat.$iTmpWoche.'\');show_layer(\'off_'.$iTmpMonat.$iTmpWoche.'\');'.$cTmpStyleTableOn.'">';
				$showHTML.='&nbsp;'.strftime (constKalenderDatum, $iTmpTimeStamp).'&nbsp;';
				$showHTML.='</div>';
			
				// Keine Veranstaltungensdaten je Tag
				if (count($veranstaltung_kalender[$Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche])>0 
				&& ( empty($Monat) || (!empty($Monat) && $iTmpMonat==$Monat && $iTmpMonat!=$nowMonat && $nowWeek!=$iTmpWoche) 
				  || ($iTmpMonat==$nowMonat && $nowWeek==$iTmpWoche) ) )
					$showHTML.='<div class="kalender_tages_container_on" id="week_'.$iTmpMonat.$iTmpWoche.$iTmpTag.'">';
				else
					$showHTML.='<div class="kalender_tages_container_off" id="week_'.$iTmpMonat.$iTmpWoche.$iTmpTag.'">';
				$showHTML.='<table  width="100%" cellpadding="0" cellspacing="1">';
					// Wartungsberechtigte bekommen einen Wartungsknopf zu jeden Tag 
					if ($wartungsberechtigt)			
					{
						$showHTML.='<tr>';
							$showHTML.='<td>';
								if ($wartungsberechtigt)			
								{
									$showHTML.='<span class="cursor_hand" onclick="callWindows(\'jahresplan_veranstaltung.php?work=neu&amp;veranstaltung_id=&amp;start_datum='.date("d.m.Y",$iTmpTimeStamp).'&amp;ende_datum='.date("d.m.Y",$iTmpTimeStamp).'\',\'Veranstaltung_Neuanlage\');"><img title="Neuanlage '.date("d.m.Y",$iTmpTimeStamp).'" src="../../../skin/images/date_edit.png" alt="Wartung Veranstaltung" border="0"></span>';
								}
							$showHTML.='</td>';
						$showHTML.='</tr>';
					}


					// Veranstaltungen je Tag
				  	for ($iTmpVeranstaltung=0;$iTmpVeranstaltung<count($iTmpStartTagErgebniss);$iTmpVeranstaltung++)
					{
						if ($wartungsberechtigt)			
						{					
							$cTmpJavaWartung=' onclick="callWindows(\'jahresplan_veranstaltung.php?veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Detail\');" onmouseout="hide_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ';
//							$cTmpJavaWartung=' onclick="callWindows(\'jahresplan_veranstaltung.php?veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Detail\');" onmouseout="hide_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" onmouseover="show_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ';
						}
						else
						{
							$cTmpJavaWartung=' onclick="callWindows(\'jahresplan_detail.php?veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Detail\');" onmouseout="hide_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ';
//							$cTmpJavaWartung=' onclick="callWindows(\'jahresplan_detail.php?veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Detail\');" onmouseout="hide_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" onmouseover="show_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" ';
						}
						// Rundung je Termin Start
						$showHTML.='
							<tr><td>
							<b class="rtop">
							  <b class="r1" style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r2"  style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r3" style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r4" style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b>
							</b>';
						// Termin Start		
						$showHTML.='<table class="kalender_tages_info" cellpadding="0" cellspacing="0" style="background-color:#'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';">';
						$showHTML.='<tr class="kalender_tages_info" '.$cTmpJavaWartung.'  title="Veranstaltung '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]["bezeichnung"]." ID ".$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id']." \n".htmlspecialchars($iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'])." \n".htmlspecialchars($iTmpStartTagErgebniss[$iTmpVeranstaltung]["inhalt"])." \n ".strftime(constKalenderDetailDatumZeit,$iTmpStartTagErgebniss[$iTmpVeranstaltung]["start_timestamp"])." Uhr \n - ". ($iTmpStartTagErgebniss[$iTmpVeranstaltung]["start_datum"]==$iTmpStartTagErgebniss[$iTmpVeranstaltung]["ende_datum"]?strftime(constKalenderZeit,$iTmpStartTagErgebniss[$iTmpVeranstaltung]["ende_timestamp"]) : strftime(constKalenderDetailDatumZeit,$iTmpStartTagErgebniss[$iTmpVeranstaltung]["ende_timestamp"]) ).' Uhr">';
							$showHTML.='<td class="kalender_tages_info">
									<table summary="blank'.$iTmpMonat.$iTmpWoche.$iTmpTag.'" style="border:0px;vertical-align:top;text-align:left;" cellpadding="0" cellspacing="0">
										<tr>
											<td>&nbsp;'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['bild_image'].'&nbsp;</td>
											<td>'.(strlen($iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'])>8?substr(trim($iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung']),0,8).'<span style="font-size:7px;">...</span>' :trim($iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'])).'</td>
										</tr>
									</table>
								</td>';
						$showHTML.='</tr>';
						// Termine Wartungsberechtigte Icons anzeigen					
						if ($wartungsberechtigt)			
						{
				// onmouseover			$showHTML.='<tr class="ausblenden" id="kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'" onmouseout="hide_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');" onmouseover="show_layer(\'kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'\');">';
							$showHTML.='<tr class="ausblenden" id="kalinfo'.$iTmpMonat.$iTmpWoche.$iTmpTag.$iTmpVeranstaltung.'">';
							$showHTML.='<td><table><tr><td>';

							$cTmpScriptWartung=' onclick="callWindows(\'jahresplan_veranstaltung.php?work=show&amp;veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Aenderung\');" ';
							$showHTML.='&nbsp;<img '.$cTmpScriptWartung.' class="cursor_hand" title="pflege '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'].'" height="14px" src="../../../skin/images/date_edit.png" alt="pflege Veranstaltung" border="0">';

							$cTmpScriptWartung=' onclick="if (!confirm(\'Wollen Sie wirklich ID '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].' l&ouml;schen ?\')) {return false;}  ; callWindows(\'jahresplan_veranstaltung.php?work=del&amp;veranstaltung_id='.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'\',\'Veranstaltung_Loeschen\');" ';
							$showHTML.='&nbsp;<img '.$cTmpScriptWartung.' class="cursor_hand" title="enfernen '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['beschreibung'].'" height="14px" src="../../../skin/images/date_delete.png" alt="l&ouml;schen Veranstaltung ID '.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltung_id'].'" border="0">';

							if (empty($iTmpStartTagErgebniss[$iTmpVeranstaltung]['freigabeamum']))
								$showHTML.='&nbsp;<img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="noch keine Freigabe" border="0">';
							if (substr($iTmpStartTagErgebniss[$iTmpVeranstaltung]['veranstaltungskategorie_kurzbz'],0,1)=='*')
								$showHTML.='&nbsp;<img title="Anzeige nur fuer Mitarbeiter - Hausintern" height="14px" src="../../../skin/images/eye.png" alt="Anzeige nur fuer Mitarbeiter" border="0">';
							$showHTML.='</td></tr></table></td>';
						$showHTML.='</tr>';
						} 
						// Rundung je Termin Ende
						$showHTML.='</table>
							<b class="rbottom">
							  <b class="r4"  style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r3" style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r2" style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b> <b class="r1"  style="background: #'.$iTmpStartTagErgebniss[$iTmpVeranstaltung]['farbe'].';"></b>
							</b>
						</td>
					</tr>';
					} // Termin Ende					
					
					
				// TagesContainer Ende
				$showHTML.='</table>';
				$showHTML.='</div>';
			$showHTML.='</td>';
		$showHTML.='</tr></table></td>';			
			} // 7 Tage Container Ende
		$showHTML.='</tr>';
		}		
		// WochenContainer Ende
#			$alleKWanzeigen.=$cTmpStyleTableOn;
#			$alleKWausblenden.=$cTmpStyleTableOff;

		if (!empty($Monat))
		{
			$showHTML.='
			<tr><td colspan="9"><table>
				<tr>
					<td id="alleKW_Monat_'.$cTmpInfoMonat.'_on" onclick="hide_layer(\'alleKW_Monat_'.$cTmpInfoMonat.'_on\');show_layer(\'alleKW_Monat_'.$cTmpInfoMonat.'_off\');'.$alleKWanzeigen.'">alle Wochen &ouml;ffnen&nbsp;<img title="&ouml;ffnen - open" src="../../../skin/images/bullet_arrow_right.png" alt="open" border="0"></td>
					<td id="alleKW_Monat_'.$cTmpInfoMonat.'_off" class="ausblenden" onclick="hide_layer(\'alleKW_Monat_'.$cTmpInfoMonat.'_off\');show_layer(\'alleKW_Monat_'.$cTmpInfoMonat.'_on\');'.$alleKWausblenden.'">alle Wochen schliessen&nbsp;<img title="schliessen - close" src="../../../skin/images/bullet_arrow_down.png" alt="close" border="0"></td>
				</tr>
			</table></td></tr>
			';
		}	
		$showHTML.='			
		</table></td></tr>';
	}
	$showHTML.='<tr><td style="color:silver;">';
		$showHTML.='Bei Fragen geben Sie bitte immer die Veranstaltungs ID an.';	
	$showHTML.='</td></tr>';
	$showHTML.='</table>';
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltungskategorie_kalendererzeugen zur Termin Selektion Jahr-Monat Tabelle aufbauen 
*
* @param $veranstaltung Veranstalltungstabelle mit allen Daten zur Selektion 
* @param $Jahr Selektions Jahr
* @param $Monat Selektions Monat
*
* @return HTML Kalender
*
*/
function jahresplan_veranstaltungskategorie_kalendererzeugen($veranstaltung_tabelle,$Jahr,$Monat)
{
	$veranstaltung_kalender=array();
	$iTmpMinMonate=(empty($Monat)?1:$Monat);
	$iTmpMaxMonate=(empty($Monat)?12:$Monat);
	for ($iTmpMonat=(empty($Monat)?1:$Monat);$iTmpMonat<=$iTmpMaxMonate;$iTmpMonat++)
	{
		$veranstaltung_kalender[$Jahr]['VerarbeitenMonate'][]=$iTmpMonat;

		$iTmpMinTage=1;
		$iTmpMaxTage=strftime("%d",mktime(0, 0, 0,( $iTmpMonat + 1 ), 0, $Jahr));

		$iTmpMinKW=(int)date("W",mktime(0, 0, 0,$iTmpMonat,1, $Jahr));
		$iTmpMaxKW=(int)date("W",mktime(0, 0, 0,$iTmpMonat,$iTmpMaxTage, $Jahr));
		if ($iTmpMaxKW<2 && $iTmpMonat==12)
			$iTmpMaxKW=53;
			
		// Fuer die Erste Woche das Montag-Datum ermitteln
		$iTmpTagNr=date('w',mktime(0, 0, 0, $iTmpMonat  ,1, $Jahr));	
		$iTmpInitDay=mktime(0, 0, 0, $iTmpMonat  ,1, $Jahr);
		if ($iTmpTagNr!=1)
		{
			$iTmpInitDay=mktime(0, 0, 0, $iTmpMonat  ,(1 - ($iTmpTagNr==0?7:$iTmpTagNr) )+1, $Jahr);		
		}
		// KalenderInit	
	  	for ($iTmpWoche=$iTmpMinKW;$iTmpWoche<=$iTmpMaxKW;$iTmpWoche++)
		{	
			$veranstaltung_kalender[$Jahr]['VerarbeitenWochen'][$iTmpMonat][]=$iTmpWoche;

			for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{	
				$iTmpZwTag=(int)date('d',$iTmpInitDay);
				$iTmpZwMonat=(int)date('m',$iTmpInitDay);
				$iTmpZwWoche=(int)date('W',$iTmpInitDay);
				$iTmpZwWochentag=(int)date('w',$iTmpInitDay);
				$iTmpZwWochentagname=date('D',$iTmpInitDay);
				$iTmpZw_jjjjmmtt=date('Ymd',$iTmpInitDay);

				$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag]=$iTmpInitDay;
				$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTimestampDatum'][$iTmpWoche][$iTmpTag]=$iTmpZw_jjjjmmtt;
				$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTag'][$iTmpWoche][$iTmpZwWochentag]=array();
				
				$iTmpInitDay=mktime(0, 0, 0, $iTmpZwMonat ,$iTmpZwTag +1, date('Y',$iTmpInitDay) );	
			}	
		}

		$veranstaltung_next=$veranstaltung_tabelle;
	  	for ($iTmpWoche=$iTmpMinKW;$iTmpWoche<=$iTmpMaxKW;$iTmpWoche++)
		{	
			// Keine weitere Vearbeitungen mehr noetig
			if (!is_array($veranstaltung_next) || count($veranstaltung_next)<1)
				continue;

			for ($iTmpTag=0;$iTmpTag<7;$iTmpTag++)		
			{	
				$iTmpInitDay=$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTimestamp'][$iTmpWoche][$iTmpTag];

				$iTmpZwWochentag=(int)date('w',$iTmpInitDay);
				$iTmpZwWoche=(int)date('W',$iTmpInitDay);
				$iTmpZwMonat=(int)date('m',$iTmpInitDay);
				$iTmpZw_jjjjmmtt=date('Ymd',$iTmpInitDay);
				
				// Keine weitere Vearbeitungen mehr noetig
				if (!is_array($veranstaltung_next) || count($veranstaltung_next)<1)
					continue;
					
				// Daten zur Verarbeitung bereitstellen 
				// es werden nur mehr die Tage die noch nicht verarbeitet sind im next gemerkt
				$veranstaltung=$veranstaltung_next;
				$veranstaltung_next=array();
				reset($veranstaltung);

			  	for ($iTmpVeranstaltung=0;$iTmpVeranstaltung<count($veranstaltung);$iTmpVeranstaltung++)
				{
					if ( ($Jahr>=$veranstaltung[$iTmpVeranstaltung]['start_jahr'] && $Jahr<=$veranstaltung[$iTmpVeranstaltung]['ende_jahr'])
					&& ($iTmpWoche>$veranstaltung[$iTmpVeranstaltung]['ende_woche']) )
						continue;

					// Datum ist OK fuer weitere verarbeitung
					$veranstaltung_next[]=$veranstaltung[$iTmpVeranstaltung];
					
					if ( ($Jahr>=$veranstaltung[$iTmpVeranstaltung]['start_jahr'] 
					&& $Jahr<=$veranstaltung[$iTmpVeranstaltung]['ende_jahr'])
					&& ($iTmpWoche>=$veranstaltung[$iTmpVeranstaltung]['start_woche'] 
					&& $iTmpWoche<=$veranstaltung[$iTmpVeranstaltung]['ende_woche']) )
					{
						// Veranstaltung passt nicht mit Start - Ende in diesen Tag
						if ($iTmpZw_jjjjmmtt<$veranstaltung[$iTmpVeranstaltung]['start_jjjjmmtt']
						|| $iTmpZw_jjjjmmtt>$veranstaltung[$iTmpVeranstaltung]['ende_jjjjmmtt'])
							continue;
						$veranstaltung_kalender[$Jahr]['Monat'][$iTmpMonat][$veranstaltung[$iTmpVeranstaltung]['veranstaltung_id']]=$veranstaltung[$iTmpVeranstaltung]['veranstaltung_id'];
						$veranstaltung_kalender[$Jahr][$iTmpMonat]['Woche'][$iTmpMonat][$iTmpWoche][$veranstaltung[$iTmpVeranstaltung]['veranstaltung_id']]=$veranstaltung[$iTmpVeranstaltung]['veranstaltung_id'];
						$veranstaltung_kalender[$Jahr][$iTmpMonat]['WochenTag'][$iTmpZwWoche][$iTmpTag][]=$veranstaltung[$iTmpVeranstaltung];
					}	
				} // Ende For Veranstaltung
			} // Ende For Woche
		}
	}	
	return $veranstaltung_kalender;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_listenanzeige anzeigen Veranstaltungen in Listenform 
*
* @param $conn Aktuelle Datenbankverbindung
* @param $veranstaltung Veranstaltungstabelle
* @param $wartungsberechtigt Aktueller Anwender darf Daten warten
*
* @return HTML Liste der Ergebnisse der Veranstaltungen
*
*/
function jahresplan_veranstaltung_listenanzeige($conn,$veranstaltung,$wartungsberechtigt)
{
	// Listen 
	if (!defined('constHeaderVeranstaltungsdatum')) define('constHeaderVeranstaltungsdatum','%a, %d %B %G' );	
	if (!defined('constZeileVeranstaltungsdatum')) define('constZeileVeranstaltungsdatum','%a, %d.%m.%Y  %R %H:%M' );	
	if (!defined('constZeileVeranstaltungszeit')) define('constZeileVeranstaltungszeit','%H:%M' );	  

	// Pruefen ob Daten vorhanden sind zum anzeigen
	if (!is_array($veranstaltung) || count($veranstaltung)<1 || !isset($veranstaltung[0]) || !isset($veranstaltung[0]['veranstaltung_id']) || empty($veranstaltung[0]['veranstaltung_id']) )
	{
		return 'keine Veranstaltung ';
	}

	// Daten in Work Array uebertragen 
	$veranstaltung_tabelle=$veranstaltung;

	//  Moderator,Bild-Icon ermitteln und Leerzeichen aus Textfelder entfernen
	reset($veranstaltung_tabelle);
  	for ($iTmpZehler=0;$iTmpZehler<count($veranstaltung_tabelle);$iTmpZehler++)
	{
		$veranstaltung_tabelle[$iTmpZehler]=jahresplan_funk_veranstaltung_extend($veranstaltung_tabelle[$iTmpZehler]);
	}
	// Initialisieren Gruppenwechsel und ZeilenfarbenIndex		
  	$cTmpLastKat="";
	$cTmpLastDat="";
	$cTmpLastRow=0;

	// Initialisieren HTML Code Ausgabe
	$showHTML=''; 
	// Start Listenausgabe 
	$showHTML.='<table>';
	
	reset($veranstaltung_tabelle);
	for ($iTmpZehler=0;$iTmpZehler<count($veranstaltung_tabelle);$iTmpZehler++)
	{
		$veranstaltung_tabelle[$iTmpZehler]["start_timestamp"]=jahresplan_date_to_timestamp(trim($veranstaltung_tabelle[$iTmpZehler]["start"]));
		$veranstaltung_tabelle[$iTmpZehler]["ende_timestamp"]=jahresplan_date_to_timestamp(trim($veranstaltung_tabelle[$iTmpZehler]["ende"]));

		// Datum Gruppenwechsel - Listenzeile 
		if ($cTmpLastDat!=$veranstaltung_tabelle[$iTmpZehler]['start_jjjjmmtt'])
		{
			if (!empty($cTmpLastDat)) // Strichzeile vor einem Datumswechsel - nicht beim ersten mal
			{
				$showHTML.='<tr><td colspan="15"><hr></td></tr>'; 
			}
			$showHTML.='<tr><td colspan="15"><h2>&nbsp;'.strftime(constHeaderVeranstaltungsdatum,$veranstaltung_tabelle[$iTmpZehler]['start_timestamp']).'&nbsp;</h2></td></tr>';
			// Titelleiste immer nach Datumanzeigen
			$showHTML.='<tr class="header_liste_titelzeile">	
						<th>ID</th>
						<th>Veranstaltung</th>
						<th>Beginn</th>
						<th>Ende</th>
					';
			// Spezielle Anzeigen fuer Wartungsberechtigte Admins
			if ($wartungsberechtigt)					
			{
				$showHTML.='<th colspan="2">Aktion</th><th colspan="2">Freigabe</th>';			
			}	
			$showHTML.='</tr>';
			$cTmpLastKat=""; // Kategoriegruppe Init fuer Gruppenwechsel - Zeilenanzeige
		}
		$cTmpLastDat=$veranstaltung_tabelle[$iTmpZehler]['start_jjjjmmtt'];
		// ---- Ende Veranstaltungsdatum Gruppenwechsel
		
		
		// Kategorie Gruppenwechsel - Listenzeile
		if ($cTmpLastKat!=$veranstaltung_tabelle[$iTmpZehler]['veranstaltungskategorie_kurzbz'])
		{
			if (!empty($cTmpLastDat)) // Leerzeile vor einem Veranstaltungskategoriewechsel - nicht beim ersten mal
			{
				$showHTML.='<tr><td colspan="15">&nbsp;</td></tr>'; 
			}
		
			// Create Kategorie IMG  
			$veranstaltung_tabelle[$iTmpZehler]["bild_image"]='';
			if (!empty($veranstaltung_tabelle[$iTmpZehler]["bild"]))
			{
				$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($veranstaltung_tabelle[$iTmpZehler]["bild"])<800?'heximg='.$veranstaltung_tabelle[$iTmpZehler]["bild"]:'veranstaltungskategorie_kurzbz='.$veranstaltung_tabelle[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
				$veranstaltung_tabelle[$iTmpZehler]["bild_image"]='<img width="16" border="0" title="'.$veranstaltung_tabelle[$iTmpZehler]["bezeichnung"].'" alt="Kategoriebild" src="'.$cURL.'">';
			}
			// Kategorie = Bild + Bezeichnung
			$cKategorie=(isset($veranstaltung_tabelle[$iTmpZehler]['bild_image'])?$veranstaltung_tabelle[$iTmpZehler]['bild_image'].'&nbsp;':'');
			$cKategorie.=$veranstaltung_tabelle[$iTmpZehler]['bezeichnung'].'&nbsp;';
			$showHTML.='<tr><td colspan="15">'.$cKategorie.'</td></tr>';
			$cTmpLastRow=0; // Zeilenfarbe Initialisieren - Startfarbe der Kategorie
		}
		$cTmpLastKat=$veranstaltung_tabelle[$iTmpZehler]['veranstaltungskategorie_kurzbz'];
	// ---- Ende Veranstaltungskategorie Gruppenwechsel
		$showHTML.='<tr '.($cTmpLastRow%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>';
		// Detailanzeige - Switch zum umschalten ob das PopUp Extern oder Intern im Layer geoeffnet wird
			$showHTML.='
				<td class="cursor_hand" onclick="callWindows(\'jahresplan_detail.php?veranstaltung_id='.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].'\',\'Jahresplan\',\'\');">
					<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><img width="16" border="0" alt="spacer" src="jahresplan_bilder.php?time='.time().'" ></td>
						<td>'.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].'</td>
						<td>&nbsp;</td>
						<td><img title="Detail" src="../../../skin/images/date_magnify.png" alt="Detail" border="0"></td>
					</tr>
					</table>
				</td>

				';	

				
		$showHTML.='<td title="'.trim($veranstaltung_tabelle[$iTmpZehler]['beschreibung']).'\n '.trim($veranstaltung_tabelle[$iTmpZehler]['inhalt']).'">';
		if ($wartungsberechtigt)			
		{
			$maxWortlaenge=45;
		}
		else
		{
			$maxWortlaenge=90;
		}	
		$showHTML.=(strlen(trim($veranstaltung_tabelle[$iTmpZehler]['beschreibung']).' '.trim($veranstaltung_tabelle[$iTmpZehler]['inhalt']) )>$maxWortlaenge?substr(trim($veranstaltung_tabelle[$iTmpZehler]['beschreibung']).' '.trim($veranstaltung_tabelle[$iTmpZehler]['inhalt']),0,$maxWortlaenge).'...':trim($veranstaltung_tabelle[$iTmpZehler]['beschreibung']).' '.trim($veranstaltung_tabelle[$iTmpZehler]['inhalt']));
		$showHTML.='&nbsp;</td>';

		
			$showHTML.='<td>'.strftime(constZeileVeranstaltungsdatum,$veranstaltung_tabelle[$iTmpZehler]["start_timestamp"]).'&nbsp;</td>';
			// Veranstaltungs - Ende Variable : wenn Startdatum und Endedatum gleich nur die Zeit als Ende anzeigen
			if (strftime(constZeitDatumJJJJMMTT,$veranstaltung_tabelle[$iTmpZehler]["start_timestamp"])==strftime(constZeitDatumJJJJMMTT,$veranstaltung_tabelle[$iTmpZehler]["ende_timestamp"]))
				$showHTML.='
					<td>'.strftime(constZeileVeranstaltungszeit ,$veranstaltung_tabelle[$iTmpZehler]["ende_timestamp"]).'&nbsp;</td>';
			else
				$showHTML.='
					<td>'.strftime(constZeileVeranstaltungsdatum ,$veranstaltung_tabelle[$iTmpZehler]["ende_timestamp"]).'&nbsp;</td>';
				
		if ($wartungsberechtigt)			
		{
			// Erzeugen PopUp URL fuer Wartung

			// Url
			$cTmpScriptWartung=' onclick="callWindows(\'jahresplan_veranstaltung.php?work=show&amp;veranstaltung_id='.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].'\',\'Veranstaltung_Aenderung\');" ';
			// Aendern Icon und Text		
			$showHTML.='<td '.$cTmpScriptWartung.'>
				<img title="ID '.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].' aendern '.$veranstaltung_tabelle[$iTmpZehler]['titel'].'" height="14px" src="../../../skin/images/date_edit.png" alt="aendern Veranstaltung" border="0">
					&auml;ndern
				</td>';
				
			// Erzeugen PopUp URL fuer Entfernen
			// Url
			$cTmpScriptWartung=' onclick="if (!confirm(\'Wollen Sie wirklich ID '.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].' l&ouml;schen ?\')) {return false;}  ; callWindows(\'jahresplan_veranstaltung.php?work=del&amp;veranstaltung_id='.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].'\',\'Veranstaltung_Loeschen\');" ';
			// Loeschen Icon und Text
			$showHTML.='<td id="jh_va_delrow'.$iTmpZehler.'" '.$cTmpScriptWartung.'>
				<img title="ID '.$veranstaltung_tabelle[$iTmpZehler]['veranstaltung_id'].' entfernen '.$veranstaltung_tabelle[$iTmpZehler]['titel'].'" height="14px" src="../../../skin/images/date_delete.png" alt="entfernen Veranstaltung" border="0">
				entfernen
				</td>';

			// Freigabe Information und Berechtigungsinfo wer diesen Eintrag sehen darf - Oeffentlich - Mitarbeiter
			$showHTML.='<td>';
				$showHTML.=(!empty($veranstaltung_tabelle[$iTmpZehler]['freigabeamum'])?$veranstaltung_tabelle[$iTmpZehler]['freigabeamum'].', '.$veranstaltung_tabelle[$iTmpZehler]['freigabevon']:'');
				if (empty($veranstaltung_tabelle[$iTmpZehler]['freigabeamum']))
					$showHTML.='<img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="keine Freigabe" border="0">';
				$showHTML.='</td>';

			// Plausibfehler Datum Von-Bis ausgeben 
			if ($veranstaltung_tabelle[$iTmpZehler]["start_timestamp"]>$veranstaltung_tabelle[$iTmpZehler]["ende_timestamp"])
				$showHTML.='
					<td ><b>Fehler! Start kleiner Ende</b>&nbsp;</td>
				';	
		}
		
		$showHTML.='</tr>'; // Ende Zeile mit einer Veranstaltung
		// Detailanzeige PopUp
		$cTmpLastRow++; // ZeilenFarbWechsel erhoehen
	}
	$showHTML.='</table>';	

	$showHTML.='<span class="footer_zeile">Bei Fragen geben Sie bitte immer die Veranstaltungs ID an.</span>';
	// Return HTML Liste
	return $showHTML;		
}

#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_funk_veranstaltung_extend Erweitern der Datenbankdatentabelle mit Datum,Bildlink,User,....
*
* @param $veranstaltung Array Erweitern der Veranstaltungsdaten aus der DB
*
* @return Erweiterte Veranstaltungen Array 
*
*/
function jahresplan_funk_veranstaltung_extend($veranstaltung)
{
	// Plausib
	if (is_array($veranstaltung) && isset($veranstaltung[0]) && is_array($veranstaltung[0]) && isset($veranstaltung[0]['veranstaltung_id']) )
	{
		 $veranstaltung=$veranstaltung[0];
	}
	// Plausib
	if (!is_array($veranstaltung) || count($veranstaltung)<1 || !isset($veranstaltung["veranstaltung_id"])) 
	{
		return $veranstaltung;
	}	
	// Daten in Work Array uebertragen
	$veranstaltung_work=$veranstaltung;
	
	// Check Space in Textfelder
	// ---- Veranstaltungs-Kategorie
	$veranstaltung_work["veranstaltungskategorie_kurzbz"]=trim($veranstaltung_work["veranstaltungskategorie_kurzbz"]);
	$veranstaltung_work["bezeichnung"]=trim($veranstaltung_work["bezeichnung"]);
	// ---- Veranstaltung
	$veranstaltung_work["beschreibung"]=trim($veranstaltung_work["beschreibung"]);
	$veranstaltung_work["inhalt"]=trim($veranstaltung_work["inhalt"]);

	
	// Bildaufbereiten
	$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($veranstaltung_work["bild"])<700?'heximg='.$veranstaltung_work["bild"]:'veranstaltungskategorie_kurzbz='.$veranstaltung_work["veranstaltungskategorie_kurzbz"]);
	$veranstaltung_work["bild_image"]='<img width="16" border="0" title="'.$veranstaltung_work["bezeichnung"].'" alt="Kategoriebild" src="'.$cURL.'" >';

	return $veranstaltung_work;
}


#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_detail_user anzeige der Anwenderinformation Freigabe,Anlage,Aenderung der Veranstaltungen 
*
* @param $conn Aktuelle Datenbankverbindung
* @param $veranstaltung Veranstaltung 
* @param $wartungsberechtigt Anzeige fuer Admin und Wartungsberechtigte 
*
* @return HTML Informationsansicht der Anwenderinformation der Veranstaltungen
*
*/
function jahresplan_veranstaltung_detail_user($conn,$veranstaltung,$wartungsberechtigt=false)
{
	$unicode=null;
	if (!$wartungsberechtigt)
	{
		return 'keine Berechtigung zur Information der Anwenderdaten (Freigabe,Anlage,Aenderung)';
	}
	
	
	$veranstaltung_detail=$veranstaltung;
	
	if (is_array($veranstaltung_detail) && isset($veranstaltung_detail[0]) && is_array($veranstaltung_detail[0]) && isset($veranstaltung_detail[0]['veranstaltung_id']))
	{
		$veranstaltung_detail=$veranstaltung_detail[0];
	}
		
	if (!is_array($veranstaltung_detail) || !isset($veranstaltung_detail['veranstaltung_id'])  || empty($veranstaltung_detail['veranstaltung_id']))
	{
		return '';
	}

	$showHTML='<table class="userinfo">';
	// Freigabe
	
		$userNAME=$veranstaltung_detail['freigabevon'];
		$pers = new benutzer($conn,$userNAME,$unicode); // Lesen Person - Benutzerdaten

		if (isset($pers->nachname))
		{
			$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
			$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
			$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
			$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
			if ($pers->foto)
			{
				$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
				$veranstaltung_detail["freigabebild"]='<img width="16" border="0" title="'.$userNAME.'" alt="Benutzerbild" src="'.$cURL.'" >';
			}
		}
	
		$showHTML.='<tr><td>Freigabe von :'.$userNAME.'</td><td>'.(isset($veranstaltung_detail['freigabeamum'])?' am '.$veranstaltung_detail['freigabeamum']:'').'</td><td>'.(isset($veranstaltung_detail["freigabebild"])?' '.$veranstaltung_detail["freigabebild"]:'').'</td></tr>';
	// Letzte Aenderung
		$userNAME=$veranstaltung_detail['updatevon'];
		$pers = new benutzer($conn,$userNAME,$unicode); // Lesen Person - Benutzerdaten
		if (isset($pers->nachname))
		{
			$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
			$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
			$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
			$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
			if ($pers->foto)
			{
				$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
				$veranstaltung_detail["updatebild"]='<img width="16" border="0" title="'.$userNAME.'" alt="Benutzerbild" src="'.$cURL.'" >';
			}
		}
		$showHTML.='<tr><td>&Auml;nderung von :'.$userNAME.'</td><td>'.(isset($veranstaltung_detail['updateamum'])?' am '.$veranstaltung_detail['updateamum']:'').'</td><td>'.(isset($veranstaltung_detail["updatebild"])?' '.$veranstaltung_detail["updatebild"]:'').'</td></tr>';
	
	// Neuanlage
		$userNAME=$veranstaltung_detail['insertvon'];
		$pers = new benutzer($conn,$userNAME,$unicode); // Lesen Person - Benutzerdaten
		if (isset($pers->nachname))
		{
			$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
			$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
			$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
			$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
			if ($pers->foto)
			{
				$cURL='jahresplan_bilder.php?time='.time().'&amp;'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
				$veranstaltung_detail["insertbild"]='<img width="16" border="0" title="'.$userNAME.'" alt="Benutzerbild" src="'.$cURL.'" >';
			}
		}
		$showHTML.='<tr><td>Neuanlage von :'.$userNAME.'</td><td>'.(isset($veranstaltung_detail['insertamum'])?' am '.$veranstaltung_detail['insertamum']:'').'</td><td>'.(isset($veranstaltung_detail["insertbild"])?' '.$veranstaltung_detail["insertbild"]:'').'</td></tr>';
	$showHTML.='</table>';
	return $showHTML;
}


#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_date_to_timestamp Erzeugt einen Timestamp aus einem Datum
*
* @param $string Datum mit / Ohne Zeit
*
* @return Timestamp
*
*/
function jahresplan_date_to_timestamp($string="")
{

	$cTmpWert=$string;

	if (!empty($cTmpWert) && !is_numeric($cTmpWert)) // Start wurde als Datum Zeit uebergeben
	{	
			$cTmpWert=str_replace('.','-',$cTmpWert);
			$dateparam=explode(' ',$cTmpWert);
			$date=explode('-',$dateparam[0]);
			if (!isset($dateparam[1])) $dateparam[1]='00:01:00';
			$time=explode(':',$dateparam[1]);
			if (!isset($time[2])) $time[2]=0;
			
			if ($date[2]<1000) 
			{
				$wechsel=$date[0];
				$date[0]=$date[2];
				$date[2]=$wechsel;
			}
			
			if (@checkdate($date[1], $date[0], $date[2]) )
			{			
				if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], $time[2], $date[1],$date[0],$date[2] )))
						$cTmpWert=$cTmpTimeStampWert;	
			}	
			else "kein Datum ";
	}
	return 	$cTmpWert;	
}
 
# Testfunktion zur Anzeige einer übergebenen Variable oder Array, Default ist GLOBALS
function Test($arr=constLeer,$lfd=0,$displayShow=true,$onlyRoot=false )
{

    $tmpArrayString='';
    if (!is_array($arr) && !is_object($arr)) return $arr;
    if (is_array($arr) && count($arr)<1 && $displayShow) return '';
    if (is_array($arr) && count($arr)<1 && $displayShow) return "<br /><b>function Test (???)</b><br />";
   
    $lfdnr=$lfd + 1; 
    $tmpAnzeigeStufe='';
    for ($i=1;$i<$lfdnr;$i++) $tmpAnzeigeStufe.="=";
    $tmpAnzeigeStufe.="=>";
	while (list( $tmp_key, $tmp_value ) = each($arr) ) 
	{
       	if (!$onlyRoot && (is_array($tmp_value) || is_object($tmp_value)) && count($tmp_value) >0) 
       	{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key</b>".Test($tmp_value,$lfdnr);
       	} else if ( (is_array($tmp_value) || is_object($tmp_value)) ) 
       	{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key -- 0 Records</b>";
		} else if ($tmp_value!='') 
		{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :== ".$tmp_value;
		} else {
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :-- (is Empty :: $tmp_value)";
		}  
    }
     if ($lfd!='') { return $tmpArrayString; }
     if (!$displayShow) { return $tmpArrayString; }
       
    $tmpArrayString.="<br />";
    $tmpArrayString="<br /><hr><br />******* START *******<br />".$tmpArrayString."<br />******* ENDE *******<br /><hr><br />";
    $tmpArrayString.="<br />Server:: ".$_SERVER['PHP_SELF']."<br />";

    return "$tmpArrayString";
}
 
?>