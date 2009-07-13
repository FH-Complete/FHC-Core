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
* @showTeamEinladung  Spieler/Team einladung an einen anderen Spieler/Team zu einem Match
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return  HTML Ausgabe der Seite mit Einladung,Best&auml;tigung, Erledigung
*
*/
function showTeamEinladung($oWettbewerb)
{
	// Plausib

	if (empty($oWettbewerb->team_kurzbz) && isset($oWettbewerb->EigeneWettbewerbe[0]))
		$oWettbewerb->team_kurzbz=$oWettbewerb->EigeneWettbewerbe[0]['team_kurzbz'];
	if (empty($oWettbewerb->team_kurzbz) && empty($oWettbewerb->userUID))	   	
		return "Keine Angaben &uuml;ber das Team gefunden!";
	if (empty($oWettbewerb->wettbewerb_kurzbz))	   	
		return "Keine Angaben &uuml;ber den Wettbewerb gefunden!";
   	if (empty($oWettbewerb->team_kurzbz_einladung))
		return 'Fehler bei der Einladung! Es fehlt das Eingeladene Team/Spieler.<br /> <a href="'. $_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'=" title="Startseite,Home,Beginn">zur Startseite </a>'; 
	if ($oWettbewerb->team_kurzbz==$oWettbewerb->team_kurzbz_einladung)
		return 'Fehler bei der Einladung!  Eingeladene und Einladende Team/Spieler sind gleich!.<br /> <a href="'. $_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'=" title="Startseite,Home,Beginn">zur Startseite </a>'; 

	// Initialisierung
	$showHTML='';
	
	// Wettbewerb-Teams
 	$WettbewerbTeam= new komune_wettbewerbteam('','',$oWettbewerb->wettbewerb_kurzbz);

	// Einladung AN
	$WettbewerbTeam->InitWettbewerbteam();
	$WettbewerbTeam->setTeam_kurzbz($oWettbewerb->team_kurzbz_einladung);
	$WettbewerbTeam->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);
	if ($WettbewerbTeam->loadWettbewerbteam())
		$oWettbewerb->EinladungAnTeam=$WettbewerbTeam->getWettbewerbteam();
	else	
		$oWettbewerb->Error[]=$WettbewerbTeam->getError();

	// Einladung VON
	$WettbewerbTeam->InitWettbewerbteam();
	$WettbewerbTeam->setTeam_kurzbz($oWettbewerb->team_kurzbz);
	$WettbewerbTeam->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);
	if ($WettbewerbTeam->loadWettbewerbteam())
		$oWettbewerb->EinladungVonTeam=$WettbewerbTeam->getWettbewerbteam();
	else
		$oWettbewerb->Error[]=$WettbewerbTeam->getError();

	if (isset($WettbewerbTeam)) unset($WettbewerbTeam);

	// -------------------------- Verarbeitung Request
	// Submit Verarbeiten :: Check - Request - Datenverarbeitung

   	$cTmpSubmitVerarbeitung = (isset($_REQUEST['einladen']) ? $_REQUEST['einladen']:'');
		
