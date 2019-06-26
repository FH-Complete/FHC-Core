<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Aktualisiert eine bestehende Akte mit einer neuen Datei
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/akte.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin', null, 'suid'))
	die($rechte->errormsg);

echo '<html>
<head>
		<title>Akte aktualisieren</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>';

//Bei Upload des Bildes
if(isset($_POST['submitdatei']))
{
	if(isset($_FILES['datei']['tmp_name']))
	{
		$filename = $_FILES['datei']['tmp_name'];
		//File oeffnen
		$fp = fopen($filename,'r');
		//auslesen
		$content = fread($fp, filesize($filename));
		fclose($fp);
		//Base64 Codieren
		$content = base64_encode($content);
		$akte_id = $_POST['akte_id'];
		$signiert = isset($_POST['signiert']);
		$selfservice = isset($_POST['selfservice']);
		$db = new basis_db();

		$qry = "UPDATE public.tbl_akte 
				SET inhalt=".$db->db_add_param($content).",
					signiert= ".$db->db_add_param($signiert, FHC_BOOLEAN).",
					stud_selfservice= ".$db->db_add_param($selfservice, FHC_BOOLEAN)."
				WHERE akte_id=".$db->db_add_param($akte_id, FHC_INTEGER).";";

		if($db->db_query($qry))
			echo '<span class="ok">Saved!</span>';
		else
			echo '<span class="error">Failed</span>';
	}
}

if(isset($_GET['akte_id']) && is_numeric($_GET['akte_id']))
{
	$akte_id = $_GET['akte_id'];
}
else
	$akte_id = '';

$signiert = false;
$selfservice = false;
$aktetitel =
$akte_obj = new akte();
if ($akte_obj->load($akte_id))
{
	$signiert = $akte_obj->signiert;
	$selfservice = $akte_obj->stud_selfservice;
	$aktetitel = $akte_obj->bezeichnung.' ('.$akte_obj->titel.')';
}
else
{
	$aktetitel = '<span style="color: red">Es wurde keine Akte ID übergeben</span>';
}

echo '
<br>
Hier können bestehende Akten, die bereits im FAS archiviert wurden, mit neuen Dokumenten überschrieben werden.<br>
Dies ist hilfreich wenn zB Zeugnisse manuell korrigiert wurden.
<br><br>
	<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'">
	<table>
	<tr><td>Akte:</td><td>'.$aktetitel.'</td></tr>
	<tr><td>Neue Datei:</td><td><input type="file" name="datei" /></td></tr>
	<tr><td>AkteID:</td><td><input type="text" name="akte_id" value="'.$akte_id.'"/></td></tr>
	<tr><td>Signiert:</td><td><input type="checkbox" name="signiert" '.($signiert ? 'checked' : '').'/></td></tr>
	<tr><td>Selfservice:</td><td><input type="checkbox" name="selfservice" '.($selfservice ? 'checked' : '').'/></td></tr>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<tr><td></td><td><input type="submit" name="submitdatei" value="Upload" /></td></tr>

	</table>
	</form>
</body>
</html>';

?>
