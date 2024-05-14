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
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('mitarbeiter'))
	die($rechte->errormsg);

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

?>
<html>
<head>
<title>Mitarbeiter Übersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>

<body class="background_main">
<h2>Mitarbeiter &Uuml;bersicht</h2><br>

<?php
if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else
	$searchstr = '';

echo '
	<form accept-charset="UTF-8" name="search" method="GET">
  		Bitte Suchbegriff eingeben:
  		<input type="text" name="searchstr" size="30" value="'.$db->convert_html_chars($searchstr).'">
  		<input type="submit" value="Suchen">
  	</form>';
if(!isset($_GET['searchstr']))
	exit;

	$qry = "SELECT vw_mitarbeiter.*, tbl_standort.kurzbz as standort_kurzbz FROM campus.vw_mitarbeiter LEFT JOIN public.tbl_standort USING(standort_id)";
	if(!empty($searchstr))
			$qry.=" where nachname||' '||vorname ~* '".$db->db_escape($searchstr)."' OR vorname||' '||nachname ~* '".$db->db_escape($searchstr)."' OR uid ~* '".$db->db_escape($searchstr)."'  ";
	$qry .= " ORDER BY nachname, vorname";

	if($result = $db->db_query($qry))
	{
		echo "<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>";
		echo "<thead>
				<tr>
					<th class='table-sortable:default'>UID</th>
					<th class='table-sortable:default'>Titel</th>
					<th class='table-sortable:default'>Vorname</th>
					<th class='table-sortable:default'>Nachname</th>
					<th class='table-sortable:default'>Fix</th>
					<th class='table-sortable:default'>Lkt</th>
					<th class='table-sortable:default'>Raum</th>
					<th class='table-sortable:default'>Standort</th>
					<th class='table-sortable:default'>Tel</th>
					<th class='table-sortable:default'>eMail</th>
					<th colspan='2'>Aktion</th>
				</tr>
			</thead><tbody>";

		for ($i=0; $row=$db->db_fetch_object($result); $i++)
		{
			echo '<tr>';
			if((isset($fix) || isset($lek))&& isset($uid) && $uid==$row->uid) //Anker setzen
				echo "<td nowrap>".$row->uid."<a name='anker1'></a></td>";
			else
				echo "<td nowrap>".$row->uid."</td>";

			echo "<td nowrap>".$row->titelpre."</td>";
			echo "<td nowrap>".$row->vorname."</td>";
			echo "<td nowrap>".$row->nachname."</td>";
			echo "<td nowrap>".($row->fixangestellt=='t'?'Ja':'Nein')."</td>";
			echo "<td nowrap>".($row->lektor=='t'?'Ja':'Nein')."</td>";
			echo "<td nowrap>".$row->ort_kurzbz."</td>";
			echo "<td nowrap>".$row->standort_kurzbz."</td>";
			echo "<td nowrap>".$row->telefonklappe."</td>";

			$email=$row->uid.'@'.DOMAIN;
			echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
            if($rechte->isBerechtigt('student/stammdaten', null, 's') || $rechte->isBerechtigt('mitarbeiter/stammdaten', null, 's'))
                echo "<td nowrap class='button'><a href='personen_details.php?uid=".$row->uid."'>Edit</a></td>";
			echo "<td nowrap class='button'>";
			if ($row->lektor)
			{
				echo "<a href='zeitwunsch.php?uid=".$row->uid."' class='linkblue'>Zeitwunsch</a>";
			}
			echo '</td>';
			echo '</tr>';
		}
		echo "</table>";
	}
	else
		echo "Fehler beim Laden der Mitarbeiter: ".$db->db_last_error();
?>

</body>
</html>
