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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Ermoeglicht das Anmelden zu Freifaechern
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/basis_db.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die('Unable to Connect');

$user = get_uid();

//Aktuelles Studiensemester holen
$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();

?><!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<title><?php echo $p->t('freifach/freifaecherAnmeldung');?></title>
	</head>
	<body>
	<h1><?php echo $p->t('freifach/freifaecherAnmeldung');?></h1>
		<?php echo $p->t('freifach/markierenFreifachFuerAnmeldung');?>:
	<br />
<?php
//Wenn das Formular abgeschickt wurde
if (isset($_POST['submit']))
{
	//Wenn eine der Checkboxen angeklickt wurde
	if (isset($_POST['chkbox']))
	{
		$db->db_query('BEGIN');
		//Zuerst die alten Eintraege herausloeschen...
		$qry = "DELETE FROM campus.tbl_benutzerlvstudiensemester
			WHERE
				uid=".$db->db_add_param($user)."
				AND studiensemester_kurzbz=".$db->db_add_param($stsem);
		if (!$db->db_query($qry))
			die($p->t('freifach/fehlerBeimAktualisieren'));

		//...dann die angeklickten FF hinzufuegen
		foreach ($_POST['chkbox'] as $elem)
		{
			if (!is_numeric($elem))
			{
				$db->db_query('ROLLBACK');
				die('Unbekannter Parameter gefunden - Operation wird abgebrochen');
			}

			$qry = "INSERT INTO campus.tbl_benutzerlvstudiensemester(uid, lehrveranstaltung_id, studiensemester_kurzbz)
			VALUES(".$db->db_add_param($user).",".$db->db_add_param($elem).",".$db->db_add_param($stsem).");";
			if (!$db->db_query($qry))
			{
				$db->db_query('ROLLBACK');
				die($p->t('freifach/freifaecherNichtZugeteilt'));
			}
		}
		$db->db_query('COMMIT');
		echo "<b>".$p->t('freifach/datenErfolgreichAktualisiert')."!</b><br />";
	}
	else
	{
		//Wenn keine Checkbox angeklickt wurde, alle Eintraege herausloeschen
		$qry = "DELETE FROM campus.tbl_benutzerlvstudiensemester
			WHERE uid=".$db->db_add_param($user)." AND studiensemester_kurzbz=".$db->db_add_param($stsem);

		if (!$db->db_query($qry))
			die($p->t('freifach/fehlerBeimAktualisieren'));
		else
			echo "<b>".$p->t('freifach/datenErfolgreichAktualisiert')."!</b><br />";
	}
}

//Freifachzuteilungen holen
$qry = "SELECT * FROM campus.tbl_benutzerlvstudiensemester
WHERE uid = ".$db->db_add_param($user)." AND studiensemester_kurzbz=".$db->db_add_param($stsem);

if ($result = $db->db_query($qry))
{
	$ff = array();
	while ($row = $db->db_fetch_object($result))
	{
		$ff[] = $row->lehrveranstaltung_id;
	}
}
else
	echo $p->t('freifach/fehlerBeimAuslesen');

echo '<br />';
//Freifaecher laden
$lv_obj = new lehrveranstaltung();
if ($lv_obj->load_lva('0', null, null, true, null, 'bezeichnung'))
{
	$anz = count($lv_obj->lehrveranstaltungen);

	echo "<form method='POST'>";
	$i = 0;
	echo "<table><tr><td valign='top'>";
	foreach ($lv_obj->lehrveranstaltungen as $row)
	{
		//Auftrennen in eine zweite Spalte bei der haelfte der Eintraege
		if ($i == intval($anz / 2))
			echo "</td><td valign='top'>";

		if (in_array($row->lehrveranstaltung_id, $ff))
			$checked = "checked='true'";
		else
			$checked = '';

		//Wenn aktiv=false dann ist fuer dieses Lehrfach keine Anmeldung mehr moeglich
		if ($row->aktiv == false && $checked == '')
			$disabled = "disabled='true'";
		else
			$disabled = "";

		echo "\n<input type='checkbox' value='$row->lehrveranstaltung_id' name='chkbox[]' $checked $disabled >";
		echo "$row->bezeichnung<br />";
		$i++;
	}
	echo "</td></tr><tr><td></td><td>&nbsp;</td></tr>";
	echo "<tr><td></td><td><input type='submit' name='submit' value='".$p->t('global/speichern')."'></td></tr>";
	echo "</table>";
	echo "</form>";
}
else
{
	die($p->t('freifach/fehlerBeimAuslesenFreifach'));
}
?>
</body>
</html>
