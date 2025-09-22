<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once '../../include/person.class.php';
require_once '../../include/functions.inc.php';
require_once '../../include/phrasen.class.php';
require_once '../../include/preincoming.class.php';
require_once '../../include/studiensemester.class.php';
require_once '../../include/studiengang.class.php';
require_once '../../include/lehrveranstaltung.class.php';
require_once '../../include/studiengang.class.php';
require_once '../../include/benutzerberechtigung.class.php';

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$sprache = getSprache();
$p=new phrasen($sprache);

$method = htmlspecialchars($_GET['method']);

$db = new basis_db();

$stsem = new studiensemester();
$stsem->getNextStudiensemester();

$stg = new studiengang();
$stg->getAll();

$message = '';

$filter_url = '';
if (isset($_GET['filter']) || isset($_GET['unterrichtssprache']) || isset($_GET['studiengang']))
	$filter_url = 'filter='.$_GET['filter'].'&unterrichtssprache='.$_GET['unterrichtssprache'].'&studiengang='.$_GET['studiengang'].'&go=Filter&';

?>
<html>
	<head>
	<title>Lehrveranstaltungs-Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="expires" content="Sat, 01 Dec 2001 00:00:00 GMT">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				sortList: [[1,0],[3,0],[4,0],[5,0]],
				widgets: ["zebra"],
				headers: {10: {sorter: false}, 11: {sorter: false}}
			});
			$("#t2").tablesorter(
			{
				sortList: [[0,0],[1,0]],
				widgets: ["zebra"]
			});
		});
		function conf(val1)
		{
			return confirm("Incomingplätze der LV '"+val1+"' auf 0 setzen?");
		}
	</script>
	</head>
	<body>
<?php

if(!$rechte->isBerechtigt('inout/incoming', null, 'suid'))
	die($rechte->errormsg);

