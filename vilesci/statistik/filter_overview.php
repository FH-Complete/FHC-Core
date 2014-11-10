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

if(!$rechte->isBerechtigt('basis/statistik', 's'))
	die('Sie haben keine Berechtigung (basis/statistik) für diese Seite');


if(isset($_POST['action']) && $_POST['action']=='delete' && isset($_POST['filter_id']))
{
	$filter = new filter();
	$filter->delete($_POST['filter_id']);
		
}
$filter = new filter();
if (!$filter->loadAll())
    die($filter->errormsg);

//$htmlstr = "<table class='liste sortable'>\n";
$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table class='tablesorter' id='t1'>\n";
$htmlstr .= "   <thead><tr>\n";
$htmlstr .= '    <th onmouseup="document.formular.check.value=0">ID</th>
		<th title="Kurzbezeichnung des Filters">KurzBz</th>
		<th>ValueName</th>
		<th>Show Value</th>
		<th>Type</th>
		<th>HTMLAttributes</th>
		<th>SQL</th>
		<th>Action</th>';
		
$htmlstr .= "   </tr></thead><tbody>\n";
$i = 0;
foreach ($filter->result as $filter)
{
    //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	$htmlstr .= "   <tr>\n";
	$htmlstr .= "       <td align='right'><a href='filter_details.php?filter_id=".$filter->filter_id."' target='frame_filter_details'>".$filter->filter_id." </a>
						<a href='filter_vorschau.php?filter_id=".$filter->filter_id."' target='_blank'>
							<img src='../../skin/images/x-office-presentation.png' height='15px'/>
						</a>
						</td>\n";
	$htmlstr .= "       <td><a href='filter_details.php?filter_id=".$filter->filter_id."' target='frame_filter_details'>".$filter->kurzbz."</a></td>\n";
	$htmlstr .= "       <td>".$db->convert_html_chars($filter->valuename)."</td>\n";
	$htmlstr .= "       <td>".($filter->showvalue?'Ja':'Nein')."</td>\n";
	$htmlstr .= "       <td>".$db->convert_html_chars($filter->type)."</td>\n";
	$htmlstr .= "       <td>".$db->convert_html_chars($filter->htmlattr)."</td>\n";
	$htmlstr .= "       <td>".$db->convert_html_chars(substr($filter->sql,0,32))."...</td>\n";
	$htmlstr .=	'		<td><form action="'.$_SERVER['PHP_SELF'].'" style="display: inline" name="form_'.$filter->filter_id.'" method="POST"><input type="hidden" name="filter_id" value="'.$filter->filter_id.'"><input type="hidden" name="action" value="delete"/><a href="#Loeschen" onclick="ConfirmDelete('.$filter->filter_id.');">Delete</a></form></td>';
	$htmlstr .= "   </tr>\n";
	$i++;
}
$htmlstr .= "</tbody></table>\n";


?>
<html>
<head>
	<title>Filter &Uuml;bersicht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function() 
			{ 
				$("#t1").tablesorter(
				{
					sortList: [[2,0]],
					widgets: ["zebra"]
				}); 
			});
	function ConfirmDelete(filter_id)
	{
		if(confirm("Wollen Sie diesen Filter wirklich löschen?"))
		{
			document.forms['form_'+filter_id].submit();
		}
	}
	</script>
</head>

<body>
<a href="filter_details.php" target="frame_filter_details">Neuer Filter</a>
<?php 
    echo $htmlstr;
?>
</body>
</html>
