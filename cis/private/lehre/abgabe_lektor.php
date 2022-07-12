<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*******************************************************************************************************
 *				abgabe_lektor
 * 		abgabe_lektor ist die Lektorenmaske des Abgabesystems
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$getuid=get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($getuid);

if (isset($_GET['user']))
{
	if ($rechte->isBerechtigt('admin',null,'suid'))
		$getuid = $_GET['user'];
}

$sprache = getSprache();

$p = new phrasen($sprache);

$htmlstr = "";

$showall=isset($_GET['showall']);

$sql_query = "SELECT
				*
			FROM
			(SELECT tbl_person.vorname, tbl_person.nachname, tbl_studiengang.typ, tbl_studiengang.kurzbz,
			tbl_projektarbeit.projekttyp_kurzbz, tbl_projekttyp.bezeichnung, tbl_projektarbeit.titel, tbl_projektarbeit.projektarbeit_id,
			tbl_projektbetreuer.betreuerart_kurzbz, tbl_benutzer.uid, tbl_student.matrikelnr, tbl_lehreinheit.studiensemester_kurzbz
			 FROM lehre.tbl_projektarbeit LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id)
			LEFT JOIN public.tbl_benutzer on(uid=student_uid)
			LEFT JOIN public.tbl_student on(public.tbl_benutzer.uid=public.tbl_student.student_uid)
			LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id)
			LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
			LEFT JOIN public.tbl_studiengang on(lehre.tbl_lehrveranstaltung.studiengang_kz=public.tbl_studiengang.studiengang_kz)
			LEFT JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
			AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer
				WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id
				AND public.tbl_benutzer.uid=".$db->db_add_param($getuid).")
			".($showall?'':' AND public.tbl_benutzer.aktiv AND lehre.tbl_projektarbeit.note IS NULL ')."
			AND (betreuerart_kurzbz='Betreuer' OR betreuerart_kurzbz='Begutachter' OR betreuerart_kurzbz='Erstbegutachter'
				OR betreuerart_kurzbz='Zweitbegutachter' OR betreuerart_kurzbz='Erstbetreuer')
			ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy
		ORDER BY nachname";

if(!$erg=$db->db_query($sql_query))
{
	$errormsg=$p->t('global/fehlerBeimLesenAusDatenbank');
}
else
{
	$htmlstr .= "<form name='multitermin' action='abgabe_lektor_multitermin.php' title='Serientermin' target='al_detail' method='POST'>";
	$htmlstr .= "<table id='t1' class='tablesorter' width='100%'>\n";
	$htmlstr .= "<thead><tr class='liste'>\n";
	$htmlstr .= "<th></th><th>".$p->t('global/uid').' / '.$p->t('global/personenkz')."</th>
				<th>".$p->t('global/mail')."</th>
				<th>".$p->t('global/vorname')."</th>
				<th>".$p->t('global/nachname')."</th>";
	$htmlstr .= "<th>".$p->t('abgabetool/typ')."</th>
				<th>".$p->t('lvplan/stg')."</th>
				<th>".$p->t('lvplan/sem')."</th>
				<th>".$p->t('abgabetool/titel')."</th>
				<th>".$p->t('abgabetool/betreuerart')."</th>";
	$htmlstr .= "</tr></thead><tbody>\n";
	$i = 0;
	while($row=$db->db_fetch_object($erg))
	{
		$htmlstr .= "   <tr>\n"; //class='liste".($i%2)."'
		$htmlstr .= "		<td><input type='checkbox' name='mc_".$row->projektarbeit_id."' ></td>";
		$htmlstr .= "       <td><a href='abgabe_lektor_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&betreuerart=".$row->betreuerart_kurzbz."' target='al_detail' title='Details anzeigen'>".$row->uid."</a> / ".$row->matrikelnr."</td>\n";
		$htmlstr .= "	    <td align= center><a href='mailto:$row->uid@".DOMAIN."?subject=Betreuung%20".$row->bezeichnung."'><img src='../../../skin/images/email.png' alt='email' title='Email an Studenten'></a></td>";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->vorname)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->nachname)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->bezeichnung)."</td>\n";
		$htmlstr .= "       <td>".strtoupper($row->typ.$row->kurzbz)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->studiensemester_kurzbz)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->titel)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->betreuerart_kurzbz)."</td>\n";
		$htmlstr .= "   </tr>\n";
		$i++;
	}
	$htmlstr .= "</tbody></table>\n";
	$htmlstr .= "<table><tr><td rowspan=3><input type='submit' name='multi' value='".$p->t('abgabetool/terminserieAnlegen')."' title='".$p->t('abgabetool/terminserieAnlegenHelp')."'></td></tr></table>\n";
	$htmlstr .= "</form>";
}

echo '
<html>
	<head>
		<title>'.$p->t('abgabetool/abgabetool').'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
		<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
		<script language="JavaScript" type="text/javascript">
		function confdel()
		{
			if(confirm("'.$p->t('global/warnungWirklichLoeschen').'"))
				return true;
			return false;
		}

		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				sortList: [[4,0]],
				widgets: ["zebra"]
			});

		});
		</script>
	</head>

<body>';

echo "<h1><div style='float: left'>".$p->t('abgabetool/ueberschrift')." ($getuid) </div><div style='text-align:right'><a href='".$p->t('dms_link/abgabetoolLektorHandbuch')."' target='_blank'><img src='../../../skin/images/information.png' alt='Anleitung' title='Anleitung Abgabetool' border=0>&nbsp;".$p->t('global/handbuch')."</a></div></h1>";

echo $htmlstr;

echo '<a href="'.$_SERVER['PHP_SELF'].'?showall">- '.$p->t('abgabetool/alleArbeitenAnzeigen').'</a><br />';
echo '<a href="abgabe_lektor_terminuebersicht.php" target="_blank">- '.$p->t('abgabetool/terminuebersichtAnzeigen').'</a>';

echo '</body>
</html>';
?>
