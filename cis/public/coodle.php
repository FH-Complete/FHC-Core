<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>,
 * 			Andreas Österreicher <oesi@technikum-wien.at>
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/coodle.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/reservierung.class.php');
require_once('../../include/stunde.class.php');
require_once('../../include/stundenplan.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/globals.inc.php');

header("Content-Type: text/html; charset=utf-8");

$sprache = getSprache();
$p = new phrasen($sprache);
$sprache_obj = new sprache();
$sprache_obj->load($sprache);
$sprache_index = $sprache_obj->index;
$datum_obj = new datum();
$message = '';
$mailMessage = '';
$saveOk = null;
$ersteller = false;
$abgeschlossen = false;

$coodle_id = (isset($_GET['coodle_id']) ? $_GET['coodle_id'] : '');

$coodle = new coodle();
if (!$coodle->load($coodle_id))
	die($coodle->errormsg);

// Überprüfen ob Coodle Status laufend oder abgeschlossen hat
if (!$coodle->checkStatus($coodle_id))
	die($p->t('coodle/umfrageNichtGueltig'));

// authentifizierung
if (!isset($_GET['zugangscode']))
{
	$uid = get_uid();
	if (!$coodle->checkBerechtigung($coodle_id, $uid))
		die($p->t('coodle/keineBerechtigung'));

	// überprüfen ob ersteller gleich uid ist
	if ($coodle->ersteller_uid == $uid)
		$ersteller = true;
}
else
{
	if (!$coodle->checkBerechtigung($coodle_id, '', $_GET['zugangscode']))
		die($p->t('coodle/keineBerechtigung'));
}

// checkboxen speichern
if (isset($_POST['save']))
{
	$coodle_help = new coodle();
	$error = false;
	$teilnehmer_uid = '';

	// Ressource ID von Zugangscode oder UID holen und Beiträge löschen
	if (isset($_GET['zugangscode']))
	{
		// Einträge löschen
		$coodle_help->getRessourceFromUser($coodle_id, '', $_GET['zugangscode']);
		$coodle_ressource_termin = $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);
		$teilnehmer_uid = $coodle_help->coodle_ressource_id;
	}
	else
	{
		if ($coodle_help->RessourceExists($coodle_id, $uid))
		{
			$coodle_help->getRessourceFromUser($coodle_id, $uid);
			$coodle_ressource_termin = $coodle_help->deleteRessourceTermin($coodle_id, $coodle_help->coodle_ressource_id);
			$teilnehmer_uid = $coodle_help->coodle_ressource_id;
		}
	}

	// Einträge speichern
	foreach ($_POST as $key => $value)
	{
		if (mb_substr($key, 0, 5) == 'check')
		{
			$termin = explode('_', $key);
			$ressource_id = $termin[1];
			$termin_id = $termin[2];

			$coodle_ressource_termin = new coodle();
			$coodle_ressource_termin->coodle_ressource_id = $ressource_id;
			$coodle_ressource_termin->coodle_termin_id = $termin_id;
			$coodle_ressource_termin->new = true;

			if (!$coodle_ressource_termin->saveRessourceTermin())
				$error = true;
		}
	}

	if ($error)
	{
		$message .= '	<div class="alert alert-danger">
							<strong>Error!</strong> '.$p->t('global/fehlerBeimSpeichernDerDaten').'
						</div>';
	}
	else
	{
		$coodle_help->load($coodle_id);
		// email an ersteller senden wenn option aktiviert
		if ($coodle_help->mailversand && (!isset($_POST['auswahl_termin'])))
			sendBenachrichtigung($coodle_id, $teilnehmer_uid);

		$saveOk = true;
	}
}

