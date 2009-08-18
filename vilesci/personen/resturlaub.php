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
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/resturlaub.class.php');
require_once('../../include/benutzerberechtigung.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Resturlaub</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
function gesamt()
{
	document.getElementById("gesamturlaub").value=parseInt(document.getElementById("resturlaubstage").value)+parseInt(document.getElementById("anspruch").value);
}
function conf_del()
{
	return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
}
</script>
</head>

<body class="background_main">
<h2>Resturlaubstage</h2>
<br><br>
';

$user = get_uid();
$uid=(isset($_GET['uid'])?$_GET['uid']:'');
if(isset($_GET['type']) && $_GET['type']=='edit' && isset($_GET['uid']))
{
	if(isset($_GET['del']) && isset($_GET['zeitsperre_id']))
	{
		//echo "<script type='text/javascript'>check=confirm('Wollen Sie diesen Eintrag wirklich löschen?');</script>";
		$qry="DELETE FROM campus.tbl_zeitsperre WHERE mitarbeiter_uid='".$_GET['uid']."' AND zeitsperre_id='".$_GET['zeitsperre_id']."' ;";
		if(!$db->db_query($qry))
		{
			die("Zeitsperren konnte nicht gelo&uml;scht werden!");
		}
	}
	$ma = new mitarbeiter();
	$ma->load($_GET['uid']);
	
	$resturlaub = new resturlaub();
	$resturlaub->load($_GET['uid']);
	echo 'Resturlaubstage von <b>'.$ma->nachname.' '.$ma->vorname.'</b>:<br><br>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'?type=save&uid='.$ma->uid.'" method="POST">
			<table>
				<tr>
					<td>Resturlaubstage</td>
					<td><input type="text" id="resturlaubstage" oninput="gesamt()" name="resturlaubstage" value="'.$resturlaub->resturlaubstage.'"></td>
				</tr>
				<tr>
					<td>Anspruch</td>
					<td><input type="text" id="anspruch" oninput="gesamt()" name="anspruch" value="'.$resturlaub->urlaubstageprojahr.'"></td>
				</tr>
				<tr>
					<td>Gesamturlaub</td>
					<td><input type="text" name="gesamturlaub" id="gesamturlaub" value="'.($resturlaub->resturlaubstage+$resturlaub->urlaubstageprojahr).'"></td>
				</tr>

				<tr>
					<td>Mehrarbeitsstunden</td>
					<td><input type="text" name="mehrarbeitsstunden" value="'.$resturlaub->mehrarbeitsstunden.'"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" value="Speichern"></td>
				</tr>
			</table>
		  </form>';
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if($rechte->isBerechtigt('admin', '0', 'suid'))
	{
		echo "<h3>Übersicht Zeitsperren</h3>";
		echo "<input type='button' onclick='parent.lv_detail.location=\"resturlaub_details.php?neu=true&uid=$uid\"' value='Neu'/>";
		echo"<table class='liste table-autosort:5 table-stripeclass:alternate table-autostripe'>
		<thead>
		<tr class='liste'>";
		echo "<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th class='table-sortable:default'>ID</th>
			  <th class='table-sortable:default'>Kurzbz</th>
			  <th class='table-sortable:default'>Bezeichnung</th>
			  <th class='table-sortable:default'>Von-Datum</th>
			  <th class='table-sortable:default'>Von-Stunde</th>
			  <th class='table-sortable:default'>Bis-Datum</th>
			  <th class='table-sortable:default'>Bis-Stunde</th>
			  <th class='table-sortable:default'>Vertretung</th>
			  <th class='table-sortable:default'>Erreichbarkeit</th>
			  <th class='table-sortable:default'>Freigabe</th>
			  <th class='table-sortable:default'>Freigabedatum</th>\n";
		echo "</tr></thead>";
		echo "<tbody>";
		$qry="SELECT * FROM campus.tbl_zeitsperre WHERE mitarbeiter_uid='".$uid."' ORDER BY vondatum DESC";
		if(!$result_urlaub = $db->db_query($qry))
			die("Zeitsperren nicht gefunden!");
		$num_rows=$db->db_num_rows($result_urlaub);
		if ($num_rows>0)
		{
			for($i=0;$i<$num_rows;$i++)
			{
				$row_urlaub=$db->db_fetch_object($result_urlaub);
				echo "<tr>";
				echo "<td><a href='resturlaub_details.php?zeitsperre_id=$row_urlaub->zeitsperre_id' target='lv_detail'>edit</a></td>";
				echo "<td><a href='".$_SERVER['PHP_SELF']."?type=edit&del=true&uid=$uid&zeitsperre_id=$row_urlaub->zeitsperre_id' onclick='return conf_del()' target='uebersicht'>delete</a></td>";
				echo "<td>".$row_urlaub->zeitsperre_id."</td>";
				echo "<td>".$row_urlaub->zeitsperretyp_kurzbz."</td>";
				echo "<td>".$row_urlaub->bezeichnung."</td>";
				echo "<td>".$row_urlaub->vondatum."</td>";
				echo "<td>".$row_urlaub->vonstunde."</td>";
				echo "<td>".$row_urlaub->bisdatum."</td>";
				echo "<td>".$row_urlaub->bisstunde."</td>";
				echo "<td>".$row_urlaub->vertretung_uid."</td>";
				echo "<td>".$row_urlaub->erreichbarkeit_kurzbz."</td>";
				echo "<td>".$row_urlaub->freigabevon."</td>";
				echo "<td>".$row_urlaub->freigabeamum."</td>";
				echo "</td></tr>";
			}
		}
		else
			echo "<tr><td colspan=5>Kein Eintrag gefunden!</td></tr>";
	}
	exit;
}

if(isset($_GET['type']) && $_GET['type']=='save')
{
	$resturlaub = new resturlaub();
	
	if($resturlaub->load($_GET['uid']))
	{
		$resturlaub->new = false;
	}
	else 
	{
		$resturlaub->new = true;
		$resturlaub->insertamum = date('Y-m-d H:i:s');
		$resturlaub->insertvon = $user;
		$resturlaub->mitarbeiter_uid=$_GET['uid'];
	}
	
	$resturlaub->mehrarbeitsstunden = $_POST['mehrarbeitsstunden'];
	if($resturlaub->mehrarbeitsstunden=='')
		$resturlaub->mehrarbeitsstunden=0;
	$resturlaub->resturlaubstage = $_POST['resturlaubstage'];
	if($resturlaub->resturlaubstage=='')
		$resturlaub->resturlaubstage=0;
	$resturlaub->urlaubstageprojahr = $_POST['anspruch'];
	if($resturlaub->urlaubstageprojahr=='')
		$resturlaub->urlaubstageprojahr=0;
	$resturlaub->updateamum = date('Y-m-d H:i:s');
	$resturlaub->updatevon = $user;
	
	if($resturlaub->save())
	{
		echo '<b>Daten wurden gespeichert</b><br><br>';
	}
	else 
	{
		die('Fehler beim Speichern der Daten: '.$resturlaub->errormsg.'<br><a href="javascript:history.back()">Zurück</a><br>');
	}
}

$qry = "SELECT * FROM campus.vw_mitarbeiter LEFT JOIN campus.tbl_resturlaub ON(uid=mitarbeiter_uid) 
		WHERE aktiv AND fixangestellt ORDER BY nachname, vorname";

echo "<table class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>";
echo "<thead>
		<tr>
			<th class='table-sortable:default'>Nachname</th>
			<th class='table-sortable:default'>Vorname</th>
			<th class='table-sortable:default'>Resturlaubstage</th>
			<th class='table-sortable:default'>Anspruch</th>
			<th class='table-sortable:default'>Gesamturlaub</th>
			<th class='table-sortable:default'>Mehrarbeitsstunden</th>
			<th class='table-sortable:default'>Aktion</th>
		</tr>
	</thead>";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo '<tr>';
		echo "<td>$row->nachname</td>";
		echo "<td>$row->vorname</td>";
		echo "<td align='center'>$row->resturlaubstage</td>";
		echo "<td align='center'>$row->urlaubstageprojahr</td>";
		echo "<td align='center'>".($row->resturlaubstage+$row->urlaubstageprojahr)."</td>";
		echo "<td align='center'>$row->mehrarbeitsstunden</td>";
		echo "<td><a href='".$_SERVER['PHP_SELF']."?type=edit&uid=$row->uid'>Bearbeiten</a></td>";
		echo '</tr>';
	}
}
echo '</table>';
?>

</body>
</html>