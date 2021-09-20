<?php
/* Copyright (C) 2017 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Gui zum aktivieren der Zutrittskarte
 * Hier wird die neue Karte einmal über den Kartenleser gezogen zum das Ausgabedatum zu setzen
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiensemester.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum_obj = new datum();

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<title>Kartenrücknahme</title>
</head>
<body>
<h2>Zutrittskarte - Rücknahme</h2>';

if(!$rechte->isBerechtigt('basis/fhausweis', 's'))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();
$kartennummer = (isset($_POST['kartennummer'])?$_POST['kartennummer']:'');
$action=(isset($_POST['action'])?$_POST['action']:'');

if ($action == 'karte_loeschen')
{
	if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
		die('Sie haben keine Berechtigung zum löschen von Karten');

	$bmp = new betriebsmittelperson();
	if ($bmp->getKartenzuordnung($kartennummer, false))
	{
		if ($bmp->betriebsmittelperson_id != '')
		{
			if ($bmp->delete($bmp->betriebsmittelperson_id))
			{
				if ($bmp->delete_betriebsmittel($bmp->betriebsmittel_id))
				{
					echo '<span class="ok">Karte erfolgreich gelöscht</span>';
				}
				else
				{
					echo '<span class="error">Fehler beim löschen der Betriebsmittel_id: '.$bmp->betriebsmittel_id.'</span>';
				}
			}
			else
			{
				echo '<span class="error">Fehler beim löschen der Betriebsmittelperson_id: '.$bmp->betriebsmittelperson_id.'</span>';
			}
		}
		else
		{
			echo '<span class="error">Diese Karte ist derzeit nicht zugewiesen</span>';
		}
	}
	else
	{
		echo '<span class="error">Diese Karte ist derzeit nicht zugewiesen</span>';
	}

	echo '<br><hr><br>';
}

if ($action == 'kartenabfrage' || $action == 'kartenruecknahme')
{
	$bmp = new betriebsmittelperson();
	if ($bmp->getKartenzuordnung($kartennummer, false))
	{
		if ($bmp->uid != '')
		{
			$karten_user = $bmp->uid;

			$benutzer = new benutzer();
			if(!$benutzer->load($karten_user))
			{
				echo '<span class="error">Fehler beim Laden des Benutzers</span>';
			}
			else
			{
				$bmp = new betriebsmittelperson();
				if ($bmp->getKartenzuordnungPerson($benutzer->person_id, $kartennummer))
				{
					if ($action == 'kartenruecknahme')
					{
						if(!$rechte->isBerechtigt('basis/fhausweis', 'su'))
							die('Sie haben keine Berechtigung zum aktualisieren von Kartendaten');

						$bmp->retouram = date('Y-m-d');
						$bmp->updateamum = date('Y-m-d H:i:s');
						$bmp->updatevon = $uid;

						if(!$bmp->save(false))
						{
							echo '<span class="error">Fehler beim austragen der Karte</span>';
						}
						else
							echo '<span class="ok">Karte wurde erfolgreich ausgetragen.</span>';
					}
					else
					{
						echo '<table>
								<tr>
									<td>
										<img src="../../content/bild.php?src=person&person_id='.$benutzer->person_id.'"
										height="100px" width="75px"/>
									</td>
									<td>
										Vorname: '.$benutzer->vorname.'<br>
										Nachname: '.$benutzer->nachname.'<br>
										UID: '.$benutzer->uid.'<br>
										Karte augegeben am: '.($bmp->ausgegebenam != '' ? $datum_obj->formatDatum($bmp->ausgegebenam,'d.m.Y') : '<span style="color: red">Karte nicht ausgegeben</span>').'<br>
										Karte retour am: '.($bmp->retouram != '' ? $datum_obj->formatDatum($bmp->retouram,'d.m.Y') : '<span style="color: red">Karte nicht retourniert</span>').'<br>';
										if ($bmp->beschreibung != '')
										{
											echo 'Beschreibung: '.$bmp->beschreibung.'<br>';
										}
										$student = new student();
										$mitarbeiter = new mitarbeiter();
										if ($student->load($bmp->uid))
										{
											$prestudent = new prestudent();
											if ($prestudent->getLastStatus($student->prestudent_id))
											{
												echo '<br>Letzter Status: <b>';
												$style = '';
												if ($prestudent->status_kurzbz == 'Abbrecher' || $prestudent->status_kurzbz == 'Absolvent')
												{
													$style = 'style="color: red; font-weight: bold"';
												}
												echo '<span '.$style.'>'.$prestudent->status_kurzbz.'</span> im ';
												$style = '';
												$studiensemester = new studiensemester();
												if ($prestudent->studiensemester_kurzbz != $studiensemester->getakt())
												{
													$style = 'style="color: red; font-weight: bold"';
												}
												echo '<span '.$style.'>'.$prestudent->studiensemester_kurzbz.'</span><br>';
											}
										}
										elseif ($mitarbeiter->load($bmp->uid))
										{
											echo '<br>Letzter Status: '.($benutzer->bnaktiv ? '<span style="color: green;">Mitarbeiter*in aktiv</span>' : '<span style="color: red; font-weight: bold">Mitarbeiter*in inaktiv seit '.$datum_obj->formatDatum($benutzer->updateaktivam,'d.m.Y').'</span>').'<br>';
										}
										else
										{
											echo '<br>Kein/e Mitarbeiter*in oder Student*in<br>';
										}
						echo '		</td>
								</tr>
							</table><br>';

						if ($bmp->retouram == '')
						{
							echo '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
									<input type="hidden" name="action" value="kartenruecknahme" />
									<input type="hidden" name="kartennummer" value="'.$kartennummer.'"/>
									<input type="submit" name="retournieren" style="color: #fff; background-color: #f0ad4e; border-color: #eea236; padding: 3px" value="Karte austragen" />
								</form><br>';
						}

						if($rechte->isBerechtigt('basis/fhausweis', 'suid'))
						{
							echo '<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
									<input type="hidden" name="action" value="karte_loeschen" />
									<input type="hidden" name="kartennummer" value="'.$kartennummer.'"/>
									<input type="submit" 
										name="loeschen" 
										style="color: #fff; background-color: #d9534f; border-color: #d43f3a;; padding: 3px" 
										value="Karte löschen" ';
							// Wenn die Karte ausgegeben und/oder retourniert ist, wird zur Sicherheit nachgefragt ob wirklich gelöscht werden soll
							if ($bmp->ausgegebenam != '' || $bmp->retouram != '')
							{
								echo ' onclick="return confirm(\'Die Karte wurde ausgegeben oder retourniert. Wollen Sie sie wirklich löschen?\')"';
							}
							echo '/>
								</form>';
						}
					}
				}
				else
				{
					echo '
						<span class="error">
						Fehler beim Tauschen: Die Karte wurde dieser
						Person noch nicht zugeordnet ('.$benutzer->uid.' '.$kartennummer.')
						</span>';
				}
			}
		}
		else
		{
			echo '<span class="error">Diese Karte ist derzeit nicht zugewiesen</span>';
		}
	}
	else
	{
		echo '<span class="error">Diese Karte ist derzeit nicht zugewiesen</span>';
	}

	echo '<br><hr><br>';
}

echo '
Ziehen Sie die neue Karte über den Hitag Kartenleser um die Karte zu deaktivieren:
<script type="text/javascript">
	$(document).ready(function()
	{
		$("#kartennummer").val("");
		$("#kartennummer").focus();
	});
</script>
<br><br>
<form action="'.$_SERVER['PHP_SELF'].'" METHOD="POST">
	<input type="hidden" name="action" value="kartenabfrage" />
	Kartennummer:
	<input type="text" id="kartennummer" name="kartennummer"/>
	<input type="submit" name="abfragen" value="Karte abfragen" />
</form>
';

echo '</body>
</html>';
?>
