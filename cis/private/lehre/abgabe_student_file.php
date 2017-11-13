<?php
/* Copyright (C) 2013 FH Technikum-Wien
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

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/student.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
	
$getuid=get_uid();

if(isset($_GET['student_uid']))
	$uid = $_GET['student_uid'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
	
if(isset($_GET['abgabe_id']))
	$id = $_GET['abgabe_id'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
	
if(!is_numeric($id) || $id=='')
	die($p->t('global/fehlerBeiDerParameteruebergabe'));
	
$student = new student();
if(!$student->load($uid))
	die('Student wurde nicht gefunden');
	
if($getuid!=$uid)
{
	//Berechtigung ueber das Berechtigungssystem
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($getuid);

	if(!$rechte->isBerechtigt('lehre/abgabetool'))
		die('Sie haben keine Berechtigung fuer diese Datei');
}

$file = $id.'_'.$uid.'.pdf';
$filename = PAABGABE_PATH.$file;
if(file_exists($filename))
{
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}
else
	echo 'Datei existiert nicht';
?>