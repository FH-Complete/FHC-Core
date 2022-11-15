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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/firma.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

// Aktuellen User - UID holen
$uid = get_uid();

//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('basis/firma'))
	die('Sie haben keine Berechtigung fuer diese Seite');

// ---------------------------------------
// Parameter uebernehmen
// ---------------------------------------
// Suchkriterien - das wird geloescht
$suchen1 = (isset($_GET['suchen1'])?$_GET['suchen1']:null);
$filter1 = (isset($_GET['filter1'])?$_GET['filter1']:'');
$firmentyp_kurzbz1 = (isset($_GET['firmentyp_kurzbz1'])?$_GET['firmentyp_kurzbz1']:'');

// Suchkriterien - das bleibt
$suchen2 = (isset($_GET['suchen2'])?$_GET['suchen2']:null);
$filter2 = (isset($_GET['filter2'])?$_GET['filter2']:'');
$firmentyp_kurzbz2 = (isset($_GET['firmentyp_kurzbz2'])?$_GET['firmentyp_kurzbz2']:'');

// ---------------------------------------
// Fixe Tabellenwerte einlesen
// ---------------------------------------
$firma = new firma();
$rows=array();
if ($firma->getFirmenTypen())
{
	foreach ($firma->result as $row)
		$rows[]=$row;
}
?>

