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

$vertragtyp_kurzbz=isset($_REQUEST['vertragtyp_kurzbz'])?$_REQUEST['vertragtyp_kurzbz']:'';
$vertragtyp_bezeichnung=isset($_REQUEST['vertragtyp_bezeichnung'])?$_REQUEST['vertragtyp_bezeichnung']:'';

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
	if($vertragtyp_kurzbz != '')
	{
		$vertrag = new vertrag();
                $vertrag->vertragtyp_kurzbz = $vertragtyp_kurzbz;
                $vertrag->vertragtyp_bezeichnung = $vertragtyp_bezeichnung;
                $vertrag->saveVertragtyp();
	}
}

if($action=='delete')
{
	if($vertragtyp_kurzbz != '')
        {
                $vertrag = new vertrag();
                if(!$vertrag->deleteVertragtyp($vertragtyp_kurzbz))
                    echo 'Fehler beim Löschen: ' . $vertrag->errormsg;
        }
}


echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css">
	<script type="text/javascript" src="../../include/js/jquery.js"></script>

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
                if(!$vertrag->deleteVertragtyp($_GET['vertragtyp_kurzbz']))
                        echo $vertrag->errormsg;
        }
}
if(isset($_POST['saveVertragtyp']))
{
        $vertrag = new vertrag();
        $vertrag->vertragtyp_kurzbz=$_POST['vertragtyp_kurzbz'];
        $vertrag->vertragtyp_bezeichnung = $_POST['vertragtyp_bezeichnung'];
        if(isset($_POST['neu']) && $_POST['neu']=='true')
                $neu=true;
        else
                $neu=false;

        if(!$vertrag->saveVertragtyp($neu))
                echo $vertrag->errormsg;
}

$vertrag = new vertrag();
$vertrag->getAllVertragtypen();

echo '
<form action="'.$_SERVER['PHP_SELF'].'?action=vertragtypen" method="post">
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
                                <a href="'.$_SERVER['PHP_SELF'].'?action=vertragtypen&type=edit&vertragtyp_kurzbz='.$row->vertragtyp_kurzbz.'"><img src="../../skin/images/edit.png" title="Bearbeiten" /></a>
                                ';
        // Lichtbil und Zeugnis duerfen nicht geloescht werden da diese fuer Bildupload und 
        // Zeugnisarchivierung verwendet werden
        if(!in_array($row->vertragtyp_kurzbz,array('Lichtbil','Zeugnis')))
                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=vertragtypen&type=delete&vertragtyp_kurzbz='.$row->vertragtyp_kurzbz.'"><img src="../../skin/images/cross.png" title="Löschen" /></a>';

        echo '
                        </td>
                        <td>'.$row->vertragtyp_kurzbz.'</td>
                        <td>'.$row->vertragtyp_bezeichnung.'</td>				
                </tr>';
}

$vertragtyp_kurzbz='';
$vertragtyp_bezeichnung='';

if(isset($_GET['type']) && $_GET['type']=='edit')
{
        $vertrag = new vertrag();
        if($vertrag->loadVertragtyp($_GET['vertragtyp_kurzbz']))
        {
                $vertragtyp_kurzbz = $vertrag->vertragtyp_kurzbz;
                $vertragtyp_bezeichnung = $vertrag->vertragtyp_bezeichnung;
        }
}

echo '
</tbody>
<tfoot>
        <tr>
                <td></td>
                <td>
                        <input typ="text" id="vertragtyp_kurzbz" name="vertragtyp_kurzbz" maxlength="8" size="8" '.($vertragtyp_kurzbz!=''?'readonly':'').' value="'.$vertragtyp_kurzbz.'"/>
                        <input type="hidden" id="neu" name="neu" value="'.($vertragtyp_kurzbz==''?'true':'false').'" />
                </td>
                <td><input type="text" id="vertragtyp_bezeichnung" name="vertragtyp_bezeichnung" maxlength="128" value="'.$vertragtyp_bezeichnung.'">
                <input type="submit" name="saveVertragtyp" value="Speichern"></td>
        </tr>
</tfoot>
</table>
</form>';


echo '
</body>
</html>';

?>
