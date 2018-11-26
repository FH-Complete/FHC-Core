<?php
/* Copyright (C) 2018 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
/**
 * Testclient fuer Abfrage der REST Webservice Schnittstelle des Datenverbundes
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/dvb.class.php');
require_once('../include/errorhandler.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if (!$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

$db = new basis_db();

if (isset($_GET['action']))
	$action = $_GET['action'];
else
	$action = 'getBySvnr';

$username = DVB_USERNAME;
$password = DVB_PASSWORD;

$studienjahr = filter_input(INPUT_POST, 'studienjahr');
$matrikelnr = filter_input(INPUT_POST, 'matrikelnummer');
$nachname = filter_input(INPUT_POST, 'nachname');
$vorname = filter_input(INPUT_POST, 'vorname');
$geburtsdatum = filter_input(INPUT_POST, 'geburtsdatum');
$geschlecht = filter_input(INPUT_POST, 'geschlecht');
$postleitzahl = filter_input(INPUT_POST, 'postleitzahl');
$staat = filter_input(INPUT_POST, 'staat');
$matura = filter_input(INPUT_POST, 'matura');
$svnr = filter_input(INPUT_POST, 'svnr');
$ersatzkennzeichen = filter_input(INPUT_POST, 'ersatzkennzeichen');
$person_id = filter_input(INPUT_POST, 'person_id');
$strasse = filter_input(INPUT_POST, 'strasse');

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Datenverbund-Client</title>
</head>
<body>
	<h1>Testclient für Datenverbund-Webservice</h1>
	<ul>
		<li><a href="datenverbund_client.php?action=getOAuth">OAuth Token anfordern</a></li>
		<li><a href="datenverbund_client.php?action=getBySvnr">Matrikelnummer nach SVNR suchen</a></li>
		<li>
			<a href="datenverbund_client.php?action=getByErsatzkennzeichen">
			Matrikelnummer nach Ersatzkennzeichen suchen
			</a>
		</li>
		<li><a href="datenverbund_client.php?action=getByNachname">Matrikelnummer nach Nachname suchen</a></li>
		<li><a href="datenverbund_client.php?action=getReservations">Matrikelnummer Reservierungen anzeigen</a></li>
		<li><a href="datenverbund_client.php?action=getKontingent">Matrikelnummer Kontingent anfordern</a></li>
		<li><a href="datenverbund_client.php?action=setMatrikelnummer">Matrikelnummer Vergabe melden</a></li>
		<li><a href="datenverbund_client.php?action=assignMatrikelnummer">Gesamtprozess (Abfrage, ggf Vergabemeldung, Speichern bei Person)</a></li>
		<li><a href="datenverbund_client.php?action=getBPK">BPK ermitteln</a></li>
		<li><a href="datenverbund_client.php?action=pruefeBPK">BPK ermitteln manuell</a></li>
	</ul>
	<?php
	echo "<br>Portal: ".DVB_PORTAL;
	echo "<br>Bildungseinrichtung: ".DVB_BILDUNGSEINRICHTUNG_CODE;
	?>
	<br><br>
	<form action="<?php echo $_SERVER['PHP_SELF'].'?action='.$action; ?>" method="post">
		<table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	<?php
	/**
	 * Erstellt eine Tabllezeile mit Input-Feld
	 * @param string $name Name des Inputs.
	 * @param string $title Titel der Zeile.
	 * @param string $value Value des Inputs.
	 * @param string $hint Hinweistext zu Inputfeld.
	 * @param int $maxlength Maximallaenge des Eingabefeldes.
	 * @return void
	 */
	function printrow($name, $title, $value, $hint = '', $maxlength = 15, $type = 'text')
	{
		global $db;

		echo '
		<tr>
			<td align="right">'.$title.':</td>
			<td>
				<input name="'.$name.'" type="'.$type.'" size="30" maxlength="'.$maxlength.'"
					value="'.$db->convert_html_chars($value).'"> '.$hint.'
			</td>
		</tr>';
	}

	switch($action)
	{
		case 'getOAuth':
			break;

		case 'getBySvnr':
			printrow('svnr', 'SVNR', $svnr);
			break;

		case 'getByErsatzkennzeichen':
			printrow('ersatzkennzeichen', 'Ersatzkennzeichen', $ersatzkennzeichen);
			break;
		case 'getByNachname':
			printrow('nachname', 'Nachname', $nachname);
			printrow('geburtsdatum', 'Geburtsdatum', $geburtsdatum, ' (Format: YYYYMMDD)', 8);
			break;

		case 'getReservations':
		case 'getKontingent':
			printrow('studienjahr', 'Studienjahr', $studienjahr, 'zB 2016 (für WS2016 und SS2017)', 4);
			break;

		case 'setMatrikelnummer':
			printrow('matrikelnummer', 'Matrikelnummer', $matrikelnr);
			printrow('nachname', 'Nachname', $nachname, '', 255);
			printrow('vorname', 'Vorname', $vorname, '', 30);
			printrow('geburtsdatum', 'Geburtsdatum', $geburtsdatum, 'Format: YYYYMMDD', 10);
			printrow('geschlecht', 'Geschlecht', $geschlecht, 'Format: M | W', 1);
			printrow('postleitzahl', 'Postleitzahl', $postleitzahl, '', 10);
			printrow('staat', 'Staat', $staat, '1-3 Stellen Codex (zb A für Österreich)', 3);
			printrow('svnr', 'SVNR', $svnr);
			printrow('matura', 'Maturadatum', $matura, 'Format: YYYYMMDD (optional)', 10);
			break;

		case 'assignMatrikelnummer':
			printrow('person_id', 'PersonID', $person_id);
			break;

		case 'getBPK':
			printrow('person_id', 'PersonID', $person_id);
			break;

		case 'pruefeBPK':
			printrow('nachname', 'Nachname', $nachname, '', 255);
			printrow('vorname', 'Vorname', $vorname, '', 30);
			printrow('geburtsdatum', 'Geburtsdatum', $geburtsdatum, 'Format: YYYYMMDD', 10);
			printrow('geschlecht', 'Geschlecht', $geschlecht, 'Format: M | W', 1);
			printrow('postleitzahl', 'Postleitzahl', $postleitzahl, 'optional', 10);
			printrow('strasse', 'Strasse', $strasse, 'optional', 255);

			break;

		default:
			echo "Unknown action";
			break;
	}

	echo '
			<tr>
				<td align="right">Debug</td>
				<td><input name="debug" type="checkbox" '.(isset($_POST['debug'])?'checked="checked"':'').' /></td>
			</tr>';
	?>

			<tr>
				<td align="right"></td>
				<td>
					<input type="submit" value=" Absenden " name="submit">
				</td>
			</tr>
		</table>
	</form>
