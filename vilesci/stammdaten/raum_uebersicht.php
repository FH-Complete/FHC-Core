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
require_once('../../include/ort.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/ort'))
	die('Sie haben keine Rechte fuer diese Seite');

if (isset($_GET["toggle"]))
{
	if(!$rechte->isBerechtigt('basis/ort', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	if ($_GET["rlehre"] != "" && $_GET["rlehre"] != NULL)
	{
		$rlehre = $_GET["rlehre"];
		$sg_update = new ort();
		$qry = "UPDATE public.tbl_ort SET lehre = NOT lehre WHERE ort_kurzbz='".$rlehre."';";
		if(!$db->db_query($qry))
		{
			die('Fehler beim Speichern des Datensatzes');
		}	
	}
	if ($_GET["rres"] != "" && $_GET["rres"] != NULL)
	{
		$rres = $_GET["rres"];
		$sg_update = new ort();
		$qry = "UPDATE public.tbl_ort SET reservieren = NOT reservieren WHERE ort_kurzbz='".$rres."';";
		if(!$db->db_query($qry))
		{
			die('Fehler beim Speichern des Datensatzes');
		}	
	}
	if ($_GET["raktiv"] != "" && $_GET["raktiv"] != NULL)
	{
		$raktiv = $_GET["raktiv"];
		$sg_update = new ort();
		$qry = "UPDATE public.tbl_ort SET aktiv = NOT aktiv WHERE ort_kurzbz='".$raktiv."';";
		if(!$db->db_query($qry))
		{
			die('Fehler beim Speichern des Datensatzes');
		}	
	}
}

$sg = new ort();
if (!$sg->getAll('ort_kurzbz',false))
    die($sg->errormsg);

//$htmlstr = "<table class='liste sortable'>\n";
$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table id='t1' class='liste table-autosort:0 table-stripeclass:alternate table-autostripe'>\n";
$htmlstr .= "   <thead><tr class='liste'>\n";
$htmlstr .= "    <th class='table-sortable:default' onmouseup='document.formular.check.value=0'>Kurzbezeichnung</th>
		<th class='table-sortable:default'>Bezeichnung</th>
		<th class='table-sortable:default'>Planbezeichnung</th>
		<th class='table-sortable:numeric'>Max. Person</th>
		<th>Lehre</th>
		<th>Reservieren</th>
		<th>Aktiv</th>
		<th class='table-sortable:numeric'>Kosten</th>
		<th class='table-sortable:numeric'>Stockwerk</th>";
$htmlstr .= "   </tr></thead><tbody>\n";
$i = 0;
foreach ($sg->result as $twraum)
{
    //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	$htmlstr .= "   <tr>\n";
	$htmlstr .= "       <td><a href='raum_details.php?ort_kurzbz=".$twraum->ort_kurzbz."' target='detail_raum'>".$twraum->ort_kurzbz."</a></td>\n";
	$htmlstr .= "       <td>".$twraum->bezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->planbezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->max_person."</td>\n";
	if($twraum->lehre=='t')
	{
		$lehrebild = "true.png";	
	}
	else
	{
		$lehrebild = "false.png";
	}
	$lehrelink = "?toggle=true&rlehre=".$twraum->ort_kurzbz."&rres=NULL&raktiv=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$lehrelink."'><img src='../../skin/images/".$lehrebild."' height='20'></a></td>\n";
	if($twraum->reservieren=='t')
	{
		$resbild = "true.png";	
	}
	else
	{
		$resbild = "false.png";
	}
	$reslink = "?toggle=true&rres=".$twraum->ort_kurzbz."&rlehre=NULL&raktiv=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$reslink."'><img src='../../skin/images/".$resbild."' height='20'></a></td>\n";
	if($twraum->aktiv)
	{
		$aktivbild = "true.png";	
	}
	else
	{
		$aktivbild = "false.png";
	}
	$aktivlink = "?toggle=true&raktiv=".$twraum->ort_kurzbz."&rres=NULL&rlehre=NULL";
	$htmlstr .= "	    <td align='center'><a href='".$aktivlink."'><img src='../../skin/images/".$aktivbild."' height='20'></a></td>\n";
	$htmlstr .= "       <td>".$twraum->kosten."</td>\n";
	$htmlstr .= "       <td>".$twraum->stockwerk."</td>\n";
	$htmlstr .= "   </tr>\n";
	$i++;
}
$htmlstr .= "</tbody></table>\n";


?>
<html>
<head>
<title>R&auml;ume &Uuml;bersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}


</script>

</head>

<body class="background_main">
<h2>R&auml;ume &Uuml;bersicht</h2>



<?php 
    echo $htmlstr;
?>



</body>
</html>