// endgültige auswahl des termins speichern
if (isset($_POST['auswahl_termin']))
{
	if ($ersteller)
	{
		$auswahl = $_POST['auswahl_termin'];
		if ($auswahl != '')
		{
			// setzte auswahl von termin_id auf true
			$coodle_help = new coodle();
			$coodle_help->loadTermin($auswahl);
			$coodle_help->auswahl = true;

			// alle termine der coodle_id auf false setzen
			if (!$coodle_help->setTerminFalse($coodle_id))
				exit('Fehler beim Update aufgetreten');

			if (!$coodle_help->saveTermin(false))
			{
				$message .= '	<div class="alert alert-danger">
									<strong>Error!</strong> '.$p->t('global/fehlerBeimSpeichernDerDaten').'
								</div>';
			}
			else
			{
				$saveOk = true;
			}

			$coodle_status = new coodle();
			$coodle_status->load($coodle_id);
			$coodle_status->coodle_status_kurzbz = 'abgeschlossen';
			$coodle_status->new = false;
			$coodle_status->save();

			sendEmail($coodle_id);

			if ($coodle_help->datum < RES_TAGE_LEKTOR_BIS)
			{
				// Raum reservieren
				$coodle_raum = new coodle();
				$coodle_raum->getRaumeFromId($coodle_id);

				// Ende Uhrzeit berechnen
				$date = new DateTime($coodle_help->datum.' '.$coodle_help->uhrzeit);
				$interval = new DateInterval('PT'.$coodle->dauer.'M');
				$date->add($interval);
				$uhrzeit_ende = $date->format('H:i:s');

				foreach ($coodle_raum->result as $raum)
				{
					$stunde = new stunde();
					$stunden = $stunde->getStunden($coodle_help->uhrzeit, $uhrzeit_ende);

					// Pruefen ob der Raum frei ist
					if (!RaumBelegt($raum->ort_kurzbz, $coodle_help->datum, $stunden))
					{
						$reservierung_error = false;
						// Stunden reservieren
						foreach ($stunden as $stunde)
						{
							$raum_reservierung = new reservierung();
							$raum_reservierung->studiengang_kz = '0';
							$raum_reservierung->uid = $uid;
							$raum_reservierung->ort_kurzbz = $raum->ort_kurzbz;
							$raum_reservierung->datum = $coodle_help->datum;
							$raum_reservierung->stunde = $stunde;
							$raum_reservierung->titel = mb_substr($coodle->titel, 0, 10);
							$raum_reservierung->beschreibung = mb_substr($coodle->titel, 0, 32);
							$raum_reservierung->insertamum = date('Y-m-d H:i:s');
							$raum_reservierung->insertvon = $uid;

							// $message.= "Reserviere $raum->ort_kurzbz Stunde $stunde:";
							if (!$raum_reservierung->save(true))
								$reservierung_error = true;
						}
						$message .= '	<div class="alert alert-success">
											'.$p->t('coodle/raumErfolgreichReserviert', array($raum->ort_kurzbz)).'
										</div>';
					}
					else
					{
						$message .= '	<div class="alert alert-danger">
											'.$p->t('coodle/raumBelegt', array($raum->ort_kurzbz)).'
										</div>';
					}
				}
			}
			else
			{
				$message .= '	<div class="alert alert-danger">
									'.$p->t('coodle/raumNichtReserviert', array($datum_obj->formatDatum(RES_TAGE_LEKTOR_BIS, 'd.m.Y'))).'
								</div>';
			}
		}
	}
	else
	{
		$message .= '	<div class="alert alert-danger">
							'.$p->t('global/keineBerechtigung').'
						</div>';
	}
}

$coodle->load($coodle_id);

if ($coodle->coodle_status_kurzbz == 'abgeschlossen')
	$abgeschlossen = true;

