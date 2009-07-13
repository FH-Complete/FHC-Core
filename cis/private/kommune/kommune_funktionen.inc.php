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

#-------------------------------------------------------------------------------------------	
/*
*
* @showMenueFunktion erzeugt das Top Menue , die aktuelle Auswahl muss uebergeben werden
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
* @param $cTmpMenue		Aktuelles Menue
*
* @return showHTML String mit HTML TopMenue 
*
*/
function showMenueFunktion($oWettbewerb)
{
	// Plausib

	// Initialisierung
	$showHTML='';
	$cTmpMenue='';
	$cTmpIDausblenden="hide_layer('idWBPyramide');hide_layer('idWBWartung');hide_layer('idWBEinladung');hide_layer('idWBPyramide');hide_layer('idWBInformation');hide_layer('idWBInformation');hide_layer('idWBTermine');hide_layer('idWBListe');hide_layer('idWBUser');hide_layer('idWBEinlad');hide_layer('idWBAufford');hide_layer('idWBSpiele');";
	$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[0]["farbe"]) && !empty($oWettbewerb->Wettbewerb[0]["farbe"])?' style="background-color:#'.$oWettbewerb->Wettbewerb[0]["farbe"].';text-align:center;" ':' style="text-align:center;"');

	//
	//	 Anzeigenauswahl - Oberesmenue 
	//		wird nur angezeigt wenn Daten fuer die Auswahl vorhanden sind 
	//
	
	$cTmpName=$oWettbewerb->userUID;
	$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
	if (isset($pers->langname)) 
		$cTmpName=$pers->langname;

	// Startseite ( Immer )
	$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
	$cTmpMenue.=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callStartseite" />Startseite','Startseite&nbsp;');

	// Variable MenueEintraege
	$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
	if (trim($oWettbewerb->workSITE)==constKommuneSTATISTIK)
	{
		$cTmpMenue.='&nbsp;<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBInformation\');">';
			$cTmpMenue.='Information&nbsp;';
		$cTmpMenue.='</a>&nbsp;';
		$showHTML.='<div id="idWBInformation"><h1>Information</h1>'.kommune_funk_Statistik($oWettbewerb).'</div>';
	}	
	else
		$cTmpMenue.=kommune_funk_create_href(constKommuneSTATISTIK,array(),array(),'<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callInformation" />Information','Information&nbsp;');

     // Auswahl - Verteiler - Selektion - Dealer
	
     #$cTmpMenue='';
     switch (trim($oWettbewerb->workSITE)) 
     {
     
        case constKommuneWartungWettbewerbtyp:     
	       $includeFILE=strtolower($oWettbewerb->workSITE.".inc.php"); 
       	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		    include_once($includeFILE);
		exit;    
        case constKommuneWartungWettbewerb:     
	       $includeFILE=strtolower($oWettbewerb->workSITE.".inc.php"); 
       	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		    include_once($includeFILE);
		exit;
		
        case constKommuneEinladenTEAM:
              // Wettbewerbteam zum Wettbewerb anzeigen 
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|':'');		
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBEinladung\');">';

			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callEinladungbearbeiten" />Einladungbearbeiten&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBEinladung">'.showTeamEinladung($oWettbewerb).'</div>';
              break;
        case constKommuneWartungUID:
        // Wettbewer UID User Warten/Neuanlage
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');		
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBWartung\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callWartung" />WartungTeam / Spieler Wartung&nbsp;';
		$cTmpMenue.='</a>';
      		$showHTML.='<div id="idWBWartung">'.showTeamWartung($oWettbewerb).'</div>';
              break;
        case constKommuneAnzeigeWETTBEWERBTEAM:
        //  User-Teams zu einem Wettbewerb anzeigen ( Pyramide = Rang )
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');		
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBPyramide\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callWettbewerbpyramide" />Wettbewerbpyramide&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBPyramide">'.showPyramide($oWettbewerb).'</div>';
		break;
	}
		

	$iTmpAnzahl=(!is_array($oWettbewerb->EigeneWettbewerbe) || count($oWettbewerb->EigeneWettbewerbe)<1?0:count($oWettbewerb->EigeneWettbewerbe)); 
	if ($iTmpAnzahl!=0)
	{
#		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
#		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBListe\');">';
#			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callWettbewerbe" />Wettbewerbe&nbsp;('.$iTmpAnzahl.')&nbsp;';
#		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBListe" '.($oWettbewerb->workSITE==constKommuneAnzeigeDEFAULT?'':' style="display:none"').' ><h1>Wettbewerbe</h1>'.kommune_funk_showWettbewerbe($oWettbewerb).'</div>';
	}	


	$iTmpAnzahl=(!is_array($oWettbewerb->EigeneWettbewerbe) || count($oWettbewerb->EigeneWettbewerbe)<1?0:count($oWettbewerb->EigeneWettbewerbe)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBUser\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callmeineWettbewerbe" />meine Wettbewerbe&nbsp;('.$iTmpAnzahl.')&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBUser" style="display:none"><h1 '.$cTmpFarbe.'>meine Wettbewerbe '.$oWettbewerb->wettbewerb_kurzbz.'</h1>'.kommune_funk_showMeineWettbewerbe($oWettbewerb).'</div>';
	}

	$iTmpAnzahl=(!is_array($oWettbewerb->Einladung) || count($oWettbewerb->Einladung)<1?0:count($oWettbewerb->Einladung)); 
	$iTmpAnzahl=$iTmpAnzahl+(!is_array($oWettbewerb->Forderungen) || count($oWettbewerb->Forderungen)<1?0:count($oWettbewerb->Forderungen)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBTermine\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callTerminekontrolle" />Terminekontrolle&nbsp;('.$iTmpAnzahl.')&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBTermine" style="display:none"><h1 '.$cTmpFarbe.'>Terminkontrolle '.$oWettbewerb->wettbewerb_kurzbz.'</h1>'.kommune_funk_show_wettbewerbeinladungen_forderungstage($oWettbewerb).'</div>';
	}

	$iTmpAnzahl=(!is_array($oWettbewerb->Einladung) || count($oWettbewerb->Einladung)<1?0:count($oWettbewerb->Einladung)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBEinlad\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callForderung" />Forderung&nbsp;('.$iTmpAnzahl.')&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBEinlad" style="display:none"><h1 '.$cTmpFarbe.'>Forderung '.$oWettbewerb->wettbewerb_kurzbz.'</h1>'.kommune_funk_wartung_spielergebnis($oWettbewerb,false).'</div>';		
	}

	$iTmpAnzahl=(!is_array($oWettbewerb->Forderungen) || count($oWettbewerb->Forderungen)<1?0:count($oWettbewerb->Forderungen)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBAufford\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callGeFordert" />Gefordert&nbsp;('.$iTmpAnzahl.')&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBAufford" style="display:none"><h1 '.$cTmpFarbe.'>Gefordert '.$oWettbewerb->wettbewerb_kurzbz.'</h1>'.kommune_funk_wartung_spielergebnis($oWettbewerb,true).'</div>';
	}

	$iTmpAnzahl=(!is_array($oWettbewerb->Spiele) || count($oWettbewerb->Spiele)<1?0:count($oWettbewerb->Spiele)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');
		$cTmpMenue.='<a href="#" onclick="'.$cTmpIDausblenden.'show_layer(\'idWBSpiele\');">';
			$cTmpMenue.='<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callEreignis" />Ergebnisse&nbsp;('.$iTmpAnzahl.')&nbsp;';
		$cTmpMenue.='</a>';
		$showHTML.='<div id="idWBSpiele" style="display:none"><h1 '.$cTmpFarbe.'>Ergebnisse '.$oWettbewerb->wettbewerb_kurzbz.'</h1>'.kommune_funk_show_spielergebnis($oWettbewerb).'</div>';
	}	

	if (empty($cTmpMenue))
		return '<div style="text-align:center;"><br />Keine Wettbewerbe zurzeit Online.<br />Bitte etwas Geduld.<br />Danke</div>';
	
	$showHTML='<div style="text-align:left;"  title="Login / Anmeldung :'.$cTmpName.'">&nbsp;<span style="font-size:11px;">['.$cTmpMenue.'&nbsp;]</span></div>'.$showHTML;
	return $showHTML;
}
 
