<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/*
Dieses Script kopiert die Reihungstestanmeldungen und Punkte aus
der Tabelle public.tbl_prestudent in die Tabelle public.tbl_rt_person

Dieses muss einmalig gestartet werden beim Umstieg auf die CI Version

Vorgehensweise
Alle Einträge aus tbl_prestudent holen

Wenn Reihungstest Termin zugeteilt ist
	1. Eintrag in tbl_rt_person erstellen
		Punkte1 in Punkte Feld speichern
	2. Wenn Punkte2 gesetzt dann Reihungstest erstellen mit Stufe 2
		und selben Datum wie der Ursprungstermin
		Punkte2 in das Punkte Feld speichern
	3. Wenn Punkte3 gesetzt dann Reihungstest erstellen mit Stufe 3
		und selben Datum wie der Ursprungstermin
		Punkte3 in das Punkte Feld speichern
Wenn kein Reihungstest zugeteilt ist aber Punkte vorhanden sind
	1. Reihungstest erstellen mit Datum des Semesterstarts des Studiensemesters
		des letzten Interessentenstatus des Studierenden
		und diesen für die Punkte 1 verwenden. Weiter vorgehensweise mit
		Punkt2 und Punkte3 wie oben
Wenn kein Reihungstest zugeteilt und keine Punkte eingetragen sind,
dann nichts tun
*/
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studienplan.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('admin', null, 'suid'))
	die($rechte->errormsg);

echo '<doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
	<title>Reihungstest Migration</title>
