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

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");


$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_REQUEST["stsem"]))
	$stsem = $_REQUEST["stsem"];
else
	$stsem = "WS2007";

if (isset($_REQUEST["lvid"]))
	$lvid = $_REQUEST["lvid"];
else
	$lvid = 0;

if (isset($_REQUEST["gruppe"]))
	$gruppe = $_REQUEST["gruppe"];
else
	$gruppe = "";

if (isset($_REQUEST["semester"]))
	$semester = $_REQUEST["semester"];
else
	$semester = 1;

if(!is_numeric($stg_kz))
	$stg_kz=0;

if (isset($_REQUEST["grp_in"]) && $gruppe != "")
{
	$b = new benutzerlvstudiensemester($conn);
	if ($b->get_all_uids($stsem, $lvid))
	{
		
		foreach ($b->uids as $u)
		{
			if (isset($_REQUEST["anmeldung_".$u->uid]))
			{
				$bg = new benutzergruppe($conn);
				$bg->uid = $u->uid;
				$bg->gruppe_kurzbz = $gruppe;
				$bg->updateamum = null;
				$bg->updatevon=null;
				$bg->insertamum = null;
				$bg->insertvon = $user;
				$bg->studiensemester_kurzbz = $stsem;
				$bg->new = 1;
				$bg->save(1);
			}
		}
	}
}

	if ($gruppe != "" && isset($_REQUEST["grp_aus"]))
	{
		$gu = new benutzergruppe($conn);
		if ($gu->load_uids($gruppe, $stsem))
		{
			foreach ($gu->uids as $uidliste)
			{
				if (isset($_REQUEST["gruppe_".$uidliste->uid]))
				{
					$bg = new benutzergruppe($conn);
					$bg->delete($uidliste->uid, $gruppe);
				}
			}
		}
	}//(uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, studiensemester_kurzbz)

?>

<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="Background_main">
<?php

	echo "<H2>Freif&auml;cher Teilnehmer-Verwaltung</H2>";
	echo "<form name='auswahl' method='POST' action='freifach.php'>";
	echo "<table>";
	echo "<tr><td><b>Freif&auml;cher</b></td<td><b>Gruppen</b></td</tr>";	
	echo "<tr>";
	echo "<td>";
	echo "<select name='lvid' onchange='document.auswahl.submit();'>";
	echo "<option></option>";
	$lv_obj = new lehrveranstaltung($conn);
	if(!$lv_obj->load_lva('0',null, null, true,null,'bezeichnung'))
		echo "$lv_obj->errormsg";

	foreach($lv_obj->lehrveranstaltungen AS $row)
	{
		if ($lvid == $row->lehrveranstaltung_id)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$row->lehrveranstaltung_id."'".$sel.">".$row->kurzbz." - ".$row->bezeichnung."</option>";

	}
	echo "</select>";
	
	
	echo "</td><td>";	
	
	echo "<select name='semester' onchange='document.auswahl.submit();'>";
	for ($i=0; $i<=10; $i++)
	{
		if ($semester == $i)
			$sel = " selected";
		else
			$sel = "";		
		echo "<option value='".$i."'".$sel.">".$i."</option>";
	}
	echo "</select>";	
	
	echo "<select name='gruppe' onchange='document.auswahl.submit();'>";
	echo "<option></option>";
	$grp_obj = new gruppe($conn);
	if(!$grp_obj->getgruppe('0',$semester,null,'true'))
		echo "$lv_obj->errormsg";

	foreach($grp_obj->result AS $row)
	{
		if ($gruppe == $row->gruppe_kurzbz)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$row->gruppe_kurzbz."'".$sel.">".$row->gruppe_kurzbz."</option>";

	}
	echo "</select>";
	
	echo "</td></tr>";
	echo "<tr>";
	echo "<td valign='top'>";
	
	if ($lvid > 0)
	{
		$b = new benutzerlvstudiensemester($conn);
		if ($b->get_all_uids($stsem, $lvid))
		{
			
			foreach ($b->uids as $u)
			{
				echo "<br><input type='checkbox' name='anmeldung_".$u->uid."'>".$u->uid;
				//echo "<br>".$u->uid;
			}
		}
	}
	echo "</td><td valign='top'>";
	
	if ($gruppe != "")
	{
		$gu = new benutzergruppe($conn);
		if ($gu->load_uids($gruppe, $stsem))
		{
			foreach ($gu->uids as $uidliste)
			{
				echo "<br><input type='checkbox' name='gruppe_".$uidliste->uid."'>".$uidliste->uid;
				//echo "<br>".$u->uid;
			}
		}
	}	
	
	echo "</td></tr>";
	echo "<tr><td>";
	echo "<br><input type='submit' name='grp_in' value='Auswahl in Gruppe einf&uuml;gen =>'>";
	echo "</td><td>";
	echo "<br><input type='submit' name='grp_aus' value=' <= Auswahl aus Gruppe l&ouml;schen'>";
	echo "</td></tr>";
	echo "</table>";
	echo "</form>";
?>


<br>
</body>
</html>