#-------------------------------------------------------------------------------------------	
/* 
*
* @showMeineWettbewerbSpiele Aufbau einer bisher gespielten Wettbewerbe
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Liste der Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_wettbewerb($oWettbewerb)
{
	$Wettbewerb= new komune_wettbewerb($oWettbewerb->wbtyp_kurzbz,$oWettbewerb->wettbewerb_kurzbz);

	// WettbewerbTypen
	$oWettbewerb->WettbewerbTyp=array();
	if ($Wettbewerb->loadWettbewerbTyp())
		$oWettbewerb->WettbewerbTyp=$Wettbewerb->getWettbewerb();
	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();
	if (!isset($oWettbewerb->WettbewerbTyp[0]))
		return false;
		
	// WettbewerbTypen und Wettbewerbe
	$oWettbewerb->Wettbewerb=array();
	if ($Wettbewerb->loadWettbewerb())
		$oWettbewerb->Wettbewerb=$Wettbewerb->getWettbewerb();
	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();
	if (!isset($oWettbewerb->Wettbewerb[0]))
		return false;
	unset($Wettbewerb);
	// Wettbewerbstyp wenn nicht uebergeben wurde ermitteln zu einem Wettbewerb 	
	if (empty($oWettbewerb->wbtyp_kurzbz) && !empty($oWettbewerb->wettbewerb_kurzbz) )
	   	$oWettbewerb->wbtyp_kurzbz=$oWettbewerb->Wettbewerb[0]["wbtyp_kurzbz"];
		
	//  Moderator,Bild-Icon ermitteln und Leerzeichen aus den KeyWords entfernen
	reset($oWettbewerb->Wettbewerb);
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
		// Check Space
		$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]);
		$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]);
		$oWettbewerb->Wettbewerb[$iTmpZehler]["uid"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["uid"]);
		$oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"]='';
		// Create IMG  
		if (!empty($oWettbewerb->Wettbewerb[$iTmpZehler]["icon"]))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().'&amp;wettbewerb_kurzbz='.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'&amp;wbtyp_kurzbz='.$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"].(strlen($oWettbewerb->Wettbewerb[$iTmpZehler]["icon"])<1000?'&amp;heximg='.$oWettbewerb->Wettbewerb[$iTmpZehler]["icon"]:'');
			$oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"]='<img height="80" border="0" alt="'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" src="'.$paramURL.'" />';
		}
		// Moderator lesen zu jedem Wettbewerb : Moderator - Person-Benutzer
		$pers=kommune_funk_benutzerperson($oWettbewerb->Wettbewerb[$iTmpZehler]["uid"],$oWettbewerb);
	}
	return true;
}	
#-------------------------------------------------------------------------------------------	
/* 
*
* @skommune_funk_eigene_wettbewerb Tabellen mit den eigenen Teamdaten zu keinem,einem oder mehreren Wettbewerben
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Fehlertext oder Leer
*
*/
function kommune_funk_eigene_wettbewerb($oWettbewerb)
{
	// --- Wettbewerbe zum angemeldeten User (EINGETRAGEN)                
	$Wettbewerb=new komune_wettbewerbteam($oWettbewerb->userUID,$oWettbewerb->team_kurzbz,$oWettbewerb->wettbewerb_kurzbz);

	$oWettbewerb->EigeneWettbewerbe=array();
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->EigeneWettbewerbe=$Wettbewerb->getWettbewerbteam();
	else	
		return $oWettbewerb->Error[]=$Wettbewerb->getError();
	unset($WettbewerbTeam);

	@reset($oWettbewerb->EigeneWettbewerbe);
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->EigeneWettbewerbe);$iTmpZehler++)
	{
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["team_kurzbz"]=trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["team_kurzbz"]);
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["wettbewerb_kurzbz"]=trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["wettbewerb_kurzbz"]);
		
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["logo_image"]='';
		if (!empty($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["logo"]))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().'&amp;team_kurzbz='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["team_kurzbz"].'&amp;wettbewerb_kurzbz='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["wettbewerb_kurzbz"].(strlen($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["logo"])<1000?'&amp;heximg='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["logo"]:'');
	   		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["logo_image"]='<img height="80" border="0" alt="'.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["team_kurzbz"].'" src="'.$paramURL.'" />';
		}	
		// Anwender lesen zu jedem Wettbewerb : UID - Person-Benutzer
		$pers=kommune_funk_benutzerperson($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["uid"],$oWettbewerb);
	}

	// Suchen Wettbewerb wo der Angemeldeten Anwender (uid) angemeldet ist
	@reset($oWettbewerb->Wettbewerb);
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
	   	$oWettbewerb->Wettbewerb[$iTmpZehler]["bereits_eingetragen"]="";
		$oWettbewerb->Wettbewerb[$iTmpZehler]["daten_eingetragen"]="";
	   	if (is_array($oWettbewerb->EigeneWettbewerbe) && count($oWettbewerb->EigeneWettbewerbe)>0)
       	{
	           	reset($oWettbewerb->EigeneWettbewerbe);
       	    	for ($iTmpZehlerEX=0;$iTmpZehlerEX<count($oWettbewerb->EigeneWettbewerbe);$iTmpZehlerEX++)
            		{
				if (trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])==trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehlerEX]["wettbewerb_kurzbz"]) )
 	        	 	{
            			$oWettbewerb->Wettbewerb[$iTmpZehler]["bereits_eingetragen"]="*";
	             		$oWettbewerb->Wettbewerb[$iTmpZehler]["daten_eingetragen"]=$oWettbewerb->EigeneWettbewerbe[$iTmpZehlerEX];
				break; // Datensatz gefunden. Suche kann beendet werden
          		   	}
	             }       
		} 
	}// Ende Wettbewerb Suchen Datensatz des Angemeldeten Anwender (uid) fuer den Wettbewerb
	return true;
}	
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_showWettbewerbe Aufbau einer Tabelle aller Wettbewerbe 
*
* @param $oWettbewerb Array mit allen Wettbewerbs und Benutzerdaten
*
* @return HTML String in Listenform der Wettbewerbe
*
*/
function kommune_funk_showWettbewerbe($oWettbewerb)
{
	$showHTML='';
	if (!is_array($oWettbewerb->Wettbewerb))
		return $showHTML;
	$ctmpLast_wbtyp_kurzbz=null; // Init Gruppenwechsel	
	$itmpCount_wbtyp_kurzbz=0; // Init Gruppenwechsel	
		
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{

		// PopUP ID , und JavaScript fuer Team / Spieler
		$cTmpTeamPopUpID='sWbT'.$iTmpZehler;
		$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';

		// PopUP ID , und JavaScript fuer Wettbewerb
 		$cTmpWettbewerbPopUpID='sWb'.$iTmpZehler;
 		$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';


 		// PopUP ID , und JavaScript fuer Wettbewerb Haupzeile - Ueberschrift
 		$cTmpWettbewerbPopUpID_on='sWbon'.$iTmpZehler;
 		$cTmpWettbewerbPopUpID_off='sWboff'.$iTmpZehler;
 		$cTmpWettbewerbPopUp_on=' onclick="hide_layer(\''.$cTmpWettbewerbPopUpID.'show\');show_layer(\''.$cTmpWettbewerbPopUpID.'help\');hide_layer(\''.$cTmpWettbewerbPopUpID_on.'\');show_layer(\''.$cTmpWettbewerbPopUpID_off.'\');" ';
 		$cTmpWettbewerbPopUp_off=' style="display:none" onclick="show_layer(\''.$cTmpWettbewerbPopUpID.'show\');hide_layer(\''.$cTmpWettbewerbPopUpID.'help\');hide_layer(\''.$cTmpWettbewerbPopUpID_off.'\');show_layer(\''.$cTmpWettbewerbPopUpID_on.'\');" ';


	       // Anzahl Bilder in einer Reihe erreicht? Ja:=Neue Tabellenreihe beginnen, und Breite der TD errechnen
		$tmpAnzahlBewerbeRow=constMaxWettbwerbeZeile;
		$withBreite=100/$tmpAnzahlBewerbeRow;
		
		// Gruppenwechsel :: Wettbewerbs-Type 
		if (trim($ctmpLast_wbtyp_kurzbz)!=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]))
		{				
			// Abschluss der letzten WettbewerbsTypenGruppe bereits eine Verarbeitet wurde (nicht am Anfang)
			if ($ctmpLast_wbtyp_kurzbz!="") 
			{
				// Rest der Zeile mit TD auffuellen
				for ($itmpCount_wbtyp_kurzbz-0;$itmpCount_wbtyp_kurzbz<$tmpAnzahlBewerbeRow;$itmpCount_wbtyp_kurzbz++ )
					$showHTML.='<td style="width:'.$withBreite.'%;">&nbsp;</td>'; 

				$showHTML.='</tr></table></div>';
		       	$showHTML.='</fieldset>';				
			}	
			
			// Wettbewerb Haupzeile - Ueberschrift
			$showHTML.='<fieldset >';
		           	$showHTML.='<legend >';
					$showHTML.='<span '.$cTmpWettbewerbPopUp_on.' id="'.$cTmpWettbewerbPopUpID_on.'"><img alt="close'.$iTmpZehler.'" height="18" src="../../../skin/images/folder.gif" border="0"  title="ausblenden" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpWettbewerbPopUpID.'on_s" />'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"].'&nbsp;ausblenden</span> ';
					$showHTML.='<span '.$cTmpWettbewerbPopUp_off.' id="'.$cTmpWettbewerbPopUpID_off.'"><img alt="open'.$iTmpZehler.'" height="18" src="../../../skin/images/folderup.gif" border="0"  title="einblenden" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpWettbewerbPopUpID.'off_s" />'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"].'&nbsp;anzeigen</span> ';
				$showHTML.='</legend>';		

			$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'help">';
				$showHTML.='<h2 '.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]) && !empty($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"])?' style="background : #'.$oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"].';" ':'').'>'.$oWettbewerb->Wettbewerb[$iTmpZehler]["bezeichnung"].'</h2>';		
			$showHTML.='</div>';
			
			$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'show" style="width:100%;" title="WettbewerbContent" >
					<table class="tabcontent" summary="'.$oWettbewerb->Wettbewerb[$iTmpZehler]["bezeichnung"].'"><tr>';
					$ctmpLast_wbtyp_kurzbz=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]);
					$itmpCount_wbtyp_kurzbz=0; // Zeilenumbruch 

		} // ende Gruppenwechsel	
		elseif ($iTmpZehler!=0 && ($itmpCount_wbtyp_kurzbz%$tmpAnzahlBewerbeRow)==0 )
			$showHTML.='</tr><tr>'; 
			
		$itmpCount_wbtyp_kurzbz++;
		
		// Kennzeichen ob ein Record in tbl_wettbewerb angelegt wurde ist wbtyp_kurzbz 
		if (empty($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])) // wbtyp_kurzbz=(leer=keine wettbewerbe)
		{
			$showHTML.='<td>Es sind noch keine Gruppen verf&uuml;gbar!</td>'; 
			continue;
		}
		// Start eines Wettbewerbes	in TabellenElement (TD) mit maximaler Breite 		
		$showHTML.='<td valign="bottom" style="width:'.$withBreite.'%;">';
	# Wettbewerb	
			// Wettbewerb Link zur Pyramidenanzeige
			$showHTML.='<span>';
				$showHTML.='<img height="16" '.$cTmpWettbewerbPopUp.' title="Detailinformationen '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" style="vertical-align: bottom;" alt="infoWettbewerb'.$iTmpZehler.'" src="../../../skin/images/icon_voransicht.gif" border="0" />&nbsp;';
				$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img height="16" title="weiter zu '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" style="vertical-align: bottom;" alt="openWettbewerb'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;weiter&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'&nbsp;','weiter');
				$showHTML.=$cTmpHREF.'&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]["bereits_eingetragen"];

			$showHTML.='</span>';			

			$showHTML.='<br />';			
			// Wettbewerb ICON
	      		$showHTML.='<span>'.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"])?$oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"].'<br />':'').'</span>';
	# Team /Spieler			
			// Team / Spieler  Neuanlage oder Wartung der Daten			
			$showHTML.='<span>';
				if (empty($oWettbewerb->Wettbewerb[$iTmpZehler]["bereits_eingetragen"]))
					$showHTML.=kommune_funk_create_href(constKommuneWartungUID,'',array('team_kurzbz'=>'','wettbewerb_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'&nbsp;<img title="anmelden bei '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" style="vertical-align: bottom;" alt="open'.$iTmpZehler.'" src="../../../skin/images/NeuDokument.png" border="0" />&nbsp;anmelden&nbsp;','anmelden bei '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]);
		  		else
					$showHTML.=kommune_funk_create_href(constKommuneWartungUID,'',array('team_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz'],'wettbewerb_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img '.$cTmpTeamPopUp.' height="16" style="vertical-align: bottom;" title="bearbeiten '.$oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz'].'"  src="../../../skin/images/icon_voransicht.gif"  alt="bearbeiten '.$oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz'].'" border="0" />&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz'].'&nbsp;&auml;ndern','bearbeiten '.$oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz']).'';
			$showHTML.='</span>';					
		
			$iPopUp=true;
			// Wettbewerb PopUp ( Im Wettbewerb PopUp wird der Moderator PopUp erstellt )
			$showHTML.='<div '.$cTmpWettbewerbPopUp.' id="'.$cTmpWettbewerbPopUpID.'" style="display:none; position: absolute;z-index:100;">';
				$showHTML.=kommune_funk_popup_wettbewerb($oWettbewerb->Wettbewerb[$iTmpZehler],$oWettbewerb,$cTmpWettbewerbPopUpID.$iTmpZehler,$iPopUp);
			$showHTML.='</div>';		

			// Team / Spieler PopUp ( Im Teampopup wird auch das Wettbewerbspopup erstellt )
			$showHTML.='<div id="'.$cTmpTeamPopUpID.'" '.$cTmpTeamPopUp.' style="display:none; position: absolute;z-index:98;">';
			if (isset($oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz']))
				$showHTML.=kommune_funk_popup_wettbewerbteam($oWettbewerb->Wettbewerb[$iTmpZehler]['daten_eingetragen']['team_kurzbz'],$oWettbewerb,$cTmpTeamPopUpID.$iTmpZehler,$iPopUp);
			$showHTML.='</div>';	

	$showHTML.='</td>';
	} // Ende Kennzeichen ob ein Record in tbl_wettbewerb

	// Abschluss wenn bereits ein Gruppenwechsel erfolgte ist der Abschluss am Ende noetig (alle HTML Tags schliessen)
	if ($ctmpLast_wbtyp_kurzbz!='')
		$showHTML.='</tr></table></div></fieldset>';	
		
	$cTmpName='';	 // Anzeige des Anwendernamen 
	if (isset($oWettbewerb->EigeneWettbewerbe[0]["uid"]))
	{
		$cTmpName=trim($oWettbewerb->EigeneWettbewerbe[0]["uid"]);
		$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
		if (isset($pers->langname)) 
			$cTmpName=$pers->langname;
		$showHTML.='mit *  Makierte Wettbewerbe sind bereits abonniert von '.$cTmpName;		  
	}		
	
	$showHTML.='<br /><br /><div style="text-align:center;" class="home_logo">&nbsp;</div>'; 
	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/*
*
* @kommune_funk_showMeineWettbewerbe Aufbau einer HTML-Ausgabe-Liste der eigenen Wettbewerbe 
*
* @param $oWettbewerb->Wettbewerb Array mit den Bewerben die zur Zeit aktiv sind 
*
* @return HTML Liste der Wettbewerbe mit dem Benutzer 
*
*/
function kommune_funk_showMeineWettbewerbe($oWettbewerb)
{
	$showHTML=''; // Init
	if (!is_array($oWettbewerb->EigeneWettbewerbe) || count($oWettbewerb->EigeneWettbewerbe)<1) // DB eof
		return "keine Information gefunden";
	
	$cLastBewerb='';
	reset($oWettbewerb->EigeneWettbewerbe);
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->EigeneWettbewerbe);$iTmpZehler++)
	{
		// PopUP ID , und JavaScript fuer Wettbewerb
 		$cTmpWettbewerbPopUpID='sWbMein'.$iTmpZehler;
 		$cTmpWettbewerbPopUpID_on=$cTmpWettbewerbPopUpID.'on';
 		$cTmpWettbewerbPopUpID_off=$cTmpWettbewerbPopUpID.'off';

 		$cTmpWettbewerbPopUp_on=' onclick="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');hide_layer(\''.$cTmpWettbewerbPopUpID_on.'\');show_layer(\''.$cTmpWettbewerbPopUpID_off.'\');" ';
 		$cTmpWettbewerbPopUp_off=' style="display:none" onclick="show_layer(\''.$cTmpWettbewerbPopUpID.'\');hide_layer(\''.$cTmpWettbewerbPopUpID_off.'\');show_layer(\''.$cTmpWettbewerbPopUpID_on.'\');" ';

		// Hauptzeile - Ueberschrift
		$showHTML.='<fieldset>';
			$showHTML.='<legend>';
				$showHTML.='<span '.$cTmpWettbewerbPopUp_on.' id="'.$cTmpWettbewerbPopUpID_on.'"><img alt="close'.$iTmpZehler.'" height="18" src="../../../skin/images/folder.gif" border="0" title="ausblenden" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpWettbewerbPopUpID.'on_s" />ausblenden&nbsp;</span>';
				$showHTML.='<span '.$cTmpWettbewerbPopUp_off.' id="'.$cTmpWettbewerbPopUpID_off.'"><img alt="open'.$iTmpZehler.'" height="18" src="../../../skin/images/folderup.gif" border="0" title="einblenden" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpWettbewerbPopUpID.'off_s" />anzeigen&nbsp;</span>';
			
				$showHTML.=$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["wettbewerb_kurzbz"];
				$showHTML.='&nbsp;-&nbsp;';
				$showHTML.=$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]["team_kurzbz"];
			$showHTML.='</legend>';			
		
		$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'">';
			$showHTML.='<table class="tabcontent"  summary="Meine Wettbewerbe in Listenform"><tr>';
				$bPopUp=false; // Kein PopUp Aussehen, und Funktionen
				$showHTML.='<td>'.kommune_funk_show_wettbewerbteam($oWettbewerb->EigeneWettbewerbe[$iTmpZehler],$oWettbewerb,$cTmpWettbewerbPopUpID.$iTmpZehler,$bPopUp).'</td>';
			$showHTML.='</tr></table>';
		$showHTML.='</div>';

		$showHTML.='</fieldset>';
		$cLastBewerb=trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]['wettbewerb_kurzbz']).trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]['team_kurzbz']);
		
	}		
	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_popup_wettbewerb Anzeige eines Wettbewerbes f&uuml;r PopUp Anzeige
