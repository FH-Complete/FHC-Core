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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerlvstudiensemester.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/studiensemester.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");


$user = get_uid();



$stsem_obj = new studiensemester($conn);

if (isset($_REQUEST["stsem"]))
	$stsem = $_REQUEST["stsem"];
else
	$stsem = $stsem_obj->getakt();





?>

<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script  type="text/javascript">
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
	
	echo "<table border='1'>";
	$emailstr = "";
	$emailarr = array();

	$qry = "select tbl_lehreinheitmitarbeiter.mitarbeiter_uid,  tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.bezeichnung, tbl_lehreinheitmitarbeiter.stundensatz, tbl_lehreinheitmitarbeiter.semesterstunden from lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung where tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id and tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id and tbl_lehrveranstaltung.studiengang_kz = 0 and tbl_lehrveranstaltung.lehre = TRUE and tbl_lehreinheitmitarbeiter.stundensatz > 0 and tbl_lehreinheitmitarbeiter.semesterstunden > 0 and tbl_lehreinheit.studiensemester_kurzbz = '".$stsem."' order by mitarbeiter_uid, lehrveranstaltung_id;";

		if($result = pg_query($conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				echo "<tr>";				
				echo "<td><b>".$row->mitarbeiter_uid."</b></td>";
				echo "<td>".$row->lehrveranstaltung_id."</td>";
				echo "<td>".$row->bezeichnung."</td>";
				echo "<td>".$row->stundensatz."</td>";
				echo "<td>".$row->semesterstunden."</td>";
				$gesamt = $row->semesterstunden * $row->stundensatz;
				echo "<td align='right'><b>".$gesamt."</b></td>";
				echo "</tr>";
				if (!in_array($row->mitarbeiter_uid, $emailarr))
					$emailarr[] = $row->mitarbeiter_uid;
				
			}
		}	


	echo "</table>";
	echo "<br><br>";
	foreach ($emailarr as $mail)
		$emailstr .= $mail."@technikum-wien.at, ";
		
	echo "<a href='mailto:".$emailstr."'>Mail an alle Lektoren</a><br>(".$emailstr.")";
	echo "</form>";
?>


<br>
</body>
</html>