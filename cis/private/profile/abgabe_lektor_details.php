<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> 
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 */
/*******************************************************************************************************
 *				abgabe_lektor
 * 		abgabe_lektor ist die Lektorenmaske des Abgabesystems 
 * 			f�r Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/datum.class.php');
$fixtermin=false;

if (!$conn = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if(!isset($_POST['uid']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');

	$command = '';
	$paabgabe_id = '';
	$fixtermin = false;
	$datum = '01.01.1980';
	$kurzbz = '';
}
else 
{
	$uid = (isset($_POST['uid'])?$_POST['uid']:'-1');
	$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$titel = (isset($_POST['titel'])?$_POST['titel']:'');
	$command = (isset($_POST['command'])?$_POST['command']:'-1');
	$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
	$fixtermin = (isset($_POST['fixtermin'])?1:0);
	$datum = (isset($_POST['datum'])?$_POST['datum']:'');
	$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
}
$user = get_uid();
$datum_obj = new datum();
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
$htmlstr='';

$datum = $datum_obj->formatDatum($datum, $format='Y-m-d');
if($uid==-1 && $projektarbeit_id==-1&& $titel==-1)
{
	//echo "Fehler bei der Daten&uuml;bergabe";
	exit;
}
		

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Reihungstest</title>
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
	function confdel()
	{
		return confirm("Wollen Sie diesen Eintrag wirklich loeschen");
	}
</script>
</head>
<body class="Background_main"  style="background-color:#eeeeee;">
<h3>Abgabe Lektorenbereich</h3>';
if($datum)
{
	// Speichern eines Termines
	if(isset($_POST["schick"]))
	{
		if($command=='insert')
		{
			//neuer Termin
			$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz, fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum) 
				VALUES ('$projektarbeit_id', '$paabgabetyp_kurzbz', ".($fixtermin==1?'true':'false').", '$datum', '$kurzbz', NULL, '$user', now(), NULL, NULL)";
		}
		if($command=='update')
		{
			//Termin�nderung
			$qry="UPDATE campus.tbl_paabgabe SET
				projektarbeit_id = '".$projektarbeit_id."', 
				paabgabetyp_kurzbz = '".$paabgabetyp_kurzbz."', 
				fixtermin = ".($fixtermin==1?'true':'false').", 
				datum = '".$datum."', 
				kurzbz = '".$kurzbz."', 
				updatevon = '".$user."', 
				updateamum = now() 
				WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";
		}
		//echo $qry;	
		$result=pg_query($conn, $qry);	
	}
	//L�schen eines Termines
	if(isset($_POST["del"]))
	{
		$qry="DELETE FROM campus.tbl_paabgabe WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";	
		$result=pg_query($conn, $qry);
	}
}
else 
{
	echo "<font color=\"#FF0000\">Datumseingabe ung&uuml;ltig!</font><br>&nbsp;";
}
$qry="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id='".$projektarbeit_id."' ORDER BY datum;";
$htmlstr .= "<table width=100%>\n";
$htmlstr .= "<tr><td style='font-size:16px'>Student: <b>".$uid."</b></td>";
$htmlstr .= "<td width=20% align=center><form action='abgabe_lektor_benotung.php' target='_blank' method='GET'>";
$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
$htmlstr .= "<input type='submit' name='note' value='benoten'></form></td></tr>";
$htmlstr .= "<tr><td style='font-size:16px'>Titel: <b>".$titel."<b><br>";
$htmlstr .= "</tr>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "<br><b>Abgabetermine:</b>\n";
$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
$htmlstr .= "<tr></tr>\n";
$htmlstr .= "<tr><td>fix</td><td>Datum</td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td><td>abgegeben am</td><td></td><td></td><td></td></tr>\n";
$result=@pg_query($conn, $qry);
	while ($row=@pg_fetch_object($result))
	{
		$htmlstr .= "<form action='$PHP_SELF' method='POST' name='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$row->paabgabe_id."'>\n";
		$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
		$htmlstr .= "<input type='hidden' name='command' value='update'>\n";
		$htmlstr .= "<tr id='".$row->projektarbeit_id."'>\n";
		if(!$row->abgabedatum)
		{
			if ($row->datum<=date('Y-m-d'))
			{
				$bgcol='#FF0000';
			}
			elseif (($row->datum>date('Y-m-d')) && ($row->datum<date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+11, date("Y")))))
			{
				$bgcol='#FFFF00';
			}
			else 
			{
				$bgcol='#FFFFFF';
			}
		}
		else 
		{
			$bgcol='#00FF00';
		}
		$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked=\"checked\"':'')." >";
		$htmlstr .= "		</td>\n";
		$htmlstr .= "		<td><input  type='text' name='datum' style='background-color:".$bgcol."' value='".$datum_obj->formatDatum($row->datum,'d.m.Y')."' size='10' maxlegth='10'></td>\n";
		$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
		$htmlstr .= "			<option value=''>&nbsp;</option>";
		$qry_typ="SELECT * FROM campus.tbl_paabgabetyp";
		$result_typ=@pg_query($conn, $qry_typ);
		while ($row_typ=@pg_fetch_object($result_typ))
		{
			if($row->paabgabetyp_kurzbz==$row_typ->paabgabetyp_kurzbz)
			{
				$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."' selected>$row_typ->bezeichnung</option>";
			}
			else 
			{
				$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."'>$row_typ->bezeichnung</option>";
			}
		}		
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td><input  type='text' name='kurzbz' value='".$row->kurzbz."' size='60' maxlegth='256'></td>\n";		
		$htmlstr .= "		<td>".$row->abgabedatum=''?'':$datum_obj->formatDatum($row->abgabedatum,'d.m.Y')."</td>\n";		
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		if(!$row->abgabedatum)
		{
			$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen' onclick='return confdel()'></td>";
		}
		else 
		{
			$htmlstr .= "		<td>&nbsp;</td>";
		}
		if(file_exists($_SERVER['DOCUMENT_ROOT'].PAABGABE_PATH.$row->paabgabe_id.'_'.$uid.'.pdf'))
		{
			$htmlstr .= "		<td><a href='".PAABGABE_PATH.$row->paabgabe_id.'_'.$uid.'.pdf'."' target='_blank'><img src='../../../skin/images/pdf.ico' alt='PDF' border=0></a></td>";
		}
		$htmlstr .= "	</tr>\n";
		
		$htmlstr .= "</form>\n";
	}	
	
//Eingabezeile f�r neuen Termin
$htmlstr .= "<form action='$PHP_SELF' method='POST' name='".$projektarbeit_id."'>\n";
$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$paabgabe_id."'>\n";
$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
$htmlstr .= "<input type='hidden' name='command' value='insert'>\n";
$htmlstr .= "<tr id='".$projektarbeit_id."'>\n";

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
$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";

$htmlstr .= "	</tr>\n";
$htmlstr .= "</form>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "</body></html>\n";

	echo $htmlstr;

?>