if (isset($_GET['resend']))
{
	if ($ersteller && $abgeschlossen)
		sendEmail($coodle_id);
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css" type="text/css">
	<?php
	include('../../include/meta/jquery.php');
	?>
	<script type="text/javascript" src="../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
	<title><?php echo $p->t('coodle/terminauswahl'); ?></title>
	<script type="text/javascript">
		$(document).ready(function () {
			$("#disableCheckboxes").click(function () {
				$('input:checkbox').not(this).removeAttr('checked');
				if ($(this).is(':checked'))
					$('input:checkbox').not(this).attr('disabled', 'true');
				else
					$('input:checkbox').not(this).removeAttr('disabled');
			});
			if ($("#disableCheckboxes").is(':checked'))
				$('input:checkbox').not("#disableCheckboxes").attr('disabled', 'true');
		});

		function showInfotext() {
			$("#infotext").show();
		};

		function hideInfotext() {
			$("#infotext").hide();
		};
		window.setTimeout(function () {
			$("#success-alert").fadeTo(500, 0).slideUp(500, function () {
				$(this).remove();
			});
		}, 1500);

	</script>
	<style type="text/css">

		#header {
			background: #DCDDDF;
			border: 1px solid #c4c6ca;
			/*position: relative;*/
			padding-left: 50px;
		}

		.error {
			color: red;
			padding-left: 20px;
		}

		.normal, .normal_uhrzeit {
			padding: 2px 4px;
		}

		th.normal, th.auswahl {
			padding: 2px 4px;
			/*background-color: #dfdfdf;*/
		}

		tr.normal:hover {
			padding: 2px 4px;
			/*background-color: #dfdfdf;*/
		}

		th.normal_uhrzeit {
			/*background-color: #f5f5f5;*/
			/*border-color: #ddd;*/
			text-align: center;
		}

		#coodle_content th {
			/*color: #555555; */
			padding-left: 10px;
			padding-right: 10px;
			/*border-radius: 4px;*/
		}

		#coodle_content tr.owner td.owner {
			background-color: #DFF0D8;
			padding: 3px 4px;
			/*border-radius: 4px;*/
		}

		#coodle_content th.auswahl {
			/*background-color: #dfdfdf;*/
		}

		#coodle_content th.auswahl_uhrzeit {
			/*background-color: #EAEBED;*/
			border: 2px solid #D9534F;
			text-align: center;
		}

		a {
			color: #0086CC;
			text-decoration: none;
			cursor: pointer;
		}

		a:hover {
			color: Black;
			text-decoration: none;
		}

		#wrapper {
			width: 70%;
			padding: 15px;
			border: 1px solid #e3e3e3;
			background: #f5f5f5;
			text-align: left;
			border-radius: 4px;
		}

		#wrapper h4 {
			font-size: 1.5em;
			text-decoration: none;
		}

		.infotext {
			color: #8a6d3b;
			background-color: #fcf8e3;
			border: 1px solid #faebcc;
			padding: 6px;
			border-radius: 4px;
		}

		.abgeschlossen {
			width: 70%;
			color: #3c763d;
			background-color: #dff0d8;
			padding: 15px;
			border: 1px solid #d6e9c6;
			border-radius: 4px;
		}

		.footer {
			padding: 2px 4px;
			/*background-color: #EAEBED;*/
			text-align: center;
			border-radius: 4px;
		}

		.success {
			padding: 15px;
			margin: 20px;
			background-color: #dff0d8;
			color: #3c763d;
			text-align: center;
			border: 1px solid #d6e9c6;
			border-radius: 4px;
		}

		/* Custom Checkbox */
		.checkbox label:after,
		.radio label:after {
			content: '';
			display: table;
			clear: both;
		}

		.checkbox .cr,
		.radio .cr {
			position: relative;
			display: inline-block;
			border: 1px solid #a9a9a9;
			border-radius: .25em;
			width: 1.3em;
			height: 1.3em;
			float: left;
			margin-right: .5em;
			background-color: white;
		}

		.radio .cr {
			border-radius: 50%;
		}

		.checkbox .cr .cr-icon,
		.radio .cr .cr-icon {
			position: absolute;
			font-size: .8em;
			line-height: 0;
			top: 50%;
			left: 15%;
		}

		.radio .cr .cr-icon {
			margin-left: 0.04em;
		}

		.checkbox label input[type="checkbox"] {
			display: none;
		}

		.checkbox label input[type="checkbox"] + .cr > .cr-icon {
			transform: scale(3) rotateZ(-20deg);
			opacity: 0;
			transition: all .2s;
		}

		.checkbox label input[type="checkbox"]:checked + .cr > .cr-icon {
			transform: scale(1) rotateZ(0deg);
			opacity: 1;
		}

		.checkbox label input[type="checkbox"]:disabled + .cr {
			opacity: .5;
		}
	</style>
