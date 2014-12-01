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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

// ---------------- Konstante

// ---------------- Datenbank - Schema der Kommune, Wettbewerbe	


// Max. Wettbewerbe je Zeile am Starttemplate	
   if (!defined('constMaxWettbwerbeZeile')) define('constMaxWettbwerbeZeile',5 );
// Hoehe der Benutzer Foto
   if (!defined('constBenutzerFotoHigh')) define('constBenutzerFotoHigh',60 );
  
// Pflichteingabefelder Defaultwert   
   if (!defined('constEingabeFehlt')) define('constEingabeFehlt','Eingabe !' );

// ------ Anzeige - Display Include HTML Datenerzeugen
   // Auswahl Kommunen Template :: Anzeigenauswahl ::  Definition mit constKommuneParmSetWork
   if (!defined('constKommuneParmSetWork')) define('constKommuneParmSetWork','usersel');
   
   // Auswahl Kommunen Template :: Anzeigenauswahl ::  Definition mit constKommuneParmSetWork
   if (!defined('constKommuneAnzeigeDEFAULT')) define('constKommuneAnzeigeDEFAULT','kommune_template_start');
   // Anzeige Wettbewerb Team(s) in Pyramidenform
   if (!defined('constKommuneAnzeigeWETTBEWERBTEAM')) define('constKommuneAnzeigeWETTBEWERBTEAM','kommune_template_pyramiden');
   // Neuanlage Teamspieler zu einem Wettbewerb
   if (!defined('constKommuneWartungUID')) define('constKommuneWartungUID','kommune_wartung_team' );

   if (!defined('constKommuneWartungWettbewerbtyp')) define('constKommuneWartungWettbewerbtyp','kommune_wartung_wettbewerbtypen' );
   if (!defined('constKommuneWartungWettbewerb')) define('constKommuneWartungWettbewerb','kommune_wartung_wettbewerb' );

   // Neuanlage Teamspieler zu einem Wettbewerb
   if (!defined('constKommuneEinladenTEAM')) define('constKommuneEinladenTEAM','kommune_einladen_team' );
   // Bildausgabe lt.Datenbank
   if (!defined('constKommuneDisplayIMAGE')) define('constKommuneDisplayIMAGE','kommune_hex_img' );
   // Statistik - Bestenliste - Sonstiges
   if (!defined('constKommuneSTATISTIK')) define('constKommuneSTATISTIK','kommune_template_statistik' );
   // XML User Liste
   if (!defined('constKommuneUserXML')) define('constKommuneUserXML','kommune_user_xml' );
         
         
// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');
// ---------------- Datenbank-Verbindung 
#	include_once('../../../include/postgre_sql.class.php');

	include_once('../../../include/komune_wettbewerb.class.php');
	include_once('../../../include/komune_wettbewerbteam.class.php');
	include_once('../../../include/komune_wettbewerbeinladungen.class.php');
	
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	include_once('../../../include/mail.class.php');

// Kommunen Allg.Funktionen		
	include_once('kommune_funktionen.inc.php');
// ---------------- Anzeige/Ausgabe Variable Initialisieren

	// Initialisieren Anzeige-Variable
	$showHTML='';
		 
		 
// Kommunen - Wettbewerb - Datenobjekt -----------------------------------------------------------------------------------------------------------
	// Datenobjekt - Alle Daten je Parameter werden gesammelt fuer die neachste Funktionn
	$oWettbewerb= new stdClass;

#	$oWettbewerb->clientENCODE='UTF8';
	$oWettbewerb->sqlSCHEMA='kommune';

	// Parameter Applikation - Template Auswahl
	$oWettbewerb->workSITE = (isset($_REQUEST[constKommuneParmSetWork]) ? $_REQUEST[constKommuneParmSetWork] : constKommuneAnzeigeDEFAULT);
	$oWettbewerb->workSITE = (!empty($oWettbewerb->workSITE) ? trim($oWettbewerb->workSITE):constKommuneAnzeigeDEFAULT);
	
