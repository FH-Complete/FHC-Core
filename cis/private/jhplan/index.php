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
 
 
// Anzeige/Ausgabe  -----------------------------------------------------------------------------------------------------------
	$showHTML='';

// Datenobjekt -----------------------------------------------------------------------------------------------------------
	$oJahresplan= new stdClass;
	
	// Classen
	$oJahresplan->classJahresplan=null;
	
	// Datenbankverbindungen
	$oJahresplan->oConn=null;
		
//	Tabelle der Aufgetretenen Fehler	
	$oJahresplan->Error=array();				// 	Fehlermeldungen
	
//  Post/Get ParameterFelder und die Daten (Tabellen) 		
	
	// Veranstaltungskategorie
	$oJahresplan->veranstaltungskategorie_kurzbz='';		//	Alle Veranstaltungskategorien
	$oJahresplan->veranstaltungskategorie=array();		//	Alle Veranstaltungskategorien
	$oJahresplan->veranstaltungskategorie_key=array();	//	Key veranstaltungskategorie_kurzbz
	// Veranstaltung	
	$oJahresplan->veranstaltung_id='';				//  Alle Veranstaltungskategorien
	$oJahresplan->veranstaltung=array();			//  Alle Veranstaltungen mit Veranstaltungskategorie
	$oJahresplan->veranstaltung_kalender=array();	//  Alle Veranstaltungen mit Veranstaltungskategorie
	// Reservierung
   	$oJahresplan->reservierung_id='';
	$oJahresplan->reservierung=array(); 			//  Alle Reservierungen mit Veranstaltungen und Veranstaltungskategorie
	
	// Post/Get Parameter - Bedingungen 
	$oJahresplan->Jahr="";						// 	Eingabe Jahr
   	$oJahresplan->Monat="";						//	Eingabe Monat
   	$oJahresplan->Woche="";						//	Eingabe Woche	
	$oJahresplan->Suchtext="";					//	Suchen nach Textinhalte in den Veranstaltungen

	// Berechtigungen Schalter 
	$oJahresplan->Wartungsberechtigt=false;			// Wartungsberechtigt
	$oJahresplan->is_lector=false;				// Kategorieberechtigt
 
// ---------------- Konstante

// Max. Kalendertage je Zeile am Starttemplate	
   if (!defined('constPopUpName')) define('constPopUpName','PopUp'.Time() );
   if (!defined('constPopUpReserv')) define('constPopUpReserv','PopRes'.Time() );
      
   if (!defined('constDatumZeitLang')) define('constDatumZeitLang','%A, %d.%B %G %R' );	  
   if (!defined('constDatumZeitMittel')) define('constDatumZeitMittel','%a, %d.%b %G %R' );	  

   if (!defined('constDatumLang')) define('constDatumLang','%a, %d %B %G' );	  
   if (!defined('constDatumMittel')) define('constDatumMittel','%a, %d.%b %G' );	  

   if (!defined('constDatumKalenderHead')) define('constDatumKalenderHead','%B  %Y' );	   
#   if (!defined('constDatumKalender')) define('constDatumKalender','%a, %d. %b ' );	  
   if (!defined('constDatumKalender')) define('constDatumKalender','%d ' );	  

   if (!defined('constZeitKalender')) define('constZeitKalender','%a,%d.%b %H:%M' );	  

   if (!defined('constZeitKalenderListe')) define('constZeitKalenderListe','%a, %d. %b %G %R' );	  

if (!defined('constZeitDatumJJJJMMTT')) define('constZeitDatumJJJJMMTT','%Y%m%d' );	
	  
// Pflichteingabefelder Defaultwert   
   if (!defined('constEingabeFehlt')) define('constEingabeFehlt','Eingabe !' );
   if (!defined('constLeer')) define('constLeer','' );
   
   
// HREF Parameter fuer die Include Auswahl
   if (!defined('constJahresplanParmSetWork')) define('constJahresplanParmSetWork','jvwork' );
   if (!defined('constJahresplanParmSetFunk')) define('constJahresplanParmSetFunk','jvfunk' );
   if (!defined('constJahresplanAnzeigeDEFAULT')) define('constJahresplanAnzeigeDEFAULT','jahresplan_veranstaltungskategorie_default' );
   if (!defined('constJahresplanAJAX')) define('constJahresplanAJAX','ajax' );
   
