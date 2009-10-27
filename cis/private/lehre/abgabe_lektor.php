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

// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
		
	$getuid=get_uid();
	$htmlstr = "";

	
	$sql_query = "SELECT * FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) * FROM lehre.tbl_projektarbeit LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id) 
			LEFT JOIN public.tbl_benutzer on(uid=student_uid) 
			LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id) 
			LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) 
			LEFT JOIN public.tbl_studiengang using(studiengang_kz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
			AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer 
									WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id 
									AND public.tbl_benutzer.uid='$getuid')
			AND public.tbl_benutzer.aktiv 
			AND lehre.tbl_projektarbeit.note IS NULL 
			AND (betreuerart_kurzbz='Betreuer' OR betreuerart_kurzbz='Begutachter' OR betreuerart_kurzbz='Erstbegutachter' OR betreuerart_kurzbz='Erstbetreuer') 
			ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy 
		ORDER BY nachname";

if(!$erg=$db->db_query($sql_query))
{
	$errormsg='Fehler beim Laden der Betreuungen';
}
else
{
	//$htmlstr .= "<form name='formular'><input type='hidden' name='check' value=''></form>";
	$htmlstr .= "<form name='multitermin' action='abgabe_lektor_multitermin.php' title='Serientermin' target='al_detail' method='POST'>";
	$htmlstr .= "<table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>\n";
	$htmlstr .= "<thead><tr class='liste'>\n";
	$htmlstr .= "<th></th><th class='table-sortable:default'>UID</th>
				<th>Email</th>
				<th class='table-sortable:default'>Vorname</th>
				<th class='table-sortable:alphanumeric'>Nachname</th>";
	$htmlstr .= "<th>Typ</th>
				<th>Stg.</th>
				<th>Sem.</th>
				<th>Titel</th>
				<th>Betreuerart</th>";
	$htmlstr .= "</tr></thead><tbody>\n";
	$i = 0;
	while($row=$db->db_fetch_object($erg))
	{
		$htmlstr .= "   <tr class='liste".($i%2)."'>\n";
		$htmlstr .= "		<td><input type='checkbox' name='mc_".$row->projektarbeit_id."' ></td>";
		$htmlstr .= "       <td><a href='abgabe_lektor_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&titel=".$row->titel."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
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
	$htmlstr .= "<table><tr><td rowspan=3><input type='submit' name='multi' value='Terminserie anlegen' title='Termin f&uuml;r mehrere Personen anlegen.'></td></tr></table>\n";
	$htmlstr .= "</form>";
}

?>
<html>
<head>
<title>Abgabesystem_Lekorensicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
		return true;
	return false;
}
</script>
</head>

<body class="background_main">
<?php 
echo "<h2><a href='../../cisdocs/Projektarbeitsabgabe_FHTW_Anleitung.pdf' target='_blank'><img src='../../../skin/images/information.png' alt='Anleitung' title='Anleitung BaDa-Abgabe' border=0></a>&nbsp;&nbsp;Bachelor-/Diplomarbeitsbetreuungen ($getuid)</h2>";


    echo $htmlstr;
?>

</body>
</html>