*
* @param $arrWettbewerb Array mit Wettbewerbe fuer die Anzeige
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit Wettbewerbesdaten
*
*/
function kommune_funk_popup_wettbewerb($arrWettbewerb,$oWettbewerb,$cSeitenKey="")
{
	$showHTML=''; // Init
	// Wenn kein Wettbewerb-Array uebergeben wurde ermitteln Wettbewerb mit dem Parameter als wettbewerb_kurzbz
	if (!is_array($arrWettbewerb) && !empty($arrWettbewerb) ) 
	{
		reset($oWettbewerb->Wettbewerb);	
	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
		{
			if (trim($arrWettbewerb)==trim($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']))
			{
				$arrWettbewerb=$oWettbewerb->Wettbewerb[$iTmpZehler];
				if (empty($oWettbewerb->wbtyp_kurzbz))
				   	$oWettbewerb->wbtyp_kurzbz=$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"];
				break;
			}	
		}	
	} // Ende ermitteln Wettbewerb wenn kein Wettbewerb uebergeben wurde
		
	// Plausib Wettbewerb-Array	
	if (!is_array($arrWettbewerb))
		return $showHTML;

	if (isset($arrWettbewerb['wbtyp_kurzbz']))
		$arrTempWettbewerb[0]=$arrWettbewerb;
	else
		$arrTempWettbewerb=$arrWettbewerb;
		 
  	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
		$cTmpFarbe=(isset($arrTempWettbewerb[$iTmpZehler]["farbe"]) && !empty($arrTempWettbewerb[$iTmpZehler]["farbe"])?$arrTempWettbewerb[$iTmpZehler]["farbe"]:'000000');
	
		$showHTML.='<fieldset style="border:3px outset #'.$cTmpFarbe.';background-color:#DDDDDD;">
	            <legend style="border:4px inset #'.$cTmpFarbe.';background-color:#FFFFF2;">&nbsp;Wettbewerb&nbsp;'.$arrTempWettbewerb[$iTmpZehler]["wbtyp_kurzbz"].'&nbsp;-&nbsp;'.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'&nbsp;</legend>';                  
				$iPopUp=true;
				$showHTML.=kommune_funk_show_wettbewerb($arrTempWettbewerb[$iTmpZehler],$oWettbewerb,$cSeitenKey,$iPopUp);
		$showHTML.='</fieldset>';	
	}		   
   	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_popup_wettbewerb Anzeige eines Wettbewerbes f&uuml;r PopUp Anzeige
*
* @param $arrWettbewerb Array mit Wettbewerbe fuer die Anzeige
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit Wettbewerbesdaten
*
*/
function kommune_funk_show_wettbewerb($arrWettbewerb,$oWettbewerb,$cSeitenKey="", $iPopUp=false)
{
	$showHTML=''; // Init
	// Wenn kein Wettbewerbe uebergeben wurde ermitteln Wettbewerb aus dem Parameter wettbewerb_kurzbz
	if (!is_array($arrWettbewerb) && !empty($arrWettbewerb) ) 
	{
		reset($oWettbewerb->Wettbewerb);	
	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
		{
			if (trim($arrWettbewerb)==trim($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']) )
			{
				$arrWettbewerb=$oWettbewerb->Wettbewerb[$iTmpZehler];
				if (empty($oWettbewerb->wbtyp_kurzbz))
				   	$oWettbewerb->wbtyp_kurzbz=$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"];
				break;
			}
		}	
	} // Ende ermitteln Wettbewerb wenn kein Wettbewerb uebergeben wurde
	#----------------------------------------------------------------------------------------------------
		
	// Plausib Wettbewerb-Array	
	if (!is_array($arrWettbewerb))
		return $showHTML;

	if (isset($arrWettbewerb['wbtyp_kurzbz']))
		$arrTempWettbewerb[0]=$arrWettbewerb;
	else
		$arrTempWettbewerb=$arrWettbewerb;
		 
  	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
		$showHTML.='<table style="font-size:11px;" summary="Wettbewerb '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">';    
			$showHTML.='<tr>
						<td colspan="2" style="text-align:right;vertical-align: top;">'.$arrTempWettbewerb[$iTmpZehler]["bezeichnung"].'</td>
					</tr>';

			$showHTML.='<tr style="vertical-align: top;">';
				if ($iPopUp)
			       	$showHTML.='<td></td>';
				else
			    	   	$showHTML.='<td><h2>'.$arrTempWettbewerb[$iTmpZehler]["wbtyp_kurzbz"].' - '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'</h2></td>';
				$showHTML.='<td style="vertical-align: top;font-size:11px;" rowspan="2">'.(isset($arrTempWettbewerb[$iTmpZehler]['icon_image'])?$arrTempWettbewerb[$iTmpZehler]['icon_image']:'').'</td>';
	            	$showHTML.='</tr>';

			// Moderator PopUp Aufruf
			$intKey='moderator_'.$cSeitenKey.'_'.time().'_'.$arrTempWettbewerb[$iTmpZehler]['wettbewerb_kurzbz'].'_'.$arrTempWettbewerb[$iTmpZehler]['uid'];
			$intKey=str_replace(' ','_',$intKey);
			$pers=kommune_funk_benutzerperson($arrTempWettbewerb[$iTmpZehler]['uid'],$oWettbewerb);

			$showHTML.='<tr><td style="vertical-align: top;">
					<table style="text-align:right;width: 280px;" cellpadding="2" cellspacing="2" summary="Wettbewerbdetail '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">
						<tr style="vertical-align: top;">
							<td colspan="2" style="text-align:left;vertical-align: top;"><b>Forderungstag(e) '.$arrTempWettbewerb[$iTmpZehler]["forderungstage"].'</b></td>
						</tr>
						<tr style="vertical-align: top;">
							<td style="text-align:right;vertical-align: top;">Moderator</td>
							<td style="text-align:left;" onmouseover="show_layer(\''.$intKey.'\');"  onmouseout="hide_layer(\''.$intKey.'\');">'
								.(isset($pers->langname)?$pers->langname:$arrTempWettbewerb[$iTmpZehler]['uid']).
							'</td>
						</tr>
						<tr style="vertical-align: top;">
							<td style="text-align:right;vertical-align:top;">'.$arrTempWettbewerb[$iTmpZehler]["wettbewerbart"].'</td>
							<td style="text-align:left;vertical-align:top;">'.$arrTempWettbewerb[$iTmpZehler]["regeln"].'</td>
						</tr>
					</table>
				</td></tr>';
	       $showHTML.='</table>';

	// Moderator PopUp
	$showHTML.='<div id="'.$intKey.'" style="display:none;position: absolute;z-index:99;">'.kommune_funk_popup_benutzer($arrTempWettbewerb[$iTmpZehler]['uid'],$oWettbewerb).'</div>';	
	}		   
   	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_eigene_wettbewerb Tabellen mit den eigenen Teamdaten zu keinem,einem oder mehreren Wettbewerben
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Fehlertext oder Leer
*
*/
function kommune_funk_teams($oWettbewerb)		
{
	// Init

	// TeamGesamt Spieler (alle Spieler zum Wettbewerb)
	// WettbewerbTeam Classe initialisieren
	$Wettbewerb=new komune_wettbewerbteam('','','');

	$Wettbewerb->InitWettbewerbteam();
	$Wettbewerb->setUid('');
	$Wettbewerb->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->TeamGesamt=$Wettbewerb->getWettbewerbteam();
   	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();
		
	// TeamMitglieder lesen aus Person-Benutzer
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->TeamGesamt);$iTmpZehler++)
	{
		$oWettbewerb->TeamGesamt[$iTmpZehler]["uid"]=trim($oWettbewerb->TeamGesamt[$iTmpZehler]["uid"]);
		$oWettbewerb->TeamGesamt[$iTmpZehler]["team_kurzbz"]=trim($oWettbewerb->TeamGesamt[$iTmpZehler]["team_kurzbz"]);
		$oWettbewerb->TeamGesamt[$iTmpZehler]["wettbewerb_kurzbz"]=trim($oWettbewerb->TeamGesamt[$iTmpZehler]["wettbewerb_kurzbz"]);

		// Laden Benutzer - Person wenn noch nicht vorhanden
		$cShowImage='';
		$pers=kommune_funk_benutzerperson($oWettbewerb->TeamGesamt[$iTmpZehler]["uid"],$oWettbewerb);
		if (isset($pers->foto_image) && !empty($pers->foto_image))
			$cShowImage=$pers->foto_image;
		$oWettbewerb->TeamGesamt[$iTmpZehler]['foto_image']=$cShowImage;
			
		// Erzeugen HREF zum Team LogoIcon
		$oWettbewerb->TeamGesamt[$iTmpZehler]['logo_image']='';
		if (!empty($oWettbewerb->TeamGesamt[$iTmpZehler]["logo"]))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().'&amp;team_kurzbz='.$oWettbewerb->TeamGesamt[$iTmpZehler]["team_kurzbz"].'&amp;wettbewerb_kurzbz='.$oWettbewerb->TeamGesamt[$iTmpZehler]["wettbewerb_kurzbz"].(strlen($oWettbewerb->TeamGesamt[$iTmpZehler]["logo"])<1000?'&amp;heximg='.$oWettbewerb->TeamGesamt[$iTmpZehler]["logo"]:'');
   			$oWettbewerb->TeamGesamt[$iTmpZehler]['logo_image']='<img height="80" border="0" alt="'.$oWettbewerb->TeamGesamt[$iTmpZehler]["team_kurzbz"].'" src="'.$paramURL.'" />';
		}
	}	
	return true;	
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_TeamAnwenders Tabellen mit den Anwender in den Teamdaten
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Fehlertext oder Leer
*
*/
function kommune_funk_anwenderteams($oWettbewerb)		
{
	// Init
	$showHTML='';
	// TeamGesamt Spieler (alle Spieler zum Wettbewerb)
	// WettbewerbTeam Classe initialisieren
	$Wettbewerb=new komune_wettbewerbteam('','','');

	$Wettbewerb->InitWettbewerbteam();
	$Wettbewerb->setUid($oWettbewerb->userUID);
	$Wettbewerb->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);
	
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->TeamAnwender=$Wettbewerb->getWettbewerbteam();
   	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();


	if (!is_array($oWettbewerb->TeamAnwender) || count($oWettbewerb->TeamAnwender)<1)
		return false;

	// TeamMitglieder lesen aus Person-Benutzer
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->TeamAnwender);$iTmpZehler++)
	{
		$oWettbewerb->TeamAnwender[$iTmpZehler]["uid"]=trim($oWettbewerb->TeamAnwender[$iTmpZehler]["uid"]);
		$oWettbewerb->TeamAnwender[$iTmpZehler]["team_kurzbz"]=trim($oWettbewerb->TeamAnwender[$iTmpZehler]["team_kurzbz"]);
		$oWettbewerb->TeamAnwender[$iTmpZehler]["wettbewerb_kurzbz"]=trim($oWettbewerb->TeamAnwender[$iTmpZehler]["wettbewerb_kurzbz"]);
		
		$cShowImage='';
		$pers=kommune_funk_benutzerperson($oWettbewerb->TeamAnwender[$iTmpZehler]["uid"],$oWettbewerb);
		if (isset($pers->foto_image) && !empty($pers->foto_image))
       		$cShowImage=$pers->foto_image;
		$oWettbewerb->TeamAnwender[$iTmpZehler]['foto_image']=$cShowImage;

		// Erzeugen HREF zum Team LogoIcon
		$oWettbewerb->TeamAnwender[$iTmpZehler]['logo_image']='';
		if (!empty($oWettbewerb->TeamAnwender[$iTmpZehler]["logo"]))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().'&amp;team_kurzbz='.$oWettbewerb->TeamAnwender[$iTmpZehler]["team_kurzbz"].'&amp;wettbewerb_kurzbz='.$oWettbewerb->TeamAnwender[$iTmpZehler]["wettbewerb_kurzbz"].(strlen($oWettbewerb->TeamAnwender[$iTmpZehler]["logo"])<1000?'&amp;heximg='.$oWettbewerb->TeamAnwender[$iTmpZehler]["logo"]:'');
   			$oWettbewerb->TeamAnwender[$iTmpZehler]['logo_image']='<img height="80" border="0" alt="'.$oWettbewerb->TeamAnwender[$iTmpZehler]["team_kurzbz"].'" src="'.$paramURL.'" />';
		}

	}	
	return true;	
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_teambenutzer Baut das Array Team auf, 
*	und makiert das Array TeamGesamt wenn a) der Anwender in diesem Wettbewerb ist
*		 , und nicht noch Offene Spiele hat (wichtig in der Pyramide)
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Fehlertext oder Leer
*
*/
function kommune_funk_teambenutzer($oWettbewerb)		
{
	// Init
	$showHTML='';

	$oWettbewerb->Team=array();	
	$oWettbewerb->TeamBenutzer=array();
	
	// Check des Teams nur Notwendig wenn bereits zu diesem Wettbewerb ein Eintrag vorhanden ist
	if (!is_array($oWettbewerb->TeamGesamt) )	
		return $showHTML;
		
	for ($zeileIND=0;$zeileIND<count($oWettbewerb->TeamGesamt);$zeileIND++)
        {		
		$cTeam_kurzbz=trim($oWettbewerb->TeamGesamt[$zeileIND]['team_kurzbz']);
		$cWettbewerb_kurzbz=trim($oWettbewerb->TeamGesamt[$zeileIND]['wettbewerb_kurzbz']);
		
		// Vergleiche Aktuelles Team mit dem Angemeldeten User sein Team
		if (isset($oWettbewerb->EigeneWettbewerbe[0]['team_kurzbz']) 
		&& trim($oWettbewerb->EigeneWettbewerbe[0]['team_kurzbz'])==trim($cTeam_kurzbz) )
			$oWettbewerb->TeamGesamt[$zeileIND]['team_aktiv']=true;
		else
			$oWettbewerb->TeamGesamt[$zeileIND]['team_aktiv']=false;

		// Es gibt noch Offene Bewerbe - Keine Einladungen moeglich
		if ( (is_array($oWettbewerb->Forderungen) && count($oWettbewerb->Forderungen)>0)
		|| (is_array($oWettbewerb->Einladung) && count($oWettbewerb->Einladung)>0))
			$oWettbewerb->TeamGesamt[$zeileIND]['team_aktiv']=false;
				
		// je Team eine Gruppe der Spieler bilden (Array)
		$oWettbewerb->TeamBenutzer[$cTeam_kurzbz][]=$oWettbewerb->TeamGesamt[$zeileIND];
		$oWettbewerb->Team[$cTeam_kurzbz]=$oWettbewerb->TeamGesamt[$zeileIND];
	  }	
	return $showHTML;	
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_popup_wettbewerbteam Anzeige eines Wettbewerbteam f&uuml;r PopUp Anzeige
*
* @param $arrWettbewerb Array mit Wettbewerbeteam fuer die Anzeige
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit WettbewerbTeamdaten
*
*/
function kommune_funk_popup_wettbewerbteam($arrWettbewerbTeam,$oWettbewerb,$cSeitenKey="")
{
	$showHTML=''; // Init
	if (!is_array($arrWettbewerbTeam) && !empty($arrWettbewerbTeam) ) 
	{
		reset($oWettbewerb->TeamAnwender);	

	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->TeamAnwender);$iTmpZehler++)
		{
			if (trim($arrWettbewerbTeam)==trim($oWettbewerb->TeamAnwender[$iTmpZehler]['team_kurzbz']) )
			{
				$arrWettbewerbTeam=$oWettbewerb->TeamAnwender[$iTmpZehler];
				break;
			}
		}	
		if (!is_array($arrWettbewerbTeam)) // Keine Teamdaten
		{
		$Wettbewerb=new komune_wettbewerbteam('',$arrWettbewerbTeam,'');
		if ($Wettbewerb->loadWettbewerbteam())
			$arrWettbewerbTeam=$Wettbewerb->getWettbewerbteam();
		}	
	}
	if (!is_array($arrWettbewerbTeam))
		return $showHTML;

	if (isset($arrWettbewerbTeam['team_kurzbz']))
		$arrTempWettbewerb[0]=$arrWettbewerbTeam;
	else
		$arrTempWettbewerb=$arrWettbewerbTeam;
		 
  	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
		$showHTML.='<fieldset style="border:1px outset Black;background-color:#DDDDDD;">';
			$showHTML.='<legend style="border:2px outset Black;background-color:#FFFFF2;">'.(count($oWettbewerb->TeamAnwender)>1?'Team':'Spieler').'&nbsp;Informationen</legend>';
				$iPopUp=true;
				$showHTML.=kommune_funk_show_wettbewerbteam($arrWettbewerbTeam,$oWettbewerb,$cSeitenKey.$iTmpZehler.$iTmpZehler,$iPopUp);
		 $showHTML.='</fieldset>';	
	}		   
   	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_show_wettbewerbteam Anzeige eines Wettbewerbteam f&uuml;r PopUp Anzeige
