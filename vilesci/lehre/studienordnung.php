<?php
/* 
 * Copyright 2013 fhcomplete.org
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studienordnung.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');


$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiengang_kz=(isset($_POST['studiengang_kz'])?$_POST['studiengang_kz']:'');

echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Studienordnung</title>
	<link rel="stylesheet" href="../../skin/jquery.css" />
	<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" />
	<link rel="stylesheet" href="../../skin/fhcomplete.css" />
	<link rel="stylesheet" href="../../skin/vilesci.css" />
	<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>
	<script src="studienordnung.js" type="text/javascript"></script>

	<script type="text/javascript">
	$(function() 
	{
		$( "#menueLinks" ).accordion({
			heightStyle: "content",
			header: "h3",
			collapsible: true
		});
		$( "#menueRechts" ).accordion({
			heightStyle: "content",
			header: "h3",
			collapsible: true
		});

	});
	</script>
</head>
<body>';
if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung für diese Seite');
$studiengang = new studiengang();
$studiengang->getAll('typ,kurzbz');

echo '
<table style="width:100%">
	<tr>
		<td valign="top" width="20%">
			<div id="menueLinks">
				<h3>Studiengang</h3>
				<div style="margin:0px;padding:5px;">
					<p>
					<select id="studiengang" name="studiengang_kz" onchange="loadStudienordnung()">';

foreach($studiengang->result as $row)
{
	if($studiengang_kz=='')
		$studiengang_kz=$row->studiengang_kz;

	if($studiengang_kz==$row->studiengang_kz)
		$selected='selected';
	else
		$selected='';

	echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.$db->convert_html_chars($row->kuerzel.' - '.$row->kurzbzlang).'</option>';
}
echo '
					</select>
					</p>
				</div>
				<h3>Studienordnung</h3>
				<div style="margin:0px;padding:5px;">
					<p id="studienordnung" >
					Bitte wählen Sie einen Studiengang aus!
					<br><br><br><br><br><br><br><br><br><br>
					</p>
				</div>

				<h3>Studienplan</h3>
				<div style="margin:0px;padding:5px;">
					<p id="studienplan" style="margin:0;padding:0;">
					Bitte wählen Sie zuerst eine Studienordnung aus!
					</p>
				</div>
			</div>
	</td>
	<td valign="top">	
			<div id="header">
			&nbsp;
			</div>
			<div id="data" >
			&nbsp;
			</div>
	</td>
	<td valign="top" width="20%">
		<div id="menueRechts">
			<h3>Lehrveranstaltungen</h3>
			<div style="margin:0px;padding:5px;">
				<p id="lehrveranstaltung" style="margin:0;padding:0;">
				Bitte wählen Sie zuerst einen Studienplan aus!
				</p>
			</div>
		</div>
	</td>
	</tr>
</table>
';

echo '
</body>
</html>';
?>
