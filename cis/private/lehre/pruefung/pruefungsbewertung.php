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
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$pruefung = new pruefungCis();
$pruefung->getPruefungByMitarbeiter($uid, $studiensemester->getaktorNext());
if (empty($pruefung->result) && !$rechte->isBerechtigt('lehre/pruefungsanmeldungAdmin'))
	die('Sie haben keine Berechtigung für diese Seite');

?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Prüfungsbewertung</title>
	<script src="../../../../include/js/datecheck.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script src="./pruefung.js.php"></script>
	<script src="./pruefungsbewertung.js.php"></script>
	<link rel="stylesheet" href="../../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css">
	<link rel="stylesheet" href="../../../../skin/fhcomplete.css">
	<link rel="stylesheet" href="../../../../skin/style.css.php">
	<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/theme.default.min.css">
	<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css">
	<style type="text/css">
		body {
			padding: 10px 0 0 10px;
		}

		#prfWrapper {
			position: absolute;
			height: 80%;
			width: 300px;
			padding: 1.8em 1.5em 1.8em 1em;
			border: 1px solid #dddddd;
		}

		#pruefungen {
			width: 94%;
			position: relative;
			float: left;
			padding: 0 1em 0em 1em;
			height: 100%;
			overflow: auto;
			overflow-x: hidden;
		}

		 #anmWrapper {
			position: absolute;
			left: 360px;
			width: 600px;
			height: 80%;
			padding: 1.8em 1.5em 1.8em 1em;
			border: 1px solid #dddddd;
		}

		#anmeldungen {
			height: 100%;
			overflow: auto;
			overflow-x: hidden;
		}

		#anmeldungen > div, h2 {
			padding: 0.5em;
		}

		#message {
			position: fixed;
			top: 0px;
			right: 0px;
			width: 50%;
			height: 2em;
			font-size: 1.5em;
			font-weight: bold;
		}

		.missingFormData {
			border: 2px solid red;
			outline: 2px solid red;
		}

		.modalOverlay {
			position: fixed;
			width: 100%;
			height: 100%;
			top: 0px;
			left: 0px;
			background-color: rgba(0,0,0,0.3); /* black semi-transparent */
		}

		.anmeldung {
			font-size: 1.2em;
			border-bottom: 1px solid black;
			margin: 0.5em 0 0 0.5em;
			padding: 0.5em 0 0 0.5em;
		}

		.saved {
			background-color: lightgreen;
		}

		.unsaved {
			background-color: lightpink;
		}

		#wrapper {
			display: none;
		}

		#lektor {
			margin: 0 0 1em 0;
		}
	</style>
	<script>
		$(document).ready(function()
		{
			var isFormHidden = true;
			$("#lektor").autocomplete({
				source: "lektor_autocomplete.php?autocomplete=lektor",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
						ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					}
				},
				select: function(event, ui)
				{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				if (ui.item.mitarbeiter_uid=='')
				{
					$("#mitarbeiter_uid").val(ui.item.uid);
					$("#uid").val("student");
				}
				else
				{
					$("#mitarbeiter_uid").val(ui.item.uid);
					$("#uid").val("lektor");
				}
				if (isFormHidden)
				{
					isFormHidden = false;
					$("#wrapper").slideToggle("slow");
				}
				loadPruefungenMitarbeiter();
				}
			});
		});
		</script>
</head>
<body>
	<div>
		<h1><?php echo $p->t('pruefung/pruefungsbewertungTitle'); ?></h1>
		<div>
		<?php
			if (!$rechte->isBerechtigt('lehre/pruefungsbeurteilungAdmin'))
			{
				echo '<input id="mitarbeiter_uid" type="hidden" value="'.$uid.'"/>
				<script>
				$(document).ready(function() {
					$("#wrapper").attr("style", "display: block");
					loadPruefungenMitarbeiter();
				});
				</script>';
			}
			else
			{
				echo '<span width="116px">'.$p->t('pruefung/pruefungLektor').': </span>';
				echo '<input placeholder="UID" type="text" id="lektor" value="" size="30"/>';
				echo '<input type="hidden" id="uid" value="" />';
				echo '<input type="hidden" id="mitarbeiter_uid" value="" />';
			}
			?>
		</div>
		<div id="wrapper">
			<div id='prfWrapper'>
				<div id='pruefungen'>
				<h2><?php echo $p->t('pruefung/pruefungPruefungenTitle'); ?></h2>
				<ul id="pruefungenListe">
				</ul>
				</div>
			</div>
			<div id='anmWrapper'>
				<div id="anmeldungen">
				<h2><?php echo $p->t('pruefung/pruefungsbewertungAnmeldungen'); ?></h2>
				<div id="anmeldeDaten">

				</div>
				</div>
			</div>
		</div>
		<div id="message"></div>
		<div id="progressbar"></div>
		<div id="modalOverlay"></div>
	</div>
	</body>
</html>
