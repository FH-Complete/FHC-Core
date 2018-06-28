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

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$bildungseinrichtung = filter_input(INPUT_POST, 'bildungseinrichtung');
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
		<li><a href="datenverbund_client.php?action=getReservations">Matrikelnummer Reservierungen anzeigen</a></li>
		<li><a href="datenverbund_client.php?action=getKontingent">Matrikelnummer Kontingent anfordern</a></li>
		<li><a href="datenverbund_client.php?action=setMatrikelnummer">Matrikelnummer Vergabe melden</a></li>
		<li><a href="datenverbund_client.php?action=assignMatrikelnummer">Gesamtprozess (Abfrage, ggf Vergabemeldung, Speichern bei Person)</a></li>
	</ul>
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

	printrow('username', 'Username', $username, '', 100);
	printrow('password', 'Passwort', $password, '', 100, 'password');

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

		case 'getReservations':
		case 'getKontingent':
			printrow(
				'bildungseinrichtung',
				'Bildungseinrichtung',
				$bildungseinrichtung,
				'Kurzzeichen der Bildungseinrichtung'
			);
			printrow('studienjahr', 'Studienjahr', $studienjahr, 'zB 2016 (für WS2016 und SS2017)', 4);
			break;

		case 'setMatrikelnummer':
			printrow(
				'bildungseinrichtung',
				'Bildungseinrichtung',
				$bildungseinrichtung,
				'Kurzzeichen der Bildungseinrichtung'
			);
			printrow('matrikelnummer', 'Matrikelnummer', $matrikelnr);
			printrow('nachname', 'Nachname', $nachname, '', 255);
			printrow('vorname', 'Vorname', $vorname, '', 30);
			printrow('geburtsdatum', 'Geburtsdatum', $geburtsdatum, 'Format: YYYYMMDD', 10);
			printrow('geschlecht', 'Geschlecht', $geschlecht, 'Format: M | W', 1);
			printrow('postleitzahl', 'Postleitzahl', $postleitzahl, '', 10);
			printrow('staat', 'Staat', $staat, '1-3 Stellen Codex (zb A für Österreich)', 3);
			printrow('svnr', 'SVNR', $staat);
			printrow('matura', 'Maturadatum', $matura, 'Format: YYYYMMDD (optional)', 10);
			break;

		case 'assignMatrikelnummer':
			printrow('person_id', 'PersonID', $person_id);
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
			if ($dvb->authenticate())
				echo '<br><b>OAuth Bearer Token:</b> '.$dvb->authentication->access_token;
			else
				echo '<br><b>Failed:</b> '.$dvb->errormsg;
			break;

		case 'getBySvnr':
			$matrikelnr = $dvb->getMatrikelnrBySVNR($_POST['svnr']);
			if ($matrikelnr !== false)
				echo '<br><b>Matrikelnummer vorhanden:</b>'.$matrikelnr;
			else
				echo '<br><b>Matrikelnummer nicht vorhanden:</b>'.$dvb->errormsg;
			break;

		case 'getByErsatzkennzeichen':
			$matrikelnr = $dvb->getMatrikelnrByErsatzkennzeichen($_POST['ersatzkennzeichen']);
			if ($matrikelnr !== false)
				echo '<br><b>Matrikelnummer vorhanden:</b>'.$matrikelnr;
			else
				echo '<br><b>Matrikelnummer nicht vorhanden:</b>'.$dvb->errormsg;
			break;

		case 'getReservations':
			$reservierteNummern = $dvb->getReservations($_POST['bildungseinrichtung'], $_POST['studienjahr']);

			if ($reservierteNummern !== false)
				echo '<br><b>Reservierte Nummern:</b>'.print_r($reservierteNummern, true);
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$dvb->errormsg;
			break;

		case 'getKontingent':
			$kontingent = $dvb->getKontingent($_POST['bildungseinrichtung'], $_POST['studienjahr']);

			if ($kontingent !== false)
				echo '<br><b>Kontingent:</b>'.print_r($kontingent, true);
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$dvb->errormsg;
			break;

		case 'setMatrikelnummer':
			$person = new stdClass();
			$person->matrikelnummer = $matrikelnummer;
			$person->vorname = $vorname;
			$person->nachname = $nachname;
			$person->geburtsdatum = $geburtsdatum;
			$person->geschlecht = $geschlecht;
			$person->staat = $staat;
			$person->plz = $postleitzahl;
			$person->matura = $matura; // Optional
			$person->svnr = $svnr; // Optional

			if ($dvb->setMatrikelnummer($_POST['bildungseinrichtung'], $person))
				echo '<br><b>Erfolgreich gemeldet</b>';
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$dvb->errormsg;
			break;

		case 'assignMatrikelnummer':
			if($dvb->assignMatrikelnummer($person_id))
			{
				echo '<br><b>OK</b>';
			}
			else
				echo '<br><b>Fehlgeschlagen:</b>'.$dvb->errormsg;
			break;

		default:
			echo "Unknown action";
			break;
	}
	if (isset($_POST['debug']))
		echo '<div style="color: gray">'.$dvb->debug_output.'</div>';
}

?>
</body>
</html>
