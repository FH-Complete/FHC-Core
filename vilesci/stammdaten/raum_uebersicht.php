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

if(!$rechte->isBerechtigt('basis/ort', null, 'suid'))
	die('Sie haben keine Rechte fuer diese Seite');

// Speichern der Daten
if(isset($_POST['ort_kurzbz']))
{
	// Die Aenderungen werden per Ajax Request durchgefuehrt,
	// daher wird nach dem Speichern mittels exit beendet

	//Lehre Feld setzen
	if(isset($_POST['lehre']))
	{
		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->lehre=($_POST['lehre']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
	
	//Reservieren Feld setzen
	if(isset($_POST['reservieren']))
	{
		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->reservieren=($_POST['reservieren']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
	
	//Aktiv Feld setzen
	if(isset($_POST['aktiv']))
	{
		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->aktiv=($_POST['aktiv']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
}

$sg = new ort();
if (!$sg->getAll())
    die($sg->errormsg);

$htmlstr = "
<table class='tablesorter' id='t1'>
<thead>
	<tr>
		<th></th>
		<th>Kurzbezeichnung</th>
		<th>Bezeichnung</th>
		<th>Planbezeichnung</th>
		<th>Max. Person</th>
		<th>Lehre</th>
		<th>Reservieren</th>
		<th>Aktiv</th>
		<th>Kosten</th>
		<th>Stockwerk</th>
   </tr>
</thead>
<tbody>\n";

foreach ($sg->result as $twraum)
{
	$htmlstr .= "   <tr>\n";
	$htmlstr .= '		<td><a href="raum_details.php?type=raumtyp&ort_kurzbz='.$twraum->ort_kurzbz.'" target="detail_raum" title="Raumtyp zuteilen" ><img src="../../skin/images/entitlement-pot.png" height="20px"/></a></td>';
	$htmlstr .= "       <td><a href='raum_details.php?ort_kurzbz=".$twraum->ort_kurzbz."' target='detail_raum'>".$twraum->ort_kurzbz."</a></td>\n";
	$htmlstr .= "       <td>".$twraum->bezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->planbezeichnung."</td>\n";
	$htmlstr .= "       <td>".$twraum->max_person."</td>\n";
	
	// Lehre boolean setzen
	
	$htmlstr .= "       <div style='display: none'>".$db->convert_html_chars($twraum->lehre)."</div> <td align='center'><a href='#Lehre' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"lehre\"); return false'>";
	$htmlstr .= "       <input type='hidden' id='lehre".$twraum->ort_kurzbz."' value='".($twraum->lehre=="t"?"true":"false")."'>";
	$htmlstr .= "       <img id='lehreimg".$twraum->ort_kurzbz."' alt='Lehre' title='Lehre' src='../../skin/images/".($twraum->lehre=="t"?"true.png":"false.png")."' height='20'>";
	$htmlstr .= "       </a></td>";
	
	// Reservieren boolean setzen
	
	$htmlstr .= "       <div style='display: none'>".$db->convert_html_chars($twraum->reservieren)."</div> <td align='center'><a href='#Reservieren' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"reservieren\"); return false'>";
	$htmlstr .= "       <input type='hidden' id='reservieren".$twraum->ort_kurzbz."' value='".($twraum->reservieren=="t"?"true":"false")."'>";
	$htmlstr .= "       <img id='reservierenimg".$twraum->ort_kurzbz."' alt='Reservieren' title='Reservieren' src='../../skin/images/".($twraum->reservieren=="t"?"true.png":"false.png")."' style='margin:0;' height='20'>";
	$htmlstr .= "       </a></td>";
	
	// Aktiv boolean setzen
	
	$htmlstr .= "       <div style='display: none'>".$db->convert_html_chars($twraum->aktiv)."</div> <td align='center'><a href='#Aktiv' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"aktiv\"); return false'>";
	$htmlstr .= "       <input type='hidden' id='aktiv".$twraum->ort_kurzbz."' value='".($twraum->aktiv=="t"?"true":"false")."'>";
	$htmlstr .= "       <img id='aktivimg".$twraum->ort_kurzbz."' alt='Aktiv' title='Aktiv' src='../../skin/images/".($twraum->aktiv=="t"?"true.png":"false.png")."' style='margin:0;' height='20'>";
	$htmlstr .= "       </a></td>";
	
	$htmlstr .= "       <td>".$twraum->kosten."</td>\n";
	$htmlstr .= "       <td>".$twraum->stockwerk."</td>\n";

	$htmlstr .= "   </tr>\n";
}
$htmlstr .= "</tbody></table>\n";


?>
<html>
<head>
	<title>R&auml;ume &Uuml;bersicht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<style>
	table.tablesorter tbody td
	{
		margin: 0;
		padding: 0;
		vertical-align: middle;
	}
	</style>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function() 
	{ 
		$("#t1").tablesorter(
		{
			sortList: [[3,0]],
			widgets: ["zebra"]
		}); 
	});
		
	function changeboolean(ort_kurzbz, name)
	{
		value=document.getElementById(name+ort_kurzbz).value;
	
		var dataObj = {};
		dataObj["ort_kurzbz"]=ort_kurzbz;
		dataObj[name]=value;

		$.ajax({
			type:"POST",
			url:"raum_uebersicht.php", 
			data:dataObj,
			success: function(data) 
			{
				if(data=="true")
				{
					//Image und Value aendern
					if(value=="true")
						value="false";
					else
						value="true";
					document.getElementById(name+ort_kurzbz).value=value;
					document.getElementById(name+"img"+ort_kurzbz).src="../../skin/images/"+value+".png";
				}
				else 
					alert("ERROR:"+data)
			},
			error: function() { alert("error"); }
		});
	}

	</script>
</head>
<body>
<h2>R&auml;ume &Uuml;bersicht</h2>
<a href="raum_details.php" target="detail_raum">Neuer Raum </a>

<?php 
    echo $htmlstr;
?>

</body>
</html>