</head>
<body>
<h1>Reihungstest Migration</h1>
';
if (!isset($_POST['run']))
{
	echo '
	Dieses Script migriert die Reihungstestanmeldungen und Punkte von der
	tbl_prestudent in die neue Tabelle public.tbl_rt_person.<br>
	Dabei werden ggf neue Reihungstesttermine erstellt für Stufe2 und Stufe3 Tests.<br>
	Dieses Script sollte einmalig gestartet werden nach dem Update auf 3.2 (CI)<br>
	Die Migration kann einige Minuten dauern.<br>
	<br>
	<form method="POST">
	<input type="submit" name="run" value="Migration jetzt starten" />
	</form>';
}
else
{
	set_time_limit(10000);

	$db = new basis_db();

	$qry = "SELECT
				tbl_prestudent.*, tbl_person.nachname, tbl_person.vorname
			FROM
				public.tbl_prestudent
				JOIN public.tbl_person USING(person_id)
			WHERE
				reihungstest_id IS NOT NULL
				OR rt_punkte1 is not null
				OR rt_punkte2 is not null
			";

	$count_zuordnungen = 0;
	$count_neue_reihungstests = 0;
	$count_zuordnung_vorhanden = 0;

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$error = false;
			$errormsg = '';
			$reihungstest_id = '';
			$studienplan_id = '';
			$stufe = 1;
			$ausbildungssemester = 1;
			$studiensemester_kurzbz = '';
			$orgform_kurzbz = '';

			$prestudent_obj = new prestudent();
			if (!$prestudent_obj->getLastStatus($row->prestudent_id, '', 'Interessent'))
			{
				$errormsg .= 'kein Interessentenstatus für PrestudentID '.$row->prestudent_id.' gefunden';
				$error = true;
				continue;
			}
			else
			{
				$studienplan_id = $prestudent_obj->studienplan_id;
				$studiensemester_kurzbz = $prestudent_obj->studiensemester_kurzbz;
				$orgform_kurzbz = $prestudent_obj->orgform_kurzbz;
			}

			if ($row->reihungstest_id == '')
			{
				// Reihungstesttermin nicht eingetragen -> erstellen
				$ausbildungssemester = $prestudent_obj->ausbildungssemester;

				$stsem_obj = new studiensemester();
				if ($stsem_obj->load($prestudent_obj->studiensemester_kurzbz))
				{
					$datum = $stsem_obj->start;
					$studiensemester_kurzbz = $prestudent_obj->studiensemester_kurzbz;
					$studienplan_id = $prestudent_obj->studienplan_id;
				}
				else
				{
					$errorsmg .= 'Fehler beim Laden des Studiensemesters';
					$error = true;
				}

				if (!$error)
				{
					$reihungstest_id = getReihungstest($datum, $studiensemester_kurzbz, $stufe, $row->studiengang_kz);
				}
			}
			else
				$reihungstest_id = $row->reihungstest_id;

			if ($studienplan_id == '')
			{
				// Wenn kein Studienplan eingetragen ist, dann wird geraten
				$studienplanObj = new studienplan();

				// Pruefen ob ein Studienplan mit selber orgform vorhanden ist
				$studienplanObj->getStudienplaeneFromSem($row->studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz);

				if (isset($studienplanObj->result[0]))
					$studienplan_id = $studienplanObj->result[0]->studienplan_id;
				else
				{
					// Falls kein passender Studienplan fuer diese Orgform vorhanden ist, dann nochmal suchen
					// ohne orgform
					$studienplanObj = new studienplan();
					$studienplanObj->getStudienplaeneFromSem($row->studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester);

					if (isset($studienplanObj->result[0]))
						$studienplan_id = $studienplanObj->result[0]->studienplan_id;
					else
					{
						$error = true;
						$errormsg .= 'StudienplanID nicht gefunden für '.$row->prestudent_id.' '.$row->nachname.' '.$row->vorname;
					}
				}
			}

			if ($reihungstest_id == '')
			{
				$error = true;
				$errormsg .= 'Reihungstest kann nicht ermittelt werden';
			}

			if (!$error)
			{
				if (addReihungstestPerson($row, $reihungstest_id, $row->rt_punkte1, $studienplan_id) === false)
					$error = true;
			}

			if (!$error)
			{
				if ($row->rt_punkte2 != '')
				{
					$stufe = 2;
					$rt = new reihungstest();
					$rt->load($reihungstest_id);
					if($rt->datum=='')
						$rt->datum='1970-01-01';
					$reihungstest_id = getReihungstest($rt->datum, $rt->studiensemester_kurzbz, $stufe, $row->studiengang_kz);
					if (addReihungstestPerson($row, $reihungstest_id, $row->rt_punkte2, $studienplan_id) === false)
						$error = true;
				}
				if ($row->rt_punkte3 != '' && $row->rt_punkte3 != '0.0000')
				{
					$stufe = 3;
					$rt = new reihungstest();
					$rt->load($reihungstest_id);
					if($rt->datum=='')
						$rt->datum='1970-01-01';
					$reihungstest_id = getReihungstest($rt->datum, $rt->studiensemester_kurzbz, $stufe, $row->studiengang_kz);
					if (addReihungstestPerson($row, $reihungstest_id, $row->rt_punkte3, $studienplan_id) === false)
						$error = true;
				}
			}

			if ($error)
				echo $errormsg.'<br>';
		}
	}

	echo '<h2>Migration abgeschlossen</h2><br>
	Neue Zuordnungen: '.$count_zuordnungen.'<br>
	Neue Reihungstests: '.$count_neue_reihungstests.'<br>
	Bereits vorhandene Zuordnungen: '.$count_zuordnung_vorhanden;
}
echo '</body>
</html>';

/*** FUNKTIONEN ***/

/**
 * Erstellt die Zuordnung einer Person zu einem Reihungstest
 * @param object $row DB Result mit Personen.
 * @param int $reihungstest_id ID des Reihungstests.
 * @param float $punkte Punkte des Reihungstests.
 * @param int $studienplan_id ID des Studienplans.
 * @return errormsg oder true wenn ok
 */
