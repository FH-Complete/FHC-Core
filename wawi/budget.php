<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/wawi.config.inc.php');
require_once('../include/organisationseinheit.class.php');
require_once('auth.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/geschaeftsjahr.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Kostenstellen - Budget</title>	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>

	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script> 

	<script type="text/javascript">
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: ['zebra']
				}); 
			} 
		); 			
	</script>
</head>
<body>
<?php 

$kostenstelle = new wawi_kostenstelle(); 
$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('wawi/budget'))
	die('Sie haben keine Berechtigung für diese Seite');

$geschaeftsjahr_kurzbz=(isset($_GET['geschaeftsjahr_kurzbz'])?$_GET['geschaeftsjahr_kurzbz']:'');

if(isset($_POST['save']))
{
	if(!$rechte->isBerechtigt('wawi/budget',null, 'suid'))
		die('Sie haben keine Berechtigung zum Speichern');
	foreach($_POST as $key=>$value)
	{
		if(mb_strstr($key,"budget_"))
		{
			$kostenstelle_id = mb_substr($key,mb_strlen('budget_'));
			$kst = new wawi_kostenstelle();
			$budget = mb_str_replace(',', '.', $value);
			
			if(!$kst->setBudget($kostenstelle_id, $geschaeftsjahr_kurzbz, $budget))
				echo $kst->errormsg.'<br>';
		}
	}
}

echo '<h1>Kostenstellen - Budget</h1>';
$gj = new geschaeftsjahr();

//Wenn kein GJ uebergeben wurde wird das aktuelle ausgewaehlt
if($geschaeftsjahr_kurzbz=='')
	$geschaeftsjahr_kurzbz = $gj->getakt();

$gj->getAll();
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Geschäftsjahr: <SELECT name="geschaeftsjahr_kurzbz">';
foreach($gj->result as $row)
{
	if($row->geschaeftsjahr_kurzbz==$geschaeftsjahr_kurzbz)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->geschaeftsjahr_kurzbz.'" '.$selected.'>'.$row->geschaeftsjahr_kurzbz.'</OPTION>';
}
echo '</SELECT>';
echo ' <input type="submit" value="Anzeigen">';
echo '</form>';

$kst = new wawi_kostenstelle();
$kst->getAll();

echo '<form action="'.$_SERVER['PHP_SELF'].'?geschaeftsjahr_kurzbz='.$geschaeftsjahr_kurzbz.'" method="POST">';
echo '<table id="myTable" class="tablesorter" style="width: auto;">
	<thead>
		<tr>
			<th>ID</th>
			<th>Kostenstelle</th>
			<th>Budget</th>
		</tr>
	</thead>
	<tbody>';
foreach($kst->result as $row)
{
	$budget = $kst->getBudget($row->kostenstelle_id, $geschaeftsjahr_kurzbz);
	echo '<tr>';
	echo '<td>',$row->kostenstelle_id,'</th>';
	if($row->aktiv)
		echo '<td>',$row->bezeichnung,'</th>';
	else
		echo '<td><strike>',$row->bezeichnung,'</strike></th>';
	echo '<td><input type="text" size="13" maxlenght="13" name="budget_'.$row->kostenstelle_id.'" value="'.$budget.'" class="number"></td>';
	echo '</tr>';
}
echo '</tbody>
	</table>';

echo '<input type="submit" name="save" value="Speichern">';
echo '</form>';
echo '<br><br><br><br><br><br>';	

?>