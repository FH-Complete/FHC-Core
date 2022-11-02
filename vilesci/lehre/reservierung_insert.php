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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *			Manfred Kindl < manfred.kindl@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/reservierung.class.php');

if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid=get_uid();
$datum_obj = new datum();

$rechte =  new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/reservierungAdvanced', null, 'sui'))
	die('<span class="error">Sie haben keine Berechtigung für diese Seite</span>');

$stgid=(isset($_REQUEST['stgid'])?$_REQUEST['stgid']:0);
$lektorid=(isset($_REQUEST['lektorid'])?$_REQUEST['lektorid']:$uid);
$ortid=(isset($_REQUEST['ortid'])?$_REQUEST['ortid']:'');
$semester=(isset($_REQUEST['semester'])?$_REQUEST['semester']:'');
$verband=(isset($_REQUEST['verband'])?$_REQUEST['verband']:'');
$gruppe=(isset($_REQUEST['gruppe'])?$_REQUEST['gruppe']:'');
$gruppe_kurzbz=(isset($_REQUEST['gruppe_kurzbz'])?$_REQUEST['gruppe_kurzbz']:'');
$datum=(isset($_REQUEST['datum'])?$_REQUEST['datum']:date('d.m.Y'));
$titel=(isset($_REQUEST['titel'])?$_REQUEST['titel']:'');
$beschreibung=(isset($_REQUEST['beschreibung'])?$_REQUEST['beschreibung']:'');
$type=(isset($_REQUEST['type'])?$_REQUEST['type']:'');
$stdbegin=(isset($_REQUEST['stdbegin'])?$_REQUEST['stdbegin']:1);
$stdblock=(isset($_REQUEST['stdblock'])?$_REQUEST['stdblock']:2);

echo '
<!DOCTYPE HTML>
<html>
<head>
<title>Insert Reservierungen</title>
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
	    $( "#datepicker_datum" ).datepicker($.datepicker.regional[\'de\']);

	    $("#ort").autocomplete({
			source: "reservierung_autocomplete.php?autocomplete=ort",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].ort_kurzbz;
					ui.content[i].label=ui.content[i].ort_kurzbz;
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#ort_kurzbz").val(ui.item.uid);
			}
			});
	});
</script>
</head>
<body>
<H2>Reservierungen einfügen</H2>
<hr>
<form name="stdplan" method="post" action="reservierung_insert.php">
  <p>Studiengang
    <select name="stgid">';

$sql_query="SELECT studiengang_kz, UPPER(oe_kurzbz) AS oe_kurzbz, bezeichnung FROM public.tbl_studiengang WHERE aktiv ORDER BY oe_kurzbz";

$result_stg=$db->db_query($sql_query);
if(!$result_stg)
	die("studiengang not found! ".$db->db_last_error());

if ($result_stg)
		$num_rows=$db->db_num_rows($result_stg);
else
	$num_rows=0;
for ($i=0;$i<$num_rows;$i++)
{
	$row=$db->db_fetch_object ($result_stg, $i);
	if ($stgid==$row->studiengang_kz)
		echo "<option value=\"$row->studiengang_kz\" selected>$row->oe_kurzbz - $row->bezeichnung</option>";
	else
		echo "<option value=\"$row->studiengang_kz\">$row->oe_kurzbz - $row->bezeichnung</option>";
}

echo '</select>
	</p>
    <p>Semester
    <select name="semester">
		<option value="">*</option>
';

for ($i=1;$i<15;$i++)
{
	if ($semester==$i)
		echo "<option value=\"$i\" selected>$i</option>";
	else
		echo "<option value=\"$i\">$i</option>";
}

echo '
    </select>
    Verband
    <select name="verband">
	  <option value="">*</option>';

$verbaende=array("A","B","C","D","E","F","I","J","L","M","O","P","S","V","X");

foreach ($verbaende as $i)
{
	if ($verband==$i)
		echo "<option value=\"$i\" selected>$i</option>";
	else
		echo "<option value=\"$i\">$i</option>";
}

echo '
	</select>
    Gruppe
    <select name="gruppe">
	  <option value="">*</option>';

for ($i=1;$i<10;$i++)
{
	if ($gruppe==$i)
		echo "<option value=\"$i\" selected>$i</option>";
	else
		echo "<option value=\"$i\">$i</option>";
}

echo '
    </select>
    Spezialgruppe
    <select name="gruppe_kurzbz">
	  <option value="">*</option>';

$sql_query="SELECT gruppe_kurzbz FROM public.tbl_gruppe WHERE lehre=true AND sichtbar=true AND aktiv=true ORDER BY gruppe_kurzbz";
$result_gruppe_kurzbz=$db->db_query($sql_query);
if(!$result_gruppe_kurzbz)
	die("spezialgruppe not found! ".$db->db_last_error());

if ($result_gruppe_kurzbz)
	$num_rows=$db->db_num_rows($result_gruppe_kurzbz);
else
	$num_rows=0;
for ($i=0;$i<$num_rows;$i++)
{
	$row=$db->db_fetch_object ($result_gruppe_kurzbz, $i);
	if ($gruppe_kurzbz==$row->gruppe_kurzbz)
		echo "<option value=\"$row->gruppe_kurzbz\" selected>$row->gruppe_kurzbz</option>";
	else
		echo "<option value=\"$row->gruppe_kurzbz\">$row->gruppe_kurzbz</option>";
}

