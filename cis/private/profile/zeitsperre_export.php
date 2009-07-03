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
// **
// * @brief Uebersicht der Zeitsperren fuer Lektorengruppen

  require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');

	$crlf=crlf();
	$trenn=";";

	$uid = get_uid();

	if(isset($_GET['lektor']))
		$lektor=$_GET['lektor'];
	else
		$lektor=null;
	if ($lektor=='true' || $lektor=='1') $lektor=true;
	if ($lektor=='false' || $lektor=='') $lektor=false;

	if(isset($_GET['fix']))
		$fix=$_GET['fix'];
	else
		$fix=null;
	if ($fix=='true' || $fix=='1') $fix=true;
	if ($fix=='false' || $fix=='') $fix=false;

	if(isset($_GET['funktion']))
		$funktion=$_GET['funktion'];
	else
		$funktion=null;
	if ($funktion=='true' || $funktion=='1') $funktion=true;
	if ($funktion=='false' || $funktion=='') $funktion=false;

	if(isset($_GET['institut']))
		$institut = $_GET['institut'];
	else
		$institut = null;

	$stge=array();
	if(isset($_GET['stg_kz']))
	{
		$stg_kz=$_GET['stg_kz'];
		$stge[]=$stg_kz;
	}

	if(isset($_GET['studiensemester']))
		$studiensemester=$_GET['studiensemester'];
	else
		$studiensemester=null;

	$datum_obj = new datum();

	// Studiensemester setzen
	$ss=new studiensemester($studiensemester);
	if ($studiensemester==null)
	{
		$studiensemester = $ss->getaktorNext();
		$ss->load($studiensemester);
		//$studiensemester=$ss->getAktTillNext();
	}
	$datum_beginn=$ss->start;
	$datum_ende='2008-09-01';//$ss->ende;
	$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
	$ts_ende=$datum_obj->mktime_fromdate($datum_ende);

	// Lektoren holen
	$ma=new mitarbeiter();
	if(!is_null($institut))
	{
		$mitarbeiter = $ma->getMitarbeiterInstitut($institut);
	}
	else
	{
		//if (!is_null($funktion))
		//	$mitarbeiter=$ma->getMitarbeiterStg(true,null,$stge,$funktion);
		//else
			$mitarbeiter=$ma->getMitarbeiter(null,true);//($lektor,$fix);
	}

//EXPORT
	header("Content-type: application/vnd.ms-excel");
    header('Content-Disposition: attachment; filename="Zeitsperren.csv"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");

	echo '"Datum"'.$trenn;
	for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
	{
		$tag=date('d',$ts);
		$wt=date('w',$ts);
		$monat=date('M',$ts);
		if ($wt==0 || $wt==6)
			$class='feiertag';
		else
			$class='';
		echo '"'.$tagbez[$wt].' '.$tag.'.'.$monat.'"'.$trenn;
	}
	$zs=new zeitsperre();
	foreach ($mitarbeiter as $ma)
	{
		$zs->getzeitsperren($ma->uid, false);
		echo $crlf.'"'.$ma->nachname.' '.$ma->vorname.'"'.$trenn;
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$monat=date('M',$ts);
			$wt=date('w',$ts);
			if ($wt==0 || $wt==6)
				$class='feiertag';
			else
				$class='';
			$grund=$zs->getTyp($ts);
			$erbk=$zs->getErreichbarkeit($ts);
			echo '"'.$grund.' - '.$erbk.'"'.$trenn;
		}
	}
	?>