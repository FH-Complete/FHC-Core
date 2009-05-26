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
 *				               abgabe_assistenz
 * 		abgabe_assistenz ist die Assistenzoberfläche des Abgabesystems 
 * 			            für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

	require_once('../../cis/config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/mitarbeiter.class.php');

	//DB Verbindung herstellen
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
$getuid=get_uid();
$htmlstr = "";
$erstbegutachter='';
$zweitbegutachter='';

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz='';
if(!is_numeric($stg_kz) && $stg_kz!='')
	$stg_kz='0';

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($getuid);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', null, 'suid', $fachbereich_kurzbz))
	die('Sie haben keine Berechtigung für diesen Studiengang');
	
$sql_query = "SELECT * 
			FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) * FROM lehre.tbl_projektarbeit  
			LEFT JOIN public.tbl_benutzer on(uid=student_uid) 
			LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id) 
			LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) 
			LEFT JOIN public.tbl_studiengang using(studiengang_kz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
			AND lehre.tbl_projektarbeit.note IS NULL 
			AND public.tbl_studiengang.studiengang_kz='$stg_kz'   
			ORDER BY tbl_projektarbeit.projektarbeit_id desc) as xy 
		ORDER BY nachname";

if(!$erg=pg_query($conn, $sql_query))
{
	$errormsg='Fehler beim Laden der Betreuungen';
}
else
{
	//$htmlstr .= "<form name='formular'><input type='hidden' name='check' value=''></form>";
	$htmlstr .= "<form name='multitermin' action='abgabe_assistenz_multitermin.php' title='Serientermin' target='al_detail' method='POST'>";
	$htmlstr .= "<table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>\n";
	$htmlstr .= "<thead><tr class='liste'>\n";
	$htmlstr .= "<th></th><th class='table-sortable:default'>UID</th>
				<th>Email</th>
				<th class='table-sortable:default'>Vorname</th>
				<th class='table-sortable:alphanumeric'>Nachname</th>";
	$htmlstr .= "<th>Typ</th>
				<th>Titel</th>
				<th>1.Betreuer</th>
				<th>2.Betreuer</th>";
	$htmlstr .= "</tr></thead><tbody>\n";
	$i = 0;
	while($row=pg_fetch_object($erg))
	{
		//Betreuer suchen
		$qry_betr="SELECT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first, '' as second 
		FROM public.tbl_person, lehre.tbl_projektbetreuer
		WHERE projektarbeit_id='$row->projektarbeit_id'  
		AND lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id
		AND (tbl_projektbetreuer.betreuerart_kurzbz='Erstbegutachter' OR tbl_projektbetreuer.betreuerart_kurzbz='Betreuer')
		UNION
		SELECT '' as first,trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as second 
		FROM public.tbl_person, lehre.tbl_projektbetreuer
		WHERE projektarbeit_id='$row->projektarbeit_id' 
		AND lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id
		AND tbl_projektbetreuer.betreuerart_kurzbz='Zweitbegutachter'
		";

		if(!$betr=pg_query($conn, $qry_betr))
		{
			$errormsg='Fehler beim Laden der Betreuer';
		}
		else
		{
			while($row_betr=pg_fetch_object($betr))
			{
				if($row_betr->first!='')
					$erstbegutachter=$row_betr->first;
				if($row_betr->second!='')
					$zweitbegutachter=$row_betr->second;
					
			}
		}
		$htmlstr .= "   <tr class='liste".($i%2)."'>\n";
		$htmlstr .= "		<td><input type='checkbox' name='mc_".$row->projektarbeit_id."' ></td>";
		$htmlstr .= "       <td><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&titel=".$row->titel."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
		$htmlstr .= "	    <td align= center><a href='mailto:$row->uid@".DOMAIN."?subject=".$row->projekttyp_kurzbz."arbeitsbetreuung'><img src='../../skin/images/email.png' alt='email' title='Email an Studenten'></a></td>";
		$htmlstr .= "       <td>".$row->vorname."</td>\n";
		$htmlstr .= "       <td>".$row->nachname."</td>\n";
		$htmlstr .= "       <td>".$row->projekttyp_kurzbz."</td>\n";
		$htmlstr .= "       <td>".$row->titel."</td>\n";
		$htmlstr .= "       <td>".$erstbegutachter."</td>\n";
		$htmlstr .= "       <td>".$zweitbegutachter."</td>\n";
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
<title>Abgabesystem_Assistenzsicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript">
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
echo "<h2>Bachelor-/Diplomarbeitsbetreuungen (Studiengang $stg_kz)</h2>";


    echo $htmlstr;
?>

</body>
</html>