echo '    </select>
	Wenn Spezialgruppe ausgewählt, muss Studiengang und Semester gleich der Spezialgruppe sein
  </p>
  <p>

	Titel
    <input type="text" name="titel" size="10" maxlength="10" value="'.$db->convert_html_chars($titel).'">
    Beschreibung
    <input type="text" name="beschreibung" size="32" maxlength="32" value="'.$db->convert_html_chars($beschreibung).'">

    LektorIn
    <SELECT name="lektorid">';

$sql_query="SELECT uid, person_id, kurzbz FROM campus.vw_mitarbeiter WHERE aktiv=true ORDER BY kurzbz";
$result_lektor=$db->db_query($sql_query);
if(!$result_lektor)
	die("lehre.lektor not found! ".$db->db_last_error());

$num_rows=$db->db_num_rows($result_lektor);
$row=$db->db_fetch_object ($result_lektor);
if ($lektorid==$row->uid)
	$lektorid=$uid;
for ($i=0;$i<$num_rows;$i++)
{
	$row=$db->db_fetch_object ($result_lektor, $i);
	if ($lektorid==$row->uid)
		echo "<option value=\"$row->uid\" selected>$row->kurzbz</option>";
	else
		echo "<option value=\"$row->uid\">$row->kurzbz</option>";
}

echo '    </SELECT>

  </p>
	<p>
	Ort';

if ($ortid!='')
	echo '<input type="text" size="25" maxlength="40" name="ortid" id="ort" value="'.$db->convert_html_chars($ortid).'"/><input type="hidden" value="'.$db->convert_html_chars($ortid).'" id="ort_kurzbz" name="ort_kurzbz" />';
else
	echo '<input type="text" size="25" maxlength="40" name="ortid" id="ort" value=""/><input type="hidden" value="" id="ort_kurzbz" name="ort_kurzbz" />';


echo '
	Datum
	<input type="text" id="datepicker_datum" size="12" name="datum" value="'.$datum.'">
	</p>
	<p>
    Einheit Beginn
    <input type="text" name="stdbegin" size="2" maxlength="2" value="'.$stdbegin.'">
	Anzahl Einheiten
    <input type="text" name="stdblock" size="2" maxlength="2" value="'.$stdblock.'">
  </p>
  <p>
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Ausführen">
  </p>
  <hr>
</form>';

if($rechte->isBerechtigt('lehre/reservierungAdvanced', null, 'sui'))
{
	if ($type=="save")
	{
		$error=false;
		$stunde=$stdbegin;

		//Einfuegen in die Datenbank
		if (!$error)
		{
			$insert_datum=$datum_obj->formatDatum($datum,'Y-m-d');
			for ($i=0; ($i<$stdblock)&&!$error; $i++)
			{
				$std=$stdbegin+($i % $stdblock);

				$reservierung = new reservierung();
				$reservierung->ort_kurzbz = $_POST['ortid'];
				$reservierung->studiengang_kz = $_POST['stgid'];
				$reservierung->uid = $_POST['lektorid'];
				$reservierung->stunde = $std;
				$reservierung->datum = $insert_datum;
				$reservierung->titel = $_POST['titel'];
				$reservierung->beschreibung = $_POST['beschreibung'];
				$reservierung->semester = $_POST['semester'];
				$reservierung->verband = $_POST['verband'];
				$reservierung->gruppe = $_POST['gruppe'];
				$reservierung->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
				$reservierung->insertamum = date('Y-m-d H:i:s');
				$reservierung->insertvon = $uid;

				if(!$reservierung->save(true))
				{
					echo $reservierung->errormsg."<br>";
					$error=true;
				}
				else
				{
					echo "<div style='font-size:small;'>
					<strong>Ort:</strong> ".$db->convert_html_chars($_POST['ortid'])." -
					<strong>Studiengang_Kz:</strong> ".$db->convert_html_chars($_POST['stgid'])." -
					<srong>Semester:</strong> ".$db->convert_html_chars($_POST['semester'])." -
					<strong>Verband:</strong> ".$db->convert_html_chars($_POST['verband'])." -
					<strong>Gruppe:</strong> ".$db->convert_html_chars($_POST['gruppe'])." -
					<strong>Spezialgruppe:</strong> ".$db->convert_html_chars($_POST['gruppe_kurzbz'])." -
					<strong>Lektor:</strong> ".$db->convert_html_chars($_POST['lektorid'])." -
					<strong>Titel:</strong> ".$db->convert_html_chars($_POST['titel'])." -
					<strong>Beschreibung:</strong> ".$db->convert_html_chars($_POST['beschreibung'])." -
					<strong>Datum:</strong>".$db->convert_html_chars($datum)." -
					<strong>Stunde:</strong>".$db->convert_html_chars($std)." -- <strong>Eingefügt!</strong></div>";
				}
			}
			if (!$error)
				echo '<br><span class="ok">Einfügen erfolgreich abgeschlossen!</span><br>';
			else
				echo '<br><span class="error">Es ist ein Fehler aufgetreten!</span><br>';
		}
	}
}
else
 echo "<div style='color:red;'><strong>Für diese Aktion haben Sie nicht die nötigen Rechte</strong></div>";

echo '
</body>
</html>';

?>
