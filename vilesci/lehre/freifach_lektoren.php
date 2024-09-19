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
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerlvstudiensemester.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (!$user = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/freifach'))
	die('Sie haben keine Berechtigung fuer diese Seite  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$stsem_obj = new studiensemester();

if (isset($_REQUEST["stsem"]))
	$stsem = $_REQUEST["stsem"];
else
{
	if (!$stsem = $stsem_obj->getakt())
		$stsem = $stsem_obj->getaktorNext();
}
?>
<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
<script  type="text/javascript">
$(document).ready(function()
{
	$("#t1").tablesorter(
	{
		sortList: [[0,0],[2,0]],
		widgets: ["zebra"]
	});
});
function selectAll()
{
	var a = document.getElementById("anmeldungen");
	var checkboxen = a.getElementsByTagName("input");

	for (var i = 0; i < checkboxen.length; i++)
	{
		if (document.auswahl.toggle.checked == true)
			checkboxen[i].checked = true;
		else
			checkboxen[i].checked = false;
	}
}

</script>
</head>
<body class="Background_main">
<?php

	echo "<H2>Freif&auml;cher LektorInnen-Verwaltung</H2>";
	echo "<form name='auswahl' method='POST' action='freifach_lektoren.php'>";
	echo "<table>";

	echo "<tr><td>";

	echo "<select name='stsem' onchange='document.auswahl.submit();'>";;
	$stsem_obj->getAll();

	foreach($stsem_obj->studiensemester AS $strow)
	{
		if ($stsem == $strow->studiensemester_kurzbz)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$strow->studiensemester_kurzbz."'".$sel.">".$strow->studiensemester_kurzbz."</option>";

	}
	echo "</select>";

	echo "</td></tr>";
	echo "</table>";
	echo "<br>";
	echo "<table style='width:10%' id='t1' class='tablesorter'>";
	echo "<thead>";
	echo "<tr>";
	echo "<th>UID</th>";
	echo "<th>LV-ID</th>";
	echo "<th>Bezeichnung</th>";
	echo "<th title='Stundensatz'>Satz</th>";
	echo "<th title='Semesterstunden'>SS</th>";
	echo "<th title='Planstunden'>PS</th>";
	echo "<th>Summe</td>";
	echo "</tr></thead><tbody>";
	$emailstr = "";
	$emailarr = array();

	$qry = "SELECT
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid,  tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_lehrveranstaltung.bezeichnung, tbl_lehreinheitmitarbeiter.stundensatz,
				tbl_lehreinheitmitarbeiter.semesterstunden, tbl_lehreinheitmitarbeiter.planstunden
			FROM
				lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
			WHERE
				tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_lehrveranstaltung.studiengang_kz = 0 AND
				tbl_lehrveranstaltung.lehre = TRUE AND
				((tbl_lehreinheitmitarbeiter.stundensatz > 0 AND
				tbl_lehreinheitmitarbeiter.semesterstunden > 0) OR
				tbl_lehreinheitmitarbeiter.planstunden > 0) AND
				tbl_lehreinheit.studiensemester_kurzbz = ".$db->db_add_param($stsem)."
			ORDER BY mitarbeiter_uid, lehrveranstaltung_id;";

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			echo "<tr>";
			echo "<td><b>".$row->mitarbeiter_uid."</b></td>";
			echo "<td>".$row->lehrveranstaltung_id."</td>";
			echo "<td><nobr>".$row->bezeichnung."</nobr></td>";
			echo "<td>".$row->stundensatz."</td>";
			echo "<td>".$row->semesterstunden."</td>";
			echo "<td>".$row->planstunden."</td>";
			$gesamt = $row->semesterstunden * $row->stundensatz;
			echo "<td align='right'><b>".$gesamt."</b></td>";
			echo "</tr>";
			if (!in_array($row->mitarbeiter_uid, $emailarr))
				$emailarr[] = $row->mitarbeiter_uid;
		}
	}

	echo "</tbody></table>";
	echo "<br><br>";
	foreach ($emailarr as $mail)
		$emailstr .= $mail.'@'.DOMAIN.', ';

	echo "<a href='mailto:".$emailstr."'>Mail an alle LektorInnen</a><br>(".$emailstr.")";
	echo "</form>";
?>


<br>
</body>
</html>