// Image   
   if (!defined('constJahresplanIMAGE')) define('constJahresplanIMAGE','jahresplan_hex_img' );
   if (!defined('constJahresplanLoadIMAGE')) define('constJahresplanLoadIMAGE','jahresplan_load_hex_img' );
   
// Kategorie
   if (!defined('constJahresplanWartungKATEGORIE')) define('constJahresplanWartungKATEGORIE','jahresplan_veranstaltungskategorie' );
   if (!defined('constJahresplanDeleteKATEGORIE')) define('constJahresplanDeleteKATEGORIE','jahresplan_veranstaltungskategorie_remove' );
			
// Veranstaltungen				 
   if (!defined('constJahresplanWartungVERANSTALTUNG')) define('constJahresplanWartungVERANSTALTUNG','jahresplan_veranstaltung_upd' );

   if (!defined('constJahresplanDeleteVERANSTALTUNG')) define('constJahresplanDeleteVERANSTALTUNG','jahresplan_veranstaltung_liste_del' );
   if (!defined('constJahresplanDetailVERANSTALTUNG')) define('constJahresplanDetailVERANSTALTUNG','jahresplan_veranstaltung_detail' );
   if (!defined('constJahresplanLesenVERANSTALTUNG')) define('constJahresplanLesenVERANSTALTUNG','jahresplan_veranstaltung_listenanzeige' );
// Reservierungen   
   if (!defined('constJahresplanLesenRESERVIERUNG')) define('constJahresplanLesenRESERVIERUNG','jahresplan_reservierung_listenanzeige' );
   if (!defined('constJahresplanWartungRESERVIERUNG')) define('constJahresplanWartungRESERVIERUNG','jahresplan_reservierung_upd' );
   
		
// ---------------- CIS Include Dateien einbinden
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');

// ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	
// Jahresplan Classe und Allg.Funktionen		
	include_once('../../../include/jahresplan.class.php');
	include_once(dirname(__FILE__).'/jahresplan_funktionen.inc.php');		
// -------------------------------------------------------------------------------------------------------------------------

//	Datenbank Verbindung herstellen 		
	if (!datebenbankConnect(&$oJahresplan))
		exit('db error!');
		
// Initialisieren, und Parameter einlesen
	if (!getRequestParameter(&$oJahresplan))
		exit('Requestparam error!');
		
// ---------------- Jahresplan Include Dateien einbinden je Anzeige bzw Verarbeitung
   	$includeFILE=strtolower($oJahresplan->workSITE.".inc.php"); 
   	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		    include_once($includeFILE);
	if (!empty($oJahresplan->workFUNK))
	{
	   	$includeFILE=strtolower($oJahresplan->workFUNK.".inc.php"); 
	   	if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
		    include_once($includeFILE);
	}
// -------------------------------------------------------------------------------------------------------------------------
// Kategorie einlesen
	jahresplan_funk_veranstaltungskategorie_load_kpl(&$oJahresplan);	
	
	
	
	
// -------------------------------------------------------------------------------------------------------------------------
	// Fuer die Bildfunktion werden keine Datenbenoetigt, und nach Verarbeitung Programm beenden
   	if (trim($oJahresplan->workSITE)==constJahresplanIMAGE) 
	{	
		switch ($oJahresplan->workFUNK)
		{
			case constJahresplanLoadIMAGE:
				$showHTML.=loadIMGfromHEX(&$oJahresplan);
				break;
			default:
				createIMGfromHEX(&$oJahresplan);
				break;
		}			
	}	// bei der Bildverarbeitung wird in der Funktion mit Exit das Prog. verlassen	
	
	
