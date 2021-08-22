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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Erstellt eine Liste mit dem Lehrveranstaltungen und Betreuungen denen der Lektor zugeteilt ist
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/lvangebot.class.php');
require_once('../../../include/addon.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/vertrag.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$adress = MAIL_ADMIN;

$user=get_uid();
$studiensemester = new studiensemester();

if (isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = $user;

if (isset($_GET['stdsem']))
	$stdsem = $_GET['stdsem'];
else
	$stdsem = $studiensemester->getaktorNext();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if (!$rechte->isBerechtigt('admin') && $uid != $user)
	die('Sie haben keine Berechtigung für diesen Vorgang');

$datum = new datum();

$addon = new addon();
if (in_array('lvinfo',$addon->aktive_addons))
	$lvinfo=true;
else
	$lvinfo=false;

//Studiensemester abfragen. Letzten 5, aktuelles und naechstes.
$sql_query = '
	SELECT
		*
	FROM
		public.tbl_studiensemester
	WHERE (start<=(now()::date+240) AND ende>=(now()::date-900))
	ORDER BY start';
$result_stdsem = $db->db_query($sql_query);
$num_rows_stdsem = $db->db_num_rows($result_stdsem);

$p = new phrasen(getSprache());
/*
0000453: Sortierung von LVs - Meine LV
1. Bachelor
2. Name des Bachelors
3. Studienjahr
4. Name der LV
5. Master
6. Name des Masters
7. Studienjahr
8. Name der LV

*/
//Lehrveranstaltungen abfragen.
$sql_query = "
	SELECT
		*, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg_kurzbz,
		tbl_lehrveranstaltung.semester as lv_semester,
		lehrfach.kurzbz as lehrfach,
		lehrfach.bezeichnung as lehrfach_bez,
		tbl_lehreinheitmitarbeiter.semesterstunden as semesterstunden,
		tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung,
		tbl_lehreinheit.anmerkung as le_anmerkung,
		tbl_lehreinheit.lehrform_kurzbz as le_lehrform_kurzbz,
		(SELECT kurzbz FROM public.tbl_mitarbeiter
		 WHERE mitarbeiter_uid=tbl_lehreinheitmitarbeiter.mitarbeiter_uid) as lektor,
		tbl_lehrveranstaltung.lehrveranstaltung_id
	FROM
		lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
		JOIN public.tbl_studiengang USING(studiengang_kz)
		JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
	WHERE
		tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($stdsem)."
		AND mitarbeiter_uid = ".$db->db_add_param($uid)."
	ORDER BY stg_kurzbz,lv_semester,lv_bezeichnung";

$result = $db->db_query($sql_query);
$num_rows = $db->db_num_rows($result);

echo '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>'.$p->t('lvaliste/titel').'</title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script language="Javascript">
	<!--
	function printhelp()
	{
		alert("'.$p->t('lvaliste/hilfeText').'");
	}
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[5,0],[6,0],[3,0]],
			widgets: ["zebra"]
		});
		$("#t2").tablesorter(
		{
			sortList: [[0,0],[1,0],[3,0]],
			widgets: ["zebra"]
		});
		$("#t3").tablesorter(
		{
			sortList: [[0,0],[1,0],[3,0]],
			widgets: ["zebra"]
		});
	});
	-->
	</script>
