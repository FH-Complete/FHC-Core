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
 *         
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/statistik.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$fb_obj = new fachbereich();
$fb_obj->getAll();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen(get_uid());

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Studenten Historie</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body class="Background_main">
<h2>Studenten Historie</h2>';

$stsem = new studiensemester();
if(isset($_GET['ws']) && check_stsem($_GET['ws']))
	$ws = $_GET['ws'];
else
	$ws = $stsem->getNearest(1);
	
if(isset($_GET['ss']) && check_stsem($_GET['ss']))
	$ss = $_GET['ss'];
else
	$ss = $stsem->getNearest(2);

if(isset($_POST['show']))
{
	$studiengang_kz=$_POST['studiengang_kz'];
	$ausbildungssemester = $_POST['ausbildungssemester'];
}
else 
{
	$studiengang_kz=335;
	$ausbildungssemester=1;
}
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
$stg_obj = new studiengang();
$stg_obj->getAll();
echo "\n",'Studiengang <SELECT name="studiengang_kz">';
foreach($stg_obj->result as $row)
{
	if($row->studiengang_kz==$studiengang_kz)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.'</OPTION>';
}
echo '</SELECT>';
/*
$stsem_obj = new studiensemester();
$stsem_obj->getAll();
echo "\n",'Studiensemester <SELECT name="studiensemester_kurzbz">';
foreach($stsem_obj->studiensemester as $row)
{
	if($row->studiensemester_kurzbz==$studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
}
echo '</SELECT>';
*/
echo "\n",'Ausbildungssemester <SELECT name="ausbildungssemester">';
echo '<OPTION value="">-- Alle --</OPTION>';
for($i=1;$i<10;$i++)
{
	if($i==$ausbildungssemester)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$i.'" '.$selected.'>'.$i.'</OPTION>';
}
echo '</SELECT>';

echo '&nbsp;&nbsp;<input type="submit" name="show" value="OK"></form>';

echo "<table>
		<tr class='liste'>
			<th>Gesamt</th>
			<th>Anfänger</th>
			<th>Dropout</th>
			<th>%</th>
		</tr>
";

$stsem_obj = new studiensemester();
$stsem_obj->getAll();
foreach($stsem_obj->studiensemester as $row_stsem)
{
	$statistik = new statistik();
		
	echo '<tr>';
	echo '<td>'.$row_stsem->studiensemester_kurzbz.'</td>';
	
	$statistik->get_prestudenten($studiengang_kz, $row_stsem->studiensemester_kurzbz, $ausbildungssemester);
	$anf=count($statistik->statistik_obj);
	echo '<td align="right">'.$anf.'</td>';
	
	$statistik->get_DropOut($studiengang_kz, $row_stsem->studiensemester_kurzbz, $ausbildungssemester);
	$dropout=count($statistik->statistik_obj);
	echo '<td align="right">'.$dropout.'</td>';
	if ($anf>0)
		$prozent=round((100*$dropout/$anf),2);
	else
		$prozent='-';
	echo '<td align="right">'.$prozent.' %</td>';
	echo '</tr>';
}
echo '</tbody></table>';


echo '</body></html>';
?>