*
* @param $arrWettbewerb Array mit Wettbewerbeteam fuer die Anzeige
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit WettbewerbTeamdaten
*
*/
function kommune_funk_show_wettbewerbteam($arrWettbewerbTeam,$oWettbewerb,$cSeitenKey="",$iPopUp=false)
{
	$showHTML=''; // Init

	if (!is_array($arrWettbewerbTeam))
		return $showHTML;

	if (isset($arrWettbewerbTeam['team_kurzbz'])) // Es wurde nur ein Record uebergeben diesen umwandeln in ein RecordArray
		$arrTempWettbewerb[0]=$arrWettbewerbTeam;
	else
		$arrTempWettbewerb=$arrWettbewerbTeam;
		
	// Wettbewerb zum Team suchen
  	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
		reset($oWettbewerb->Wettbewerb);	
		for ($iTmpZehler2=0;$iTmpZehler2<count($oWettbewerb->Wettbewerb);$iTmpZehler2++)
		{
			if (trim($oWettbewerb->Wettbewerb[$iTmpZehler2]["wettbewerb_kurzbz"])==trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]) )
			{
				$oWettbewerb->Wettbewerb[$iTmpZehler2]["wbtyp_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler2]["wbtyp_kurzbz"]);
				$arrTempWettbewerb[$iTmpZehler]=array_merge($oWettbewerb->Wettbewerb[$iTmpZehler2],$arrTempWettbewerb[$iTmpZehler]);
				break;
			}
		}	

		$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]=trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]);
		if (isset($oWettbewerb->Team[$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]]))
			$arrTempWettbewerb[$iTmpZehler]=array_merge($oWettbewerb->Team[$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]],$arrTempWettbewerb[$iTmpZehler]);

		$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]=trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]);
		$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]=trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]);

		$showHTML.='<table class="tabcontent2" style="text-align:left;" summary="Wettbewerbteam '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].' '.$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"].'">';    

		// PopUp Key Init
		$intKey='st_'.$cSeitenKey.$iTmpZehler;
		
		// Wettbewerb PopUp
		$cTmpWettbewerbPopUpID=$intKey.'_Wettbewerb'.$iTmpZehler;
		$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';

		// Spieler PopUp
		$cTmpUserPopUpID=$intKey.'_sp'.$iTmpZehler;
		$cTmpUserPopUp=' onmouseover="show_layer(\''.$cTmpUserPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpUserPopUpID.'\');" ';


		// Spieler Detailinformation
		$pers=kommune_funk_benutzerperson(trim($arrTempWettbewerb[$iTmpZehler]["uid"]),$oWettbewerb);
		
		$showHTML.='<tr style="vertical-align: top;">';
   		$showHTML.='<td style="'.(!$iPopUp?' width:300px;':'').'border:2px inset  '.(!empty($arrTempWettbewerb[$iTmpZehler]["farbe"])?' #'.$arrTempWettbewerb[$iTmpZehler]["farbe"].' ':'black').';">
			<table cellpadding="2" cellspacing="5" summary="Wettbewerbteamdetail'.$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"].' '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">
              	<tr style="vertical-align: top;white-space : nowrap;">
		       		<td style="white-space : nowrap;"><h3 '.$cTmpWettbewerbPopUp.'>'.$arrTempWettbewerb[$iTmpZehler]["wbtyp_kurzbz"].'  '.kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('team_kurzbz'=>$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"],'wettbewerb_kurzbz'=>$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img height="14" style="vertical-align: bottom;" title="weiter zu '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'"  src="../../../skin/images/open.gif"  alt="weiter zu '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" border="0" />&nbsp;'.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'</h3>';
		// Wettbewerb PopUp ( Im Wettbewerb PopUp wird der Moderator PopUp erstellt )
			$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'" '.$cTmpWettbewerbPopUp.' style="display:none; position: absolute;z-index:99;">';
					$showHTML.=kommune_funk_popup_wettbewerb($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],$oWettbewerb,$cTmpWettbewerbPopUpID,true);
			$showHTML.='</div>';					
		
			$showHTML.='</td>
						<td colspan="2"  style="vertical-align: top;text-align:left;white-space : nowrap;">'.(!empty($arrTempWettbewerb[$iTmpZehler]["logo_image"])?$arrTempWettbewerb[$iTmpZehler]["logo_image"]:'').'
							<div id="'.$cTmpUserPopUpID.'" style="display:none;position: absolute;z-index:99;">'.
								kommune_funk_popup_benutzer($arrTempWettbewerb[$iTmpZehler]['uid'],$oWettbewerb)
							.'</div>
						</td>

				</tr>';
				
		$showHTML.='<tr style="vertical-align: top;">
		       	<td><h3>';
		$showHTML.=kommune_funk_create_href(constKommuneWartungUID,'',array('team_kurzbz'=>$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"],'wettbewerb_kurzbz'=>$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img height="14" style="vertical-align: bottom;" title="bearbeiten '.$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"].'"  src="../../../skin/images/file.gif"  alt="bearbeiten '.$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"].'" border="0" />&nbsp;'.$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"],$arrTempWettbewerb[$iTmpZehler]["team_kurzbz"]);
		$showHTML.='&nbsp;</h3></td>';
		$showHTML.='</tr>
				  	  <tr style="vertical-align: top;font-size:11px;">
						<td style="vertical-align: top;text-align:right;">Mitglied</td>
						<td style="text-align:left;" '.$cTmpUserPopUp.'>'.(isset($pers->langname)?$pers->langname:$arrTempWettbewerb[$iTmpZehler]['uid']).'</td>
					</tr>
					<tr style="vertical-align: top;font-size:11px;">
						<td style="vertical-align: top;text-align:right;">Rang</td>
						<td style="text-align:left;">'.$arrTempWettbewerb[$iTmpZehler]["rang"].'</td>
					</tr>
					<tr style="vertical-align: top;font-size:11px;">
          	          	<td style="text-align:right;">Punkte</td>
						<td style="text-align:left;">'.$arrTempWettbewerb[$iTmpZehler]["punkte"].'</td>
					</tr>

           				<tr style="vertical-align: top;font-size:10px;">
						<td style="vertical-align: top;text-align:right;">Bezeichnung</td>
						<td style="text-align:left;">'.$arrTempWettbewerb[$iTmpZehler]["bezeichnung"].'</td>
					</tr>
           			<tr style="vertical-align: top;font-size:10px;">
						<td style="vertical-align: top;text-align:right;">Beschreibung</td>
						<td style="text-align:left;">'.$arrTempWettbewerb[$iTmpZehler]["beschreibung"].'</td>
					</tr>';
				$showHTML.='</table>
				 </td>';
				 
				// alle Mitglieder - Spieler anzeigen	 
		     	$showHTML.='<td style="vertical-align: top;border:1px outset transparent;">';

				if ( ($iPopUp==2)  && isset($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"])]) 
				&& count($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"])])>0 )
				{
			     	$showHTML.='<table summary="Wettbewerbteamdetail'.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">
						<tr style="vertical-align: top;">
							<td><h3>Mitglieder</h3></td>
						</tr>
						<tr style="vertical-align: top;">
							<td style="white-space : nowrap;">';
							$intKey='mitgl_'.$cSeitenKey;
							$intKey=str_replace('\\','_',str_replace(' ','_',$intKey)).$iTmpZehler;
							$showHTML.=kommune_funk_show_wettbewerbteam_mitglied($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"])],$oWettbewerb,$intKey);  

							if (count($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"])])==1 )
								$showHTML.='<br />'.$oWettbewerb->TeamBenutzer[trim($arrTempWettbewerb[$iTmpZehler]["team_kurzbz"])][0]['foto_image'];
						
					$showHTML.='</td>
						</tr>
					</table>';

			}
			$showHTML.='&nbsp;</td>';

		 // Spielergebnisse nur wenn es kein PopUp ist
		if (!$iPopUp && $iPopUp!=2)
			$showHTML.='<td style="vertical-align: top;"><div style="overflow : auto;">'.kommune_funk_show_wettbewerbteam_spiele($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],'',$oWettbewerb).'</div>&nbsp;</td>';
		$showHTML.='</tr>';
    	$showHTML.='</table>'; // Ende kommune_funk_show_wettbewerbteam
	}		   
   	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_show_wettbewerbteam_mitglied Anzeige der Spieler des Wettbewerbteams f&uuml;r PopUp Anzeige
*
* @param $arrWettbewerb Array mit Wettbewerbeteam fuer die Anzeige
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit WettbewerbTeamdaten
*
*/
function kommune_funk_show_wettbewerbteam_mitglied($arrWettbewerbTeam,$oWettbewerb,$cSeitenKey="")
{
	$showHTML=''; // Init
	
	if (!is_array($arrWettbewerbTeam))
		return $showHTML;

	$showHTML.='<table summary="Wettbewerbteam Personen und Benutzer">';    
  	for ($iTmpZehler=0;$iTmpZehler<count($arrWettbewerbTeam);$iTmpZehler++)
	{
		$cTmpName=$arrWettbewerbTeam[$iTmpZehler]["uid"];
		$pers=kommune_funk_benutzerperson($arrWettbewerbTeam[$iTmpZehler]["uid"],$oWettbewerb);
		if (isset($pers->nachname) && !empty($pers->nachname)) 
			$cTmpName=$pers->langname;
			
		// User PopUp
		$intKey='benutzer_'.$cSeitenKey.'_'.time().'_kommune_funk_show_wettbewerbteam_mitglied_'.$arrWettbewerbTeam[$iTmpZehler]["uid"];
		$cTmpTeamPopUpID=str_replace('\\','_',str_replace(' ','_',$intKey));
		$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';
		
		$showHTML.='<tr style="vertical-align: top;" '.$cTmpTeamPopUp.'>';
  	 	      $showHTML.='<td style="vertical-align: bottom;">';
			      	 $showHTML.='<img alt="Person'.$iTmpZehler.'" src="../../../skin/images/person.gif"  border="0" />'.$cTmpName;
  	 	      $showHTML.='</td>';
		$showHTML.='</tr>';
		$showHTML.='<tr style="vertical-align: top;">';
			$showHTML.='<td>';
				$showHTML.='<div id="'.$cTmpTeamPopUpID.'" style="display:none;position: absolute;z-index:99;">'.kommune_funk_popup_benutzer($arrWettbewerbTeam[$iTmpZehler]['uid'],$oWettbewerb).'</div>';	
			$showHTML.='</td>';
		$showHTML.='</tr>';
	}		   
	$showHTML.='</table>';
   	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_show_wettbewerbeinladungen_forderungstage Anzeige eines Wettbewerbteam f&uuml;r PopUp Anzeige
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit WettbewerbTeamdaten
*
*/
function kommune_funk_show_wettbewerbeinladungen_forderungstage($oWettbewerb)
{

		$showHTML=''; // Init
		$showHTML.='<fieldset>';
           	$showHTML.='<legend>Terminkontrolle</legend>';		
			$showHTML.='<h2>Termin - &Uuml;berwachung</h2>';		

	$WettbewerbTermine= new komune_wettbewerbeinladungen();
	
	$WettbewerbTermine->initWettbewerbeinladungen();
	$WettbewerbTermine->setTeam_gefordert($oWettbewerb->EigeneWettbewerbe);	
	$arrTempWettbewerbGefordertTermine=array();
 
	if ($WettbewerbTermine->loadWettbewerbeinladungenForderungstage())
		$arrTempWettbewerbGefordertTermine=$WettbewerbTermine->getWettbewerbeinladung();
	else
		$oWettbewerb->Error[]=$WettbewerbTermine->getError();

	$WettbewerbTermine->initWettbewerbeinladungen();
	$WettbewerbTermine->setTeam_forderer($oWettbewerb->EigeneWettbewerbe);	
	$arrTempWettbewerbFordertTermine=array();
	if ($WettbewerbTermine->loadWettbewerbeinladungenForderungstage())
		$arrTempWettbewerbFordertTermine=$WettbewerbTermine->getWettbewerbeinladung();
	else
		$oWettbewerb->Error[]=$WettbewerbTermine->getError();


	if (isset($WettbewerbTermine)) unset($WettbewerbTermine);

	// Forderungen und Geforderte - Daten zusammenfuehren	
	if (!is_array($arrTempWettbewerbFordertTermine)) $arrTempWettbewerbFordertTermine=array();
	if (!is_array($arrTempWettbewerbGefordertTermine)) $arrTempWettbewerbGefordertTermine=array();
	$arrTempWettbewerbTermine=array_merge($arrTempWettbewerbFordertTermine,$arrTempWettbewerbGefordertTermine);

	$showHTML.='<table cellpadding="1" cellspacing="1" style="background-color: black;text-align:left;" summary="Wettbewerb Forderungstage">';    
		$showHTML.='<tr>
			<td style="color:back;background-color: #DDDDDD;" colspan="2"> Wettbewerb </td>
			<td style="color:back;background-color: #DDDDDD;"> Forderer </td>
			<td style="color:back;background-color: #DDDDDD;"> Gefordert </td>
			<td style="color:back;background-color: #DDDDDD;"> Tage </td>
			<td style="color:back;background-color: #DDDDDD;"> Datum / Zeit </td>
			<td style="color:back;background-color: #DDDDDD;"> Status </td>
			<td style="color:back;background-color: #DDDDDD;"> &Uuml;berpr&uuml;fungsdatum '.date("d.m.Y", mktime(0,0,0,date("m"),date("d"),date("y"))).'</td>			
			</tr>';	

	reset($arrTempWettbewerbTermine);
  	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerbTermine);$iTmpZehler++)
	{

		$arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]=trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]);
		$arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"]=trim($arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"]);
		$arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"]=trim($arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"]);
		$arrTempWettbewerbTermine[$iTmpZehler]["team_sieger"]=trim($arrTempWettbewerbTermine[$iTmpZehler]["team_sieger"]);
		
		$cTmpTeamPopUpID1='TT1'.$iTmpZehler;
		$cTmpTeamPopUp1=' onmouseover="show_layer(\''.$cTmpTeamPopUpID1.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID1.'\');" ';

		$cTmpTeamPopUpID2='TT2'.$iTmpZehler;
		$cTmpTeamPopUp2=' onmouseover="show_layer(\''.$cTmpTeamPopUpID2.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID2.'\');" ';

		$cTmpTeamPopUpID3='WT1'.$iTmpZehler;
		$cTmpTeamPopUp3=' onmouseover="show_layer(\''.$cTmpTeamPopUpID3.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID3.'\');" ';

		$cTmpFarbe=(isset($arrTempWettbewerbTermine[$iTmpZehler]["farbe"]) && !empty($arrTempWettbewerbTermine[$iTmpZehler]["farbe"])?$arrTempWettbewerbTermine[$iTmpZehler]["farbe"]:'transparent');

		$showHTML.='<tr style="background-color:#F2F2F2;vertical-align: top;" >

			<td style="color:back;background-color: #FFFFB0;border:2px solid #'.$cTmpFarbe.';vertical-align: top;" >'.$arrTempWettbewerbTermine[$iTmpZehler]["wbtyp_kurzbz"].'&nbsp;</td>
			<td style="color:back;background-color: #FFFFB0;vertical-align: top;" '.$cTmpTeamPopUp3.'>'.kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"])),'<img title="weiter '.trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]).'" style="vertical-align: bottom;" alt="open_termin_'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;'.trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]),trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"])).'&nbsp;</td>
			<td style="color:back;background-color: #FFFFB0;vertical-align: top;" '.$cTmpTeamPopUp1.'>'.$arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"].'&nbsp;</td>
			<td style="color:back;background-color: #FFFFB0;vertical-align: top;" '.$cTmpTeamPopUp2.'>'.$arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"].'&nbsp;</td>
			<td style="color:back;background-color: #FFFFB0;vertical-align: top;" >'.$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"].'&nbsp;</td>
			';

		$cTmpDatumSuche='<table summary="Datum, und Taetigkeit Information ">';
		
		if (!empty($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamumdatum"]))
			$cTmpDatumSuche.='<tr><td>Forderungsbeginn</td><td>'.$arrTempWettbewerbTermine[$iTmpZehler]["gefordertamumdatum"].'</td></tr>';	

		if (!empty($arrTempWettbewerbTermine[$iTmpZehler]["bestaetigtamum"]))
		{
			$cTmpDatumSuche.='<tr><td>Forderungbest&auml;tigt</td><td>'.$arrTempWettbewerbTermine[$iTmpZehler]["bestaetigtdatum"].'</td></tr>';	
			// Das MatchbestaetigtDatum gilt erst nach der Bestaetigung
			
			if (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit"]))
				$cTmpDatumSuche.='<tr><td>Spieldatum</td><td>'.$arrTempWettbewerbTermine[$iTmpZehler]["matchdatum"].'</td></tr>';	
			
			if (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchbestaetigtamum"])
			&& !empty($arrTempWettbewerbTermine[$iTmpZehler]["ergebniss"]))
				$cTmpDatumSuche='<tr><td>Spielbest&auml;tigt</td><td>'.$arrTempWettbewerbTermine[$iTmpZehler]["matchbestaetigtdatum"].'</td></tr>';	
		}
		elseif (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_tag_diff"])) 
 		{
			if (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit"]))
				$cTmpDatumSuche.='<tr><td>Spieldatum</td><td>'.$arrTempWettbewerbTermine[$iTmpZehler]["matchdatum"].'</td></tr>';	
		
		}
		$cTmpDatumSuche.='</table>';
		
		$showHTML.='<td style="vertical-align: top;" >'.$cTmpDatumSuche.'&nbsp;</td>';	

#exit($oWettbewerb->Einladung[0]['gefordertamum']." --- ".strtotime($oWettbewerb->Einladung[0]['gefordertamum']) ." #### ".time());			
			
		$bTmpFehlerNummerGefunden=false; // Init Check Eingabe nicht vollstaendig 
	// Check die Bestaetigung des Forderungsdatum 
	// 	Fehler : wenn keine Bestaetigung der Forderung [bestaetigtdatum] eingegeben wurde
	// 	und das Tagesdatum kleiner Forderungsdatum [gefordertamum] plus [forderungstage] 
	//
	//	,extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) as gefordertamum_diff
	//	,extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum,tbl_match.gefordertamum))) as bestaetigtamum_diff
	//
		if (empty($arrTempWettbewerbTermine[$iTmpZehler]["bestaetigtdatum"]) 
		&& !empty($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"]) 
		&& $arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"]>$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"] )
			$bTmpFehlerNummerGefunden=1;

#		,extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit,tbl_match.bestaetigtamum))) as matchdatumzeit_diff ";
			
		// Check Eintrag des Ergebnis 
		// 	Fehler : wenn kein Team-Sieger eingetragen wurde 
		//	und das  Matchdatum [matchdatumzeit]  plus [forderungstage] kleiner Tagesdatum ist
		if (empty($arrTempWettbewerbTermine[$iTmpZehler]["team_sieger"]) 
		&& (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_tag_diff"]) 
		&& $arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_tag_diff"]>$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]) )
			$bTmpFehlerNummerGefunden=2;

#		,extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtdatum,tbl_match.matchdatumzeit))) as matchbestaetigtamum_diff ";
			
		// Check Bestaetigen MatchErgebnis 
		// 	Fehler : wenn der Team-Sieger nach dem eingetragen nicht bestaetigt wurde 
		//	und das Tagesdatum kleiner Matchdatum [matchdatumzeit]  plus [forderungstage]
		if (empty($arrTempWettbewerbTermine[$iTmpZehler]["matchbestaetigtdatum"]) 
		&& !empty($arrTempWettbewerbTermine[$iTmpZehler]["team_sieger"])
		&& (!empty($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_diff"]) 
		|| $arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_diff"]>$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]) )
			$bTmpFehlerNummerGefunden=3;


		$showHTML.='<td style="vertical-align: top;" >';
		if ($bTmpFehlerNummerGefunden)
			$showHTML.='<img height="18" style="vertical-align: bottom;" title="OK"  src="../../../skin/images/red_point.gif"  alt="red_point.gif" />';
		else
			$showHTML.='<img height="18" style="vertical-align: bottom;" title="OK"  src="../../../skin/images/green_point.gif"  alt="green_point.gif" />';
		$showHTML.='</td>';

		$showHTML.='<td  style="vertical-align: top;">';	
			if ($bTmpFehlerNummerGefunden==1)
			{
				$cTmpDelDate=date("d.m.Y", mktime(0,0,0,date("m"),date("d") + ($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"] -( $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"] * 2 )) ,date("y")));
			
				$showHTML.='Achtung! Die Forderung wurde noch nicht best&auml;tigt von <b '.$cTmpTeamPopUp2.'>'.$arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"] .'</b>
				<br /> innerhalb von '.$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"].' Tag(en).
				<br />Die Best&auml;tigung sollte vor '. ($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"] - $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]).' Tag(en) erfolgen.
				';
#exit($cTmpDelDate ." ". $arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum"]." ".$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]." ".$arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"]);

				if ( ($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"] - $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"])+1  )
				{
					$WettbewerbTermine= new komune_wettbewerbeinladungen();
					$WettbewerbTermine->initWettbewerbeinladungen();
					$WettbewerbTermine->setMatch_id(trim($arrTempWettbewerbTermine[$iTmpZehler]["match_id"]));	
					$arrTempWettbewerbGefordertTermine=array();
					
 					if ($WettbewerbTermine->unloadWettbewerbeinladungen())
					{
						$showHTML.='<br />Die Forderung wurde automatisch <b>gel&ouml;scht</b> Ablaufdatum war der '.$cTmpDelDate;

						$cTmpName=trim($arrTempWettbewerbTermine[$iTmpZehler]['uid']);
						$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
						if (isset($pers->langname)) 
							$cTmpName=$pers->langname;
				

						$cTmpName1=trim($arrTempWettbewerbTermine[$iTmpZehler]['team_gefordert']);
						$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
						if (isset($pers->langname)) 
								$cTmpName1=$pers->langname;				

						$cTmpName2=trim($arrTempWettbewerbTermine[$iTmpZehler]['team_forderer']);
						$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
						if (isset($pers->langname)) 
								$cTmpName2=$pers->langname;				
				
						$betreff='Die Forderung im Wettbewerb '.$arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"] ." wurde entfernt";
						$text="Die Forderung wurde nicht in der vorgegebenen Zeit angenommen (".$arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"] .") sie wird entfernt.\n\n";
						$text.="Die Forderung von ".$cTmpName2 ." ( Kurzzeichen ".$arrTempWettbewerbTermine[$iTmpZehler]['team_forderer']." )\n\n";
						$text.="an Geforderten von ".$cTmpName1 ." ( Kurzzeichen ".$arrTempWettbewerbTermine[$iTmpZehler]['team_gefordert']." )\n\n";
						$text.=" kann noch mal beantragt werden."."\n\n";
						$text.="Ihr Moderator ".$cTmpName."\n\n";
						// Einladung an Spieler/Team Information
						$oWettbewerb->Error[]=kommune_funk_sendmail($arrTempWettbewerbTermine[$iTmpZehler]['team_forderer'],$betreff,$text,$arrTempWettbewerbTermine[$iTmpZehler]['uid'],$oWettbewerb);
						$oWettbewerb->Error[]=kommune_funk_sendmail($arrTempWettbewerbTermine[$iTmpZehler]['team_gefordert'],$betreff,$text,$arrTempWettbewerbTermine[$iTmpZehler]['uid'],$oWettbewerb);
						$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->userUID,$betreff,$text,$arrTempWettbewerbTermine[$iTmpZehler]['uid'],$oWettbewerb);
						
					}						
					else
						$oWettbewerb->Error[]=$WettbewerbTermine->getError();					
					#$showHTML.=$WettbewerbTermine->getStringSQL();		
					if (isset($WettbewerbTermine)) unset($WettbewerbTermine);

				}	
				else				
					$showHTML.='<br />Die Forderung wird automatisch <b>gel&ouml;scht</b> in '. ($arrTempWettbewerbTermine[$iTmpZehler]["gefordertamum_diff"] - $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"])+2 ." Tag(en)";
			}
			if ($bTmpFehlerNummerGefunden==2) // matchbestaetigtamum mit matchdatumzeit
			{
				$showHTML.='Achtung! Das Spielergebnis wurde noch nicht eingetragen von <b '.$cTmpTeamPopUp1.'>'.$arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"] .'</b>.
				<br />Das Ergebnis zum Spieldatum '.$arrTempWettbewerbTermine[$iTmpZehler]["matchdatum"].' sollte sp�testens vor '. ($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_tag_diff"] - $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]).' Tag(en) erfolgen.';
				$showHTML.='<br />Das <b>Ergebnis '.$arrTempWettbewerbTermine[$iTmpZehler]["ergebniss"].'</b> bitte <b>erfassen</b>! ';
				$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;match_id='.trim($arrTempWettbewerbTermine[$iTmpZehler]["match_id"]).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"]);

				$showHTML.=' <a title="'.$arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"] .'erfassen Ergebnis '.$arrTempWettbewerbTermine[$iTmpZehler]["ergebniss"].'" target="_parent" href="'.$paramURL.'">weiter zum Ergebnis erfassen </a>';
			}			
			
			if ($bTmpFehlerNummerGefunden==3) // matchbestaetigtamum mit matchdatumzeit
			{
				$showHTML.='Achtung! Das <b>Spielergebnis</b> wurde noch nicht best&auml;tigt von <b '.$cTmpTeamPopUp2.'>'.$arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"] .'</b>.
				<br />Die Best&auml;tigung f&uuml;r das Spiel am '.$arrTempWettbewerbTermine[$iTmpZehler]["matchdatum"].' sollte sp&auml;testens vor '. ($arrTempWettbewerbTermine[$iTmpZehler]["matchdatumzeit_tag_diff"] - $arrTempWettbewerbTermine[$iTmpZehler]["forderungstage"]).' Tag(en) erfolgen.';
				$showHTML.='<br />Das Ergebnis <b>'.$arrTempWettbewerbTermine[$iTmpZehler]["ergebniss"].'</b> bitte <b>best&auml;tigen</b>! ';
				$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;match_id='.trim($arrTempWettbewerbTermine[$iTmpZehler]["match_id"]).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"]);

				$showHTML.='<br /><a title="best&auml;tigen '.$arrTempWettbewerbTermine[$iTmpZehler]["ergebniss"].'" target="_parent" href="'.$paramURL.'">weiter zur Best&auml;tigung</a>';
			}		

			$showHTML.='</td>';							
		$showHTML.='</tr>';

		$showHTML.='<tr>';
			$showHTML.='<td colspan="5">';
				// Team Forderer / Spieler PopUp ( Im Teampopup wird auch das Wettbewerbspopup erstellt )
				$showHTML.='<div id="'.$cTmpTeamPopUpID1.'" '.$cTmpTeamPopUp1.' style="display:none; position: absolute;z-index:98;">';
					$showHTML.=kommune_funk_popup_wettbewerbteam($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerbTermine[$iTmpZehler]["team_forderer"])],$oWettbewerb,$cTmpTeamPopUpID1.$iTmpZehler,true);
				$showHTML.='</div>';	
				// Team Gefordert / Spieler PopUp ( Im Teampopup wird auch das Wettbewerbspopup erstellt )
				$showHTML.='<div id="'.$cTmpTeamPopUpID2.'" '.$cTmpTeamPopUp2.' style="display:none; position: absolute;z-index:98;">';
					$showHTML.=kommune_funk_popup_wettbewerbteam($oWettbewerb->TeamBenutzer[trim($arrTempWettbewerbTermine[$iTmpZehler]["team_gefordert"])][0],$oWettbewerb,$cTmpTeamPopUpID2.$iTmpZehler,true);
				$showHTML.='</div>';					

				// Wettbewerb PopUp
				$showHTML.='<div id="'.$cTmpTeamPopUpID3.'" '.$cTmpTeamPopUp3.' style="display:none; position: absolute;z-index:98;">';
					$showHTML.=kommune_funk_popup_wettbewerb($arrTempWettbewerbTermine[$iTmpZehler]["wettbewerb_kurzbz"],$oWettbewerb,$cTmpTeamPopUpID3.$iTmpZehler);
				$showHTML.='</div>';					
			$showHTML.='</td>';				
		$showHTML.='</tr>';
		

	}		   
       $showHTML.='</table>';

		$showHTML.='</fieldset>';
		return $showHTML;    	
   	return $showHTML;                            
}



#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_team_wettbewerbe Tabellen mit den eigenen Teamdaten zu keinem,einem oder mehreren Wettbewerben
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Fehlertext oder Leer
*
*/
function kommune_funk_team_wettbewerbe($oWettbewerb)		
{
	// Init
	$showHTML='';
// --- Wettbewerbe (EINLADUNGEN)                
	if (!is_array($oWettbewerb->EigeneWettbewerbe) 
	|| !isset($oWettbewerb->EigeneWettbewerbe[0]) )
		return $showHTML;
		
	$WettbewerbEinladung= new komune_wettbewerbeinladungen();
	
	// Einladungen - Aufforderungen	
	$WettbewerbEinladung->InitWettbewerbeinladungen();		
	$WettbewerbEinladung->setSwitchGewinner('0');
	$WettbewerbEinladung->setTeam_forderer($oWettbewerb->EigeneWettbewerbe);
	if ($WettbewerbEinladung->loadWettbewerbeinladungen())
		$oWettbewerb->Einladung=$WettbewerbEinladung->getWettbewerbeinladung();
	else	
		$showHTML.='<br />'.$WettbewerbEinladung->getError();
			
	// Forderungen 
	$WettbewerbEinladung->InitWettbewerbeinladungen();
	$WettbewerbEinladung->setTeam_gefordert($oWettbewerb->EigeneWettbewerbe);
	$WettbewerbEinladung->setSwitchGewinner('0');
	if ($WettbewerbEinladung->loadWettbewerbeinladungen())
		$oWettbewerb->Forderungen=$WettbewerbEinladung->getWettbewerbeinladung();
	else	
		$showHTML.='<br />'.$WettbewerbEinladung->getError();

	// Spiele - Einladungen
	$WettbewerbEinladung->InitWettbewerbeinladungen();
	$WettbewerbEinladung->setTeam_gefordert($oWettbewerb->EigeneWettbewerbe);
	$WettbewerbEinladung->setSwitchGewinner('1');
	if ($WettbewerbEinladung->loadWettbewerbeinladungen())
		$oWettbewerb->Spiele=$WettbewerbEinladung->getWettbewerbeinladung();
	else	
		$showHTML.='<br />'.$WettbewerbEinladung->getError();

	if (isset($WettbewerbEinladung))
		 unset($WettbewerbEinladung);
	
	// Personen  - zu den Aufforderungen oder den Forderungen suchen
		
	// TeamMitglieder lesen aus Person-Benutzer

	if (isset($oWettbewerb->Einladung[0]["gefordertvon"])) // Forderugen
  	{	
	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Einladung);$iTmpZehler++)
		{
			$pers=kommune_funk_benutzerperson($oWettbewerb->Einladung[$iTmpZehler]["gefordertvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Einladung[$iTmpZehler]["gefordertvon"]]=$pers;
			$pers=kommune_funk_benutzerperson($oWettbewerb->Einladung[$iTmpZehler]["bestaetigtvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Einladung[$iTmpZehler]["bestaetigtvon"]]=$pers;
		}			
	}	

	if (isset($oWettbewerb->Forderungen[0]["gefordertvon"])) // Aufforderungen
	{
	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Forderungen);$iTmpZehler++)
		{
			$pers=kommune_funk_benutzerperson($oWettbewerb->Forderungen[$iTmpZehler]["gefordertvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Forderungen[$iTmpZehler]["gefordertvon"]]=$pers;
			$pers=kommune_funk_benutzerperson($oWettbewerb->Forderungen[$iTmpZehler]["bestaetigtvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Forderungen[$iTmpZehler]["bestaetigtvon"]]=$pers;
		}			
	}	
	
	if (isset($oWettbewerb->Spiele[0]["gefordertvon"])) // Aufforderungen
	{
	  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Spiele);$iTmpZehler++)
		{
			$pers=kommune_funk_benutzerperson($oWettbewerb->Spiele[$iTmpZehler]["gefordertvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Spiele[$iTmpZehler]["gefordertvon"]]=$pers;
			$pers=kommune_funk_benutzerperson($oWettbewerb->Spiele[$iTmpZehler]["bestaetigtvon"],$oWettbewerb);
			if (isset($pers->nachname)) $oWettbewerb->PersonenBenutzer[$oWettbewerb->Spiele[$iTmpZehler]["bestaetigtvon"]]=$pers;
		}			
	}	
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_wartung_spielergebnis Anzeige, und moeglich Bestaetigungsaufrufe 
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
* @param $switchForderungen Schalter welche Anzeigenart gewaehlt wurde ( Forderer, Geforderter)
*
* @return HTML String  der Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_wartung_spielergebnis($oWettbewerb,$switchForderungen)
{
	$showHTML=''; // Init
	$arrTempWettbewerb=array();
     switch (trim($switchForderungen)) 
     {
        case 0:
			 $arrTempWettbewerb=$oWettbewerb->Einladung;
             break;
        case 1:
			 $arrTempWettbewerb=$oWettbewerb->Forderungen;
             break;
         default: // Default Uebersicht der Wettbewerbe
			 return "Kein Verarbeitungsswitch : $switchForderungen";
          	 break;
       }

	if (!is_array($arrTempWettbewerb) || count($arrTempWettbewerb)<1) // DB eof
		return "";
	reset($arrTempWettbewerb);			

	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
		// PopUP ID , und JavaScript fuer Wettbewerb
 		$cTmpWettbewerbPopUpID='sWb'.$iTmpZehler;
 		$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';

		// PopUP ID , und JavaScript fuer Team / Spieler Forderer
		$cTmpTeamPopUpID='sTeam'.$iTmpZehler;
		$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';
	
		// PopUP ID , und JavaScript fuer Team / Spieler Gefordert
		$cTmpTeamPopUpID2='sTeam2'.$iTmpZehler;
		$cTmpTeamPopUp2=' onmouseover="show_layer(\''.$cTmpTeamPopUpID2.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID2.'\');" ';

	
		$cTmpGefordertvon=trim($arrTempWettbewerb[$iTmpZehler]["gefordertvon"]);
		$cTmpBestaetigtvon=trim($arrTempWettbewerb[$iTmpZehler]["bestaetigtvon"]);
		$cTmpErgebnis=trim($arrTempWettbewerb[$iTmpZehler]["ergebniss"]);
				
			$showHTML.='<fieldset>';
			
				$iTmpMatch_id=$arrTempWettbewerb[$iTmpZehler]["match_id"];

				reset($oWettbewerb->Wettbewerb);	
				for ($iTmpZehler2=0;$iTmpZehler2<count($oWettbewerb->Wettbewerb);$iTmpZehler2++)
				{
					if (trim($oWettbewerb->Wettbewerb[$iTmpZehler2]["wettbewerb_kurzbz"])==trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]) )
					{
							$arrTempWettbewerb[$iTmpZehler]=array_merge($oWettbewerb->Wettbewerb[$iTmpZehler2],$arrTempWettbewerb[$iTmpZehler]);
							break;
					}
				}

			$showHTML.='<legend>'.$arrTempWettbewerb[$iTmpZehler]["wbtyp_kurzbz"].', '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'</legend>';	
				$showHTML.='<table cellpadding="1" cellspacing="1" style="background-color: #DDDDDD;text-align:left;width:99%;" summary="'.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">';  

	          		 $showHTML.='<tr><td colspan="2" >
				 	<table summary="Header Information" cellpadding="5" cellspacing="5" >';
			         $showHTML.='<tr>';
				                $showHTML.='<td><h3>Sieger : </h3></td><td>'.(empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"])?' <b style="color:red;">offen</b> ':' Spieler / Team <b>'.$arrTempWettbewerb[$iTmpZehler]["team_sieger"].'</b>' ).'</td>';
							  $showHTML.='<td><h3>Austragungsort : </h3></td><td>'.$arrTempWettbewerb[$iTmpZehler]["matchort"].'</td>';
				                $showHTML.='<td><h3>am : </h3></td><td>'.$arrTempWettbewerb[$iTmpZehler]["matchdatum"].' '.$arrTempWettbewerb[$iTmpZehler]["matchzeit"] .'</td>';
		              $showHTML.='</tr>';
							
					if (!empty($arrTempWettbewerb[$iTmpZehler]["ergebniss"]))
					{
				          $showHTML.='<tr style="vertical-align: top;">';
				               $showHTML.='<td><h3>Ergebnis :</h3></td><td colspan="5"><b>'.$arrTempWettbewerb[$iTmpZehler]["ergebniss"].'</b>   '.(empty($arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"])?' keine Best&auml;tigung durch  <b>'.$arrTempWettbewerb[$iTmpZehler]["team_gefordert"].'</b>':' Best&auml;tigt durch  <b>'.$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"].'</b> am '.$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtdatum"].', '.$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtzeit"]).'</td>';
				          $showHTML.='</tr>';
					}
		            $showHTML.='</table></td></tr>';
			     
			// Auffoderungsinfo
				$cShowImage='';
				if (isset($oWettbewerb->PersonenBenutzer[$cTmpBestaetigtvon]))
				{
					$pers=$oWettbewerb->PersonenBenutzer[$cTmpBestaetigtvon];
					$cTmpBestaetigtvon=(!empty($pers->anrede)?$pers->anrede.' ':'').(!empty($pers->vorname)?$pers->vorname.' ':'').(!empty($pers->nachname)?$pers->nachname.' ':'');
					if (!empty($pers->foto_image))
	   		       		$cShowImage=$pers->foto_image;
				}
				
 					
          		 $showHTML.='<tr>';
	                $showHTML.='<td style="vertical-align: top;border:1px outset Black;width:50%;">
					<table class="tabcontent" summary="Information Forderung an">';
				   $showHTML.='<tr><td colspan="2" style="border:1px outset Black;color:back;background-color: #DDDDDD;">Gefordert Team / Spieler</td></tr>
						<tr style="vertical-align: top;">
							<td style="color:back;background-color: #FFFFB0;vertical-align: top;">Team : </td>																				
							<td style="border:1px outset Black;background-color:#DDDDDD;">'.$arrTempWettbewerb[$iTmpZehler]["team_gefordert"].'</td>
							<td rowspan="3">'.$cShowImage.'</td>
						</tr>
						<tr style="vertical-align: top;">
							<td style="color:back;background-color: #FFFFB0;vertical-align: top;">best&auml;tigt durch : </td>																				
							<td style="border:1px outset Black;background-color:#DDDDDD;">'.(!empty($cTmpBestaetigtvon)?$cTmpBestaetigtvon:'-').'</td>
						</tr>
						<tr style="vertical-align: top;">
							<td style="color:back;background-color: #FFFFB0;vertical-align: top;">am : </td>																				
							<td style="border:1px outset Black;background-color:#DDDDDD;">'.(!empty($arrTempWettbewerb[$iTmpZehler]["bestaetigtdatum"])?$arrTempWettbewerb[$iTmpZehler]["bestaetigtdatum"].', um '.$arrTempWettbewerb[$iTmpZehler]["bestaetigtzeit"]:'-').'</td>
						</tr>
						<tr>
						<td style="vertical-align: top;" colspan="2">
						';
							if ( empty($arrTempWettbewerb[$iTmpZehler]["bestaetigtvon"]) && $switchForderungen==1)
							{
					      			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;einladen=2&amp;match_id='.trim($iTmpMatch_id).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerb[$iTmpZehler]["team_gefordert"]);
				         	   		 $showHTML.='
											<form method="post" name="kommunen_work" action="'.$paramURL.'" enctype="multipart/form-data">
												<input value="Forderung best&auml;tigen" type="submit" />
											</form>
										';
							}		
							if (empty($arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"]) 
							&& !empty($arrTempWettbewerb[$iTmpZehler]["ergebniss"]) && $switchForderungen==1)
							{
					      					$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;einladen=4&amp;match_id='.trim($iTmpMatch_id).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerb[$iTmpZehler]["team_gefordert"]);
				         				    $showHTML.='
											<form method="post" name="kommunen_work" action="'.$paramURL.'" enctype="multipart/form-data">
												<input value="Ergebnis best&auml;tigen" type="submit" />
											</form>
										';	
							}			
							$showHTML.='&nbsp;</td></tr>';
	            		$showHTML.='</table></td>';
						

			// Gefordert VON
				$cShowImage='';
				if (isset($oWettbewerb->PersonenBenutzer[$cTmpGefordertvon]))
				{
					$pers=$oWettbewerb->PersonenBenutzer[$cTmpGefordertvon];
					$cTmpGefordertvon=(!empty($pers->anrede)?$pers->anrede.' ':'').(!empty($pers->vorname)?$pers->vorname.' ':'').(!empty($pers->nachname)?$pers->nachname.' ':'');
					$cShowImage='';
					if (!empty($pers->foto_image))
	   		       		$cShowImage=$pers->foto_image;
				}
       			  $showHTML.='<td style="vertical-align: top;border:1px outset Black;width:50%;">
				  		<table  class="tabcontent" summary="Information Forderung von">';
			                $showHTML.='<tr>
								<td colspan="2"  style="border:1px outset Black;color:back;background-color: #DDDDDD;">Forderer Team / Spieler</td>
							</tr>
							<tr style="vertical-align: top;">
								<td style="color:back;background-color: #FFFFB0;vertical-align: top;">Team : </td>																				
								<td style="border:1px outset Black;background-color:#DDDDDD;">'.$arrTempWettbewerb[$iTmpZehler]["team_forderer"].'</td>
								<td rowspan="3">'.$cShowImage.'</td>
							</tr>
							<tr style="vertical-align: top;">
								<td style="color:back;background-color: #FFFFB0;vertical-align: top;">gefordert von : </td>																				
								<td style="border:1px outset Black;background-color:#DDDDDD;">'.$cTmpGefordertvon.'</td>
							</tr>
							<tr style="vertical-align: top;">
								<td style="color:back;background-color: #FFFFB0;vertical-align: top;">am : </td>																				
								<td style="border:1px outset Black;background-color:#DDDDDD;">'.$arrTempWettbewerb[$iTmpZehler]["gefordertamumdatum"].', um  '.$arrTempWettbewerb[$iTmpZehler]["gefordertamumzeit"].'</td>
							</tr>
							<tr>
								<td style="vertical-align: top;" colspan="2">
							';
							if (empty($cTmpBestaetigtvon) && $switchForderungen==0)
							{
					      			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;match_id='.trim($iTmpMatch_id).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerb[$iTmpZehler]["team_gefordert"]);
				         			$showHTML.='
									<form method="post" name="kommunen_work" action="'.$paramURL.'" enctype="multipart/form-data">
										<input value="&auml;ndern" type="submit" />
									</form>
									';
							}	
							
							if (!empty($cTmpBestaetigtvon) && $switchForderungen==0 && empty($arrTempWettbewerb[$iTmpZehler]["ergebniss"]) )
							{
					      			$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;match_id='.trim($iTmpMatch_id).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerb[$iTmpZehler]["team_gefordert"]);
				         			$showHTML.='
									<form method="post" name="kommunen_work" action="'.$paramURL.'" enctype="multipart/form-data">
										<input value="Ergebnis eintragen" type="submit" />
									</form>
									';
							}				
							if (empty($arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"]) 
							&& !empty($arrTempWettbewerb[$iTmpZehler]["ergebniss"]) && $switchForderungen==0)
							{
					      					$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;match_id='.trim($iTmpMatch_id).'&amp;wbtyp_kurzbz=&amp;wettbewerb_kurzbz='.trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'&amp;team_forderer='.trim($arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'&amp;team_gefordert='.trim($arrTempWettbewerb[$iTmpZehler]["team_gefordert"]);
				         				    $showHTML.='
											<form method="post" name="kommunen_work" action="'.$paramURL.'" enctype="multipart/form-data">
												<input value="Ergebnis &auml;ndern" type="submit" />
											</form>
										';	
							}													
							$showHTML.='&nbsp;</td></tr>';
										
															
		                $showHTML.='</table></td>';
	                $showHTML.='</tr>';
                $showHTML.='</table>';
            $showHTML.='</fieldset>';				
	}
    return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_show_wettbewerbteam_spiele Anzeige der Spiele des Wettbewerbteams Listenform
*
* @param $cWettbewerb Wettbewerb_kurzbezeichnung, oder kpl. Array des Wettbewerbes 
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String mit Wettbewerb Teamspiele
*
*/
function kommune_funk_show_wettbewerbteam_spiele($cWettbewerb_kurzbz='',$iMatch_id='',$oWettbewerb)
{


	
		$showHTML='';
		if (!is_array($oWettbewerb->Spiele) || count($oWettbewerb->Spiele)<1)
			return $showHTML;
			
		$showHTML.='<table summary="Show Wettbewerb Team Spiele - Spielergebnis">';
		reset($oWettbewerb->Spiele);

		for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Spiele);$iTmpZehler++)
		{
			if (!empty($cWettbewerb_kurzbz) && trim($cWettbewerb_kurzbz)!=trim($oWettbewerb->Spiele[$iTmpZehler]["wettbewerb_kurzbz"]))
				continue;
			if (!empty($iMatch_id) && trim($iMatch_id)!=trim($oWettbewerb->Spiele[$iTmpZehler]["match_id"]))
				continue;

				$showHTML.='<tr>';
					$showHTML.='<td style="background-color:#DDDDDD;">&nbsp;fordert&nbsp;</td><td style="background-color:#DDDDDD;">&nbsp;'.$oWettbewerb->Spiele[$iTmpZehler]["team_forderer"].'&nbsp;</td>';
					$showHTML.='<td style="background-color:#FFFFB0;">&nbsp;gefordert&nbsp;</td><td style="background-color:#FFFFB0;">&nbsp;'.$oWettbewerb->Spiele[$iTmpZehler]["team_gefordert"].'&nbsp;</td>';
					$showHTML.='<td '.(empty($oWettbewerb->Spiele[$iTmpZehler]["team_sieger"])? ' style="background-color:#B1D8D8;" ':(trim($oWettbewerb->Spiele[$iTmpZehler]["team_sieger"])==trim($oWettbewerb->Spiele[$iTmpZehler]["team_forderer"])? ' style="background-color:#DDDDDD;" ':' style="background-color:#FFFFB0;" ')).'>&nbsp;'.$oWettbewerb->Spiele[$iTmpZehler]["matchdatum"].'&nbsp;</td>';

				$showHTML.='</tr>';
#				$showHTML.='<tr><td colspan="4"><hr /></td></tr>';						
				$showHTML.='<tr '.(trim($oWettbewerb->Spiele[$iTmpZehler]["team_sieger"])==trim($oWettbewerb->Spiele[$iTmpZehler]["team_forderer"])? ' style="background-color:#DDDDDD;" ':' style="background-color:#FFFFB0;" ').'>';
					$showHTML.='<td><img style="vertical-align:bottom;" alt="Sieger" height="18" src="../../../skin/images/ok.png" border="0" />&nbsp;<b>Sieger</b>&nbsp;</td><td colspan="3"><b>&nbsp;'.$oWettbewerb->Spiele[$iTmpZehler]["team_sieger"].' Ergebnis '.$oWettbewerb->Spiele[$iTmpZehler]["ergebniss"].'&nbsp;</b></td>';
					$showHTML.='<td '.(empty($oWettbewerb->Spiele[$iTmpZehler]["team_sieger"])? ' style="background-color:#B1D8D8;" ':(trim($oWettbewerb->Spiele[$iTmpZehler]["team_sieger"])==trim($oWettbewerb->Spiele[$iTmpZehler]["team_forderer"])? ' style="background-color:#DDDDDD;" ':' style="background-color:#FFFFB0;" ')).'>&nbsp;'.$oWettbewerb->Spiele[$iTmpZehler]["matchbestaetigtdatum"].'&nbsp;</td>';
				$showHTML.='</tr>';
		}
		$showHTML.='<tr><td>&nbsp;</td></tr>';
		$showHTML.='</table>';
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_show_spielergebnis Statusanzeige der Match - Spiele , Default bereits gespielte , als Option Forderungen bzw. Einladungen
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Liste der Gesamten Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_show_spielergebnis($oWettbewerb,$showStatus=false)
{
	$showHTML=''; // Init

	if (isset($oWettbewerb->Spiele))
		$arrTempWettbewerb=$oWettbewerb->Spiele;
	else
		$arrTempWettbewerb=array();
			
	$bTmpSwitchShow=0;
	if ($showStatus) // Anzeigen Status von Offenen Matchdaten
	{
		if (isset($oWettbewerb->Forderungen) && (!is_array($arrTempWettbewerb) || count($arrTempWettbewerb)<1)) // DB eof
		{
			$arrTempWettbewerb=$oWettbewerb->Forderungen;
			$bTmpSwitchShow=1;
		}	
		elseif (isset($oWettbewerb->Einladung) && (!is_array($arrTempWettbewerb) || count($arrTempWettbewerb)<1)) // DB eof
		{
			$arrTempWettbewerb=$oWettbewerb->Einladung;
			$bTmpSwitchShow=2;
		}	
		else
			return "Anzeigeart '$showStatus' wird nicht verarbeitet.";
	}	

	if (!is_array($arrTempWettbewerb) || count($arrTempWettbewerb)<1) // DB eof
		return "keine Informationen zum Anzeigen vorhanden.";
		
	reset($arrTempWettbewerb);			
	for ($iTmpZehler=0;$iTmpZehler<count($arrTempWettbewerb);$iTmpZehler++)
	{
	
		$cTmpSpielergebnisPopUpID='sSpErg'.$iTmpZehler;
		$cTmpSpielergebnisPopUpID_on=$cTmpSpielergebnisPopUpID.'On';
		$cTmpSpielergebnisPopUpID_off=$cTmpSpielergebnisPopUpID.'Off';		
		$cTmpSpielergebnisPopUp_on=' style="display:none" onclick="show_layer(\''.$cTmpSpielergebnisPopUpID.'help\');hide_layer(\''.$cTmpSpielergebnisPopUpID.'\');hide_layer(\''.$cTmpSpielergebnisPopUpID_on.'\');show_layer(\''.$cTmpSpielergebnisPopUpID_off.'\');" ';
		$cTmpSpielergebnisPopUp_off=' onclick="hide_layer(\''.$cTmpSpielergebnisPopUpID.'help\');show_layer(\''.$cTmpSpielergebnisPopUpID.'\');hide_layer(\''.$cTmpSpielergebnisPopUpID_off.'\');show_layer(\''.$cTmpSpielergebnisPopUpID_on.'\');" ';
	
		// Hauptzeile - Ueberschrift	
		$showHTML.='<fieldset>';
			$showHTML.='<legend style="vertical-align: top;">';		
				$showHTML.='<span id="'.$cTmpSpielergebnisPopUpID_on.'" '.$cTmpSpielergebnisPopUp_on.'><img alt="open'.$iTmpZehler.'" height="18" src="../../../skin/images/folderup.gif" border="0" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpSpielergebnisPopUpID_on.'" />ausblenden&nbsp;</span>';
				$showHTML.='<span id="'.$cTmpSpielergebnisPopUpID_off.'" '.$cTmpSpielergebnisPopUp_off.'><img alt="close'.$iTmpZehler.'" height="18" src="../../../skin/images/folder.gif" border="0" /><input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="'.$cTmpSpielergebnisPopUpID_off.'" />anzeigen&nbsp;</span>';
					$showHTML.='Wettbewerb <b>'.kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('team_kurzbz'=>trim($oWettbewerb->team_kurzbz),'wettbewerb_kurzbz'=>trim($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])),$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]).'</b>  ';		
					$showHTML.=($bTmpSwitchShow==1?' Forderung ':($bTmpSwitchShow==2?' Einladung ':' Spiel ')). ' '.$arrTempWettbewerb[$iTmpZehler]["matchdatum"] .'</legend>';				

			$showHTML.='<table id="'.$cTmpSpielergebnisPopUpID.'help" summary="Kurzliste '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">';
				$showHTML.='<tr '.$cTmpSpielergebnisPopUp_off.'><td>'.kommune_funk_show_wettbewerbteam_spiele($arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],$arrTempWettbewerb[$iTmpZehler]["match_id"],$oWettbewerb).'</td></tr>';
			$showHTML.='</table>';
	
			$showHTML.='<table '.$cTmpSpielergebnisPopUp_on.' id="'.$cTmpSpielergebnisPopUpID.'" summary="Detail '.$arrTempWettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'">';    
		            $showHTML.='<tr style="vertical-align: top;background-color:#DDDDDD;">';
					$showHTML.='<td style="text-align:right;">Forderung von : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["gefordertvon"].'</td>';
					$showHTML.='<td style="text-align:right;">am : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["gefordertamumdatum"].' '.$arrTempWettbewerb[$iTmpZehler]["gefordertamumzeit"].'</td>';
			        $showHTML.='</tr>';

		            $showHTML.='<tr style="vertical-align: top;background-color:#DDDDDD;">';
					$showHTML.='<td style="text-align:right;">Ort : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["matchort"].' </td>';
					$showHTML.='<td style="text-align:right;">am : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["matchdatum"].' '.$arrTempWettbewerb[$iTmpZehler]["matchzeit"].'</td>';
			        $showHTML.='</tr>';

		            $showHTML.='<tr style="vertical-align: top;background-color:#FFFFB0;">';
					$showHTML.='<td style="text-align:right;">Best&auml;tigt Forderung von : </td><td>'.(empty($arrTempWettbewerb[$iTmpZehler]["bestaetigtvon"])?'offen':$arrTempWettbewerb[$iTmpZehler]["bestaetigtvon"]).'</td>';
					$showHTML.='<td style="text-align:right;">am : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["bestaetigtdatum"].' '.$arrTempWettbewerb[$iTmpZehler]["bestaetigtzeit"].'</td>';
			        $showHTML.='</tr>';

				 
		            $showHTML.='<tr style="vertical-align: top;background-color:#DDDDDD;">';
					$showHTML.='<td style="text-align:right;">Sieger : </td><td>'.(empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"])?'offen':$arrTempWettbewerb[$iTmpZehler]["team_sieger"]).'</td>';
					$showHTML.='<td style="text-align:right;">Ergebnis : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["ergebniss"].'</td>';
			        $showHTML.='</tr>';
				 
		            $showHTML.='<tr style="vertical-align: top;background-color:#FFFFB0;">';
					$showHTML.='<td style="text-align:right;">Best&auml;tigt Sieger von : </td><td>'.(empty($arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"])?'offen':$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtvon"]).'</td>';
					$showHTML.='<td style="text-align:right;">am : </td><td>'.$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtdatum"].' '.$arrTempWettbewerb[$iTmpZehler]["matchbestaetigtzeit"].'</td>';
			        $showHTML.='</tr>';				 

			        $showHTML.='<tr style="vertical-align: top;">';
					$showHTML.='<td '.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]==$arrTempWettbewerb[$iTmpZehler]["team_forderer"]?' style="background-color:#FFE2D5;text-align:right;">Sieger ':' style="background-color:#FFFFFF;text-align:right;">Verlierer ').' : </td><td'.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]==$arrTempWettbewerb[$iTmpZehler]["team_forderer"]?' style="background-color:#FFE2D5;" ':' style="background-color:#FFFFFF;" ').'>'.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]==$arrTempWettbewerb[$iTmpZehler]["team_forderer"]?'<b>'.$arrTempWettbewerb[$iTmpZehler]["team_forderer"].'</b>':$arrTempWettbewerb[$iTmpZehler]["team_forderer"]).'</td>';
					$showHTML.='<td '.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]!=$arrTempWettbewerb[$iTmpZehler]["team_forderer"]?' style="background-color:#FFE2D5;text-align:right;">Sieger ':' style="background-color:#FFFFFF;text-align:right;">Verlierer ').' : </td><td '.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]!=$arrTempWettbewerb[$iTmpZehler]["team_forderer"]?' style="background-color:#FFE2D5;" ':' style="background-color:#FFFFFF	;" ').'>'.(!empty($arrTempWettbewerb[$iTmpZehler]["team_sieger"]) && $arrTempWettbewerb[$iTmpZehler]["team_sieger"]==$arrTempWettbewerb[$iTmpZehler]["team_gefordert"]?'<b>'.$arrTempWettbewerb[$iTmpZehler]["team_gefordert"].'</b>':$arrTempWettbewerb[$iTmpZehler]["team_gefordert"]).'</td>';
			        $showHTML.='</tr>';		

	     $showHTML.='</table>';
            $showHTML.='</fieldset>';				
	}
    	return $showHTML;                            
}

