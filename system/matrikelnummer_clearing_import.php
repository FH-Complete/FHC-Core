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
 * Clearing Import
 * Importiert Daten aus dem Matrikelnummer Clearing
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/person.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin', null, 'suid'))
	die($rechte->errormsg);

echo '<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Matrikelnummer Clearing Import</title>
	</head>
<body>
<h1>Matrikelnummer Clearing Import</h1>
Über diese Seite kann das Ergebnis des Matrikelnummer Clearings importiert werden.
Wähle dazu das Antwort-XML des Matrikelnummer clearings aus, das importiert werden soll.
Matrikelnummer und BPK wird aus dem XML importiert.<br><br>';
echo '<form method="POST" enctype="multipart/form-data">
<input type="file" name="datei">
<input type="submit" value="Importieren" />';

if(isset($_FILES['datei']))
{
	$db = new basis_db();
	$dom = new DOMDocument();
	$dom->load($_FILES['datei']['tmp_name']);
	$studierende = $dom->getElementsByTagName('studierende');
	if($studierende->length > 0)
	{
		$domnodes_personen = $studierende->item(0)->getElementsByTagName('personen');
		foreach($domnodes_personen as $row_person)
		{
			$personid_node = $row_person->getElementsByTagName('personId');
			if($personid_node->length > 0)
			{
				$node_bpk = $personid_node->item(0)->getElementsByTagName('bpk');
				$node_personenkennzeichen = $personid_node->item(0)->getElementsByTagName('personenkennzeichen');
				$node_matrikelnr = $personid_node->item(0)->getElementsByTagName('matrikelnummer');

				$bpk = '';
				$personenkennzeichen = '';
				$matrikelnr = '';

				if ($node_bpk->length > 0)
					$bpk = $node_bpk->item(0)->textContent;
				if ($node_personenkennzeichen->length > 0)
					$personenkennzeichen = $node_personenkennzeichen->item(0)->textContent;
				if ($node_matrikelnr->length > 0)
					$matrikelnr = $node_matrikelnr->item(0)->textContent;

				if($personenkennzeichen != '')
				{
					$qry = "
						SELECT
							person_id
						FROM
							campus.vw_student
						WHERE
							matrikelnr=".$db->db_add_param($personenkennzeichen);

					if($result = $db->db_query($qry))
					{
						if($db->db_num_rows($result) == 1)
						{
							if($row = $db->db_fetch_object($result))
							{
								$person_id = $row->person_id;
								$person = new person();
								if($person->load($person_id))
								{
									$person->matr_nr = $matrikelnr;

									if($bpk != '' && $bpk != '****************************')
										$person->bpk = $bpk;

									if($person->save())
									{
										echo '<br>Daten von '.$personenkennzeichen.' importiert';
									}
									else
									{
										echo '<br>Fehler beim Import von '.$personenkennzeichen.':'.$person->errormsg;
									}
								}
								else
								{
									echo '<br>Person mit PersonID '.$person_id.' konnte nicht geladen werden';
								}
							}
							else
							{
								echo '<br>Failed to get Results';
							}
						}
						else
						{
							echo 'Person mit Perskz: '.$personenkennzeichen.' konnte nicht eindeutig gefunden werden';
						}
					}
					else
					{
						echo 'Fehler beim Laden der Person mit Perskz: '.$personenkennzeichen.'';
					}
				}
				else
				{
					echo '<br>Failed to get Personenkennzeichen';
				}
			}
			else
			{
				echo '<br>personID Tag no present -> wrong xml?';
			}
		}
		echo '<br>Import abgeschlossen';
	}
	else
	{
		echo '<br>studierende Tag not present -> wrong xml?';
	}
}

echo '</body>
</html>';