if($method=="lehrveranstaltungen")
{

	if(isset($_GET['mode']) && $_GET['mode'] == "setZero")
	{
		$id= $_GET['id'];
		$lehrveranstaltung = new lehrveranstaltung();
		$lehrveranstaltung->load($id);

		$lehrveranstaltung->incoming = 0;

		if($lehrveranstaltung->save())
			$message = $p->t('global/erfolgreichgespeichert');
		else
			$message = $p->t('global/fehleraufgetreten');
	}

	// Übersicht aller LVs
	echo '<h2>Lehrveranstaltungs-Verwaltung</h2>';
	echo '

		<form name="filterSemester" action="'.$_SERVER['PHP_SELF'].'" method="GET">
		<table width="90%" border="0" align="center">
			<tr>
				<td>'.$p->t('incoming/studentenImWS').'</td>
			</tr>
			<tr>
				<td>'.$p->t('incoming/studentenImSS').'</td>
			</tr>
			<tr>
				<td>'.$p->t('incoming/filter').':
					<SELECT name="filter">
					<option value="allSemester">'.$p->t('incoming/alleSemester').'</option>';

					// Vorauswahl der Übergebenen Filter
					$WSemesterSelected = '';
					$SSemesterSelected = '';

					if(isset($_GET['filter']))
						if($_GET['filter'] == 'WSemester')
							$WSemesterSelected ='selected';
						elseif($_GET['filter']=='SSemester')
							$SSemesterSelected='selected';

					echo '<option value="WSemester" '.$WSemesterSelected.'>'.$p->t('incoming/wintersemester').'</option>';
					echo '<option value="SSemester" '.$SSemesterSelected.'>'.$p->t('incoming/sommersemester').'</option>';

			echo'</SELECT><br>';
			echo $p->t('courseInformation/unterrichtssprache').':<SELECT name="unterrichtssprache">
			<option value="">'.$p->t('incoming/alleSprachen').'</option>';

					// Vorauswahl der Übergebenen Filter
					$GermanSelected = '';
					$EnglishSelected = '';

					if(isset($_GET['unterrichtssprache']))
						if($_GET['unterrichtssprache'] == 'German')
							$GermanSelected ='selected';
						elseif($_GET['unterrichtssprache']=='English')
							$EnglishSelected='selected';

					echo '<option value="German" '.$GermanSelected.'>'.$p->t("global/deutsch").'</option>';
					echo '<option value="English" '.$EnglishSelected.'>'.$p->t("global/englisch").'</option>';

			echo'</SELECT><br>';
			echo $p->t('global/studiengang').':<SELECT name="studiengang">
			<option value="">'.$p->t('incoming/alleStudiengaenge').'</option>';

			// Vorauswahl der Übergebenen Filter

			$studiengang = new studiengang();
			$studiengang->getAll('typ,kurzbz', true);
			$type = array('b' => 'Bachelor', 'm' => 'Master', 'e' => 'Other');
			$typ = '';

			foreach ($studiengang->result as $row)
			{
				//Nur Bachelor, Master und CIR-Studiengang
				if ($row->typ == 'b' || $row->typ == 'm' || $row->studiengang_kz == '10006')
				{
					$selected = '';

					if ($typ != $row->typ || $typ=='')
					{
						if ($typ!='')
							echo '</optgroup>';
							echo '<optgroup label="'.$type[$row->typ].'">';
					}

					if(isset($_GET['studiengang']) && $_GET['studiengang'] == $row->studiengang_kz)
						$selected='selected';

					$studiengang_language = ($sprache == 'German') ? $row->bezeichnung : $row->english;
					echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.strtoupper($row->typ.$row->kurzbz).' - '.$studiengang_language.'</option>';
					$typ = $row->typ;
				}
			}

			echo'</SELECT><br><br>';
			echo '<input type="hidden" name="method" value="lehrveranstaltungen">';
			//echo '<input type="hidden" >';
			echo '<input type="submit" name="go" value="Filter">';
			echo '</td>
			</tr>
		</table>
		</form>';

		// Filter für Semester setzen
		$filterqry = '';

		if(isset($_GET['filter']))
			if($_GET['filter'] == "WSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (1,3,5)";
			elseif($_GET['filter'] == "SSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (2,4,6)";

		if(isset($_GET['unterrichtssprache']) && $_GET['unterrichtssprache']!='')
			$filterqry .= " AND tbl_lehrveranstaltung.sprache=".$db->db_add_param($_GET['unterrichtssprache']);

		//Uebersicht LVs
		/* Erklaerung der Datumszeitraeume ab Zeile 857:
		 *			|=============== Studiensemester ===============|
		 *		|--------------| 											Incoming beginnt vor SS-Beginn und endet VOR SS-Ende jedoch ueberwiegend innerhalb SS
		 *												|--------------| 	Incoming beginnt VOR SS-Ende und endet NACH SS-Ende, jedoch ueberwiegend innerhalb SS
		 *	|----------| 													Incoming beginnt vor SS-Beginn und endet VOR SS-Ende jedoch ueberwiegend außerhalb SS
		 *														|---------|	Incoming beginnt VOR SS-Ende und endet NACH SS-Ende, jedoch ueberwiegend außerhalb SS
		 * 					|------------------------------| 				Incoming ist innerhalb oder GENAU SS da
		 *		|------------------------------------------------------|	Incoming ist VOR SS-Anfang und NACH SS-Ende da, jedoch ueberwiegend ueberlappend mit SS
		 *	------------------------------------------------------------	Von und Bis ist NULL
		 *	-------------------|											Von ist NULL und bis innerhalb SS
		 *									|---------------------------	Bis ist NULL und von innerhalb SS
		 */

		$studiensemester_array = array();
		$studiensemester = new studiensemester();
		$studiensemester_array[] = $studiensemester->getakt();

		$studiensemester->getFutureStudiensemester('',2);
		foreach ($studiensemester->studiensemester AS $row)
			$studiensemester_array[] = $row->studiensemester_kurzbz;

		if(isset($_GET['go']))
		{
			// QUERY liefert LVs aus den gültigen Studienordnungen UND jene mit Anmeldungen, auch wenn Incomingplätze 0 sind oder die LV in keinem gültigen Studienplan liegt
			$qry = "SELECT
						tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.ects,
						tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache,
						tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming, tbl_lehrveranstaltung.orgform_kurzbz,
						(
						Select count(*)
						FROM (
							SELECT
								person_id
							FROM
								campus.vw_student_lehrveranstaltung
								JOIN public.tbl_benutzer using(uid)
								JOIN public.tbl_student ON(uid=student_uid)
								JOIN public.tbl_prestudentstatus USING(prestudent_id)
							WHERE
								lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
								AND
								lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
							WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
								AND
								tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz).")
								AND tbl_prestudentstatus.status_kurzbz='Incoming'
								AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz)."
							UNION
							SELECT
								person_id
							FROM
								public.tbl_preincoming_lehrveranstaltung
							JOIN public.tbl_preincoming using(preincoming_id)
							WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
							AND
							(
								(bis - ".$db->db_add_param($stsem->start)." > ".$db->db_add_param($stsem->start)." - von) OR
								(".$db->db_add_param($stsem->start)." <= von AND bis >= ".$db->db_add_param($stsem->ende)." AND ".$db->db_add_param($stsem->ende)." - von > bis - ".$db->db_add_param($stsem->ende).") OR
								(bis <= ".$db->db_add_param($stsem->ende)." AND bis >= ".$db->db_add_param($stsem->start)." AND von < ".$db->db_add_param($stsem->start).") OR
								(".$db->db_add_param($stsem->start)." <= von AND von < ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->ende).") OR
								(von >= ".$db->db_add_param($stsem->start)." AND bis <= ".$db->db_add_param($stsem->ende).") OR
								(von <= ".$db->db_add_param($stsem->start)." AND bis >= ".$db->db_add_param($stsem->ende).") OR
								(von IS NULL AND bis IS NULL) OR
								(von IS NULL AND bis <= ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->start).") OR
								(bis IS NULL AND von < ".$db->db_add_param($stsem->ende)." AND von >= ".$db->db_add_param($stsem->start).")
							)
							AND aktiv = true
							)a ) as anzahl
						FROM
							lehre.tbl_lehrveranstaltung
						JOIN
							public.tbl_studiengang USING(studiengang_kz)
						WHERE
							tbl_lehrveranstaltung.incoming>0 AND
							tbl_lehrveranstaltung.aktiv AND
							tbl_lehrveranstaltung.lehre AND
							tbl_lehrveranstaltung.lehrveranstaltung_id IN
							(
								SELECT lehrveranstaltung_id FROM lehre.tbl_studienplan_lehrveranstaltung
								JOIN lehre.tbl_studienplan USING (studienplan_id)
								JOIN lehre.tbl_studienordnung USING (studienordnung_id)
								JOIN lehre.tbl_studienplan_semester USING (studienplan_id)
								WHERE tbl_studienordnung.status_kurzbz='approved'
								AND tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id
								AND tbl_studienplan_semester.studiensemester_kurzbz IN (".$db->db_implode4SQL($studiensemester_array).")
								AND tbl_studienplan_semester.semester=tbl_lehrveranstaltung.semester
							)
							AND ((tbl_lehrveranstaltung.studiengang_kz>0 AND tbl_lehrveranstaltung.studiengang_kz<10000) OR tbl_lehrveranstaltung.studiengang_kz=10006)";

						if (isset($_GET['studiengang']) && $_GET['studiengang'] !='')
							$qry .= " AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($_GET['studiengang'], FHC_INTEGER);

							$qry .= " AND tbl_studiengang.aktiv ".$filterqry;

						$qry .= "
						UNION

						SELECT
						tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.ects,
						tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache,
						tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming, tbl_lehrveranstaltung.orgform_kurzbz,
						(
						Select count(*)
						FROM (
							SELECT
								person_id
							FROM
								campus.vw_student_lehrveranstaltung
							JOIN public.tbl_benutzer using(uid)
							JOIN public.tbl_student ON(uid=student_uid)
							JOIN public.tbl_prestudentstatus USING(prestudent_id)
							WHERE
								lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
								AND
								lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
							WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
								AND
								tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz).")
								AND
								tbl_prestudentstatus.status_kurzbz='Incoming'
								AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz)."
							UNION
							SELECT
								person_id
							FROM
								public.tbl_preincoming_lehrveranstaltung
							JOIN public.tbl_preincoming using(preincoming_id)
							WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
							AND
							(
								(bis - ".$db->db_add_param($stsem->start)." > ".$db->db_add_param($stsem->start)." - von) OR
								(".$db->db_add_param($stsem->start)." <= von AND bis >= ".$db->db_add_param($stsem->ende)." AND ".$db->db_add_param($stsem->ende)." - von > bis - ".$db->db_add_param($stsem->ende).") OR
								(bis <= ".$db->db_add_param($stsem->ende)." AND bis >= ".$db->db_add_param($stsem->start)." AND von < ".$db->db_add_param($stsem->start).") OR
								(".$db->db_add_param($stsem->start)." <= von AND von < ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->ende).") OR
								(von >= ".$db->db_add_param($stsem->start)." AND bis <= ".$db->db_add_param($stsem->ende).") OR
								(von <= ".$db->db_add_param($stsem->start)." AND bis >= ".$db->db_add_param($stsem->ende).") OR
								(von IS NULL AND bis IS NULL) OR
								(von IS NULL AND bis <= ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->start).") OR
								(bis IS NULL AND von < ".$db->db_add_param($stsem->ende)." AND von >= ".$db->db_add_param($stsem->start).")
							)
							AND aktiv = true
							)a ) as anzahl
						FROM
							public.tbl_preincoming_lehrveranstaltung
						JOIN public.tbl_preincoming using(preincoming_id)
						JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
						WHERE
						(
							(bis - ".$db->db_add_param($stsem->start)." > ".$db->db_add_param($stsem->start)." - von) OR
							(".$db->db_add_param($stsem->start)." <= von AND bis >= ".$db->db_add_param($stsem->ende)." AND ".$db->db_add_param($stsem->ende)." - von > bis - ".$db->db_add_param($stsem->ende).") OR
							(bis <= ".$db->db_add_param($stsem->ende)." AND bis >= ".$db->db_add_param($stsem->start)." AND von < ".$db->db_add_param($stsem->start).") OR
							(".$db->db_add_param($stsem->start)." <= von AND von < ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->ende).") OR
							(von >= ".$db->db_add_param($stsem->start)." AND bis <= ".$db->db_add_param($stsem->ende).") OR
							(von <= ".$db->db_add_param($stsem->start)." AND bis >= ".$db->db_add_param($stsem->ende).") OR
							(von IS NULL AND bis IS NULL) OR
							(von IS NULL AND bis <= ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->start).") OR
							(bis IS NULL AND von < ".$db->db_add_param($stsem->ende)." AND von >= ".$db->db_add_param($stsem->start).")
						)
						AND tbl_preincoming.aktiv = true
							";

			if (isset($_GET['studiengang']) && $_GET['studiengang'] !='')
				$qry .= " AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($_GET['studiengang'], FHC_INTEGER);

			$qry .= " AND tbl_studiengang.aktiv ".$filterqry." order by studiengang_kz";

			if($result = $db->db_query($qry))
			{
				if ($db->db_num_rows($result)>0)
				{
					echo '<center>'.$message.'</center>';
					echo '<p style="padding-left: 10px; padding-right: 10px;">'.$p->t('incoming/tabelleSortierinformation').'</p>';
					echo '<table class="tablesorter" id="t1" width="90%" style="padding-left: 10px; padding-right: 10px;" border="0" align="center">
						<thead align="center">
						<tr>
							<th>LV-ID</th>
							<th>'.$p->t('global/studiengang').'</th>
							<th>'.$p->t('abgabetool/typ').'</th>
							<th>'.$p->t('incoming/orgform').'</th>
							<th>'.$p->t('global/semester').'</th>
							<th>'.$p->t('global/lehrveranstaltung').'</th>
							<th>'.$p->t('global/lehrveranstaltung').' '.$p->t('global/englisch').'</th>
							<th>'.$p->t('incoming/ects').'</th>
							<th>'.$p->t('courseInformation/unterrichtssprache').'</th>
							<th>'.$p->t('incoming/lvInfo').'</th>
							<th>'.$p->t('incoming/freieplätze').'</th>
							<th></th>
							<th></th>
						</tr>
						</thead>
						<tbody>';
					while($row = $db->db_fetch_object($result))
					{
						$freieplaetze = $row->incoming - $row->anzahl;
						$style = '';

						$studiengang = new studiengang();
						$studiengang->load($row->studiengang_kz);
						$studiengang_language = ($sprache == 'German') ? $studiengang->bezeichnung : $studiengang->english;
						$typ = $studiengang->typ;
						if ($studiengang->typ == 'b')
							$typ = 'Bachelor';
						else if ($studiengang->typ == 'm')
							$typ = 'Master';
						else
							$typ = '-';
						echo '<tr>';

						if ($freieplaetze<=0)
							$style = 'style="background-color: #FF8888"';

						echo '<td '.$style.'>',$row->lehrveranstaltung_id,'</td>';
						echo '<td '.$style.'>',$studiengang_language,'</td>';
						echo '<td '.$style.'>',$typ,'</td>';
						echo '<td '.$style.'>',$row->orgform_kurzbz,'</td>';
						echo '<td '.$style.'>',$row->semester,'</td>';
						echo '<td '.$style.'>',$row->bezeichnung,'</td>';
						echo '<td '.$style.'>',$row->bezeichnung_english,'</td>';
						echo '<td '.$style.'>',$row->ects,'</td>';
						echo '<td '.$style.'>',($row->sprache=='German'?$p->t("global/deutsch"):$p->t("global/englisch")),'</td>';
						echo '<td '.$style.'>
								<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'../../addons/lvinfo/cis/view.php?lehrveranstaltung_id='.$row->lehrveranstaltung_id.'&amp;sprache=German\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">'.$p->t("global/deutsch").'&nbsp;</a>
								<a href="#Englisch" class="Item" onclick="javascript:window.open(\'../../addons/lvinfo/cis/view.php?lehrveranstaltung_id='.$row->lehrveranstaltung_id.'&amp;sprache=English\',\'Courseinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">'.$p->t("global/englisch").'</a>
							  </td>';
						echo '<td '.$style.'>',($freieplaetze<$row->incoming?'<strong>'.$freieplaetze.' ('.$p->t('incoming/von').' '.$row->incoming.')</strong>':$freieplaetze.' ('.$p->t('incoming/von').' '.$row->incoming.')').'</td>';
						echo '<td '.$style.'><a href="#Teilnehmer" class="Item" onclick="javascript:window.open(\'incoming_lehrveranstaltungen.php?method=anmeldungen&amp;id='.$row->lehrveranstaltung_id.'&amp;'.$filter_url.'\',\'Anmeldungen\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Anmeldungen</a></td>';
						echo '<td '.$style.'><a href="incoming_lehrveranstaltungen.php?method=lehrveranstaltungen&mode=setZero&id='.$row->lehrveranstaltung_id.'&'.$filter_url.'" onclick="return conf(\''.$row->bezeichnung.'\')">Incomingplätze auf 0</a></td>';
						echo '</tr>';

					}
					echo '</tbody></table>';
				}
				else
					echo '<center><b>'.$p->t('incoming/derzeitKeineLehrveranstaltungen').'</b></center>';
			}
		}
		else
			echo '<center><b>'.$p->t('incoming/waehlenSieAusDenOptionen').'</b></center>';
		echo '</tbody></table>';
}
elseif($method=="anmeldungen")
{
	// Übersicht aller LVs
	echo '<h2>Übersicht Anmeldungen</h2>';

	// Filter für Semester setzen


	//Uebersicht LVs
	/* Erklaerung der Datumszeitraeume
	*			|=============== Studiensemester ===============|
	*		|--------------| 											Incoming beginnt vor SS-Beginn und endet VOR SS-Ende jedoch ueberwiegend innerhalb SS
	*												|--------------| 	Incoming beginnt VOR SS-Ende und endet NACH SS-Ende, jedoch ueberwiegend innerhalb SS
	*	|----------| 													Incoming beginnt vor SS-Beginn und endet VOR SS-Ende jedoch ueberwiegend außerhalb SS
	*														|---------|	Incoming beginnt VOR SS-Ende und endet NACH SS-Ende, jedoch ueberwiegend außerhalb SS
	* 					|------------------------------| 				Incoming ist innerhalb oder GENAU SS da
	*		|------------------------------------------------------|	Incoming ist VOR SS-Anfang und NACH SS-Ende da, jedoch ueberwiegend ueberlappend mit SS
	*	------------------------------------------------------------	Von und Bis ist NULL
	*	-------------------|											Von ist NULL und bis innerhalb SS
	*									|---------------------------	Bis ist NULL und von innerhalb SS
	*/
	if (isset($_GET['id']))
	{
		$id = $db->db_add_param($_GET['id'], FHC_INTEGER, false);
		$qry = "	SELECT
						nachname, vorname
					FROM
						campus.vw_student_lehrveranstaltung
					JOIN public.tbl_benutzer using(uid)
					JOIN public.tbl_student ON(uid=student_uid)
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_person USING(person_id)
					WHERE
						lehrveranstaltung_id=".$id."
						AND
						lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
					WHERE lehrveranstaltung_id=".$id."
						AND
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz).")
						AND
						tbl_prestudentstatus.status_kurzbz='Incoming'
						AND tbl_prestudentstatus.studiensemester_kurzbz=".$db->db_add_param($stsem->studiensemester_kurzbz)."
					UNION
					SELECT
						nachname, vorname
					FROM
						public.tbl_preincoming_lehrveranstaltung
					JOIN public.tbl_preincoming using(preincoming_id)
					JOIN public.tbl_person USING(person_id)
					WHERE lehrveranstaltung_id=".$id."
					AND
					(
						(bis - ".$db->db_add_param($stsem->start)." > ".$db->db_add_param($stsem->start)." - von) OR
						(".$db->db_add_param($stsem->start)." <= von AND bis >= ".$db->db_add_param($stsem->ende)." AND ".$db->db_add_param($stsem->ende)." - von > bis - ".$db->db_add_param($stsem->ende).") OR
						(bis <= ".$db->db_add_param($stsem->ende)." AND bis >= ".$db->db_add_param($stsem->start)." AND von < ".$db->db_add_param($stsem->start).") OR
						(".$db->db_add_param($stsem->start)." <= von AND von < ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->ende).") OR
						(von >= ".$db->db_add_param($stsem->start)." AND bis <= ".$db->db_add_param($stsem->ende).") OR
						(von <= ".$db->db_add_param($stsem->start)." AND bis >= ".$db->db_add_param($stsem->ende).") OR
						(von IS NULL AND bis IS NULL) OR
						(von IS NULL AND bis <= ".$db->db_add_param($stsem->ende)." AND bis > ".$db->db_add_param($stsem->start).") OR
						(bis IS NULL AND von < ".$db->db_add_param($stsem->ende)." AND von >= ".$db->db_add_param($stsem->start).")
					)
					AND tbl_preincoming.aktiv = true";


		if($result = $db->db_query($qry))
		{
			if ($db->db_num_rows($result)>0)
			{
				echo '<table class="tablesorter" id="t2" width="90%" style="padding-left: 10px; padding-right: 10px;" border="0" align="center">
				<thead align="center">
				<tr>
					<th>'.$p->t('global/nachname').'</th>
					<th>'.$p->t('global/vorname').'</th>
				</tr>
				</thead>
				<tbody>';
				while($row = $db->db_fetch_object($result))
				{

					echo '<tr>';
					echo '<td>',$row->nachname,'</td>';
					echo '<td>',$row->vorname,'</td>';
					echo '</tr>';

				}
				echo '</tbody></table>';
			}
			else
				echo '<center><b>Keine Anmeldungen gefunden</b></center>';
		}
	}
	else
		'<center><b>Es wurde keine Lehrveranstaltungs-ID übergeben</b></center>';
}
?>
	</body>
</html>
