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
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();
$errormsg = '';

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('lehre/gruppe:begrenzt',null,'s'))
	die($rechte->errormsg);

$kurzbz=(isset($_GET['kurzbz'])?$_GET['kurzbz']:(isset($_POST['kurzbz'])?$_POST['kurzbz']:''));
$searchItems=(isset($_GET['searchItems'])?$_GET['searchItems']:(isset($_POST['searchItems'])?$_POST['searchItems']:''));
if(empty($kurzbz))
	die('Gruppe wurde nicht &uuml;bergeben <a href="javascript:history.back()">Zur&uuml;ck</a>');

if (isset($_POST['new']))
{
	$benutzer = new benutzer();
	if ($benutzer->load($_POST['uid']))
	{
		if(!$rechte->isBerechtigt('lehre/gruppe',null,'sui'))
			die($rechte->errormsg);

		$e = new benutzergruppe();
		$uid = $_POST['uid'];
		if (!$e->load($uid, $kurzbz))
		{
			$e->new=true;
			$e->gruppe_kurzbz = $kurzbz;
			$e->updateamum = date('Y-m-d H:i:s');
			$e->updatevon = $user;
			$e->insertamum = date('Y-m-d H:i:s');
			$e->insertvon = $user;
			$e->uid = $uid;
			if(!$e->save())
				die($e->errormsg);
		}
		else
		{
			$errormsg = '<span class="error">Diese Person ist bereits der Gruppe zugeteilt</span>';
		}
	}
	else
		$errormsg = '<span class="error">UID '.$_POST['uid'].' wurde nicht gefunden</span>';
}
else if (isset($_GET['type']) && $_GET['type']=='delete')
{
	if(!$rechte->isBerechtigt('lehre/gruppe',null,'suid'))
		die($rechte->errormsg);

	$e=new benutzergruppe();
	$e->delete($_GET['uid'], $kurzbz);
}

$gruppe = new gruppe();
if(!$gruppe->load($kurzbz))
	die('Gruppe wurde nicht gefunden:'+$kurzbz);

?>
<!DOCTYPE html>
<html>
<head>
<title>Gruppen Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../skin/jquery.css" type="text/css">
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
</head>
<body>
<H2>Gruppe <?php echo $kurzbz ?></H2>

<?php
echo "<a href='einheit_menu.php?studiengang_kz=$gruppe->studiengang_kz&searchItems=$searchItems'>Zurück zur &Uuml;bersicht</a><br><br>";
echo $errormsg;
$generiertegruppe = $gruppe->generiert;
if(!$gruppe->generiert)
{
	echo '
	<FORM name="newpers" method="post" action="einheit_det.php">
	  Name: <INPUT type="hidden" name="type" value="new">
		<input type="text" name="uid" id="uid" autofocus="autofocus" />
		<script type="text/javascript">
		$(document).ready(function()
		{
			$("#uid").autocomplete({
				source: "einheit_autocomplete.php?work=searchUser",
				minLength:3,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].uid;
						ui.content[i].label=ui.content[i].uid+" - "+ui.content[i].vorname+" "+ui.content[i].nachname;
					}
				},
				select: function(event, ui)
				{
					ui.item.value=ui.item.uid;
				}
			});
		});
		$("#uid").focus();
		</script>
		 <INPUT type="hidden" name="kurzbz" value="'.$kurzbz.'">
		 <INPUT type="hidden" name="searchItems" value="'.$searchItems.'">
	  <INPUT type="submit" name="new" value="Hinzuf&uuml;gen">
	</FORM>
	<HR>
		';
}
else
{
	echo '	Name: <input type="text" name="uid" id="uid" disabled="disabled"/>
			<INPUT type="submit" name="new" value="Hinzuf&uuml;gen"  disabled="disabled">
			<span>Bei generierten Gruppen können keine Personen manuell hinzugefügt werden.</span>
			<HR>';
}

	$gruppe = new gruppe();

	if($gruppe->loadUser($kurzbz))
	{
		$num_rows=count($gruppe->result);
		echo "Anzahl: $num_rows";
		echo '<script>
		$(document).ready(function()
		{
			$("#usertabelle").tablesorter(
			{
				sortList: [[0,0],[1,0]],
				widgets: ["zebra"]
			});
		});
		</script>';
		echo '<table id="usertabelle" class="tablesorter">
				<thead>
				<tr>
					<th>Nachname</th>
					<th>Vornamen</th>
					<th>UID</th>
				</tr>
				</thead>
				<tbody>';

		foreach($gruppe->result as $row)
		{
			echo "<tr>";
			echo "<td>".$row->nachname."</td>";
			echo "<td>".$row->vorname."</td>";
			echo "<td>".$row->uid."</td>";
			if(!$generiertegruppe && $rechte->isBerechtigt('lehre/gruppe',null,'suid'))
				echo '<td class="button"><a href="einheit_det.php?uid='.$row->uid.'&type=delete&kurzbz='.$kurzbz.'">Delete</a></td>';
			echo "</tr>\n";
		}
		echo '</tbody>
		</table>';
	}
	else
		die('Fehler beim Laden der Benutzer');

?>

</body>
</html>
