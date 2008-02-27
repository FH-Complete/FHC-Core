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
require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/resturlaub.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
	die("Fehler beim Connecten zur Datenbank");

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Resturlaub</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
function gesamt()
{
	document.getElementById("gesamturlaub").value=parseInt(document.getElementById("resturlaubstage").value)+parseInt(document.getElementById("anspruch").value);
}
</script>
</head>

<body class="background_main">
<h2>Resturlaubstage</h2>
<br><br>
';

$user = get_uid();


if(isset($_GET['type']) && $_GET['type']=='edit' && isset($_GET['uid']))
{
	$ma = new mitarbeiter($conn);
	$ma->load($_GET['uid']);
	
	$resturlaub = new resturlaub($conn);
	$resturlaub->load($_GET['uid']);
	echo 'Resturlaubstabe von <b>'.$ma->nachname.' '.$ma->vorname.'</b>:<br><br>';
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
	exit;
}

if(isset($_GET['type']) && $_GET['type']=='save')
{
	$resturlaub = new resturlaub($conn);
	
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
	$resturlaub->udpateamum = date('Y-m-d H:i:s');
	$resturlaub->udpatevon = $user;
	
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
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
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