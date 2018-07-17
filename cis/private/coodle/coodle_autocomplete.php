<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/gruppe.class.php');

if(!isset($_REQUEST['work']))
	die('Parameter Work missing');

$work = $_REQUEST['work'];
if(isset($_REQUEST['term']))
	$q = $_REQUEST['term'];
else
	$q = $_REQUEST['q'];

switch($work)
{
	case 'ressource':
			$result =array();
			$ort = new ort();

			if(!$ort->filter($q))
				die('Fehler beim Laden der Orte: '.$ort->errormsg);

			foreach($ort->result as $row)
			{
				if($row->aktiv)
				{
					//echo html_entity_decode($row->ort_kurzbz.'|Ort|'.$row->bezeichnung."\n");
					$item['uid']=$row->ort_kurzbz;
					$item['typ']='Ort';
					$item['bezeichnung']=$row->bezeichnung;
					$result[]=$item;
				}
			}
		 	
			$benutzer = new benutzer();
			
			if(!$benutzer->search(array($q), null, true))
				die('Fehler beim Laden der Benutzer: '.$benutzer->errormsg);
				
			foreach($benutzer->result as $row)
			{
				//echo html_entity_decode($row->uid.'|Person|'.$row->nachname.' '.$row->vorname."\n");
				$item['uid']=$row->uid;
				$item['typ']='Person';
				$item['bezeichnung']=$row->nachname.' '.$row->vorname;
				$result[]=$item;
			}
			
			$gruppe = new gruppe();
			
			if(!$gruppe->searchGruppen(array($q)))
				die('Fehler beim Laden der Gruppe: '.$gruppe->errormsg);
				
			foreach($gruppe->result as $row)
			{
				if ($row->sichtbar)
				{
					$item['uid']=$row->gruppe_kurzbz;
					$item['typ']='Gruppe';
					$item['bezeichnung']='Gruppe';
					$result[]=$item;
				}
			}
			
			echo json_encode($result);
			break;
	default:
			die('Invalid Work Parameter');
}
?>