// -------------------------------------------------------------------------------------------------------------------------
	// AJAX Remote Datenermittlung
  	if (trim($oJahresplan->workSITE)==constJahresplanAJAX) 
	{	
		switch ($oJahresplan->workFUNK)
		{
		// Veranstaltungskategorie
			case constJahresplanWartungKATEGORIE:
				$showHTML.=jahresplan_veranstaltungskategorie(&$oJahresplan);
			
				break;
			case constJahresplanDeleteKATEGORIE:
				$showHTML.=jahresplan_veranstaltungskategorie_remove(&$oJahresplan);
				break;

		// Veranstaltung	
			case constJahresplanDetailVERANSTALTUNG:
				$showHTML.=jahresplan_veranstaltung_detail(&$oJahresplan);
				break;
		// Veranstaltung Wartung - Update - Aendern
			case constJahresplanWartungVERANSTALTUNG:
				$showHTML.=jahresplan_veranstaltung_upd(&$oJahresplan);
				break;
		// Veranstaltung Wartung - Delete - Entfernen									
			case constJahresplanDeleteVERANSTALTUNG:
				$showHTML.=jahresplan_veranstaltung_liste_del(&$oJahresplan);
				break;
				
			case constJahresplanLesenVERANSTALTUNG:
				$showHTML.=jahresplan_veranstaltung_listenanzeige(&$oJahresplan);
				break;

		// Reservierung	Auflistung fuer Wartung bzw. Detailanzeige 	
			case constJahresplanLesenRESERVIERUNG:
				$showHTML.=jahresplan_reservierung_listenanzeige(&$oJahresplan);
				break;				
		// Reservierung	aendern mit oder ohne einer Veranstaltungs ID
			case constJahresplanWartungRESERVIERUNG:
				$showHTML.=jahresplan_reservierung_upd(&$oJahresplan);
				break;				

		// Kalender
			case constJahresplanAnzeigeDEFAULT:
				// Zusaetzlich die Listeladen beim Default
			   	$includeFILE=strtolower("jahresplan_veranstaltung_listenanzeige.inc.php"); 
	   			if (file_exists($includeFILE))// Check ob das Verarbeitungs-Include File vorhanden ist
				    include_once($includeFILE);
				$showHTML.=jahresplan_veranstaltungskategorie_default(&$oJahresplan);
				break;
			default:
				$showHTML.='Funktion '.$oJahresplan->workFUNK.' ist nicht vorhanden!';
				break;
		}	
	}		
	else if (trim($oJahresplan->workSITE)!=constJahresplanAJAX) 
	{
	// Menue
		$showHTML.=showMenueFunktion(&$oJahresplan);
	}
	
	// Fehler - Error Ausgabe hinzufuegen dem HTML Content
	$showHTML.=jahresplan_funk_disp_error(@$oJahresplan);

	
