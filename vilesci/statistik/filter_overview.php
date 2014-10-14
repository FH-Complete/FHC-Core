<?php
/* Copyright (C) 2013 fhcomplete.org
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
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/filter.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$nl="\n";

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if($rechte->isBerechtigt('addon/reports', 'suid'))
	$write_admin=true;

$filter = new filter();
if (!$filter->loadAll())
    die($filter->errormsg);

//$htmlstr = "<table class='liste sortable'>\n";
$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table class='tablesorter' id='t1'>\n";
$htmlstr .= "   <thead><tr>\n";
$htmlstr .= '    <th onmouseup="document.formular.check.value=0">ID</th>
		<th title="Kurzbezeichnung des Filters">KurzBz</th>
		<th>ValueName</th>
		<th>ShowV</th>
		<th>Type</th>
		<th>HTMLAttributes</th>
		<th>SQL</th>
		<th>Reserve</th>';
$htmlstr .= "   </tr></thead><tbody>\n";
$i = 0;
foreach ($filter->result as $filter)
{
    //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	$htmlstr .= "   <tr>\n";
	$htmlstr .= "       <td align='right'><a href='filter_details.php?filter_id=".$filter->filter_id."' target='frame_filter_details'>".$filter->filter_id." </a>
							<a href='../data/".$filter->filter_id.".html' target='_blank'>
								<img title='".$filter->kurzbz." anzeigen' src='x-office-presentation.svg' height='15' />
							</a>
						</td>\n";
	$htmlstr .= "       <td><a href='filter_details.php?filter_id=".$filter->filter_id."' target='frame_filter_details'>".$filter->kurzbz."</a></td>\n";
	$htmlstr .= "       <td>".$filter->valuename."</td>\n";
	$htmlstr .= "       <td>".$filter->showvalue."</td>\n";
	$htmlstr .= "       <td>".$filter->type."</td>\n";
	$htmlstr .= "       <td>".$filter->htmlattr."</td>\n";
	$htmlstr .= "       <td>".substr($filter->sql,0,32)."...</td>\n";
	$htmlstr .= "       <td>".substr($filter->sql,0,16)."...</td>\n";
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
<!--<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>-->
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
				sortList: [[2,0]],
				widgets: ["zebra"]
			}); 
		});
		
function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}

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

<body class="background_main">
<a href="filter_details.php" target="frame_report_details">Neuer Filter</a>


<?php 
    echo $htmlstr;
?>



</body>
</html>
