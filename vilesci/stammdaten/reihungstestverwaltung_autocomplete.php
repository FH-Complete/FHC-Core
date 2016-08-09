<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Manfred Kindl <kindlm@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/ort.class.php'); 
require_once('../../include/benutzer.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/sprache.class.php');
	
if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid=get_uid();
$sprache = getSprache();

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='kunde')
{
	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
	if (is_null($search) ||$search=='')
		exit();	
	
	$benutzer = new benutzer();
	
	if($benutzer->search(array($search)))
	{
		$result_obj = array();
		foreach($benutzer->result as $row)
		{
			$item['vorname']=html_entity_decode($row->vorname);
			$item['nachname']=html_entity_decode($row->nachname);
			$item['uid']=html_entity_decode($row->uid);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='studienplan')
{
	$searchItems=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
	
	// String aufsplitten und Sonderzeichen entfernen
	$searchItems = explode(' ',trim(str_replace(',', '', $searchItems),'!.?'));
	
	// Wenn nach dem TRIM keine Zeichen uebrig bleiben, dann abbrechen
	if(implode(',', $searchItems)=='')
		exit();

	$studienplan = new studienplan();
	
	if($studienplan->searchStudienplaene($searchItems))
	{
		$result_obj = array();
		foreach($studienplan->result as $row)
		{
			$item['studienplan_id']=html_entity_decode($row->studienplan_id);
			$item['bezeichnung']=html_entity_decode($row->bezeichnung);
			//$item['uid']=html_entity_decode($row->uid);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='getStudienplan')
{
	$orgform_obj = new organisationsform();
	$orgform_obj->getAll();
	$orgform_arr=array();
	foreach($orgform_obj->result as $row)
		$orgform_arr[$row->orgform_kurzbz]=$row->bezeichnung;
	
	$sprachen_obj = new sprache();
	$sprachen_obj->getAll();
	$sprachen_arr=array();
	
	foreach($sprachen_obj->result as $row)
	{
		if(isset($row->bezeichnung_arr[$sprache]))
			$sprachen_arr[$row->sprache]=$row->bezeichnung_arr[$sprache];
		else
			$sprachen_arr[$row->sprache]=$row->sprache;
	}
	
	$studienplan_obj = new studienplan();
	
	if ($studienplan_obj->getStudienplaeneFromSem($_REQUEST['stg_kz'], $_REQUEST['studiensemester_kurzbz']))
	{
		$studienordnung_arr = array();
		$studienplan_arr = array();
		$data = array();
		
		foreach($studienplan_obj->result as $row_sto)
		{
			$studienordnung_arr[$row_sto->studienordnung_id]['bezeichnung']=$row_sto->bezeichnung_studienordnung;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['bezeichnung']=$row_sto->bezeichnung_studienplan;
		
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['orgform_kurzbz']=$row_sto->orgform_kurzbz;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['sprache']=$sprachen_arr[$row_sto->sprache];
		}
		$i=0;
		foreach($studienordnung_arr as $stoid=>$row_sto)
		{
			$data[$i]['sto_bezeichnung']=$db->convert_html_chars($row_sto['bezeichnung']);
			foreach ($studienplan_arr[$stoid] as $stpid=>$row_stp)
			{
				$data[$i]['stpid']=$stpid;
				$data[$i]['bezeichnung'] = $db->convert_html_chars($row_stp['bezeichnung']).' ('.$orgform_arr[$row_stp['orgform_kurzbz']].', '.$row_stp['sprache'].')';
				$i++;
			}
		}
		echo json_encode($data);
	}
	else
		echo $studienplan_obj->errormsg;
	exit;
}

if(isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete']=='ort_aktiv')
{
	$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
	if (is_null($search) ||$search=='')
		exit();	
	
	$ort_auswahl = new ort(); 

	if($ort_auswahl->filter($search,true))
	{
		$result_obj = array();
		foreach($ort_auswahl->result as $row)
		{
			$item['ort_kurzbz']=html_entity_decode($row->ort_kurzbz);
			$item['planbezeichnung']=html_entity_decode($row->planbezeichnung);
			$item['bezeichnung']=html_entity_decode($row->bezeichnung);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}
?>
