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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Erfragt die BPKs von Personen beim Datenverbund und speichert diese wenn gefunden
 * Dieses Script sollte nach dem Matrikelnummer Job aufgerufen werden da dieser bereits bpks ermittelt
 * Dieser Job versucht die BPKs zu holen die nicht automatisch über den Matrikelnummer Job gefunden wurden.
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../include/dvb.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../include/kennzeichen.class.php');
require_once(dirname(__FILE__).'/../../include/errorhandler.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$limit = '';
$debug = false;
$vbpkTypes = defined('VBPK_TYPES') && is_array(VBPK_TYPES) ? VBPK_TYPES : null;

// Wenn das Script nicht ueber Commandline gestartet wird, muss eine
// Authentifizierung stattfinden
if (php_sapi_name() != 'cli')
{
	$nl = '<br>';
	// Benutzerdefinierte Variablen laden
	$user = get_uid();
	loadVariables($user);

	// Berechtigungen pruefen
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if (!$rechte->isBerechtigt('admin', null, 'suid'))
		die('Sie haben keine Berechtigung für diese Seite');

	if (isset($_GET['debug']))
		$debug = ($_GET['debug'] == 'true'?true:false);

	if (isset($_GET['limit']) && is_numeric($_GET['limit']))
		$limit = $_GET['limit'];
}
else
{
	$nl = "\n";
	// Commandline Paramter parsen bei Aufruf ueber Cronjob
	// zb php matrikelnummer.php --limit 100 --debug true
	$longopt = array(
		"limit:",
		"debug:"
	);
	$commandlineparams = getopt('', $longopt);
	if (isset($commandlineparams['limit']) && is_numeric($commandlineparams['limit']))
		$limit = $commandlineparams['limit'];
	if (isset($commandlineparams['debug']))
		$debug = ($commandlineparams['debug'] == 'true'?true:false);
}

$webservice = new dvb(DVB_USERNAME, DVB_PASSWORD, $debug);

if (defined('BPK_FUER_ALLE_BENUTZER_ABFRAGEN') && BPK_FUER_ALLE_BENUTZER_ABFRAGEN)
{
    $qry = "
	SELECT
		distinct person_id, vorname, nachname
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
	WHERE
		public.tbl_benutzer.aktiv = true
		AND
		(
			tbl_person.bpk is null";

	// checken, ob vBpks fehlen
	if (isset($vbpkTypes))
	{
		$qry .=
		" OR (
				SELECT
					COUNT(DISTINCT kennzeichentyp_kurzbz)
				FROM
					public.tbl_kennzeichen
				WHERE
					person_id = tbl_person.person_id
					AND kennzeichentyp_kurzbz IN (".$db->implode4SQL($vbpkTypes).")
			) < ".$db->db_add_param(count($vbpkTypes), FHC_INTEGER);
	}

	$qry .=
		") AND gebdatum is not null";
}
else
{
    $qry = "
	SELECT
		distinct person_id, vorname, nachname
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
		JOIN public.tbl_student ON(tbl_student.student_uid=tbl_benutzer.uid)
	WHERE
		public.tbl_benutzer.aktiv = true
		AND tbl_person.matr_nr is not null
		AND
		(
			tbl_person.bpk is null";

	// checken, ob vBpks fehlen
	if (isset($vbpkTypes))
	{
		$qry .=
		" OR (
				SELECT
					COUNT(DISTINCT kennzeichentyp_kurzbz)
				FROM
					public.tbl_kennzeichen
				WHERE
					person_id = tbl_person.person_id
					AND kennzeichentyp_kurzbz IN (".$db->implode4SQL($vbpkTypes).")
			) < ".$db->db_add_param(count($vbpkTypes), FHC_INTEGER);
	}

	$qry .=
	") AND studiengang_kz<10000
		AND EXISTS(SELECT 1 FROM public.tbl_prestudent WHERE person_id=tbl_person.person_id AND bismelden=true)
		AND gebdatum is not null";
}

if ($limit != '')
	$qry .= " LIMIT ".$limit;

$db = new basis_db();
$cnt = 0;
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$cnt++;
		// Nach jeweils 25 Requests eine Pause einlegen damit die
		// Anzahl Requests pro Minute nicht überschritten wird
		if($cnt%25 == 0)
			sleep(30);

		echo $nl."Pruefe $row->person_id $row->vorname $row->nachname";
		$data = $webservice->getBPK($row->person_id);
		if (ErrorHandler::isSuccess($data))
		{
			if (ErrorHandler::hasData($data) && isset($data->retval->bpk) && $data->retval->bpk != '')
			{
				$person = new person();
				if ($person->load($row->person_id))
				{
					$person->bpk = $data->retval->bpk;
					if ($person->save())
						echo ' OK';
					else
						echo ' Failed: '.$person->errormsg;

					$vbpkErrors = array();

					// alle existierenden vBpks einer Person holen
					$kennzeichenTypes = new kennzeichen();
					if ($kennzeichenTypes->load_pers($row->person_id, $vbpkTypes))
					{
						$existingVbpks = $kennzeichenTypes->result;

						foreach ($data->retval->vbpks as $vbpkType => $vbpkValue)
						{
							$new = true;
							foreach ($existingVbpks as $existingVbpk)
							{
								// nicht speichern, wenn vBpk bereits vorhanden
								if ($existingVbpk->kennzeichentyp_kurzbz == $vbpkType)
								{
									$new = false;
									break;
								}
							}

							if (!$new) continue;

							// neue vBpk speichern
							$kennzeichen = new kennzeichen();

							$kennzeichen->person_id = $row->person_id;
							$kennzeichen->kennzeichentyp_kurzbz = $vbpkType;
							$kennzeichen->inhalt = $vbpkValue;
							$kennzeichen->aktiv = true;
							$kennzeichen->insertvon = 'bpkJob';

							if (!$kennzeichen->save())
							{
								$vbpkErrors[] = 'Failed to save vBpk '.$vbpkType.':'.$kennzeichen->errormsg;
							}
						}
					}

					if (count($vbpkErrors) > 0)
					{
						echo implode('; ', $vbpkErrors);
					}
				}
			}
			else
			{
				echo 'Failed: BPK Empty';
			}
		}
		else
			echo ' Failed:'.$webservice->errormsg;
	}
}
if ($debug)
	echo $webservice->debug_output;