function addReihungstestPerson($row, $reihungstest_id, $punkte, $studienplan_id)
{
	global $count_zuordnungen, $count_zuordnung_vorhanden;

	// Suchen ob bereits ein Eintrag vorhanden ist in rt_person
	$rt_obj = new reihungstest();
	if (!$rt_obj->getPersonReihungstest($row->person_id, $reihungstest_id))
	{
		// Zuordnung noch nicht vorhanden
		// Neue Zuordnung erstellen
		$rt_obj->person_id = $row->person_id;
		$rt_obj->reihungstest_id = $reihungstest_id;
		$rt_obj->studienplan_id = $studienplan_id;
		$rt_obj->anmeldedatum = $row->anmeldungreihungstest;
		$rt_obj->teilgenommen = ($row->reihungstestangetreten == 't'?true:false);
		$rt_obj->punkte = $punkte;
		$rt_obj->new = true;
        $rt_obj->insertamum = date('Y-m-d H:i:s');
        $rt_obj->insertvon = $uid;
        
		if (!$rt_obj->savePersonReihungstest())
		{
			return 'Fehler beim Eintragen der RT-Zuordnung'.$rt_obj->errormsg;
		}
		else
		{
			$count_zuordnungen++;
			// schauen ob der Studienplan dem Reihungstest zugeordnet ist und
			// gegebenenfalls zuordnen
			$rt_obj = new reihungstest();
			$rt_obj->getStudienplaeneReihungstest($reihungstest_id);
			$found = false;
			foreach ($rt_obj->result as $row)
			{
				if ($row->studienplan_id == $studienplan_id)
				{
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				$rt_obj->new = true;
				$rt_obj->reihungstest_id = $reihungstest_id;
				$rt_obj->studienplan_id = $studienplan_id;
				$rt_obj->saveStudienplanReihungstest();
			}
			return true;
		}
	}
	else
	{
		// Eintrag bereits vorhanden.
		// keine weitere aktion nötig.
		$count_zuordnung_vorhanden++;
		return true;
	}
}

/**
 * Liefert eine ReihungstestID die den Kriterien entspricht
 * Wenn es keinen passenden Termin gibt, dann wird einer erstellt
 * @param date $datum Datum des Reihungstests.
 * @param varchar $studiensemester_kurzbz Kurzbz des Studiensemesters in dem der RT stattfindet.
 * @param int $stufe Stufe des Reihungstests.
 * @param int $studiengang_kz Kennzahl des Studiengangs in dem der RT abgehalten wird.
 * @return ID des Reihungstests oder false im Fehlerfall.
 */
function getReihungstest($datum, $studiensemester_kurzbz, $stufe, $studiengang_kz)
{
	global $errormsg, $error, $count_neue_reihungstests;

	// Pruefen ob bereits ein passender Reihungstesttermin vorhanden ist
	$reihungstest_obj = new reihungstest();
	$reihungstest_obj->findReihungstest($datum, $studiensemester_kurzbz, $stufe);
	if (!isset($reihungstest_obj->result[0]))
	{
		// Wenn kein Termin gefunden wurde, dann einen Neuen anlegen
		$reihungstest_obj = new reihungstest();
		$reihungstest_obj->new = true;
		$reihungstest_obj->datum = $datum;
		$reihungstest_obj->freigeschaltet = false;
		$reihungstest_obj->oeffentlich = false;
		$reihungstest_obj->stufe = $stufe;
		$reihungstest_obj->studiensemester_kurzbz = $studiensemester_kurzbz;
		$reihungstest_obj->studiengang_kz = $studiengang_kz;

		if ($reihungstest_obj->save())
		{
			$reihungstest_id = $reihungstest_obj->reihungstest_id;
			$count_neue_reihungstests++;
		}
		else
		{
			$errormsg .= 'Fehler beim Erstellen des Reihungstesttermins'.$reihungstest_obj->errormsg;
			$error = true;
			return false;
		}
	}
	else
	{
		$reihungstest_id = $reihungstest_obj->result[0]->reihungstest_id;
	}
	return $reihungstest_id;
}
