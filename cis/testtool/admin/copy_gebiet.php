<?php
/* Copyright (C) 2009 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Kopiert ein Testtool-Gebiet mit allen Fragen und Antworten aber ohne Zuordnungen zu Studiengängen und dgl.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/gebiet.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/frage.class.php');
require_once('../../../include/antwort.class.php');
require_once('../../../include/vorschlag.class.php');

if (!$user = get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$sprache = new sprache();
$sprache->getAll(true, 'index');

$sprache_user = getSprache();

$db = new basis_db();

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">
    $(document).ready(function()
    {
        $("#t1").tablesorter(
        {
            sortList: [[0,0]],
            widgets: ["zebra"]
        });
    });
	 </script>
	 <style type="text/css">
	.success
	{
		color: #3c763d;
		font-weight: bold;
	} 
	.error
	{
		color: #ff0000;
	}
	</style>
</head>
<body style="padding: 10px">
<div id="data"></div>
';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
$gebietToCopy = (isset($_POST['gebietToCopy'])?$_POST['gebietToCopy']:(isset($_GET['gebietToCopy'])?$_GET['gebietToCopy']:''));
$gebiet = new gebiet();

echo '<a href="index.php?stg_kz='.$stg_kz.'" class="Item">Zurück zur Admin Seite</a><br /><br />';
echo '<h1>Gebiet kopieren</h1>';
echo '<p>Kopiert ein Gebiet mit allen Fragen und Antworten aber ohne Zuordnungen zu Studiengängen und dgl.</p>';

if(!$rechte->isBerechtigt('basis/testtool'))
	die($rechte->errormsg);

$returnmsg = '';
//Speichern der Daten
if (isset($_POST['copyGebiet']) && $_POST['copyGebiet'] == 'copyGebiet')
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	if (isset($_POST['kurzbz']) && $_POST['kurzbz'] != '')
	{
		//Test, ob kurzbz schon vorhanden
		if ($result = $db->db_query('SELECT kurzbz FROM testtool.tbl_gebiet WHERE kurzbz = '.$db->db_add_param($_POST['kurzbz']).' LIMIT 1;'))
		{
			if ($db->db_num_rows($result) == 0)
			{
				$gebietToCopy = $_POST['gebietToCopy'];

				// Zu kopierendes Gebiet laden
				$gebiet = new gebiet($gebietToCopy);

				$bezeichnung_mehrsprachig = array();
				foreach ($sprache->result as $row_sprache)
				{
					$bezeichnung_mehrsprachig[$row_sprache->sprache] = $_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache];
				}
				$gebiet->bezeichnung_mehrsprachig = $bezeichnung_mehrsprachig;

				$gebiet->kurzbz = $_POST['kurzbz'];
				$gebiet->bezeichnung = $_POST['bezeichnung_intern'];
				$gebiet->beschreibung = $_POST['beschreibung'];
				$gebiet->insertamum = date('Y-m-d H:i:s');
				$gebiet->insertvon = $user;

				// Neues Gebiet speichern
				if ($gebiet->save(true))
				{
					$returnmsg .= '<p class="success">Gebiet erfolgreich kopiert</p>';

					// Array mit allen Fragen und Antworten anlegen
					$frageAntwortArray = array();

					// Fragen laden
					$fragenToCopy = new frage();
					$fragenToCopy->getFragenGebiet($gebietToCopy);
					// Sprachen laden und für jede Sprache die Fragen und Antworten laden
					foreach ($sprache->result as $row_sprache)
					{
						$fragenSpracheToCopy = new frage();
						$indexFrageSprache = 0;
						foreach ($fragenToCopy->result AS $copyFrage)
						{
							if ($fragenSpracheToCopy->getFrageSprache($copyFrage->frage_id, $row_sprache->sprache, true))
							{
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['nummer'] = $fragenSpracheToCopy->nummer;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['demo'] = $fragenSpracheToCopy->demo;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['kategorie_kurzbz'] = $fragenSpracheToCopy->kategorie_kurzbz;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['level'] = $fragenSpracheToCopy->level;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['aktiv'] = $fragenSpracheToCopy->aktiv;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['text'] = $fragenSpracheToCopy->text;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['bild'] = $fragenSpracheToCopy->bild;
								$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['audio'] = $fragenSpracheToCopy->audio;

								// Vorschläge laden
								$vorschlagSprachenToCopy = new vorschlag();
								$vorschlagSprachenToCopy->getVorschlag($copyFrage->frage_id, $row_sprache->sprache, false);

								$indexVorschlagSprache = 0;
								foreach ($vorschlagSprachenToCopy->result AS $vorschlagSprache)
								{
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['nummer'] = $vorschlagSprache->nummer;
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['punkte'] = $vorschlagSprache->punkte;
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['text'] = $vorschlagSprache->text;
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['bild'] = $vorschlagSprache->bild;
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['audio'] = $vorschlagSprache->audio;
									$frageAntwortArray[$row_sprache->sprache][$indexFrageSprache]['vorschlaege'][$indexVorschlagSprache]['aktiv'] = $vorschlagSprache->aktiv;
									$indexVorschlagSprache++;
								}
							}
							$indexFrageSprache++;
						}
					}

					// Einfügen der Fragen und Antworten aus dem Array in das neue Gebiet
					$anzahlFragenKopiert = 0;

					foreach ($frageAntwortArray AS $fragesprache => $index)
					{
						foreach ($index AS $frage => $value)
						{
							$newfragen = new frage();
							$newfragen->new = true;

							$newfragen->kategorie_kurzbz = $value["kategorie_kurzbz"];
							$newfragen->gebiet_id = $gebiet->gebiet_id;
							$newfragen->level = $value["level"];
							$newfragen->nummer = $value["nummer"];
							$newfragen->demo = $value["demo"];
							$newfragen->insertamum = date('Y-m-d H:i:s');
							$newfragen->insertvon = $user;
							$newfragen->aktiv = $value["aktiv"];

							if ($newfragen->save())
							{
								$newfragen->sprache = $fragesprache;
								$newfragen->text = $value["text"];
								$newfragen->bild = $value["bild"];
								$newfragen->audio = $value["audio"];
								$newfragen->insertamum = date('Y-m-d H:i:s');
								$newfragen->insertvon = $user;

								if ($newfragen->save_fragesprache())
								{
									if (isset($value["vorschlaege"]))
									{
										foreach ($value["vorschlaege"] AS $vorschlag => $content)
										{
											// Vorschläge speichern
											$newvorschlaege = new vorschlag();
											$newvorschlaege->new = true;

											$newvorschlaege->frage_id = $newfragen->frage_id;
											$newvorschlaege->nummer = $content["nummer"];
											$newvorschlaege->punkte = $content["punkte"];
											$newvorschlaege->aktiv = $content["aktiv"];
											$newvorschlaege->insertamum = date('Y-m-d H:i:s');
											$newvorschlaege->insertvon = $user;

											if ($newvorschlaege->save())
											{
												$newvorschlaege->sprache = $fragesprache;
												$newvorschlaege->text = $content["text"];
												$newvorschlaege->bild = $content["bild"];
												$newvorschlaege->audio = $content["audio"];
												$newvorschlaege->insertamum = date('Y-m-d H:i:s');
												$newvorschlaege->insertvon = $user;

												if ($newvorschlaege->save_vorschlagsprache())
												{
													$anzahlFragenKopiert++;
												}
											}
										}
									}
								}
								else
								{
									$returnmsg .= '<p class="error">Fehler beim Speichern der Fragesprache '.$fragesprache.' bei Frage: '.$newfragen->frage_id.'</p>';
								}
							}
							else
							{
								$returnmsg .= '<p class="error">Fehler beim Speichern der Frage: '.$newfragen->frage_id.'</p>';
							}
						}
					}
				}
				else
				{
					$returnmsg .= '<p class="error">Fehler beim Speichern: '.$gebiet->errormsg.'</p>';
				}
			}
			else
				$returnmsg .= '<p class="error">Kurzbezeichnung ist schon vorhanden</p>';
		}
	}
	else
	{
		$returnmsg .= '<p class="error">Bitte f&uuml;llen Sie alle Pflichtfelder aus</p>';
	}
}


$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

echo '<p>'.$returnmsg.'</p>';
echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
 <table cellspacing="4">
	<tr>
		<td>Zu kopierendes Gebiet: </td><td>';
	//Dropdown bestehender Gebiete
	$gebiete = new gebiet();
	$gebiete->getAll();
	echo '<SELECT name="gebietToCopy" id="gebieteSelect"><OPTION value="-1">-- Keine Auswahl --</OPTION>';
	foreach ($gebiete->result AS $row)
	{
		if ($gebietToCopy == $row->gebiet_id)
		{
			$selected = 'selected';
		}
		echo '<option value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' - '.$row->kurzbz.' - '.$row->zeit.'</option>';

		$selected = '';
	}
	echo '</select>';
echo '</td></tr>
	<tr>
		<td colspan="2"><b>Daten neues Gebiet</b></td>
	</tr>
	<tr>
		<td>Kurzbezeichnung</td>
		<td><input type="text" name="kurzbz" placeholder="Pflichtfeld" maxlength="10" value="'.(isset($_POST['kurzbz'])?$_POST['kurzbz']:'').'" required/></td>
	</tr>
	<tr>
		<td>Bezeichnung (intern)</td>
		<td><input type="text" name="bezeichnung_intern" maxlength="50" value="'.(isset($_POST['bezeichnung_intern'])?$_POST['bezeichnung_intern']:'').'"/></td>
	</tr>';

foreach ($sprache->result as $row)
{
	echo '	<tr>
				<td>Bezeichnung '.$row->bezeichnung_arr[$sprache_user].'</td>
				<td><input type="text" name="bezeichnung_mehrsprachig_'.$row->sprache.'" maxlength="255" value="'.(isset($_POST['bezeichnung_mehrsprachig_'.$row->sprache.''])?$_POST['bezeichnung_mehrsprachig_'.$row->sprache.'']:'').'"/></td>
			</tr>';
}
echo '
	<tr>
		<td>Beschreibung (intern)</td>
		<td><textarea rows="" cols="" name="beschreibung" style="font-size: 9pt">'.(isset($_POST['beschreibung'])?$_POST['beschreibung']:'').'</textarea></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Gebiet kopieren"/></td>
	</tr>
 </table>
 <input type="hidden" name="copyGebiet" value="copyGebiet"/>
</form>
';

echo '</body></html>';
?>