</head>
<body id="inhalt">
<H1>'.$p->t('lvaliste/titel').' ( '.$stdsem.' )</H1>';
echo '<table width="100%"><tr><td>';
for ($i = 0;$i < $num_rows_stdsem;$i++)
{
	$row = $db->db_fetch_object($result_stdsem);
	if ($stdsem == $row->studiensemester_kurzbz)
		echo '<strong><A class="Item" style="text-decoration: underline;" href="lva_liste.php?uid='.$uid.'&stdsem='.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</A></strong> - ';
	else
		echo '<A class="Item" href="lva_liste.php?uid='.$uid.'&stdsem='.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</A> - ';
}
echo '</td><td align="right">';
echo '<a href="#" onclick="printhelp()" class="Item">'.$p->t('lvaliste/hilfeAnzeigen').'</a>';
echo '</td></tr></table><br>';
if ($num_rows > 0)
{
	$anzahl_lvs = 0;
	$lvtable = '
	<table class="tablesorter" id="t1">
		<thead>
		<tr>';
	if (!defined('CIS_LVALISTE_NOTENEINGABE_ANZEIGEN') || CIS_LVALISTE_NOTENEINGABE_ANZEIGEN)
		$lvtable .= '<th>'.$p->t('lvaliste/gesamtnote').'</th>';

	if ($lvinfo)
		$lvtable .= '<th>'.$p->t('lvaliste/lvinfo').'</th>';

	$lvtable .= '
			<th>'.$p->t('lvaliste/id').'</th>
			<th>'.$p->t('lvaliste/lehrfach').'</th>
			<th>'.$p->t('lvaliste/lehrform').'</th>
			<th>'.$p->t('lvaliste/lvBezeichnung').'</th>
			<th>'.$p->t('lvaliste/lektor').'</th>
			<th>'.$p->t('lvaliste/studiengang').'</th>
			<th>'.$p->t('lvaliste/semester').'</th>
			<th>'.$p->t('lvaliste/gruppen').'</th>
			<th>'.$p->t('lvaliste/raumtyp').'</th>
			<th>'.$p->t('lvaliste/raumtypalternativ').'</th>
			<th>'.$p->t('lvaliste/blockung').'</th>
			<th>'.$p->t('lvaliste/wochenrythmus').'</th>
			<th>'.$p->t('lvaliste/stunden').'</th>
			<th>'.$p->t('lvaliste/kalenderwoche').'</th>
			<th>Anm. von</th>
			<th>Anm. bis</th>
		</tr>
	</thead><tbody>';
	$stg_obj = new studiengang();
	$stg_obj->getAll(null,null);
	$summe_std = 0;

	for ($i = 0; $i < $num_rows; $i++)
	{
		$row = $db->db_fetch_object($result);

		// Nur erteilte Vertraege anzeigen wenn dies im Config hinterlegt ist.
		if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
		 && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
		{
			$vertrag = new vertrag();
			if (!$vertrag->isVertragErteiltLE($row->lehreinheit_id, $stdsem, $uid))
			{
				continue;
			}
		}
		$anzahl_lvs++;

		$lvangebot = new lvangebot();
		$lvtable .= '<tr>';
		if (!defined('CIS_LVALISTE_NOTENEINGABE_ANZEIGEN') || CIS_LVALISTE_NOTENEINGABE_ANZEIGEN)
			$lvtable .= '<td nowrap><a href="../lehre/benotungstool/lvgesamtnoteverwalten.php?lvid='.$row->lehrveranstaltung_id.'&stsem='.$stdsem.'">'.$p->t('lvaliste/gesamtnote').'</a></td>';

		if ($lvinfo)
			$lvtable .= '<td><a href="../../../addons/lvinfo/cis/lvinfo.php?lv_id='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stdsem.'" target="_blank">'.$p->t('lvaliste/lvinfo').'</a></td>';

		$lvtable .= '<td>'.$row->lehreinheit_id.'</td>';
		$lvtable .= '<td>'.$row->lehrfach.'</td>';
		$lvtable .= '<td>'.$row->le_lehrform_kurzbz.'</td>';
		if ($row->lehrfach_bez != $row->lv_bezeichnung)
			$lvtable .= '<td><a href="../lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stdsem.'">'.$row->lv_bezeichnung.' ('.$p->t('lvaliste/lehrfach').': '.$row->lehrfach_bez.')</a></td>';
		else
			$lvtable .= '<td><a href="../lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stdsem.'">'.$row->lv_bezeichnung.'</a></td>';
		$lvtable .= '<td>'.$row->lektor.'</td>';
		$lvtable .= '<td><a href="mailto:'.$row->email.'">'.$row->stg_kurzbz.'</a></td>';
		$lvtable .= '<td>'.$row->semester.'</td>';

		$qry = "
			SELECT * FROM lehre.tbl_lehreinheitgruppe
			WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id);

		$gruppe = '';
		if ($result_grp = $db->db_query($qry))
		{
			while ($row_grp = $db->db_fetch_object($result_grp))
			{
				if ($row_grp->gruppe_kurzbz != '')
					$gruppe .= $row_grp->gruppe_kurzbz.'<br>';
				else
					$gruppe .= $stg_obj->kuerzel_arr[$row->studiengang_kz].'-'.$row_grp->semester.$row_grp->verband.$row_grp->gruppe.'<br>';
			}
		}
		$lvtable .= '<td>'.$gruppe.'</td>';
		$lvtable .= '<td>'.$row->raumtyp.'</td>';
		$lvtable .= '<td>'.$row->raumtypalternativ.'</td>';
		$lvtable .= '<td>'.$row->stundenblockung.'</td>';
		$lvtable .= '<td>'.$row->wochenrythmus.'</td>';
		if (getSprache() == 'German')
		{
			$lvtable .= '<td>'.number_format($row->semesterstunden,2,$dec_point=",",$thousands_sep=".").'</td>';
		}
		else
		{
			$lvtable .= '<td>'.number_format($row->semesterstunden,2,$dec_point=".",$thousands_sep=",").'</td>';
		}
		$lvtable .= '<td>'.$row->start_kw.'</td>';

		$lvangebot->getAllFromLvId($row->lehrveranstaltung_id, $row->studiensemester_kurzbz);
		if (!empty($lvangebot->result))
		{
			$lvtable .= '<td>'.$datum->formatDatum($lvangebot->result[0]->anmeldefenster_start, "d.m.Y").'</td>';
			$lvtable .= '<td>'.$datum->formatDatum($lvangebot->result[0]->anmeldefenster_ende, "d.m.Y").'</td>';
		}
		else
		{
			$lvtable .= '<td>&nbsp;</td>
			<td>&nbsp;</td>';
		}

		$lvtable .= '</tr>';
		$summe_std += $row->semesterstunden;
	}
	$lvtable .= '</tbody>';
	$lvtable .= '<tfoot>';
	$lvtable .= '<tr>';
	if (!defined('CIS_LVALISTE_NOTENEINGABE_ANZEIGEN') || CIS_LVALISTE_NOTENEINGABE_ANZEIGEN)
		$lvtable .= '<td>&nbsp;</td>';
	if ($lvinfo)
		$lvtable .= '<td>&nbsp;</td>';

	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '<td align="right"><b>'.$p->t('lvaliste/summe').'</b></td>';
	if (getSprache() == 'German')
	{
		$lvtable .= '<th>'.number_format($summe_std, 2, $dec_point = ",", $thousands_sep = ".").'</th>';
	}
	else
	{
		$lvtable .= '<th>'.number_format($summe_std, 2, $dec_point = ".", $thousands_sep = ",").'</th>';
	}
	$lvtable .= '<td>&nbsp;</td>';
	$lvtable .= '</tr>';
	$lvtable .= '</tfoot>';
	$lvtable .= '</table>';

	if($anzahl_lvs > 0)
	{
		echo '<h3>'.$p->t('lvaliste/lehrveranstaltungen').'</h3>';
		echo $p->t('lvaliste/anzahl').': '.$anzahl_lvs;
		echo $lvtable;
	}
	else
	{
		echo $p->t('lvaliste/keineDatensaetze').'<BR>';
	}
}
else
	echo $p->t('lvaliste/keineDatensaetze').'<BR>';

