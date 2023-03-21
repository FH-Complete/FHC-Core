<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/dokument_export.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/erhalter.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/lehrelisthelper.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid = $_GET['lvid'];
else
	die('Eine gueltige LvID muss uebergeben werden');

$lv = new lehrveranstaltung();
$lv->load($lvid);

if(isset($_GET['stsem']))
	$studiensemester = $_GET['stsem'];
else
	die('Eine Studiensemester muss uebergeben werden');

if(	!$berechtigung->isBerechtigt('admin')
	&& !$berechtigung->isBerechtigt('assistenz')
	&& !$berechtigung->isBerechtigt('lehre', $lv->oe_kurzbz, 's')
	&& !check_lektor_lehrveranstaltung($user,$lvid,$studiensemester))
	die('Sie muessen LektorIn der LV sein oder das Recht "ADMIN", "ASSISTENZ" oder "LEHRE" haben, um diese Seite aufrufen zu koennen');

$output='pdf';

if(isset($_GET['output']) && ($output='odt' || $output='doc'))
	$output=$_GET['output'];

isset($_GET['stg_kz']) ? $studiengang = $_GET['stg_kz'] : $studiengang = NULL;
isset($_GET['lehreinheit_id']) ? $lehreinheit = $_GET['lehreinheit_id'] : $lehreinheit = NULL;

$stg = new studiengang();
$stg->load($lv->studiengang_kz);

$doc = new dokument_export('Anwesenheitslist',  $stg->oe_kurzbz);

$lehrelisthelper = new LehreListHelper($db, $studiensemester, $lvid, $lv, $stg, $lehreinheit);
$arr_lehrende = $lehrelisthelper->getArr_Lehrende();
$data = $lehrelisthelper->getData();
$studentuids = $lehrelisthelper->getStudentUids();

$doc->addDataArray($data,'anwesenheitsliste');
if($lehreinheit!='')
{
	$lehrende = '_'.implode('_',array_unique($arr_lehrende));
}
else
	$lehrende = '';

$doc->setFilename('Anwesenheitsliste_'.$studiensemester.'_'.$stg->kuerzel.'_'.$lv->semester.'_'.$lv->kurzbz.$lehrende);
if(!$doc->create($output))
	die($doc->errormsg);
$doc->output();
$doc->close();
?>
