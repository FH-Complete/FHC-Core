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
 * 			Stefan Puraner	<puraner@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studienordnung.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lehrveranstaltung.class.php');


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
	<link rel="stylesheet" href="../../include/js/treeGrid/css/jquery.treegrid.css">
	
	<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>
	<script>var jqUi = jQuery.noConflict(true);</script>
	<script type="text/javascript" src="../../include/js/jstree/_lib/jquery.js"></script>
	<!-- Script zum erstellen des Trees-->
	<script type="text/javascript" src="../../include/js/jstree/jquery.jstree.js"></script>
	
	<script type="text/javascript" src="../../include/js/treeGrid/jstreegrid.js"></script>
	
	
	<script src="studienordnung.js" type="text/javascript"></script>
	<script src="studienordnung_lvregel.js" type="text/javascript"></script>

	<script type="text/javascript">
	$(function() 
	{
		jqUi( "#menueLinks" ).accordion({
			heightStyle: "content",
			header: "h3",
			collapsible: true
		});
//		jqUi( "#menueRechts" ).accordion({
//			heightStyle: "content",
//			header: "h2",
//			collapsible: true
//		});

';
echo "
		jqUi('#menueRechts').addClass('ui-accordion ui-accordion-icons ui-widget ui-helper-reset')
		.find('h2')
		  .addClass('ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom')
		  .hover(function() { $(this).toggleClass('ui-state-hover'); })
		  .prepend('<span class=\"ui-icon ui-icon-triangle-1-e\"></span>')
		  .click(function() {
			$(this)
			  .toggleClass('ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom')
			  .find('> .ui-icon').toggleClass('ui-icon-triangle-1-e ui-icon-triangle-1-s').end()
			  .next().toggleClass('ui-accordion-content-active').slideToggle();
			return false;
		  })
		  .next()
			.addClass('ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom');
			//.hide();

	});
	var user='".$uid."';
	</script>
	<style>
	html,body {
		margin:0;
		padding:0;
	}
	.newLVRegel
	{
		background-color: #F99F9F;
	}

	</style>
</head>
<body>";
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
			<input type="button" onclick="LVRegelnloadRegeln(1)" value="LVRegelnloadRegeln(1)" />
	</td>
	<td valign="top">	
			<div id="header">
			&nbsp;
			</div>
			<div id="treeWrapper">
				<div id="data" style="min-height: 10px;">
					&nbsp;
				</div>
			</div>
			<div id="jsonData"></div>
	</td>
	<td valign="top" width="20%">
		<div id="menueRechts" style="width: 420px;">
			<h2><a href=#>Filter</a></h2>
			<div style="margin:0px;padding:5px;">
				<div id="lehrveranstaltung" style="margin:0;padding:0; width: 400px;">
				Bitte wählen Sie zuerst einen Studienplan aus!';
//	var_dump($studiengang_kz);
//	$lv = new lehrveranstaltung();
//	$lv->load_lva($studiengang_kz, null, null, TRUE, TRUE);
//	$sem = $lv->lehrveranstaltungen[1]->semester;
//	echo "<ul>";
//	echo "<li>Semester ".$row->lehrveranstaltungen[1]->semester."</li><ul>";
//	foreach($lv->lehrveranstaltungen as $row)
//	{
//		if($sem==$row->semester)
//		{
//			echo "<li>".$row->bezeichnung."</li>";
//		}
//		else
//		{
//			echo "</ul><li>".$row->semester."</li>";
//			echo "<ul><li>".$row->bezeichnung."</li>";
//		}
//		
//	}
//	echo "</ul></ul>";

echo'
				</div>
			</div>
			<h2>Lehrveranstaltungen</h2>
			<div style="margin:0px;padding:5px;">
				<div id="filteredLVs" style="width: 400px;">
					<div id="lvListe">
						Keine Einträge gefunden!
					</div>
				</div>
			</div>
		</div>
	</td>
	</tr>
</table>
<script>
$(function() 
	{
		jqUi(\'#LVREGELDetailsDialog\').dialog(
		{
			autoOpen: false,
			minWidth: 650,
			title: "Lehrveranstaltungsregeln"
		});
	});
</script>
<div id="LVREGELDetailsDialog">Details</div>
';

echo '
</body>
</html>';
?>