// AktiverAnwender-----------------------------------------------------------------------------------------------------------
	$user=(isset($_REQUEST['user']) ? $_REQUEST['user'] :get_uid() );
#	$user='pam';
#	$user='oesi';
#	$user='ruhan';
#	$user='kindlm';
	
	$oWettbewerb->user=$user;
	if (!kommune_funk_benutzerperson($oWettbewerb->user,@$oWettbewerb))
		die(kommune_funk_show_error($oWettbewerb));
		
	$benutzerberechtigung = new benutzerberechtigung($user);
	$benutzerberechtigung->getBerechtigungen($user,true);
	// Nur Lektoren oder Mitarbeiter duerfen alle Termine sehen , Studenten nur Freigegebene Kategorien
	if($benutzerberechtigung->fix || $benutzerberechtigung->lektor)
		$oWettbewerb->wartungsberechtigt=true;
	else
		$oWettbewerb->wartungsberechtigt=false;		
		
// Teams -------------------------------------------------------------------------------------------------------------------
	// Parameter Team (zum Wettbewerb)
  	$oWettbewerb->team_kurzbz=(isset($_REQUEST['team_kurzbz']) ? $_REQUEST['team_kurzbz']:'');
	$oWettbewerb->team_kurzbz=(isset($_REQUEST['team_forderer']) ? $_REQUEST['team_forderer']:$oWettbewerb->team_kurzbz);
   	$oWettbewerb->team_kurzbz=trim($oWettbewerb->team_kurzbz);

	// Spieler/Team Wartung 
	$oWettbewerb->team_kurzbz_old=(isset($_REQUEST['team_kurzbz_old']) ? $_REQUEST['team_kurzbz_old']:'');;
   	$oWettbewerb->team_kurzbz_old=trim($oWettbewerb->team_kurzbz_old);

	// Einladung an Spieler/Team
	$oWettbewerb->team_kurzbz_einladung=(isset($_REQUEST['einladen_team_kurzbz']) ? $_REQUEST['einladen_team_kurzbz']:'');
	$oWettbewerb->team_kurzbz_einladung=(isset($_REQUEST['team_gefordert']) ? $_REQUEST['team_gefordert']:$oWettbewerb->team_kurzbz_einladung);
	$oWettbewerb->team_kurzbz_einladung=(isset($_REQUEST['team_kurzbz_einladen']) ? $_REQUEST['team_kurzbz_einladen']:$oWettbewerb->team_kurzbz_einladung);
	$oWettbewerb->team_kurzbz_einladung=trim($oWettbewerb->team_kurzbz_einladung);
	// -------------------------------------------------------------------------------------------------------------------------
	
// Wettbewerb ---------------------------------------------------------------------------------------------------------------
	// Parameter Wettbewerb - Type
   	$oWettbewerb->wbtyp_kurzbz=(isset($_REQUEST['wbtyp_kurzbz']) ? $_REQUEST['wbtyp_kurzbz']:'');
   	$oWettbewerb->wbtyp_kurzbz=trim($oWettbewerb->wbtyp_kurzbz);
	// Parameter Wettbewerb
   	$oWettbewerb->wettbewerb_kurzbz=(isset($_REQUEST['wettbewerb_kurzbz']) ? $_REQUEST['wettbewerb_kurzbz']:'');
	$oWettbewerb->wettbewerb_kurzbz=trim($oWettbewerb->wettbewerb_kurzbz);
// -------------------------------------------------------------------------------------------------------------------------
	$oWettbewerb->WettbewerbTyp=array();		//  Alle Daten des Wettbewerb
	$oWettbewerb->Wettbewerb=array();			//  Alle Daten des Wettbewerb	
	$oWettbewerb->EigeneWettbewerbe=array(); 	//  Eigene Wettbewerbe auf UID Basis
	