#	echo $cTmpSubmitVerarbeitung;		
#	var_dump($_REQUEST);
#	exit;			
		
	if ($cTmpSubmitVerarbeitung)
		showTeamEinladung_submit($oWettbewerb,$cTmpSubmitVerarbeitung);

	// -------------------------- HTML Anzeige 
	// Header
	if (empty($oWettbewerb->match_id))
	    	$showHTML.='<h1 style="text-align:center;"> Wettbewerb-Forderung (Einladung) </h1>';
	else
	    	$showHTML.='<h1 style="text-align:center;"> Wettbewerb-Daten </h1>';
			
	// -------------------------- TEAM Information HTML Anzeige 
	$showHTML.='<table summary="Anzeige der Spieler oder Team">';
	$showHTML.='<tr>';
		$showHTML.='<td style="vertical-align: top;width:50%;">';
			$showHTML.='<h3>forderndes Team / Spieler</h3>';		
		$showHTML.='</td>';
		$showHTML.='<td style="vertical-align: top;width:50%;">';
			$showHTML.='<h3>gefordertes Team / Spieler</h3>';
		$showHTML.='</td>';
	$showHTML.='</tr>';
	if (isset($oWettbewerb->EinladungVonTeam[0]) && isset($oWettbewerb->EinladungAnTeam[0]) )
	{
		$showHTML.='<tr>';
		$showHTML.='<td style="vertical-align: top;width:50%;">';
			$iPopUp=2; // Anzeige der Teamspieler (ohne Spielergebnis, mit Teamspieler)
			$cPopUpIDKey='EgWb_VonTeam_'.time();// Eindeutiger Key fuer PopUp IDs	
			#Alternative - Anzeige mit kommune_funk_show_wettbewerbteam ( der erste Datensatz genuegt )
			reset($oWettbewerb->EinladungVonTeam);
			$showHTML.=kommune_funk_show_wettbewerbteam($oWettbewerb->EinladungVonTeam[0],$oWettbewerb,$cPopUpIDKey."1",$iPopUp);
		$showHTML.='</td>';
		$showHTML.='<td style="vertical-align: top;width:50%;">';
			$iPopUp=2; // Anzeige der Teamspieler (ohne Spielergebnis, mit Teamspieler)
			$cPopUpIDKey='EgWb_AnTeam_'.time();// Eindeutiger Key fuer PopUp IDs	
			#Alternative - Anzeige mit kommune_funk_show_wettbewerbteam ( der erste Datensatz genuegt )
			reset($oWettbewerb->EinladungAnTeam);
			$showHTML.=kommune_funk_show_wettbewerbteam($oWettbewerb->EinladungAnTeam[0],$oWettbewerb,$cPopUpIDKey."2",$iPopUp);
		$showHTML.='</td>';
		$showHTML.='</tr>';
	}	
	$showHTML.='</table>';

	// -------------------------- INPUT HTML Anzeige 
	$showHTML.='<h3> bearbeiten aktuelles Match </h3>';
	$showHTML.=showTeamEinladung_show($oWettbewerb);
	
	$showHTML.=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/moreright.gif" border="0" />&nbsp;zur&nbsp;Startseite&nbsp;','&nbsp;zur&nbsp;Startseite&nbsp;');

	// Wettbewerbsinformation Ende
	return $showHTML;

}

