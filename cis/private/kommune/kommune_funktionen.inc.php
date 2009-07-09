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
* @showMeineWettbewerbSpiele Aufbau einer bisher gespielten Wettbewerbe
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML Liste der Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_wettbewerb($oWettbewerb)
{
	// WettbewerbTypen
	$Wettbewerb= new komune_wettbewerb();
	
	$Wettbewerb->InitWettbewerb();
	$Wettbewerb->wbtyp_kurzbz=$oWettbewerb->wbtyp_kurzbz;
	$oWettbewerb->WettbewerbTyp=array();
	if ($Wettbewerb->loadWettbewerbTyp($oWettbewerb->wbtyp_kurzbz))
		$oWettbewerb->WettbewerbTyp=$Wettbewerb->result;
	else
		$oWettbewerb->errormsg[]=$Wettbewerb->errormsg;
	if (!isset($oWettbewerb->WettbewerbTyp[0]))
		return false;

	// WettbewerbTypen und Wettbewerbe

	$Wettbewerb->InitWettbewerb();
	$Wettbewerb->wbtyp_kurzbz=$oWettbewerb->wbtyp_kurzbz;
	$Wettbewerb->wettbewerb_kurzbz=$oWettbewerb->wettbewerb_kurzbz;

	$oWettbewerb->Wettbewerb=array();
	if ($Wettbewerb->loadWettbewerb($oWettbewerb->wbtyp_kurzbz,$oWettbewerb->wettbewerb_kurzbz))
		$oWettbewerb->Wettbewerb=$Wettbewerb->result;
	else
		$oWettbewerb->errormsg[]=$Wettbewerb->errormsg;
	if (!isset($oWettbewerb->Wettbewerb[0]))
		return false;

	// Wettbewerbstyp wenn nicht uebergeben wurde ermitteln zu einem Wettbewerb 	
	if (empty($oWettbewerb->wbtyp_kurzbz) && !empty($oWettbewerb->wettbewerb_kurzbz) )
	   	$oWettbewerb->wbtyp_kurzbz=$oWettbewerb->Wettbewerb[0]->wbtyp_kurzbz;
		
	//  Moderator,Bild-Icon ermitteln und Leerzeichen aus den KeyWords entfernen
	reset($oWettbewerb->Wettbewerb);
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
		// Check Space
		$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz);
		$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz);
		$oWettbewerb->Wettbewerb[$iTmpZehler]->uid=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->uid);
		$oWettbewerb->Wettbewerb[$iTmpZehler]->icon_image='';
	   	$oWettbewerb->Wettbewerb[$iTmpZehler]->bereits_eingetragen="";
		$oWettbewerb->Wettbewerb[$iTmpZehler]->daten_eingetragen="";
		// Create IMG  
		if (!empty($oWettbewerb->Wettbewerb[$iTmpZehler]->icon))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?userSel=kommune_hex_img&amp;timecheck'.time().'&amp;wettbewerb_kurzbz='.$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz.'&amp;wbtyp_kurzbz='.$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz.(strlen($oWettbewerb->Wettbewerb[$iTmpZehler]->icon)<1000?'&amp;heximg='.$oWettbewerb->Wettbewerb[$iTmpZehler]->icon:'');
			$oWettbewerb->Wettbewerb[$iTmpZehler]->icon_image='<img height="40" border="0" alt="'.$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz.'" src="'.$paramURL.'" />';
		}
		
		// Moderator lesen zu jedem Wettbewerb : Moderator - Person-Benutzer
		$cShowImage='';
		if (!empty($oWettbewerb->Wettbewerb[$iTmpZehler]->uid))
		{
			$pers=kommune_funk_benutzerperson($oWettbewerb->Wettbewerb[$iTmpZehler]->uid,$oWettbewerb);
			if (isset($pers->foto_image) && !empty($pers->foto_image))
				$cShowImage=$pers->foto_image;
			$oWettbewerb->Wettbewerb[$iTmpZehler]->pers=$pers;
		}		
		$oWettbewerb->Wettbewerb[$iTmpZehler]->foto_image=$cShowImage;
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
	$Wettbewerb=new komune_wettbewerbteam($oWettbewerb->team_kurzbz,$oWettbewerb->wettbewerb_kurzbz,$oWettbewerb->user);
	$oWettbewerb->EigeneWettbewerbe=array();
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->EigeneWettbewerbe=$Wettbewerb->result;
	else	
		return $oWettbewerb->errormsg[]=$Wettbewerb->errormsg;


	@reset($oWettbewerb->EigeneWettbewerbe);
  	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->EigeneWettbewerbe);$iTmpZehler++)
	{
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->team_kurzbz=trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->team_kurzbz);
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->wettbewerb_kurzbz=trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->wettbewerb_kurzbz);
		
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->logo_image='';
		if (!empty($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->logo))
		{
			$paramURL=$_SERVER['PHP_SELF'].'?'.'userSel'.'='.'kommune_hex_img'.'&amp;timecheck'.time().'&amp;team_kurzbz='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->team_kurzbz.'&amp;wettbewerb_kurzbz='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->wettbewerb_kurzbz.(strlen($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->logo)<1000?'&amp;heximg='.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->logo:'');
	   		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->logo_image='<img height="80" border="0" alt="'.$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->team_kurzbz.'" src="'.$paramURL.'" />';
		}	
		// Anwender lesen zu jedem Wettbewerb : UID - Person-Benutzer
		$cShowImage='';
		if (!empty($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->uid))
			$pers=kommune_funk_benutzerperson($oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->uid,$oWettbewerb);
		else
			$pers=array();	
		if (isset($pers->foto_image) && !empty($pers->foto_image))
			$cShowImage=$pers->foto_image;
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->foto_image=$cShowImage;
		$oWettbewerb->EigeneWettbewerbe[$iTmpZehler]->pers=$pers;
			
	}



	// Suchen Wettbewerb wo der Angemeldeten Anwender (uid) angemeldet ist
	@reset($oWettbewerb->Wettbewerb);
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
	   	$oWettbewerb->Wettbewerb[$iTmpZehler]->bereits_eingetragen="";
		$oWettbewerb->Wettbewerb[$iTmpZehler]->daten_eingetragen="";
	   	if (is_array($oWettbewerb->EigeneWettbewerbe) && count($oWettbewerb->EigeneWettbewerbe)>0)
       	{
	           	reset($oWettbewerb->EigeneWettbewerbe);
       	    	for ($iTmpZehlerEX=0;$iTmpZehlerEX<count($oWettbewerb->EigeneWettbewerbe);$iTmpZehlerEX++)
            		{
				if (trim($oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz)==trim($oWettbewerb->EigeneWettbewerbe[$iTmpZehlerEX]->wettbewerb_kurzbz) )
 	    	    	 	{
            				$oWettbewerb->Wettbewerb[$iTmpZehler]->bereits_eingetragen="*";
		             		$oWettbewerb->Wettbewerb[$iTmpZehler]->daten_eingetragen=$oWettbewerb->EigeneWettbewerbe[$iTmpZehlerEX];
					$iTmpZehlerEX=count($oWettbewerb->EigeneWettbewerbe) + 1; // Datensatz gefunden. Suche kann beendet werden
          		   	}
	             }       
		} 
	}// Ende Wettbewerb Suchen Datensatz des Angemeldeten Anwender (uid) fuer den Wettbewerb
	return true;
}	 



