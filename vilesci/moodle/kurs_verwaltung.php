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
#	require_once('../config.inc.php');
// ---------------- Vilesci Include Dateien einbinden
require_once('../../config/vilesci.config.inc.php');	
require_once('../../include/functions.inc.php');
require_once('../../include/globals.inc.php');
include_once('../../include/moodle19_course.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/studiengang.class.php');	
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/moodle'))
	die('Sie haben keine Berechtigung für diese Seite');


// ***********************************************************************************************	
// Variable Initialisieren
// ***********************************************************************************************
	// AusgabeStream
    $content='';
    $errormsg=array();
		
	$objMoodle = new moodle19_course();		
		
// ***********************************************************************************************
// POST oder GET Parameter einlesen 
// ***********************************************************************************************

#$studiensemester_kurzbz='';
#var_dump($_REQUEST);

// @$bAnzeige Listenanzeige wenn Submitbottom Anzeige gedrueckt wurde
	$bAnzeige= (isset($_REQUEST['anzeige'])?True:False);
// @cCharset Zeichensatz - Ajax mit UTF-8
	$cCharset= (isset($_REQUEST['client_encode'])?trim($_REQUEST['client_encode']):'UTF-8');
// @debug_switch Anzeige der xml-rfc Daten moegliche Stufen sind 0,1,2,3
	$debug_switch= (isset($_REQUEST['debug'])?$_REQUEST['debug']:0);


// @$studiensemester_kurzbz Studiensemester xxJJJJ - xx fuer SS Sommer  oder WW Winter
	$stsem = new studiensemester();
	if (!$stsem_aktuell = $stsem->getakt())
		$stsem_aktuell = $stsem->getaktorNext();

	$studiensemester_kurzbz=(isset($_REQUEST['studiensemester_kurzbz'])?trim($_REQUEST['studiensemester_kurzbz']):$stsem_aktuell);
// @$studiengang_kz Studiengang
	$studiengang_kz=(isset($_REQUEST['studiengang_kz'])?trim($_REQUEST['studiengang_kz']):'');
// @$semester Semester des Studienganges 
	$semester=(isset($_REQUEST['semester'])?trim($_REQUEST['semester']):'');
	
	$lehrveranstaltung_id=null;
	$lehreinheit_id=null;	


// @$semester Semester des Studienganges 
	$kursid=(isset($_REQUEST['kursid'])?trim($_REQUEST['kursid']):'');
    if (!empty($kursid))
    {
		$bAnzeige=false;
	  	if(!$objMoodle->getAllMoodleVariant($kursid,'','','','','',false))
	    {
		   	 	$errormsg[]='Problem beim Lehre Moodle-Kurs '.addslashes($kursid).' lesen '.$objMoodle->errormsg;
		}
		// Lehre Moodle-Kurs gefunden
		if(isset($objMoodle->result) && isset($objMoodle->result[0]))
	    {
			  $bAnzeige=true;
	          $moodle_id=$objMoodle->result[0]->moodle_id;
	          $lehrveranstaltung_id=$objMoodle->result[0]->moodle_lehrveranstaltung_id;
	          $lehreinheit_id=$objMoodle->result[0]->moodle_lehreinheit_id;
	          $studiensemester_kurzbz=$objMoodle->result[0]->studiensemester_kurzbz;
		}
		else if ($objMoodle->load($kursid))
       	{
	      	   $bAnzeige=true;
		}	  
       	else
       	{
        	 $errormsg[]='Moodle-Kurs wurde nicht gefunden '.addslashes($kursid).' '.$objMoodle->errormsg;
	    }
		 
   	}
	
	
// @$mdl_course_id Moodle Kurs ID
	$mdl_course_id= (isset($_REQUEST['mdl_course_id'])?$_REQUEST['mdl_course_id']:'');
// @$moodle_id Moodle SubKurs (Unterkat.) ID zu Moodle Kurs ID (mdl_course_id)
	$moodle_id= (isset($_REQUEST['moodle_id'])?$_REQUEST['moodle_id']:'');
	
	