</head>
<div>
	<div class="container-fluid">
		<?php
		echo '<h2>'.$p->t('coodle/coodle').'</h2>';

		if (!isset($_GET['zugangscode']))
		{
			echo '<a href="'.APP_ROOT.'cis/private/coodle/uebersicht.php" class="btn btn-default" role="button">'.$p->t('coodle/zurueckZurUebersicht').'</a>';
			echo '<br><br>';
		}

		if ($saveOk === true)
		{
			echo '
				<div class="alert alert-success" id="success-alert2" style="width: 800px">
					<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
				</div>
				<div>
					<a href="'.$_SERVER['PHP_SELF'].'?coodle_id='.$coodle_id.'" class="btn btn-info" role="button">'.$p->t('coodle/zurueckZurUmfrage').'</a>
				</div>';
		}
		else
		{
			echo '<div id="wrapper">';

			$coodle_help = new coodle();
			$coodle_help->load($coodle_id);

			$alt = strtotime($coodle_help->insertamum);

			$differenz = time() - $alt;
			$differenz = $differenz / 86400;
			$benutzer = new benutzer();
			$benutzer->load($coodle->ersteller_uid);
			// $ersteller_name = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
			$ersteller_name = trim($benutzer->vorname.' '.$benutzer->nachname);
			echo '<h4>'.$coodle->titel.'</h4>';
			$erstellt = array(
				$ersteller_name,
				round($differenz)
			);
			echo '<span style="color: #555555">'.$p->t('coodle/erstelltVon', $erstellt).'</span><br>';
			echo '<span style="color: #555555">'.$p->t('coodle/dauer').': '.$coodle->dauer.' min.</span><br><br>';

			echo $coodle->beschreibung;

			echo '</div>';

			if ($abgeschlossen)
			{
				$datum = new datum();
				$coodle_abgeschlossen = new coodle();
				$termin_id = $coodle_abgeschlossen->getTerminAuswahl($coodle_id);
				$coodle_abgeschlossen->loadTermin($termin_id);
				echo '<br><div class="abgeschlossen">'.$p->t('coodle/umfrageAbgeschlossen', array(
						substr($tagbez[$sprache_index][$datum->formatDatum($coodle_abgeschlossen->datum, 'N')], 0, 2).', '.
						$datum->formatDatum($coodle_abgeschlossen->datum, 'd.m.Y').' '.
						$datum->formatDatum($coodle_abgeschlossen->uhrzeit, 'H:i')
					)).'</div>';
			}

			echo '<br>
			<div>';

			$coodle_ressourcen = new coodle();
			$coodle_ressourcen->getRessourcen($coodle_id);
			$mailadressen = '?subject=Coodle%20Umfrage%20'.htmlspecialchars($coodle->titel).'&amp;bcc=';

			foreach ($coodle_ressourcen->result as $ressource)
			{
				$name = '';
				$benutzer = new benutzer();

				// wenn uid gesetzt ist nimm uid
				if ($ressource->uid != '')
				{
					$benutzer->load($ressource->uid);
					// $name .= ($benutzer->titelpre!='')?$benutzer->titelpre.' ':'';
					$name .= $benutzer->vorname.' ';
					$name .= $benutzer->nachname.' ';
					// $name .= $benutzer->titelpost;

					$mailadressen .= $ressource->uid.'@'.DOMAIN.';';

					$ressource->anzeigename = $name;
					$ressource->vorname = $benutzer->vorname;
					$ressource->nachname = $benutzer->nachname;
				}

				// wenn uid nicht gesetzt ist nimm zugangscode
				if ($ressource->zugangscode != '' && $ressource->uid == '')
				{
					$name = $ressource->name;
					$ressource->anzeigename = $name;
					$ressource->vorname = $name;
					$ressource->nachname = $name;

					$mailadressen .= ';'.$ressource->email;
				}
			}

			// alle termine der coodle umfrage holen
			$coodle_termine = new coodle();
			$coodle_termine->getTermine($coodle_id);

			$datum = new datum();
			$datum_colspan = '';

			echo "<div id='coodle_content' >
						<form action='' method='POST'>
	
					<table class='table-bordered'>
					<tr><td></td>";
			// Für Colspan bei Datum
			$max_colspan = array();
			foreach ($coodle_termine->result as $termin)
			{
				$max_colspan[] .= $termin->datum;
			}

			foreach ($coodle_termine->result as $termin)
			{
				$class_auswahl = 'normal';
				$time = strtotime($termin->uhrzeit);
				$coodle_auswahl = new coodle();

				// Falls es schon eine Auswahl gibt - hervorheben
				if ($coodle_auswahl->checkTerminAuswahl($coodle_id, $termin->coodle_termin_id))
				{
					$class_auswahl = 'auswahl';
				}

				// Colspan für Datum berechnen
				$count = array_count_values($max_colspan);
				$colspan = $count[$termin->datum];

				if ($datum_colspan != $termin->datum && $termin->datum != '1900-01-01')
				{
					echo "<th colspan='$colspan' class='".$class_auswahl."' style='text-align: center'>
					<span style='color: #71787D'>".substr($monatsname[$sprache_index][$datum->formatDatum($termin->datum, 'n') - 1], 0, 3)."</span><br>
					<span style='font-size: x-large'>".$datum->formatDatum($termin->datum, 'd')."</span><br>
					<span style='color: #71787D'>".substr($tagbez[$sprache_index][$datum->formatDatum($termin->datum, 'N')], 0, 2)."</span>
					</th>";
				}

				$datum_colspan = $termin->datum;
			}
			if ($ersteller)
			{
				echo '<th></th>';
			}
			echo "</tr><tr>";
			echo '<td class="normal">';
			if ($ersteller && $abgeschlossen)
			{
				echo '<a href="mailto:'.$mailadressen.'" title="Mail an alle schicken"><span class="glyphicon glyphicon-envelope"></span></a>';
			}
			echo '</td>';

			foreach ($coodle_termine->result as $termin)
			{
				$class_auswahl = 'normal_uhrzeit';
				$time = strtotime($termin->uhrzeit);
				// Endzeit berechnen
				$ende = $time + ($coodle->dauer * 60);

				$coodle_auswahl = new coodle();

				// Falls es schon eine Auswahl gibt - hervorheben
				if ($coodle_auswahl->checkTerminAuswahl($coodle_id, $termin->coodle_termin_id))
				{
					$class_auswahl = 'auswahl_uhrzeit';
				}

				if ($termin->datum != '1900-01-01')
				{
					echo "<th class='".$class_auswahl."'>".date('H:i', $time)." -<br>".date('H:i', $ende)."&nbsp;&nbsp;</th>";
				}
				else
				{
					echo '<th class="'.$class_auswahl.'">'.$p->t('coodle/keinTerminMoeglich').'</th>';
				}
			}
			echo "</tr>";

			// Sortiert die Ressourcen alphabetisch nach anzeigename
			function sortRessourcen($a, $b)
			{
				return strcmp($a->nachname.''.$a->vorname, $b->nachname.''.$b->vorname);
			}

			usort($coodle_ressourcen->result, "sortRessourcen");

			$owner = false;
			// ressourcen durchlaufen
			foreach ($coodle_ressourcen->result as $ressource)
			{
				$owner = false;
				// Ist der User ident mit einer Ressource
				if (isset($_GET['zugangscode']) && $_GET['zugangscode'] == $ressource->zugangscode)
				{
					$owner = true;
				}
				if (!isset($_GET['zugangscode']) && $ressource->uid == $uid)
				{
					$owner = true;
				}

				if ($coodle_help->teilnehmer_anonym && !$owner)
				{
					continue;
				}
				else
				{

					// Ort-Ressourcen ueberspringen
					if ($ressource->ort_kurzbz != '')
					{
						continue;
					}

					$class = 'normal';
					// eigene Reihe farbig hervorheben
					if ($owner)
					{
						$class = 'owner';
					}
					// Bei anonymen TeilnehmerInnen entfaellt das Hervorheben
					if ($coodle_help->teilnehmer_anonym)
					{
						$class = 'normal';
					}

					echo "<tr class='".$class."'><td class='".$class."'>".$ressource->anzeigename."</td>";

					$termin_datum = '';
					$disabled = false;
					$checked = false;

					$coodle_ressource = new coodle();
					if (isset($_GET['zugangscode']))
					{
						$coodle_ressource->getRessourceFromUser($coodle_id, '', $_GET['zugangscode']);
						if ($ressource->coodle_ressource_id != $coodle_ressource->coodle_ressource_id)
						{
							$disabled = true;
						}
					}
					else
					{
						$coodle_ressource->getRessourceFromUser($coodle_id, $uid);
						if ($ressource->coodle_ressource_id != $coodle_ressource->coodle_ressource_id)
						{
							$disabled = true;
						}
					}

					if ($abgeschlossen)
					{
						$disabled = true;
					}

					// termine zu ressourcen anzeigen
					foreach ($coodle_termine->result as $termin)
					{
						$checked = false;
						$style = '';
						if ($coodle_termine->checkTermin($termin->coodle_termin_id, $ressource->coodle_ressource_id))
						{
							$checked = true;
						}

						if ($termin_datum != '' && $termin_datum != $termin->datum)
						{
							$style = 'style="border-left: 1px solid #DCDDDF;"';
						}

						if ($coodle_help->termine_anonym && !$owner && !$ersteller)
						{
							echo "<td class='normal' align='center'></td>";
						}
						else
						{
							if ($disabled)
							{
								if ($checked)
								{
									echo '<td class="'.$class.'" align="center" '.$style.'><span class="glyphicon glyphicon-ok"></span></td>';
								}
								else
								{
									echo '<td class="'.$class.'" align="center" '.$style.'></td>';
								}
							}
							else
							{
								// Der 01.01.1900 wird fuer "Keine Auswahl" verwendet. Beim anklicken der Checkbox werden alle anderen Checkboxen deaktiviert
								echo '	<td class="'.$class.'" align="center" '.$style.'>
									<div class="checkbox">
										<label style="font-size: 1.5em; padding-left: 10px">
											<input  type="checkbox" 
													value="" 
													'.($checked ? 'checked="checked"' : '').'
													'.($termin->datum == '1900-01-01' ? 'id="disableCheckboxes"' : '').'
													name="check_'.$ressource->coodle_ressource_id.'_'.$termin->coodle_termin_id.'"
											>
											<span class="cr" '.($termin->datum == '1900-01-01' ? 'style="background-color: #F2DEDE; border-color: #ebccd1;"' : '').'>
												<span class="cr-icon glyphicon glyphicon-ok"></span>
											</span>
										</label>
									</div>
								</td>';
							}
						}

						$termin_datum = $termin->datum;
					}
					if ($ersteller)
					{
						echo "<td></td>";
					}
					echo '</tr>';
				}
			}

			$disabled = $abgeschlossen ? 'disabled' : '';

			// Counter fuer Anzahl der Auswahlen pro Termin
			$counter_arr = array();
			foreach ($coodle_termine->result as $termin)
			{
				$countTermine = new coodle();
				$countTermine->countTermin($termin->coodle_termin_id);

				$counter_arr[] = $countTermine->anzahl;
			}
			if ($coodle_help->teilnehmer_anonym)
			{
				echo '<tr><td></td><td class="infotext" colspan="200">Die TeilnehmerInnen dieser Umfrage sind anonym</td></tr>';
			}
			elseif ($coodle_help->termine_anonym)
			{
				echo '<tr><td></td><td class="infotext" colspan="200">Die Terminwahl dieser Umfrage erfolgt anonym</td></tr>';
			}

			echo '<tr><td class="normal" style="color: #71787D">Summe der Einträge</td>';
			foreach ($coodle_termine->result as $termin)
			{
				$countTermine = new coodle();
				$countTermine->countTermin($termin->coodle_termin_id);

				if ($countTermine->anzahl == max($counter_arr))
				{
					echo '<td class="footer"><b>'.$countTermine->anzahl.'</b></td>';
				}
				else
				{
					echo '<td class="footer" style="color: #71787D">'.$countTermine->anzahl.'</td>';
				}
			}
			if ($ersteller)
			{
				echo '<td align="center" class="normal">'.$p->t('coodle/keineAuswahl').'</td>';
			}
			echo "</tr>";

			if ($ersteller)
			{
				// buttons für auswahl des endgültigen termins
				echo '<tr><td class="normal" style="background-color: #d9edf7">'.$p->t('coodle/auswahlEndtermin').'</td>';
				foreach ($coodle_termine->result as $termin)
				{
					$checked = ($termin->auswahl) ? 'checked' : '';
					if ($termin->datum != '1900-01-01')
					{
						echo '<td align="center" style="background-color: #d9edf7"><input type="radio" onclick="showInfotext();" name="auswahl_termin" '.$checked.' '.$disabled.' value='.$termin->coodle_termin_id.'></td>';
					}
					else
					{
						echo '<td align="center" style="background-color: #d9edf7"></td>';
					}
				}
				echo '<td align="center" style="background-color: #d9edf7"><input type="radio" onclick="hideInfotext();" name="auswahl_termin" '.$disabled.' value=""></td>';
				echo "</tr>";
			}

			echo '	<tr><td id="infotext" class="infotext" style="display: none" colspan="20">'.$p->t('coodle/auswahlHinweis').'</td></tr>';
			echo '</td></tr>
				</table>';
			if ($saveOk === true)
			{
				echo '
					<div class="success" id="success-alert">
						<strong>'.$p->t('global/erfolgreichgespeichert').'</strong>
					</div>';
			}
			echo '<br><input type="submit" class="btn btn-success '.$disabled.'" value="'.$p->t('global/speichern').'" name="save" '.$disabled.'>';
		}

		// Benutzer mit CIS-Account können die Terminzusagen als iCal importieren
		if (isset($uid) && $uid != '')
		{
			echo '<br><br><div class="alert alert-info" style="width: 800px">
			<span class="glyphicon glyphicon-info-sign"></span>
			Sie können ihre vorläufigen Terminzusagen in ihr Kalendersystem einbinden.<br>
			Importieren Sie dazu die .ics-Datei aus folgendem Link in ihren Kalender:<br>
			<a href="'.APP_ROOT.'cis/public/ical_coodle.php/'.$uid.'" target="_blank">
			'.APP_ROOT.'cis/public/ical_coodle.php/'.$uid.'
			</a>
			<br><br>
			Die Datei enthält ihre Terminzusagen aus <u>allen laufenden Umfragen</u> in anonymisierter Form.
			</div>';
		}

		if ($ersteller && $abgeschlossen)
		{
			echo '&nbsp;&nbsp;<input type="button" class="btn btn-success" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?coodle_id='.$coodle_id.'&resend\'" value="'.$p->t('coodle/einladungNeuVerschicken').'">';
		}
		echo '</form></div>';

		echo '<br>'.$message;

		echo $mailMessage;

		?>
	</div>