// ************************************************************************************

#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_benutzerperson ermittelt zu einer UID die Person, und speichert diese im Objekt
*
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
* @param $user UserUID Anwenderkurzzeichen 
*
* @return HTML Liste der Ergebnisse der Wettbewerbe
*
*/
function kommune_funk_benutzerperson($user,$oWettbewerb)
{
	$user=trim($user);
	if (empty($user))
	{
		$oWettbewerb->Error[]="keine Benutzer UID $user der Funktion 'benutzerperson' &uuml;bergeben ";
		return false;
	}

	// Gibt es bereits den User in der Objektliste - 	
	if (!isset($oWettbewerb->PersonenBenutzer[$user])) // Wurde bereits gefunden
	{
		if (!$pers = new benutzer($user)) // Lesen PersonenBenutzer
		{
			$oWettbewerb->errormsg[]=$pers->errormsg;
			return false;
		}
		$oWettbewerb->PersonenBenutzer[$user]=$pers;
	}

	if (!isset($oWettbewerb->PersonenBenutzer[$user]->langname))
		$oWettbewerb->PersonenBenutzer[$user]->langname=kommune_funk_pers_langname($oWettbewerb->PersonenBenutzer[$user]);	

	if (!isset($oWettbewerb->PersonenBenutzer[$user]->foto_image))
		$oWettbewerb->PersonenBenutzer[$user]->foto_image=kommune_funk_pers_image($oWettbewerb->PersonenBenutzer[$user]);	

	if (!isset($oWettbewerb->PersonenBenutzer[$user]->emailaccount))
		$oWettbewerb->PersonenBenutzer[$user]->emailaccount=kommune_funk_create_emailaccount($oWettbewerb->PersonenBenutzer[$user]->uid);	

	return $oWettbewerb->PersonenBenutzer[$user];
}	
#-------------------------------------------------------------------------------------------	
/*
*
* @kommune_funk_pers_langname Gibt zum User den Langtext retour
*
* @param pers array der Personen - Benutzer Daten 
*
* @return RETURN wird der Langname des Users geliefert. Leer wenn nicht moeglich.
*
*/
function kommune_funk_pers_langname($pers="")
{           
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
* @kommune_funk_pers_image Gibt zum User den Image Href reour
*
* @param pers array der Personen - Benutzer Daten 
*
* @return RETURN wir der HREF des Bildes geliefert. Leer wenn nicht moeglich.
*
*/
function kommune_funk_pers_image($pers="")
{           
	$cTmpImgHref='';

	if (isset($pers->foto) && !empty($pers->foto))
	{
		$paramURL=$_SERVER['PHP_SELF'].'?userSel=kommune_hex_img&amp;timecheck'.time().'&amp;person_id='.$pers->person_id.(strlen($pers->foto)<1000?'&amp;heximg='.$pers->foto:'');
		$cTmpImgHref='<img height="40"  border="0" alt="'.$pers->uid.'" title="'.(isset($pers->langname)?$pers->langname:$pers->nachname).' '.$pers->person_id.'" src="'.$paramURL.'" />';
	}
	return $cTmpImgHref;
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
function kommune_funk_create_emailaccount($user)
{
	if (empty($user))
		$user=get_uid();
	$user=trim($user);	
	if (!defined('DOMAIN')) die('Die Konstante DOMAIN wurde nicht gefunden! Bitte config pruefen.' );
	if (!stristr($user,'@')) // Domainkonstante nur dazufuegen wenn noch keine Domain im Namen ist
		$user=$user.(stristr(DOMAIN,'@')?DOMAIN:'@'.DOMAIN); // Pruefen ob in der Konstant der Klammeraffe ist
	$user=mb_ereg_replace(' ','',$user);
   	return mb_strtolower($user);
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @kommune_funk_popup_benutzer Aufbau einer bisher gespielten Wettbewerbe
*
* @param $user UserUID Anwenderkurzzeichen 
* @param $oWettbewerb Objekt mit allen Daten zur Selektion wie Wettbewerbe,Personen,Teams,Matches
*
* @return HTML String Benutzeruebersicht
*
*/
function kommune_funk_popup_benutzer($user,$oWettbewerb)
{
	$showHTML=''; // Init

	// Plausib
	if (is_object($user) && isset($user->uid))
		$user=$user->uid;
	else if (is_array($user) && isset($user['uid']))
		$user=$user['uid'];
	else if (is_array($user) && isset($user[0]['uid']))
		$user=$user[0]['uid'];
	else if (empty($user)) 
		return $showHTML;
		 
	$cTmpName=$user;
	$pers=kommune_funk_benutzerperson($cTmpName,$oWettbewerb);
	if (isset($pers->langname)) 
		$cTmpName=$pers->langname;		 
		 
	$showHTML.='
     	<fieldset style="border:1px outset Black;background-color:#DDDDDD;">
		<legend style="border:2px  outset Black;background-color:#FFFFF2;">'.(isset($pers->langname)?$pers->langname:$user).'</legend>
		<table cellpadding="2" cellspacing="2" border="0" summary="'.(isset($pers->langname)?$pers->langname:$user).'">    
		        <tr>
				<td rowspan="3">'.(isset($pers->foto_image)?$pers->foto_image:'').'</td>
				<td colspan="2" style="vertical-align: top;"><a href="mailto:'.kommune_funk_create_emailaccount($user).'">'.kommune_funk_create_emailaccount($user).'</a></td>
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
	$cTmpUrl=$_SERVER['PHP_SELF'].'?'.'userSel'.'='.(!empty($workurl)?$workurl:'');
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
    $hex="";
    for ($i=0;$i<strlen($string);$i++)
        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
    return $hex;
}  
function kommune_hexstr($hex)
{
    $string="";
    for ($i=0;$i<strlen($hex)-1;$i+=2)
        $string.=chr(hexdec($hex[$i].$hex[$i+1]));
    return $string;
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
              $userSETWORK = (isset($_REQUEST['userSel']) ? $_REQUEST['userSel'] : '');
          if(!isset($userSETWORK) || $userSETWORK=='') // Default Verarbeitung setzten
	       	  $userSETWORK='';

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
                     $sqlSELECT.="BEGIN;UPDATE tbl_team set logo='$content' WHERE UPPER(team_kurzbz)=UPPER('$selectTEAM');COMMIT;";
                     break;
       //  User-Teams zu einem Wettbewerb anzeigen ( Pyramide = Rang )
               case constKommuneAnzeigeWETTBEWERBTEAM:
                     return ''; 
                     break;
               case '':
                   	$selectWETTBEWERB = (isset($_REQUEST[constKommuneParmWettbewerbArt]) ? $_REQUEST[constKommuneParmWettbewerbArt] : '');
                   	$sqlSELECT.="BEGIN;UPDATE tbl_wettbewerb set icon='$content' WHERE UPPER(wettbewerb_kurzbz)=UPPER('$selectWETTBEWERB');COMMIT;";
                   	break;
              default: // Keine Verarbeitung
                   return ''; 
          	       break;
       }
       $tmp_result=querySQL($sqlSELECT);
       if (empty($tmp_result)) // Wenn kein Datenbankfehler aufgetreten ist OK-Information senden
    		 $tmp_result="<p>".'Bild'." Upload : ".$_FILES['bild']['name']." (".$_FILES['bild']['type'] .")</p>";
       return $tmp_result; // DB Fehler
  } // Ende Bild Upload laden

  
/* 
*-------------------------------------------------------------------------------------------	
* HTML Header 
*      erzeugt den HTML Header fuer die Seite
*
*--------------------------------------------------------------------------------------------------
*/
function kommune_html_header($oWettbewerb)
{
// -------------------------------------------------------------------------------------------------------------------------
// HTML Ausgabe Datenstrom Teil I Header
	$showHTML='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="DE" lang="DE">
	<head>
		<title>Kommune '.$oWettbewerb->workSITE.'</title>
		<meta name="description" content="Kommune - Wettbewerbe '.$oWettbewerb->workSITE.'" />
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
		<meta http-equiv="expires" content="-1" />
		<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
       	<meta http-equiv="pragma" content="no-cache" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css" />

	<style type="text/css">
	<!-- 
	form {display:inline;}
				
	/* Kategorien Abrundungen im Detail */
	b.rtop, b.rbottom{display:block;background: transparent;}
	b.rtop b, b.rbottom b{display:block;height: 1px; overflow: hidden; background: #E5E5E5;}
	b.r1{margin: 0 5px}
	b.r2{margin: 0 3px}
	b.r3{margin: 0 2px}
	b.rtop b.r4, b.rbottom b.r4{margin: 0 1px;height: 2px}
	
	.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}
		
	.pflichtfeld {background-color:#FFFFE0;border : 1px solid Black;}

	-->
	</style>
	
	<script language="JavaScript1.2" type="text/javascript">
	<!--
	function show_layer(x)
	{
 		if (document.getElementById && document.getElementById(x)) 
		{  
			document.getElementById(x).style.visibility = \'visible\';
			document.getElementById(x).style.display =  \'inline\';
		} else if (document.all && document.all[x]) {      
		   	document.all[x].visibility = \'visible\';
			document.all[x].style.display=\'inline\';
	      	} else if (document.layers && document.layers[x]) {                          
	           	 document.layers[x].visibility = \'show\';
			 document.layers[x].style.display=\'inline\';
	          }

	}

	function hide_layer(x)
	{
		if (document.getElementById && document.getElementById(x)) 
		{                       
		   	document.getElementById(x).style.visibility = \'hidden\';
			document.getElementById(x).style.display = \'none\';
       	} else if (document.all && document.all[x]) {                                
			document.all[x].visibility = \'hidden\';
			document.all[x].style.display=\'none\';
       	} else if (document.layers && document.layers[x]) {                          
	           	 document.layers[x].visibility = \'hide\';
			 document.layers[x].style.display=\'none\';
	          }
	}			
	
	var InfoWin;  
	function callWindows(url,nameID)
	{
		 // width=(Pixel) - erzwungene Fensterbreite 
		 // height=(Pixel) - erzwungene Fensterh&ouml;he 
		 // resizable=yes/no - Gr&ouml;&szlig;e fest oder ver&auml;nderbar 
		 // scrollbars=yes/no - fenstereigene Scrollbalken 
		 // toolbar=yes/no - fenstereigene Buttonleiste 
		 // status=yes/no - fenstereigene Statuszeile 
		 // directories=yes/no - fenstereigene Directory-Buttons (Netscape) 
		 // menubar=yes/no - fenstereigene Men&uuml;leiste 
		 // location=yes/no - fenstereigenes Eingabe-/Auswahlfeld f&uuml;r URLs 
		 
		if (InfoWin) {
			InfoWin.close();
	 	}
	       InfoWin=window.open(url,nameID,"copyhistory=no,directories=no,location=no,dependent=no,toolbar=yes,menubar=no,status=no,resizable=yes,scrollbars=yes, width=550,height=600,left=60, top=15");  
		InfoWin.focus();
		InfoWin.setTimeout("window.close()",800000);
	}
	function checkTeamAnzahl(obj,nameID,anz)
	{
		var ok=true;
		var ii=0;
		
		for (var i = 0; i < anz; i++) {
			if (obj[nameID][i].value=="") {
				obj[nameID][i].focus();
				ii = i + 1;
				alert("Es wurden erst "+ i +" Teamspieler eingegeben. Es muessen "+ anz +" eingegeben werden.");		
				return false;
			}
		}	
		return ok;
	}
-->
</script>				
</head>

<body id="hauptbody">
';
return $showHTML;
}

/* 
*-------------------------------------------------------------------------------------------	
* Hilfsfunktionen 
*      Diverse Debug, Test, Sonstiges
*
*--------------------------------------------------------------------------------------------------
*/
#	$const=@get_defined_constants();
#	@reset($const);	
#	print_r($const);   

# Testfunktion zur Anzeige einer übergebenen Variable oder Array, Default ist GLOBALS
function kommune_Test($arr=leer_zeichen,$lfd=0,$displayShow=true,$onlyRoot=false )
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
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key</b>".kommune_Test($tmp_value,$lfdnr);
       	} else if ( (is_array($tmp_value) || is_object($tmp_value)) ) 
       	{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key -- 0 Records</b>";
		} else if (!empty($tmp_value)) 
		{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :== ".$tmp_value;
		} else {
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :-- (is Empty)";
		}  
    }
     if (!empty($lfd)) { return $tmpArrayString; }
     if (!$displayShow) { return $tmpArrayString; }
       
    $tmpArrayString.="<br />";
    $tmpArrayString="<br /><hr /><br />******* START *******<br />".$tmpArrayString."<br />******* ENDE *******<br /><hr /><br />";
	if (defined('Sprache_ISO')) 
	{
	    $tmpArrayString.="<br />Language:: ".Sprache_ISO;
	}    
    $tmpArrayString.="<br />Server:: ".$_SERVER['PHP_SELF']."<br />";
	return "$tmpArrayString";

}



?>