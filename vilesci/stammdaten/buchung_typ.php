<?php
/*
 * Copyright 2014 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/buchung.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$buchungstyp_kurzbz=isset($_REQUEST['buchungstyp_kurzbz'])?$_REQUEST['buchungstyp_kurzbz']:'';
$buchungstyp_bezeichnung=isset($_REQUEST['buchungstyp_bezeichnung'])?$_REQUEST['buchungstyp_bezeichnung']:'';

$action=isset($_GET['action'])?$_GET['action']:'';
if(isset($_POST['add']))
	$action='add';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!isset($stg_kz))
    $stg_kz = null;

if(!$rechte->isBerechtigt('buchung/typen', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

if($action=='add')
{
	if($buchungstyp_kurzbz != '')
	{
		$buchung = new buchung();
                $buchung->buchungstyp_kurzbz = $buchungstyp_kurzbz;
                $buchung->buchungstyp_bezeichnung = $buchungstyp_bezeichnung;
                $buchung->saveBuchungstyp();
	}
}

if($action=='delete')
{
	if($buchungstyp_kurzbz != '')
        {
                $buchung = new buchung();
                if(!$buchung->deleteBuchungstyp($buchungstyp_kurzbz))
                    echo 'Fehler beim Löschen: ' . $buchung->errormsg;
        }
}


echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>

	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"],
				headers: {0:{sorter:false}}
			}); 
		});		
	</script>
	<title>Buchungstypen</title>
</head>
<body>';


echo '<h1>Buchungstypen</h1>';

if(isset($_GET['type']))
{
        if($_GET['type']=='delete')
        {
                $buchung = new buchung();
                if(!$buchung->deleteBuchungstyp($_GET['buchungstyp_kurzbz']))
                        echo $buchung->errormsg;
        }
}
if(isset($_POST['saveBuchungstyp']))
{
        $buchung = new buchung();
        $buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
        $buchung->buchungstyp_bezeichnung = $_POST['buchungstyp_bezeichnung'];

        if(isset($_POST['neu']) && $_POST['neu']=='true')
                $neu=true;
        else
                $neu=false;

        if(!$buchung->saveBuchungstyp($neu))
                echo $buchung->errormsg;
}

$buchung = new buchung();
$buchung->getAllBuchungstypen();

echo '
<form action="'.$_SERVER['PHP_SELF'].'?action=buchungstypen" method="post">
<table id="t1" class="tablesorter" style="width:auto">
<thead>
        <th></th>
        <th>Kurzbz</th>
        <th>Bezeichnung</th>
</thead>
<tbody>
        ';
foreach($buchung->result as $row)
{
        echo '<tr>
                        <td>
                                <a href="'.$_SERVER['PHP_SELF'].'?action=buchungstypen&type=edit&buchungstyp_kurzbz='.$row->buchungstyp_kurzbz.'"><img src="../../skin/images/edit.png" title="Bearbeiten" /></a>
                                <a href="'.$_SERVER['PHP_SELF'].'?action=buchungstypen&type=delete&buchungstyp_kurzbz='.$row->buchungstyp_kurzbz.'"><img src="../../skin/images/cross.png" title="Löschen" /></a>';
        echo '
                        </td>
                        <td>'.$buchung->convert_html_chars($row->buchungstyp_kurzbz).'</td>
                        <td>'.$buchung->convert_html_chars($row->buchungstyp_bezeichnung).'</td>				
                </tr>';
}

$buchungstyp_kurzbz = '';
$buchungstyp_bezeichnung = '';

if(isset($_GET['type']) && $_GET['type']=='edit')
{
        $buchung = new buchung();
        if($buchung->loadBuchungstyp($_GET['buchungstyp_kurzbz']))
        {
            $buchungstyp_kurzbz = $buchung->buchungstyp_kurzbz;
            $buchungstyp_bezeichnung = $buchung->buchungstyp_bezeichnung;
        }
}

echo '
</tbody>
<tfoot>
        <tr>
                <td></td>
                <td>
                        <input typ="text" id="buchungstyp_kurzbz" name="buchungstyp_kurzbz" maxlength="8" size="8" '.($buchungstyp_kurzbz!=''?'readonly':'').' value="'.$buchungstyp_kurzbz.'"/>
                        <input type="hidden" id="neu" name="neu" value="'.($buchungstyp_kurzbz==''?'true':'false').'" />
                </td>
                <td><input type="text" id="buchungstyp_bezeichnung" name="buchungstyp_bezeichnung" maxlength="128" value="'.$buchung->convert_html_chars($buchungstyp_bezeichnung).'">
                <input type="submit" name="saveBuchungstyp" value="Speichern"></td>
        </tr>
</tfoot>
</table>
</form>';


echo '
</body>
</html>';

?>