#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamEinladung_show  Anzeigenaufbau Input,Bestaetigungen
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return  HTML Ausgabe der Seite mit Einladung,Best&auml;tigung, Erledigung
*
*/
function showTeamEinladung_show($oWettbewerb)
{
	$showHTML='';
	
	
	if (!isset($oWettbewerb->EigeneWettbewerbe[0]['team_kurzbz'])
	&& !isset($oWettbewerb->EinladungAnTeam[0]['team_kurzbz']) )
		return $showHTML;

	// Ermitteln Spieler / Team welche gerade Online die Daten bearbeiten
	$bTmpForderer=false;
	$bTmpGeforderter=false;

	// Wettbewerb-Teams
 	$WettbewerbTeam= new komune_wettbewerbteam('','',$oWettbewerb->wettbewerb_kurzbz);

	// Ermitteln welcher Wettbewer gerade Online ist : Check mit Forder,EInladung
	$WettbewerbTeam->InitWettbewerbteam();
	$WettbewerbTeam->setTeam_kurzbz(trim($oWettbewerb->team_kurzbz_einladung));
	$WettbewerbTeam->setWettbewerb_kurzbz(trim($oWettbewerb->wettbewerb_kurzbz));
	$WettbewerbTeam->setUID($oWettbewerb->userUID);

	if ($WettbewerbTeam->loadWettbewerbteam())
		$bTmpGeforderter=true;
	else	
		$bTmpForderer=true;
	if (isset($WettbewerbTeam)) unset($WettbewerbTeam);

	// Es konnte keine Partei (Forderer/Geforderter) ermittelt werden. Fehler! 
	if (!$bTmpForderer && !$bTmpGeforderter)
		return '<p>Daten Forderer / Gefordertes Team-Spieler stimmen nicht !</p>';	

	// Erzeugen Link fuer SubmitForm
	$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;team_kurzbz='.trim($oWettbewerb->team_kurzbz).'&amp;team_gefordert='.trim($oWettbewerb->team_kurzbz_einladung).'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz).'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz);
	$showHTML.='<form action="'.$paramURL.'" enctype="multipart/form-data" method="post">';
	$showHTML.='<fieldset>';


	
	// Match Ende
	if (isset($oWettbewerb->Einladung[0]['matchbestaetigtdatum']) && !empty($oWettbewerb->Einladung[0]['matchbestaetigtdatum']))
	{
		$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('team_kurzbz'=>trim($oWettbewerb->team_kurzbz_einladung),'wettbewerb_kurzbz'=>trim($oWettbewerb->wettbewerb_kurzbz)),'<img style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;weiter zm Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz,$oWettbewerb->wettbewerb_kurzbz);
		$showHTML.='<b>Spiel ist beendet mit '.$oWettbewerb->Einladung[0]['matchbestaetigtdatum'].'. Ergebnis :'.$oWettbewerb->Einladung[0]['ergebniss'].'</b>';
		$showHTML.='<br />'.$cTmpHREF;

	}
	// Teil 1 Match Einladung und Bestaetigen 
	elseif ($bTmpForderer && (!isset($oWettbewerb->Einladung[0]['bestaetigtamum']) || empty($oWettbewerb->Einladung[0]['bestaetigtamum'])) )
	{
		$showHTML.='<legend>Match Ort und Zeitpunkt</legend>';
		// Der Forderer kann bis zur Bestaetigung die Match Orts- und Zeitangaben aendern
		$showHTML.=showTeamEinladung_show_ort_zeit($oWettbewerb);
	}
	// Teil 2 Match Einladung und Bestaetigen OFFEN
	elseif ($bTmpGeforderter && !empty($oWettbewerb->match_id) && (!isset($oWettbewerb->Einladung[0]['bestaetigtamum']) || empty($oWettbewerb->Einladung[0]['bestaetigtamum'])) )
	{
		$showHTML.='<legend>Match Ort und Zeitpunkt best&auml;tigen</legend>';

		$showHTML.='<br />'.(isset($oWettbewerb->Einladung[0]['matchort'])?'Austragungsort ist '.$oWettbewerb->Einladung[0]['matchort']:'').' '.(isset($oWettbewerb->Einladung[0]['matchdatumzeit'])?' am '.$oWettbewerb->Einladung[0]['matchdatum'].' '.$oWettbewerb->Einladung[0]['matchzeit']:'');

		$showHTML.='<br /><a href="'.$paramURL.'&amp;match_id='.$oWettbewerb->match_id.'&amp;einladen=99">die Forderung ablehnen.</a>
				<br /><br /><input style="display:none" name="einladen" value="2" /><input value="Ich m&ouml;chte die Einladung best&auml;tigen ( annehmen )" type="submit" />
				';


	}	
	// Teil 3 Match Einladung und Bestaetigen 
	elseif ($bTmpForderer && !empty($oWettbewerb->match_id) && (!isset($oWettbewerb->Einladung[0]['matchbestaetigtdatum']) || empty($oWettbewerb->Einladung[0]['matchbestaetigtdatum'])) )
	{
		$showHTML.=showTeamEinladung_show_ergebnisseintrag($oWettbewerb);
	}
	// Teil 4 Match Ergebnis  
	elseif ($bTmpGeforderter && !empty($oWettbewerb->match_id) && (isset($oWettbewerb->Einladung[0]['ergebniss']) && !empty($oWettbewerb->Einladung[0]['ergebniss'])) )
	{
		$showHTML.='<br />Ich m&ouml;chte das Spielergebnis '.$oWettbewerb->Einladung[0]['ergebniss'].' &nbsp;<input style="display:none" name="einladen" value="4" /><input value="best&auml;tigen" type="submit" />';
	}
	else
	{
		$showHTML.='<br />zur Zeit sind keine Eingaben notwenig.';
	}
	$showHTML.='<input style="display:none" name="match_id" value="'.$oWettbewerb->match_id.'" />';
	
	$showHTML.='</fieldset>';
	$showHTML.='</form> ';
	
	
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamEinladung_show_ergebnisseintrag Ein- Ausgabeformular zum Ort,Zeitpunkt eintragen (Ins/Upd)
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return  HTML Ausgabe des Beginn der Forderung (Matchstart)
*
*/
function showTeamEinladung_show_ort_zeit($oWettbewerb)
{	

	// Init
		$cTmpCheckHeute = date("d.m.Y", mktime(0,0,0,date("m"),date("d"),date("y")));

		$cTmpMatchort=(isset($oWettbewerb->Einladung[0]['matchort'])?$oWettbewerb->Einladung[0]['matchort']:'');
		$cTmpMatchzeit=(isset($oWettbewerb->Einladung[0]['matchzeit'])?$oWettbewerb->Einladung[0]['matchzeit']:'12:00');
		$cTmpMatchdatum=(isset($oWettbewerb->Einladung[0]['matchdatum'])?$oWettbewerb->Einladung[0]['matchdatum']:$cTmpCheckHeute);


	$showHTML='';
		$showHTML.='<table summary="Eingabe der Wettbewerbseinladung">';
		$showHTML.='<tr>
				<td>Ort</td>
				<td><input name="matchort" size="32" maxlength="32"  value="'.$cTmpMatchort.'" /></td>
			</tr>';
		$showHTML.='<tr>
				<td>Datum</td>
				<td>';

		$showHTML.='<select  name="matchdatum">';
					// Ein altes Datum muss auch noch hinzugefuegt werden. Datum koennte sonst beim Update nicht mehr gesetzt werden
					if (!empty($oWettbewerb->Einladung[0]['gefordertamum']) && strtotime($oWettbewerb->Einladung[0]['gefordertamum'])<time() )
						$showHTML.='<option selected="selected" value="'. $cTmpMatchdatum.'">'. $cTmpMatchdatum.'</option>';	

					for ($timeIND=0;$timeIND<90;$timeIND++)
					{
						$cTmpdatum = date("d.m.Y", mktime(0,0,0,date("m"),date("d")+$timeIND,date("y")));
						if (empty($cTmpMatchdatum))
							$showHTML.='<option '. ($cTmpCheckHeute==$cTmpdatum?'selected="selected"':'') .' value="'. $cTmpdatum.'">'. $cTmpdatum.'</option>';	
						else
							$showHTML.='<option '. ($cTmpMatchdatum==$cTmpdatum?'selected="selected"':'') .' value="'. $cTmpdatum.'">'. $cTmpdatum.'</option>';	
					}	
		$showHTML.='</select>';
		$showHTML.='&nbsp;&nbsp;Zeit&nbsp;';
		$showHTML.='<select  name="matchzeit">';
					for ($timeIND=0;$timeIND<24;$timeIND++)
					{
						$cTmpTime=$timeIND.':00';
						$showHTML.='<option '. ($cTmpMatchzeit==$cTmpTime || $cTmpMatchzeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.$cTmpTime.'">'.$cTmpTime.'</option>';	
						$cTmpTime=$timeIND.':15';
						$showHTML.='<option '. ($cTmpMatchzeit==$cTmpTime || $cTmpMatchzeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.$cTmpTime.'">'.$cTmpTime.'</option>';	
						$cTmpTime=$timeIND.':30';
						$showHTML.='<option '. ($cTmpMatchzeit==$cTmpTime || $cTmpMatchzeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.$cTmpTime.'">'.$cTmpTime.'</option>';	
						$cTmpTime=$timeIND.':45';
						$showHTML.='<option '. ($cTmpMatchzeit==$cTmpTime || $cTmpMatchzeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.$cTmpTime.'">'.$cTmpTime.'</option>';	
					}	
			$showHTML.='</select>';
			$showHTML.='</td>';
		$showHTML.='</tr>';
	
		if (empty($oWettbewerb->match_id))
			$showHTML.='<tr><td>&nbsp;</td><td><br />Ich m&ouml;chte das Team / den Spieler <b>'.$oWettbewerb->team_kurzbz_einladung.'</b>&nbsp;<input value="einladen" type="submit" /></td></tr>';
		elseif (trim($oWettbewerb->EigeneWettbewerbe[0]['team_kurzbz'])==trim($oWettbewerb->EinladungVonTeam[0]["team_kurzbz"]) )
			$showHTML.='<tr><td>&nbsp;</td><td><br />Ich m&ouml;chte das Spiel &nbsp;<input value="&auml;ndern" type="submit" /></td></tr>';
		else
			$showHTML.='<tr><td>&nbsp;</td><td><br /> nur Anzeige f&uuml;r '.$oWettbewerb->EinladungAnTeam[0]["team_kurzbz"].'</td></tr>';
		
		$showHTML.='<tr><td>';		
			$showHTML.='<input style="display:none" name="einladen" value="1" />';
		$showHTML.='</td></tr>';
		
		$showHTML.='</table>';
	
	return $showHTML;
}	
#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamEinladung_show_ergebnisseintrag Ein- Ausgabeformular zum Matchergebniss eintragen (Ins/Upd)
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return  HTML Ausgabe des Ergebnisseintragsformular
*
*/
function showTeamEinladung_show_ergebnisseintrag($oWettbewerb)
{
	$showHTML='';
		$showHTML.='<table summary="Eingabe der Wettbewerbseinladung">';

		$arrSelectTeams='<select  name="team_sieger">';
				$arrSelectTeams.='<option '.(trim($oWettbewerb->Einladung[0]['team_sieger'])==trim($oWettbewerb->EinladungVonTeam[0]["team_kurzbz"])?'selected="selected"':'').' value="'.$oWettbewerb->EinladungVonTeam[0]["team_kurzbz"].'">'.$oWettbewerb->EinladungVonTeam[0]["team_kurzbz"].'</option>';		
				$arrSelectTeams.='<option '.(trim($oWettbewerb->Einladung[0]['team_sieger'])==trim($oWettbewerb->EinladungAnTeam[0]["team_kurzbz"])?'selected="selected"':'').' value="'.$oWettbewerb->EinladungAnTeam[0]["team_kurzbz"].'">'.$oWettbewerb->EinladungAnTeam[0]["team_kurzbz"].'</option>';	
		$arrSelectTeams.='</select>';
		

		$showHTML.='<tr><td>Sieger Spieler/Team</td><td>'.$arrSelectTeams.'</td></tr>';
		$showHTML.='<tr><td>Ergebnis</td><td><input name="ergebniss"  value="'.$oWettbewerb->Einladung[0]['ergebniss'].'" size="17" maxlength="16" /> </td></tr>';
			
		$showHTML.='<tr><td>&nbsp;</td>
				<td><br />Ich m&ouml;chte das Spielergebnis &nbsp;';
			
		if (empty($oWettbewerb->Einladung[0]['ergebniss']))		
			$showHTML.='<input value="erfassen" type="submit" />';
		else
			$showHTML.='<input value="&auml;ndern" type="submit" />';
		$showHTML.='</td></tr>';
		
		$showHTML.='<tr><td>';		
			$showHTML.='<input style="display:none" name="einladen" value="3" />';
		$showHTML.='</td></tr>';
	$showHTML.='</table>';
	
	return $showHTML;
}	


function showTeamEinladung_submit($oWettbewerb,$cTmpSubmitVerarbeitung=false)
{	
	$showHTML='';
	if ( !$cTmpSubmitVerarbeitung)
		return $showHTML;	
	
		
	$WettbewerbTeameinladen= new komune_wettbewerbeinladungen($oWettbewerb->match_id,$oWettbewerb->team_kurzbz,$oWettbewerb->team_kurzbz_einladung,$oWettbewerb->wettbewerb_kurzbz,'');
			
	// Teil1 Foderung anlegen / aendern
	if ($cTmpSubmitVerarbeitung==1) // Bestaetigen der Einladung
	{
	   
	   	$cTmpMatchdatum = (isset($_REQUEST['matchdatum']) ? $_REQUEST['matchdatum']:'');
	   	$cTmpMatchzeit = (isset($_REQUEST['matchzeit']) ? $_REQUEST['matchzeit']:'');
	   	$cTmpMatchort = (isset($_REQUEST['matchort']) ? $_REQUEST['matchort']:'');

		$date=explode('.',$cTmpMatchdatum);
		$time=explode(':',$cTmpMatchzeit);
		
		if (@checkdate($date[1], $date[0], $date[2]) )
			$cTmpMatchdatumzeit=@mktime($time[0], $time[1], 0, @date($date[1]),@date($date[0]),@date($date[2]) );
		else
			$cTmpMatchdatumzeit=Time();
		$WettbewerbTeameinladen->setTeam_forderer($oWettbewerb->team_kurzbz);
		$WettbewerbTeameinladen->setTeam_gefordert($oWettbewerb->team_kurzbz_einladung);

		$WettbewerbTeameinladen->setMatchdatumzeit($cTmpMatchdatumzeit);	
 		$WettbewerbTeameinladen->setMatchort($cTmpMatchort);	
		$WettbewerbTeameinladen->setGefordertamum(Time());	
		$WettbewerbTeameinladen->setGefordertvon($oWettbewerb->userUID);
		$WettbewerbTeameinladen->setMatch_id($oWettbewerb->match_id);
		if (empty($oWettbewerb->match_id))
			$WettbewerbTeameinladen->setSwitchGewinner(0);

		if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
			$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
		else
			return $oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();

		if (isset($WettbewerbTeameinladen)) unset($WettbewerbTeameinladen);	
			
			
		// Senden Information per email	
			$oWettbewerb->match_id=$oWettbewerb->Einladung[0]['match_id'];
		#exit(Test($oWettbewerb->Einladung));
		
			$betreff='Neue Forderung im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz;
	
			$cTmpName=trim($oWettbewerb->userUID);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;
				
			$cTmpName2=trim($oWettbewerb->EinladungAnTeam[0]['uid']);
			$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName2=$pers->langname;				


			$paramURL=constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;team_kurzbz='.trim($oWettbewerb->team_kurzbz_einladung).'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz).'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz);
			$paramURL=''; // ohne Parameter
			$cTmpURL=str_replace($_SERVER["QUERY_STRING"],$paramURL,$_SERVER["HTTP_REFERER"]);

			$iTmpAnzahlTeam=1;
			if (isset($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]['team_kurzbz']))
				$iTmpAnzahlTeam=count($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]);
	
			$text=$cTmpName." - ( Kurzbezeichnug ".$oWettbewerb->team_kurzbz." )\n\n";
			$text.="hat ".($iTmpAnzahlTeam>1?'das Team':'den Spieler')." ".$cTmpName2." ( Kurzbezeichnug ".$oWettbewerb->team_kurzbz_einladung.") gefordert am ".$oWettbewerb->Einladung[0]['matchdatum']." um ".$oWettbewerb->Einladung[0]['matchzeit']." , "."\n";
			$text.=$oWettbewerb->Einladung[0]['matchort']." einen ".$oWettbewerb->wettbewerb_kurzbz ." Wettbewerb auszutragen."."\n\n";

			$text.="\n\n zum Aufruf der Forderung benutzen sie folgenden Link : ".$cTmpURL."\n\n";
			$text.="Die Einladung wurde von ".$cTmpName."\nam ".$oWettbewerb->Einladung[0]['gefordertamumdatum']." um ".$oWettbewerb->Einladung[0]['gefordertamumzeit'] .(empty($oWettbewerb->match_id)?" erfasst.":" geaendert.")."\n";


			// Einladung an Spieler/Team Information
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->EinladungAnTeam[0]['uid'],$betreff,$text,$oWettbewerb->EinladungVonTeam[0]['uid'],$oWettbewerb);
			// Moderator Information
	#???	$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->Wettbewerb[0]['uid'],$betreff." [Moderatorinformtion]",$text,$oWettbewerb->EinladungVonTeam[0]['uid'],$oWettbewerb);
		return true;
	} // Ende Teil 1			


	if (empty($oWettbewerb->match_id)) // Ab Verarbeitungsteil 2 muss die MatchID dabei sein
		return $oWettbewerb->Error[]="Bei der Verarbeitung ist ein Fehler aufgetreten. Die MatchID wurde nicht gefunden.";



		
		
	if ($cTmpSubmitVerarbeitung==99) // Bestaetigen der Einladung
	{
			$bSwitchWork=true;		
			$WettbewerbTeameinladen->InitWettbewerbeinladungen();	

			$WettbewerbTeameinladen->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);

			$WettbewerbTeameinladen->setMatch_id($oWettbewerb->match_id);			

			$WettbewerbTeameinladen->setTeam_forderer($oWettbewerb->team_kurzbz);
			$WettbewerbTeameinladen->setTeam_gefordert($oWettbewerb->team_kurzbz_einladung);
 			$WettbewerbTeameinladen->setTeam_sieger($oWettbewerb->team_kurzbz);	
			
			$WettbewerbTeameinladen->setBestaetigtamum(Time());	
 			$WettbewerbTeameinladen->setBestaetigtvon($oWettbewerb->userUID);	

			$WettbewerbTeameinladen->setErgebniss('abgelehnt');

			if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
				$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
			else
				$oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();
			
			$WettbewerbTeameinladen->setMatchbestaetigtamum(Time());	
 			$WettbewerbTeameinladen->setMatchbestaetigtvon($oWettbewerb->userUID);	
		
			if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
				$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
			else
				$oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();

			if (isset($WettbewerbTeameinladen)) unset($WettbewerbTeameinladen);	

			$iTmpAnzahlTeam=1;
			if (isset($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]['team_kurzbz']))
				$iTmpAnzahlTeam=count($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]);

			$cTmpName=trim($oWettbewerb->userUID);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;
				
			$cTmpName2=trim($oWettbewerb->EinladungVonTeam[0]['uid']);
			$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName2=$pers->langname;		
								
			$betreff='Die Forderung im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz ." wurde NICHT angenommen";
			$text=($iTmpAnzahlTeam>1?'Das Team':'Der Spieler')."\n".$cTmpName ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz_einladung." )\n\n";
			$text.="hat die Forderung von ".$cTmpName2 ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz." )\n\nNICHT angenommen\n\nam ".$oWettbewerb->Einladung[0]['matchdatum']." um ".$oWettbewerb->Einladung[0]['matchzeit']." , "."\n";
			$text.=$oWettbewerb->Einladung[0]['matchort']." den Wettbewerb ".$oWettbewerb->wettbewerb_kurzbz ." auszutragen."."\n\n";

			$text.=" Der Forderer ".$cTmpName2 ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz." ) wird als Sieger eingetragen !\n\n";
			
			$text.="Die Einladung wurde abgelehnt von ".$cTmpName."\nam ".$oWettbewerb->Einladung[0]['bestaetigtdatum']." um ".$oWettbewerb->Einladung[0]['bestaetigtzeit'] ." erfasst."."\n";
			// Einladung an Spieler/Team Information

			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->EinladungVonTeam[0]['uid'],$betreff,$text,$oWettbewerb->userUID,$oWettbewerb);
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->userUID,$betreff,$text,$oWettbewerb->EinladungVonTeam[0]['uid'],$oWettbewerb);
			// Moderator Information
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->Wettbewerb[0]['uid'],$betreff." [Moderatorinformtion]",$text,$oWettbewerb->userUID,$oWettbewerb);
			
			return true;
	}
	elseif ($cTmpSubmitVerarbeitung==2) // Bestaetigen der Einladung
	{
	
	
			$WettbewerbTeameinladen->InitWettbewerbeinladungen();	

			$WettbewerbTeameinladen->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);
			$WettbewerbTeameinladen->setTeam_forderer($oWettbewerb->team_kurzbz);
			$WettbewerbTeameinladen->setTeam_gefordert($oWettbewerb->team_kurzbz_einladung);
			$WettbewerbTeameinladen->setBestaetigtamum(Time());	
 			$WettbewerbTeameinladen->setBestaetigtvon($oWettbewerb->userUID);	
			$WettbewerbTeameinladen->setMatch_id($oWettbewerb->match_id);			

			
			
			if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
				$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
			else
				$oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();
			if (isset($WettbewerbTeameinladen)) unset($WettbewerbTeameinladen);	

			$iTmpAnzahlTeam=1;
			if (isset($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]['team_kurzbz']))
				$iTmpAnzahlTeam=count($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]);
							
			$cTmpName=trim($oWettbewerb->userUID);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;
				
