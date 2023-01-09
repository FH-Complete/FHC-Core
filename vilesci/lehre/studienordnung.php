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
 * 			Andreas Moik	<moik@technikum-wien.at>
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

	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script>var jqUi = jQuery.noConflict(true);</script>
	<script type="text/javascript" src="../../include/js/jstree/_lib/jquery.js"></script>
	<!-- Script zum erstellen des Trees-->
	<script type="text/javascript" src="../../include/js/jstree/jquery.jstree.js"></script>

	<script type="text/javascript" src="../../include/js/treeGrid/jstreegrid.js"></script>

	<script type="text/javascript" src="../../vendor/alvaro-prieto/colresizable/colResizable-1.6.min.js"></script>

	<script src="studienordnung_lvregel.js" type="text/javascript"></script>
	<script src="studienordnung.js" type="text/javascript"></script>
	<style type="text/css">
		.col_ects {
			//width: auto;
		}

		.col_lehrform {
			width: auto !important;
			align:center;
			text-align:center;
		}
		.col_left {
			width: auto !important;
			align:left;
			text-align:left;
		}
		.header_ects {
			//width: auto !important;
		}
	</style>

	<script type="text/javascript">
	$(function()
	{';
		//jqUi( "#menueLinks" ).accordion({
		//	heightStyle: "content",
		//	header: "h3",
		//	collapsible: true
		//});
		echo "
		jqUi('#menueLinks').addClass('ui-accordion ui-accordion-icons ui-widget ui-helper-reset')
		.find('h3')
		  .addClass('ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom')
		  .hover(function() { $(this).toggleClass('ui-state-hover'); })
		  .prepend('<span class=\"ui-icon ui-icon-triangle-1-s\"></span>')
		  .click(function() {
			$(this)
			  .toggleClass('ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom')
			  .find('> .ui-icon').toggleClass('ui-icon-triangle-1-s ui-icon-triangle-1-e').end()
			  .next().toggleClass('ui-accordion-content-active').slideToggle();
			return false;
		  })
		  .next()
			.addClass('ui-accordion-content  ui-helper-reset ui-widget-content ui-corner-bottom');

";
echo "$(\"#layoutTable\").colResizable({liveDrag:true});";
echo "
		jqUi('#menueRechts').addClass('ui-accordion ui-accordion-icons ui-widget ui-helper-reset')
		.find('h2')
		  .addClass('ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom')
		  .hover(function() { $(this).toggleClass('ui-state-hover'); })
		  .prepend('<span class=\"ui-icon ui-icon-triangle-1-s\"></span>')
		  .click(function() {
			$(this)
			  .toggleClass('ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom')
			  .find('> .ui-icon').toggleClass('ui-icon-triangle-1-s ui-icon-triangle-1-e').end()
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
	.ui-icon
	{
		display:inline-block;
	}
	#filteredLVs > div.jstree-grid-wrapper
	{
		width: 600px;
	}
	h3
	{
		white-space: nowrap;
	}
	</style>
</head>
<body>";
if((!$rechte->isBerechtigt('lehre/studienordnung')) && (!$rechte->isBerechtigt('lehre/studienordnungInaktiv')))
	die('Sie haben keine Berechtigung für diese Seite');

if(($rechte->isBerechtigt('lehre/studienordnungInaktiv')) && (!$rechte->isBerechtigt('lehre/studienordnung')))
    echo "<script type='text/javascript'>var initSTOs = 'inaktiv';</script>";

$stg_arr = $rechte->getStgKz('lehre/studienordnung');
if(empty($stg_arr))
    $stg_arr = $rechte->getStgKz('lehre/studienordnungInaktiv');

$studiengang = new studiengang();
$studiengang->loadArray($stg_arr,'typ,kurzbz');
$types = new studiengang();
$types->getAllTypes();
$typ = '';

echo '
<table id="layoutTable" width="100%">
	<tr>
		<td valign="top" width="20%">
			<div id="menueLinks">
				<h3>Studiengang</h3>
				<div style="margin:0px;padding:5px;">
					<p>
					<SELECT id="studiengang" name="studiengang_kz" onchange="loadStudienordnung()" style="width: 100%;">
                    <OPTION value="">-- Bitte auswählen --</option>';
                    foreach($studiengang->result as $row)
                    {
                        if ($typ != $row->typ || $typ == '')
                        {
                            if ($typ != '')
                                echo '</optgroup>';
                            
                            echo '<optgroup label = "'.($types->studiengang_typ_arr[$row->typ] != '' ? $types->studiengang_typ_arr[$row->typ] : $row->typ).'">';
                        }
                        echo '<OPTION value="'.$row->studiengang_kz.'"'.($studiengang_kz==$row->studiengang_kz?'selected':'').'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';
                        $typ = $row->typ;
                    }
                    echo '</SELECT><br>
                    <input type="button" value="Daten laden" onclick="loadStudienordnung()" style="margin-top: 5px" />
                    </p>
                    </div>
                  
                    <h3>Studienordnung</h3>
                    <div style="margin:0px;padding:5px;">
                    <p id="studienordnung" >
                    Bitte wählen Sie einen Studiengang aus!
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
	<td valign="top" style="max-width:900px">
			<div id="header">
			&nbsp;
			</div>
			<div id="treeWrapper" style="overflow:auto">
				<div id="data" style="min-height: 10px; min-width: 700px;">
					&nbsp;
				</div>
			</div>
			<div id="jsonData"></div>

			<!-- Tabs -->
			<script>
			$(function()
			{
				jqUi( "#tabs" ).tabs();
				$( "#tabs" ).hide();
			});

			</script>
			<div id="tabs">
				<ul>
					<li><a href="#tab-lehrveranstaltungdetail">LV Details</a></li>
					<li><a href="#tab-regel">Regeln</a></li>
					<li><a href="#tab-kompatibel">Kompatibilität</a></li>
					<li><a href="#tab-sortierung">Sortierung</a></li>
				</ul>
				<div id="tab-lehrveranstaltungdetail">
					<p>Klicken Sie auf eine Lehrveranstaltung um die Details anzuzeigen</p>
				</div>
				<div id="tab-regel">
					<p>Klicken Sie auf eine Lehrveranstaltung um die Regeln anzuzeigen</p>
				</div>
				<div id="tab-kompatibel">
					<p>Klicken Sie auf eine Lehrveranstaltung um die kompatiblen Lehrveranstaltungen anzuzeigen</p>
				</div>
				<div id="tab-sortierung">
					<p>Klicken Sie auf eine Lehrveranstaltung um die Sortierung innerhalb der Studienplanansicht im CIS zu ändern.</p>
				</div>
			</div>
			<!-- Tabs ende -->
	</td>
	<td valign="top" width="20%" >
		<!--script> colResizable plugin used instead
			$(function()
			{
				jqUi("#menueRechts").resizable({
					handles: "w",
					minWidth: 400,
					maxWidth: 1000,
					resize: function(event, ui) { jqUi("#menueRechts").css("left",0);}
				});
				jqUi("#menueLinks").resizable({
					handles: "e",
					minWidth: 200,
					maxWidth: 1000,
				});
			});
		</script-->
		<div id="menueRechts" style="width: auto; height:auto; minHeight:20px; maxHeigth: 700px; margin:0px;">
			<h2><a href=#>Filter</a></h2>
			<div id = "divFilter" style="margin:0px;padding:5px;">
				<div id="lehrveranstaltung" style="margin:0;padding:0; width: 400px;">
				Bitte wählen Sie zuerst einen Studienplan aus!';
echo'
				</div>
			</div>
			<h2>Lehrveranstaltungen</h2>
			<div id="divLVuebersicht" style="margin:0px;padding:5px;">
				<div id="filteredLVs" style="width:auto; max-height:500px;">
					<div id="lvListe">
						Keine Einträge gefunden!
					</div>
				</div>
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
