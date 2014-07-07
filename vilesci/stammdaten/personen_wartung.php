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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Script zum Zusammenlegen Doppelter Studenten
 * Es werden zwei Listen mit Studenten angezeigt
 * Links wird der Student markiert, der mit dem
 * rechts markierten zusammengelegt werden soll.
 * Der linke Student wird danach entfernt.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/person.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('basis/person'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$msg='';
$outp='';

$filter = isset($_REQUEST['filter'])?$_REQUEST['filter']:'';

if (isset($_GET['person_id']) || isset($_POST['person_id']))
{
	$person_id=(isset($_GET['person_id'])?$_GET['person_id']:$_POST['person_id']);
}
else
{
	$person_id=NULL;
}

if (isset($_GET['radio_1']) || isset($_POST['radio_1']))
{
	$radio_1=(isset($_GET['radio_1'])?$_GET['radio_1']:$_POST['radio_1']);
}
else
{
	$radio_1=-1;
}
if (isset($_GET['radio_2']) || isset($_POST['radio_2']))
{
	$radio_2=(isset($_GET['radio_2'])?$_GET['radio_2']:$_POST['radio_2']);
}
else
{
	$radio_2=-1;
}

if(isset($radio_1) && isset($radio_2) && $radio_1>=0 && $radio_2>=0)
{
	if($radio_1==$radio_2)
	{
		$msg="Die Datensaetze duerfen nicht die gleiche ID haben";
	}
	else
	{
		$person = new person();
		if($person->load($radio_1))
		{
			$msg='';
			$sql_query_upd1="BEGIN;";
			// Wenn bei einer der Personen das Foto gesperrt ist, dann die Sperre uebernehmen
			if($person->foto_sperre)
				$sql_query_upd1.="UPDATE public.tbl_person SET foto_sperre=true WHERE person_id=".$db->db_add_param($radio_2, FHC_INTEGER).";";

			// Wenn die zu loeschende Person ein Foto hat, und die andere nicht, 
			// dann wird das Foto uebernommen
			if($person->foto!='')
			{
				$person2 = new person();
				$person2->load($radio_2);
				if($person2->foto=='')
				{
					$sql_query_upd1.="UPDATE public.tbl_person SET foto=".$db->db_add_param($person->foto)." WHERE person_id=".$db->db_add_param($radio_2, FHC_INTEGER).";";
				}
			}

			$sql_query_upd1.="UPDATE wawi.tbl_betriebsmittelperson SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_benutzer SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_konto SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_prestudent SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer1=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE pruefer1=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer2=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE pruefer2=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer3=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE pruefer3=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE lehre.tbl_projektbetreuer SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_adresse SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_akte SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_bankverbindung SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_kontakt SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";			
			$sql_query_upd1.="UPDATE public.tbl_preinteressent SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_personfunktionstandort SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="UPDATE public.tbl_notizzuordnung SET person_id=".$db->db_add_param($radio_2, FHC_INTEGER)." WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";
			$sql_query_upd1.="DELETE FROM public.tbl_person WHERE person_id=".$db->db_add_param($radio_1, FHC_INTEGER).";";

			if($db->db_query($sql_query_upd1))
			{
				$msg = "Daten erfolgreich gespeichert<br>";
				$msg .= "<br>".mb_eregi_replace(';',';<br>',$sql_query_upd1);
				$db->db_query("COMMIT;");
			}
			else
			{
				$msg = "Die Änderung konnte nicht durchgeführt werden!";
				$db->db_query("ROLLBACK;");
				$msg.= "<br>".mb_eregi_replace(';',';<br><b>',$sql_query_upd1)."ROLLBACK</b>";
			}
			$radio_1=0;
			$radio_2=0;
		}
		else
		{
			$msg = "Fehler beim Laden von Person1";
		}
	}
}
if((isset($radio_1) && !isset($radio_2))||(!isset($radio_1) && isset($radio_2)) || ($radio_1<0 || $radio_2<0))
{
	$msg="Es muß je ein Radio-Button pro Tabelle angeklickt werden";
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link href="../../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>
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

	});
	function enable(id)
	{
		if (id == 'radio_1')
			var radios = document.getElementsByName('radio_2');
		else
			var radios = document.getElementsByName('radio_1');
		for (var i=0, iLen=radios.length; i<iLen; i++) {
			radios[i].disabled = false;
			} 
	}
	function disable(id)
	{
		document.getElementById(id).disabled = true;
	}
	</script>

	<title>Personen-Zusammenlegung</title>
