<?php 
//@version $Id$
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

/*
*	Dieses Programm listet nach Selektinskreterien alle Moodelkurse zu einem Studiengang auf. 
*   Fuer jede MoodleID werden die Anzahl Benotungen, und erfassten sowie angelegte Zusaetze angezeigt.
*	Jeder der angezeigten Moodle IDs kann geloescht werden nach bestaetigung eines PopUp Fenster.
*/


// ***********************************************************************************************	
// Include Dateien
// ***********************************************************************************************
// ---------------- Standart Include Dateien einbinden
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/globals.inc.php');
// ---------------- Moodle Daten Classe
	include_once('../../include/moodle_course.class.php');
	
// ***********************************************************************************************	
// Variable Initialisieren
// ***********************************************************************************************
	// AusgabeStream
	$content='';
	// Vergleichsdatum Jahr und Monat fuer Studiensemester (Select-Auswahl)
	$cYYYYMM=date("Ym", mktime(0,0,0,date("m"),date("d"),date("y")));

	
// ***********************************************************************************************
// POST oder GET Parameter einlesen 
// ***********************************************************************************************
// @$studiensemester_kurzbz Studiensemester xxJJJJ - xx fuer SS Sommer  oder WW Winter
	$studiensemester_kurzbz=(isset($_REQUEST['studiensemester_kurzbz'])?trim($_REQUEST['studiensemester_kurzbz']):'');
// @$studiengang_kz Studiengang
	$studiengang_kz=(isset($_REQUEST['studiengang_kz'])?trim($_REQUEST['studiengang_kz']):'');
// @$semester Semester des Studienganges 
	$semester=(isset($_REQUEST['semester'])?trim($_REQUEST['semester']):'');

// @$semester Semester des Studienganges 
	$kursid=(isset($_REQUEST['kursid'])?trim($_REQUEST['kursid']):'');


// @$mdl_course_id Moodle Kurs ID
	$mdl_course_id= (isset($_REQUEST['mdl_course_id'])?$_REQUEST['mdl_course_id']:'');
// @$moodle_id Moodle SubKurs (Unterkat.) ID zu Moodle Kurs ID (mdl_course_id)
	$moodle_id= (isset($_REQUEST['moodle_id'])?$_REQUEST['moodle_id']:'');
// @$bAnzeige Listenanzeige wenn Submitbottom Anzeige gedrueckt wurde
	$bAnzeige= ($studiensemester_kurzbz!=''?True:False);
// @cCharset Zeichensatz - Ajax mit UTF-8
	$cCharset= (isset($_REQUEST['client_encode'])?trim($_REQUEST['client_encode']):'UTF-8');
// @debug_switch Anzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
	$debug_switch= (isset($_REQUEST['debug'])?$_REQUEST['debug']:0);
	
	
// ***********************************************************************************************
//	Datenbankverbindungen zu Moodle und Vilesci und Classen
// ***********************************************************************************************
	// DB Connect
	$conn=@pg_pconnect(CONN_STRING) or die('<div style="text-align:center;"><br />Datenbank zurzeit NICHT Online.<br />Bitte etwas Geduld.<br />Danke</div>');// 	Datenbankverbindung
	$conn_moodle = pg_pconnect(CONN_STRING_MOODLE) or die('<div style="text-align:center;"><br />MOODLE Datenbank zurzeit NICHT Online.<br />Bitte etwas Geduld.<br />Danke</div>');
	// Classen Instanzen
	$objMoodle = new moodle_course($conn, $conn_moodle);	

	
