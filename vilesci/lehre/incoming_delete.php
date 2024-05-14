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
/**
 *	@updated 11.09.2012 kindl
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/lvplan', null, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

//Spezialgruppen für DropDown
$sql_query="SELECT gruppe_kurzbz FROM public.tbl_gruppe WHERE studiengang_kz=10006 AND aktiv=true AND sichtbar=true ORDER BY gruppe_kurzbz";
//echo $sql_query."<br>";
$result_incgrp=$db->db_query($sql_query);
if(!$result_incgrp)
	die("Keine Incoming-Gruppen gefunden! ".$db->db_last_error());

$incgrp=(isset($_REQUEST['incgrp'])?$_REQUEST['incgrp']:'');
$lehreinheit_id=(isset($_REQUEST['lehreinheit_id'])?$_REQUEST['lehreinheit_id']:'');
$type=(isset($_REQUEST['type'])?$_REQUEST['type']:'');

?>
<html>
<head>
<title>Incoming löschen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H2>Incoming löschen</H2>
<hr>
<form name="stdplan" method="post" action="incoming_delete.php">

	<p>
	Löscht einen Incoming aus <strong>beiden</strong> LV-Plan Tabellen und auch die <strong>Gruppenzuteilung im FAS</strong>.<br/><br/>

	Lehreinheit aus der der Incoming gelöscht werden soll:
    <input type="text" name="lehreinheit_id" size="6" maxlength="10" value="<?php echo $lehreinheit_id; ?>"><br/>
	</p>
	<p>Gruppe des Incomings, die gelöscht werden soll:
	<select name="incgrp">
		<option value=NULL>*</option>
      <?php
		if ($result_incgrp)
				$num_rows=$db->db_num_rows($result_incgrp);
		else
			$num_rows=0;
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_incgrp, $i);
			if ($incgrp==$row->gruppe_kurzbz)
				echo "<option value=\"$row->gruppe_kurzbz\" selected>$row->gruppe_kurzbz</option>";
			else
				echo "<option value=\"$row->gruppe_kurzbz\">$row->gruppe_kurzbz</option>";
		}
		?>
    </select>
    <br/>
  </p>

  <p>
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Löschen">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	echo "Auftrag wird ausgefuehrt...<br>";
	if (!$error)
	{
			$sql_query="DELETE FROM lehre.tbl_stundenplandev
						WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)."
						AND gruppe_kurzbz=".$db->db_add_param($_POST['incgrp']).";

						DELETE FROM lehre.tbl_stundenplan
						WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)."
						AND gruppe_kurzbz=".$db->db_add_param($_POST['incgrp']).";

						DELETE FROM lehre.tbl_lehreinheitgruppe
						WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'])."
						AND gruppe_kurzbz=".$db->db_add_param($_POST['incgrp']).";";
			//echo $sql_query;
			$result=$db->db_query($sql_query);
			if(!$result)
			{
				echo $db->db_last_error()."<br>";
				$error=true;
			}
			else
				echo "<strong>Lehreinheit:</strong> ".$db->convert_html_chars($_POST['lehreinheit_id'])." - <strong>Gruppe:</strong> ".$db->convert_html_chars($_POST['incgrp'])." -- <strong>Gelöscht!</strong><br>";

		if (!$error)
			echo "<br><font style='color:green'><strong>Gruppe erfolgreich gelöscht</strong></font><br>";
		else
			echo "<br><font style='color:red'><strong>Es ist ein Fehler aufgetreten!</strong></font><br>";
	}
}
?>
</body>
</html>
