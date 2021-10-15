<?php
/* Copyright (C) 2021 FH-Burgenland
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
 * Authors: mlehrner
 */

/*
Dieses Admin-Skript speichert alle Gemeinden in die DB. (Es gibt derzeit keinen Foreign Key auf diese Tabelle dadurch könnte die Tabelle einfach gelöscht und neu befüllt werden)
Zuerst von https://www.bis.ac.at/BISSuite die Excel Datei herunter laden und zu einen csv konvertieren.
Die unnötigen Zeilen über der Head Zeile entfernen
Die Tabelle bis.tbl_gemeinde löschen. -> delete from bis.tbl_gemeinde;
Skript aufrufen, csv auswählen und hochladen.
Datei wird eingelesen und in die DB gespeichert.
*/
require_once('../../config/system.config.inc.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/gemeinde.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (!$rechte->isBerechtigt('basis/gemeinde'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$tmp_gemeinde_ar = array();

if (isset($_FILES['parsefile']) && $_FILES['parsefile']['error'] == 0)
{

	$rows = array_map('str_getcsv', file( $_FILES['parsefile']['tmp_name'] ));
	$header = array_shift($rows);
	$data = array();
	foreach ($rows as $row)
	{
		$data[] = array_combine($header, $row);
	}

	foreach ($data as $gemeinde_details)
	{

		//Wenn nicht gültig dann überspringen
		if ($gemeinde_details['Gültig'] == 'Nein')  continue;

		//es können mehrere plz in einer zeile stehen
		$plzs = explode(' ', trim($gemeinde_details['PLZ']));

		foreach ($plzs as $plz)
		{
			$tmp_obj_gemeinde = null;
			$tmp_obj_gemeinde = new gemeinde();
			$tmp_obj_gemeinde->plz = $plz;
			$tmp_obj_gemeinde->name = $gemeinde_details['Gemeindename'];
			$tmp_obj_gemeinde->ortschaftskennziffer = $gemeinde_details['Ortschaftskennziffer'];
			$tmp_obj_gemeinde->ortschaftsname = $gemeinde_details['Ortschaftsname'];
			$tmp_obj_gemeinde->bulacode = $gemeinde_details['BULA_Code'];
			$tmp_obj_gemeinde->bulabez = $gemeinde_details['BULA_Bez'];
			$tmp_obj_gemeinde->kennziffer = $gemeinde_details['Gemeindekennziffer'];

			$tmp_obj_gemeinde->save();
			$tmp_gemeinde_ar[] = $tmp_obj_gemeinde;
		}
	}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Set Gemeinde</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
  </head>
  <body>
	  	<h2>Gemeinden aktualisieren</h2>
		Diese Seite dient dazu die Gemeinde Kodex Tabelle zu aktualisieren.<br /><br />
		Die Gemeinde Tabelle muss zuvor manuell geleert werden<br />
		<b>Filesyntax:</b>(Standard xlsx-File von https://www.bis.ac.at/BISSuite, gespeichert als csv! erste Zeile ist Header (alles was oberhalb ist kann entfernt werden) -> dann Daten)
		<br/><br/>
		<table border="1">
			<tbody>
				<tr>
					<td>Gemeindekennziffer</td>
					<td>Gemeindename</td>
					<td>Ortschaftskennziffer</td>
					<td>Ortschaftsname</td>
					<td>PLZ</td>
					<td>BULA_Code</td>
					<td>BULA_Bez</td>
					<td>Gültig</td>
				</tr>
				<tr>
					<td>10101</td>
					<td>Eisenstadt</td>
					<td>1</td>
					<td>Eisenstadt</td>
					<td>7000</td>
					<td>1</td>
					<td>BGLD</td>
					<td>Ja</td>
				</tr>
				<tr>
					<td>10101</td>
					<td>Eisenstadt</td>
					<td>2</td>
					<td>Kleinhöflein im Burgenland</td>
					<td>7000 7001 7002</td>
					<td>1</td>
					<td>BGLD</td>
					<td>Ja</td>
				</tr>
				<tr>
					<td>...</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
				</tr>
			</tbody>
		</table>
		<hr>
		<form enctype="multipart/form-data" method="post">
			<br>PLZ Kodextabelle <input name="parsefile" type="file" />
			<input type="submit" value="hochladen" />
		</form>
		<hr>
		<div>
			<pre>
				<?php
					if ($tmp_gemeinde_ar)
					{
						print_r($tmp_gemeinde_ar);
					}
				?>
			</pre>
		</div>
	</body>
</html>
