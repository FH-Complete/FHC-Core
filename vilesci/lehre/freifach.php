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

#		require_once('../../include/basis_db.class.php');
#		if (!$db = new basis_db())
#			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerlvstudiensemester.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');


	if (!$user = get_uid())
			die('Keine UID gefunde !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
			


if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('lehre',0))
	die('Sie haben keine Berechtigung für diese Seite   <a href="javascript:history.back()">Zur&uuml;ck</a>');

$stsem_obj = new studiensemester();
if (isset($_REQUEST["stsem"]))
	$stsem = $_REQUEST["stsem"];
else
{
	if (!$stsem = $stsem_obj->getakt())
		$stsem = $stsem_obj->getaktorNext();
}
	
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
	$b = new benutzerlvstudiensemester();
	if ($b->get_all_uids($stsem, $lvid))
	{
		
		foreach ($b->uids as $u)
		{
			if (isset($_REQUEST["anmeldung_".$u->uid]))
			{
				$bg = new benutzergruppe();
				$bg->uid = $u->uid;
				$bg->gruppe_kurzbz = $gruppe;
				$bg->updateamum = null;
				$bg->updatevon=null;
				$bg->insertamum = date('Y-m-d H:i:s');
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
		$gu = new benutzergruppe();
		if ($gu->load_uids($gruppe, $stsem))
		{
			foreach ($gu->uids as $uidliste)
			{
				if (isset($_REQUEST["gruppe_".$uidliste->uid]))
				{
					$bg = new benutzergruppe();
					$bg->delete($uidliste->uid, $gruppe);
				}
			}
		}
	}

$spezgrp = array();
$spezgrpstr = "";
if ($gruppe != "")
{
	$gu = new benutzergruppe();
	if ($gu->load_uids($gruppe, $stsem))
	{
		foreach ($gu->uids as $uidliste)
		{
			$spezgrp[] = $uidliste->uid;
			$spezgrpstr .= "<br><input type='checkbox' name='gruppe_".$uidliste->uid."'>".$uidliste->uid;
			//echo "<br>".$u->uid;
		}
	}
}
//(uid, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, studiensemester_kurzbz)

?>

<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	
	echo "<H2>Freif&auml;cher Teilnehmer-Verwaltung</H2>";
	echo "<form name='auswahl' method='POST' action='freifach.php'>";
	echo "<table>";
	echo "<tr><td><b>Freif&auml;cher</b></td><td><b>Gruppen</b></td></tr>";	
	echo "<tr>";
	echo "<td>";
	echo "<select name='lvid' onchange='document.auswahl.submit();'>";
	echo "<option></option>";
	$lv_obj = new lehrveranstaltung();
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
	$grp_obj = new gruppe();
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
	
	echo "<select name='stsem' onchange='document.auswahl.submit();'>";;
	$stsem_obj->getAll();	

	foreach($stsem_obj->studiensemester AS $strow)
	{
		echo "	 <option value='".$strow->studiensemester_kurzbz."' " .($stsem==$strow->studiensemester_kurzbz?' selected="selected" ':'').">".$strow->studiensemester_kurzbz."</option>";
	}
	echo "</select>";
	echo "</td></tr>";
	echo "<tr>";
	echo "<td valign='top' id='anmeldungen'>";
	
	
	$anz = 0;
	if ($lvid > 0)
	{		
		$b = new benutzerlvstudiensemester();
		if ($b->get_all_uids($stsem, $lvid))
		{
			
			foreach ($b->uids as $u)
			{
				if (in_array($u->uid, $spezgrp))
					echo "<br><input type='checkbox' disabled>".$u->uid." - ".$u->nachname." ".$u->vorname;				
				else				
					echo "<br><input type='checkbox' name='anmeldung_".$u->uid."'>".$u->uid." - ".$u->nachname." ".$u->vorname;
				$anz++;
							
				//echo "<br>".$u->uid;
			}
		}
	}
	if ($anz > 0)
	{	
		
		echo "<br><hr><input type='checkbox' onclick='selectAll();' name='toggle'>de/select all *** Angemeldet: <b>".$anz."</b> Studierende ***";
		
	}	
	echo "</td><td valign='top'>";
	
	if ($gruppe != "")
	{
		echo $spezgrpstr;
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