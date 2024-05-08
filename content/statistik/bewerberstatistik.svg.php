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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/basis_db.class.php');
	require_once('../../include/ezcomponents/Base/src/ezc_bootstrap.php');
	require_once('../../include/studiengang.class.php');
		
	if(!$graph = new ezcGraphLineChart())
		die('Fehler beim Initialisieren von EZComponents');
	
	$stsem = (isset($_GET['stsem'])?$_GET['stsem']:'');
	$studiengang_kz = (isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'');
	$db = new basis_db();
	$hlp=array();
	
	$stg = new studiengang($studiengang_kz);
	$studienplaetze = ($stg->studienplaetze!=''?$stg->studienplaetze:0);
	
	//Interessenten holen
	$qry = "SELECT 
				date_part('month', datum) as monat,
				date_part('year', datum) as jahr,
				count(*) as anzahl
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
			WHERE
				tbl_prestudentstatus.status_kurzbz='Interessent' 
				AND studiensemester_kurzbz='".addslashes($stsem)."'
				AND studiengang_kz='".addslashes($studiengang_kz)."'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year',datum)
			ORDER BY jahr, monat";
	
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$hlp['Interessent'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	
	//Bewerber holen
	$qry = "SELECT 
				date_part('month', datum) as monat, 
				date_part('year', datum) as jahr, 
				count(*) as anzahl 
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
			WHERE
				tbl_prestudentstatus.status_kurzbz='Bewerber'
				AND studiensemester_kurzbz='".addslashes($stsem)."'
				AND studiengang_kz='".addslashes($studiengang_kz)."'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year',datum)
			ORDER BY jahr, monat";
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$hlp['Bewerber'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	
	//Studenten holen
	$qry = "SELECT 
				date_part('month', datum) as monat, 
				date_part('year', datum) as jahr, 
				count(*) as anzahl
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
			WHERE
				tbl_prestudentstatus.status_kurzbz='Student' 
				AND studiensemester_kurzbz='".addslashes($stsem)."'
				AND studiengang_kz='".addslashes($studiengang_kz)."'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year', datum)
			ORDER BY jahr, monat";
	
	if($db->db_query($qry))
	{
		while($row = $db->db_fetch_object())
		{
			$hlp['Student'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	
	$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
	$graph->xAxis->axisLabelRenderer->angle = 0;
	
	if(empty($keys))
		die('Keine Daten vorhanden');
	asort($keys, SORT_NUMERIC);
	
	//Keys fuer alle Monate anlegen damit keine Luecken vorhanden sind
	$firstkey = $keys[0];
	$lastkey = $keys[count($keys)-1];
	$year = mb_substr($firstkey,0,4);
	$month = mb_substr($firstkey, 4);
	$lastyear = mb_substr($lastkey, 0, 4);
	$lastmonth = mb_substr($lastkey, 4);
	
	for($i=$year;$i<=$lastyear;$i++)
	{
		if($i==$lastyear)
			$maxmonth=$lastmonth;
		else 
			$maxmonth=12;
		
		for($j=$month;$j<=$maxmonth;$j++)
		{
			if(!in_array($i.sprintf('%02s',$j), $keys))
				$keys[]=$i.sprintf('%02s',$j);
		}
		$month=1;
	}
	$keys = array_unique($keys);
	asort($keys, SORT_NUMERIC);
	
	//Array mit den Daten befuellen
	//Die Daten muessen fuer jeden Status fuer alle Monate gesetzt werden,
	//sonst gibts einen error
	foreach($hlp as $status=>$data)
	{		
		reset($keys);
		$valuebefore=0;
		foreach ($keys as $key) 
		{
			//Studienplaetze fuer jedes Monat eintragen
			$studienplaetzearr[$key]=$studienplaetze;
			if(!isset($data[$key]))
				$data[$key] = $valuebefore;
			else 
				$data[$key] = $data[$key]+$valuebefore;	
			$valuebefore=$data[$key];
		}
		
		ksort($data, SORT_NUMERIC);
		
		$graph->data[$status] = new ezcGraphArrayDataSet( $data );
	}
	//Sollstudienplaetze markieren 
	$graph->data['Studienplaetze'] = new ezcGraphArrayDataSet( $studienplaetzearr );
		
	$graph->renderToOutput( 500, 500);
 ?>