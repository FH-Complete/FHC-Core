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
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/studiensemester.class.php');
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
		<title><?php echo $p->t('pruefung/anmeldungenVerwaltenTitle'); ?></title>
		<script src="../../../../include/js/datecheck.js"></script>
		<script type="text/javascript" src="../../../../vendor/components/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script src="./pruefung.js.php"></script>
		<link rel="stylesheet" href="../../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css">
		<link rel="stylesheet" href="../../../../skin/fhcomplete.css">
		<link rel="stylesheet" href="../../../../skin/style.css.php">
		<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/theme.default.min.css">
		<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css">

	<style type="text/css">
		body {
		padding: 10px 0 0 10px;
		}

		#stgWrapper {
		position: absolute;
		height: 80px;
		width: 850px;
		padding: 1.8em 1.5em 1.8em 1em;
		/*border-radius: 25px;*/
		border: 1px solid #dddddd;
		/*box-shadow: 0em 0em 2em 0.5em #888888 inset;*/
		}

		#studiengaenge {
		/*border: 1px solid black;*/
		width: 94%;
		position: relative;
		float: left;
		padding: 0 1em 0em 1em;
		height: 100%;
		overflow: auto;
		overflow-x: hidden;
		}


		#prfWrapper {
		position: absolute;
		height: 70%;
		width: 40%;
		top: 180px;
		padding: 1.8em 1.5em 1.8em 1em;
		/*border-radius: 25px;*/
		border: 1px solid #dddddd;
		/*box-shadow: 0em 0em 2em 0.5em #888888 inset;*/
		}

		#pruefungen {
		/*border: 1px solid black;*/
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
		/*top: 45px;*/
		left: 45%;
		top: 180px;
		width: 40%;
		height: 70%;
		padding: 1.8em 1.5em 1.8em 1em;
		/*border-radius: 25px;*/
		border: 1px solid #dddddd;
		/*box-shadow: 0em 0em 2em 0.5em #888888 inset;*/
		}

		#anmeldungen {
		height: 100%;
		overflow: auto;
		overflow-x: hidden;
		}

		#anmeldungen > * {
		padding: 0.5em;
		}

		#raum > * {
		margin-bottom: 0.5em;
		}

		#message {
		position: fixed;
		bottom: 0px;
		width: 100%;
		height: 2em;
		font-size: 1.5em;
		font-weight: bold;
		}

		#sortable {
		list-style-type: none;
		margin: 0;
		padding: 0;
		width: 100%;
		}
		#sortable li {
		margin: 0 3px 3px 3px;
		padding: 0.2em 0.4em 0.4em;
		padding-left: 1.5em;
		font-size: 1.4em;
		height: 18px;
		list-style-image: none;
		display: block;
		}
		#sortable li span {
		/*position: absolute;*/
		margin-left: -1.3em;
		float:left;
		}

		.resultOK {
		color: green;
		}

		.resultNotOK {
		color: red;
		}

		#sortable li a {
		float: left;
		}

		#sortable li div {
		float: right;
		margin-left: 5px;
		font-size: 0.8em;
		}

		.anmerkungInfo {
		text-align: right;
		width: 10%;
		}

		#progressbar {
		position: fixed;
		width: 300px;
		top: 30%;
		left: 50%;
		margin-left: -150px;
		z-index: 100;
		background: '#9CFF29';
		}
		.modalOverlay {
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
		background-color: rgba(0,0,0,0.3); /* black semi-transparent */
		}

		.studiengang {
		font-size: 1em;
		font-weight: bold;
		}

		#studiengaenge > div {
		float: left;
		width: 50%;
		}

	</style>
	</head>
	<body>
	<script>
		$(document).ready(function()
		{
			$("#filter_studiensemester").css("visibility","visible");

			$("#raumDialog").dialog({
				modal: true,
				autoOpen: false,
				width: "400px"
			});

			$("#kommentarDialog").dialog({
				modal: true,
				autoOpen: false,
				width: "400px",
				buttons: {
					Ok: function() {
						$(this).dialog('close');
					}
				}
			});

			$("#table4").tablesorter({
				widgets: ["zebra"],
				headers: {
					0: { sorter: false },
					3: { sorter: 'shortDate',
						dateFormat: 'ddmmyyyy' },
					4: { sorter: 'time' },
					5: { sorter: 'time' }
				}
			});


			$('#zusammenlegen').on('click', function() {
				let ausgewaehlte_termine = $('.termin-checkbox:checked');

				if (ausgewaehlte_termine.length === 0)
					return;

				let erster_termin = ausgewaehlte_termine.first();
				let erstes_datum = erster_termin.data('datum');
				let erste_lvid = erster_termin.data('lv-id');

				let termine = [];
				ausgewaehlte_termine.each(function() {{
					let termin = $(this);
					let datum = termin.data('datum');
					let lv_id = termin.data('lv-id');

					if (erstes_datum !== datum)
						return alert("Die ausgewählten Termine liegen nicht am selben Tag und können daher nicht zusammengelegt werden.")
					if (erste_lvid !== lv_id)
						return alert("Bei den ausgewählten Terminen handelt es sich um unterschiedliche Lehrveranstaltungen, die daher nicht zusammengelegt werden können.")

					termine.push(termin.data('termin-id'));
				}})

				if (termine.length > 0)
				{
					terminezusammenlegen(termine, erste_lvid);
				}
			})

			loadPruefungStudiengang();
		});
	</script>
	<h1><?php echo $p->t('pruefung/anmeldungenVerwalten'); ?></h1>
	<div id='stgWrapper'>

		<div>
			<h2><?php echo $p->t('global/studiensemester'); ?></h2>
			<?php
			echo '<select id="filter_studiensemester" onchange="loadPruefungStudiengang();" style="visibility: hidden;">';
			$aktuellesSemester = $studiensemester->getaktorNext();
			$studiensemester->getPlusMinus(null, 5);
			foreach($studiensemester->studiensemester as $sem)
			{
				if ($aktuellesSemester == $sem->studiensemester_kurzbz)
				{
				echo '<option selected value="'.$sem->studiensemester_kurzbz.'">'.$sem->bezeichnung.'</option>';
				}
				else
				{
				echo '<option value="'.$sem->studiensemester_kurzbz.'">'.$sem->bezeichnung.'</option>';
				}
			}
			   echo '</select></p>';
		   ?>
		</div>
		</div>
	</div>
	<div id='prfWrapper'>
		<div id='pruefungen'>
		<h2><?php echo $p->t('pruefung/pruefungPruefungenTitle'); ?></h2>
		<button id="zusammenlegen">Termine zusammenlegen</button>
		<table id="table4" class="tablesorter" style="display:none">
			<thead>
				<tr>
					<th></th>
					<th><?php echo $p->t('global/studiengang'); ?></th>
					<th><?php echo $p->t('global/lehrveranstaltung'); ?></th>
					<th><?php echo $p->t('global/datum'); ?></th>
					<th><?php echo $p->t('global/von'); ?></th>
					<th><?php echo $p->t('global/bis'); ?></th>
					<th><?php echo $p->t('pruefung/pruefungsbewertungAnmeldungen'); ?></th>
				</tr>
			</thead>
			<tbody id="pruefungenListe"></tbody>
		</table>
		</div>
	</div>
	<div id='anmWrapper'>
		<div id="anmeldungen">
		<h2><?php echo $p->t('pruefung/pruefungsbewertungAnmeldungen'); ?> <span id='lvdaten'></span></h2>
		<div id="anmeldung_hinzufuegen">

		</div>
		<div id="anmeldeDaten">

		</div>
		<div id="reihungSpeichernButton">

		</div>
		<div id="kommentar">

		</div>
		<div id="kommentarSpeichernButton">

		</div>
		<div id="raumLink">

		</div>
		<div id="listeDrucken">

		</div>
		<div id="raumDialog">
			<div id="raum">

			</div>
			<div id="raumSpeichernButton">

			</div>
		</div>
		</div>
		<div id="kommentarDialog" title="<?php echo $p->t('pruefung/anmerkungDesStudenten'); ?>" style="display:none;">
			<div id="kommentarimDialog"></div>
		</div>
	</div>

	<div id="message"></div>
	<div id="progressbar"></div>
	</body>
</html>
