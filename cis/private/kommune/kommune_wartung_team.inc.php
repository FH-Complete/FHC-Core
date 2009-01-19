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
* @showTeamWartung		Team zu einem Wettbewerb Anzeigen , und die Datenpflege
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return 			Teaminformationen, und Pflegeingabefelder in HTML Ausgabeform
*
*/
function showTeamWartung($oWettbewerb)
{
	if ($oWettbewerb->Wettbewerb[0]["teamgroesse"]<2 || $oWettbewerb->Wettbewerb[0]["teamgroesse"]==null)
		$oWettbewerb->Wettbewerb[0]["teamgroesse"]=1;

	// Initialisierung
	$showHTML='';
	// Link zum Wettbewerb als Stringvariable wird im Header und in der Informationszeile benoetigt
	$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,$oWettbewerb,array(),'&nbsp;'.$oWettbewerb->wettbewerb_kurzbz.'&nbsp;');
	 // Header 
       $showHTML.='<div>';
		$showHTML.='<h1 onmouseover="show_layer(\'wettbewerb_popup\');" onmouseout="hide_layer(\'wettbewerb_popup\');" style="text-align:center;">'.$cTmpHREF.'</h1>';
    		$showHTML.='<p>';
				// Moderator PopUp 	
				$showHTML.='<span onmouseover="show_layer(\'wettbewerb_moderator_popup\');" onmouseout="hide_layer(\'wettbewerb_moderator_popup\');">Moderator : '.kommune_funk_pers_langname($oWettbewerb->Wettbewerb[0]['uid'],kommune_funk_benutzerperson($oWettbewerb->Wettbewerb[0]['uid'],$oWettbewerb)).'</span>&nbsp;&nbsp;&nbsp;';
				// Wettbewerb PopUp
				$showHTML.='<span onmouseover="show_layer(\'wettbewerb_popup\');" onmouseout="hide_layer(\'wettbewerb_popup\');"><img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;weiter zum Wettbewerb '.$cTmpHREF.'&nbsp;</span>';
		$showHTML.='</p>';

	// Header Moderator PopUp Anzeige
		$showHTML.='<div style="display:none;position: absolute;z-index:99;"  onmouseover="show_layer(\'wettbewerb_moderator_popup\');" onmouseout="hide_layer(\'wettbewerb_moderator_popup\');" id="wettbewerb_moderator_popup">';
		if (isset($oWettbewerb->Wettbewerb[0]))	
			$showHTML.=kommune_funk_popup_benutzer($oWettbewerb->Wettbewerb[0]['uid'],$oWettbewerb);
		$showHTML.='</div>';	
		
	// Header Wettbewerb PopUp	Anzeige
		$showHTML.='<div style="display:none;position: absolute;z-index:100;"  onmouseover="show_layer(\'wettbewerb_popup\');" onmouseout="hide_layer(\'wettbewerb_popup\');" id="wettbewerb_popup">';
			if (isset($oWettbewerb->Wettbewerb[0]))	
				$showHTML.=kommune_funk_popup_wettbewerb($oWettbewerb->Wettbewerb[0],$oWettbewerb);
		$showHTML.='</div>';	
	$showHTML.='</div>'; 
	// Header Ende
	
	// Plausib	
	if (empty($oWettbewerb->team_kurzbz) && empty($oWettbewerb->userUID))	   	
		return $showHTML.="Keine Angaben &uuml;ber das Team gefunden!";

	if (empty($oWettbewerb->wettbewerb_kurzbz))	   	
		return $showHTML.="Keine Angaben &uuml;ber den Wettbewerb gefunden!";
		
	// Datenloeschen zum Team / Spieler
	if (isset($_REQUEST['del']) || isset($_REQUEST['delete']))
	{
		$_REQUEST['rang']=9999;
	}

	// Datenspeicherung  
	// 	 Submit wurde gedrueckt
	if (isset($_REQUEST['array_userUID']))
	{
		// Bilderupload
		showTeamWartung_Bildupload($oWettbewerb);
		//Datenpflege - Funktion , wird nur aufgerufen wenn der Standartwert im Kurzzeichenfeld geaendert wurde
		$arrTmpWettbewerteam=showTeamWartung_Datenverarbeiten($oWettbewerb);
		if (is_array($arrTmpWettbewerteam)) // Wichtig das nach der Neuanlage die Vergleichsvariable gesetzt wird.
			$oWettbewerb->team_kurzbz_old=$oWettbewerb->team_kurzbz;
	}	

	// Pruefen ob nach dem Speichern (Submit) ein Fehler ( Kein DatenArray)
	// aufgetreten ist. Bei einem Fehler muessen die Request-Daten
	// in die Tabellen zurueck geladen werden fuer die Inputfeld-Werte 
	if (isset($_REQUEST['array_userUID']) && !is_array($arrTmpWettbewerteam) )
	{
		$oWettbewerb->Team[$oWettbewerb->team_kurzbz]=$_REQUEST;
		for ($zeileIND=0;$zeileIND<count($_REQUEST['array_userUID']);$zeileIND++)
			$oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"]=$_REQUEST['array_userUID'][$zeileIND];
	}	

	// Keine Request, und keine bestehenden Daten -> Login User in die Tabellen laden
	if (!isset($oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][0]["uid"]) ) // Neuanlage (es wurde noch nicht Submit gedrueckt)
		$oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][0]["uid"]=$oWettbewerb->userUID;

	// Ausgabe des Template
	$showHTML.=showTeamWartung_erzeugen_HTML($oWettbewerb);
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamWartung_erzeugen_HTML Output HTML fuer Team/Spieler-Daten I/O 
*
* @param $oWettbewerb 	Objekt mit allen Wettbewerbsdaten
*
* @return 				HTML String
*
*/
function showTeamWartung_erzeugen_HTML($oWettbewerb)
{
	$showHTML='';
#exit(kommune_Test($oWettbewerb->Wettbewerb[0]));
	// Form Start
	$cTmpURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneWartungUID.'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz).'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz);
	$showHTML.='<form onsubmit="return checkTeamAnzahl(this,\'array_userUID[]\',\''.$oWettbewerb->Wettbewerb[0]["teamgroesse"].'\');" target="_self" method="post" name="kommunen_work" action="'.$cTmpURL.'" enctype="multipart/form-data">';              

	$showHTML.='<div>'; // Zusammenfassung der kpl. Eingabe


	$cTmpWetbewerbUSERok='';
	if ( isset($oWettbewerb->Wettbewerb) && isset($oWettbewerb->Wettbewerb[0]) && isset($oWettbewerb->Wettbewerb[0]["daten_eingetragen"]) 
	&& trim($oWettbewerb->Wettbewerb[0]["daten_eingetragen"]["uid"])!=trim($oWettbewerb->userUID)  )
		$cTmpWetbewerbUSERok=' style="display:none" ';
	if (empty($oWettbewerb->team_kurzbz) || $oWettbewerb->team_kurzbz==constEingabeFehlt)
		$cTmpWetbewerbUSERok='';

		
	$cSeitenKey="sTeam"	;
	$iPopUp=false;
	if (!empty($cTmpWetbewerbUSERok))
	 	return kommune_funk_show_wettbewerbteam($oWettbewerb->Wettbewerb[0]["daten_eingetragen"],$oWettbewerb,$cSeitenKey,$iPopUp);
 		
