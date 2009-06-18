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
 *				abgabe_assistenz
 * 		abgabe_assistenz ist die Assistenzoberfläche des Abgabesystems 
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

	require_once('../../cis/config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/mail.class.php');
	

	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$i=0;
$irgendwas='';
foreach($_POST as $key=>$value)
{
	if(stristr($key, "mc_"))
	{
		$irgendwas.=substr($key, 3).";";
		//echo $irgendwas."<br>";
		$i++;
	}	
}
$irgendwas = (isset($_POST['irgendwas'])?$_POST['irgendwas']:$irgendwas);
$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$command = (isset($_POST['command'])?$_POST['command']:'-1');
$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
$fixtermin = (isset($_POST['fixtermin'])?1:0);
$datum = (isset($_POST['datum'])?$_POST['datum']:'');
$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');


$user = get_uid();
$datum_obj = new datum();
$error='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

//echo $irgendwas."<br>";

if(isset($_POST["schick"]))
{
	$termine=explode(";",$irgendwas);
	//var_dump($termine);
	for($j=0;$j<count($termine)-1;$j++)
	{
		$qrychk="SELECT * FROM campus.tbl_paabgabe 
			WHERE projektarbeit_id='".$termine[$j]."' AND paabgabetyp_kurzbz='$paabgabetyp_kurzbz' 
			AND fixtermin=".($fixtermin==1?'true':'false')." AND datum='$datum' AND kurzbz='$kurzbz'";
		//echo $qrychk;
		if($result=pg_query($conn, $qrychk))
		{
			if(pg_num_rows($result)>0)
			{
				echo "Datensatz bereits vorhanden";
			}
			else 
			{
				//echo "neuer Termin";
				$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz, fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum) 
					VALUES ('".$termine[$j]."', '$paabgabetyp_kurzbz', ".($fixtermin==1?'true':'false').", '$datum', '$kurzbz', NULL, '$user', now(), NULL, NULL)";
				//echo $qry;	
				if(!$result=pg_query($conn, $qry))
				{
					echo "<font color=\"#FF0000\">Termin konnte nicht eingetragen werden!</font><br>&nbsp;";	
				}
				else 
				{
					$row=@pg_fetch_object($result);
					$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$paabgabetyp_kurzbz."'";
					if($result_typ=pg_query($conn, $qry_typ))
					{
						$row_typ=@pg_fetch_object($result_typ);
					}
					else 
					{
						$row_typ->bezeichnung='';
					}
					//Student zu projektarbeit_id suchen
					$qry_std="SELECT * FROM campus.vw_student WHERE uid IN(SELECT student_uid FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=$termine[$j])";
					if($result_std=pg_query($conn, $qry_std))
					{
						$row_std=@pg_fetch_object($result_std);
						//$mail = new mail($row_std->uid."@".DOMAIN, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung",
						//"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr(e) Betreuer(in) hat einen neuen Termin angelegt:\n".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nIhr(e) Betreuer(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
					}
				}
				$command='';
			}
		}
		else 
		{
			echo "Datenbank-Zugriffsfehler!";
		}
	}
}

$htmlstr='';

	echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Mehrfachtermin PA-Abgabe</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		</head>
		<body class="Background_main"  style="background-color:#eeeeee;">
		<h3>Eingabe eines Termins f&uuml;r mehrere Personen</h3>';
		//Eingabezeile für neuen Termin
		$htmlstr .= "<br><b>Abgabetermin:</b>\n";
		$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
		$htmlstr .= "<form action='$PHP_SELF' method='POST' name='multitermin'>\n";
		$htmlstr .= "<input type='hidden' name='irgendwas' value='".$irgendwas."'>\n";
		$htmlstr .= "<tr></tr>\n";
		$htmlstr .= "<tr><td>fix</td><td>Datum</td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td></tr>\n";
		$htmlstr .= "<tr id='termin'>\n";
		$htmlstr .= "<td><input type='checkbox' name='fixtermin'></td>";
		$htmlstr .= "		<td><input  type='text' name='datum' size='10' maxlegth='10'></td>\n";
		$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
		$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp";
		$result_typ=pg_query($conn, $qry_typ);
		while ($row_typ=pg_fetch_object($result_typ))
		{
			$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."'>".$row_typ->bezeichnung."</option>";
		}		
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td><input  type='text' name='kurzbz' size='60' maxlegth='256'></td>\n";		
		$htmlstr .= "		<td>&nbsp;</td>\n";		
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern' title='neuen Termin speichern'></td>";
		
		$htmlstr .= "</tr>\n";
		$htmlstr .= "</form>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "</body></html>\n";
		
		echo $htmlstr;
		echo '</body></html>';

?>