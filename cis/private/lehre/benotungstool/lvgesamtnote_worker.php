<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 *
 */
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/notenschluessel.class.php');

$uid = get_uid();

if(!check_lektor($uid))
	die('Sie haben keine Berechtigung fuer diese Seite');

if(!isset($_POST['work']))
	die('Fehlerhafte Parameteruebergabe');

$lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
$punkte = $_POST['punkte'];
$studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
$work = $_POST['work'];

switch($work)
{
	case 'getGradeFromPoints':
		$notenschluessel = new notenschluessel();
		$note = $notenschluessel->getNote($punkte, $lehrveranstaltung_id, $studiensemester_kurzbz);
		echo $note;
		exit;
}
?>
