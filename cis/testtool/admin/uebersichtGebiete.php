<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once("../../../config/cis.config.inc.php");
require_once('../../../include/basis_db.class.php');
require_once("../../../include/gebiet.class.php");
require_once('../../../include/functions.inc.php');
require_once("../../../include/benutzerberechtigung.class.php");
require_once("../../../include/studiengang.class.php");

if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Testool Fragen Übersicht</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
    <link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
    <script type="text/javascript">
    function deleteGebiet(id)
    {
        if(confirm("Wollen Sie dieses Gebiet wirklich löschen?"))
        {
            $("#data").html('<form action="uebersichtGebiete.php" name="sendform" id="sendform" method="POST"><input type="hidden" name="action" value="deleteGebiet" /><input type="hidden" name="id" value="'+id+'" /></form>');
			document.sendform.submit();
        }
        return false;
    }
    $(document).ready(function()
    {
        $("#t1").tablesorter(
        {
            sortList: [[3,0]],
            widgets: ["zebra"]
        });
    });

    </script>
</head>
<body>
<h1>Gebiete Übersicht</h1>
<div id="data"></div>
<?php
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/testtool', null, 's'))
	die('<span class="error">Sie haben keine Berechtigung für diese Seite</span>');

if(isset($_POST['action']) && $_POST['action']=='deleteGebiet')
{
    if(!isset($_POST['id']) || !is_numeric($_POST['id']))
        die('Falsche Parameteruebergabe');

    if(!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
        die('<span class="error">Sie haben keine Berechtigung für diesen Vorgang</span>');

    $id = $_POST['id'];
    $gebiet = new gebiet();
    if(!$gebiet->delete($id))
        echo '<span class="error">'.$gebiet->errormsg.'</span>';
}

$gebiet = new gebiet();
$gebiet->getAll();

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz',false);

echo '<table id="t1" class="tablesorter">
    <thead>
        <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Kurzbz</th>
            <th>Bezeichnung</th>
            <th>Beschreibung</th>
            <th>Zeit</th>
            <th>Multipleresonse</th>
            <th>Levelgleichverteilung</th>
            <th>Ablauf</th>
        </tr>
    </thead>
    <tbody>';
foreach($gebiet->result as $row_gebiet)
{
    $ablauf = new gebiet();
    $ablauf->loadAblaufGebiet($row_gebiet->gebiet_id);
    echo '<tr>
        <td>
            <a href="edit_gebiet.php?gebiet_id='.$row_gebiet->gebiet_id.'"><img src="../../../skin/images/edit.png" title="Edit" height="15px"/></a>
            <a href="#Delete" onclick="return deleteGebiet(\''.$row_gebiet->gebiet_id.'\');"><img src="../../../skin/images/delete.png" title="Delete" height="15px"/></a></td>
        <td>'.$row_gebiet->gebiet_id.'</td>
        <td>'.$row_gebiet->kurzbz.'</td>
        <td>'.$row_gebiet->bezeichnung.'</td>
        <td>'.$row_gebiet->beschreibung.'</td>
        <td>'.$row_gebiet->zeit.'</td>
        <td>'.($row_gebiet->multipleresponse?'Ja':'Nein').'</td>
        <td>'.($row_gebiet->levelgleichverteilung?'Ja':'Nein').'</td>
        <td>';

    foreach($ablauf->result as $row_ablauf)
    {
        echo $studiengang->kuerzel_arr[$row_ablauf->studiengang_kz].'('.$row_ablauf->semester.') ';
    }
    echo '</td>
    </tr>';
}
echo '</table>';
?>

</body>
