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
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/gruppe.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die('Fehler beim Aufbau der Datenbankconnection');

$user=get_uid();
$kurzbz=(isset($_GET['kurzbz'])?$_GET['kurzbz']:$_POST['kurzbz']);

if (isset($_POST['new']))
{
	$e=new benutzergruppe($conn);
	$e->new=true;
	$e->gruppe_kurzbz=$kurzbz;
	$e->updateamum = date('Y-m-d H:i:s');
	$e->updatevon = $user;
	$e->insertamum = date('Y-m-d H:i:s');
	$e->insertvon = $user;
	$e->uid = $_POST['uid'];
	$e->save();
}
else if (isset($_GET['type']) && $_GET['type']=='delete')
{
	$e=new benutzergruppe($conn);
	$e->delete($_GET['uid'], $kurzbz);
}
$gruppe = new gruppe($conn);
if(!$gruppe->load($kurzbz))
	die('Gruppe wurde nicht gefunden:'+$kurzbz);

?>
<html>
<head>
<title>Gruppen Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Gruppe <?php echo $kurzbz ?></H1>

<?php
echo "<a href='einheit_menu.php?studiengang_kz=$gruppe->studiengang_kz'>Zurück zur &Uuml;bersicht</a><br><br>";

if(!$gruppe->generiert)
{
	echo '
	<FORM name="newpers" method="post" action="einheit_det.php">
	  <INPUT type="hidden" name="type" value="new">

  	<SELECT name="uid">';

	$qry = "SELECT * FROM campus.vw_benutzer ORDER BY nachname, vorname";

	$result = pg_query($conn, $qry);

	for ($i=0;$row = pg_fetch_object($result);$i++)
	{
		echo "<option value=\"".$row->uid."\">".$row->nachname." ".$row->vorname." - ".$row->uid."</option>";
	}

	echo '
	  </SELECT>
	  <INPUT type="hidden" name="kurzbz" value="'.$kurzbz.'">
	  <INPUT type="submit" name="new" value="Hinzuf&uuml;gen">
	</FORM>
	<HR>';
}
	$qry = "SELECT * FROM public.tbl_benutzergruppe, public.tbl_benutzer, public.tbl_person WHERE".
	       " tbl_benutzergruppe.gruppe_kurzbz='".addslashes($kurzbz)."' AND".
	       " tbl_benutzergruppe.uid = tbl_benutzer.uid AND tbl_benutzer.person_id=tbl_person.person_id ORDER BY nachname, vorname";

	if($result = pg_query($conn, $qry))
	{
		$num_rows=pg_num_rows($result);
		echo "Anzahl: $num_rows";
		echo '<table class="liste">
			<tr class="liste"><th>UID</th><th>Vornamen</th><th>Nachname</th></tr>';

		for ($j=0; $row = pg_fetch_object($result);$j++)
		{
			echo "<tr class='liste".($j%2)."'>";
		    echo "<td>".$row->uid."</td>";
			echo "<td>".$row->vorname."</td>";
			echo "<td>".$row->nachname."</td>";
			if(!$gruppe->generiert)
				echo '<td class="button"><a href="einheit_det.php?uid='.$row->uid.'&type=delete&kurzbz='.$kurzbz.'">Delete</a></td>';
		    echo "</tr>\n";
		}
	}
	else
		die('Fehler beim Laden der Benutzer');

?>
</table>
</body>
</html>