<?php

if (isset($_REQUEST['submit']))
{
	$dvb = new dvb($username, $password, isset($_POST['debug']));
	switch ($action)
	{
		case 'getOAuth':
			$result = $dvb->authenticate();
			if (ErrorHandler::isSuccess($result))
				echo '<br><b>OAuth Bearer Token:</b> '.$dvb->authentication->access_token;
			else
				echo '<br><b>Failed:</b> '.$dvb->errormsg;
			break;

		case 'getBySvnr':
			$data = $dvb->getMatrikelnrBySVNR($_POST['svnr']);

			if(ErrorHandler::isSuccess($data) && ErrorHandler::hasData($data))
			{
					echo '<br><b>Matrikelnummer vorhanden:</b> '.$data->retval->matrikelnummer;
					if(isset($data->retval->bpk) && $data->retval->bpk!='')
						echo '<br><b>BPK vorhanden:</b> '.$data->retval->bpk;
			}
			else
			{
					echo '<br><b>Matrikelnummer nicht vorhanden:</b>'.$data->errormsg;
			}
			break;

		case 'getByErsatzkennzeichen':
			$data = $dvb->getMatrikelnrByErsatzkennzeichen($_POST['ersatzkennzeichen']);

			if (ErrorHandler::isSuccess($data) && ErrorHandler::hasData($data))
				echo '<br><b>Matrikelnummer vorhanden:</b>'.$data->retval->matrikelnummer;
			else
				echo '<br><b>Matrikelnummer nicht vorhanden:</b>'.$data->errormsg;
			break;

		case 'getByNachname':
			$data = $dvb->getMatrikelnrByNachname($_POST['nachname'], $_POST['geburtsdatum']);

			if(ErrorHandler::isSuccess($data) && ErrorHandler::hasData($data))
			{
				if(isset($data->retval->data) && is_array($data->retval->data) && count($data->retval->data)>0)
				{
					echo '<br><b>Daten gefunden:</b> ';
					var_dump($data->retval);
				}
				else
				{
					echo 'keine Einträge gefunden';
				}
			}
			else
			{
					echo '<br><b>Matrikelnummer nicht vorhanden:</b>'.$data->errormsg;
			}
			break;
		case 'getReservations':
			 $result = $dvb->getReservations(DVB_BILDUNGSEINRICHTUNG_CODE, $_POST['studienjahr']);
			 if(ErrorHandler::isSuccess($result) && ErrorHandler::hasData($result))
			 {
			 	$reservierteNummern = $result->retval->reservations;
				if ($reservierteNummern !== false)
					echo '<br><b>Reservierte Nummern:</b>'.print_r($reservierteNummern, true);
				else
					echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			}
			break;

		case 'getKontingent':
			$result = $dvb->getKontingent(DVB_BILDUNGSEINRICHTUNG_CODE, $_POST['studienjahr']);

			if(ErrorHandler::isSuccess($result) && ErrorHandler::hasData($result))
			{
				$kontingent = $result->retval->kontingent;
				if ($kontingent !== false)
					echo '<br><b>Kontingent:</b>'.print_r($kontingent, true);
				else
					echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			}
			break;

		case 'setMatrikelnummer':
			$person = new stdClass();
			$person->matrikelnummer = $matrikelnr;
			$person->vorname = $vorname;
			$person->nachname = $nachname;
			$person->geburtsdatum = $geburtsdatum;
			$person->geschlecht = $geschlecht;
			$person->staat = $staat;
			$person->plz = $postleitzahl;
			$person->matura = $matura; // Optional
			$person->svnr = $svnr; // Optional

			$result = $dvb->setMatrikelnummer(DVB_BILDUNGSEINRICHTUNG_CODE, $person);

			if (ErrorHandler::isSuccess($result))
				echo '<br><b>Erfolgreich gemeldet</b>';
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			break;

		case 'assignMatrikelnummer':
			$result = $dvb->assignMatrikelnummer($person_id);
			if(ErrorHandler::isSuccess($result))
			{
				echo '<br><b>OK</b>';
			}
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			break;

		case 'getBPK':
			$data = $dvb->getBPK($person_id);
			if(ErrorHandler::isSuccess($data))
			{
				echo '<br><b>OK BPK:</b> '.$data->retval->bpk;
			}
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			break;

		case 'pruefeBPK':
			$data = $dvb->pruefeBPK($geburtsdatum, $vorname, $nachname, $geschlecht, $postleitzahl, $strasse);
			if(ErrorHandler::isSuccess($data))
			{
				echo '<br><b>OK BPK:</b> '.$data->retval->bpk;
			}
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$data->errormsg;
			break;
		default:
			echo "Unknown action";
			break;
	}
	if (isset($_POST['debug']))
		echo '<div style="color: gray">'.nl2br(htmlentities($dvb->debug_output)).'</div>';
}

?>
</body>
</html>
