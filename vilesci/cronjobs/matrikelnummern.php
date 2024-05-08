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
 * Erfragt die Matrikelnummern von Personen beim Datenverbund
 * Wenn keine bestehende Matrikelnummer gefunden wird, wird eine neue Matrikelnummer angefordert
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../include/dvb.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../include/errorhandler.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$limit = '';
$debug = false;
$softrun = false;

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

	if (isset($_GET['softrun']))
		$debug = ($_GET['softrun'] == 'true'?true:false);
}
else
{
	$nl = "\n";
	// Commandline Paramter parsen bei Aufruf ueber Cronjob
	// zb php matrikelnummer.php --limit 100 --debug true
	$longopt = array(
		"limit:",
		"debug:",
		"softrun:"
	);
	$commandlineparams = getopt('', $longopt);
	if (isset($commandlineparams['limit']) && is_numeric($commandlineparams['limit']))
		$limit = $commandlineparams['limit'];
	if (isset($commandlineparams['debug']))
		$debug = ($commandlineparams['debug'] == 'true'?true:false);
	if (isset($commandlineparams['softrun']))
		$softrun = ($commandlineparams['softrun'] == 'true'?true:false);
}

$matrikelnummer_added = 0;
$webservice = new dvb(DVB_USERNAME, DVB_PASSWORD, $debug);

$qry = "
	SELECT
		distinct person_id, vorname, nachname
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
		JOIN public.tbl_student ON(tbl_student.student_uid=tbl_benutzer.uid)
	WHERE
		public.tbl_benutzer.aktiv = true
		AND tbl_person.matr_nr is null
		AND studiengang_kz<10000
		AND EXISTS(SELECT 1 FROM public.tbl_prestudent WHERE person_id=tbl_person.person_id AND bismelden=true)
		AND (svnr is not null OR ersatzkennzeichen is not null)";

if ($limit != '')
	$qry .= " LIMIT ".$limit;

$db = new basis_db();
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		echo $nl."Pruefe $row->person_id $row->vorname $row->nachname";
		$data = $webservice->assignMatrikelnummer($row->person_id, $softrun);
		if (ErrorHandler::isSuccess($data))
			echo ' OK';
		else
			echo ' Failed:'.$webservice->errormsg;
	}
}
if($debug)
	echo $webservice->debug_output;
