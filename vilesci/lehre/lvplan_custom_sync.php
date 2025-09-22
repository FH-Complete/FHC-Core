<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>LV-Plan Syncronisation</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
	$( ".datepicker_datum" ).datepicker({
		 changeMonth: true,
		 changeYear: true,
		 dateFormat: 'dd.mm.yy',
		 });
});

function enable()
{
	if(document.getElementById("nostudentmail").disabled == false)
	{
		document.getElementById("nostudentmail").disabled = true;
		if (document.getElementById("nostudentmail").checked == true)
			document.getElementById("nostudentmail").checked = false;
	}
	else
	{
		document.getElementById("nostudentmail").disabled = false
	}
}
function set_bis()
{
	document.getElementById("bis").value=document.getElementById("von").value;

}
function checkdatum()
{
	var Datum,Tag,Monat,Jahr,vonDatum,bisDatum;

	Datum=document.getElementById("von").value;
    Tag=Datum.substring(0,2);
    Monat=Datum.substring(3,5);
    Jahr=Datum.substring(6,10);
    vonDatum=Jahr+''+Monat+''+Tag;

    Datum=document.getElementById("bis").value;
    Tag=Datum.substring(0,2);
    Monat=Datum.substring(3,5);
    Jahr=Datum.substring(6,10);
    bisDatum=Jahr+''+Monat+''+Tag;

	if (bisDatum<vonDatum)
	{
		alert("Das Bis-Datum darf nicht kleiner als das Von-Datum sein");
		document.getElementById("bis").focus();
	  	return false;
	}
	else
	{
	  	return true;
	}
	return true;
}
</script>
<style type="text/css">
.ui-datepicker
{
	width:13em;
	padding:.2em .2em 0;
	display:none
}
.ui-datepicker .ui-datepicker-title select
{
	font-size:0.6em;
	margin:1px 0
}
.ui-datepicker table
{
	width:100%;
	font-size:0.6em;
	border-collapse:collapse;
	margin:0 0 .4em
}
</style>
</head>
<body>
<h2>LV-Plan Synronisation</h2>
<?php
	echo '
	<a href="../../system/sync/sync_stpldev_stpl.php">LV-Plan Sync - Normal (Alle) - mit Mails</a><br><br>
	<a href="../../system/sync/sync_stpldev_stpl.php?sendmail=false">LV-Plan Sync - Normal (Alle) - ohne Mails</a><br>
	<br>
	<fieldset>
	<legend>Sync für speziellen Studiengang / Zeitraum</legend>
	<form action="../../system/sync/sync_stpldev_stpl.php" method="GET">
	<input type="hidden" name="custom" value="true">
	<table>
		<tr>
			<td>Studiengang</td>
			<td><SELECT name="studiengang_kz">';

	$stg = new studiengang();
	$stg->getAll('typ, kurzbz');

	foreach($stg->result as $row)
	{
		echo '<option value="'.$row->studiengang_kz.'">'.$row->kuerzel.' ('.$row->kurzbzlang.')</option>';
	}
	echo '</SELECT>
			</td>
		</tr>
		<tr>
			<td>Von</td>
			<td><input id="von" class="datepicker_datum" type="text" name="von" size="10" value="'.date('d.m.Y').'" oninput="set_bis()" onchange="set_bis()"></td>
		</tr>
		<tr>
			<td>Bis</td>
			<td><input id="bis" class="datepicker_datum" type="text" name="bis" size="10" value="'.date('d.m.Y').'"></td>
		</tr>
		<tr>
			<td>Mails senden</td>
			<td><input type="checkbox" name="mail" onclick="enable()"></td>
		</tr>
 		<tr>
			<td>Mails nur an Lektoren senden</td>
			<td><input id="nostudentmail" type="checkbox" name="nostudentmail" disabled></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Start" onclick="return checkdatum()"></td>
		</tr>
	</table>
	</form>
	</fieldset>';
?>
</body>
</html>