//Betreuungen
$mitarbeiter = new benutzer();
$mitarbeiter->load($uid);

$qry = "SELECT
			tbl_lehrveranstaltung.bezeichnung, tbl_projektarbeit.titel,
			(SELECT nachname || ' ' || vorname FROM public.tbl_benutzer JOIN public.tbl_person USING(person_id)
			 WHERE uid=student_uid) as student, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester,
			 tbl_studiengang.email, tbl_betreuerart.beschreibung AS beutreuerart_beschreibung, tbl_projektbetreuer.stunden
		FROM
			lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektarbeit, lehre.tbl_projektbetreuer, public.tbl_studiengang, lehre.tbl_betreuerart
		WHERE
			tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
			tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
			tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stdsem)." AND
			tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
			tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz AND
			tbl_projektbetreuer.betreuerart_kurzbz=tbl_betreuerart.betreuerart_kurzbz AND
			tbl_projektbetreuer.person_id=".$db->db_add_param($mitarbeiter->person_id, FHC_INTEGER);

$stg_obj = new studiengang();
$stg_obj->getAll(null,null);
$summe_std = 0;

if ($result = $db->db_query($qry))
{
	if ($db->db_num_rows($result) > 0)
	{
		echo '<br>';
		echo '<H3>'.$p->t('lvaliste/betreuungen').'</H3>';
		echo $p->t('lvaliste/anzahl').': '.$db->db_num_rows($result);
		echo '<table class="tablesorter" id="t2">';
		echo '<thead><tr>';
		echo '<th>'.$p->t('lvaliste/studiengang').'</th>';
		echo '<th>'.$p->t('lvaliste/semester').'</th>';
		echo '<th>'.$p->t('lvaliste/stunden').'</th>';
		echo '<th>'.$p->t('lvaliste/lvBezeichnung').'</th>';
		echo '<th>'.$p->t('lvaliste/student').'</th>';
		echo '<th>'.$p->t('lvaliste/betreuungsart').'</th>';
		echo '<th>'.$p->t('lvaliste/titelProjektarbeit').'</th>';
		echo '</tr></thead><tbody>';
		while ($row = $db->db_fetch_object($result))
		{
			echo '<tr>';
			echo '<td><a href="mailto:'.$row->email.'">'.$stg_obj->kuerzel_arr[$row->studiengang_kz].'</a></td>';
			echo '<td>'.$row->semester.'</td>';
			if (getSprache() == 'German')
			{
				echo '<td>'.number_format($row->stunden,2,$dec_point =",", $thousands_sep ="."). '</td>';
			}
			else
			{
				echo '<td>'.number_format($row->stunden,2,$dec_point =".", $thousands_sep =","). '</td>';
			}
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.$row->student.'</td>';
			echo '<td>'.$row->beutreuerart_beschreibung.'</td>';
			echo '<td>'.$row->titel.'</td>';

			$summe_std += $row->stunden;
		}
		echo '</tbody>';
		echo '<tfoot>';
		echo '<tr>';
		if (!defined('CIS_LVALISTE_NOTENEINGABE_ANZEIGEN') || CIS_LVALISTE_NOTENEINGABE_ANZEIGEN)
		{
			echo '<td>&nbsp;</td>';
		}
		if ($lvinfo)

			echo '<td align="right"><b>'.$p->t('lvaliste/summe').'</b></td>';

		if (getSprache() == 'German')
		{
			echo '<th>'.number_format($summe_std, 2, $dec_point = ",", $thousands_sep = ".").'</th>';
		}
		else
		{
			echo '<th>'.number_format($summe_std, 2, $dec_point = ".", $thousands_sep = ",").'</th>';
		}

		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '</tbody></table>';
	}
}

