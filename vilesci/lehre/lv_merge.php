<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Manfred Kindl		< manfred.kindl@technikum-wien.at >
 */
/**
 * Script to merge or transfer courses.
 * Two columns are shown with courses that matches the filter.
 * Mark the course on the left side, that will be merged with the one on the right side.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
//require_once('../../include/person.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/lehrtyp.class.php');
require_once('../../include/log.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/lehrveranstaltung'))
	die($rechte->errormsg);

$activeAddons = array_filter(explode(";", ACTIVE_ADDONS));

$msg='';

$input_text_left = isset($_REQUEST['input_text_left'])?$_REQUEST['input_text_left']:'';
$input_text_right = isset($_REQUEST['input_text_right'])?$_REQUEST['input_text_right']:'';
$select_stg_kz_left = isset($_REQUEST['select_stg_kz_left'])?$_REQUEST['select_stg_kz_left']:'';
$select_stg_kz_right = isset($_REQUEST['select_stg_kz_right'])?$_REQUEST['select_stg_kz_right']:'';
$select_semester_left = isset($_REQUEST['select_semester_left'])?$_REQUEST['select_semester_left']:'-1';
$select_semester_right = isset($_REQUEST['select_semester_right'])?$_REQUEST['select_semester_right']:'-1';
$select_orgform_left = isset($_REQUEST['select_orgform_left'])?$_REQUEST['select_orgform_left']:'';
$select_orgform_right = isset($_REQUEST['select_orgform_right'])?$_REQUEST['select_orgform_right']:'';
$select_studienplan_left = isset($_REQUEST['select_studienplan_left'])?$_REQUEST['select_studienplan_left']:'';
$select_studienplan_right = isset($_REQUEST['select_studienplan_right'])?$_REQUEST['select_studienplan_right']:'';
$select_lehrtyp_left = isset($_REQUEST['select_lehrtyp_left'])?$_REQUEST['select_lehrtyp_left']:'';
$select_lehrtyp_right = isset($_REQUEST['select_lehrtyp_right'])?$_REQUEST['select_lehrtyp_right']:'';

$courseLeft = isset($_REQUEST['courseLeft'])?$_REQUEST['courseLeft']:'-1';
$courseRight = isset($_REQUEST['courseRight'])?$_REQUEST['courseRight']:'-1';

$stsem = new studiensemester();
$stsem_default = $stsem->getakt();

$studiensemester_kurzbz = isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:$stsem_default;

//echo $courseLeft.'<br>';
//echo $courseRight.'<br>';

if (isset($_REQUEST['compare']))
{
	if ($courseLeft!=$courseRight && $courseLeft!='-1' && $courseRight!='-1')
	{
		// Define an array of attributes, that are NOT to be compared
		$deleteValues = array(
				'new' => null,
				'ext_id' => null,
				'insertamum' => null,
				'insertvon' => null,
				'updateamum' => null,
				'updatevon' => null,
				'bezeichnung_arr' => null,
				'lehrveranstaltungen' => null,
				'errormsg' => null
		);

		$lv1 = new lehrveranstaltung();
		$lv1->load($courseLeft);
		$lv1_arr = get_object_vars($lv1);
		$lv1_arr = array_diff_key($lv1_arr, $deleteValues);

		$lv2 = new lehrveranstaltung();
		$lv2->load($courseRight);
		$lv2_arr = get_object_vars($lv2);
		$lv2_arr = array_diff_key($lv2_arr, $deleteValues);

		$lv_diff1 = array_diff_assoc($lv1_arr, $lv2_arr);
		$lv_diff2 = array_diff_assoc($lv2_arr, $lv1_arr);

		$msg = '<span style="font-size: small"><b>Differences in courses (only columns with differences are shown)</b></span>';
		$msg .= '<table id="t3" class="tablesorter"><thead><tr>';
		foreach ($lv_diff1 as $key => $value)
		{
			$msg .= '<th title="'.$key.'">'.StringCut($key,10,false,'...').'</th>';
		}
		$msg .= '</tr></thead><tbody><tr>';
		foreach ($lv_diff1 as $key => $value)
		{
			if (is_bool($value))
				$msg .= '<td>'.($value?'<img src="../../skin/images/true.png" alt="true">':'<img src="../../skin/images/false.png" alt="false">').'</td>';
			elseif ($key == 'farbe')
				$msg .= '<td>'.$value.' <span id="farbevorschau" style="background-color: #'.$value.'; border: 1px solid #999999; cursor: default;">&nbsp;&nbsp;&nbsp;&nbsp;</span></td>';
			else
				$msg .= '<td>'.$value.'</td>';
		}
		$msg .= '</tr><tr>';
		foreach ($lv_diff2 as $key => $value)
		{
			if (is_bool($value))
				$msg .= '<td>'.($value?'<img src="../../skin/images/true.png" alt="true">':'<img src="../../skin/images/false.png" alt="false">').'</td>';
			elseif ($key == 'farbe')
				$msg .= '<td>'.$value.' <span id="farbevorschau" style="background-color: #'.$value.'; border: 1px solid #999999; cursor: default;">&nbsp;&nbsp;&nbsp;&nbsp;</span></td>';
			else
				$msg .= '<td>'.$value.'</td>';
		}
		$msg .= '</tr></tbody></table>';
	}
	else
		$msg="Please select 2 different courses";
}

if((isset($_REQUEST['transfer']) || isset($_REQUEST['mergeDelete'])) && isset($courseLeft) && isset($courseRight) && $courseLeft>=0 && $courseRight>=0)
{
	if($courseLeft==$courseRight)
	{
		$msg="The courses may not have the same ID";
	}
	else
	{
		if(!$rechte->isBerechtigt('lehre/lehrveranstaltung', NULL, 'sui'))
			die($rechte->errormsg);

		$msg='';
		$update_qry="BEGIN;";

		if (isset($_REQUEST['transfer']))
		{
			$update_qry.="UPDATE campus.tbl_benutzerlvstudiensemester SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			$update_qry.="UPDATE campus.tbl_lvgesamtnote SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			// Updates for Pruefungsverwaltung
			$update_qry.="UPDATE campus.tbl_lehrveranstaltung_pruefung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND pruefung_id IN (SELECT pruefung_id FROM campus.tbl_pruefung WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).");";
			$update_qry.="UPDATE campus.tbl_pruefungsanmeldung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND pruefungstermin_id IN (SELECT pruefungstermin_id FROM campus.tbl_pruefungstermin JOIN campus.tbl_pruefung USING (pruefung_id) WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).");";

			// LV-Infos will be copied if $courseRight has none and $courseLeft has some
			$lvinfo_qry_right = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER).";";
			$result_right = $db->db_query($lvinfo_qry_right);
			$lvinfo_qry_left = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$result_left = $db->db_query($lvinfo_qry_left);
			// Check if $courseRight has lvinfos
			if ($db->db_affected_rows($result_right)==0)
			{
				if ($db->db_affected_rows($result_left)>0)
				{
					while($row = $db->db_fetch_object($result_left))
					{
						$update_qry.="	INSERT INTO campus.tbl_lvinfo (lehrveranstaltung_id, sprache, titel, lehrziele, lehrinhalte, methodik, voraussetzungen, unterlagen, pruefungsordnung, anmerkung, kurzbeschreibung, genehmigt, aktiv, updateamum, updatevon, insertamum, insertvon, anwesenheit)
										SELECT ".$db->db_add_param($courseRight, FHC_INTEGER).", ".$db->db_add_param($row->sprache).", titel, lehrziele, lehrinhalte, methodik, voraussetzungen, unterlagen, pruefungsordnung, anmerkung, kurzbeschreibung, genehmigt, aktiv, NULL, NULL, now(), ".$db->db_add_param($uid).", anwesenheit
											FROM campus.tbl_lvinfo
											WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)."
											AND sprache=".$db->db_add_param($row->sprache).";";
					}
				}
			}
			$update_qry.="/*<hr>*/";
			$update_qry.="UPDATE lehre.tbl_lehreinheit SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			// If lerhfach_id was the same as the old lehrveranstaltung_id, it will be changed to
			$update_qry.="UPDATE lehre.tbl_lehreinheit SET lehrfach_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrfach_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			$update_qry.="UPDATE lehre.tbl_zeugnisnote SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			$update_qry.="UPDATE lehre.tbl_lvangebot SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			// Notenschluesselzuordnung will be copied if $courseRight has none and $courseLeft has some
			$notenschluessel_qry_right = "SELECT * FROM lehre.tbl_notenschluesselzuordnung WHERE lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER).";";
			$result_notenschluessel_right = $db->db_query($notenschluessel_qry_right);
			$notenschluessel_qry_left = "SELECT * FROM lehre.tbl_notenschluesselzuordnung WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$result_notenschluessel_left = $db->db_query($notenschluessel_qry_left);
			// Check if $courseRight has notenschluesselzuordnung
			if ($db->db_affected_rows($result_notenschluessel_right)==0)
			{
				if ($db->db_affected_rows($result_notenschluessel_left)>0)
				{
					while($row = $db->db_fetch_object($result_notenschluessel_left))
					{
						$update_qry.="	INSERT INTO lehre.tbl_notenschluesselzuordnung (notenschluessel_kurzbz, lehrveranstaltung_id, studienplan_id, oe_kurzbz, studiensemester_kurzbz)
										SELECT notenschluessel_kurzbz, ".$db->db_add_param($courseRight, FHC_INTEGER).", studienplan_id, oe_kurzbz, studiensemester_kurzbz
											FROM lehre.tbl_notenschluesselzuordnung
											WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
					}
				}
			}
			$update_qry.="/*<hr>*/";
			$update_qry.="UPDATE public.tbl_preincoming_lehrveranstaltung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="/*<hr>Addons<br>*/";

			//addon lvevaluierung
			if (in_array('lvevaluierung', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_lvevaluierung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			}

			//addon moodle
			if (in_array('moodle', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_moodle SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			}

			//addon lvinfo
			if (in_array('lvinfo', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_lvinfo SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).";";
			}
		}

		if (isset($_REQUEST['mergeDelete']))
		{
			if(!$rechte->isBerechtigt('lehre/lehrveranstaltung', NULL, 'suid'))
				die($rechte->errormsg);

			$update_qry.="UPDATE campus.tbl_benutzerlvstudiensemester SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE campus.tbl_feedback SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE campus.tbl_lehrveranstaltung_pruefung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE campus.tbl_lvgesamtnote SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE campus.tbl_lvinfo SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE campus.tbl_pruefungsanmeldung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="/*<hr>*/";
			$update_qry.="UPDATE lehre.tbl_anrechnung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_anrechnung SET lehrveranstaltung_id_kompatibel=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id_kompatibel=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lehreinheit SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lehreinheit SET lehrfach_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrfach_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lehrveranstaltung_kompatibel SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lehrveranstaltung_kompatibel SET lehrveranstaltung_id_kompatibel=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id_kompatibel=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lvangebot SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lvregel SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_lvregel SET studienplan_lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE studienplan_lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_notenschluesselzuordnung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_studienplan_lehrveranstaltung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_studienplan_lehrveranstaltung SET studienplan_lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE studienplan_lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_studienplan_lehrveranstaltung SET studienplan_lehrveranstaltung_id_parent=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE studienplan_lehrveranstaltung_id_parent=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_vertrag SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="UPDATE lehre.tbl_zeugnisnote SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="/*<hr>*/";
			$update_qry.="UPDATE public.tbl_preincoming_lehrveranstaltung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="/*<hr>Addons<br>*/";
			//addon lvevaluierung
			if (in_array('lvevaluierung', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_lvevaluierung SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			}

			//addon moodle
			if (in_array('moodle', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_moodle SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			}

			//addon lvinfo
			if (in_array('lvinfo', $activeAddons))
			{
				$update_qry.="UPDATE addon.tbl_lvinfo SET lehrveranstaltung_id=".$db->db_add_param($courseRight, FHC_INTEGER)." WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			}
			$update_qry.="/*<hr>*/";
			$update_qry.="DELETE FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($courseLeft, FHC_INTEGER).";";
			$update_qry.="/*<br></br>*/";
		}

		//$msg = "Merged successfully<br>";
		//$msg .= "<br>".mb_eregi_replace(';',';<br>',$update_qry);

		if($db->db_query($update_qry))
		{
			$msg = "<span style='color: green'><b>Merged successfully</b></span><br>";
			$msg_qry = mb_eregi_replace(';',';<br>',$update_qry);
			$msg_qry = str_replace('/*', '', $msg_qry);
			$msg_qry = str_replace('*/', '', $msg_qry);
			$msg .= "<br>".$msg_qry;
			$db->db_query("COMMIT;");

			//Log schreiben
			$log = new log();

			$log->new = true;
			$log->sql = $update_qry;
			$log->sqlundo = 'No undo statement implemented yet';
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = $uid;
			if (isset($_REQUEST['transfer']))
				$log->beschreibung = "lv_merge.php: Merge of course $courseLeft to $courseRight";
			elseif (isset($_REQUEST['mergeDelete']))
				$log->beschreibung = "lv_merge.php: Deletion of course $courseLeft. Merged with $courseRight";

			if(!$log->save())
			{
				$msg .= "<span style='color: red'><b>Error while writing log-file</b></span><br>";
			}
		}
		else
		{
			$msg = $db->errormsg;
			$msg .= "<span style='color: red'><b>An error occured while updating data. No changes were made</b></span><br>";
			$db->db_query("ROLLBACK;");
			$msg_qry = mb_eregi_replace(';',';<br>',$update_qry);
			$msg_qry = str_replace('/*', '', $msg_qry);
			$msg_qry = str_replace('*/', '', $msg_qry);
			$msg.= "<br>".$msg_qry."ROLLBACK";
		}
		//$courseLeft=0;
		//$courseRight=0;
	}
}
/*if((isset($courseLeft) && !isset($courseRight))||(!isset($courseLeft) && isset($courseRight)) || ($courseLeft<0 || $courseRight<0))
{
	$msg="Please select a radio-button from each table";
}*/
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link href="../../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript">

	$(document).ready(function()
	{
		$('#t1').tablesorter(
		{
			sortList: [[1,0]],
			widgets: ["zebra"]
		});
		$('#t2').tablesorter(
		{
			sortList: [[2,0]],
			widgets: ["zebra"]
		});
		$('#t3').tablesorter(
		{
			sortList: [[0,0]]
		});

	});
	function enableRadio(id)
	{
		if (id == 'courseLeft')
			var radios = document.getElementsByName('courseRight');
		else
			var radios = document.getElementsByName('courseLeft');
		for (var i=0, iLen=radios.length; i<iLen; i++) {
			radios[i].disabled = false;
			}
	}
	function disableRadio(id)
	{
		document.getElementById(id).disabled = true;
	}
	function disable(source_id,target_id)
	{
		if (document.getElementById(source_id).value!='')
			document.getElementById(target_id).disabled=true;
		else
			document.getElementById(target_id).disabled=false;
	}
	function copyFromTo(x,y)
	{
		document.getElementById('select_stg_kz_'+y).value=document.getElementById('select_stg_kz_'+x).value;
		document.getElementById('select_semester_'+y).value=document.getElementById('select_semester_'+x).value;
		document.getElementById('select_orgform_'+y).value=document.getElementById('select_orgform_'+x).value;
		document.getElementById('select_studienplan_'+y).value=document.getElementById('select_studienplan_'+x).value;
		document.getElementById('select_lehrtyp_'+y).value=document.getElementById('select_lehrtyp_'+x).value;
		document.getElementById('input_text_'+y).value=document.getElementById('input_text_'+x).value;
		document.getElementById('filterform').submit();
	}
	</script>

	<title>LV-Merge/Transfer</title>
</head>
<body>
<H1>Merge/Transfer courses</H1>

<?php
echo '<div contenteditable="true" style="width: 100%; height : 150px; border : 1px dotted grey; overflow-y:auto; text-align: left">'.$msg.'</div><br>';
echo '<form name="filter" id="filterform" action="lv_merge.php" method="POST">';
echo '	<div style="width: 100%">
			<div style="width: 50%; float: left">';
//////////
// FILTER LEFT SIDE
//////////

// Degree Program DropDown
$studiengang =  new studiengang();
$studiengang->getAll('typ, kurzbz');

//echo 'Degree Program <select name="select_stg_kz_left" id="select_stg_kz_left" onchange="disable(\'select_stg_kz_left\',\'input_text_left\'); document.getElementById(\'filterform\').submit()" '.($input_text_left!=''?'disabled="disabled"':'').'>';
echo 'Degree Program <select name="select_stg_kz_left" id="select_stg_kz_left" onchange="document.getElementById(\'filterform\').submit()">';
echo '<option value="" '.($select_stg_kz_left==''?'selected':'').'>-- All --</option>';
$typ = '';
$maxsemester = array();

foreach ($studiengang->result as $stg)
{
	if ($typ != $stg->typ || $typ=='')
	{
		if ($typ!='')
			echo '</optgroup>';
		echo '<optgroup label="'.$stg->typ.'">';
	}

	echo '<option value="'.$stg->studiengang_kz.'" '.($stg->studiengang_kz==$select_stg_kz_left?'selected':'').'>'.$db->convert_html_chars($stg->kurzbzlang.' - '.$stg->bezeichnung).'</option>';
	$maxsemester[$stg->studiengang_kz] = $stg->max_semester;
	$typ = $stg->typ;
}
echo '</select><br>or ';

// Input text or ID
//echo '<input type="text" id="input_text_left" name="input_text_left" value="'.$db->convert_html_chars($input_text_left).'" placeholder="Name of course (min. 3 characters) or ID (with leading #)" size="64" oninput="disable(\'input_text_left\',\'select_stg_kz_left\')" '.($select_stg_kz_left!=''?'disabled="disabled"':'').'>';
echo '<input type="text" id="input_text_left" name="input_text_left" value="'.$db->convert_html_chars($input_text_left).'" placeholder="Name of course (min. 3 characters) or ID (with leading #)" size="64">';

echo '<hr>';

//  Semester DropDown
echo 'Semester <select name="select_semester_left" id="select_semester_left" '.($select_stg_kz_left==''?'disabled="disabled"':'').'>';
echo '<option value="-1" '.($select_semester_left=='-1'?'selected':'').'>-- All --</option>';
if ($select_stg_kz_left!='')
{
	for ($i=0;$i<=$maxsemester[$select_stg_kz_left];$i++)
		echo '<option value="'.$i.'" '.($i==$select_semester_left?'selected':'').'>'.$i.'</option>';
}

echo '</select><br>';

// Orgform DropDown
echo ' Orgform <select name="select_orgform_left" id="select_orgform_left" '.($select_stg_kz_left==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_orgform_left==''?'selected':'').'>--All--</option>';
echo '<option value="none" '.($select_orgform_left=='none'?'selected':'').'>Without Orgform</option>';
if ($select_stg_kz_left!='')
{
	$orgform = new organisationsform();
	$orgform->getOrgformLV();
	$of_arr = array();
	$stp_arr = array();
	foreach ($orgform->result as $of)
		$of_arr[$of->orgform_kurzbz] = $of->bezeichnung;

	$studienplan = new studienplan();
	$studienplan->getStudienplaene($select_stg_kz_left);
	foreach ($studienplan->result as $plan)
	{
		if (!in_array($plan->orgform_kurzbz,$stp_arr))
		{
			if($select_orgform_left==$plan->orgform_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="'.$plan->orgform_kurzbz.'" '.$selected.'>'.$plan->orgform_kurzbz.' - '.$of_arr[$plan->orgform_kurzbz].'</option>';
			$stp_arr[] = $plan->orgform_kurzbz;
		}
	}
}
echo '</select><br>';

// Studienplan DropDown
echo ' Studienplan <select name="select_studienplan_left" id="select_studienplan_left" '.($select_stg_kz_left==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_studienplan_left==''?'selected':'').'>--All--</option>';
if ($select_stg_kz_left!='')
{
	$studienplan = new studienplan();
	$studienplan->getStudienplaene($select_stg_kz_left);
	foreach ($studienplan->result as $plan)
	{
		if($select_studienplan_left==$plan->studienplan_id)
			$selected = 'selected';
		else
			$selected = '';

		if ($select_orgform_left=='' || $select_orgform_left==$plan->orgform_kurzbz)
			echo '<option value="'.$plan->studienplan_id.'" '.$selected.'>'.$plan->bezeichnung.'</option>';
	}
}
echo '</select><br>';

// Type DropDown
echo ' Type <select name="select_lehrtyp_left" id="select_lehrtyp_left" '.($select_stg_kz_left==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_lehrtyp_left==''?'selected':'').'>--All--</option>';
$lehrtyp = new lehrtyp();
$lehrtyp->getAll();
foreach ($lehrtyp->result as $lt)
{
	echo '<option value="'.$lt->lehrtyp_kurzbz.'" '.($select_lehrtyp_left==$lt->lehrtyp_kurzbz?'selected':'').'>'.$lt->bezeichnung.'</option>';
}
echo '</select><br>';

echo '<input type="submit" value="OK">';
echo '<br><br>';
echo '<input type="button" value="Copy values from right form" onclick="copyFromTo(\'right\',\'left\')" '.($select_stg_kz_right==''?'disabled="disabled"':'').'>';

echo '<hr>';

echo '		</div>
			<div style="width: 50%; float: left">';
//////////
// FILTER RIGHT SIDE
//////////

// Degree Program DropDown
$studiengang =  new studiengang();
$studiengang->getAll('typ, kurzbz');

//echo 'Degree Program <select name="select_stg_kz_right" id="select_stg_kz_right" onchange="disable(\'select_stg_kz_right\',\'input_text_right\'); document.getElementById(\'filterform\').submit()" '.($input_text_right!=''?'disabled="disabled"':'').'>';
echo 'Degree Program <select name="select_stg_kz_right" id="select_stg_kz_right" onchange="document.getElementById(\'filterform\').submit()">';
echo '<option value="" '.($select_stg_kz_right==''?'selected':'').'>-- All --</option>';
$typ = '';
$maxsemester = array();
foreach ($studiengang->result as $stg)
{
	if ($typ != $stg->typ || $typ=='')
	{
		if ($typ!='')
			echo '</optgroup>';
		echo '<optgroup label="'.$stg->typ.'">';
	}

	echo '<option value="'.$stg->studiengang_kz.'" '.($stg->studiengang_kz==$select_stg_kz_right?'selected':'').'>'.$db->convert_html_chars($stg->kurzbzlang.' - '.$stg->bezeichnung).'</option>';
	$maxsemester[$stg->studiengang_kz] = $stg->max_semester;
	$typ = $stg->typ;
}
echo '</select><br>or ';

// Input text or ID
//echo '<input type="text" id="input_text_right" name="input_text_right" value="'.$db->convert_html_chars($input_text_right).'" placeholder="Name of course (min. 3 characters) or ID (with leading #)" size="64" oninput="disable(\'input_text_right\',\'select_stg_kz_right\')" '.($select_stg_kz_right!=''?'disabled="disabled"':'').'>';
echo '<input type="text" id="input_text_right" name="input_text_right" value="'.$db->convert_html_chars($input_text_right).'" placeholder="Name of course (min. 3 characters) or ID (with leading #)" size="64">';

echo '<hr>';

//  Semester DropDown
echo 'Semester <select name="select_semester_right" id="select_semester_right" '.($select_stg_kz_right==''?'disabled="disabled"':'').'>';
echo '<option value="-1" '.($select_semester_right=='-1'?'selected':'').'>-- All --</option>';
if ($select_stg_kz_left!='')
{
	for ($i=0;$i<=$maxsemester[$select_stg_kz_right];$i++)
		echo '<option value="'.$i.'" '.($i==$select_semester_right?'selected':'').'>'.$i.'</option>';
}

echo '</select><br>';

// Orgform DropDown
echo ' Orgform <select name="select_orgform_right" id="select_orgform_right" '.($select_stg_kz_right==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_orgform_right==''?'selected':'').'>--All--</option>';
echo '<option value="none" '.($select_orgform_right=='none'?'selected':'').'>Without Orgform</option>';
if ($select_stg_kz_right!='')
{
	$orgform = new organisationsform();
	$orgform->getOrgformLV();
	$of_arr = array();
	$stp_arr = array();
	foreach ($orgform->result as $of)
		$of_arr[$of->orgform_kurzbz] = $of->bezeichnung;

	$studienplan = new studienplan();
	$studienplan->getStudienplaene($select_stg_kz_right);
	foreach ($studienplan->result as $plan)
	{
		if (!in_array($plan->orgform_kurzbz,$stp_arr))
		{
			if($select_orgform_right==$plan->orgform_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="'.$plan->orgform_kurzbz.'" '.$selected.'>'.$plan->orgform_kurzbz.' - '.$of_arr[$plan->orgform_kurzbz].'</option>';
			$stp_arr[] = $plan->orgform_kurzbz;
		}
	}
}
echo '</select><br>';

// Studienplan DropDown
echo ' Studienplan <select name="select_studienplan_right" id="select_studienplan_right" '.($select_stg_kz_right==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_studienplan_right==''?'selected':'').'>--All--</option>';
if ($select_stg_kz_right!='')
{
	$studienplan = new studienplan();
	$studienplan->getStudienplaene($select_stg_kz_right);
	foreach ($studienplan->result as $plan)
	{
		if($select_studienplan_right==$plan->studienplan_id)
			$selected = 'selected';
		else
			$selected = '';

		if ($select_orgform_right=='' || $select_orgform_right==$plan->orgform_kurzbz)
			echo '<option value="'.$plan->studienplan_id.'" '.$selected.'>'.$plan->bezeichnung.'</option>';
	}
}
echo '</select><br>';

// Type DropDown
echo ' Type <select name="select_lehrtyp_right" id="select_lehrtyp_right" '.($select_stg_kz_right==''?'disabled="disabled"':'').'>';
echo '<option value="" '.($select_lehrtyp_right==''?'selected':'').'>--All--</option>';
foreach ($lehrtyp->result as $lt)
{
	echo '<option value="'.$lt->lehrtyp_kurzbz.'" '.($select_lehrtyp_right==$lt->lehrtyp_kurzbz?'selected':'').'>'.$lt->bezeichnung.'</option>';
}
echo '</select><br>';

echo '<input type="submit" value="OK">';
echo '<br><br>';
echo '<input type="submit" value="Copy values from left form" onclick="copyFromTo(\'left\',\'right\')" '.($select_stg_kz_left==''?'disabled="disabled"':'').'>';

echo '<hr>';

echo '		</div>
		</div>';
echo '</form>';

$stg_arr = new studiengang();
$stg_arr->getAll(null,false);
foreach ($stg_arr->result as $row)
	$studiengang_arr[$row->studiengang_kz] = $row->kurzbzlang;

//////////
// COURSES LEFT SIDE
//////////
echo '<form name="courses" action="lv_merge.php" method="POST" style="width: 100%; text-align: center">';
echo '<input type="hidden" name="input_text_left" value="'.$input_text_left.'">';
echo '<input type="hidden" name="select_stg_kz_left" value="'.$select_stg_kz_left.'">';
echo '<input type="hidden" name="select_semester_left" value="'.$select_semester_left.'">';
echo '<input type="hidden" name="select_orgform_left" value="'.$select_orgform_left.'">';
echo '<input type="hidden" name="select_lehrtyp_left" value="'.$select_lehrtyp_left.'">';
echo '<input type="hidden" name="select_studienplan_left" value="'.$select_studienplan_left.'">';
echo '<input type="hidden" name="input_text_right" value="'.$input_text_right.'">';
echo '<input type="hidden" name="select_stg_kz_right" value="'.$select_stg_kz_right.'">';
echo '<input type="hidden" name="select_semester_right" value="'.$select_semester_right.'">';
echo '<input type="hidden" name="select_orgform_right" value="'.$select_orgform_right.'">';
echo '<input type="hidden" name="select_lehrtyp_right" value="'.$select_lehrtyp_right.'">';
echo '<input type="hidden" name="select_studienplan_right" value="'.$select_studienplan_right.'">';

if($rechte->isBerechtigt('lehre/lehrveranstaltung', NULL, 'sui'))
{
	echo '	<input type="submit" name="transfer" value="Transfer for" style="margin: 3px 0 3px 0; background-color: #faebcc; color: #8a6d3b;" onclick="return confirm(\'Are you sure you want to transfer the these courses?\')">';

	echo '	<select name="studiensemester_kurzbz" id="studiensemester_kurzbz">';
	$studiensemester = new studiensemester();
	$studiensemester->getAll();
	foreach ($studiensemester->studiensemester as $row)
	{
		if($studiensemester_kurzbz==$row->studiensemester_kurzbz)
			$selected = 'selected';
			else
				$selected = '';
				echo '<option value="'.$db->convert_html_chars($row->studiensemester_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</option>';
	}
	echo '</select> | ';
}
if($rechte->isBerechtigt('lehre/lehrveranstaltung', NULL, 'suid'))
	echo '	<input type="submit" name="mergeDelete" value="Merge and Delete" style="width: 200px; margin: 3px 0 3px 0; background-color: #f2dede; color: #a94442;" onclick="return confirm(\'Are you sure you want to merge these courses?\nThe left course will be deleted\')"> | ';

echo '	<input type="submit" name="compare" value="Compare" style="width: 200px; margin: 3px 0 3px 0; background-color: #dff0d8; color: #3c763d;">';

echo '	<div style="width: 100%; text-align: center">
			<div style="display: inline-block; width: 49%; border-right: 1px solid black;">';


// Left table
echo 'Select course to be deleted or copied from';
echo '<table id="t1" class="tablesorter"><thead><tr>';
echo "<th>ID</th>";
echo "<th>Name</th>";
echo "<th>Type</th>";
if ($select_stg_kz_left=='')
	echo "<th title='Degree Program'>DP</th>";
echo "<th>Semester</th>";
echo "<th>Language</th>";
echo "<th>ECTS</th>";
echo "<th>LVS</th>";
echo "<th>ALVS</th>";
echo "<th>SWS</th>";
echo "<th>LVPLS</th>";
echo "<th>&nbsp;</th>";
echo "</tr></thead><tbody>";

//if ((substr($input_text_left,0,1)=='#' || ($input_text_left!='' && strlen($input_text_left)>=3)) || $select_studienplan_left!='' || $select_stg_kz_left!='')
if ((is_numeric($input_text_left) || ($input_text_left!='' && strlen($input_text_left)>=3)) || $select_studienplan_left!='' || $select_stg_kz_left!='')
{
	$qry_left = "SELECT DISTINCT * FROM lehre.tbl_lehrveranstaltung ";

	/*if ($input_text_left!='')
	{
		if (substr($input_text_left,0,1)=='#')
			$qry_left .= " WHERE lehrveranstaltung_id=".$db->db_add_param(substr($input_text_left,1), FHC_INTEGER);
		else
			$qry_left .= " WHERE lower(bezeichnung) LIKE LOWER('%".$db->db_escape($input_text_left)."%')";
	}
	else*/
	{
		if ($select_studienplan_left!='')
			$qry_left .= " JOIN lehre.tbl_studienplan_lehrveranstaltung USING (lehrveranstaltung_id)";

		$qry_left .= " WHERE 1=1";
		if ($select_stg_kz_left!='')
			$qry_left .= " AND studiengang_kz=".$db->db_add_param($select_stg_kz_left);
		if (is_numeric($input_text_left))
			$qry_left .= " AND lehrveranstaltung_id=".$db->db_add_param($input_text_left, FHC_INTEGER);
		elseif ($input_text_left!='' && strlen($input_text_left)>=3)
			$qry_left .= " AND lower(bezeichnung) LIKE LOWER('%".$db->db_escape($input_text_left)."%')";
		if ($select_studienplan_left!='')
			$qry_left .= " AND studienplan_id=".$db->db_add_param($select_studienplan_left, FHC_INTEGER);
		if ($select_semester_left!='-1')
			$qry_left .= " AND tbl_lehrveranstaltung.semester=".$db->db_add_param($select_semester_left, FHC_INTEGER);
		if ($select_orgform_left!='')
			$qry_left .= " AND orgform_kurzbz=".$db->db_add_param($select_orgform_left);
		if ($select_lehrtyp_left!='')
			$qry_left .= " AND lehrtyp_kurzbz=".$db->db_add_param($select_lehrtyp_left);
	}
	$qry_left .= " ORDER BY bezeichnung;";
	//echo $qry_left.'<br>';

	if($db->db_query($qry_left))
	{
		while($row = $db->db_fetch_object())
		{
			echo '<tr>';
			echo '<td>'.$row->lehrveranstaltung_id.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.strtoupper($row->lehrtyp_kurzbz).'</td>';
			if ($select_stg_kz_left=='')
				echo '<td>'.$studiengang_arr[$row->studiengang_kz].'</td>';
			echo '<td>'.$row->semester.'</td>';
			echo '<td>'.$row->sprache.'</td>';
			echo '<td>'.$row->ects.'</td>';
			echo '<td>'.$row->lvs.'</td>';
			echo '<td>'.$row->alvs.'</td>';
			echo '<td>'.$row->sws.'</td>';
			echo '<td>'.$row->lvps.'</td>';
			echo '<td><input type="radio" name="courseLeft" id="courseLeft_'.$row->lehrveranstaltung_id.'" value="'.$row->lehrveranstaltung_id.'" '.((isset($courseLeft) && $courseLeft==$row->lehrveranstaltung_id)?'checked':'').' onclick="enableRadio(\'courseLeft\'); disableRadio(\'courseRight_'.$row->lehrveranstaltung_id.'\')"></td>';
			echo "</tr>";
		}
	}
}
echo "</tbody></table>";

//////////
// COURSES RIGHT SIDE
//////////

echo '		</div>
			<div style="display: inline-block; width: 49%; margin-left: -5px">';

// Right table
echo 'Select course to remain or transfer to';
echo '<table id="t2" class="tablesorter"><thead><tr>';
echo "<th>&nbsp;</th>";
echo "<th>ID</th>";
echo "<th>Name</th>";
echo "<th>Type</th>";
if ($select_stg_kz_right=='')
	echo "<th title='Degree Program'>DP</th>";
echo "<th>Semester</th>";
echo "<th>Language</th>";
echo "<th>ECTS</th>";
echo "<th>LVS</th>";
echo "<th>ALVS</th>";
echo "<th>SWS</th>";
echo "<th>LVPLS</th>";
echo "</tr></thead><tbody>";

//if ((substr($input_text_right,0,1)=='#' || ($input_text_right!='' && strlen($input_text_right)>=3)) || $select_studienplan_right!='' || $select_stg_kz_right!='')
if ((is_numeric($input_text_right) || ($input_text_right!='' && strlen($input_text_right)>=3)) || $select_studienplan_right!='' || $select_stg_kz_right!='')
{
	$qry_right = "SELECT DISTINCT * FROM lehre.tbl_lehrveranstaltung ";

	/*if ($input_text_right!='')
	{
		if (substr($input_text_right,0,1)=='#')
			$qry_right .= " WHERE lehrveranstaltung_id=".$db->db_add_param(substr($input_text_right,1), FHC_INTEGER);
		else
			$qry_right .= " WHERE lower(bezeichnung) LIKE LOWER ('%".$db->db_escape($input_text_right)."%')";
	}
	else*/
	{
		if ($select_studienplan_right!='')
			$qry_right .= " JOIN lehre.tbl_studienplan_lehrveranstaltung USING (lehrveranstaltung_id)";

		$qry_right .= " WHERE 1=1";
		if ($select_stg_kz_right!='')
			$qry_right .= " AND studiengang_kz=".$db->db_add_param($select_stg_kz_right);
		if (is_numeric($input_text_right))
			$qry_right .= " AND lehrveranstaltung_id=".$db->db_add_param($input_text_right, FHC_INTEGER);
		elseif ($input_text_right!='' && strlen($input_text_right)>=3)
			$qry_right .= " AND lower(bezeichnung) LIKE LOWER('%".$db->db_escape($input_text_right)."%')";
		if ($select_studienplan_right!='')
			$qry_right .= " AND studienplan_id=".$db->db_add_param($select_studienplan_right, FHC_INTEGER);
		if ($select_semester_right!='-1')
			$qry_right .= " AND tbl_lehrveranstaltung.semester=".$db->db_add_param($select_semester_right, FHC_INTEGER);
		if ($select_orgform_right!='')
			$qry_right .= " AND orgform_kurzbz=".$db->db_add_param($select_orgform_right);
		if ($select_lehrtyp_right!='')
			$qry_right .= " AND lehrtyp_kurzbz=".$db->db_add_param($select_lehrtyp_right);
	}
	$qry_right .= " ORDER BY bezeichnung;";
	//echo $qry_right.'<br>';

	if($db->db_query($qry_right))
	{
		while($row = $db->db_fetch_object())
		{
			echo '<tr>';
			echo '<td><input type="radio" name="courseRight" id="courseRight_'.$row->lehrveranstaltung_id.'" value="'.$row->lehrveranstaltung_id.'" '.((isset($courseRight) && $courseRight==$row->lehrveranstaltung_id)?'checked':'').' onclick="enableRadio(\'courseRight\'); disableRadio(\'courseLeft_'.$row->lehrveranstaltung_id.'\')"></td>';
			echo '<td>'.$row->lehrveranstaltung_id.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.strtoupper($row->lehrtyp_kurzbz).'</td>';
			if ($select_stg_kz_right=='')
				echo '<td>'.$studiengang_arr[$row->studiengang_kz].'</td>';
			echo '<td>'.$row->semester.'</td>';
			echo '<td>'.$row->sprache.'</td>';
			echo '<td>'.$row->ects.'</td>';
			echo '<td>'.$row->lvs.'</td>';
			echo '<td>'.$row->alvs.'</td>';
			echo '<td>'.$row->sws.'</td>';
			echo '<td>'.$row->lvps.'</td>';
			echo "</tr>";
		}
	}
}
echo "</tbody></table>";
echo '		</div>
		</div>';
echo '</form>';
exit;

?>
</tr>
</table>
</body>
</html>
