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

// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');

/*	
	require_once('../../../include/basis_db.class.php');
	if (!$db = new basis_db())
	      die('<div style="text-align:center;"><br />Keine Wettbewerbe zurzeit Online.<br />Bitte etwas Geduld.<br />Danke</div>');// 	Datenbankverbindung  
*/	  	
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');
// ---------------- Datenbank-Verbindung 
	include_once('../../../include/komune_wettbewerb.class.php');
	include_once('../../../include/komune_wettbewerbteam.class.php');
	include_once('../../../include/komune_wettbewerbeinladungen.class.php');
	
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');

// Kommunen Allg.Funktionen		
	include_once('kommune_funktionen.inc.php');

// ---------------- Konstante
// Max. Wettbewerbe je Zeile am Starttemplate	
	if (!defined('constMaxWettbwerbeZeile')) define('constMaxWettbwerbeZeile',3 );
// Pflichteingabefelder Defaultwert   
   	if (!defined('constEingabeFehlt')) define('constEingabeFehlt','Eingabe !' );
		 
// Kommunen - Wettbewerb - Datenobjekt -----------------------------------------------------------------------------------------------------------

	// Datenobjekt - Alle Kommunen - Daten in einem Objekt sammeln
	$oWettbewerb= new stdClass;

	$oWettbewerb->user='?';
	$oWettbewerb->admin=false;
	$oWettbewerb->admin=true;
	
	$oWettbewerb->errormsg=array();
	$oWettbewerb->errormsg[]='Fehleranzeige';
	
// ---------------- Anzeige/Ausgabe Variable Initialisieren

	// Parameter Applikation - Template Auswahl
	$oWettbewerb->workSITE = (isset($_REQUEST['workSITE']) ? $_REQUEST['workSITE'] : (isset($_REQUEST['userSel']) ? $_REQUEST['userSel'] : ''));
	$oWettbewerb->PersonenBenutzer=array(); // Merken der Personen in eigen Array - nur einmal lesen jeden User (Performence)
		
// AktiverAnwender-----------------------------------------------------------------------------------------------------------
	$user=get_uid();
#	$user='pam';
#	$user='oesi';
#	$user='ruhan';
#	$user='kindlm';
	if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');
	if (!$pers=kommune_funk_benutzerperson($user,&$oWettbewerb))
		die('Sie wurden nicht als Benutzer gefunden - UID ! <a href="javascript:history.back()">Zur&uuml;ck</a>');
	$oWettbewerb->user=$user;

	
// Wettbewerb ---------------------------------------------------------------------------------------------------------------
	// Parameter Wettbewerb - Type
   	$oWettbewerb->wbtyp_kurzbz=trim((isset($_REQUEST['wbtyp_kurzbz']) ? $_REQUEST['wbtyp_kurzbz']:''));
	// Parameter Wettbewerb
   	$oWettbewerb->wettbewerb_kurzbz=trim((isset($_REQUEST['wettbewerb_kurzbz']) ? $_REQUEST['wettbewerb_kurzbz']:''));

	// WettbewerbTypen und Wettbewerbe
	$oWettbewerb->WettbewerbTyp=array();
	$oWettbewerb->Wettbewerb=array();
	
	// Team
	 $oWettbewerb->team_kurzbz=trim((isset($_REQUEST['team_kurzbz']) ? $_REQUEST['team_kurzbz'] : ''));
	

// Initialisieren Anzeige-Variable --------------------------------------------------------------------------------------

	$showHTML=kommune_html_header($oWettbewerb);
	
// Headerzeile-----------------------------------------------------------------------------------------------------------
#   	$showHTML.='<h1>Kommune von '.$pers->langname.(isset($pers->foto_image)?$pers->foto_image:'').'</h1>';

// Menuezeile-----------------------------------------------------------------------------------------------------------
	$showHTML.='<p>[&nbsp;<a href="'.kommune_funk_create_url('kommune_template_start',$oWettbewerb).'">Startseite</a>&nbsp;|&nbsp;<a href="'.kommune_funk_create_url('kommune_wettbewerb_wartung',$oWettbewerb).'">meine Spiele</a>&nbsp;]&nbsp;';

     	if ($oWettbewerb->admin)
     	{
     		$showHTML.='&nbsp;Admin:&nbsp;[&nbsp;<a href="'.kommune_funk_create_url('kommune_wettbewerbtypen_wartung',$oWettbewerb).'">Wettbewerbtypen</a>&nbsp;|&nbsp;<a href="'.kommune_funk_create_url('kommune_wettbewerb_wartung',$oWettbewerb).'">Wettbewerbe</a>&nbsp;]&nbsp;';
   	}
	$showHTML.='</p>';
	
     switch (trim($oWettbewerb->workSITE)) 
     {
	   	case 'kommune_hex_img':
		   	@ob_end_clean();
		    	include_once(dirname(__FILE__)."/kommune_hex_img.inc.php");	
			exit;
	       	break;

		case 'kommune_wettbewerbtypen_wartung':
			echo $showHTML;	 
		    	include_once(dirname(__FILE__)."/kommune_wettbewerbtypen_wartung.inc.php");	
			break;
			
		case 'kommune_wettbewerb_wartung':
			echo $showHTML;	 
		    	include_once(dirname(__FILE__)."/kommune_wettbewerb_wartung.inc.php");	
			break;

		case 'kommune_team_wartung':
			echo $showHTML;	 
		    	include_once(dirname(__FILE__)."/kommune_team_wartung.inc.php");	
			break;

			
		case 'kommune_template_pyramiden':
			echo $showHTML;	 
		    	include_once(dirname(__FILE__)."/kommune_template_pyramiden.inc.php");	
			break;

			
		default:
			echo $showHTML;	 
		    	include_once(dirname(__FILE__)."/kommune_template_start.inc.php");	
	       	break;
     }	
	$showHTML='';			
	

	
// Errorzeile-----------------------------------------------------------------------------------------------------------
	$showHTML='<div id="errorKommune">';
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->errormsg);$iTmpZehler++)
	{
		if (!empty($oWettbewerb->errormsg[$iTmpZehler]))	
			$showHTML.='<p classe="error">'. $oWettbewerb->errormsg[$iTmpZehler].'</p>';
	}
	$showHTML.='</div>';
   	echo $showHTML.'</body></html>';

// HTMLOutput-----------------------------------------------------------------------------------------------------------
   	exit();
?>
