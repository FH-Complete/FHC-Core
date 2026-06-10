<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Cristina Hainberger		hainberg@technikum-wien.at>.
 */

require_once('../../config/cis.config.inc.php');
require_once('../../include/dokument_export.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/projektbetreuer.class.php');
require_once('../../include/projektarbeit.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);

$doc = new dokument_export('Lehrtaetigkeit');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

//	Check permission
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);
if (!$berechtigung->isBerechtigt('admin') &&
	!$berechtigung->isBerechtigt('mitarbeiter'))
	die('Sie muessen das Recht "ADMIN" oder "MITARBEITER" haben, um diese Seite aufrufen zu koennen');

// Get GET params
$uid = (isset($_GET['uid']) && is_string($_GET['uid'])) ? $_GET['uid'] : die($p->t('global/fehlerBeimErmittelnDerUID'));
$output = (isset($_GET['output']) && ($_GET['output'] == 'odt' || $_GET['output'] == 'doc')) ? $_GET['output'] : 'pdf';

// Personal data of lector
$person = new Person();
$person->getPersonFromBenutzer($uid);
$person_id = $person->person_id;
$anrede = $person->anrede;
$gender = $person->geschlecht;
$fullname = $person->getFullName();
$birthday_date = new DateTime($person->gebdatum);

// Get the lectors lehreinheiten-semesterstunden per semester
$semesterstunden_per_semester = array();
$le_ma = new Lehreinheitmitarbeiter();

// * get all semester of the lector where he was teaching actively
$active_semester_arr = $le_ma->getSemesterZuLektor($uid);
$active_semester_arr = array_keys($active_semester_arr);

// * for each semester:
foreach($active_semester_arr as $active_semester)
{
	// * get all the lectors lehreinheiten
	$le_id_arr = array();
	$le_array = $le_ma->getLehreinheiten($uid, $active_semester);
	foreach($le_array as $le)
	{
		$le_id_arr[]= $le->lehreinheit_id;
	}

	// * get begin- and end date of studiensemester
	$ss = new Studiensemester($active_semester);
	$studiensemester_start_date = $ss->start;
	$studiensemester_end_date = $ss->ende;

	// * get total amount of semesterstunden of the lehreinheiten
	$total_semesterstunden = 0;
	foreach ($le_id_arr as $le_id)
	{
		$le_ma = new Lehreinheitmitarbeiter($le_id, $uid);
		if ($le_ma)
		{
			$total_semesterstunden = $total_semesterstunden + $le_ma->semesterstunden;
		}
	}

	// * store data only if semesterstunden > 0
	if ($total_semesterstunden > 0)
	{
		$semesterstunden_per_semester []= (
			array(
				'studiensemester_kurzbz'=> $active_semester,
				'total_semesterstunden' => $total_semesterstunden,
				'studiensemester_start_date' => $studiensemester_start_date,
				'studiensemester_end_date' => $studiensemester_end_date
			)
		);
	}
}

// Get the lectors projektarbeitstunden per semester
$projektstunden_per_semester = array();
$pb = new Projektbetreuer();
$pb->getAllProjects($person_id);
$project_arr = $pb->result;

// * for each project
foreach ($project_arr as $project)
{
	$pa_id = $project->projektarbeit_id;
	$projektstunden = $project->stunden;

	// * get studiensemester
	$pa = new Projektarbeit($pa_id);
	$le = new Lehreinheit($pa->lehreinheit_id);
	$studiensemester_kurzbz = $le->studiensemester_kurzbz;

	// * get begin- and end date of studiensemester
	$ss = new Studiensemester($studiensemester_kurzbz);
	$studiensemester_start_date = $ss->start;
	$studiensemester_end_date = $ss->ende;

	// Get total amount of semesterstunden by studiensemester
	// * check if studiensemester already exists. If so, get array index.
	$studiensemester_index = array_search($studiensemester_kurzbz, array_map(function($val) {
		return $val['studiensemester_kurzbz'];
	}, $projektstunden_per_semester
	));

	// * if studiensemester exists, sum up hours of projektarbeit
	if ($studiensemester_index !== false)
	{
		$projektstunden_per_semester[$studiensemester_index]['total_semesterstunden'] =
			$projektstunden_per_semester[$studiensemester_index]['total_semesterstunden'] + $projektstunden;
	}
	// * if not, create new index
	else
	{
		$projektstunden_per_semester[] = (
			array(
				'studiensemester_kurzbz'=> $studiensemester_kurzbz,
				'total_semesterstunden' => $projektstunden,
				'studiensemester_start_date' => $studiensemester_start_date,
				'studiensemester_end_date' => $studiensemester_end_date
			)
		);
	}
}