<html>
<head>
	<title>Firmen Zusammenlegung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>

	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
        <link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

	<style type="text/css">
	<!--

	/* define the table content to be scrollable                                              */
	/* set TBODY element to have block level attributes. All other non-IE browsers            */
	/* this enables overflow to work on TBODY element. All other non-IE, non-Mozilla browsers */
	/* induced side effect is that child TDs no longer accept width: auto                     */
	html>body tbody.scrollContent
	{
		display: block;
		height: 500px;
		overflow: auto;
		width: 100%
	}
	-->
	</style>

	<script type="text/javascript" language="JavaScript1.2">
	<!--
		function set_firmen_search(wohin,filter,firmentypkurzbz)
		{
	   		$("div#infodetail").html('');
	       	$("div#info").hide("slow");

			$('td#'+wohin).html('<img src="../../skin/images/spinner.gif" alt="warten" title="warten" >');
			$.ajax
			(
				{
					type: "POST", url: 'stammdaten_autocomplete.php', async: true,	dataType: 'json',timeout: 4500,
					data: "work=work_firmen_search" + "&filter=" + $('#'+filter).val()  + "&firmentyp_kurzbz=" +  $('#'+firmentypkurzbz).val(),
					error: function()
					{
				   			$('td#'+wohin).html("Fehler");
							return;
					},
					success: function(json)
					{
						var output=show_firmen_search(wohin,json);
						$('td#'+wohin).html(output);
						$('td#'+wohin).focus();
					}
				}
			);
		}
		function show_firmen_search(wohin,json)
		{
			var output = '';
			output += '<table cellpadding="1" cellspacing="1" width="100%" class="scrollTable">';
			output += '<tbody class="scrollContent"><tr class="liste"><td>&nbsp;</td><td width="10%">Firma</td><td width="84%">Name</td></tr>\n';

			var classen='liste1';
			for (p in json)
			{
				if (classen=='liste1')
					classen='liste0';
				else
					classen='liste1';

				if (json[p].oFirma_id=='')
					output += '<tr class="'+classen+'"><td colspan="3">' + json[p].oName + '</td></tr>\n';
				else
					output += '<tr class="'+classen+'"><td><input name="firma_id_'+wohin+'" type="Radio" value="' + json[p].oFirma_id + '">&nbsp;</td><td align="right">' + json[p].oFirma_id + '&nbsp;</td><td>' + json[p].oName + '&nbsp;</td></tr>\n';
			}
			output += '</tbody></table>';
			return output;
		}

		function set_firmen_zusammenlegen()
		{

	   		$("div#infodetail").html('<img src="../../../skin/images/spinner.gif" alt="warten" title="warten" >');
		    $("div#info").show("slow"); // div# langsam oeffnen

			if (!$('input[name=firma_id_geloescht]:checked').val() && !$('input[name=firma_id_bleibt]:checked').val())
			{
				$("div#infodetail").html('keine Auswahl getroffen!');
				return;
			}
			if (!$('input[name=firma_id_geloescht]:checked').val())
			{
				$("div#infodetail").html('welche Firma soll geloescht werden ?');
				return;
			}
			if (!$('input[name=firma_id_bleibt]:checked').val())
			{
				$("div#infodetail").html('welche Firma soll bleiben ?');
				return;
			}
			$.ajax
			(
				{
					type: "GET", timeout: 4500,	dataType: 'html',url: 'firma_zusammen_details.php',
					data: "firma_id_geloescht=" + $('input[name=firma_id_geloescht]:checked').val() + '&firma_id_bleibt='+$('input[name=firma_id_bleibt]:checked').val(),
					error: function()
					{
			   			$("div#infodetail").html("error");
						return;
					},
					success: function(phpData)
					{
				   		$("div#infodetail").html(phpData);
						return;
					}
				}
			);
		}

		$(function()
		{
			$("#info").resizable();
			$("#ui-resizable").draggable();

		});

		-->
	</script>

	<style type="text/css">
	<!--
		li { list-style : outside url("../../skin/images/right.gif");}
		/* ----------------------------------
		Resizable
		---------------------------------- */
		.ui-resizable { position: relative;}
		.ui-resizable-handle { position: absolute;font-size: 0.1px;z-index: 99999; display: block;}
		.ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
		.ui-resizable-n { cursor: n-resize; height: 7px; width: 100%; top: -5px; left: 0; }
		.ui-resizable-s { cursor: s-resize; height: 7px; width: 100%; bottom: -5px; left: 0; }
		.ui-resizable-e { cursor: e-resize; width: 7px; right: -5px; top: 0; height: 100%; }
		.ui-resizable-w { cursor: w-resize; width: 7px; left: -5px; top: 0; height: 100%; }
		.ui-resizable-se { cursor: se-resize; width: 12px; height: 12px; right: 1px; bottom: 1px; }
		.ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: -5px; bottom: -5px; }
		.ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: -5px; top: -5px; }
		.ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: -5px; top: -5px;}

		div.info {width:90%;display:none;padding: 5px 5px 5px 5px;border: 1px solid Black;empty-cells : hide;text-align:center;vertical-align: top;z-index: 99;background-color: white; position:absolute;}
		div.infoclose {border: 7px outset #008381;padding: 0px 10px 0px 10px;}
		div.infodetail {font-size:medium;text-align:left;background-color: #F5F5F5;padding: 15px 15px 15px 15px;}
	-->
	</style>

</head>
<body class="background_main">
	<h2>Firmen -  Zusammenlegung</h2>
	<!-- Zusammenlegen - Ergebniss -->
	<div id="ui-resizable" class="ui-resizable">
	   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-e"></div>
	   <div style="-moz-user-select: none;"  class="ui-resizable-handle ui-resizable-s"></div>
	   <div  style="z-index: 1001; -moz-user-select: none;" class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se"></div>

		<div id="info" class="info">
			<div id="infodaten" class="infodaten">
				<div style="text-align:right;color:#000; cursor: pointer; cursor: hand;"><b id="info_close">schliessen  <img border="0" src="../../skin/images/cross.png" title="schliessen">&nbsp;</b></div>
					<script type="text/javascript">
					   $(document).ready(function()
					   {
						   $("#info_close").click(function(event)
						   {
					    	       $("div#info").hide("slow");
				   			});
						});
					</script>
				</div>
				<br>
				<div id="infodetail" class="infodetail">&nbsp;</div>
				<br>
			</div>
		</div>
	</div>

	<!-- Suchauswahl -->
	<table width="100%">
		<tr>
			<td colspan="2" align="center">
					&nbsp;Typ:&nbsp;<select id="firmentyp_kurzbz" name="firmentyp_kurzbz">
						<option value="">-- Alle --</option>';
					<?php
					foreach ($rows as $row)
						echo '<option value="'.$row->firmentyp_kurzbz.'" '.($row->firmentyp_kurzbz==$firmentyp_kurzbz1?' selected ':'').'>'.$row->firmentyp_kurzbz.'</option>';
					?>
					</select>
					<input style="display:none;" type="text" id="filter" name="filter" value="">
					&nbsp;<input type="button" name="suchen1" value="nur nach Typ suchen" onclick="$('#filter1').val('') ; $('#filter2').val('');set_firmen_search('geloescht','filter','firmentyp_kurzbz');set_firmen_search('bleibt','filter','firmentyp_kurzbz');">
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td width="50%" align="left">
			<table>
			<tr>
				<td>&nbsp;Suche:&nbsp;<input type="text" id="filter1" name="filter1" value="<?php echo $filter1;?>">
					&nbsp;<input type="button" name="suchen1" value="suchen" onclick="set_firmen_search('geloescht','filter1','firmentyp_kurzbz');">
				</td>
			</tr>
			</table>
			</td>
			<td width="50%" align="right">
			<table>
			<tr>
				<td>&nbsp;Suche:&nbsp;<input type="text" id="filter2" name="filter2" value="<?php echo $filter2;?>">
					&nbsp;<input type="button" name="suchen2" value="suchen" onclick="set_firmen_search('bleibt','filter2','firmentyp_kurzbz');">
				</td>
			</tr>
			</table>
			</td>
		</tr>
	</table>

	<hr>

	<!-- SuchErgebniss -->
	<table width="100%">
		<tr>
			<th width="50%">Das wird gel&ouml;scht</th>
			<th>&nbsp;<input type="button" name="aendern2" value="Zusammenlegen" onclick="set_firmen_zusammenlegen();">&nbsp;</th>
			<th width="50%">Das bleibt</th>
		</tr>
		<tr>
			<td valign="top" id="geloescht"></td>
			<td></td>
			<td valign="top" id="bleibt"></td>
		</tr>
	</table>
</body>
</html>
