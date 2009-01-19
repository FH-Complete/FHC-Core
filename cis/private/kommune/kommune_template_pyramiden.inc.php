<?php
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
#-------------------------------------------------------------------------------------------	
/*
*
* @showPyramide Teams des Wettbewerbes in Pyramidenform(HTML Table) erzeugen
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return showPyramide HTML Ausgabe der Wettbewerbesteilnehmer in Pyramidenform
*
*/
function showPyramide($oWettbewerb)
{
	// Initialisierung
	$showHTML='';

	// Header 
      	$showHTML.='<div>';

		$cTmpWettbewerbPopUpID='Wettbewerbs';
		$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';

		// Wettbewerb-Farbe
		$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[0]["farbe"]) && !empty($oWettbewerb->Wettbewerb[0]["farbe"])?' style="background-color:#'.$oWettbewerb->Wettbewerb[0]["farbe"].';text-align:center;" ':' style="text-align:center;"');
		// Ueberschrift des Wettbewerbs
		$showHTML.='<h1 '.$cTmpFarbe.' '.$cTmpWettbewerbPopUp.'>';
			$showHTML.=$oWettbewerb->wbtyp_kurzbz.' :: '.$oWettbewerb->wettbewerb_kurzbz;
    		$showHTML.='</h1>';
		// Wettbewerb PopUp
		$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'" style="display:none;position: absolute;z-index:99;">';
			$showHTML.=kommune_funk_popup_wettbewerb($oWettbewerb->Wettbewerb[0],$oWettbewerb,'pyramide');
		$showHTML.='</div>';

		// Moderator
		$cTmpName=$oWettbewerb->Wettbewerb[0]["uid"];
		$pers=kommune_funk_benutzerperson($oWettbewerb->Wettbewerb[0]["uid"],$oWettbewerb);
		if (isset($pers->nachname)) 
			$cTmpName=$pers->langname;

		$cTmpPersPopUpID='sPers';
		$cTmpPersPopUp=' onmouseover="show_layer(\''.$cTmpPersPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpPersPopUpID.'\');" ';
			
		$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[0]["farbe"]) && !empty($oWettbewerb->Wettbewerb[0]["farbe"])?' style="color:#'.$oWettbewerb->Wettbewerb[0]["farbe"].';" ':'');
		$showHTML.='<h3><a '.$cTmpFarbe.' href="#" '.$cTmpPersPopUp.'>Moderator '.$cTmpName.'</a></h3>';
			$showHTML.='<div id="'.$cTmpPersPopUpID.'" style="display:none;position: absolute;z-index:99;">';
			$showHTML.=kommune_funk_popup_benutzer($oWettbewerb->Wettbewerb[0]["uid"],$oWettbewerb);
	    	$showHTML.='</div>';


	$showHTML.='</div>';
	// Header  Ende


	 // Es gibt noch keine Spieler in diesem Wettbewerb. Link zur Anmeldung zeigen
	if (!is_array($oWettbewerb->Team) || count($oWettbewerb->Team)<1) 
	{
		$cTmpHREF=kommune_funk_create_href(constKommuneWartungUID,$oWettbewerb,array(),'weiter zur Anmeldung');
		$cTmpHREF2=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/moreright.gif" border="0" />&nbsp;zur&nbsp;Startseite&nbsp;','&nbsp;zur&nbsp;Startseite&nbsp;');
		$showHTML.='Es gibt noch keine Anmeldungen im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz.' ! <img style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;'.$cTmpHREF.'</span>&nbsp;,oder&nbsp;'.$cTmpHREF2;
		return $showHTML;
	}		

	 // Es gibt keinen weiteren Spieler ausser dem Anwender
	if ( (is_array($oWettbewerb->Team) && count($oWettbewerb->Team)==1 ) 
	&& (is_array($oWettbewerb->TeamAnwender) && count($oWettbewerb->TeamAnwender)==1) )
	{
		// PopUP ID , und JavaScript fuer Team / Spieler
		$cTmpTeamPopUpID='sTeam';
		$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';
		// zur Wartung des Teams, oder Zurueck
		$cTmpHREF=kommune_funk_create_href(constKommuneWartungUID,$oWettbewerb,array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;meinen Eintrag &auml;ndern&nbsp;','meinen Eintrag &auml;ndern');
		$cTmpHREF2=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/moreright.gif" border="0" />&nbsp;zur&nbsp;Startseite&nbsp;','&nbsp;zur&nbsp;Startseite&nbsp;');

		$showHTML.='Es gibt keine weiteren Anmeldungen im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz.'&nbsp;!&nbsp;<span '.$cTmpTeamPopUp.'>'.$cTmpHREF.'</span>&nbsp;,oder&nbsp;'.$cTmpHREF2;
		$showHTML.='<div style="display:none;position: absolute;z-index:98;" id="'.$cTmpTeamPopUpID.'">';
			$showHTML.=kommune_funk_popup_wettbewerbteam($oWettbewerb->TeamAnwender,$oWettbewerb,'team');
		$showHTML.='</div>';
		return $showHTML;
	}		
	
	// Offene Spiel Forderungen anzeigen	
	if ( is_array($oWettbewerb->Einladung) && count($oWettbewerb->Einladung)>0)
		$showHTML.=kommune_funk_wartung_spielergebnis($oWettbewerb,false);
	if ( is_array($oWettbewerb->Forderungen) && count($oWettbewerb->Forderungen)>0)
		$showHTML.=kommune_funk_wartung_spielergebnis($oWettbewerb,true);

	// Wettbewerbsteams in Pyramiden Array Strucktur - HTML Tablenform
	$iTmpAnzahl=(!is_array($oWettbewerb->Einladung) || count($oWettbewerb->Einladung)<1?0:count($oWettbewerb->Einladung)); 
	$iTmpAnzahl=$iTmpAnzahl+(!is_array($oWettbewerb->Forderungen) || count($oWettbewerb->Forderungen)<1?0:count($oWettbewerb->Forderungen)); 
#	if (empty($iTmpAnzahl)) // Anzeige der Pyramide nur wenn keine Einladung oder Forderung besteht
	$oWettbewerb->AnzeigePyramide=createPyramide($oWettbewerb->Team);	
	// Pyramidenanzeige
	$showHTML.=showPyramideHTML($oWettbewerb);

	// Footer - Ende  
	$showHTML.='<hr />';
       // Es gibt keine Anmeldung in diesen Spiel fuer den Anwender
	
		if (!is_array($oWettbewerb->TeamAnwender) || count($oWettbewerb->TeamAnwender)<1) 
		{
			$cTmpHREF=kommune_funk_create_href(constKommuneWartungUID,$oWettbewerb,array(),'weiter zur Anmeldung');
			$showHTML.='Sie sind noch nicht in '.$oWettbewerb->wettbewerb_kurzbz.' eingetragen ! <img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;'.$cTmpHREF;
			unset($cTmpHREF);
		}		  
		// Es gibt eine Anmeldung in diesen Spiel fuer den Anwender, Link zur Aenderung 
		else
		{
		// Link zur Wartung, und PopUp 
			$showHTML.='<span>';

			$cTmpTeamPopUpID='TeamFooter';
			$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';

			$cTmpWettbewerbPopUpID='sTeamWettbewerb';
			$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';
	
			$cTmpHREF=kommune_funk_create_href(constKommuneWartungUID,'',array('wettbewerb_kurzbz'=>$oWettbewerb->wettbewerb_kurzbz,'team_kurzbz'=>$oWettbewerb->TeamAnwender[0]['team_kurzbz']),' weiter zum Eintrag &auml;ndern');
			$cTmpHREF2=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/moreright.gif" border="0" />&nbsp;zur&nbsp;Startseite&nbsp;','&nbsp;zur&nbsp;Startseite&nbsp;');
				
			$showHTML.='Sie sind bereits im Wettbewerb  <b '.$cTmpWettbewerbPopUp.'>'.$oWettbewerb->wettbewerb_kurzbz.'</b> als <b '.$cTmpTeamPopUp.'>'.$oWettbewerb->TeamAnwender[0]['team_kurzbz'] .'</b> eingetragen ! <span><img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;'.$cTmpHREF.'</span>'.$cTmpHREF2;
				
			$showHTML.='</span><br />';	

			// Team / Spieler PopUp ( Im Teampopup wird auch das Wettbewerbspopup erstellt )
			$showHTML.='<div style="display:none; position: absolute;z-index:99;" id="'.$cTmpTeamPopUpID.'">';
				$showHTML.=kommune_funk_popup_wettbewerbteam($oWettbewerb->TeamAnwender[0],$oWettbewerb,'showTeamFooter');
			$showHTML.='</div>';		

			// Wettbewerb PopUp
			$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'" style="display:none;position: absolute;z-index:99;">';
				$showHTML.=kommune_funk_popup_wettbewerb($oWettbewerb->Wettbewerb[0],$oWettbewerb,'pyramide');
			$showHTML.='</div>';			
										
		}
		$showHTML.='<br />';

		// Liste der bereits gespielten Bewerbe
		// Anzeige der Pyramide nur wenn keine Einladung oder Forderung besteht

		if (empty($iTmpAnzahl) && is_array($oWettbewerb->Spiele) && count($oWettbewerb->Spiele)>0)
		{
			$showHTML.='<fieldset><legend>Ergebnisse '.$oWettbewerb->TeamAnwender[0]['team_kurzbz'].'</legend>';
				$showHTML.=kommune_funk_show_spielergebnis($oWettbewerb,false);
			$showHTML.='</fieldset>';
		}
		// Ausgabststring (HTML) zurueck an die Index.php liefern
		return $showHTML;
} // Ende   showPyramide     
#--------------------------------------------------------------------------------------------------
/*
*
* @createPyramide Alle Spieler werden vom flachen Array in eine Pyramidenform (Stuffen) umgeformt
*
* @param arrAktiveSpielerWettbewerbe Liste aller Spieler
*
* @return Retour wird ein Array in Pyramidenform der Spieler geliefert
*
*/
function createPyramide($arrAktiveSpielerWettbewerbe)
{
	$arrTmpWettbewerb=array();
	if (isset($arrAktiveSpielerWettbewerbe[0]['team_kurzbz'])) 
		$arrTmpWettbewerb=$arrAktiveSpielerWettbewerbe;
	else // Array - Konvertieren 
	{	
		while (list( $key, $value ) = each($arrAktiveSpielerWettbewerbe) )
			$arrTmpWettbewerb[]=$arrAktiveSpielerWettbewerbe[$key];
	}
	$arrAktiveSpielerWettbewerbe=$arrTmpWettbewerb;
	if (isset($arrTmpWettbewerb)) unset($arrTmpWettbewerb);
	
	// Initialisierung
	$arrSpielerPyramide=array();
   	// Es gibt keinen Datensatz, Pyramidenaufbau ist nicht noetig
	if (!is_array($arrAktiveSpielerWettbewerbe) || count($arrAktiveSpielerWettbewerbe)<1) 
		return $arrSpielerPyramide;
		
	// Pyramidenaufbau
	$aktivIND=0;
	reset($arrAktiveSpielerWettbewerbe);	
	$arrSpielerPyramide["Anwender"]=array();
    	for ($zeileIND=0;$zeileIND<count($arrAktiveSpielerWettbewerbe);$zeileIND++)
    	{
            for ($spaltenIND=0;$spaltenIND<=$zeileIND&&$aktivIND<count($arrAktiveSpielerWettbewerbe);$spaltenIND++)
            {
		   if (!isset($arrAktiveSpielerWettbewerbe[$aktivIND]))
	  	 	break;	

		   if ($arrAktiveSpielerWettbewerbe[$aktivIND]['team_aktiv']) // Eigenen Eintrag Makieren
			   $arrSpielerPyramide["Anwender"]=array('zeile'=>$zeileIND,'spalte'=>$spaltenIND,'daten'=>$arrAktiveSpielerWettbewerbe[$aktivIND]);

		   if ($arrAktiveSpielerWettbewerbe[$aktivIND]['team_kurzbz']) // Eigenen Eintrag Makieren
			   $arrSpielerPyramide[$zeileIND][]=$arrAktiveSpielerWettbewerbe[$aktivIND];

	           $aktivIND++;    
            }
    	}
    return $arrSpielerPyramide;
}       
#-------------------------------------------------------------------------------------------	
/*
*
* @showPyramideHTML Anzeigen Pyramidendaten in Tabelle(n)-Form der Spieler
*
* @param $arrSpielerPyramide Wettbewerbsdaten
*
* @return Retour wird ein HTML String mit der  Ausgabe Tabelle der Pyramide geliefert
*
*/
function showPyramideHTML($oWettbewerb=array())
{
	// Initialisierung
		$showHTML='';
	// Es gibt keinen Datensatz, Pyramidenaufbau ist nicht noetig
		if (!is_array($oWettbewerb->AnzeigePyramide) || count($oWettbewerb->AnzeigePyramide)<1) 
			return $showHTML;
		$arrSpielerPyramide=$oWettbewerb->AnzeigePyramide;			
		$arrAktiverSpieler=$arrSpielerPyramide["Anwender"];
		unset($arrSpielerPyramide["Anwender"]);
		reset($arrSpielerPyramide);	

		if (is_array($arrAktiverSpieler) && count($arrAktiverSpieler)>0)
		{
			$iTmpAktiverSpielerZeile=$arrAktiverSpieler['zeile'];		
			$iTmpAktiverSpielerSpalte=$arrAktiverSpieler['spalte'];		
			$iTmpAktiverSpielerDaten=$arrAktiverSpieler['daten'];		
		}
		else
		{
			$iTmpAktiverSpielerZeile='';		
			$iTmpAktiverSpielerSpalte='';		
			$iTmpAktiverSpielerDaten='';		
		}		
	// Start des Pyramidenaufbaues	      
       $showHTML.='<table summary="Pyramide" style="text-align:center;width: 100%;">';

	$cTmpStartFarbe=(isset($oWettbewerb->Wettbewerb[0]["farbe"]) && !empty($oWettbewerb->Wettbewerb[0]["farbe"])?$oWettbewerb->Wettbewerb[0]["farbe"]:'B1B1B1');
       for ($zeileIND=0;$zeileIND<count($arrSpielerPyramide);$zeileIND++)
       {		
		if ($zeileIND>0) 		// Neue Pyramiden Reihe beginnnen Border-Farbe reduzieren
		{
	   		$arrTmpStartFarbe=str_split($cTmpStartFarbe ,2);
		   	$cTmpRotFarbe=hexdec($arrTmpStartFarbe[0])-10;
	   		if ($cTmpRotFarbe<0)
			   $cTmpRotFarbe=0;
		   	$cTmpGelbFarbe=hexdec($arrTmpStartFarbe[1]) - 10;
	   		if ($cTmpGelbFarbe<0)
	 		   $cTmpGelbFarbe=0;
			$cTmpBlauFarbe=hexdec($arrTmpStartFarbe[2])-10;  
			if ($cTmpBlauFarbe<0)
	 		   $cTmpBlauFarbe=0;		
			$cTmpStartFarbe = dechex($cTmpRotFarbe). dechex($cTmpGelbFarbe). dechex($cTmpBlauFarbe);
		}
#	exit(Test($arrTmpStartFarbe)."$cTmpRotFarbe , $cTmpGelbFarbe , $cTmpBlauFarbe  ").$cTmpStartFarbe;	   	   

       	$showHTML.='<tr><td style="text-align: center;">
			<table summary="Spielpyramiden Zeile '.($zeileIND + 1).'"  style="width: 100%;"><tr>';
		for ($spaltenIND=0;$spaltenIND<count($arrSpielerPyramide[$zeileIND]);$spaltenIND++)
        	{
			// PopUP ID , und JavaScript fuer Team / Spieler
			$cTmpTeamPopUpID='sTeam'.$zeileIND.'_'.$spaltenIND;
			$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';
		
			$wettbewerb_kurzbz=$arrSpielerPyramide[$zeileIND][$spaltenIND]["wettbewerb_kurzbz"];
			
			$team_kurzbz=$arrSpielerPyramide[$zeileIND][$spaltenIND]["team_kurzbz"];
			$prozentTDbreite=(100/($zeileIND+1)); // Breite in Prozent je TD ermitteln
			
			// Farbstufen
			if ($zeileIND%2)
				$showHTML.='<td style="text-align: center;width:'.$prozentTDbreite.'%;border:2px outset #'.$cTmpStartFarbe.';">&nbsp;';
			else			
				$showHTML.='<td style="text-align: center;width:'.$prozentTDbreite.'%;border:2px inset #'.$cTmpStartFarbe.';">&nbsp;';
			
			$showHTML.='<b>'.$arrSpielerPyramide[$zeileIND][$spaltenIND]['rang'].'</b> <br />';

			// Farbstufen
			if ($zeileIND%2)
				$showHTML.='<span style="border:0px outset #'.$cTmpStartFarbe.';">&nbsp;';
			else			
				$showHTML.='<span style="border:0px inset #'.$cTmpStartFarbe.';">&nbsp;';


			if ($arrSpielerPyramide[$zeileIND][$spaltenIND]['team_aktiv'])// Eigenen Eintrag des Anwender
			{

			// PopUp Fenster mit Informationen zum Wettbewerbteam
 		    		$showHTML.='&nbsp;<img title="weiter zu '.$team_kurzbz.'" style="text-align: center;vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />';
				$showHTML.=kommune_funk_create_href(constKommuneWartungUID,'',array('team_kurzbz'=>trim($team_kurzbz),'wettbewerb_kurzbz'=>trim($wettbewerb_kurzbz)),$team_kurzbz);
			}				
			// Eingeladen werden koennen  
 			// a) Alle Spieler in der gleiche Zeile die vor dem Eintrag sind 
 			// b) Der Letzte Spieler in der Vorherigen Zeile kann eingeladen werden
			elseif (isset($iTmpAktiverSpielerDaten['team_kurzbz'])
			    && ($zeileIND==$iTmpAktiverSpielerZeile && $spaltenIND<$iTmpAktiverSpielerSpalte)
			    || ( $zeileIND==($iTmpAktiverSpielerZeile-1) && $spaltenIND==count($arrSpielerPyramide[$zeileIND])-1 ) )  
			{
 		    		$showHTML.='&nbsp;<img title="weiter zur Forderung" alt="Forderung" height="18" src="../../../skin/images/image_legend0.gif" border="0" />';
				$showHTML.='Fordern '.kommune_funk_create_href(constKommuneEinladenTEAM,'',array('team_kurzbz'=>trim($iTmpAktiverSpielerDaten['team_kurzbz']),'team_forderer'=>trim($iTmpAktiverSpielerDaten['team_kurzbz']),'einladen_team_kurzbz'=>trim($team_kurzbz),'wettbewerb_kurzbz'=>trim($wettbewerb_kurzbz)),$team_kurzbz);
			}
			else
			{
	           		$showHTML.=$team_kurzbz;
			}	
			$showHTML.='&nbsp;<img height="16" '.$cTmpTeamPopUp.' title="Detailinformationen '.$team_kurzbz.'" style="vertical-align: bottom;" alt="infoWettbewerb'.$zeileIND.$spaltenIND.'" src="../../../skin/images/icon_voransicht.gif" border="0" />&nbsp;';

			$showHTML.='&nbsp;</span>'; // Ende Farbstufen
			
			$showHTML.='<div style="display:none;position: absolute;z-index:98;" id="'.$cTmpTeamPopUpID.'" >';
			if (isset($arrSpielerPyramide[$zeileIND][$spaltenIND]['team_kurzbz']) && is_array($arrSpielerPyramide[$zeileIND][$spaltenIND]) )
				$showHTML.=kommune_funk_popup_wettbewerbteam($arrSpielerPyramide[$zeileIND][$spaltenIND],$oWettbewerb,$cTmpTeamPopUpID.$spaltenIND,true);
			$showHTML.='</div>';	 	
						
		$showHTML.='</td>';	 		
            }

	// TD ausgleich fuer eine kpl. Tabellenreihe aufzufuellen
	     for ($spaltenIND+1;$spaltenIND< ($zeileIND + 1) ;$spaltenIND++)
            {
           		$showHTML.='<td  style="border:2px outset #'.$cTmpStartFarbe.';text-align: center;width:'.$prozentTDbreite.'%;" id="col_row_'.$zeileIND.'_'.$spaltenIND.'">&nbsp;</td>';
            }
            $showHTML.='</tr></table>
	      </td></tr>';
       }	
   	$showHTML.='</table>';
	return $showHTML;
}     
#-------------------------------------------------------------------------------------------	
/*
*
* @findUIDinPyramide Suchen Zeile und Spalte einer bestimmten Anwender UID in einem Spiel 
*
* @param arrayPYRAMIDE Liste der UIDs in Pyramidenform je Spiel
* @param userUID Anwender UID nach der in der Pyramide gesucht wird 
*
* @return Retour wird ein Array mit UID,Zeile,Splate der gefundenen Position geliefert
*
*/
function findUIDinPyramide($arrayPYRAMIDE,$arrayWETTBEWERBUSER)
{
    $arrayPyramidePositionUID=array(); 

 // Es gibt keine Suche nach einer UID moeglich - notwendig 
    if ((!is_array($arrayPYRAMIDE) || count($arrayPYRAMIDE)<1)
     || (!is_array($arrayWETTBEWERBUSER) || count($arrayWETTBEWERBUSER)<1) )
		return $arrayPyramidePositionUID;
		
   	// Suchen Position(Zeile-Spalte) des Anwender in der Pyramide
    for ($zeileIND=0;$zeileIND<count($arrayPYRAMIDE);$zeileIND++)
    {
            for ($spaltenIND=0;$spaltenIND<count($arrayPYRAMIDE[$zeileIND]);$spaltenIND++)
            {
				$spielerTEAM_KURZBZ=trim($arrayWETTBEWERBUSER[0]["team_kurzbz"]);
				$wettbewerbTEAM_KURZBZ=trim($arrayPYRAMIDE[$zeileIND][$spaltenIND]["team_kurzbz"]);
       			if ($spielerTEAM_KURZBZ==$wettbewerbTEAM_KURZBZ)
				{ 
       			 	$arrayPyramidePositionUID=array($wettbewerbTEAM_KURZBZ,$zeileIND,$spaltenIND,$GLOBALS["userUID"],"team_kurzbz"=>$wettbewerbTEAM_KURZBZ,"zeileIND"=>$zeileIND,"spaltenIND"=>$spaltenIND,"userUID"=>$GLOBALS["userUID"]);
       			 	$arrayPYRAMIDE[$zeileIND][$spaltenIND]["EIGENER_EINTRAG"]="X";
					$zeileIND=count($arrayPYRAMIDE); // Schleifenbedingung 1 auf True setzten
       			 	break; // diese Schleife beenden 
       			}	
            }
    }
	return $arrayPyramidePositionUID;
}
?>