// Merge lehreinheit- and projektarbeitstunden arrays
foreach ($projektstunden_per_semester as $item)
{
	// check if studiensemester already exists in projektstunden-per-term-array. If so, get array index.
	$studiensemester_index = array_search($item['studiensemester_kurzbz'], array_map(function($val) {
		return $val['studiensemester_kurzbz'];
	}, $semesterstunden_per_semester
	));

	// * if studiensemester exists, merge lehreinheit- and projektarbeit hours
	if ($studiensemester_index !== false)
	{
		$semesterstunden_per_semester[$studiensemester_index]['total_semesterstunden'] =
			$semesterstunden_per_semester [$studiensemester_index]['total_semesterstunden']
			+ $item['total_semesterstunden'];
	}
	// * if not, create new index
	else
	{
		$semesterstunden_per_semester []= (
			array(
				'studiensemester_kurzbz'=> $item['studiensemester_kurzbz'],
				'total_semesterstunden' => intval($item['total_semesterstunden']),
				'studiensemester_start_date' => $item['studiensemester_start_date'],
				'studiensemester_end_date' =>  $item['studiensemester_end_date']
			)
		);
	}
}

// Sort lehreinheit- and projektarbeitstunden array by date
usort($semesterstunden_per_semester, function($a, $b)
{
	return strtotime($a['studiensemester_start_date']) - strtotime($b['studiensemester_start_date']);
});

// Split studiensemester array into actual studiensemester array and former studiensemester array
// * get actual studiensemester
$ss = new Studiensemester();
$actual_studiensemester = $ss->getakt();
$actual_studiensemester_index = array_search($actual_studiensemester, array_map(function($val) {
	return $val['studiensemester_kurzbz'];
}, $semesterstunden_per_semester
));

// if lector is teaching actually, split former teaching activities
// from actual teaching activities of actual studiensemester
$semesterstunden_of_actual_semester = array();
if ($actual_studiensemester_index !== false)
{
	// array with actual + future semester
	$semesterstunden_of_actual_semester = array_slice($semesterstunden_per_semester, $actual_studiensemester_index);
	// array with all former semester
	$semesterstunden_per_semester = array_slice($semesterstunden_per_semester, 0, $actual_studiensemester_index);
}

// Semester begin and ending date of lehreinheit- and projektarbeit studiensemester
//	* begin date of first lehreinheit- and projektarbeit studiensemester
$earliest_ss = current($semesterstunden_per_semester)['studiensemester_start_date'];
$begin_date = !is_null($earliest_ss) ? new DateTime($earliest_ss) : null;

//	* end date of last lehreinheit- and projektarbeit studiensemester
// * if lector is teaching on actual studiensemester, get end date of actual studiensemester
$latest_ss = !empty($semesterstunden_of_actual_semester)
	? current($semesterstunden_of_actual_semester)['studiensemester_end_date']
// * else get end date of the last of his teaching studiensemester
	: end($semesterstunden_per_semester)['studiensemester_end_date'];
$end_date = !is_null($latest_ss) ? new DateTime($latest_ss) : null;

$actual_date = new DateTime();

// if the lector is still employed, reset the end date to null
if ($end_date > $actual_date)
{
	$end_date = null;
}

$data = array (
	'anrede' => $anrede,
	'full_name' => $fullname,
	'birthday' => $birthday_date->format('d.m.Y'),
	'begin_date' => !is_null($begin_date) ? $begin_date->format('d.m.Y') : '',
	'end_date' =>  !is_null($end_date) ? $end_date->format('d.m.Y') : '',	// empty, if lector is still employed
	'total_ss_actual_semester' => current($semesterstunden_of_actual_semester),	// empty, if lector has no lehreinheit- or projektarbeitsstunden at the actual studiensemester
	'actual_date' => $actual_date->format('d.m.Y'),
	'gender' => $gender
);

// Put semesterstunden per semester array in correct format for xsl template
if (!empty($semesterstunden_per_semester))
{
	foreach ($semesterstunden_per_semester as &$item)
	{
		$data[]= array('total_ss_per_semester'=>
		   array(
			   'studiensemester_kurzbz'=> $item['studiensemester_kurzbz'],
			   'total_semesterstunden' => $item['total_semesterstunden']
		   )
		);
	}
}
else
{
	// empty if lector has no lehreinheit- or projektarbeitsstunden in the past (before the actual studiensemester)
	$data[]= array('total_ss_per_semester'=> '');
}

// Add data to lehrtaetigkeit.xsl
$doc->addDataArray($data, 'lehrtaetigkeit');

// Set doc name
$doc->setFilename('Lehrtaetigkeit_'. rtrim($fullname, '.'));

// Create doc in format required
if (!$doc->create($output))
	die($doc->errormsg);

// Download doc
$doc->output();

// unlink doc from tmp-folder
$doc->close();