// -------------------------------------------------------------------------------------------------------------------------
// HTML Ausgabe Datenstrom Teil I Header

	$showCSS="../../../skin/style.css.php";
	$showHTML='<?xml version="1.0" encoding="'.$oJahresplan->htmlENCODE.'" standalone="yes"?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.(defined('HTML_HEADER_LANGUAGE_ISO')?HTML_HEADER_LANGUAGE_ISO:'DE').'" lang="'.(defined('HTML_HEADER_LANGUAGE_ISO')?HTML_HEADER_LANGUAGE_ISO:'DE').'">
	<head>
	
		<title>Veranstaltung,Reservierung '.$oJahresplan->workSITE.'</title>
		<meta name="description" content="Jahresplan '.$oJahresplan->workSITE.'" />
		<meta name="keywords" content="Jahresplan,Reservierung,'.$oJahresplan->workSITE.'" />
		<meta http-equiv="Content-Type" content="text/html;charset='.$oJahresplan->htmlENCODE.'" />
		
		<meta http-equiv="expires" content="-1" />
		<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
       	<meta http-equiv="pragma" content="no-cache" />
		
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<link href="'.$showCSS.'" rel="stylesheet" type="text/css" />

	<script language="JavaScript1.2" type="text/javascript">
	<!--
	function show_popup(x)
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
		   else
		    	alert(" show layer "+x +" nicht gefunden");
	}	   	
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
			} 

			else {	

//				hoch=(Hoehe - ( DivHeight + window.event.clientY ))-2 ;
				weite=(Weite - ( DivWidth + window.event.clientX ))-2 ;
//				if (hoch>0) hoch=0;
				if (weite>0) weite=0;
				with (window.event && document.getElementById(x).style) {
//					top=window.event.clientY + document.body.scrollTop+( 1 + hoch );
					left=window.event.clientX + document.body.scrollLeft+( 1 + weite );
				}	
			}
			
		}
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
function clear_layer(wohin)	
{
	set_layer(\'&nbsp;\',wohin);
}
function copy_layer(was,wohin)	
{
	if (document.getElementById(wohin).innerHTML) 
	{
		if (document.getElementById(was).innerHTML) 
			document.getElementById(wohin).innerHTML=document.getElementById(was).innerHTML;
		else if (document.getElementById(was).value) 
			document.getElementById(wohin).innerHTML=document.getElementById(was).value;
	}
	else if (document.getElementById(wohin).value) 
	{
		if (document.getElementById(was).innerHTML) 
			document.getElementById(wohin).value=document.getElementById(was).innerHTML;
		else if (document.getElementById(was).value) 
			document.getElementById(wohin).value=document.getElementById(was).value;
	}
}
function set_layer(was,wohin)	
{
	if (document.getElementById(wohin).innerHTML) 
	{
		document.getElementById(wohin).innerHTML=was;
	}
	else if (document.getElementById(wohin).value) 
	{
		document.getElementById(wohin).value=was;
	}
	else if (document.all && document.all[wohin]) 
	{      
	   	document.all(wohin).value=was;
      	}
	else
		alert(wohin + " nicht gefunden");
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

function PruefeDatum(Datum,Startjahr,Endjahr) 
 { 
      var Datum, Tag, Monat, Jahr, Laenge, tageMonat; 
      Laenge=Datum.length; 
	  
      var datum_int = new Date(); 
  
      if (!parseInt(Startjahr) || Startjahr<1000)
	  {	  	
    	  Startjahr = datum_int.getFullYear();
	      Startjahr = Startjahr - 1;      
      } 
  

      if (!parseInt(Endjahr) || Endjahr<1000)
	  {
	  	Endjahr = datum_int.getFullYear();
      	Endjahr = Endjahr +1;
	  }

      if (Laenge==10 && Datum.substring(2,3)=="." && Datum.substring(5,6)==".") 
      { 
	      Tag=parseInt(Datum.substring(0,2),10); 
	      Monat=parseInt(Datum.substring(3,5),10); 
	      Jahr=parseInt(Datum.substring(6,10),10); 
      } 
      else 
      { 
         alert("Kein gueltiges Datum!\nBitte Datum "+ Datum +" in der Form: TT.MM.JJJJ eingeben!"); 
	     return false; 
      } 
       
      if (Monat==4 || Monat==6 || Monat==9 || Monat==11) 
      { 
	      tageMonat=30; 
      } 
      else if (Monat==1 || Monat==3 || Monat==5 || Monat==7 || Monat==8 
        || Monat==10 || Monat==12) 
      { 
      tageMonat=31; 
      } 
      else if(Monat==2 && Jahr%4==0 && Jahr%100!=0 || Jahr%400==0) 
      { 
      tageMonat=29; 
      } 
      else if(Monat==2 && Jahr%4!=0 || Jahr%100==0 && Jahr%400!=0) 
      { 
      tageMonat=28; 
      } 
       
      if (Tag>=1 && Tag<=tageMonat && Monat>=1 && Monat<=12 && Jahr>=Startjahr && Jahr<=Endjahr) 
      { 
	      return true; 
      } 
      else 
      { 
	  	if (Tag<1 || Tag>tageMonat)
	         alert("Kein gueltiges Datum - Tag ("+ Tag +" >1 und <"+ tageMonat+" ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
	  	else if (Monat<1 || Monat>12)
	         alert("Kein gueltiges Datum - Monat ("+ Monat +"> 1 und <12 ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
	  	else if (Jahr<Startjahr || Jahr>Endjahr )
	         alert("Kein gueltiges Datum - Jahr ("+ Jahr +"> "+Startjahr+" und <"+Endjahr+" ) Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
		else	
	         alert("Kein gueltiges Datum!\nBitte Datum  "+ Datum +"  in der Form: TT.MM.JJJJ eingeben!"); 
	     return false; 
      } 
 
         
  }

function TimestampDatumZeit(Datum,Zeit,Startjahr,Endjahr) 
 { 
      var Datum, Tag, Monat, Jahr, Laenge,Stunde,Minute; 
      Laenge=Zeit.length; 
      var datum = new Date(); 
      var Endjahr = datum.getYear(); 
      Endjahr = Endjahr +10;
      var Startjahr = datum.getYear();
      Startjahr = Startjahr - 10;      
	  if (!PruefeDatum(Datum,Startjahr,Endjahr))
	  	return false;

      Tag=parseInt(Datum.substring(0,2),10); 
      Monat=parseInt(Datum.substring(3,5),10); 
      Jahr=parseInt(Datum.substring(6,10),10); 
		
		
      if (Laenge==5 && Zeit.substring(2,3)==":") 
      { 
	      Stunde=parseInt(Zeit.substring(0,2),10); 
	      Minute=parseInt(Zeit.substring(3,5),10); 
      } 

      else if (Laenge==4 && Zeit.substring(1,2)==":") 
      { 
	      Stunde=parseInt(Zeit.substring(0,1),10); 
	      Minute=parseInt(Zeit.substring(2,4),10); 
      } 
      else 
      { 
         alert("Kein gueltige Zeit!\nBitte Zeit "+Zeit+" in der Form: HH:MM eingeben!"); 
	     return false; 
      }
	Monat=Monat-1;
    //if (Monat<1) Monat=1;
	var timestamp = (new Date(Jahr,Monat,Tag,Stunde,Minute).getTime()/1000); 
	return timestamp;

}
  
  
function callWindows(url,nameID,Inhalt,druck)
{
 // width=(Pixel) - erzwungene Fensterbreite 
 // height=(Pixel) - erzwungene Fensterhöhe 
 // resizable=yes/no - Größe fest oder veränderbar 
 // scrollbars=yes/no - fenstereigene Scrollbalken 
 // toolbar=yes/no - fenstereigene Buttonleiste 
 // status=yes/no - fenstereigene Statuszeile 
 // directories=yes/no - fenstereigene Directory-Buttons (Netscape) 
 // menubar=yes/no - fenstereigene Menüleiste 
 // location=yes/no - fenstereigenes Eingabe-/Auswahlfeld für URLs 
 
 	if (Inhalt!=\'\' && document.getElementById(Inhalt) ) 
	{
		var PrintWin=window.open("",nameID, "dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes, width=1000,height=800,left=5, top=5"); 
		PrintWin.document.open();
		PrintWin.document.write(\'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">\');
		PrintWin.document.write(\'<html><head>\');	
		PrintWin.document.write(\'<meta http-equiv="Content-Type" content="text/html;charset='.$oJahresplan->htmlENCODE.'">\');		
		PrintWin.document.write(\'<meta http-equiv="pragma" content="no-cache">\');
		PrintWin.document.write(\'<meta http-equiv="expires" content="0">\');
		PrintWin.document.write("<title>"+nameID+"</title>");
		var getstr=\'<link href="'.$showCSS.'" rel="stylesheet" type="text/css" />\'; //CSS
		PrintWin.document.write(getstr);
		
		if (!druck || druck!=false) {
			PrintWin.document.write(\'</head><body onload="window.print();">\');
	 	} else {
			PrintWin.document.write(\'</head><body>\');
			}
		
		var text=document.getElementById(Inhalt).innerHTML;
		
		PrintWin.document.write(text);
		if (text) delete text; 
		
		PrintWin.document.write("</body></html>");
		PrintWin.document.close();
		
		PrintWin.focus();
	 	PrintWin.setTimeout("window.close();",500000);
	} else {
	       var InfoWin=window.open(url,nameID,"copyhistory=no,directories=no,location=no,dependent=yes,toolbar=no,status=no,menubar=no,resizable=yes,scrollbars=yes, width=1000,height=800,left=5, top=5");  
		InfoWin.focus();
		InfoWin.setTimeout("window.close()",800000);
	}
	
}


function callAjax(url,nameID,mime_type)
{
  if (url=="")  {
  	document.getElementById(nameID).innerHTML="Url fehlt!";
  }	
  if (mime_type=="XML" || mime_type=="xml") {
  	mime_type=="HTML"
  }
  

  
	//erstellen des requests
    	var req = null;
	
		try
		{
		     req = new XMLHttpRequest();
        	}
	    catch (ms)// hier beginnt der IE Teil		
	    {
       		 try
			 {
			    req = new ActiveXObject("Msxml2.XMLHTTP");
       		 } 
		        catch (nonms)
			 {
			        try{
              			req = new ActiveXObject("Microsoft.XMLHTTP");
				     } 
			        catch (failed)
					 {
            	 		req = null;
			       	 }
			 }  
		}
      
	if (req == null)
	{
		if (nameID!="")
	           document.getElementById(nameID).innerHTML="Browser ohne Ajax Funktion";
		   return "";
	}
	    if (req.overrideMimeType) {
			if (mime_type=="XML" || mime_type=="xml") {
	      	       	req.overrideMimeType("text/xml; charset='.$oJahresplan->htmlENCODE.'"); // zu dieser Zeile siehe weiter unten
			} else {
		           req.overrideMimeType("text/html; charset='.$oJahresplan->htmlENCODE.'"); // zu dieser Zeile siehe weiter unten
			}
	} // ende overrideMimeType		     
		   
    //anfrage erstellen (GET, url ist localhost, request ist asynchron      

	url=url+"&Zeitpunkt="+new Date().getTime();
	req.open("GET", url , true);
	
       //Beim abschliessen des request wird diese Funktion ausgeführt
                req.onreadystatechange = function(){            
			/*	Status Info
			    * 0 (nicht initialisiert)
			    * 1 (lade)
			    * 2 (geladen)
			    * 3 (interaktiv)
			    * 4 (vollständig) 
			*/				  
	                switch(req.readyState) {
    	                      case 4:
        	    	   	        if(req.status!=200) {
							if (nameID!="")
							{
//								set_layer("Fehler:"+req.status,nameID);
								document.getElementById(nameID).innerHTML="Fehler:"+req.status;
							}	
							else
							{
								alert("Fehler:"+req.status);
							}
	                	        } else {    
									if (nameID!="")
									{
					//					set_layer(req.responseText,nameID);
										document.getElementById(nameID).innerHTML=req.responseText;
									}		
                     		    }
                            	break;
                           default:
								if (nameID!="")
								{	
									var waitText="bitte warten!";
//									set_layer(waitText,nameID);
									document.getElementById(nameID).innerHTML=waitText;
       							}
		            	        break;     
                        }
                    };
                req.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
                req.send(null);
            }



function neuAufbau () {
/*  if (Weite != Fensterweite() || Hoehe != Fensterhoehe())
	location.href = location.href;
*/	
}
/* Überwachung von Netscape initialisieren */

if (!window.Weite && window.innerWidth) {
  	window.onresize = neuAufbau;
  
  Weite = Fensterweite();
  Hoehe = Fensterhoehe();
}
-->
</script>				
</head>
<body id="hauptbody" >
<script type="text/javascript">
<!--
/* Überwachung von Internet Explorer initialisieren */
if (!window.Weite && document.body && document.body.offsetWidth) 
	{
	  window.onresize = neuAufbau;
	  Weite = Fensterweite();
	  Hoehe = Fensterhoehe();
	}
//  alert(Weite+ \' \'+Hoehe);
-->
</script>
'.(trim($oJahresplan->workSITE)!=constJahresplanAJAX?'<a name="top">&nbsp;</a>':'').'
<div id="'.constPopUpName.'" style="position: absolute;right:20px;background-color:#FFFFFF;width:70%;display:none;empty-cells: hide;z-index:103;">&nbsp;</div>
<div id="'.constPopUpReserv.'" style="position: absolute;top:20px;left:10px;background-color:#FFF5EC;width:75%;display:none;empty-cells: hide;z-index:110;">&nbsp;</div>
' .$showHTML.(trim($oJahresplan->workSITE)!=constJahresplanAJAX?'<a href="#top">top</a>':'').
'</body></html>';

	if (stristr($oJahresplan->htmlENCODE,"UTF") )
		$showHTML=utf8_encode(utf8_decode($showHTML));
	unset($oJahresplan);
	
exit($showHTML);


	$proxy=GetTrueIP();
	$browser=GetBrowser();
	if (headers_sent() || !empty($proxy) || empty($browser)) 
		exit($showHTML);

	
// HTML Kompremierung	
	$iTmpCompress=0;
	echo $encode = getenv("HTTP_ACCEPT_ENCODING");
	if(ereg("gzip",$encode) || ereg("x-gzip",$encode)) { 
			//zlib.output_compression = 1 , zlib.output_compression_level = 9
			if (@ini_get( 'zlib.output_compression' )) 
			{
				@ini_set('zlib.output_compression_level',5);
				@ob_end_clean();
				@ob_start();
				@ob_implicit_flush(0);
				$iTmpCompress=3;	
			}
			//   ob_gzhandler() requires the zlib extension,output_handler =	,output_buffering=On		
		 	If (empty($iTmpCompress) && extension_loaded("zlib") && @ini_get('output_buffering')) 
			{
				@ob_end_clean();
				If (@ob_start('ob_gzhandler')) 
				{
					$iTmpCompress=2;
				}
			} 
		} // Ende If HTTP_ACCEPT_ENCODING"
	// output_buffering=On	and not  zlib extension
	if (empty($iTmpCompress) && @ob_start()) 
	{
		@ob_end_clean();
		$iTmpCompress=1;
	}		
	
	if ($iTmpCompress==1) 
	{
 		@ob_end_flush();
	}	
	elseif ($iTmpCompress==2) 
	{
		@ob_end_clean();
		header("Content-Encoding: gzip");
		$str = ob_gzhandler ( $showHTML, 5 );
		if($str===false)
		    exit('ob_gzhandler() returns false.');
		else
		    exit("$str");
	}
	else if ($iTmpCompress==4) 
	{
		print_r($showHTML);
		$gzip_size= @ob_get_length(); 
   		$gzip_contents = @ob_get_clean(); // PHP < 4.3 use ob_get_contents() + ob_end_clean() 
		@ob_end_clean();
		@header('Content-length: '.$gzip_size);
		if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
			@header('Content-Encoding: x-gzip');
		} else if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			@header('Content-Encoding: gzip');
			}	
		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00", 
   		substr(gzcompress($gzip_contents, $iTmpCompressLevel), 0, - 4), // substr -4 isn't needed 
		pack('V', crc32($gzip_contents)),    // crc32 and 
   		pack('V', $gzip_size);              // size are ignored by all the browsers i have tested 
		@flush();
	}
	else if ($iTmpCompress==3) 
	{
		print_r($showHTML);
		$gzip_size= @ob_get_length(); 
   		$gzip_contents = @ob_get_clean(); // PHP < 4.3 use ob_get_contents() + ob_end_clean() 
		@ob_end_clean();
		@header('Content-length: '.$gzip_size);
		if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
			@header('Content-Encoding: x-gzip');
		} else if(strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			@header('Content-Encoding: gzip');
			}	
		// open file for writing with maximum compression
		$filename="erp_". time().".gz" ;
		$zp = gzopen($filename, "w9");
		// write string to file
		gzwrite($zp, $gzip_contents);
		// close file
		gzclose($zp);
		// open file for reading
		$zp = gzopen($filename, "r");
		// read 3 char
		echo gzread($zp, $gzip_size);
		// output until end of the file and close it.
		gzpassthru($zp);
		gzclose($zp);
		echo "\n";
		unlink($filename);
		@flush();
	} 
	else
		exit($showHTML);

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
# **************************************************************************************************** 
function GetTrueIP() {
	global $REMOTE_ADDR, $HTTP_CLIENT_IP;
	global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
	global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;

// Get some server/environment variables values
if (empty($REMOTE_ADDR)) {
if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];}
else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
$REMOTE_ADDR = $_ENV['REMOTE_ADDR'];}
else if (@getenv('REMOTE_ADDR')) {
$REMOTE_ADDR = getenv('REMOTE_ADDR');}} // end if

if (empty($HTTP_CLIENT_IP)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_CLIENT_IP'])) {
$HTTP_CLIENT_IP = $_SERVER['HTTP_CLIENT_IP'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_CLIENT_IP'])) {
$HTTP_CLIENT_IP = $_ENV['HTTP_CLIENT_IP'];}
else if (@getenv('HTTP_CLIENT_IP')) {
$HTTP_CLIENT_IP = getenv('HTTP_CLIENT_IP');}} // end if

if (empty($HTTP_X_FORWARDED_FOR)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
$HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];}
else if (@getenv('HTTP_X_FORWARDED_FOR')) {
$HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');}} // end if

if (empty($HTTP_X_FORWARDED)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
$HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
$HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];}
else if (@getenv('HTTP_X_FORWARDED')) {
$HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');}} // end if

if (empty($HTTP_FORWARDED_FOR)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
$HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
$HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];}
else if (@getenv('HTTP_FORWARDED_FOR')) {
$HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');}} // end if

if (empty($HTTP_FORWARDED)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
$HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
$HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];}
else if (@getenv('HTTP_FORWARDED')) {
$HTTP_FORWARDED = getenv('HTTP_FORWARDED');}} // end if

if (empty($HTTP_VIA)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
$HTTP_VIA = $_SERVER['HTTP_VIA'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
$HTTP_VIA = $_ENV['HTTP_VIA'];}
else if (@getenv('HTTP_VIA')) {
$HTTP_VIA = getenv('HTTP_VIA');}} // end if

if (empty($HTTP_X_COMING_FROM)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
$HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
$HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];}
else if (@getenv('HTTP_X_COMING_FROM')) {
$HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');}} // end if

if (empty($HTTP_COMING_FROM)) {
if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
$HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];}
else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
$HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];}
else if (@getenv('HTTP_COMING_FROM')) {
$HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');}} // end if

// Gets the default ip sent by the user
if (!empty($REMOTE_ADDR)) {
$direct_ip = $REMOTE_ADDR;}

// Gets the proxy ip sent by the user
$proxy_ip = constLeer;
if (!empty($HTTP_X_FORWARDED_FOR)) {
$proxy_ip = $HTTP_X_FORWARDED_FOR;
} else if (!empty($HTTP_X_FORWARDED)) {
$proxy_ip = $HTTP_X_FORWARDED;
} else if (!empty($HTTP_FORWARDED_FOR)) {
$proxy_ip = $HTTP_FORWARDED_FOR;
} else if (!empty($HTTP_FORWARDED)) {
$proxy_ip = $HTTP_FORWARDED;
} else if (!empty($HTTP_VIA)) {
$proxy_ip = $HTTP_VIA;
} else if (!empty($HTTP_X_COMING_FROM)) {
$proxy_ip = $HTTP_X_COMING_FROM;
} else if (!empty($HTTP_COMING_FROM)) {
$proxy_ip = $HTTP_COMING_FROM;} // end if... else if...

// Returns the true IP if it has been found, else ...
if (empty($proxy_ip)) {
// True IP without proxy
	return constLeer;
} else {
#	return constLeer;
	$is_ip = ereg('^([0-9]{1,3}.){3,3}[0-9]{1,3}', $proxy_ip, $regs);
	if ($is_ip && (count($regs) > 0)) {
	// True IP behind a proxy
		return $regs[0];
	} else {
		if (empty($HTTP_CLIENT_IP)) {
		// Can't define IP: there is a proxy but we don't have
		// information about the true IP
			return "(unbekannt) " . $proxy_ip;
		} else {
			// better than nothing
			return $HTTP_CLIENT_IP;}}} // end if... else...
	return "Proxy ?";			
} // end of function


function GetBrowser() {



$browser='?';
if (isset($_SERVER['HTTP_USER_AGENT']) && (!isset($HTTP_USER_AGENT) || empty($HTTP_USER_AGENT)) ) $HTTP_USER_AGENT=$_SERVER['HTTP_USER_AGENT'];
if (!isset($HTTP_USER_AGENT) || empty($HTTP_USER_AGENT) ) return $browser;


 if( eregi("(opera) ([0-9]{1,2}.[0-9]{1,3}){0,1}",$HTTP_USER_AGENT,$regs) || eregi("(opera/)([0-9]{1,2}.[0-9]{1,3}){0,1}",$HTTP_USER_AGENT,$regs))
 {
    $browser = "Opera $regs[2]";
 }
 else if( eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) )
 {
    $browser = "MS InternetExplorer $regs[2]";
 }
 else if( eregi("(konqueror)/([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) )
 {
    $browser = "Konqueror $regs[2]";
 }
 else if( eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})",$HTTP_USER_AGENT,$regs) )
 {
    $browser = "Lynx $regs[2]";
 }
 else if( eregi("(netscape6)/(6.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) )
 {
    $browser = "Netscape $regs[2]";
 }
 else if( eregi("mozilla/5",$HTTP_USER_AGENT) )
 {
    $browser = "Mozilla";
 }
 else if( eregi("(mozilla)/([0-9]{1,2}.[0-9]{1,3})",$HTTP_USER_AGENT,$regs) )
 {
    $browser = "Mozilla $regs[2]";
 }
# else if( eregi("w3m",$HTTP_USER_AGENT) )
# {
#    $browser = "w3m";
# }
 else 
 {
    $browser=constLeer;
 }
	
return $browser;

} // end of function



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
    $tmpArrayString="<br /><hr /><br />******* START *******<br />".$tmpArrayString."<br />******* ENDE *******<br /><hr /><br />";
	if (defined('Sprache_ISO')) 
	{
	    $tmpArrayString.="<br />Language:: ".Sprache_ISO;
	}    
    $tmpArrayString.="<br />Server:: ".$_SERVER['PHP_SELF']."<br />";
	return "$tmpArrayString";


}

?>
