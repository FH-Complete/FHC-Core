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

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');
	
$getuid=get_uid();

$sprache = getSprache();

$p = new phrasen($sprache);

$htmlstr = "";

$showall=isset($_GET['showall']);
	
$sql_query = "SELECT * FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) * FROM lehre.tbl_projektarbeit LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id) 
			LEFT JOIN public.tbl_benutzer on(uid=student_uid)
			LEFT JOIN public.tbl_student on(public.tbl_benutzer.uid=public.tbl_student.student_uid)
			LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id) 
			LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) 
			LEFT JOIN public.tbl_studiengang on(lehre.tbl_lehrveranstaltung.studiengang_kz=public.tbl_studiengang.studiengang_kz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
			AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer 
				WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id 
				AND public.tbl_benutzer.uid='".addslashes($getuid)."')
			AND public.tbl_benutzer.aktiv ".($showall?'':' AND lehre.tbl_projektarbeit.note IS NULL ')."
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
	$htmlstr .= "<table id='t1' class='liste table-autosort:4 table-stripeclass:alternate table-autostripe'>\n";
	$htmlstr .= "<thead><tr class='liste'>\n";
	$htmlstr .= "<th></th><th class='table-sortable:default'>".$p->t('global/uid').' / '.$p->t('global/personenkz')."</th>
				<th>".$p->t('global/mail')."</th>
				<th class='table-sortable:default'>".$p->t('global/vorname')."</th>
				<th class='table-sortable:alphanumeric'>".$p->t('global/nachname')."</th>";
	$htmlstr .= "<th class='table-sortable:alphanumeric'>".$p->t('abgabetool/typ')."</th>
				<th class='table-sortable:alphanumeric'>".$p->t('global/stg')."</th>
				<th class='table-sortable:alphanumeric'>".$p->t('global/sem')."</th>
				<th>".$p->t('abgabetool/titel')."</th>
				<th class='table-sortable:alphanumeric'>".$p->t('abgabetool/betreuerart')."</th>";
	$htmlstr .= "</tr></thead><tbody>\n";
	$i = 0;
	while($row=$db->db_fetch_object($erg))
	{
		$htmlstr .= "   <tr>\n"; //class='liste".($i%2)."'
		$htmlstr .= "		<td><input type='checkbox' name='mc_".$row->projektarbeit_id."' ></td>";
		$htmlstr .= "       <td><a href='abgabe_lektor_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&titel=".$row->titel."&betreuerart=".$row->betreuerart_kurzbz."' target='al_detail' title='Details anzeigen'>".$row->uid."</a> / ".$row->matrikelnr."</td>\n";
		$htmlstr .= "	    <td align= center><a href='mailto:$row->uid@".DOMAIN."?subject=".$row->projekttyp_kurzbz."arbeitsbetreuung'><img src='../../../skin/images/email.png' alt='email' title='Email an Studenten'></a></td>";
		$htmlstr .= "       <td>".$row->vorname."</td>\n";
		$htmlstr .= "       <td>".$row->nachname."</td>\n";
		$htmlstr .= "       <td>".$row->projekttyp_kurzbz."</td>\n";
		$htmlstr .= "       <td>".strtoupper($row->typ.$row->kurzbz)."</td>\n";
		$htmlstr .= "       <td>".$row->studiensemester_kurzbz."</td>\n";
		$htmlstr .= "       <td>".$row->titel."</td>\n";
		$htmlstr .= "       <td>".$row->betreuerart_kurzbz."</td>\n";
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
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
		function confdel()
		{
			if(confirm("'.$p->t('global/warnungWirklichLoeschen').'"))
				return true;
			return false;
		}
		</script>
	</head>

<body class="background_main">';

echo "<h2><div style='float: left'>".$p->t('abgabetool/ueberschrift')." ($getuid) </div><div style='text-align:right'><a href='../../private/info/handbuecher/abgabetool_lektoren.pdf' target='_blank'><img src='../../../skin/images/information.png' alt='Anleitung' title='Anleitung BaDa-Abgabe' border=0>&nbsp;".$p->t('global/handbuch')."</a></div></h2>";

echo $htmlstr;

echo '<a href="'.$_SERVER['PHP_SELF'].'?showall">- '.$p->t('abgabetool/alleArbeitenAnzeigen').'</a><br />';
echo '<a href="abgabe_lektor_terminuebersicht.php" target="_blank">- '.$p->t('abgabetool/terminuebersichtAnzeigen').'</a>';

echo '</body>
</html>';
?>