<?php
/* Copyright (C) 2006 fhcomplete.org
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
 *		    Stefan Puraner		< puraner@technikum-wien.at >
 *			Manfred Kindl		< manfred.kindl@technikum-wien.at >
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/lehrveranstaltung.class.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/lehrtyp.class.php');
	require_once('../../include/lehrmodus.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/studienplan.class.php');

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$reloadstr = "";  // neuladen der liste im oberen frame
	$errorstr='';
	$htmlstr='';
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
	$semester = (isset($_GET['semester'])?$_GET['semester']:'-1');

	$stg_arr = array();
	$sprache_arr = array();
	$lehrform_arr = array();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt',null,'s'))
		die('Sie haben keine Berechtigung fuer diese Seite');

	if(isset($_POST["schick"]) || isset($_POST["schick_neu"]))
	{
		if(!$rechte->isBerechtigt('lehre/lehrveranstaltung',null,'sui') && !$rechte->isBerechtigt('lehre/lehrveranstaltungAnlegen',null,'sui'))
			die('Sie haben keine Berechtigung fuer diese Aktion');

		$lv = new lehrveranstaltung();

		if(isset($_POST["schick_neu"]))
		{
			$lv->new=true;
			$lv->insertamum=date('Y-m-d H:i:s');
			$lv->insertvon = $user;
		}
		elseif(isset($_POST['lv_id']) && $_POST['lv_id']!='')
		{
			if($lv->load($_POST['lv_id']))
			{
				$lv->new=false;
			}
			else
			{
				die('Fehler beim Laden der Lehrveranstaltung');
			}
		}
		else
		{
			$lv->new=true;
			$lv->insertamum=date('Y-m-d H:i:s');
			$lv->insertvon = $user;
		}

		$lv->kurzbz = $_POST['kurzbz'];
		$lv->bezeichnung = $_POST['bezeichnung'];
		$lv->bezeichnung_english = $_POST['bezeichnung_english'];
		$lv->lehrform_kurzbz = $_POST['lehrform'];
		$lv->studiengang_kz = $_POST['studiengang_kz'];
		$lv->semester = $_POST['semester'];
		$lv->sprache = $_POST['sprache'];
		$lv->ects  = mb_eregi_replace(',','.',$_POST['ects']);
		$lv->semesterstunden = $_POST['semesterstunden'];
		$lv->anmerkung = $_POST['anmerkung'];
		$lv->lehre = isset($_POST['lehre']);
		$lv->lehreverzeichnis = $_POST['lehreverzeichnis'];
		$lv->aktiv = isset($_POST['aktiv']);
		//$lv->planfaktor = $_POST['planfaktor'];
		$lv->planlektoren = $_POST['planlektoren'];
		$lv->planpersonalkosten = $_POST['planpersonalkosten'];
		$lv->plankostenprolektor = $_POST['plankostenprolektor'];
		$lv->updateamum = date('Y-m-d H:i:s');
		$lv->updatevon = $user;
		$lv->sort = $_POST['sort'];
		$lv->incoming = $_POST['incoming'];
		$lv->zeugnis = isset($_POST['zeugnis']);
		$lv->projektarbeit = isset($_POST['projektarbeit']);
		$lv->orgform_kurzbz = $_POST['orgform_kurzbz'];
		$lv->lehrtyp_kurzbz = $_POST['lehrtyp_kurzbz'];
		$lv->lehrmodus_kurzbz = $_POST['lehrmodus_kurzbz'];
		$lv->oe_kurzbz = $_POST['oe_kurzbz'];
		$lv->raumtyp_kurzbz = $_POST['raumtyp_kurzbz'];
		$lv->anzahlsemester = $_POST['anzahlsemester'];
		$lv->semesterwochen = $_POST['semesterwochen'];
		$lv->lvnr = $_POST['lvnr'];
		$lv->semester_alternativ = $_POST['semester_alternativ'];
		$lv->farbe = $_POST['farbe'];
		$lv->sws = mb_eregi_replace(',','.',$_POST['sws']);
		$lv->lvs = $_POST['lvs'];
		$lv->alvs = $_POST['alvs'];
		$lv->lvps = $_POST['lvps'];
		$lv->las = $_POST['las'];
		$lv->benotung = isset($_POST['benotung']);
		$lv->lvinfo = isset($_POST['lvinfo']);
		$lv->lehrauftrag = isset($_POST['lehrauftrag']);
		$lv->lehrveranstaltung_template_id = $lv->lehrtyp_kurzbz == 'tpl' ? '' : $_POST['lehrveranstaltung_template_id'];

		if(!$lv->save())
			$errorstr = "Fehler beim Speichern der Daten: $lv->errormsg";
		else
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht.location.href='lehrveranstaltung.php?stg_kz=$lv->studiengang_kz&semester=$lv->semester&isaktiv='+parent.uebersicht.isaktiv;";
			if($lv->lehreverzeichnisExists($lv->lehreverzeichnis, $lv->studiengang_kz, $lv->semester) && ($lv->new === true))
			{
			    $reloadstr .= " window.location.href='".$_SERVER['PHP_SELF']."?stg_kz=$lv->studiengang_kz&semester=$lv->semester&neu=true&lehrevzExists=true&update=false';";
			}
			else
			{
			    $reloadstr .= " window.location.href='".$_SERVER['PHP_SELF']."?stg_kz=$lv->studiengang_kz&semester=$lv->semester&neu=true';";
			}
			$reloadstr .= "</script>\n";
		}
	}

	$sg = new studiengang();
	$sg->getAll('typ, kurzbz', false);
	foreach($sg->result as $studiengang)
	{
		$stg_arr[$studiengang->studiengang_kz] = $studiengang->kuerzel;
	}

	$qry = "SELECT * FROM public.tbl_sprache ORDER BY sprache";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$sprache_arr[] = $row->sprache;
		}
	}

	$qry = "SELECT * FROM lehre.tbl_lehrform ORDER BY lehrform_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$lehrform_arr[] = $row->lehrform_kurzbz;
		}
	}

	$qry = "SELECT * FROM lehre.tbl_lehrtyp ORDER BY lehrtyp_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$lehrtyp_arr[] = $row->lehrtyp_kurzbz;
		}
	}


	if (isset($_REQUEST['lv_id']) || isset($_REQUEST['neu']))
	{
		//wenn ein Fehler beim Speichern auftritt, dann sollen die alten Daten nochmals
		//angezeigt werden.
		if(!isset($_POST['schick']) && !isset($_POST["schick_neu"]))
		{
			$lv = new lehrveranstaltung();

			if (isset($_REQUEST['lv_id']))
			{
				$lvid = $_REQUEST['lv_id'];
				if (!$lv->load($lvid))
					$htmlstr .= '<br><div class="kopf">Lehrveranstaltung <b>'.$lvid.'</b> existiert nicht</div>';
			}
		}
		if(isset($_REQUEST['lehrevzExists']) && ($_REQUEST['lehrevzExists'] === "true") && isset($_REQUEST['update']) && ($_REQUEST['update'] === "false"))
		{
		    $htmlstr .= '<br/><br/><span>Hinweis: Lehreverzeichnis existiert bereits.</span>';
		}
		$htmlstr .= '
		<br><div class="kopf">Lehrveranstaltung '.$lv->lehrveranstaltung_id.'</div>';

		$htmlstr.='
		<form action="lehrveranstaltung_details.php" method="POST">
		<input type="hidden" name="lv_id" value="'.$lv->lehrveranstaltung_id.'">

		<table class="detail" style="padding-top:10px;">
		<tr></tr>

		<tr>
			<td>Kurzbz*</td>
			<td><input type="text" name="kurzbz" '.($lv->lehrveranstaltung_id==''?'onchange="copyToLehreVz();"':'onchange="return copyToLehreVzAsk();"').' value="'.$lv->kurzbz.'" /></td>
			<td>Bezeichnung*</td>
			<td colspan=3><input type="text" name="bezeichnung" value="'.htmlentities($lv->bezeichnung, ENT_QUOTES, 'UTF-8').'" size="60" maxlength="128"></td>
		</tr>
		<tr>
			<td>Sprache</td>
			<td><select name="sprache">';

		foreach ($sprache_arr as $sprache)
		{
			if ($lv->sprache == $sprache)
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$sprache.'" '.$sel.'>'.$sprache.'</option>';
		}
		$htmlstr .= '</select></td>
			<td>Bezeichnung English</td>
			<td colspan=3><input type="text" name="bezeichnung_english" value="'.htmlentities($lv->bezeichnung_english, ENT_QUOTES, 'UTF-8').'" size="60" maxlength="256"></td>
		</tr><tr>
			<td>Studiengang</td>
			<td><select name="studiengang_kz">';

		foreach ($stg_arr as $stg_key=>$stg_kurzbz)
		{
			if (($stg_kz!='-1' && $stg_kz==$stg_key) || ($lv->studiengang_kz!='' && $lv->studiengang_kz == $stg_key ))
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$stg_key.'" '.$sel.'>'.$stg_kurzbz.'</option>';
		}

		$htmlstr .= '</select></td>
			<td>Semester</td>
			<td><select name="semester">';

		for ($i = 0; $i < 10; $i++)
		{
			if (($semester!='-1' && $semester==$i) || $lv->semester == $i)
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
		}

		$htmlstr .= '</select></td>
			<td>Lehrform*</td>
			<td><select name="lehrform"><option value="">-- keine Auswahl --</option>';

		foreach ($lehrform_arr as $lehrform)
		{
			if ($lv->lehrform_kurzbz == $lehrform)
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$lehrform.'" '.$sel.'>'.$lehrform.'</option>';
		}
		$htmlstr .= '
			</select></td>
		</tr><tr>
			<td>ECTS</td>
			<td><input type="text" name="ects" value="'.$lv->ects.'" maxlength="5"></td>
			<td>Semesterstunden</td>
			<td><input type="text" name="semesterstunden" value="'.$lv->semesterstunden.'" maxlength="3"></td>
			<td>Lehrtyp*</td>
			<td><select name="lehrtyp_kurzbz"><option value="">-- keine Auswahl --</option>';

		$lehrtyp_arr=new lehrtyp();
		$lehrtyp_arr->getAll();
		foreach ($lehrtyp_arr->result as $lehrtyp)
		{
			if ($lv->lehrtyp_kurzbz == $lehrtyp->lehrtyp_kurzbz)
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$lehrtyp->lehrtyp_kurzbz.'" '.$sel.'>'.$lehrtyp->bezeichnung.'</option>';
		}
		$htmlstr .= '</select></td>
		</tr>';

		$htmlstr .= '
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>Lehrmodus*</td>';

		$htmlstr .= '<td><select name="lehrmodus_kurzbz"><option value="">-- keine Auswahl --</option>';

		$lehrmodus_arr = new lehrmodus();
		$lehrmodus_arr->getAll();
		$sprache = getSprache();
		foreach ($lehrmodus_arr->result as $lehrmodus)
		{
			if ($lehrmodus->lehrmodus_kurzbz == $lv->lehrmodus_kurzbz)
				$sel = ' selected';
			else if (isset($_GET['neu']) && defined('DEFAULT_LEHRMODUS') && ($lehrmodus->lehrmodus_kurzbz == DEFAULT_LEHRMODUS) && ($lv->lehrmodus_kurzbz == ''))
				$sel = ' selected';
			else
				$sel = '';
			$htmlstr .= '<option value="'.$lehrmodus->lehrmodus_kurzbz.'" '.$sel.'>'.$lehrmodus->bezeichnung_mehrsprachig[$sprache].'</option>';
		}

		$htmlstr .= '<tr>
			<td>Sort</td>
			<td><input type="text" name="sort" value="'.$lv->sort.'" maxlength="2"></td>
			<td>Lehreverzeichnis*</td>
			<td><input type="text" onchange="checkInput(this);" name="lehreverzeichnis" value="'.$lv->lehreverzeichnis.'" maxlength="16"></td>
			<td>Anmerkung</td>
			<td><input type="text" name="anmerkung" value="'.$lv->anmerkung.'" maxlength="64"></td>
		</tr>';

		$htmlstr .= '<tr>
			<td>Planlektoren</td>
			<td><input type="text" name="planlektoren" value="'.$lv->planlektoren.'" maxlength="2"></td>
			<td>Planpersonalkosten</td>
			<td><input type="text" name="planpersonalkosten" value="'.$lv->planpersonalkosten.'" maxlength="7"></td>
			<td>Plankostenprolektor</td>
			<td><input type="text" name="plankostenprolektor" value="'.$lv->plankostenprolektor.'" maxlength="6"></td>
		</tr>';

		$htmlstr .= '<tr>
			<td>Lehre</td>
			<td><input type="checkbox" name="lehre" '.($lv->lehre?'checked':'').'></td>
			<td>Aktiv</td>
			<td><input type="checkbox" name="aktiv" '.($lv->aktiv?'checked':'').'></td>
			<td>Zeugnis</td>
			<td><input type="checkbox" name="zeugnis" '.($lv->zeugnis?'checked':'').'></td>
		</tr>';

		$htmlstr .= '<tr>
			<td>Projektarbeit</td>
			<td><input type="checkbox" name="projektarbeit" '.($lv->projektarbeit?'checked':'').'></td>
			<td>Organisationsform</td>
			<td>
			<SELECT name="orgform_kurzbz" '.($lv->lehrveranstaltung_id==''?'onchange="copyToLehreVz();"':'onchange="return copyToLehreVzAsk();"').'><OPTION value="">-- keine Auswahl --</OPTION>';

		$qry_orgform = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz NOT IN ('VBB', 'ZGS') ORDER BY orgform_kurzbz";
		if($result_orgform = $db->db_query($qry_orgform))
		{
			while($row_orgform = $db->db_fetch_object($result_orgform))
			{
				if($row_orgform->orgform_kurzbz==$lv->orgform_kurzbz)
					$selected='selected';
				else
					$selected='';

				$htmlstr .= '<OPTION value="'.$row_orgform->orgform_kurzbz.'" '.$selected.'>'.$row_orgform->bezeichnung.'</OPTION>';
			}
		}
		$htmlstr .= '</SELECT></td>
			<td>Incomingpl&auml;tze</td>
			<td><input type="text" name="incoming" size="2" value="'.$lv->incoming.'" maxlength="2"></td>
		</tr><tr>
			<td>LVNR</td>
			<td><input type="text" name="lvnr" value="'.$lv->lvnr.'" /></td>
			<td>Organisationseinheit</td>
			<td colspan="3"><SELECT name="oe_kurzbz" ><option value="">--keine Auswahl --</option>';

		$qry = "SELECT * FROM public.tbl_organisationseinheit ORDER BY organisationseinheittyp_kurzbz, oe_kurzbz";
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->oe_kurzbz==$lv->oe_kurzbz)
					$selected='selected';
				else
					$selected='';

				if($row->aktiv=='f')
				{
					$htmlstr .= '<option value="'.$row->oe_kurzbz.'" '.$selected.' style="color: red;">'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</option>';
				}
				else
				{
					$htmlstr .= '<option value="'.$row->oe_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</option>';
				}
			}
		}
		$htmlstr .= '</select></td>
		</tr><tr>
			<td>Semester alternativ</td>
			<td><input type="text" size="3" name="semester_alternativ" value="'.$lv->semester_alternativ.'" /></td>
			<td>Raumtyp</td>
			<td><select name="raumtyp_kurzbz"><option value="">-- keine Auswahl--</option>';
		$qry = "SELECT * FROM public.tbl_raumtyp ORDER BY raumtyp_kurzbz";
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->raumtyp_kurzbz==$lv->raumtyp_kurzbz)
					$selected='selected';
				else
					$selected='';
				$htmlstr .= '<option value="'.$row->raumtyp_kurzbz.'" '.$selected.'>'.$row->raumtyp_kurzbz.'</option>';
			}
		}//#'.$lv->farbe.'
		$htmlstr .= '</select></td>
			<td>Semesterwochen</td>
			<td><input type="text" name="semesterwochen" size="2" value="'.$lv->semesterwochen.'" /></td>
		</tr>
		<tr>
			<td>Farbe</td>
			<td><input id="farbe" type="text" name="farbe" size="6" value="'.$lv->farbe.'" onchange="document.getElementById(\'farbevorschau\').style.backgroundColor=this.value"/>&nbsp<span id="farbevorschau" style="background-color: #'.$lv->farbe.'; border: 1px solid #999999; cursor: default;" >&nbsp&nbsp&nbsp&nbsp</span></td>
			<td>Anzahl Semester</td>
			<td><input type="text" name="anzahlsemester" size="2" value="'.$lv->anzahlsemester.'" /></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Semesterwochenstunden (SWS)</td>
			<td><input id="sws" type="text" name="sws" size="3" value="'.$lv->sws.'"></td>
			<td>Lehrveranstaltungsstunden (LVS)</td>
			<td><input id="lvs" type="text" name="lvs" size="3" value="'.$lv->lvs.'"></td>
			<td>Angebotene Lehrveranstaltungsstunden (ALVS)</td>
			<td><input id="alvs" type="text" name="alvs" size="3" value="'.$lv->alvs.'"></td>
		</tr>
		<tr>
			<td>LV-Plan Stunden Summe (LVPS)</td>
			<td><input id="lvps" type="text" name="lvps" size="3" value="'.$lv->lvps.'"></td>
			<td>Lehrauftragsstunden Summe (LAS)</td>
			<td><input id="las" type="text" name="las" size="3" value="'.$lv->las.'"></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Benotung</td>
			<td><input type="checkbox" name="benotung" '.($lv->benotung?'checked':'').'></td>
			<td>LVInfo</td>
			<td><input type="checkbox" name="lvinfo" '.($lv->lvinfo?'checked':'').'></td>
			<td>Lehrauftrag</td>
			<td><input type="checkbox" name="lehrauftrag" '.($lv->lehrauftrag?'checked':'').'></td>
		</tr>
		<tr id="lehrveranstaltung_template_id">
			<td>Template</td>
			<td colspan="2"><input type="text" name="lehrveranstaltung_template_id" value="'.$lv->lehrveranstaltung_template_id.'" size="6"> <span class="text-template_name"></span></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>';
			if ($lv->lehrveranstaltung_id=='')
				$htmlstr .= '<td colspan="2" align="right"><input type="submit" value="Speichern" name="schick" style="cursor: pointer;"></td>';
			else
				$htmlstr .= '<td colspan="2" align="right"><input type="submit" value="Als neue LV speichern" name="schick_neu" style="font-size: smaller; cursor: pointer;">&nbsp;&nbsp;<input type="submit" value="Speichern" name="schick" style="cursor: pointer;"></td>';
		$htmlstr .= '<td></td>
		</tr>
		</table>
		</form>';


		// Details
			$htmlstr.='<span style="font-size:small">';
			$htmlstr.='<br>
			<b>Anlage</b>: '.$lv->insertamum.' '.$lv->insertvon.' <b>/ Letzte Aenderung:</b> '.$lv->updateamum.' '.$lv->updatevon.'<br>
			<b>Lehraufträge zu dieser LV</b>: ';
			$qry ="SELECT distinct studiensemester_kurzbz, tbl_studiensemester.start
					FROM
						lehre.tbl_lehreinheit
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE lehrveranstaltung_id=".$db->db_add_param($lv->lehrveranstaltung_id).'
					ORDER BY tbl_studiensemester.start desc';
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					$htmlstr.= $row->studiensemester_kurzbz.'; ';
				}
			}
			$htmlstr.='<br><b>Noten zu dieser LV</b>: ';
			$qry ="SELECT distinct studiensemester_kurzbz, tbl_studiensemester.start
					FROM
						lehre.tbl_zeugnisnote
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE lehrveranstaltung_id=".$db->db_add_param($lv->lehrveranstaltung_id).'
					ORDER BY tbl_studiensemester.start desc';
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					$htmlstr.= $row->studiensemester_kurzbz.'; ';
				}
			}

			$htmlstr.='<br><b>Verwendung als Lehrfach</b>: ';
			$qry ="SELECT distinct studiensemester_kurzbz, tbl_studiensemester.start
					FROM
						lehre.tbl_lehreinheit
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE lehrfach_id=".$db->db_add_param($lv->lehrveranstaltung_id).'
					ORDER BY tbl_studiensemester.start desc';
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					$htmlstr.= $row->studiensemester_kurzbz.'; ';
				}
			}

			$htmlstr.='<br><b>Verwendung in folgenden Studienplänen</b>: ';
			$stdplan = new studienplan();
			if ($stdplan->getStudienplanLehrveranstaltung($lv->lehrveranstaltung_id))
			foreach($stdplan->result as $result)
				$htmlstr .= $result->bezeichnung . "; ";

			$htmlstr.='</span>';
			// Details Ende
	}
	$htmlstr .= '
		<div class="inserterror">'.$errorstr.'</div>';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Lehrveranstaltung - Details</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/colorpicker.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
	<script type="text/javascript" src="../../include/js/mailcheck.js"></script>
	<script type="text/javascript" src="../../include/js/datecheck.js"></script>

	<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>

	<script type="text/javascript" src="../../include/js/colorpicker.js"></script>
	<script>
	    function copyToLehreVz()
	    {
		var kurzbz = $('input[name="kurzbz"]').val();
		kurzbz = kurzbz.replace(/\ä/g, "ae")
			.replace(/\ö/g, "oe")
			.replace(/\ü/g, "ue")
			.replace(/\ß/g, "sz")
			.replace(/\Ä/g, "ae")
			.replace(/\Ö/g, "oe")
			.replace(/\Ü/g, "ue")
			.replace(/[^a-z_\s]/gi, "");
		var orgform = ($('select[name="orgform_kurzbz"]').val() === "") ? "" : "_"+$('select[name="orgform_kurzbz"]').val();
		var string = (kurzbz+orgform).toLowerCase();;
		$("input[name=\'lehreverzeichnis\']").val(string);
	    }
	    function copyToLehreVzAsk()
	    {
	    var check = confirm("Lehreverzeichnis automatisch anpassen?");
		    if (check == true)
		    {
			var kurzbz = $('input[name="kurzbz"]').val();
			kurzbz = kurzbz.replace(/\ä/g, "ae")
				.replace(/\ö/g, "oe")
				.replace(/\ü/g, "ue")
				.replace(/\ß/g, "sz")
				.replace(/\Ä/g, "ae")
				.replace(/\Ö/g, "oe")
				.replace(/\Ü/g, "ue")
				.replace(/[^a-z_\s]/gi, "");
			var orgform = ($('select[name="orgform_kurzbz"]').val() === "") ? "" : "_"+$('select[name="orgform_kurzbz"]').val();
			var string = (kurzbz+orgform).toLowerCase();;
			$("input[name=\'lehreverzeichnis\']").val(string);
		    }
		    else
		    {
			return false;
		    }
	    }
	    function checkInput(ele)
	    {
		var string = ele.value;
		string.split("_");
		string = string.replace(/\ä/g, "ae")
			.replace(/\ö/g, "oe")
			.replace(/\ü/g, "ue")
			.replace(/\ß/g, "sz")
			.replace(/\Ä/g, "ae")
			.replace(/\Ö/g, "oe")
			.replace(/\Ü/g, "ue")
			.replace(/[^a-z_0-9\s]/gi, "");
		ele.value = string;
	    }
	    function getFarbe()
	    {
			document.getElementById("farbe").value
	    }
	    $(document).ready(function()
	    {
			if($("form").size() !== 0)
			{
			    $("form").submit(function(e){
					$(".missingFormData").each(function(i,v){
					   $(v).removeClass("missingFormData");
					});
					var self = this;
					//e.preventDefault();
					var error = false;
					if($('input[name="kurzbz"]').val() === "")
					{
					    error = true;
					    $('input[name="kurzbz"]').addClass("missingFormData");
					}
					if($('input[name="bezeichnung"]').val() === "")
					{
					    error = true;
					    $('input[name="bezeichnung"]').addClass("missingFormData");
					}
					if($('select[name="lehrform"]').val() === "")
					{
					    error = true;
					    $('select[name="lehrform"]').addClass("missingFormData");
					}
					if($('select[name="lehrtyp_kurzbz"]').val() === "")
					{
					    error = true;
					    $('select[name="lehrtyp_kurzbz"]').addClass("missingFormData");
					}
					if($('select[name="lehrmodus_kurzbz"]').val() === "")
					{
						error = true;
						$('select[name="lehrmodus_kurzbz"]').addClass("missingFormData");
					}
					if($('input[name="lehreverzeichnis"]').val() === "")
					{
						error = true;
						$('input[name="lehreverzeichnis"]').addClass("missingFormData");
					}
					if($('input[name="lehreverzeichnis"]').val() === "")
					{
					    error = true;
					    $('input[name="lehreverzeichnis"]').addClass("missingFormData");
					}
					if(error)
						return false;
					if(!error)
					{
						$("form").submit();
					}
				    });
			}

			$("#farbe").ColorPicker(
			{
				onSubmit: function(hsb, hex, rgb, el)
				{
					$(el).val(hex);
					$(el).ColorPickerHide();
					document.getElementById("farbevorschau").style.backgroundColor=hex;
				},
				onBeforeShow: function ()
				{
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind("keyup", function()
			{
				$(this).ColorPickerSetColor(this.value);
			});

			$('input[name="lehrveranstaltung_template_id"]').autocomplete({
				minLength: 2,
				source: function(request, response) {
					$.ajax({
						dataType: "json",
						url: "../../soap/fhcomplete.php",
						data: {
							typ: "json",
							class: "lehrveranstaltung",
							method: "loadTemplates",
							parameter_0: request.term
						}
					}).then(data => {
						for (var k in data.return) {
							data.return[k].value = data.return[k].lehrveranstaltung_id;
							data.return[k].name = data.return[k].bezeichnung + " [" + data.return[k].kurzbz + "]";
							data.return[k].label = data.return[k].name + " (" + data.return[k].lehrveranstaltung_id + ")";
						}
						response(data.return);
					});
				},
				close: function(e, ui) {
					$(this).trigger('check.ac-template');
				},
				focus: function(e, ui) {
					$(this).removeClass('input_ok input_error').val(ui.item.value).parent().parent().find('.text-template_name').text(ui.item.name);
					return false;
				},
				select: function(e, ui) {
					$(this).addClass('input_ok').val(ui.item.value).parent().parent().find('.text-template_name').text(ui.item.name);
					return false;
				}
			}).focus(function(e) {
				$(this).removeClass('input_ok input_error');
			}).blur(function(e) {
				$(this).trigger('check.ac-template');
			}).on('check.ac-template', function(e) {
				var self = $(this),
					val = self.val();
				if (!val) {
					self.removeClass('input_ok input_error').parent().parent().find('.text-template_name').text('');
				} else {
					$.ajax({
						dataType: "json",
						url: "../../soap/fhcomplete.php",
						data: {
							typ: "json",
							class: "lehrveranstaltung",
							method: "loadTemplates",
							parameter_0: val
						},
						success: function(data) {
							if (self.val() == val) {
								var label = '',
									state = '',
									item = null;

								for (var k in data.return) {
									if (data.return[k].lehrveranstaltung_id == val) {
										item = data.return[k];
										break;
									}
								}

								if (item) {
									state = 'input_ok';
									label = item.bezeichnung + " [" + item.kurzbz + "]";
								} else if (val) {
									state = 'input_error';
								}
								self.removeClass('input_ok input_error').addClass(state).parent().parent().find('.text-template_name').text(label);
							}
						}
					});
				}
			}).trigger('check.ac-template');

			$('select[name="lehrtyp_kurzbz"]').change(function(e) {
				if ($(this).val() == 'tpl') {
					$('#lehrveranstaltung_template_id').hide();
				} else {
					$('#lehrveranstaltung_template_id').show();
				}
			}).change();
	    });
	</script>
	<style type="text/css">
	    .missingFormData {
		border: 2px solid red;
		outline: 2px solid red;
	    }
	</style>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	//echo $reloadstr;
?>

</body>
</html>