// ***********************************************************************************************
//	Datenbankverbindungen zu Moodle und Vilesci und Classen
// ***********************************************************************************************

	
// ***********************************************************************************************
//	Verarbeitung einer Moodle-Kurs Loeschaktion
// ***********************************************************************************************
	if (!empty($mdl_course_id) && isset($_REQUEST['delete'])) // Kurs wird zum bearbeiten (loeschen) freigegeben
	{
		if ($objMoodle->deleteKurs($mdl_course_id,$moodle_id,$debug_switch))
			$errormsg[]=$objMoodle->errormsg;
		else
			$errormsg[]=$objMoodle->errormsg;
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
		$stsem->getAll();
		foreach ($stsem->studiensemester as $row)	
		{
			$content.='<option value="'.$row->studiensemester_kurzbz.'" '.(("$studiensemester_kurzbz"=="$row->studiensemester_kurzbz")?' selected="selected" ':'').'>&nbsp;'.$row->studiensemester_kurzbz.'&nbsp;</option>';
		}
		$content.='</select></td>';
	
	// Studiengang public.tbl_studiengang_kz
		$content.='<td>Studiengang</td><td><select onchange="document.'.$cFormName.'.submit();" name="studiengang_kz"><option value="">&nbsp;Alle&nbsp;</option>';		
		$stg = new studiengang();
		$stg->getAll('typ, kurzbz',true);

		
#var_dump($stg->result);		
		
		$max_semester=0;
		foreach ($stg->result as $row)
		{
				if (!$row->moodle)
					continue;
				if (empty($studiengang_kz) && !isset($_REQUEST['studiengang_kz']) )
					$studiengang_kz=$row->studiengang_kz;
					
				if ($studiengang_kz==$row->studiengang_kz)
					$max_semester=$row->max_semester;
					
				$content.='<option value="'.$row->studiengang_kz.'" '.(("$studiengang_kz"=="$row->studiengang_kz")?' selected="selected" ':'').'>&nbsp;'.$row->kuerzel.'&nbsp;('.$row->kurzbzlang.')&nbsp;</option>';
		}	
		$content.='</select></td>';

	// Semster public.tbl_studiengang_kz - max Semester des Selektierten Studiengangs
		$content.='<td>Semster</td><td><select onchange="document.'.$cFormName.'.submit();" name="semester"><option value="">&nbsp;Alle&nbsp;</option>';			
		if ($studiengang_kz!='')
		{
				for($i=0;$i<=$max_semester;$i++)
				{
					$content.='<option value="'.($i).'" '.(("$semester"=="$i")?' selected="selected" ':'').'>&nbsp;'.($i).'&nbsp;</option>';
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
	
	$detail=true;
	$lehre=null;
	$aktiv=null;
	
	// $kursid = Selektion der mdl_course_id
	if ($bAnzeige && $objMoodle && $objMoodle->getAllMoodleVariant($kursid,$lehrveranstaltung_id,$studiensemester_kurzbz,$lehreinheit_id,$studiengang_kz,$semester,$detail,$lehre,$aktiv))
	{
		// Header Top mit Anzahl der gelisteten Kurse		
		$content.= '<a name="top">'. count($objMoodle->result).' Kurs(e) gefunden</a>';	
		$content.='<table style="font-size:medium;border: 1px outset #F7F7F7;">';
			// Header Teil Information der Funktion	
			$content.='<tr class="liste" align="center">';
				$content.='<th colspan="8">Moodlekurs</th>';
				$content.='<td colspan="2">Anzahl</td>';
				$content.='<td>Kurs</td>';
			$content.='</tr>';

			// Headerinformation der Tabellenfelder 
			$content.='<tr class="liste" align="center">';
				$content.='<th>&nbsp;Lehrveranstaltung&nbsp;</th>';
				$content.='<th>&nbsp;Kurzbz.&nbsp;</th>';
				$content.='<th>&nbsp;StgKz&nbsp;</th>';
				$content.='<th>&nbsp;LV&nbsp;</th>';
				$content.='<th>&nbsp;Sem&nbsp;</th>';
				$content.='<th>&nbsp;LE&nbsp;</th>';
				$content.='<th>&nbsp;Kursbezeichnung&nbsp;</th>';
				$content.='<th>&nbsp;ID&nbsp;</th>';
				$content.='<td>&nbsp;Benotungen&nbsp;</td>';				
				$content.='<td title="Aktivit&auml;ten und Lehrmaterial">&nbsp;Aktivit&auml;ten&nbsp;</td>';				
				$content.='<td>&nbsp;Bearbeiten&nbsp;</td>';
			$content.='</tr>';



			if ( (!is_array($objMoodle->result) || count($objMoodle->result)<1 || !isset($objMoodle->result[0])) && $objMoodle->load($kursid))
			{
					// ZeilenCSS (gerade/ungerade) zur besseren Ansicht
					$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FEFFEC" ';
		
					// Listenzeile
					$content.='<tr '.$showCSS.' align="center">';
						$content.='<td colspan="6" '.$showCSS.'><font class="error">es gibt keine Referenz zum Kurs!  Moodlekurs entfernt ist m&ouml;glich.</font>&nbsp;</td>';
						$content.='<td '.$showCSS.'>'.$objMoodle->mdl_shortname.'</td>';
						$content.='<td '.$showCSS.' title="mdl_course_id:'.$kursid.'">'.$kursid.'</td>';
		
					// Anzahl Benotungen - Aktivitaeten und Lehrmaterial
						$content.='<td title="Benotungen" '.$showCSS.' colspan="2">&nbsp;</td>';

					// Bearbeitung Submit 				
						$content.= '<td style="cursor: pointer;" onclick="if (!window.confirm(\'L&ouml;schen Moodlekurs '.$kursid.' ? \')) {return false;}; document.'.$cFormName.'0.submit();">';
							$content.='<form style="display: inline;border:0px;" name="'.$cFormName.'0" method="GET" target="_self" action="'.$_SERVER["PHP_SELF"].'">';
							  	$content.= '<input style="display:none" type="text" name="mdl_course_id" value="'.$kursid.'" />';
								
								$content.= '<input style="display:none" type="text" name="studiensemester_kurzbz" value="'.$studiensemester_kurzbz.'" />';
								$content.= '<input style="display:none" type="text" name="studiengang_kz" value="'.$studiengang_kz.'" />';
								$content.= '<input style="display:none" type="text" name="semester" value="'.$semester.'" />';
								
								$content.= '<input style="display:none" type="text" name="debug" value="'.$debug_switch.'" />';
								$content.= '<input style="display:none" type="text" name="delete" value="delete" />';								
								$content.= '<img height="15" src="../../skin/images/table_row_delete.png" border="0" title="MoodleKurs entfernen" alt="table_row_delete.png" />';					
								$content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal0" />';
								$content.= 'entfernen';					
							$content.='</form>';
						$content.= '</td>';
					$content.='</tr>';
			
			}					

			

	
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

#	var_dump($objMoodle->result);

			// Listenzeile
			$content.='<tr '.$showCSS.' align="center">';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_bezeichnung.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_kurzbz.'</td>';
				$content.='<td "'.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_studiengang_kz.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_id.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehrveranstaltung_semester.'</td>';
				$content.='<td '.$showCSS.'>'.$objMoodle->result[$i]->lehreinheit_id.'</td>';
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
						$content.= '<input style="display:none" type="text" name="delete" value="delete" />';								
						$content.= '<input style="display:none" type="text" name="delete" value="delete" />';	
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
		'.$content.'<p class="error">'.implode('<br>',$errormsg).'</p>
	<!-- MoodleKurs Content Ende -->
	</body>
		</html>';
	exit($content);
?>