//kommune_funk_teams
	$oWettbewerb->TeamGesamt=array();			//  Alle Teams in diesem Wettbewerb (=wettbewerb_kurzbz), oder Alle wenn wettbewerb_kurzbz leer ist 
//kommune_funk_anwenderteams	
	$oWettbewerb->TeamAnwender=array();			//  Alle Wettbewerbe zum angemeldeten Anwende (Alle Teams der uid) 
// ----------- Team Key => team_kurzbz
//kommune_funk_teambenutzer 1x Datensatz je team_kurzbz
	$oWettbewerb->Team=array();				//  Alle Teams (ein DatenArray je Team Key => team_kurzbz) Achtung! nur ein Benutzer im Array (verwende TeamBenutzer)  !
//kommune_funk_teambenutzer	Alle Datensaetze je team_kurzbz
	$oWettbewerb->TeamBenutzer=array();		//  Alle Teammitglieder (ein Datensatz je Team Key => team_kurzbz)
	
// Match -------------------------------------------------------------------------------------------------------------------
	$oWettbewerb->match_id=(isset($_REQUEST['match_id']) ? $_REQUEST['match_id']:'');
	$oWettbewerb->match_id=trim($oWettbewerb->match_id);

	$oWettbewerb->Einladung=array();		// 	Alle Daten zur Einladung
	$oWettbewerb->EinladungVonTeam=array();	// Alle Teaminformation des Einladenten Teams
	$oWettbewerb->EinladungAnTeam=array();	// Alle Teaminformation des Eingeladenen Teams

	$oWettbewerb->Forderungen=array();		// 	Forderungen an Andere Teams
	$oWettbewerb->Spiele=array();			// 	Forderungen

	$oWettbewerb->Error=array();			// 	Fehlermeldungen
	// Benutzer Personen Gen. -------------------------------------------------------------------------------------------------------------------
	$oWettbewerb->PersonenBenutzer=array();	//  Personendaten je Spieler ( Key => userid )
	
	// ---------------- Kommunen Standart Include Dateien einbinden
	//  Anzeige Templates mittels Include Laden 
    	if (trim($oWettbewerb->workSITE)!=constKommuneAnzeigeDEFAULT
		&& trim($oWettbewerb->workSITE)!=constKommuneWartungWettbewerb	
    	&& trim($oWettbewerb->workSITE)!=constKommuneWartungWettbewerbtyp	) 
	{
	       	$includeFILE=strtolower($oWettbewerb->workSITE.".inc.php"); 
      	 	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		 	   include_once($includeFILE);
	}
		
	// Fuer die Bildfunktion werden keine Datenbenoetigt, und nach Verarbeitung beenden
    if (trim($oWettbewerb->workSITE)==constKommuneDisplayIMAGE) 
	{	
		createIMGfromHEX($oWettbewerb);
		exit;
	}		
	
	// Fuer die Bildfunktion werden keine Datenbenoetigt, und nach Verarbeitung beenden
    	if (trim($oWettbewerb->workSITE)==constKommuneUserXML) 
	{	
		if (empty($user))
			exit('<noInfo>Keine Daten </noInfo>');
		exit( (isset($pers->nachname)?$pers->nachname:"$user falsch!"));
	}		