// ***********************************************************************************************
//	Verarbeitung einer Moodle-Kurs Loeschaktion
// ***********************************************************************************************
	
	if ($mdl_course_id!='' && $studiensemester_kurzbz!='') // Kurs wird zum bearbeiten (loeschen) freigegeben
	{
		include(dirname(__FILE__)."/xmlrpcutils/utils.php");
	    // Aktuellen Moodle Server ermitteln.
		if (defined('MOODLE_PATH')) // Eintrag MOODLE_PATH in Vilesci config.inc.php. Hostname herausfiltern
		{
			$host = str_replace('https://','',str_replace('http://','',str_replace('/moodle','',str_replace('/moodle/','',MOODLE_PATH))));
		}
		elseif ($_SERVER["HTTP_HOST"]=="dav.technikum-wien.at" ) // Vilesci config.inc.php nicht erweitert HTTP_HOST pruefen
		{
			$host = 'dav.technikum-wien.at';
		}	
		else // Produktivessystem
		{
			$host = 'cis.technikum-wien.at';
		}	

	// Variable Daten Initialisieren
		$uri = "/moodle/xmlrpc/xmlrpc.php";
		$method = "DeleteCourseByID";
		$args['CourseID']="$mdl_course_id";
		$port=$_SERVER["SERVER_PORT"];
		if ($debug_switch)
		{
			$content.="<br />Host:$host , Port:$port , Uri:$uri , Method:$method <br />";
		}
		$callspec = array(
			'method' => $method,
			'host' => $host,
			'port' => $port,
			'uri' => $uri,
			'user' => (isset($_SERVER["PHP_AUTH_USER"])?$_SERVER["PHP_AUTH_USER"]:""),
			'pass' => (isset($_SERVER["PHP_AUTH_PW"])?$_SERVER["PHP_AUTH_PW"]:""),
			'secure' =>false,
			'debug' => $debug_switch, 
			'args' => $args);
		$result = xu_rpc_http_concise($callspec);
		// Return Information
		// $result[0] = Status true/false
		// $result[1] = Informationstext
		// $result[2] = Ausgabetext von Moodle
		if (!is_array($result)) // Server wurde nicht erreicht.
		{
				$content.="Fehler xmlrpc call $result";
		}	
		else if ($result[0]==1) // Methodenaufruf erfolgreich	
		{
				#$content.=(isset($result[1])?$result[1]:"Moodel-Kurs gel&ouml;scht ");
				$qry = "DELETE FROM lehre.tbl_moodle WHERE mdl_course_id='".addslashes($mdl_course_id)."' ";
				if ($moodle_id!='')
					$qry.= " and moodle_id='".addslashes($moodle_id)."'"; 
				if(!pg_query($conn, $qry))
					$content.="<p>Moodlekurs $mdl_course_id wurde NICHT gel&ouml;scht in Lehre.</p>";
				$content.="<h3>Moodlekurs $mdl_course_id wurde gel&ouml;scht.</h3>";
		}	
		else // Result = 0 ein Fehler im RFC wurde festgestellt
		{
			$content.=(isset($result[1])?$result[1]:"Fehler beim Kurs l&ouml;schen ");
		}	
	}