//Koordination
$qry = "SELECT
			distinct
			tbl_lehrveranstaltung.studiengang_kz, tbl_fachbereich.fachbereich_kurzbz, tbl_lehrveranstaltung.bezeichnung,
			tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.semester,tbl_lehrveranstaltung.koordinator,
			tbl_studiengang.email
		FROM
			lehre.tbl_lehrveranstaltung,
			lehre.tbl_lehreinheit,
			lehre.tbl_lehrveranstaltung as lehrfach,
			public.tbl_studiengang,
			public.tbl_fachbereich
		WHERE
			tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
			tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz AND
			tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stdsem)." AND
			(
				tbl_lehrveranstaltung.koordinator=".$db->db_add_param($uid)."
				OR
				( tbl_lehrveranstaltung.koordinator is null
			 	AND (tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz) IN (
					SELECT studiengang_kz, fachbereich_kurzbz
					FROM public.tbl_benutzerfunktion JOIN public.tbl_studiengang USING(oe_kurzbz)
					WHERE funktion_kurzbz='fbk' AND uid=".$db->db_add_param($uid)."
  					AND (
						tbl_benutzerfunktion.datum_bis is null
						OR now() between tbl_benutzerfunktion.datum_von and tbl_benutzerfunktion.datum_bis )
						)
				)
			 )
			 AND tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz
			 ORDER BY
			 	tbl_lehrveranstaltung.studiengang_kz,
				tbl_lehrveranstaltung.semester,
				tbl_lehrveranstaltung.bezeichnung
			 ";


if ($result = $db->db_query($qry))
{
	if ($db->db_num_rows($result) > 0)
	{
		echo '<H3>'.$p->t('lvaliste/koordination').'</H3>';
		echo $p->t('lvaliste/anzahl').': '.$db->db_num_rows($result);
		echo '<table class="tablesorter" id="t3">';
		echo '<thead><tr>';
		echo '<th>'.$p->t('lvaliste/studiengang').'</th>';
		echo '<th>'.$p->t('lvaliste/semester').'</th>';
		echo '<th>'.$p->t('lvaliste/institut').'</th>';
		echo '<th>'.$p->t('lvaliste/lvBezeichnung').'</th>';
		echo '<th>'.$p->t('lvaliste/lektor').'</th>';
		echo '</tr></thead><tbody>';
		while ($row = $db->db_fetch_object($result))
		{
			//Fachbereichskoordinatoren holen
			$qry = "SELECT distinct
						uid,titelpre, titelpost, vorname, nachname
					FROM
						lehre.tbl_lehreinheitmitarbeiter,
						public.tbl_benutzer,
						public.tbl_person,
						lehre.tbl_lehreinheit
					WHERE
						tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
						tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($row->lehrveranstaltung_id, FHC_INTEGER)." AND
						tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
						tbl_benutzer.person_id=tbl_person.person_id AND
						tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stdsem);
			$lektoren='';
			if($result_lkt = $db->db_query($qry))
			{
				while($row_lkt = $db->db_fetch_object($result_lkt))
				{
					if($lektoren!='')
						$lektoren.=',';
					$lektoren.=trim($row_lkt->titelpre.' '.$row_lkt->vorname.' '.$row_lkt->nachname.' '.$row_lkt->titelpost);
				}
			}

			echo '<tr>';
				echo '<td><a href="mailto:'.$row->email.'">'.$stg_obj->kuerzel_arr[$row->studiengang_kz].'</a></td>';
				echo '<td>'.$row->semester.'</td>';
				echo '<td>'.$row->stunden.'</td>';
				echo '<td>'.$row->fachbereich_kurzbz.'</td>';
				echo '<td>'.$row->bezeichnung.'</td>';
				echo '<td>'.$lektoren.'</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
echo '<BR>'.$p->t('lvaliste/fehlerAnStudiengang').'<BR><BR><BR>';
?>
</body>
</html>
