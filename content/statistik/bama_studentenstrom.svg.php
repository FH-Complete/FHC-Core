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

	
	$db = new basis_db();
	//$user = get_uid();
	$stsem=0;
	$studiengang_kz=0;

	if(!$graph = new ezcGraphLineChart())
		die('Fehler beim Initialisieren von EZComponents');
	
	$stsem = $_GET['stsem'];
	$studiengang_kz = $_GET['studiengang_kz'];
	$typ = $_GET['typ'];
	$studiengang_kurzbz = strtoupper($typ.$_GET['kurz']);
	$graph->title = "5-Jahresansicht Stg: ".$studiengang_kurzbz;
	$hlp=array();
	$keys=array();
	$summe=array();
if(trim($typ)=="m")
{
	FOR($i=0;$i<5;$i++)
	{
		$summe[(substr(trim($stsem),-4)-$i)] = 0;
		//Anzahl pro Studiengang
		$qry = "SELECT DISTINCT count(*)as count, studiengang_kz, typ||kurzbz as stgkurz  
		FROM public.tbl_person JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
		JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
		JOIN public.tbl_studiengang USING(studiengang_kz) 
		WHERE status_kurzbz='Absolvent' AND typ!='m' 
			AND public.tbl_person.person_id IN(SELECT public.tbl_person.person_id FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz=".$db->db_add_param($studiengang_kz)."  
			AND studiensemester_kurzbz='WS".(substr(trim($stsem),-4)-$i)."' 
			AND status_kurzbz='Student' 
			AND ausbildungssemester='1') 
		GROUP BY studiengang_kz, typ, public.tbl_studiengang.bezeichnung, public.tbl_studiengang.kurzbz ORDER BY stgkurz";
		//echo $qry."<br>--<br>";
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$hlp[strtoupper($row->stgkurz)][(substr(trim($stsem),-4)-$i)]=$row->count;
				$summe[(substr(trim($stsem),-4)-$i)] = $summe[(substr(trim($stsem),-4)-$i)] + $row->count;
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}
		}
		//Gesamtanzahl
		$qry_anzahl="SELECT count(*) as anzahl FROM public.tbl_person 
		JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
		JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
		WHERE studiengang_kz=".$db->db_add_param($studiengang_kz)."  
			AND studiensemester_kurzbz='WS".(substr(trim($stsem),-4)-$i)."' 
			AND status_kurzbz='Student' 
			AND ausbildungssemester='1'";
		if($result_anzahl=$db->db_query($qry_anzahl))
		{
			if($row_anzahl=$db->db_fetch_object($result_anzahl))
			{
				$hlp['extern'][(substr(trim($stsem),-4)-$i)]= $row_anzahl->anzahl - $summe[(substr(trim($stsem),-4)-$i)];
				if($hlp['extern'][(substr(trim($stsem),-4)-$i)]<0)
					$hlp['extern'][(substr(trim($stsem),-4)-$i)]=0;
				$hlp['gesamt'][(substr(trim($stsem),-4)-$i)]= $row_anzahl->anzahl;
				if($hlp['gesamt'][(substr(trim($stsem),-4)-$i)]<0)
					$hlp['gesamt'][(substr(trim($stsem),-4)-$i)]=0;
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}
		}
	}
	//'0'-er ergänzen
	FOR ($i=0;$i<5;$i++)
	{

		foreach(array_keys($hlp)as $jeder)
		{
			if(empty($hlp[$jeder][(substr(trim($stsem),-4)-$i)]))
			{
				$hlp[$jeder][(substr(trim($stsem),-4)-$i)]='0';
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}

		}
	}
}
if(trim($typ)=="b")
{
	FOR($i=0;$i<5;$i++)
	{
		$summe[(substr(trim($stsem),-4)-$i)] = 0;
		//Anzahl pro Studiengang
		$qry = "SELECT DISTINCT count(*)as count, studiengang_kz, typ||kurzbz as stgkurz FROM 
		(SELECT DISTINCT ON(public.tbl_person.person_id, studiengang_kz) studiengang_kz,typ, tbl_studiengang.bezeichnung, tbl_studiengang.kurzbz   
		FROM public.tbl_person JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
		JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
		JOIN public.tbl_studiengang USING(studiengang_kz) 
		WHERE status_kurzbz='Student' AND typ='m' 
			AND public.tbl_person.person_id IN(SELECT public.tbl_person.person_id FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz=".$db->db_add_param($studiengang_kz)."
			AND status_kurzbz='Absolvent'
			AND (studiensemester_kurzbz='WS".(substr(trim($stsem),-4)-$i)."' OR studiensemester_kurzbz='SS".(substr(trim($stsem),-4)-$i)."') )) as b 
		GROUP BY studiengang_kz, typ, bezeichnung, kurzbz ORDER BY stgkurz";
		//echo $qry."<br>--<br>";
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$hlp[strtoupper($row->stgkurz)][(substr(trim($stsem),-4)-$i)]=$row->count;
				$summe[(substr(trim($stsem),-4)-$i)] = $summe[(substr(trim($stsem),-4)-$i)] + $row->count;
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}
		}
		//Gesamtanzahl
		$qry_anzahl="SELECT count(*) as anzahl FROM public.tbl_person 
			JOIN public.tbl_prestudent ON(public.tbl_person.person_id=public.tbl_prestudent.person_id) 
			JOIN public.tbl_prestudentstatus ON(public.tbl_prestudent.prestudent_id=public.tbl_prestudentstatus.prestudent_id) 
			WHERE studiengang_kz=".$db->db_add_param($studiengang_kz)."
			AND status_kurzbz='Absolvent'
			AND (studiensemester_kurzbz='WS".(substr(trim($stsem),-4)-$i)."' OR studiensemester_kurzbz='SS".(substr(trim($stsem),-4)-$i)."')";
		if($result_anzahl=$db->db_query($qry_anzahl))
		{
			if($row_anzahl=$db->db_fetch_object($result_anzahl))
			{
				$hlp['extern'][(substr(trim($stsem),-4)-$i)]= $row_anzahl->anzahl - $summe[(substr(trim($stsem),-4)-$i)];
				if($hlp['extern'][(substr(trim($stsem),-4)-$i)]<0)
					$hlp['extern'][(substr(trim($stsem),-4)-$i)]=0;
				$hlp['gesamt'][(substr(trim($stsem),-4)-$i)]= $row_anzahl->anzahl;
				if($hlp['gesamt'][(substr(trim($stsem),-4)-$i)]<0)
					$hlp['gesamt'][(substr(trim($stsem),-4)-$i)]=0;
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}
		}
	}
	//'0'-er ergänzen
	FOR ($i=0;$i<5;$i++)
	{

		foreach(array_keys($hlp)as $jeder)
		{
			if(empty($hlp[$jeder][(substr(trim($stsem),-4)-$i)]))
			{
				$hlp[$jeder][(substr(trim($stsem),-4)-$i)]='0';
				$keys[$i] = (substr(trim($stsem),-4)-$i);
			}

		}
	}
}
	//var_dump($hlp);
	//die;
	$graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
	$graph->xAxis->axisLabelRenderer->angle = 0;
 
	if(empty($keys))
		die('Keine Daten vorhanden');
	asort($keys, SORT_NUMERIC);

	foreach($hlp as $status=>$data)
	{			
		reset($keys);		
		ksort($data, SORT_NUMERIC);
		$graph->data[$status] = new ezcGraphArrayDataSet( $data );
	} 
	$graph->renderToOutput( 500, 500);
?>