#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_popup_benutzer Aufbau einer bisher gespielten Wettbewerbe
*
* @param $cUid UserUID Anwenderkurzzeichen 
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String Benutzeruebersicht
*
*/
function kommune_funk_popup_benutzer($cUid,$oWettbewerb)
{
	$showHTML=''; // Init

	// Plausib
	if (is_array($cUid) && isset($cUid['uid']))
		$cUid=$cUid['uid'];
	elseif (is_array($cUid) && isset($cUid[0]['uid']))
		$cUid=$cUid[0]['uid'];
	elseif (empty($cUid)) 
		return $showHTML;
		 
	$cTmpName=$cUid;
	$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
	if (isset($pers->langname)) 
		$cTmpName=$pers->langname;		 
		 
	$showHTML.='
     	<fieldset style="border:1px outset Black;background-color:#DDDDDD;">
		<legend style="border:2px  outset Black;background-color:#FFFFF2;">'.(isset($pers->langname)?$pers->langname:$cUid).'</legend>
		<table cellpadding="2" cellspacing="2" border="0" summary="'.(isset($pers->langname)?$pers->langname:$cUid).'">    
		        <tr>
				<td rowspan="3">'.(isset($pers->foto_image)?$pers->foto_image:'').'</td>
				<td colspan="2" style="vertical-align: top;"><a href="mailto:'.kommune_funk_create_emailaccount($cUid).'">'.kommune_funk_create_emailaccount($cUid).'</a></td>
			</tr>
		        <tr>
				<td style="text-align:right;vertical-align: top;">'.(isset($pers->sprache)?'Sprache : ':'').'</td> 
				<td style="vertical-align: top;">'.(isset($pers->sprache)?$pers->sprache:'').'</td>
			</tr>
	       	 <tr>
				<td style="text-align:right;vertical-align: top;">'.(isset($pers->gebort)?'aus : ':'').'</td> 
				<td style="vertical-align: top;">'.(isset($pers->gebort)?$pers->gebort:'').(isset($pers->geburtsnation)?'  ('.$pers->geburtsnation.')':'').'</td>
			</tr>
		</table>   
	</fieldset>';	
	return $showHTML;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_benutzerperson ermittelt zu einer UID die Person, und speichert diese im Objekt
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
* @param $cUid UserUID Anwenderkurzzeichen 
*
* @return HTML Liste der Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_benutzerperson($userUID,$oWettbewerb)
{
	$userUID=trim($userUID);
	if (empty($userUID))
		return 'keine Benutzer UID &uuml;bergeben';
		
	if (isset($oWettbewerb->PersonenBenutzer[$userUID])) // Wurde bereits gefunden
	{
		$pers=$oWettbewerb->PersonenBenutzer[$userUID];
		if (!isset($pers->langname))
			$pers->langname=kommune_funk_pers_langname($userUID,$pers);	
		$oWettbewerb->PersonenBenutzer[$userUID]=$pers;
		return $pers;
	}
	
	$pers = new benutzer($userUID); // Lesen PersonenBenutzer
	if (!isset($pers->nachname))
		return $userUID;
		
	$pers->langname=kommune_funk_pers_langname($userUID,$pers);
	$pers->foto_image='';
	if (!empty($pers->foto))
	{
		$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().'&amp;person_id='.$pers->person_id.(strlen($pers->foto)<1000?'&amp;heximg='.$pers->foto:'');
   		$pers->foto_image='<img height="70"  border="0" alt="'.$pers->langname.' '.$pers->person_id.'" src="'.$paramURL.'" />';
	}
	$oWettbewerb->PersonenBenutzer[$userUID]=$pers;
	return $pers;
}	

#-------------------------------------------------------------------------------------------	
/* Subfunktion von getDisplayStringWettbewerb 
*
* @kommune_funk_Statistik Aufbau einer StatistikListe zu den Wettbewerben
*
* @param $oWettbewerb Array mit allen Wettbewerbs und Benutzerdaten
*
* @return HTML String in Listenform der Wettbewerbe
*
*/
function kommune_funk_Statistik($oWettbewerb)
{
	$showHTML='';
	if (!is_array($oWettbewerb->Wettbewerb))
		return $showHTML;		

	$Wettbewerb=new komune_wettbewerbteam('','','');

	// Laden alle Teams	
	$Wettbewerb->InitWettbewerbteam();
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->TeamAnwender=$Wettbewerb->getWettbewerbteam();
   	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();

	// Anzahl, max Punkte , und max Rang je Wettbewerb			
	$Wettbewerb->InitWettbewerbteam();
    	$qry="";
		$qry.="SELECT * FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam where punkte>0";
    	$qry.=" order by punkte desc OFFSET 0 LIMIT 3;";	

		$db = new basis_db();
		$aPunkteSieger=array();
		if($db->db_query($qry))
		{
			$rows=array();
			while($row = $db->db_fetch_array())
			{
				$aPunkteSieger[]=$row;
			}
		}	
		else
		{
			$oWettbewerb->Error[]=$db->db_last_error();
		}	
		
	$showHTML.='<table cellpadding="1" cellspacing="1" summary="Aktivster Spieler" style="background-color: black;">';
		$showHTML.='<tr>'; 
			$showHTML.='<th colspan="6" style="color:back;background-color:#DDDDDD;">die Aktivsten Top 3 </th>';
		$showHTML.='</tr>'; 
	$showHTML.='<tr>'; 
		$showHTML.='<th colspan="2" style="color:back;background-color: #C0C0C0;">Wettbewerb</th>';
		$showHTML.='<th style="color:back;background-color: #C0C0C0;">Team / Spieler</th>';
		$showHTML.='<th style="color:back;background-color: #C0C0C0;">Rang</th>';
		$showHTML.='<th style="color:back;background-color: #C0C0C0;">Punkte</th>';
		$showHTML.='<th style="color:back;background-color: #C0C0C0;">Bild</th>';
	$showHTML.='</tr>'; 

	
	for ($iTmpZehler=0;$iTmpZehler<count($aPunkteSieger);$iTmpZehler++)
	{
	
		$cTmpTeamPopUpID='BestTeamFooter'.$iTmpZehler;
		$cTmpTeamPopUp=' onmouseover="show_layer(\''.$cTmpTeamPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID.'\');" ';

		$cTmpWettbewerbPopUpID='BestTeamWettbewerb'.$iTmpZehler;
		$cTmpWettbewerbPopUp=' onmouseover="show_layer(\''.$cTmpWettbewerbPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpWettbewerbPopUpID.'\');" ';
		
		$cTmpFarbe='FFFFB0';
		reset($oWettbewerb->Wettbewerb);
		for ($iTmpZehler2=0;$iTmpZehler2<count($aPunkteSieger);$iTmpZehler2++)
		{
			if (trim($oWettbewerb->Wettbewerb[$iTmpZehler2]["wettbewerb_kurzbz"])==trim($aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz']))
			{
				$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[$iTmpZehler2]["farbe"]) && !empty($oWettbewerb->Wettbewerb[$iTmpZehler2]["farbe"])?'#'.$oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]:'transparent');
				break;
			}
		}
		
		$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>$aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz']),'<img title="weiter  '.$aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz'].'" style="vertical-align: bottom;" alt="open'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;'.$aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz'].'&nbsp;','weiter');


		$cShowImage='';
		$arrWettbewerbTeam=array();
		$WettbewerbT=new komune_wettbewerbteam('',$aPunkteSieger[$iTmpZehler]['team_kurzbz'],$aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz']);
		if ($WettbewerbT->loadWettbewerbteam())
		{
			$arrWettbewerbTeam=$WettbewerbT->getWettbewerbteam();

			$pers=kommune_funk_benutzerperson($arrWettbewerbTeam[0]['uid'],$oWettbewerb);
			if (isset($pers->foto_image) && !empty($pers->foto_image))
      			$cShowImage=$pers->foto_image;
		}	
		unset($WettbewerbT);


			
		if ($iTmpZehler%2)
			$cTmpBGcolor="#FFFFB0";
		else	
			$cTmpBGcolor="#FFFFD7";				
			
		$showHTML.='<tr>'; 
			$showHTML.='<th style="color:back;background-color: '.$cTmpBGcolor.';border:1px solid '.$cTmpFarbe.';">&nbsp;'.($iTmpZehler + 1).'&nbsp;</th>';
			$showHTML.='<td '.$cTmpWettbewerbPopUp.' style="color:back;background-color: '.$cTmpBGcolor.';>'.$cTmpHREF.'</td>';
			$showHTML.='<td '.$cTmpTeamPopUp.'  style="color:back;background-color: '.$cTmpBGcolor.';">'.$aPunkteSieger[$iTmpZehler]['team_kurzbz'].'</td>';
			$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';">'.$aPunkteSieger[$iTmpZehler]['rang'].'</td>';
			$showHTML.='<th style="color:back;background-color: '.$cTmpBGcolor.';">'.$aPunkteSieger[$iTmpZehler]['punkte'].'</th>';
			$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';">'.(!empty($cShowImage)?$cShowImage:'&nbsp;').'</td>';

			$showHTML.='<td>';
			// Team / Spieler PopUp ( Im Teampopup wird auch das Wettbewerbspopup erstellt )
			$showHTML.='<div id="'.$cTmpTeamPopUpID.'" style="display:none; position: absolute;z-index:99;">';
			if (is_array($arrWettbewerbTeam) && count($arrWettbewerbTeam)>0)
			{
					$showHTML.='<span style="color:back;background-color: white;">'.$aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz'].'..'.$aPunkteSieger[$iTmpZehler]['team_kurzbz'].'..'.$aPunkteSieger[$iTmpZehler]['team_kurzbz'].'</span> <br />';
					$showHTML.=kommune_funk_popup_wettbewerbteam($aPunkteSieger[$iTmpZehler]['team_kurzbz'],$oWettbewerb,$cTmpTeamPopUpID.$iTmpZehler);
			}	
			$showHTML.='</div>';		

			// Wettbewerb PopUp
			$showHTML.='<div id="'.$cTmpWettbewerbPopUpID.'" style="display:none;position: absolute;z-index:99;">';
				$showHTML.=kommune_funk_popup_wettbewerb($aPunkteSieger[$iTmpZehler]['wettbewerb_kurzbz'],$oWettbewerb,$cTmpWettbewerbPopUpID.$iTmpZehler);
			$showHTML.='</div>';		
		$showHTML.='</td>';
		
		$showHTML.='</tr>'; 
	}
	$showHTML.='</table>';	
	$showHTML.='<br />';		

// Gesamtuebersicht-------------------------------------------------------------------------------------------	
	$showHTML.='<table  class="tabcontent" summary="Wettbewerb Statistikdaten">';
	$showHTML.='<tr style="vertical-align:top;">'; 
	$showHTML.='<td><table  cellpadding="1" cellspacing="2" summary="Wettbewerb Statistik" style="background-color: black;">';
		$showHTML.='<tr>'; 
			$showHTML.='<th colspan="7" style="color:back;background-color:#DDDDDD;">Wettbewerb Informationen</th>';
		$showHTML.='</tr>'; 

		$showHTML.='<tr>'; 
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Art</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Wettbewerb</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">anz.Teiln.</th>';

			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Beste</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Aktivste</th>';

			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Punkte</th>';
			
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Forderungen</th>';
		$showHTML.='</tr>'; 

		
	$cTmpGruppeTyp='';	
	$showHTMLicon='';
	$showHTMLspiele='';
	$showHTMLteams='';


	$Wettbewerb=new komune_wettbewerbteam('','','');
	$Wettbewerb->InitWettbewerbteam();


	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
		$db = new basis_db();	
		// Kennzeichen ob ein Record in tbl_wettbewerb angelegt wurde ist wbtyp_kurzbz 
		if (empty($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])) // wbtyp_kurzbz=(leer=keine wettbewerbe)
			continue;
			
		// Wettbewerbstypen - Gruppenwechsel
		$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]);
	   	$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]);
		$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]) && !empty($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"])?$oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]:'');
	   	
		$cTmpIconPopUpID='icon'.$iTmpZehler;
		$cTmpIconPopUp=' onmouseover="show_layer(\''.$cTmpIconPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpIconPopUpID.'\');" ';

		$cTmpSpielePopUpID='spiele'.$iTmpZehler;
		$cTmpSpielePopUp=' onmouseover="show_layer(\''.$cTmpSpielePopUpID.'\');" onmouseout="hide_layer(\''.$cTmpSpielePopUpID.'\');" ';

		$cTmpTeamPopUpID1='sTeam1'.$iTmpZehler;
		$cTmpTeamPopUp1=' onmouseover="show_layer(\''.$cTmpTeamPopUpID1.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID1.'\');" ';
		
		$cTmpTeamPopUpID2='sTeam2'.$iTmpZehler;
		$cTmpTeamPopUp2=' onmouseover="show_layer(\''.$cTmpTeamPopUpID2.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID2.'\');" ';
			
		$showHTMLicon.='<div style="display:none;z-index:98;" id="'.$cTmpIconPopUpID.'">'.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"])?$oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"].'<br />':'').'</div>';
		$showHTMLspiele.='<div style="display:none;z-index:99;" id="'.$cTmpSpielePopUpID.'">'.kommune_funk_show_wettbewerbteam_spiele($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],'',$oWettbewerb).'</div>';

		if ($iTmpZehler%2)
			$cTmpBGcolor="#FFFFB0";
		else	
			$cTmpBGcolor="#FFFFD7";			
			
		$showHTML.='<tr>'; 
		
			if ($cTmpGruppeTyp!=$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'])	
				$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';border:1px solid #'.$cTmpFarbe.';">'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'</td>';
			else
				$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';"></td>';
			$cTmpGruppeTyp=$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'];
			
			$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img title="weiter  '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" style="vertical-align: bottom;" alt="open'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'&nbsp;','weiter');
			$showHTML.='<td '.$cTmpIconPopUp.' style="color:back;background-color: '.$cTmpBGcolor.';">'.$cTmpHREF.'</td>';


	// Anzahl Teams Spieler je Wettbewerb
		    	$qry="";
				$qry.="SELECT count(distinct tbl_wettbewerbteam.team_kurzbz) as count_team_kurzbz FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam ";
	   			$qry.=" WHERE UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])."') ";	
	    		$qry.=" OFFSET 0 LIMIT 1 ;";	
			$cTmpWettbewerbInfo=array();
	$aPunkteSieger=array();
		if($db->db_query($qry))
		{
			$rows=array();
			while($row = $db->db_fetch_array())
			{
				$aPunkteSieger[]=$row;
			}
		}	
		else
		{
			$oWettbewerb->Error[]= $db->db_last_error();
			return false;
		}				
			
			$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';">'.@(int)(isset($cTmpWettbewerbInfo[0]['count_team_kurzbz'])?$cTmpWettbewerbInfo[0]['count_team_kurzbz']:0).'</td>';
	// Anzahl Teams Spieler Ende
			
	// Besten suchen		
		    	$qry="";
				$qry.="SELECT tbl_wettbewerbteam.wettbewerb_kurzbz,team_kurzbz,punkte,rang FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam ";
		   		$qry.=" WHERE UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])."') ";	
		    	$qry.=" order by rang ASC OFFSET 0 LIMIT 1 ;";	
			$arrWettbewerbTeams=array();

	$aPunkteSieger=array();
		if($db->db_query($qry))
		{
			$rows=array();
			while($row = $db->db_fetch_array())
			{
				$arrWettbewerbTeams[]=$row;
			}
		}	
		else
		{
			$oWettbewerb->Error[]= $db->db_last_error();
			return false;
		}				

			$showHTMLteams.='<div style="display:none;position: absolute;z-index:99;" id="'.$cTmpTeamPopUpID1.'"  '.$cTmpTeamPopUp1.' >';
				if (isset($cTmpBesteTeam[0]['team_kurzbz']) )
					$showHTMLteams.=kommune_funk_popup_wettbewerbteam($cTmpBesteTeam[0]['team_kurzbz'],$oWettbewerb,$cTmpTeamPopUpID1.$iTmpZehler,true);
			$showHTMLteams.='</div>';			
			$showHTML.='<td '.$cTmpTeamPopUp1.' style="color:back;background-color: '.$cTmpBGcolor.';">'.(isset($cTmpBesteTeam[0]['team_kurzbz'])?$cTmpBesteTeam[0]['team_kurzbz']:'').'</td>';
	// Besten suchen Ende

	// Aktivsten suchen		
		    	$qry="";
				$qry.="SELECT tbl_wettbewerbteam.wettbewerb_kurzbz,team_kurzbz,punkte,rang FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam ";
	   			$qry.=" WHERE UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])."') ";	
	    		$qry.=" order by punkte DESC OFFSET 0 LIMIT 1 ;";	
			$cTmpAktivsteTeam=array();
		if($db->db_query($qry))
		{
			$rows=array();
			while($row = $db->db_fetch_array())
			{
				$cTmpAktivsteTeam[]=$row;
			}
		}	
		else
		{
			$oWettbewerb->Error[]= $db->db_last_error();
			return false;
		}	
					

			$showHTMLteams.='<div style="display:none;position: absolute;z-index:99;" id="'.$cTmpTeamPopUpID2.'"  '.$cTmpTeamPopUp2.' >';
				if (isset($cTmpAktivsteTeam[0]['team_kurzbz']) )
					$showHTMLteams.=kommune_funk_popup_wettbewerbteam($cTmpAktivsteTeam[0]['team_kurzbz'],$oWettbewerb,$cTmpTeamPopUpID2.$iTmpZehler,true);
			$showHTMLteams.='</div>';			
			$showHTML.='<td '.$cTmpTeamPopUp2.' style="color:back;background-color: '.$cTmpBGcolor.';">'.(isset($cTmpAktivsteTeam[0]['team_kurzbz'])?$cTmpAktivsteTeam[0]['team_kurzbz']:'').'</td>';
			$showHTML.='<td style="color:back;background-color: '.$cTmpBGcolor.';">'.@(int)(isset($cTmpAktivsteTeam[0]['punkte'])?$cTmpAktivsteTeam=$cTmpBesteTeam[0]['punkte']:0).'</td>';
	// Aktivsten suchen	Ende	

			
			if (!isset($cTmpBesteTeam[0]['punkte']) || $cTmpBesteTeam[0]['punkte']<1)
				$showHTML.='<td '.$cTmpSpielePopUp.'  style="color:back;background-color: '.$cTmpBGcolor.';">keine Forderungen</td>';
			else
				$showHTML.='<td '.$cTmpSpielePopUp.' style="color:back;background-color: '.$cTmpBGcolor.';"><b>Forderungen</b></td>';
		$showHTML.='</tr>'; 
	}
	$showHTML.='</table></td>';
		
		$showHTML.='<td style="vertical-align:top;">'.$showHTMLicon.$showHTMLteams.$showHTMLspiele.'</td>'; 
	$showHTML.='</tr>'; 
	$showHTML.='</table>';	

	if (isset($Wettbewerb)) 
		unset($Wettbewerb);	

	$showHTML.='<br /><br /><div style="text-align:center;" class="home_logo">&nbsp;</div>'; 
	return $showHTML;                            
}