</div>
</body>
</html>

<?php

/**
 * Sendet eine Email an den Ersteller der Umfrage
 *
 * @param type $ersteller
 */
function sendBenachrichtigung($coodle_id, $teilnehmer_id)
{
	global $uid;
	global $coodle;
	$coodle_send = new coodle();

	if (!$coodle_send->load($coodle_id))
	{
		die("Fehler beim senden aufgetreten");
	}

	$mitarbeiter = new mitarbeiter();
	$person = new person();
	$teilnehmer = new coodle();

	$teilnehmer->getRessourceFromId($teilnehmer_id);
	if ($teilnehmer->zugangscode != '')
		$tn = $teilnehmer->name;
	else
	{
		$mitarbeiter->load($teilnehmer->uid);
		$person->load($mitarbeiter->person_id);
		$tn = $person->vorname." ".$person->nachname;
	}
	$mitarbeiter->load($coodle_send->ersteller_uid);
	$person->load($mitarbeiter->person_id);

	$email = '';

	$name = '';
	$name .= ($person->titelpre != '') ? $person->titelpre.' ' : '';
	$name .= $person->vorname.' '.$person->nachname;
	$name .= ($person->titelpost != '') ? ' '.$person->titelpost : '';

	if ($person->geschlecht == 'w')
		$email .= 'Sehr geehrte Frau '.$name."!<br><br>";
	else
		$email .= "Sehr geehrter Herr ".$name."!<br><br>";

	$link = APP_ROOT.'cis/public/coodle.php?coodle_id='.urlencode($coodle_id).'&uid='.urlencode($uid);
	$email .= $tn.' hat einen Termin zu Ihrer Coodle-Umfrage mit dem Thema "'.$coodle->titel.'" ausgewählt.<br><a href="'.$link.'">Link zu Ihrer Coodle Umfrage</a><br><br>Mit freundlichen Grüßen <br><br>
		Fachhochschule Technikum Wien<br>
		Höchstädtplatz 6<br>
		1200 Wien';

	$mail = new mail($coodle_send->ersteller_uid.'@'.DOMAIN, 'no-reply', 'Feedback zu Ihrer Coodle Umfrage "'.$coodle->titel.'"', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if (!$mail->send())
		die("Fehler beim senden des Mails aufgetreten");
}

/**
 * Funktion sendet den ausgewählten Termin an alle Ressourcen aus der übergebenen Coodleumfrage
 *
 * @param type $coodle_id
 * @param type $auswahl
 * @global phrasen $p
 */
function sendEmail($coodle_id)
{
	global $mailMessage, $tagbez, $sprache_index;
	global $p;
	$coodle_help = new coodle();
	$termin_id = $coodle_help->getTerminAuswahl($coodle_id);
	$coodle_help->loadTermin($termin_id);

	$coodle_ressource = new coodle();
	$coodle_ressource->getRessourcen($coodle_id);
	$coodle = new coodle();
	$coodle->load($coodle_id);
	$ort = '';
	$teilnehmer = '';
	foreach ($coodle_ressource->result as $row)
	{
		if ($row->ort_kurzbz != '')
		{
			if ($ort != '')
				$ort .= ', ';
			$ort .= "$row->ort_kurzbz";
		}
		else
		{
			if ($row->uid != '')
			{
				$benutzer = new benutzer();
				$benutzer->load($row->uid);
				$name = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
				$mail = $row->uid.'@'.DOMAIN;
			}
			else
			{
				$mail = $row->email;
				$name = $row->name;
			}
			$coodle_ressource_termin = new coodle();
			$partstat = '';
			if ($coodle_ressource_termin->checkTermin($termin_id, $row->coodle_ressource_id))
				$partstat = 'ACCEPTED';
			else
				$partstat = 'TENTATIVE';

			$teilnehmer .= 'ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT='.$partstat.';CN='.$name."\n :MAILTO:".$mail."\n";
		}
	}
	$date = new DateTime($coodle_help->datum.' '.$coodle_help->uhrzeit);
	// Datum des Termins ins richtige Format bringen
	$dtstart = $date->format('Ymd\THis');

	// Ende Datum berechnen
	$interval = new DateInterval('PT'.$coodle->dauer.'M');
	$date->add($interval);
	$dtend = $date->format('Ymd\THis');
	$date = new DateTime();
	$dtstamp = $date->format('Ymd\THis');
	$benutzer = new benutzer();
	$benutzer->load($coodle->ersteller_uid);
	$erstellername = trim($benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost);
	// Ical File erstellen
	$ical = "BEGIN:VCALENDAR
PRODID:-//Microsoft Corporation//Outlook 11.0 MIMEDIR//EN
VERSION:2.0
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+02:00
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
ORGANIZER:MAILTO:".$erstellername." <".$coodle->ersteller_uid."@".DOMAIN."
".$teilnehmer."
DTSTART;TZID=Europe/Vienna:".$dtstart."
DTEND;TZID=Europe/Vienna:".$dtend."
LOCATION:".$ort."
TRANSP:OPAQUE
SEQUENCE:0
UID:FHCompleteCoodle".$coodle_id."
DTSTAMP;TZID=Europe/Vienna:".$dtstamp."
DESCRIPTION:".strip_tags(html_entity_decode($coodle->beschreibung, ENT_QUOTES, 'UTF-8'))."
SUMMARY:".strip_tags($coodle->titel)."
PRIORITY:5
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR";

	if (count($coodle_ressource->result) > 0)
	{
		$mailMessageError = '';
		$mailMessageSuccess = '<div class="alert alert-success">';
		foreach ($coodle_ressource->result as $row)
		{
			if ($row->uid != '')
			{
				$benutzer = new benutzer();
				if (!$benutzer->load($row->uid))
				{
					$mailMessageError .= "Fehler beim Laden des Benutzers ".$coodle_ressource->convert_html_chars($row->uid);
					continue;
				}

				if ($benutzer->geschlecht == 'w')
					$anrede = "Sehr geehrte Frau ";
				else
					$anrede = "Sehr geehrter Herr ";

				$anrede .= $benutzer->titelpre.' '.$benutzer->vorname.' '.$benutzer->nachname.' '.$benutzer->titelpost;

				// Interner Teilnehmer
				$email = $row->uid.'@'.DOMAIN;
			}
			elseif ($row->email != '')
			{
				// Externe Teilnehmer
				$email = $row->email;
				$anrede = 'Sehr geehrte(r) Herr/Frau '.$row->name;
			}
			else
			{
				// Raueme bekommen kein Mail
				continue;
			}
			$anrede = trim($anrede);
			$sign = $p->t('mail/signatur');

			$datum = new datum();

			$html = $anrede.'!<br><br>
				Die Terminumfrage zum Thema "'.$coodle_ressource->convert_html_chars($coodle->titel).'" ist beendet.
				<br>
				Der Termin wurde auf <b>
				'.substr($tagbez[$sprache_index][$datum->formatDatum($coodle_help->datum, 'N')], 0, 2).', '.
				$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.
				$datum->formatDatum($coodle_help->uhrzeit, 'H:i').'
				</b> festgelegt.
				<br><br>'.nl2br($sign);

			$text = $anrede."!\n\nDie Terminumfrage zum Thema \"".$coodle_help->convert_html_chars($coodle->titel).'"\" ist beendet.\n
				Der Termin wurde auf <b>
				'.substr($tagbez[$sprache_index][$datum->formatDatum($coodle_help->datum, 'N')], 0, 2).', '.
				$datum->formatDatum($coodle_help->datum, 'd.m.Y').' '.
				$datum->formatDatum($coodle_help->uhrzeit, 'H:i')."
				</b> festgelegt\n.
				\n\n$sign";

			$mail = new mail($email, 'no-reply@'.DOMAIN, 'Terminbestätigung - '.$coodle->titel, $text);
			$mail->setHTMLContent($html);
			// ICal Termineinladung hinzufuegen
			$mail->addAttachmentPlain($ical, 'text/calendar', 'meeting.ics');
			if ($mail->send())
			{
				$mailMessageSuccess .= $p->t('coodle/mailVersandtAn', array(
						$email
					))."<br>";
			}
		}
		if ($mailMessageError != '')
		{
			$mailMessageError = '<div class="alert alert-error">'.$mailMessageError.'</div>';
		}
		$mailMessageSuccess .= '</div>';
		$mailMessage = $mailMessageError.$mailMessageSuccess;
	}
	else
	{
		die($p->t('coodle/keineRessourcenVorhanden'));
	}
}

/**
 *
 * Prueft ob ein Raum belegt ist
 *
 * @param $ort_kurzbz
 * @param $datum
 * @param array $stunden
 */
function RaumBelegt($ort_kurzbz, $datum, $stunden)
{
	foreach ($stunden as $stunde)
	{
		// Reservierungen pruefen
		$raum_reservierung = new reservierung();
		if ($raum_reservierung->isReserviert($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}

		// Stundenplan abfragen
		$stundenplan = new stundenplan('stundenplan');
		if ($stundenplan->isBelegt($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}

		// Stundenplan DEV abfragen
		$stundenplan = new stundenplan('stundenplandev');
		if ($stundenplan->isBelegt($ort_kurzbz, $datum, $stunde))
		{
			return true;
		}
	}
	return false;
}

?>