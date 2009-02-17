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
	require_once('../../vilesci/config.inc.php');
	require_once('../../include/ezcomponents/Base/src/ezc_bootstrap.php');
	
	if(!$conn = pg_connect(CONN_STRING))
		die('Fehler beim der Datenbankverbindung');
	
	if(!$graph = new ezcGraphLineChart())
		die('Fehler beim Initialisieren von EZComponents');
	
	$stsem = $_GET['stsem'];
	$studiengang_kz = $_GET['studiengang_kz'];
	//$graph->title = $stsem.' 1.Semester';
	$hlp=array();
	$qry = "SELECT 
				date_part('month', datum) as monat,
				date_part('year', datum) as jahr,
				count(*) as anzahl 
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentrolle USING(prestudent_id)
			WHERE
				tbl_prestudentrolle.rolle_kurzbz='Interessent' 
				AND studiensemester_kurzbz='$stsem'
				AND studiengang_kz='$studiengang_kz'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year',datum)
			ORDER BY jahr, monat";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$hlp['Interessent'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	$qry = "SELECT 
				date_part('month', datum) as monat, 
				date_part('year', datum) as jahr, 
				count(*) as anzahl 
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentrolle USING(prestudent_id)
			WHERE
				tbl_prestudentrolle.rolle_kurzbz='Bewerber'
				AND studiensemester_kurzbz='$stsem'
				AND studiengang_kz='$studiengang_kz'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year',datum)
			ORDER BY jahr, monat";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$hlp['Bewerber'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	$qry = "SELECT 
				date_part('month', datum) as monat, 
				date_part('year', datum) as jahr, 
				count(*) as anzahl 
			FROM 
				public.tbl_studiengang 
				JOIN public.tbl_prestudent USING(studiengang_kz)
				JOIN public.tbl_prestudentrolle USING(prestudent_id)
			WHERE
				tbl_prestudentrolle.rolle_kurzbz='Student' 
				AND studiensemester_kurzbz='$stsem'
				AND studiengang_kz='$studiengang_kz'
				AND ausbildungssemester=1
			GROUP BY date_part('month', datum), date_part('year', datum)
			ORDER BY jahr, monat";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$hlp['Student'][$row->jahr.sprintf('%02s',$row->monat)]=$row->anzahl;
			$keys[] = $row->jahr.sprintf('%02s',$row->monat);
		}
	}
	
	$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
	$graph->xAxis->axisLabelRenderer->angle = 0;
	//$graph->xAxis->axisSpace = .2; 
	if(empty($keys))
		die('Keine Daten vorhanden');
	asort($keys, SORT_NUMERIC);
	//$keys = array_unique($keys);
	//var_dump($keys);
	$firstkey = $keys[0];
	$lastkey = $keys[count($keys)-1];
	$year = substr($firstkey,0,4);
	$month = substr($firstkey, 4);
	$lastyear = substr($lastkey, 0, 4);
	$lastmonth = substr($lastkey, 4);
	
	for($i=$year;$i<=$lastyear;$i++)
	{
		if($i==$lastyear)
			$maxmonth=$lastmonth;
		else 
			$maxmonth=12;
		//echo "<br>$i==$lastyear:$maxmonth<br>";
		for($j=$month;$j<=$maxmonth;$j++)
		{
			if(!in_array($i.sprintf('%02s',$j), $keys))
				$keys[]=$i.sprintf('%02s',$j);
		}
		$month=1;
	}
	$keys = array_unique($keys);
	asort($keys, SORT_NUMERIC);
	//var_dump($keys);
	foreach($hlp as $status=>$data)
	{			
		
		reset($keys);
		$valuebefore=0;
		foreach ($keys as $key) 
		{
			if(!isset($data[$key]))
				$data[$key] = $valuebefore;
			else 
				$data[$key] = $data[$key]+$valuebefore;	
			$valuebefore=$data[$key];
			//echo $key.' '.$valuebefore.'<br>';
		}
		
		ksort($data, SORT_NUMERIC);
		//echo '<br>'.$status.'<br>';
		//var_dump($data);
		/*
		$oldvalue=0;
		foreach ($data as $key=>$value) 
		{
			$data[$key]=$data[$key]+$oldvalue;
			$oldvalue = $data[$key];
		}
		*/
		
		$graph->data[$status] = new ezcGraphArrayDataSet( $data );
		//$graph->data[$status]->highlight[9] = true;
	}
	
	$graph->renderToOutput( 500, 500);
 ?>