#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_mail Sendmailfunktion
*
* @param $empf Empfaengeradresse
* @param $betreff der Nachricht
* @param $text Nachrichtentext
* @param $abs Absender der Nachricht
*
* @return HTML Status der Mailfunktion 
*
*/
function kommune_funk_sendmail($empf='',$betreff='',$text='',$abs='',$oWettbewerb)
{
// Empfaenger
	if (empty($empf) && isset($oWettbewerb->wettbewerb[0]['uid']) ) 
		$empf=$oWettbewerb->wettbewerb[0]['uid'];
	$empf=trim($empf);
	$empf=kommune_funk_create_emailaccount(trim($empf));

// Absender 
	if (empty($abs)) // wenn kein Absender vorhanden ist : den Aktuellangemeldete Anwender nehmen
		$abs=$oWettbewerb->userUID;
	$abs=trim($abs);
			
	$cTmpName=str_replace(stristr($abs,"@"),'',$abs); // Es wurde eine eMailadresse uebergeben, wir brauchen die UID
	$cTmpName=trim($cTmpName);

	$pers=kommune_funk_benutzerperson(trim($cTmpName),$oWettbewerb);
	if (isset($pers->nachname))
		$cTmpName=$pers->nachname;
	$abs=kommune_funk_create_emailaccount(trim($abs));


	if (empty($betreff))
		$betreff=(defined('CAMPUS_NAME')?CAMPUS_NAME.' ':'').$oWettbewerb->wettbewerb_kurzbz.' Information';
	if (empty($text))
		$text=$betreff."\n\n".$abs;

#G erald
#$empf='simane@technikum-wien.at';

	if (!@mail($empf, $betreff, $text, "From: ".(defined('CAMPUS_NAME')?CAMPUS_NAME.' ':'').$oWettbewerb->wettbewerb_kurzbz.' '.$cTmpName." <$abs>"))
		return "eMail Versand an $empf mit Betreff:$betreff konnte nicht erfolgreich beendet werden!";

   	return 'eMail wurde erfolgreich gesendet an '.$empf; // Init
;                            
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_create_emailaccount erzeugt aus der UID und der Domainkonstante eine eMailadresse
*
* @param $uid
*
* @return emailadresse (aus einer uid und der Domainkonstante aus der config)
*
*/
function kommune_funk_create_emailaccount($cUID)
{
	if (empty($cUID))
		$cUID=get_uid();
	$cUID=trim($cUID);	
	if (!defined('DOMAIN')) die('Die Konstante DOMAIN wurde nicht gefunden! Bitte config pruefen.' );
	if (!stristr($cUID,'@')) // Domainkonstante nur dazufuegen wenn noch keine Domain im Namen ist
		$cUID=$cUID.(stristr(DOMAIN,'@')?DOMAIN:'@'.DOMAIN); // Pruefen ob in der Konstant der Klammeraffe ist
	$cUID=str_replace(' ','',$cUID);
   	return strtolower($cUID);
}

#-------------------------------------------------------------------------------------------	
/*
*
* @kommune_wettbewerbstyp_langtext Gibt zum Wettbewerbstype den Langtext retour
*
* @param pers array der Personen - Benutzer Daten 
*
* @return RETURN wird das Erfolgreiche bzw. der Fehler der Datenbankaktion geliefert
*
*/
function kommune_funk_pers_langname($userUID="",$pers="")
{           
	if (!isset($pers->nachname)) // Plausib : wurde kein Datenobjekt der Person uebergeben
	{
		if (!empty($pers) && !is_array($pers)) // Es wurde was uebergeben (Retour dieser Information)
				return $pers;
		else	
			return $userUID;
	}	
	$cTmpLangName='';  
	$cTmpLangName.=(isset($pers->anrede) ? $pers->anrede.' ':'');
	$cTmpLangName.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
	$cTmpLangName.=(isset($pers->vorname) ? $pers->vorname.' ':'');
	$cTmpLangName.=(isset($pers->nachname) ? $pers->nachname.' ':'');
	
	return $cTmpLangName;
}
#-------------------------------------------------------------------------------------------	
/*
*
* @loadBILDintoDB Bilder als HEX Wert in DB Tabellen speichern
*
* @param -
*
* @return RETURN wird das Erfolgreiche bzw. der Fehler der Datenbankaktion geliefert
*
*/
       function loadBILDintoDB($userSETWORK="")
       {              
          if(!isset($userSETWORK) || $userSETWORK=='') 
              $userSETWORK = (isset($_REQUEST[constKommuneParmSetWork]) ? $_REQUEST[constKommuneParmSetWork] : '');
          if(!isset($userSETWORK) || $userSETWORK=='') // Default Verarbeitung setzten
	       	  $userSETWORK=constKommuneAnzeigeDEFAULT;

        // Plausib der UploadDaten      
              if(!isset($_POST['submitbild'])) return '';
              if(!isset($_FILES['bild']['tmp_name'])) return '';

              $filename=$_FILES['bild']['tmp_name'];
			  if (!is_file($filename)) return '';
              if ($fp=fopen($filename,'r'))    //File oeffnen
	    	  {
		          $content = fread($fp, filesize($filename)); // auslesen der Daten
    	          fclose($fp); // Close
			  }
			  else // Fehler Information das Bild nicht gefunden wurde
			  {
			  	return sprintf(constFehlerDatenlesen,$_FILES['bild']['name']);
			  }				  	  
              if (isset($fp)) unset($fp);
              $content=kommune_strhex($content); //in HEX-Werte umrechnen
              if(empty($content)) return '';

	          $selectWETTBEWERBTYPE = (isset($_REQUEST[constKommuneParmWettbewerbTyp]) ? $_REQUEST[constKommuneParmWettbewerbTyp] : '');

              $qry="SET search_path TO kommune;"; 
              switch (strtolower($userSETWORK)) 
              {
              // Teams (mit n User) Neuanlage zu Wettbewerb
               case constKommuneWartungTEAM:
                     return ''; 
                     break;
       // Teams (mit n User) Neuanlage zu Wettbewerb
               case constKommuneWartungUID:
                     $team_kurzbz=(isset($_REQUEST['team_kurzbz']) ? $_REQUEST['team_kurzbz'] : '');
                     $selectTEAM=(isset($_REQUEST['team_kurzbz_orig']) ? $_REQUEST['team_kurzbz_orig'] : $team_kurzbz);
                     $qry.="BEGIN;UPDATE tbl_team set logo='$content' WHERE UPPER(team_kurzbz)=UPPER('$selectTEAM');COMMIT;";
                     break;
       //  User-Teams zu einem Wettbewerb anzeigen ( Pyramide = Rang )
               case constKommuneAnzeigeWETTBEWERBTEAM:
                     return ''; 
                     break;
               case constKommuneAnzeigeDEFAULT:
                   	$selectWETTBEWERB = (isset($_REQUEST[constKommuneParmWettbewerbArt]) ? $_REQUEST[constKommuneParmWettbewerbArt] : '');
                   	$qry.="BEGIN;UPDATE tbl_wettbewerb set icon='$content' WHERE UPPER(wettbewerb_kurzbz)=UPPER('$selectWETTBEWERB');COMMIT;";
                   	break;
              default: // Keine Verarbeitung
                   return ''; 
          	       break;
       }
		if(!$tmp_result=$db->db_query($qry))
		{
			$oWettbewerb->Error[]= $db->db_last_error();
		}	
	
       if (empty($tmp_result)) // Wenn kein Datenbankfehler aufgetreten ist OK-Information senden
    		 $tmp_result="<p>".'Bild'." Upload : ".$_FILES['bild']['name']." (".$_FILES['bild']['type'] .")</p>";
       return $tmp_result; // DB Fehler
  } // Ende Bild Upload laden

#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_create_url Erzeugt eine URL fuer Kommune-Wettbewerb
*
* @param $workurl welche Seite soll aufgerufen werden. Default die Startseite
* @param $oWettbewerb Array mit den Wettbewerb,Team,Wettbewerber und Benutzerdaten
* @param $spezialparameter Array mit weiteren Parameter
* @param $textanzeige Text der im HREF angezeigt werden soll
* @param $titleanzeige Titel der im HREF angezeigt werden soll
*
* @return HREF als String
*
*/
function kommune_funk_create_href($workurl="",$oWettbewerb=array(),$spezialparameter=array(),$textanzeige="",$titleanzeige="")
{
	$cTmpHREF='<a title="'.(!empty($titleanzeige)?$titleanzeige:(!empty($textanzeige)?$textanzeige:$workurl)).'" href="'.kommune_funk_create_url($workurl,$oWettbewerb,$spezialparameter).'">'.(!empty($textanzeige)?$textanzeige:$workurl).'</a>';
	return $cTmpHREF;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_create_url Erzeugt eine URL fuer Kommune-Wettbewerb
*
* @param $workurl welche Seite soll aufgerufen werden. Default die Startseite
* @param $oWettbewerb Array mit den Wettbewerb,Team,Wettbewerber und Benutzerdaten
* @param $spezialparameter Array mit weiteren Parameter
*
* @return URL als String
*
*/
function kommune_funk_create_url($workurl="",$oWettbewerb=array(),$spezialparameter=array() )
{
	$cTmpUrl=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.(!empty($workurl)?$workurl:constKommuneAnzeigeDEFAULT);
	$cTmpUrl.=(isset($oWettbewerb->team_kurzbz)?'&amp;team_kurzbz='.trim($oWettbewerb->team_kurzbz):'');
	$cTmpUrl.=(isset($oWettbewerb->wbtyp_kurzbz)?'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz):'');
	$cTmpUrl.=(isset($oWettbewerb->wettbewerb_kurzbz)?'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz):'');
	if (is_array($spezialparameter) && count($spezialparameter)>0)
	{
		while (list( $key, $value ) = each($spezialparameter) )
			$cTmpUrl.='&amp;'.$key.'='.$value;
	}		
	return $cTmpUrl; 
}
/* 
*-------------------------------------------------------------------------------------------	
* Bilder  
*      HEX - String , Sting - Hex
*
*--------------------------------------------------------------------------------------------------
*/
function kommune_strhex($string)
{
   return base64_encode($string);
}  
function kommune_hexstr($hex)
{
    return base64_decode($hex);
}



?>