#exit($cTmpWetbewerbUSERok.$oWettbewerb->userUID.Test($oWettbewerb->Wettbewerb[0]["daten_eingetragen"]));
		
	$cTmpURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneWartungUID.'&amp;wbtyp_kurzbz='.trim($oWettbewerb->wbtyp_kurzbz).'&amp;wettbewerb_kurzbz='.trim($oWettbewerb->wettbewerb_kurzbz.'&amp;del=1');
	
	// Form Titleanzeige				
	$cTmpHeaderWorkInfo="Datenwartung von ".$oWettbewerb->userUID;

	$showHTML.='<fieldset><legend>'.$cTmpHeaderWorkInfo.'</legend>';
	
	$showHTML.='<table summary="'.$cTmpHeaderWorkInfo.'">';              
	$showHTML.='<tr>';
		// Team Hauptdaten (Kurzbezeichnung,Beschreibung,....)
   		$showHTML.='<td><table summary="Eingabe Team Hauptbeschreibungsinformation">'; 
       		$showHTML.='<tr>
		   				<td style="text-align:right;vertical-align: top;">*<label title="Kurzberzeichnung" for="team_kurzbz">Kurzberzeichnung</label>:</td>
	   					<td>
							<input title="Kurzbezeichnung" id="team_kurzbz" name="team_kurzbz" size="16" maxlength="16" value="'.(empty($oWettbewerb->team_kurzbz)?constEingabeFehlt:trim($oWettbewerb->team_kurzbz)).'" onblur="if (this.value==\'\') this.value=this.defaultValue" onfocus="if (this.value==\''.constEingabeFehlt.'\') this.value=\'\';" />
		   					<input style="display:none"  name="team_kurzbz_old" value="'.($oWettbewerb->team_kurzbz_old==constEingabeFehlt?'':trim($oWettbewerb->team_kurzbz)).'" />
		   				</td>
					</tr>

					<tr>
						<td style="text-align:right;vertical-align: top;"><label title="Bezeichnung" for="bezeichnung">Bezeichnung</label>:</td>
						<td><textarea style="white-space : nowrap;overflow: hidden;" title="Bezeichnung" id="bezeichnung" name="bezeichnung" cols="64" rows="2" >'.(isset($_REQUEST['array_userUID']) && isset($_REQUEST['bezeichnung']) ? $_REQUEST['bezeichnung'] : (isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['bezeichnung']) ? $oWettbewerb->Team[$oWettbewerb->team_kurzbz]['bezeichnung'] :'')).'</textarea></td>
					</tr>

					<tr>
						<td style="text-align:right;vertical-align: top;"><label title="Beschreibung" for="beschreibung">Beschreibung</label>:</td>
						<td><textarea style="white-space : nowrap;overflow: hidden;" title="Beschreibung" id="beschreibung" name="beschreibung" cols="64" rows="4">'.(isset($_REQUEST['array_userUID']) && isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung'] : (isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['beschreibung']) ? $oWettbewerb->Team[$oWettbewerb->team_kurzbz]['beschreibung'] :'')).'</textarea></td>
					</tr>   
					           
					<tr>
						<td>* Pflichteingabe</td>
						<td><input '.$cTmpWetbewerbUSERok.' type="submit" name="wettbewerbteam" value="speichern" /> <span style="display:none">'.(!empty($oWettbewerb->wbtyp_kurzbz)?'<a title="l&ouml;schen '.$oWettbewerb->team_kurzbz.'" target="_parent" href="'.$cTmpURL.'">l&ouml;schen</a>':'').'</span></td>
					</tr>
					';
	 	$showHTML.='</table></td>'; 
	   
		// Logo (Bild) Upload bereich 
	   	$showHTML.='<td style="vertical-align: bottom;">'; 

			// Logo Hex-String aus den Teamdaten wenn noch nicht vorhanden aus einen eventuellen Request		
			$cTmpLogo=(isset($_REQUEST['logo'])?$_REQUEST['logo']:'' );
			if ( !empty($cTmpLogo) && (!isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo']) || empty($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo'])) ) 
				$showHTML.='<br />Bild geladen, aber noch nicht gespeichert !';
			
			if ( empty($cTmpLogo) && isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo']))
				$cTmpLogo=$oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo'];
			
			if (isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo_image']) && !empty($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo_image']) ) 
				$showHTML.=$oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo_image'];
			elseif (isset($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo']) && !empty($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo']) ) 
			{
				$paramURL=$_SERVER['PHP_SELF'].'?'.constKommuneParmSetWork.'='.constKommuneDisplayIMAGE.'&amp;timecheck'.time().(strlen($oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo'])<2000?'&amp;heximg='.$oWettbewerb->Team[$oWettbewerb->team_kurzbz]['logo']:'').'&amp;team_kurzbz='.$oWettbewerb->team_kurzbz.'&amp;wettbewerb_kurzbz='.$oWettbewerb->wettbewerb_kurzbz;
				if (!empty($oWettbewerb->Team[$oWettbewerb->team_kurzbz]["logo"]))
		   			$showHTML.=$oWettbewerb->Team[$oWettbewerb->team_kurzbz]["logo_image"]='<img onmouseover="this.src=\''.$paramURL.'\'" height="80" border="0" alt="'.$oWettbewerb->team_kurzbz.'"  src="'.$paramURL.'" />';
			}
			
			$showHTML.='<br /><br />
				<div style="text-align:right;">
						Logo <input '.$cTmpWetbewerbUSERok.' type="file" name="TeamLogoBild" />
						<input '.$cTmpWetbewerbUSERok.' type="submit" name="submitTeamLogo" value="Upload" />
						<input style="display:none;" name="logo" value="'.$cTmpLogo.'" />						
		            </div>&nbsp;';              
    	   $showHTML.='</td>'; 
       $showHTML.='</tr>'; 

	// Teamspieler Daten Header	
       $showHTML.='<tr>
	   		<td colspan="2">';              
		$showHTML.='<fieldset>';
			$showHTML.='<legend>Teilnehmer '.(isset($oWettbewerb->Wettbewerb[0]["wettbewerbart"])?$oWettbewerb->Wettbewerb[0]["wettbewerbart"]:'').'</legend>';
   	    	$showHTML.='<table summary="Wettbewerb Team Informationen">';              
	       $showHTML.='<tr>
		   		<th style="vertical-align: bottom;"><label title="Spieler Kurzzeichen" for="array_userUID">Spieler</label></th>
				<th style="vertical-align: bottom;" colspan="2">Namen</th>
		     </tr>';   
					 
	  // Teamspieler Daten Header	
	$iTmpCounter=0; // wird beim Team fuer die naechsten freien Zeilen gebraucht
	for ($zeileIND=0;$zeileIND<count($oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz]);$zeileIND++)
	{
		if (empty($oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"]))
			continue;

		$iTmpCounter++;
		$cTmpName='';
		$pers=kommune_funk_benutzerperson($oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"],$oWettbewerb);
		if (isset($pers->nachname)) 
			$cTmpName=$pers->langname;
		elseif ($zeileIND>0)
			$cTmpName='Anwender ist nicht g&uuml;ltig ! ';
			
		// Teammitglieder Wartungszeile
		$showHTML.='<tr>';
		  	$showHTML.='<td>';
		// Wenn der gefundene Anwender gleich der Angemeldete ist die Daten fuer Eingabe sperren
		if (trim($oWettbewerb->userUID)==trim($oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"]))
			$showHTML.=$iTmpCounter.')&nbsp;<b onmouseover="show_layer(\'bild'.$zeileIND.'\');" onmouseout="hide_layer(\'bild'.$zeileIND.'\');">'.$oWettbewerb->userUID.'</b><input id="array_userUID" style="display:none" size="17" maxlength="16" name="array_userUID[]" value="'.$oWettbewerb->userUID.'" />';              
		else
		{
			if (!empty($cTmpWetbewerbUSERok))
			  	$showHTML.='<b onmouseover="show_layer(\'bild'.$zeileIND.'\');"	onmouseout="hide_layer(\'bild'.$zeileIND.'\');">'.$oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"].'</b>';
			
			else
			{
			  	$showHTML.=$iTmpCounter.')&nbsp;
				<input title="Anwender Spieler Kurzzeichen '.(isset($pers->person_id)?$pers->person_id:$oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"]) .'" onclick="show_layer(\'bild'.$zeileIND.'\');"
					 onchange="clear_layer(\'alt'.$zeileIND.'\');clear_layer(\'persimage'.$zeileIND.'\');if(this.value!=\'\') doIt(this.value,\'alt'.$zeileIND.'\');" 
					 onblur="hide_layer(\'bild'.$zeileIND.'\');"
					 size="17" maxlength="16" id="array_userUID" name="array_userUID[]" value="'.$oWettbewerb->TeamBenutzer[$oWettbewerb->team_kurzbz][$zeileIND]["uid"].'" />
					';
			}		
		}					
		$showHTML.='</td>';
		$showHTML.='<td id="alt'.$zeileIND.'">'.$cTmpName.'&nbsp;</td>';
		// Personen Bild PopUp			
		$showHTML.='
		<td id="persimage'.$zeileIND.'">
			<div onmouseover="show_layer(\'bild'.$zeileIND.'\');" onmouseout="hide_layer(\'bild'.$zeileIND.'\');">'.(isset($pers->foto_image) && !empty($pers->foto_image)?'[ Bildanzeige ]':'').'</div>
			<div style="display:none;border:0px outset Black;position: absolute;z-index:99;"  id="bild'.$zeileIND.'">
				'.(isset($pers->foto_image) && !empty($pers->foto_image)?$pers->foto_image:'').'&nbsp;
			</div>
		</td>';
	$showHTML.='</tr>';
	}  
		
	if (isset($pers)) unset($pers);
	
	// weitere Mitglieder eingeben wenn Teamwettbewerb
	if (isset($oWettbewerb->Wettbewerb[0]) && $oWettbewerb->Wettbewerb[0]["teamgroesse"] > 1)
	{
		$iTmpCounter++;
		for ($zeileIND=$iTmpCounter;$zeileIND<=$oWettbewerb->Wettbewerb[0]["teamgroesse"];$zeileIND++)
		{
	        $showHTML.='<tr>';
	             	$showHTML.='<td style="vertical-align: bottom;">';
				$showHTML.=$zeileIND.')&nbsp;<input onchange="if(this.value!=\'\') doIt(this.value,\'neu'.$zeileIND.'\');"  size="17" maxlength="16" id="array_userUID" name="array_userUID[]" value="" />';
			$showHTML.='</td>';
			$showHTML.='<td id="neu'.$zeileIND.'">Neuer Mitspieler</td><td>&nbsp;</td><td>&nbsp;</td>';              
		$showHTML.='</tr>';
		}
	} 
      $showHTML.='</table>';
      $showHTML.='</fieldset>';
      $showHTML.='</td></tr>';              
		  
      $showHTML.='<tr><td><input '.$cTmpWetbewerbUSERok.' type="submit" name="wettbewerbteam" value="Speichern" /></td></tr>';
	  
	    $showHTML.='</table>';              
			$showHTML.=kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/moreright.gif" border="0" />&nbsp;zur&nbsp;Startseite&nbsp;','&nbsp;zur&nbsp;Startseite&nbsp;');

		$showHTML.='</fieldset>';
	    $showHTML.='</div>'; 		// Ende Zusammenfassung der kpl. Eingabe
	    $showHTML.='</form>';  		// Form Ende

	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamWartung_Bildupload 	Datenbearbeitung nach einem Request (Neuanlage,Aenderung)
*
* @param $oWettbewerb 		Objekt zum Wettbewerb, Team, Personen, Match
*
* @return 				Information des Bilderupload
*
*/
function showTeamWartung_Bildupload($oWettbewerb)
{
     // Plausib der UploadDaten      
   	if (isset($_POST['submitTeamLogo']) 
	&& (isset($_FILES['TeamLogoBild']['tmp_name']) && !empty($_FILES['TeamLogoBild']['tmp_name'])) )
	{
		$filename=$_FILES['TeamLogoBild']['tmp_name'];
		if (!is_file($filename)) return '';
		
	       if ($fp=fopen($filename,'r'))    //File oeffnen
    		{
			$content = fread($fp, filesize($filename)); // auslesen der Daten
   	       	fclose($fp); // Close
			$_REQUEST['logo']=kommune_strhex($content); //in HEX-Werte umrechnen
		}
		else // Fehler Information das Bild nicht gefunden wurde
		{
			return '<br />Fehler beim Bild einlesen! '.$_FILES['TeamLogoBild']['name'];
		}				  	  
       	if (isset($fp)) unset($fp);
	} // Ende Plausib der UploadDaten      
	return '';
}
#-------------------------------------------------------------------------------------------	
/*
*
* @showTeamWartung_Datenverarbeiten 	Datenbearbeitung nach einem Request (Neuanlage,Aenderung)
*
* @param $oWettbewerb 			Objekt zum Wettbewerb, Team, Personen, Match
*
* @return 					gespeicherte Teaminformation (Array), oder Fehlermeldung
*
*/
function showTeamWartung_Datenverarbeiten($oWettbewerb)
{
	// Initialisierung
	
	// Plausib
	if (empty($oWettbewerb->team_kurzbz) || $oWettbewerb->team_kurzbz==constEingabeFehlt)
		return $oWettbewerb->Error[]='Bitte Eingabe pr&uuml;fen (Kurzbezeichnung darf nicht <b>Leer</b> oder <b>"'.constEingabeFehlt.'"</b> sein! )';

	// Datenwartung - Neuanlage,Aenderung
	$WettbewerbTeam= new komune_wettbewerbteam($oWettbewerb->sqlCONN,'','','');
   	$WettbewerbTeam->setEncodingSQL($oWettbewerb->clientENCODE);
	$WettbewerbTeam->setSchemaSQL($oWettbewerb->sqlSCHEMA);
	
	$WettbewerbTeam->setTeam_kurzbz($oWettbewerb->team_kurzbz);
	$WettbewerbTeam->setTeam_kurzbz_old($oWettbewerb->team_kurzbz_old);
	$WettbewerbTeam->setUid($oWettbewerb->userUID); // keine Einschraenkung auf angemeldeten Anwender

	// Request, und die Wettbewerbdaten als Array der Classe uebergeben 		
	$arrTmpWettbewerbteam=array_merge($oWettbewerb->Wettbewerb[0],$_REQUEST);
	$WettbewerbTeam->setNewWettbewerbteam($arrTmpWettbewerbteam);
#	$WettbewerbTeam->getNewWettbewerbteam();			

	if (!$arrTmpWettbewerteam=$WettbewerbTeam->saveWettbewerbteam())
		return $oWettbewerb->Error[]=$WettbewerbTeam->getError();
		
	if (!is_array($arrTmpWettbewerteam) && !empty($arrTmpWettbewerteam))
		return $oWettbewerb->Error[]=$arrTmpWettbewerteam;

	//------ Datenreload nach Datenaenderung
	// Daten Wettbewerb ermitteln /include kommune_funktionen.inc.php
	kommune_funk_eigene_wettbewerb($oWettbewerb);		
	kommune_funk_team_wettbewerbe($oWettbewerb);
	// Daten Team
	kommune_funk_teams($oWettbewerb);	 // TeamGesamt
	kommune_funk_anwenderteams($oWettbewerb); // TeamAnwender	
	kommune_funk_teambenutzer($oWettbewerb); // Team, TeamBenutzer

	// Bei Erstanmeldung eine eMail an den Moderator senden			
	if ($oWettbewerb->team_kurzbz && empty($oWettbewerb->team_kurzbz_old) )
	{
		// Moderator - Namen
		$cTmpName=$oWettbewerb->Wettbewerb[0]['uid'];
		$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
		if (isset($pers->langname) && !empty($pers->langname)) 
			$cTmpName=$pers->langname;
			
		// Angemeldeter Anwender - Name
		$cTmpName2=$oWettbewerb->userUID;
		$pers=kommune_funk_benutzerperson($cTmpName2,$oWettbewerb);
		if (isset($pers->langname) && !empty($pers->langname)) 
			$cTmpName2=$pers->langname;
			
		// Info an Moderator vom Anwender
		$betreff='Moderator information : Neuer Eintrag im Wettbewerb '.$oWettbewerb->wettbewerb_kurzbz;
		$text="Sehr geehrte(r) ".$cTmpName."\n\n";
		$text.="Sie erhalten dieses email als Moderator des Wettbewerbs \"".$oWettbewerb->wettbewerb_kurzbz."\"\n\n";
		
		$text.=$cTmpName2."( Kurzzeichen ".$oWettbewerb->team_kurzbz.") ,\n hat sich im Wettbewerb ".$oWettbewerb->wettbewerb_kurzbz." registriert.\n\n\n\n";
		$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->Wettbewerb[0]['uid'],$betreff,$text,$oWettbewerb->userUID,$oWettbewerb);

		// Info vom Moderator an Anwender
		$betreff="Ihr Eintrag im Wettbewerb ".$oWettbewerb->wettbewerb_kurzbz." wurde angenommen.";
		
		$text="Sehr geehrte(r) ".$cTmpName2."\n\n";
		$text.="Sie erhalten dieses email als Anmeldebestätigung.\n\n";
		$text.="Ihr Eintrag im Wettbewerb \"".$oWettbewerb->wettbewerb_kurzbz."\" wurde angenommen.\n\n";
		$text.="Ihr Kurzzeichen im Wettbewerb ist \"".$oWettbewerb->team_kurzbz."\"\n\n\n\n";

		$text.="Ihr Moderator ".$cTmpName.". im Wettbewerb ".$oWettbewerb->wettbewerb_kurzbz."\n\n". 
		$text.="Viel Spaß wünscht das gesamte Team.\n\n". 
		
		$oWettbewerb->Error[]=kommune_funk_sendmail($oWettbewerb->userUID,$betreff,$text,$oWettbewerb->Wettbewerb[0]['uid'],$oWettbewerb);
	}		

	
	// Link zum Wettbewerb , und das Wettbewerbs PopUp erzeugen
	$cTmpInfo='<span style="color: black;" ><br />Daten Team / Spieler  <b>'.$oWettbewerb->team_kurzbz.'</b> wurden gespeichert.';
		$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,$oWettbewerb,array(),$oWettbewerb->wettbewerb_kurzbz);
		$cTmpInfo.='<span onmouseover="show_layer(\'wettbewerb_popup\');" onmouseout="hide_layer(\'wettbewerb_popup\');"><img  style="vertical-align:bottom;" alt="weiter" height="18" src="../../../skin/images/right.gif" border="0" />&nbsp;'.'weiter zum Wettbewerb '.$cTmpHREF.'</span>';
	$cTmpInfo.='</span>';
	$oWettbewerb->Error[]=$cTmpInfo;

	
	if (isset($cTmpInfo)) unset($cTmpInfo);
	return $arrTmpWettbewerteam;
}
?>