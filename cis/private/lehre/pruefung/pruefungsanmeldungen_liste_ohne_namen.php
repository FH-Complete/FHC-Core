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
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/student.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');
require_once('../../../../include/studiengang.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
?><!DOCTYPE html>
<html moznomarginboxes="">
<head>
	<meta charset="UTF-8">
	<script type="text/javascript" src="../../../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<style type="text/css">
		body {
			margin: 0;
			padding: 0;
		}

		* {
			box-sizing: border-box;
			-moz-box-sizing: border-box;
		}

		#page {
			width: 210mm;
			min-height: 297mm;
			padding: 20mm;
			margin: 10mm auto;
			border: 1px #ffffff solid;
			border-radius: 5px;
			background: white;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			font-family: Arial, Helvetica;
		}

		#subpage {
			padding: 10mm;
			border: 1px white solid;
			height: 256mm;
			outline: 20mm;
		}

		#liste {
			border: 1px solid black;
			border-collapse: collapse;
			width: 100%;
			margin-top: 5mm;
			font-size: 11pt;
		}

		h1 {
			text-align: center;
		}

		.bold {
			font-weight: bold;
		}

		td {
			border: 1px solid black;
			padding: 1mm;
		}

		th {
			border: 1px solid black;
		}

		thead > tr {
			background-color: white !important;
		}

		tr:nth-child(odd){
			background-color: lightgrey;
		}

		span {
			line-height: 16pt;
			font-size: 12pt;
		}

		@page {
			size: A4;
			margin: 0;
		}

		@media print
		{
			html, body {
				width: 210mm;
				height: 250mm;
			}
			#page {
				margin: 0;
				border: initial;
				border-radius: initial;
				width: initial;
				min-height: initial;
				box-shadow: initial;
				background: initial;
				page-break-after: auto;
			}

			/*
			* Workaround um beim Drucken jede zweite Zeile der Tabelle
			* grau darzustellen. Standardmäßig werden von Browsern keine
			* Hintergrundfarben gedruckt.
			*/
			tr:nth-child(odd) > td{
				box-shadow: inset 0 0 0 1000px lightgrey;
			}

			//Veranlasst Chrome Hintergrundfarben zu drucken
			body{
				-webkit-print-color-adjust:exact;
				background-color: #FFFFFF;
				margin: 0;
			}

			//Anweisungen nur für Firefox
			@-moz-document url-prefix() {
				html, body {
				height: 280mm;
				}
			}
			@-moz-document url-prefix() {
				#page {
				padding: 15mm 25mm 25mm 15mm !important;
				}
			}
		}
	 </style>
	</head>
	<body>
	<script>
		$(document).ready(function()
		{
			window.print();
		});
	</script>
	<div id="page">
		<div id="subpage">
		<h1><?php echo $p->t('pruefung/anmeldungsliste'); ?></h1>
	<?php
	if(empty($pruefung->result) && !$rechte->isBerechtigt('lehre/pruefungsanmeldungAdmin'))
		die('Sie haben keine Berechtigung für diese Seite');

	$termin_id = filter_input(INPUT_GET,"termin_id");
	$lehrveranstaltung_id = filter_input(INPUT_GET,"lehrveranstaltung_id");
	$studiensemester = filter_input(INPUT_GET, "studiensemester");

	if(is_null($lehrveranstaltung_id))
	{
		die($p->t('pruefung/fehlenderParam_lvid'));
	}
	else if(is_null($termin_id))
	{
		die($p->t('pruefung/fehlenderParam_terminid'));
	}
	else if(is_null($studiensemester))
	{
		die($p->t('pruefung/fehlenderParam_studiensemester'));
	}
	else
	{
		$datum = new datum();
		$stdsem = new studiensemester($studiensemester);
		$pruefungsanmeldung = new pruefungsanmeldung();
		$anmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($termin_id, $lehrveranstaltung_id, $studiensemester, "bestaetigt");
		$lehrveranstaltung = new lehrveranstaltung($lehrveranstaltung_id);
		$einzeln = FALSE;
		if(!empty($anmeldungen))
		{
		$pruefung = new pruefungCis($anmeldungen[0]->pruefung_id);
		$pruefungstermin = new pruefungstermin($anmeldungen[0]->pruefungstermin_id);
		$mitarbeiter = new mitarbeiter($pruefung->mitarbeiter_uid);
		if($pruefung->einzeln)
		{
			$einzeln = TRUE;
			$pruefungsintervall = $pruefung->pruefungsintervall;
		}

		?>
		<span class="bold"><?php echo $p->t('global/lehrveranstaltung'); ?>: </span><span><?=$lehrveranstaltung->bezeichnung?></span><br/>
		<span class="bold"><?php echo $p->t('global/studiensemester'); ?>: </span><span><?=$stdsem->bezeichnung?></span><br/>
		<span class="bold"><?php echo $p->t('pruefung/pruefer'); ?>: </span><span><?=$mitarbeiter->getFullName(FALSE)?></span><br/>
		<table id="liste">
		<thead>
			<tr>
			<th>#</th>
			<th><?php echo $p->t('pruefung/matrikelnummer'); ?></th>
            <th><?php echo $p->t('pruefung/studiengangAbkuerzung'); ?></th>
			<th><?php echo $p->t('global/datum'); ?></th>
			<th><?php echo $p->t('benotungstool/note'); ?></th>
			<th><?php echo $p->t('global/anmerkung'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$count = 0;
			/*@var $anmeldung pruefungsanmeldung */
			foreach($anmeldungen as $anmeldung)
			{
				$student = new student($anmeldung->uid);
				$studiengang = new studiengang($student->studiengang_kz);
				$prfTermin = new pruefungstermin($anmeldung->pruefungstermin_id);

				if($einzeln)
				{
				$date = $datum->formatDatum($prfTermin->von, "Y-m-d H:i:s");
				$date = strtotime($date);
				$date = $date+(60*$pruefungsintervall*($anmeldung->reihung-1));
				$date = $datum->formatDatum($prfTermin->von,"d.m.Y").' - '.date("H:i",$date);
				$count++;
				}
				else
				{
				$date =  $datum->formatDatum($prfTermin->von,"d.m.Y - H:i");
				$count++;
				}
				echo '<tr>';
				echo '<td>'.$count.'</td>';
				echo '<td>'.$student->matr_nr.'</td>';
                echo '<td>'.$studiengang->kurzbzlang.'</td>';
				echo '<td>'.$date.'</td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '</tr>';
			}
			?>
		</tbody>
		<?php
		}
		else
		{
		?>

		<span><?php echo $p->t('pruefung/keineBestaetigtenAnmeldungenVorhanden'); ?></span><br/>

		<?php
		exit;
		}
	}
	?>
		</table>
		<br>
		<table width="100%" id="liste">
			<tr>
				<td width="26%"><?php echo $p->t('pruefung/derLektor'); ?></td>
				<td width="37%"><?php echo $mitarbeiter->getFullName(FALSE); ?></td>
				<td width="37%"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('pruefung/dieKommission'); ?></td>
				<td></td>
				<td></td>
			</tr
		</table>
		</div>
	</div>
	</body>
</html>
