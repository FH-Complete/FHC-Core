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
require_once('../../include/vertrag.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$vertragstyp_kurzbz=isset($_REQUEST['vertragstyp_kurzbz'])?$_REQUEST['vertragstyp_kurzbz']:'';
$vertragstyp_bezeichnung=isset($_REQUEST['vertragstyp_bezeichnung'])?$_REQUEST['vertragstyp_bezeichnung']:'';

$action=isset($_GET['action'])?$_GET['action']:'';
if(isset($_POST['add']))
    $action='add';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!isset($stg_kz))
    $stg_kz = null;

if(!$rechte->isBerechtigt('vertrag/typen', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

if($action=='add')
{
	if($vertragstyp_kurzbz != '')
	{
		$vertrag = new vertrag();
                $vertrag->vertragstyp_kurzbz = $vertragstyp_kurzbz;
                $vertrag->vertragstyp_bezeichnung = $vertragstyp_bezeichnung;
                $vertrag->savevertragstyp();
	}
}

if($action=='delete')
{
	if($vertragstyp_kurzbz != '')
        {
                $vertrag = new vertrag();
                if(!$vertrag->deletevertragstyp($vertragstyp_kurzbz))
                    echo 'Fehler beim Löschen: ' . $vertrag->errormsg;
        }
}


echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	<title>Vertragstypen</title>
</head>
<body>';


echo '<h1>Vertragstypen</h1>';

if(isset($_GET['type']))
{
        if($_GET['type']=='delete')
        {
                $vertrag = new vertrag();
                if(!$vertrag->deletevertragstyp($_GET['vertragstyp_kurzbz']))
                        echo $vertrag->errormsg;
        }
}
if(isset($_POST['savevertragstyp']))
{
        $vertrag = new vertrag();
        $vertrag->vertragstyp_kurzbz=$_POST['vertragstyp_kurzbz'];
        $vertrag->vertragstyp_bezeichnung = $_POST['vertragstyp_bezeichnung'];
        if(isset($_POST['neu']) && $_POST['neu']=='true')
                $neu=true;
        else
                $neu=false;

        if(!$vertrag->savevertragstyp($neu))
                echo $vertrag->errormsg;
}

$vertrag = new vertrag();
$vertrag->getAllvertragstypen();

echo '
<form action="'.$_SERVER['PHP_SELF'].'?action=vertragstypen" method="post">
<table id="t1" class="tablesorter" style="width:auto">
<thead>
        <th></th>
        <th>Kurzbz</th>
        <th>Bezeichnung</th>
</thead>
<tbody>
        ';
foreach($vertrag->result as $row)
{
        echo '<tr>
                        <td>
                                <a href="'.$_SERVER['PHP_SELF'].'?action=vertragstypen&type=edit&vertragstyp_kurzbz='.$row->vertragstyp_kurzbz.'"><img src="../../skin/images/edit.png" title="Bearbeiten" /></a>
                                ';
        // Lichtbil und Zeugnis duerfen nicht geloescht werden da diese fuer Bildupload und 
        // Zeugnisarchivierung verwendet werden
        if(!in_array($row->vertragstyp_kurzbz,array('Lichtbil','Zeugnis')))
                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=vertragstypen&type=delete&vertragstyp_kurzbz='.$row->vertragstyp_kurzbz.'"><img src="../../skin/images/cross.png" title="Löschen" /></a>';

        echo '
                        </td>
                        <td>'.$vertrag->convert_html_chars($row->vertragstyp_kurzbz).'</td>
                        <td>'.$vertrag->convert_html_chars($row->vertragstyp_bezeichnung).'</td>				
                </tr>';
}

$vertragstyp_kurzbz='';
$vertragstyp_bezeichnung='';

if(isset($_GET['type']) && $_GET['type']=='edit')
{
        $vertrag = new vertrag();
        if($vertrag->loadvertragstyp($_GET['vertragstyp_kurzbz']))
        {
                $vertragstyp_kurzbz = $vertrag->vertragstyp_kurzbz;
                $vertragstyp_bezeichnung = $vertrag->vertragstyp_bezeichnung;
        }
}

echo '
</tbody>
<tfoot>
        <tr>
                <td></td>
                <td>
                        <input typ="text" id="vertragstyp_kurzbz" name="vertragstyp_kurzbz" maxlength="8" size="8" '.($vertragstyp_kurzbz!=''?'readonly':'').' value="'.$vertragstyp_kurzbz.'"/>
                        <input type="hidden" id="neu" name="neu" value="'.($vertragstyp_kurzbz==''?'true':'false').'" />
                </td>
                <td><input type="text" id="vertragstyp_bezeichnung" name="vertragstyp_bezeichnung" maxlength="128" value="'.$vertrag->convert_html_chars($vertragstyp_bezeichnung).'">
                <input type="submit" name="savevertragstyp" value="Speichern"></td>
        </tr>
</tfoot>
</table>
</form>';


echo '
</body>
</html>';

?>
