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

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/functions.inc.php');    
require_once('../../../include/lehrverband.class.php');

$uid = get_uid();

if (!$db = new basis_db())
    die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if(!isset($_REQUEST['autocomplete']))
	die('autocomplete param missing');

switch($_REQUEST['autocomplete'])
{
	case 'benutzer':
		$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
		if (is_null($search) ||$search=='')
		exit();	

		$benutzer = new benutzer(); 
		$searchItems = explode(' ',$search);
		if($benutzer->search($searchItems))
		{
			$result_obj = array();
			foreach($benutzer->result as $row)
			{
				$item['vorname']=html_entity_decode($row->vorname);
				$item['nachname']=html_entity_decode($row->nachname);
				$item['uid']=html_entity_decode($row->uid);
				$item['mitarbeiter_uid']=html_entity_decode($row->mitarbeiter_uid);
				$result_obj[]=$item;
			}
			echo json_encode($result_obj);
		}
		break;
	case 'mitarbeiter':
		$search=trim((isset($_REQUEST['term']) ? $_REQUEST['term']:''));
		if (is_null($search) ||$search=='')
			exit();

		$mitarbeiter = new mitarbeiter();
		$searchItems = explode(' ',$search);

		foreach ($searchItems as $searchItem)
		{
			if ($mitarbeiter->search($searchItem))
			{
				$result_obj = array();
				foreach ($mitarbeiter->result as $row)
				{
					$item['vorname'] = html_entity_decode($row->vorname);
					$item['nachname'] = html_entity_decode($row->nachname);
					$item['uid'] = html_entity_decode($row->uid);
					$result_obj[] = $item;
				}
			}
		}
		echo json_encode($result_obj);
		break;
	case 'getSemester':
		$studiengang = new studiengang();
		$data = array();
		if($studiengang->load($_REQUEST['stg_kz']))
		{
			for($i=1;$i<=$studiengang->max_semester;$i++)
			{
				$data[]=$i;
			}
			echo json_encode($data);
		}
		else
		{
			echo $studiengang->errormsg;
		}			
		break;
	case 'getVerband':
		$lvb = new lehrverband();
		$studiengang_kz=$_REQUEST['stg_kz'];
		$semester=$_REQUEST['sem'];
		$data = array();
		if($lvb->getlehrverband($studiengang_kz, $semester))
		{
			foreach($lvb->result as $row)
			{
				if(trim($row->verband)!='')
					$data[]=$row->verband;
			}
			$data = array_unique($data);
			echo json_encode($data);
		}
		else
		{
			echo $studiengang->errormsg;
		}			
		break;
	case 'getGruppe':
		$lvb = new lehrverband();
		$studiengang_kz=$_REQUEST['stg_kz'];
		$semester=$_REQUEST['sem'];
		$verband=$_REQUEST['ver'];
		$data = array();
		if($lvb->getlehrverband($studiengang_kz, $semester, $verband))
		{
			foreach($lvb->result as $row)
			{
				if(trim($row->gruppe)!='')
					$data[]=$row->gruppe;
			}
			$data = array_unique($data);
			echo json_encode($data);
		}
		else
		{
			echo $studiengang->errormsg;
		}			
		break;
	default:
		echo 'Invalid Parameter';
		break;
}
?>
