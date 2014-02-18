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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Exportiert die Zeitsperren von Mitarbeitern als CSV File
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/sprache.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
$sprache_obj = new sprache();
$sprache_obj->load($sprache);
$sprache_index=$sprache_obj->index;

$crlf=crlf();
$trenn=";";

$uid = get_uid();

if(isset($_GET['lektor']))
{
	$lektor=$_GET['lektor'];
	if ($lektor=='true' || $lektor=='1') 
		$lektor=true;
	else
		$lektor=false;
}
else
	$lektor=null;


if(isset($_GET['fix']))
{
	$fix=$_GET['fix'];
	if ($fix=='true' || $fix=='1') 
		$fix=true;
	else
		$fix=false;
}
else
	$fix=null;

if(isset($_GET['funktion']))
{
	$funktion=$_GET['funktion'];
}
else
	$funktion=null;

if(isset($_GET['organisationseinheit']))
	$organisationseinheit = $_GET['organisationseinheit'];
else
	$organisationseinheit = null;

$stge=array();
if(isset($_GET['stg_kz']))
{
	$stg_kz=$_GET['stg_kz'];
	$stge[]=$stg_kz;
}

//Datumsbereich ermitteln
$datum_obj = new datum();
$days=trim((isset($_REQUEST['days']) && is_numeric($_REQUEST['days'])?$_REQUEST['days']:14));

$dTmpAktuellerMontag=date("Y-m-d",strtotime(date('Y')."W".date('W')."1")); // Montag der Aktuellen Woche
$dTmpAktuellesDatum=explode("-",$dTmpAktuellerMontag);
$dTmpMontagPlus=date("Y-m-d", mktime(0,0,0,date($dTmpAktuellesDatum[1]),date($dTmpAktuellesDatum[2])+$days,date($dTmpAktuellesDatum[0])));

$datum_beginn=$dTmpAktuellerMontag; 
$datum_ende=$dTmpMontagPlus;

$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
$ts_ende=$datum_obj->mktime_fromdate($datum_ende);

// Mitarbeiter laden
$ma=new mitarbeiter();
if(!is_null($organisationseinheit))
{
	$mitarbeiter = $ma->getMitarbeiterOrganisationseinheit($organisationseinheit);
}
else
{
	if (is_null($funktion))
		$mitarbeiter=$ma->getMitarbeiter($lektor,$fix);
	else
		$mitarbeiter=$ma->getMitarbeiterStg(true,null,$stge,$funktion);
}

//EXPORT
header("Content-type: text/csv; charset=utf-9");
header('Content-Encoding: UTF-8');
header('Content-Disposition: attachment; filename="Zeitsperren.csv"');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
//echo "\xEF\xBB\xBF"; // UTF-8 BOM


echo '"'.$p->t('global/datum').'"'.$trenn;
for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
{
	$tag=date('d',$ts);
	$wt=date('N',$ts);
	$monat=date('M',$ts);
	echo '"'.$tagbez[$sprache_index][$wt].' '.$tag.'.'.$monat.'"'.$trenn;
}
$zs=new zeitsperre();
foreach ($mitarbeiter as $ma)
{
	if($ma->aktiv)
	{
		$zs->getzeitsperren($ma->uid, false);
		echo $crlf.'"'.$ma->nachname.' '.$ma->vorname.'"'.$trenn;
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$monat=date('M',$ts);
			$wt=date('N',$ts);
			$grund=$zs->getTyp($ts);
			$erbk=$zs->getErreichbarkeit($ts);
			$vertretung=$zs->getVertretung($ts);
			echo '"'.html_entity_decode($grund).' - '.html_entity_decode($erbk).'"'.$trenn;
		}
	}
}
?>