// -------------------------------------------------------------------------------------------------------------------------
// HTML Ausgabe Datenstrom Teil I Header
	$showHTML='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="DE" lang="DE">
	<head>
		<title>Kommune '.$oWettbewerb->workSITE.'</title>
		<meta name="description" content="Kommune - Wettbewerbe '.$oWettbewerb->workSITE.'" />
		<meta name="keywords" content="Kommune,Wettbewerbe,'.$oWettbewerb->workSITE.'" />
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
	.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}
		
		
	-->
	</style>
	<script language="JavaScript1.2" type="text/javascript">
	<!--
	function show_layer(x,obj)
	{
 		if (document.getElementById && document.getElementById(x)) 
		{  
			document.getElementById(x).style.visibility = \'visible\';
			document.getElementById(x).style.display = \'inline\';
			
		} else if (document.all && document.all[x]) {      
		   	document.all[x].visibility = \'visible\';
			document.all[x].style.display=\'inline\';
	      	} else if (document.layers && document.layers[x]) {                          
	           	 document.layers[x].visibility = \'show\';
			 document.layers[x].style.display=\'inline\';
	          }

 		if (document.getElementById && document.getElementById(x))  
		{  
			var DivHeight =document.getElementById(x).offsetHeight;
			var DivWidth =document.getElementById(x).offsetWidth;
			
			var hoch=0;
			var weite=0;

			if (!window.event) {
				var top=document.getElementById(x).offsetTop;
				var left=document.getElementById(x).offsetLeft;
				
				var position=0;
				// wenn Position Rechts und Hoehe der ID groesser der Screen Hoehe , dann Korr.
				if ( (top + DivHeight) >Hoehe)
				{
					position=Hoehe-DivHeight;
					document.getElementById(x).style.top=position + "px";
				}	
				
				if ( (left + DivWidth) >Weite)
				{
					position=Weite-DivWidth;
//				alert (Weite+" "+  DivWidth + " "+ position + " offset Left "+document.getElementById(x).offsetLeft);
					document.getElementById(x).style.left=position + "px";
				}	
			} else {	

				hoch=(Hoehe - ( DivHeight + window.event.clientY ))-2 ;
				weite=(Weite - ( DivWidth + window.event.clientX ))-2 ;
				if (hoch>0) hoch=0;
				if (weite>0) weite=0;
				with (window.event && document.getElementById(x).style) {
					top=window.event.clientY + document.body.scrollTop+( 1 + hoch );
					left=window.event.clientX + document.body.scrollLeft+( 1 + weite );
				}	
			}
		}
	}
