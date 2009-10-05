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

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();

$kurzbz=(isset($_GET['kurzbz'])?$_GET['kurzbz']:(isset($_POST['kurzbz'])?$_POST['kurzbz']:''));
if(empty($kurzbz))
	die('Gruppe wurde nicht &uuml;bergeben <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
if (isset($_POST['new']))
{
	$e=new benutzergruppe();
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
	$e=new benutzergruppe();
	$e->delete($_GET['uid'], $kurzbz);
}

$gruppe = new gruppe();
if(!$gruppe->load($kurzbz))
		die('Gruppe wurde nicht gefunden:'+$kurzbz);

?>
<html>
<head>
<title>Gruppen Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body>
<H2>Gruppe <?php echo $kurzbz ?></H2>

<?php
echo "<a href='einheit_menu.php?studiengang_kz=$gruppe->studiengang_kz'>Zurück zur &Uuml;bersicht</a><br><br>";

if(!$gruppe->generiert)
{
	echo '
	<FORM name="newpers" method="post" action="einheit_det.php">
	  <INPUT type="hidden" name="type" value="new">

  	<SELECT name="uid">';

	$qry = "SELECT * FROM campus.vw_benutzer ORDER BY nachname, vorname";

	$result = $db->db_query($qry);

	for ($i=0;$row = $db->db_fetch_object($result);$i++)
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
	$qry = "SELECT * FROM public.tbl_benutzergruppe JOIN public.tbl_benutzer USING(uid) JOIN public.tbl_person USING(person_id) WHERE".
	       " tbl_benutzergruppe.gruppe_kurzbz='".addslashes($kurzbz)."'".
	       " ORDER BY nachname, vorname";

	if($result = $db->db_query($qry))
	{
		$num_rows=$db->db_num_rows($result);
		echo "Anzahl: $num_rows";
		echo "<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
				<thead>
				<tr class='liste'>
					<th>UID</th>
					<th>Vornamen</th>
					<th>Nachname</th>
				</tr>
				</thead>
				<tbody>";

		for ($j=0; $row = $db->db_fetch_object($result);$j++)
		{
			echo "<tr>";
		    echo "<td>".$row->uid."</td>";
			echo "<td>".$row->vorname."</td>";
			echo "<td>".$row->nachname."</td>";
			if(!$gruppe->generiert)
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
