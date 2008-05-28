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
require_once('../../config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/raumtyp.class.php');
require_once('../../../include/datum.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur Datenbank');

$datum = (isset($_POST['datum'])?$_POST['datum']:date('d.m.Y'));
$vonzeit = (isset($_POST['vonzeit'])?$_POST['vonzeit']:date('H:i'));
$biszeit = (isset($_POST['biszeit'])?$_POST['biszeit']:date('H:i', mktime(date('H')+1,date('i'))));
$raumtyp = (isset($_POST['raumtyp'])?$_POST['raumtyp']:'');
$anzahlpersonen = (isset($_POST['anzahlpersonen'])?$_POST['anzahlpersonen']:'');
$sent = true; //isset($_POST['sent']);
$datum_obj = new datum();

echo '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Reservierungsliste</title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<script language="Javascript">
	function checkdata()
	{
		if(document.getElementById("datum").value=="")
		{
			alert("Es muss ein Datum eingegeben werden");
			return false;
		}
		datum = document.getElementById("datum").value
		if(datum.length!=10)
		{
			alert("Das angegebene Datum ist ungueltig! Bitte geben Sie das Datum im Format dd.mm.YYYY (31.12.2008) ein");
			return false;
		}
		
		if(document.getElementById("vonzeit").value=="")
		{
			alert("VON-Zeit muss eingegeben werden");
			return false;
		}
				
		if(document.getElementById("biszeit").value=="")
		{
			alert("BIS-Zeit muss eingegeben werden");
			return false;
		}
		return true;
	}
	</script>
</head>
<body id="inhalt">
	<H2><table class="tabcontent">
		<tr>
		<td>&nbsp;<a class="Item" href="index.php">Lehrveranstaltungsplan</a> &gt;&gt; Raumsuche</td>
		<td align="right"></td>
		</tr>
		</table>
	</H2>
';

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" onsubmit="return checkdata()">
		Datum* <input type="text" name="datum" id="datum" size="10" value="'.$datum.'">
		Von* <input type="text" name="vonzeit" id="vonzeit" size="5" value="'.$vonzeit.'">
		Bis* <input type="text" name="biszeit" id="biszeit" size="5" value="'.$biszeit.'">
		Raumtyp: <SELECT name="raumtyp">
		<OPTION value="">Alle</OPTION>';
$raumtyp_obj = new raumtyp($conn);
$raumtyp_obj->getAll();

foreach ($raumtyp_obj->result as $row)
{
	if($raumtyp==$row->raumtyp_kurzbz)
		$selected='selected';
	else 
		$selected='';
		
	echo '<OPTION value="'.$row->raumtyp_kurzbz.'" '.$selected.'>'.$row->raumtyp_kurzbz.'</OPTION>';
}
echo '	</SELECT>
		Anzahl Personen <input type="text" size="3" name="anzahlpersonen" value="'.$anzahlpersonen.'">
		<input type="submit" name="sent" value="Suchen" />
	  </form>';
if($sent)
{
	$error=false;
	if($datum=='')
	{
		echo "<br>Es muss ein Datum angegeben werden";
		$error = true;
	}
	if($vonzeit=='')
	{
		echo "<br>VON-Zeit muss angegeben werden";
		$error = true;
	}
	if($biszeit=='')
	{
		echo "<br>BIS-Zeit muss angegeben werden";
		$error = true;
	}
	
	if(!$error)
	{
		//Von Zeit pruefen
		if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $vonzeit))
		{
			echo "<br>VON-Zeit muss im Format hh:mm (12:30) angegeben werden";
			$error = true;
		}
		//Bis Zeit pruefen
		if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $biszeit))
		{
			echo "<br>BIS-Zeit muss im Format hh:mm (12:30) angegeben werden";
			$error = true;
		}
		//Datum pruefen
		if(!preg_match('/^([0-9]){2}\.([0-9]){2}\.([0-9]){4}$/', $datum))
		{
			echo "<br>Das angegebene Datum ist ungueltig! Bitte geben Sie das Datum im Format dd.mm.YYYY (31.12.2008) ein";
			$error = true;
		}
	}
	if(!$error)
	{
		$ort = new ort($conn);
		$ort->search($datum, $vonzeit, $biszeit, $raumtyp, $anzahlpersonen, true);
		
		echo '<br><table>';
		echo '<tr class="liste"><td>Raum</td><td>Bezeichnung</td><td>Nummer</td><td>Personen</td></tr>';
		$i=0;
		$datum_sec = $datum_obj->mktime_datum($datum)-1;
		foreach ($ort->result as $row)
		{
			$i++;
			echo '<tr class="liste'.($i%2).'">';
			echo "<td>$row->ort_kurzbz</td>";
			echo "<td>$row->bezeichnung</td>";
			echo "<td>$row->planbezeichnung</td>";
			echo "<td>$row->max_person</td>";
			echo "<td><a href='stpl_week.php?type=ort&ort_kurzbz=$row->ort_kurzbz&datum=".$datum_sec."' class='Item'>zur Reservierung</a></td>";
			echo '</tr>';
		}
		echo '</table>';
	}
}
?>