#var_dump($oWettbewerb->Einladung);				
				
			$cTmpName2=trim($oWettbewerb->Einladung[0]['gefordertvon']);
			
			$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName2=$pers->langname;				
				
			
			$betreff='Die Forderung im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz ." wurde angenommen";
			$text=($iTmpAnzahlTeam>1?'Das Team':'Der Spieler')."\n".$cTmpName ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz_einladung." )\n\n";
			$text.="hat die Forderung von ".$cTmpName2 ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz." )\nangenommen am ".$oWettbewerb->Einladung[0]['matchdatum']." um ".$oWettbewerb->Einladung[0]['matchzeit']." , "."\n";
			$text.=$oWettbewerb->Einladung[0]['matchort']." den Wettbewerb ".$oWettbewerb->wettbewerb_kurzbz ." auszutragen."."\n\n";
			$text.="Die Einladung wurde von ".$cTmpName."\nam ".$oWettbewerb->Einladung[0]['bestaetigtdatum']." um ".$oWettbewerb->Einladung[0]['bestaetigtzeit'] ." erfasst."."\n";
			// Einladung an Spieler/Team Information
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->Einladung[0]['gefordertvon'],$betreff,$text,$oWettbewerb->userUID,$oWettbewerb);

		return true;
	}
	
	if ($cTmpSubmitVerarbeitung==3) // Ergebnis eintragen Forderer
	{

			$WettbewerbTeameinladen->InitWettbewerbeinladungen();	

			$WettbewerbTeameinladen->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);

	    	$cTmpTeam_sieger = (isset($_REQUEST['team_sieger']) ? $_REQUEST['team_sieger']:'');
   			$cTmpErgebniss = (isset($_REQUEST['ergebniss']) ? $_REQUEST['ergebniss']:'');
		
			$WettbewerbTeameinladen->setTeam_forderer($oWettbewerb->team_kurzbz);
			$WettbewerbTeameinladen->setTeam_gefordert($oWettbewerb->team_kurzbz_einladung);
			
			$WettbewerbTeameinladen->setErgebniss($cTmpErgebniss);	
 			$WettbewerbTeameinladen->setTeam_sieger($cTmpTeam_sieger);	
			$WettbewerbTeameinladen->setMatch_id($oWettbewerb->match_id);	
			
			if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
				$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
			else
				$oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();
			if (isset($WettbewerbTeameinladen)) unset($WettbewerbTeameinladen);	
					

			$cTmpName=trim($oWettbewerb->userUID);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;
				
			$cTmpName2=trim($oWettbewerb->EinladungAnTeam[0]['uid']);
			$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName2=$pers->langname;				
				
			$paramURL=constKommuneParmSetWork.'='.constKommuneEinladenTEAM.'&amp;team_kurzbz='.trim($oWettbewerb->team_kurzbz_einladung).'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz).'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz);
			$paramURL=''; // ohne Parameter
			$cTmpURL=str_replace($_SERVER["QUERY_STRING"],$paramURL,$_SERVER["HTTP_REFERER"]);
			
			$betreff='Das Ergebnis im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz;
			
			$text="Das Ergebnis im Wettbewerb".$oWettbewerb->wettbewerb_kurzbz."\n\n\n";
			$text.="zwischen ".$cTmpName2." ( Kurzzeichen ".$oWettbewerb->EinladungAnTeam[0]['uid']." )\n\nund".$cTmpName ." ( Kurzzeichen ".$oWettbewerb->team_kurzbz .")\n\n\n";

			$text.="Sieger ". (trim($oWettbewerb->Einladung[0]['team_sieger'])==trim($oWettbewerb->team_kurzbz)?$cTmpName2." ( Kurzzeichen ".$oWettbewerb->EinladungAnTeam[0]['uid']." )":$cTmpName2." ( Kurzzeichen ".$oWettbewerb->EinladungAnTeam[0]['uid']." )" )."\"\n\n\n";
			$text.="mit dem Ergebnis \"".$oWettbewerb->Einladung[0]['ergebniss']."\n\n";
			
			$text.="zum Aufruf der Bestaetigung benutzen sie ".$cTmpName2." folgenden Link : ".$cTmpURL."\n\n";


			$cTmpCheckHeute = date("d.m.Y",time());
			$cTmpCheckZeit = date("H:i",time());
			$text.="\n\nErgebnis wurde eingetragen von ".$cTmpName."\n\nam ". $cTmpCheckHeute. ", um ".$cTmpCheckZeit.".\n\n\n";

			// Einladung an Spieler/Team Information
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->EinladungAnTeam[0]['uid'],$betreff,$text,$oWettbewerb->userUID,$oWettbewerb);

		return true;		
	}
	if ($cTmpSubmitVerarbeitung==4 ) // Ergebnis bestaetigen GeForderer
	{
			$bSwitchWork=true;		
			$WettbewerbTeameinladen->InitWettbewerbeinladungen();	

			$WettbewerbTeameinladen->setWettbewerb_kurzbz($oWettbewerb->wettbewerb_kurzbz);			
			$WettbewerbTeameinladen->setTeam_forderer($oWettbewerb->team_kurzbz);
			$WettbewerbTeameinladen->setTeam_gefordert($oWettbewerb->team_kurzbz_einladung);
			$WettbewerbTeameinladen->setMatchbestaetigtamum(Time());	
 			$WettbewerbTeameinladen->setMatchbestaetigtvon($oWettbewerb->userUID);	
			$WettbewerbTeameinladen->setMatch_id($oWettbewerb->match_id);			

			if ($WettbewerbTeameinladen->saveWettbewerbeinladung())
				$oWettbewerb->Einladung=$WettbewerbTeameinladen->getWettbewerbeinladung();
			else
				$oWettbewerb->Error[]=$WettbewerbTeameinladen->getError();

			if (isset($WettbewerbTeameinladen)) unset($WettbewerbTeameinladen);	

			$iTmpAnzahlTeam=1;
			if (isset($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]['team_kurzbz']))
				$iTmpAnzahlTeam=count($oWettbewerb->Team[trim($oWettbewerb->team_kurzbz_einladung)][0]);

