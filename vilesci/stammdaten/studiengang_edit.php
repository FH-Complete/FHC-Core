<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Seite zur Aenderung des Studiengangsnamens
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/benutzer.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Studiengang - Details</title>
<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<?php
$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('assistenz'))
	die($rechte->errormsg);

if(isset($_GET['studiengang_kz']))
	$stg_kz = $_GET['studiengang_kz'];
else
	$stg_kz='';

if(isset($_GET['action']) && $_GET['action']=='save')
{
	$studiengang_kz = $_POST['studiengang_kz'];
	$studiengang = new studiengang();
	$studiengang->load($studiengang_kz);
	if(!$rechte->isBerechtigt('assistenz', $studiengang->oe_kurzbz, 'suid'))
		die($rechte->errormsg);

	$bezeichnung = $_POST['bezeichnung'];
	$english = $_POST['english'];
	$max_semester = $_POST['max_semester'];
	$orgform_kurzbz = $_POST['orgform_kurzbz'];
	$stg_kz=$studiengang_kz;

	$stg = new studiengang();
	if($stg->load($studiengang_kz))
	{
		$stg->bezeichnung = $bezeichnung;
		$stg->english = $english;
		$stg->max_semester = $max_semester;
		$stg->orgform_kurzbz = $orgform_kurzbz;
		$stg->new=false;
		if($stg->save())
			echo '<span class="ok">Erfolgreich geändert</span>';
		else
			echo '<span clasS="error">Fehler beim Speichern: '.$stg->errormsg.'</span>';

	}

	if(in_array($studiengang_kz,array(334,257)))
	{
		$benutzerfunktion = new benutzerfunktion();
		$benutzerfunktion->getOeFunktionen($stg->oe_kurzbz, 'Leitung');

		foreach($benutzerfunktion->result as $row)
		{
			if(isset($_POST['ltg_'.$row->benutzerfunktion_id]))
			{
				// Leitung wird gesetzt
				if($row->datum_bis!='')
				{
					$row->datum_bis='';
					$row->updateamum = date('Y-m-d H:i:s');
					$row->updatevon = $user;
					$row->save(false);
				}
			}
			else
			{
				// Leitung wird entfernt
				if($row->datum_bis=='' || $row->datum_bis>date('Y-m-d'))
				{
					$row->datum_bis=date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
					$row->updateamum = date('Y-m-d H:i:s');
					$row->updatevon = $user;
					$row->save(false);
				}
			}

		}
	}
}

$stg = new studiengang();
$stg_arr = $rechte->getStgKz('assistenz');
$stg->loadArray($stg_arr,'typ, kurzbz',true);

echo '<form method="GET">
Studiengang: <SELECT name="studiengang_kz">';
foreach($stg->result as $row)
{
	if (in_array($row->studiengang_kz, $stg_arr))
	{
		if($stg_kz=='')
			$stg_kz=$row->studiengang_kz;
	
		if($stg_kz==$row->studiengang_kz)
			$selected='selected';
		else
			$selected='';
	
		echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';
	}
}
echo '</SELECT><input type="submit" value="Anzeigen" /></form>';

$stg = new studiengang();
$stg->load($stg_kz);

echo 'Studiengang: '.$stg->kuerzel;
echo '<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="POST">
<input type="hidden" name="studiengang_kz" value="'.$stg->convert_html_chars($stg->studiengang_kz).'" />
<table>
<tr>
	<td>Bezeichnung</td>
	<td><input type="text" name="bezeichnung" value="'.$stg->convert_html_chars($stg->bezeichnung).'" size="50"/></td>
</tr>
<tr>
	<td>Bezeichnung Englisch</td>
	<td><input type="text" name="english" value="'.$stg->convert_html_chars($stg->english).'" size="50"/></td>
</tr>
<tr>
	<td>Max Semester</td>
	<td><input type="text" name="max_semester" value="'.$stg->convert_html_chars($stg->max_semester).'" size="2" maxlenght="2"/></td>
</tr>
<tr>
	<td>Organisationsform</td>
	<td>
		<SELECT name="orgform_kurzbz">';
$orgform = new organisationsform();
$orgform->getAll();

foreach($orgform->result as $row)
{
	if($row->orgform_kurzbz == $stg->orgform_kurzbz)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$row->orgform_kurzbz.'" '.$selected.'>'.$row->bezeichnung.'</OPTION>';
}

echo '
		</SELECT>
	</td>
</tr>';

if(in_array($stg->studiengang_kz, array(334,257)))
{
	// Studiengang MIT / MSC und BIF kann auch die Leitung aktivieren/deaktivieren
	echo '<tr>';
	echo '<td valign="top">Leitung</td>';
	echo '<td>';

	$benutzerfunktion = new benutzerfunktion();
	$benutzerfunktion->getOeFunktionen($stg->oe_kurzbz, 'Leitung');

	foreach($benutzerfunktion->result as $row)
	{
		if($row->datum_bis=='' || $row->datum_bis>date('Y-m-d'))
			$checked='checked="checked"';
		else
			$checked='';

		echo '<input type="checkbox" name="ltg_'.$row->benutzerfunktion_id.'" '.$checked.'>';
		$bn = new benutzer();
		$bn->load($row->uid);
		echo $bn->vorname.' '.$bn->nachname.'<br>';
	}

	echo '</td>';
	echo '</tr>';
}

echo '
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
';
?>

</body>
</html>
