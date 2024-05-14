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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
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

if (!$user = get_uid())
	die('Keine UID gefunden!  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/freifach', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite   <a href="javascript:history.back()">Zur&uuml;ck</a>');

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;

$stsem_obj = new studiensemester();

if (isset($_REQUEST["stsem"]))
	$stsem = $_REQUEST["stsem"];
else
	$stsem = $stsem_obj->getPrevious();

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

if (isset($_REQUEST["stsem_neu"]))
	$stsem_neu = $_REQUEST["stsem_neu"];
else
{
	if (!$stsem_neu = $stsem_obj->getakt())
		$stsem_neu = $stsem_obj->getaktorNext();
}	

if (isset($_REQUEST["gruppe_neu"]))
	$gruppe_neu = $_REQUEST["gruppe_neu"];
else
	$gruppe_neu = "";

if (isset($_REQUEST["semester_neu"]))
	$semester_neu = $_REQUEST["semester_neu"];
else
	$semester_neu = 1;

if (isset($_REQUEST["move"]) && $gruppe != "" && $_REQUEST["move"]== "=>" && $gruppe_neu!="")
{
	$b = new benutzergruppe();
	if ($b->load_uids($gruppe, $stsem))
	{
		foreach ($b->uids as $u)
		{			
				$bg = new benutzergruppe();
				$bg->uid = $u->uid;
				$bg->gruppe_kurzbz = $gruppe_neu;
				$bg->updateamum = null;
				$bg->updatevon=null;
				$bg->insertamum = date('Y-m-d H:i:s');
				$bg->insertvon = $user;
				$bg->studiensemester_kurzbz = $stsem_neu;
				$bg->new = true;
				$bg->save(true);
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
			//$spezgrpstr .= "<br><input type='checkbox' name='gruppe_".$uidliste->uid."'>".$uidliste->uid;
			$spezgrpstr .= "<br>".$uidliste->uid;
		}
	}
}

$spezgrp_neu = array();
$spezgrpstr_neu = "";
if ($gruppe_neu != "")
{
	$gu = new benutzergruppe();
	if ($gu->load_uids($gruppe_neu, $stsem_neu))
	{
		foreach ($gu->uids as $uidliste)
		{
			$spezgrp_neu[] = $uidliste->uid;
			//$spezgrpstr_neu .= "<br><input type='checkbox' name='gruppe_".$uidliste->uid."'>".$uidliste->uid;
			$spezgrpstr_neu .= "<br>".$uidliste->uid;
		}
	}
}

?>
<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script  type="text/javascript">

var count_neu = "<?php echo $spezgrpstr_neu; ?>";
var gruppe = "<?php echo $gruppe; ?>";
var gruppe_neu = "<?php echo $gruppe_neu; ?>";

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

function checkSubmit()
{
	if (count_neu != "")
	{
		alert("target group not empty!");		
		return false;
	}	
	if (gruppe == gruppe_neu)
	{
		alert("insert in same group not possible!");
		return false;	
	}
	if ((gruppe == "") || (gruppe_neu == ""))
	{
		alert("please choose group!");
		return false;	
	}
	else
		return true;
}

</script>
</head>
<body class="Background_main">
<?php
	
	echo "<H2>Freif&auml;cher Teilnehmer-Verwaltung</H2>";
	echo "<form name='auswahl' method='POST' action='freifach_studentenvorrueckung.php' onSubmit='return checkSubmit();'>";
	echo "<table>";
	echo "<tr><td colspan='3'><b>Studenten in andere Gruppen kopieren</b></td></tr>";	
	echo "<tr>";
	echo "<td>";
	
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
		if ($stsem == $strow->studiensemester_kurzbz)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$strow->studiensemester_kurzbz."'".$sel.">".$strow->studiensemester_kurzbz."</option>";

	}
	echo "</select>";
	echo "</td>";

	echo "<td>";
	echo "<input type='submit' name='move' value='=>'>";
	echo "</td>";

	echo "<td>";
	
	echo "<select name='semester_neu' onchange='document.auswahl.submit();'>";
	for ($i=0; $i<=10; $i++)
	{
		if ($semester_neu == $i)
			$sel = " selected";
		else
			$sel = "";		
		echo "<option value='".$i."'".$sel.">".$i."</option>";
	}
	echo "</select>";	
	
	echo "<select name='gruppe_neu' onchange='document.auswahl.submit();'>";
	echo "<option></option>";
	$grp_obj = new gruppe();
	if(!$grp_obj->getgruppe('0',$semester_neu,null,'true'))
		echo "$lv_obj->errormsg";

	foreach($grp_obj->result AS $row)
	{
		if ($gruppe_neu == $row->gruppe_kurzbz)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$row->gruppe_kurzbz."'".$sel.">".$row->gruppe_kurzbz."</option>";
	}
	
	echo "</select>";
	
	echo "<select name='stsem_neu' onchange='document.auswahl.submit();'>";;

	foreach($stsem_obj->studiensemester AS $strow)
	{
		if ($stsem_neu == $strow->studiensemester_kurzbz)
			$sel = " selected";
		else
			$sel = "";
		echo "	 <option value='".$strow->studiensemester_kurzbz."'".$sel.">".$strow->studiensemester_kurzbz."</option>";
	}
	
	echo "</select>";
	echo "</td>";	
	
	echo "</tr>";
	echo "<tr><td>";
	
	if ($gruppe != "")
	{
		echo $spezgrpstr;
	}
	echo "</td><td></td><td>";
	
	if ($gruppe_neu != "")
	{
		echo $spezgrpstr_neu;
	}	
	echo "</td>";
	echo "</tr></table>";
	echo "</form>";
?>
<br>
</body>
</html>