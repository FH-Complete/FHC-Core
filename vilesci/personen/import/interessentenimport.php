<?php
/* Copyright (C) 2007 Technikum-Wien
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
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Connection');
      
$user=get_uid();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="Javascript">
function disablefields(obj)
{
	if(obj.value==0)
		val=false;
	else
		val=true;
		
	document.getElementById('titel').disabled=val;
	document.getElementById('nachname').disabled=val;
	document.getElementById('vorname').disabled=val;
	document.getElementById('geschlecht').disabled=val;
	document.getElementById('geburtsdatum').disabled=val;
	document.getElementById('adresse').disabled=val;
	document.getElementById('plz').disabled=val;
	document.getElementById('ort').disabled=val;	
}

</script>
</head>
<body>
<h1>Prestudent Anlegen</h1>
<?php
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');
	
$where = '';
//Parameter
$geburtsdatum = (isset($_POST['geburtsdatum'])?$_POST['geburtsdatum']:'');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');	
$nachname = (isset($_POST['nachname'])?$_POST['nachname']:'');
$vorname = (isset($_POST['vorname'])?$_POST['vorname']:'');
$geschlecht = (isset($_POST['geschlecht'])?$_POST['geschlecht']:'');
$geburtsdatum = (isset($_POST['geburtsdatum'])?$_POST['geburtsdatum']:'');
$adresse = (isset($_POST['adresse'])?$_POST['adresse']:'');
$plz = (isset($_POST['plz'])?$_POST['plz']:'');
$ort = (isset($_POST['ort'])?$_POST['ort']:'');
$email = (isset($_POST['email'])?$_POST['email']:'');
$telefon = (isset($_POST['telefon'])?$_POST['telefon']:'');
$mobil = (isset($_POST['mobil'])?$_POST['mobil']:'');
$letzteausbildung = (isset($_POST['letzteausbildung'])?$_POST['letzteausbildung']:'');
$ausbildungsart = (isset($_POST['ausbildungsart'])?$_POST['ausbildungsart']:'');
$anmerkungen = (isset($_POST['anmerkungen'])?$_POST['anmerkungen']:'');
$studiengang_kz = (isset($_POST['studiengang_kz'])?$_POST['studiengang_kz']:'');
$person_id = (isset($_POST['person_id'])?$_POST['person_id']:'');
//end Parameter


//Speichern der Daten
if(isset($_POST['save']))
{
	
}
?>
<form method='POST'>
<table width="100%">

<tr>
<td>
<!--Formularfelder-->
<table>
<?php
echo '<tr><tr><td>Titel</td><td><input type="text" id="titel" name="titel" maxlength="64" value="'.$titel.'" /></td></tr>';
echo '<tr><tr><td>Vorname</td><td><input type="text" id="vorname" maxlength="32" name="vorname" value="'.$vorname.'" /></td></tr>';
echo '<tr><tr><td>Nachname</td><td><input type="text" maxlength="64" id="nachname" name="nachname" value="'.$nachname.'" /></td></tr>';
echo '<tr><tr><td>Geschlecht</td><td><SELECT id="geschlecht" name="geschlecht">';
echo '<OPTION value="m" '.($geschlecht=='m'?'selected':'').'>m&auml;nnlich</OPTION>';
echo '<OPTION value="w" '.($geschlecht=='w'?'selected':'').'>weiblich</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><tr><td>Geburtsdatum</td><td><input type="text" id="geburtsdatum" size="10" maxlength="10" name="geburtsdatum" value="'.$geburtsdatum.'" /></td></tr>';
echo '<tr><tr><td>Adresse</td><td><input type="text" id="adresse" maxlength="256" name="adresse" value="'.$adresse.'" /></td></tr>';
echo '<tr><tr><td>Postleitzahl</td><td><input type="text" maxlength="16" id="plz" name="plz" value="'.$plz.'" /></td></tr>';
echo '<tr><tr><td>Ort</td><td><input type="text" id="ort" maxlength="256" name="ort" value="'.$ort.'" /></td></tr>';
echo '<tr><tr><td>EMail</td><td><input type="text" id="email" maxlength="128" name="email" value="'.$email.'" /></td></tr>';
echo '<tr><tr><td>Telefon</td><td><input type="text" id="telefon" maxlength="128" name="telefon" value="'.$telefon.'" /></td></tr>';
echo '<tr><tr><td>Mobil</td><td><input type="text" id="mobil" maxlength="128" name="Mobil" value="'.$mobil.'" /></td></tr>';
echo '<tr><tr><td>Letzte Ausbildung</td><td><SELECT id="letzteausbildung" name="letzteausbildung">';
$qry = "SELECT * FROM bis.tbl_ausbildung ORDER BY ausbildungcode";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '<OPTION value="'.$row->ausbildungcode.'" '.($letzteausbildung==$row->ausbildungcode?'selected':'').'>'.$row->ausbildungbez.'</OPTION>';
	}
}
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><tr><td>Ausbildungsart</td><td><input type="text" id="ausbildungsart" name="ausbildungsart" value="'.$ausbildungsart.'" /></td></tr>';
echo '<tr><tr><td>Anmerkungen</td><td><textarea id="anmerkung" name="anmerkungen">'.$anmerkungen.'</textarea></td></tr>';
echo '<tr><tr><td>Studiengang</td><td><SELECT id="studiengang_kz" name="studiengang_kz">';
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbz');
foreach ($stg_obj->result as $row)
	echo '<OPTION value="'.$row->studiengang_kz.'" '.($row->studiengang_kz==$studiengang_kz?'selected':'').'>'.$row->kuerzel.'</OPTION>';
echo '</SELECT>';
echo '</td></tr>';
echo '<tr><tr><td></td><td>';

if($geburtsdatum=='' && $vorname=='' && $nachname=='')
	echo '<input type="submit" name="showagain" value="Vorschlag laden"</td></tr>';
else
	echo '<input type="submit" name="save" value="Speichern"</td></tr>';
?>

</table>
</td>
<td valign="top">
<!--Vorschlaege-->
<?php
//Vorschlaege laden
if($geburtsdatum!='')
{
	if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$geburtsdatum))
	{
		$where = " gebdatum='".$geburtsdatum."'";
	}
}

if($vorname!='' && $nachname!='')
{
	if($where!='')
		$where.=' OR';
	$where.=" (LOWER(vorname)='".strtolower($vorname)."' AND LOWER(nachname)='".strtolower($nachname)."')";
}

if($where!='')
{
	$qry = "SELECT * FROM public.tbl_person WHERE $where ORDER BY nachname, vorname, gebdatum";
	if($result = pg_query($conn, $qry))
	{
		echo '<table><tr><th></th><th>Nachname</th><th>Vorname</th><th>GebDatum</th><th>SVNR</th></tr>';
		while($row = pg_fetch_object($result))
		{
			echo '<tr><td><input type="radio" name="person_id" value="'.$row->person_id.'" onclick="disablefields(this)"></td><td>'."$row->nachname</td><td>$row->vorname</td><td>$row->gebdatum</td><td>$row->svnr</td></tr>";
		}
		echo '<tr><td><input type="radio" name="person_id" value="0" checked onclick="disablefields(this)"></td><td>keiner</td></tr>';
		echo '</table>';
	}
}
else 
	echo 'Vorschlag kann nicht erstellt werden';	

?>
</td>
</tr>
</table>
</form>
</body>
</html>