// ***********************************************************************************************
//	HTML Auswahlfelder (Teil 1)
// ***********************************************************************************************
	// FormName erzeugen
	$cFormName='searchMoodleCurse'.$studiensemester_kurzbz.$studiengang_kz.$semester;
	$content.='
		<form accept-charset="UTF-8" name="'.$cFormName.'" method="GET">	
			<table><tr>';

	// Studiensemester public.tbl_studiensemester_kurzbz
		$content.='<td>Studiensemester</td><td><select onchange="document.'.$cFormName.'.submit();" name="studiensemester_kurzbz">';
		$sql_query = "SELECT studiensemester_kurzbz,to_char(start,'YYYYMM') as \"startYYYYMM\",to_char(ende,'YYYYMM') as \"endeYYYYMM\" FROM public.tbl_studiensemester order by start; ";
		if ($result = @pg_query($conn, $sql_query))
		{
			while ($row = @pg_fetch_object($result))
			{
			// Gibt es noch keinen POST/GET Parameterwert den aktuellen Studiensemesterwert nehmen zum Positionieren in der Selektliste 
			if (empty($studiensemester_kurzbz) && $cYYYYMM>=$row->startYYYYMM  && $cYYYYMM<=$row->endeYYYYMM) 
				$studiensemester_kurzbz=$row->studiensemester_kurzbz;

			$content.='<option value="'.$row->studiensemester_kurzbz.'" '.(("$studiensemester_kurzbz"=="$row->studiensemester_kurzbz")?' selected="selected" ':'').'>&nbsp;'.$row->studiensemester_kurzbz.'&nbsp;</option>';
			}
		}	
		$content.='</select></td>';
	
	// Studiengang public.tbl_studiengang_kz
		$content.='<td>Studiengang</td><td><select onchange="document.'.$cFormName.'.submit();" name="studiengang_kz"><option value="">&nbsp;Alle&nbsp;</option>';		
		$sql_query = "SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kurzkz,kurzbzlang FROM public.tbl_studiengang where public.tbl_studiengang.moodle='t' ORDER BY kurzkz,kurzbzlang;";
		if ($result = @pg_query($conn, $sql_query))
		{
			while($row=@pg_fetch_object($result))
			{
				$content.='<option value="'.$row->studiengang_kz.'" '.(("$studiengang_kz"=="$row->studiengang_kz")?' selected="selected" ':'').'>&nbsp;'.$row->kurzkz.'-'.$row->kurzbzlang.'&nbsp;</option>';
			}
		}	
		$content.='</select></td>';

	// Semster public.tbl_studiengang_kz - max Semester des Selektierten Studiengangs
		$content.='<td>Semster</td><td><select onchange="document.'.$cFormName.'.submit();" name="semester"><option value="">&nbsp;Alle&nbsp;</option>';			
		if ($studiengang_kz!='')
		{
			$sql_query = "SELECT max_semester FROM public.tbl_studiengang where studiengang_kz='".addslashes($studiengang_kz)."' OFFSET 0 LIMIT 1 ;";
			$result = @pg_query($conn, $sql_query);
			if ($row = @pg_fetch_object($result))
			{
				for($i=0;$i<=$row->max_semester;$i++)
				{
					$content.='<option value="'.($i).'" '.(("$semester"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
				}
			}	
		}
		$content.='</select></td>';

		$content.='<td>Kurs ID</td><td><input size="4" maxlength="8" name="kursid" value="'.$kursid.'">';			

		$content.='
			<td><input name="anzeige" type="submit" value=" anzeigen "><input style="display:none" type="text" name="debug" value="'.$debug_switch.'" /></td>
	</tr></table>
	</form>
	<hr>';

// ***********************************************************************************************
//	HTML Listenanzeige (Teil 2)
// ***********************************************************************************************
	// Bedingung zur Listenanzeige : Anzeige und Datengefunden
	if ($bAnzeige && $objMoodle && $objMoodle->getAllVariant('',$studiensemester_kurzbz,$studiengang_kz,$semester,true))
	{
		// Header Top mit Anzahl der gelisteten Kurse		
		$content.= '<a name="top">'. count($objMoodle->result).' Kurs(e) gefunden</a>';	
		
		$content.='<table style="font-size:medium;border: 1px outset #F7F7F7;">';
			// Header Teil Information der Funktion	
			$content.='<tr class="liste" align="center">';
				$content.='<th colspan="6">Moodlekurs</th>';
				$content.='<td colspan="2">Anzahl</td>';
				$content.='<td>Kurs</td>';
			$content.='</tr>';

			// Headerinformation der Tabellenfelder 
			$content.='<tr class="liste" align="center">';
				$content.='<th>&nbsp;Lehrveranstaltung&nbsp;</th>';
				$content.='<th>&nbsp;Kurzbz.&nbsp;</th>';
				$content.='<th>&nbsp;LV&nbsp;Id&nbsp;</th>';
				$content.='<th>&nbsp;StudiengangKz&nbsp;</th>';
				$content.='<th>&nbsp;Kursbezeichnung&nbsp;</th>';
				$content.='<th>&nbsp;ID&nbsp;</th>';
				$content.='<td>&nbsp;Benotungen&nbsp;</td>';				
				$content.='<td title="Aktivit&auml;ten und Lehrmaterial">&nbsp;Aktivit&auml;ten&nbsp;</td>';				
				$content.='<td>&nbsp;Bearbeiten&nbsp;</td>';
			$content.='</tr>';

					
		// Alle Moodlekurse in einer Schleife anzeigen.
		for($i=0;$i<count($objMoodle->result);$i++)
		{
			// ZeilenCSS (gerade/ungerade) zur besseren Ansicht
			if ($i%2)
				$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FEFFEC" ';
			else
				$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FCFCFC"  ';			

			if (!empty($kursid) && $kursid!=$objMoodle->result[$i]->mdl_course_id)
				continue;
	
				
			// Listenzeile
			$content.='<tr '.$showCSS.' align="center">';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_bezeichnung.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_kurzbz.'</td>';
				$content.='<td '.$showCSS.' title="Semester '.$objMoodle->result[$i]->lehrveranstaltung_semester.'">'.$objMoodle->result[$i]->lehrveranstaltung_id.'</td>';
				$content.='<td "'.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_studiengang_kz.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->mdl_shortname.'</td>';
				$content.='<td '.$showCSS.' title="mdl_course_id:'.$objMoodle->result[$i]->mdl_course_id.'">'.$objMoodle->result[$i]->mdl_course_id.'</td>';

			// Anzahl Benotungen
				$content.='<td title="Benotungen" '.$showCSS.'>'.$objMoodle->result[$i]->mdl_benotungen.'</td>';
			// Anzahl Aktivitaeten und Lehrmaterial
				$content.='<td title="Resourcen:'.$objMoodle->result[$i]->mdl_resource.', Quiz:'.$objMoodle->result[$i]->mdl_quiz.', Chat:'.$objMoodle->result[$i]->mdl_chat.', Forum:'.$objMoodle->result[$i]->mdl_forum.', Choice:'.$objMoodle->result[$i]->mdl_choice.'" '.$showCSS.'>'.($objMoodle->result[$i]->mdl_resource+$objMoodle->result[$i]->mdl_quiz+$objMoodle->result[$i]->mdl_chat + $objMoodle->result[$i]->mdl_forum	+ $objMoodle->result[$i]->mdl_choice ).'</td>';
				
			// Bearbeitung Submit 				
				$content.= '<td style="cursor: pointer;" onclick="if (!window.confirm(\'L&ouml;schen Moodlekurs '.$objMoodle->result[$i]->mdl_course_id.', '.$objMoodle->result[$i]->lehrveranstaltung_bezeichnung.' ? \')) {return false;}; document.'.$cFormName.'_'.$i.'.submit();">';
					$content.='<form style="display: inline;border:0px;" name="'.$cFormName.'_'.$i.'" method="GET" target="_self" action="'.$_SERVER["PHP_SELF"].'">';
					  	$content.= '<input style="display:none" type="text" name="mdl_course_id" value="'.$objMoodle->result[$i]->mdl_course_id.'" />';
						$content.= '<input style="display:none" type="text" name="studiensemester_kurzbz" value="'.$studiensemester_kurzbz.'" />';
						$content.= '<input style="display:none" type="text" name="studiengang_kz" value="'.$studiengang_kz.'" />';
						$content.= '<input style="display:none" type="text" name="semester" value="'.$semester.'" />';
						$content.= '<input style="display:none" type="text" name="debug" value="'.$debug_switch.'" />';
						$content.= '<img height="15" src="../../skin/images/table_row_delete.png" border="0" title="MoodleKurs entfernen" alt="table_row_delete.png" />';					
						$content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$i.'" />';
						$content.= 'entfernen';					
					$content.='</form>';
				$content.= '</td>';
			$content.='</tr>';
		} // Ende Moodlekurse in einer Schleife anzeigen.
		$content.= '</table>';
		$content.= '<a href="#top">zum Anfang</a>';
	} // Ende IF Bedingung Anzeige + Datengefunden
	
	
// ***********************************************************************************************
//	HTML Header und Foot zum Content (Ausgabestring) hinzufuegen, und Anzeigen
// ***********************************************************************************************
	$content='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title>Moodle - Kursverwaltung</title>
		<base target="main">
		<meta http-equiv="Content-Type" content="text/html; charset='.$cCharset.'">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="background_main">
	<h2>Moodle - Kursverwaltung</h2>
	<!-- MoodleKurs Content Start -->
		'.$content.'
	<!-- MoodleKurs Content Ende -->
	</body>
		</html>';
	exit($content);



#-------------------------------------------------------------------------------------------	
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
    $tmpArrayString.="<br />Server:: ".$_SERVER['PHP_SELF']."<br />";
	return "$tmpArrayString";


}	
?>