function clear_layer(wohin,obj)	
	{
	if (obj) {
		set_layer(\'\',wohin,obj);
	} else if (wohin)
		set_layer(\'\',wohin);
	}

function hide_layer(x,obj)
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
function set_layer(was,wohin)	
{
	if (document.getElementById(wohin).innerHTML) 
	{
	document.getElementById(wohin).innerHTML=was;
	}
	elseif (document.getElementById(wohin).value) 
	{
	document.getElementById(wohin).value=was;
	}
}
	
function Fensterweite () {
   if (window.innerWidth) {
    	return window.innerWidth;	
   } else if (document.documentElement && document.documentElement.clientHeight) {
 // Explorer 6 Strict Mode
 	return document.documentElement.clientWidth;
  } else if (document.body && document.body.offsetWidth) {
    	return document.body.offsetWidth;
  } else if (document.body && document.body.clientWidth) {
    	return document.body.clientWidth;  
  } else {
    	return 0;
  }
}

function Fensterhoehe () {
 if (window.innerHeight) {
   	return window.innerHeight;
 } else if (document.documentElement && document.documentElement.clientHeight) {
 // Explorer 6 Strict Mode
 	return document.documentElement.clientHeight;
  } else if (document.body && document.body.offsetHeight) {
	return document.body.offsetHeight;
  } else if (document.body && document.body.clientHeight) {
	return document.body.clientHeight;
  } else {
    	return 0;
  }

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



function doIt(user,nameID)
{
//	alert(document.getElementById(nameID).innerHTML);
//erstellen des requests
    var req = null;
	    try{
		     req = new XMLHttpRequest();
//	       		req.http_request.overrideMimeType("text/html;"); // zu dieser Zeile siehe weiter unten
//      	       	req.http_request.overrideMimeType("text/xml;"); // zu dieser Zeile siehe weiter unten

	        }
	    catch (ms)// hier beginnt der IE Teil		
	    {
       		 try{
			    req = new ActiveXObject("Msxml2.XMLHTTP");
       		 } 
		        catch (nonms)
			 {
			        try{
              			req = new ActiveXObject("Microsoft.XMLHTTP");
				     } 
			        catch (failed)
				 {
				  	document.getElementById(nameID).innerHTML="Browser ohne Ajax Funktion";
              			req = null;
		       	 }
			 }  
		}
       
	if (req == null)
           document.getElementById(nameID).innerHTML="Error creating request object!";
                  
       //anfrage erstellen (GET, url ist localhost, request ist asynchron      

	var callURL=\''.(isset($_SERVER["HTTP_REFERER"])?str_replace(strstr($_SERVER["HTTP_REFERER"],'?'),'',$_SERVER["HTTP_REFERER"]):'').'\';       
	callURL=callURL+\'?userSel='.constKommuneUserXML.'&client_encode=UTF8&user=\'+user;
	req.open("GET", callURL , true);

       //Beim abschliessen des request wird diese Funktion ausgefuhrt
                req.onreadystatechange = function(){            
                    switch(req.readyState) {
                            case 4:
                    	       	 if(req.status!=200) {
                     	           document.getElementById(nameID).innerHTML=callURL+" Fehler: "+req.status; 
	                            }else{    
									if (document.getElementById(nameID).value)
									  	document.getElementById(nameID).value=req.responseText;
									else
									  	document.getElementById(nameID).innerHTML=req.responseText;
                     	       }
                            	break;
                           default:
							if (document.getElementById(nameID).value)
							  	document.getElementById(nameID).value="bitte warten! Suche nach "+user;    
							else
							  	document.getElementById(nameID).innerHTML="bitte warten! Suche nach "+user;    
                            break;     
                        }
                    };
                req.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                req.send(null);
            }



function neuAufbau () {
  if (Weite != Fensterweite() || Hoehe != Fensterhoehe())
	    location.href = location.href;
}

/* uberwachung von Netscape initialisieren */
if (!window.Weite && window.innerWidth) {
  	window.onresize = neuAufbau;
  
  Weite = Fensterweite();
  Hoehe = Fensterhoehe();
}
-->
</script>				
</head>
<body id="hauptbody">
<script type="text/javascript">
<!--
/* uberwachung von Internet Explorer initialisieren */
if (!window.Weite && document.body && document.body.offsetWidth) 
	{
	  window.onresize = neuAufbau;
	  Weite = Fensterweite();
	  Hoehe = Fensterhoehe();
	}
//  alert(Weite+ \' \'+Hoehe);
-->
</script>
';
		
	//-------------- Datenlesen	
	// Daten Wettbewerb ermitteln /include kommune_funktionen.inc.php
	kommune_funk_wettbewerb($oWettbewerb);	
	
	kommune_funk_eigene_wettbewerb($oWettbewerb);		
	kommune_funk_team_wettbewerbe($oWettbewerb);

	// Daten Teams ermitteln /include kommune_funktionen.inc.php
	kommune_funk_teams($oWettbewerb);	// TeamGesamt
	kommune_funk_anwenderteams($oWettbewerb); // TeamAnwender	
	kommune_funk_teambenutzer($oWettbewerb); // Team, TeamBenutzer	
	

		
   	if (trim($oWettbewerb->workSITE)==constKommuneWartungWettbewerb	
   	|| trim($oWettbewerb->workSITE)==constKommuneWartungWettbewerbtyp	) 
	{
		
		echo '[&nbsp;'.kommune_funk_create_href(constKommuneAnzeigeDEFAULT,array(),array(),'<input  type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4pt;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="callStartseite" />Startseite','Startseite&nbsp;').'&nbsp;]';

	      	$includeFILE=strtolower($oWettbewerb->workSITE.".inc.php"); 
      	 	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		 	   include_once($includeFILE);
	}	
	else
		// Daten Anzeige und Verarbeitung
   		$showHTML.=showMenueFunktion($oWettbewerb);
		
	// Fehler - Error Ausgabe
	$showHTML.='<div id="errorKommune">'.kommune_funk_show_error($oWettbewerb).'</div>';

	$showHTML.='</body></html>';
	exit($showHTML);

?>