#exit(Test($oWettbewerb->Einladung[0]));

			$cTmpName=trim($oWettbewerb->Einladung[0]['bestaetigtvon']);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;
				
			$cTmpName2=trim($oWettbewerb->Einladung[0]['gefordertvon']);
			$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName2=$pers->langname;				

			if (trim($oWettbewerb->Einladung[0]['team_gefordert'])==trim($oWettbewerb->Einladung[0]['team_sieger']))
				$cTmpName3=$cTmpName;
			else	
				$cTmpName3=$cTmpName2;
				
			$betreff='Das Ergebnis im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz;
			
			$text='Das Ergebnis im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz."\n\n";
			
			$text.=$cTmpName." ( Kurzbezeichnug ".$oWettbewerb->Einladung[0]['team_gefordert']." )\n\n";

			$text.="hat das Ergebnis der Forderung gegen\n\n";
#($iTmpAnzahlTeam>1?'das Team':'den Spieler')." ".
			$text.=$cTmpName2." ( Kurzbezeichnug ".$oWettbewerb->Einladung[0]['team_forderer'].")\n\nbestaetigt.\n\n\n\n\n";

			$text.="Gratulation dem Sieger\n\n".$cTmpName3."  ( Kurzzeichen ".$oWettbewerb->Einladung[0]['team_sieger'] ." )\n\n";
			
			$text.="mit dem Spiel.- Matchergebnis : ".$oWettbewerb->Einladung[0]['ergebniss']."\n\n\n\n\n";

			$cTmpCheckHeute = date("d.m.Y",time());
			$cTmpCheckZeit = date("H:i",time());

			
			$cTmpName=trim($oWettbewerb->userUID);
			$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
			if (isset($pers->langname)) 
				$cTmpName=$pers->langname;			
			$text.="Das Ergebnis wurde bestaetigt von ".$cTmpName."\n\nam ". $cTmpCheckHeute. ", um ".$cTmpCheckZeit.".\n";

			// Ergebnis bestaetigt - Information an Spieler/Team senden
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->EinladungVonTeam[0]['uid'],$betreff,$text,$oWettbewerb->userUID,$oWettbewerb);
			$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->userUID,$betreff,$text,$oWettbewerb->EinladungVonTeam[0]['uid'],$oWettbewerb);
			// Moderator Information
			return true;
		}
	return false;	
}
?>