</head>
<body>
<H1>Zusammenlegen von Personendatensätzen</H1>

<?php
echo $outp;
echo "<form name='suche' action='personen_wartung.php' method='POST'>";
echo '<input name="filter" type="text" value="'.$db->convert_html_chars($filter).'" size="64" maxlength="64">';
echo '<input type="submit" value=" suchen ">';
echo "</form>";

echo '<br>
	<center>
	<h2><span style="font-size:0.7em">'.$msg.'</span></h2></center>
	<br>';

	//Tabellen anzeigen
	echo '<form name="form_table" action="personen_wartung.php?uid='.$db->convert_html_chars($person_id).'&filter='.$db->convert_html_chars($filter).'" method="POST">';
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";

	echo "<td valign='top'>Der wird gelöscht:";

	 //Tabelle 1
	 echo '<table id="t1" class="tablesorter"><thead><tr>';
	 echo "<th>ID</th>";
	 echo "<th>Nachname</th>";
	 echo "<th>Vorname</th>";
	 echo "<th>Geb.datum</th>";
	 echo "<th>SVNr</th>";
	 echo "<th>Ersatzkennz.</th>";
	 echo "<th>&nbsp;</th></tr></thead><tbody>";

	 $lf  = new person();
	 $lf->getTab($filter);
	 $i=0;
	 foreach($lf->personen as $l)
	 {
	 	echo "<tr>";
	 	echo "<td>$l->person_id</td>";
	 	echo "<td>$l->nachname</td>";
	 	echo "<td>$l->vorname</td>";
	 	echo "<td>$l->gebdatum</td>";
	 	echo "<td>$l->svnr</td>";
	 	echo "<td>$l->ersatzkennzeichen</td>";
	 	echo "<td><input type='radio' name='radio_1' id='radio_1_$l->person_id' value='$l->person_id' ".((isset($radio_1) && $radio_1==$l->person_id)?'checked':'')." onclick='enable(\"radio_1\"); disable(\"radio_2_$l->person_id\")'></td>";
	 	echo "</tr>";
	 	$i++;
	 }
	 echo "</tbody></table>";
	 echo "</td>";
	 echo "<td valign='top'><input type='submit' value='  ->  '></td>";
	 echo "<td valign='top'>Der bleibt:";

	 //Tabelle 2
	 echo '<table id="t2" class="tablesorter"><thead><tr>';
	 echo "<th>&nbsp;</th>";
	 echo "<th>ID</th>";
	 echo "<th>Nachname</th>";
	 echo "<th>Vorname</th>";
	 echo "<th>Geb.datum</th>";
	 echo "<th>SVNr</th>";
	 echo "<th>Ersatzkennz.</th>";
	 echo "</tr></thead><tbody>";

	 $lf  = new person();
	 $lf->getTab($filter);
	 $i=0;
	 foreach($lf->personen as $l)
	 {
	 	echo "<tr>";
	 	echo "<td><input type='radio' name='radio_2' id='radio_2_$l->person_id' value='$l->person_id' ".((isset($radio_2) && $radio_2==$l->person_id)?'checked':'')." onclick='enable(\"radio_2\"); disable(\"radio_1_$l->person_id\")'></td>";
	 	echo "<td>$l->person_id</td>";
	 	echo "<td>$l->nachname</td>";
	 	echo "<td>$l->vorname</td>";
	 	echo "<td>$l->gebdatum</td>";
	 	echo "<td>$l->svnr</td>";
	 	echo "<td>$l->ersatzkennzeichen</td>";
	 	echo "</tr>";
	 	$i++;
	 }
	 echo "</tbody></table>";
	 echo "</td>";
	 echo "</tr>";
	 echo "</table>";
	 echo "</form>";

?>
</tr>
</table>
</body>
</html>
