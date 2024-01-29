<?php
/*
 * Copyright (C) 2010 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Manfred Kindl <kindlm@technikum-wien.at>
 */
require_once ('../../config/vilesci.config.inc.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/basis_db.class.php');
require_once ('../../include/ort.class.php');
require_once ('../../include/benutzer.class.php');
require_once ('../../include/studiengang.class.php');
require_once ('../../include/lehrverband.class.php');
require_once ('../../include/studienplan.class.php');
require_once ('../../include/studienordnung.class.php');
require_once ('../../include/organisationsform.class.php');
require_once ('../../include/organisationseinheit.class.php');
require_once ('../../include/sprache.class.php');

if (! $db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$sprache = getSprache();

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'kunde')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
		exit();
	
	$benutzer = new benutzer();
	
	if ($benutzer->search(array(
		$search
	)))
	{
		$result_obj = array();
		foreach ($benutzer->result as $row)
		{
			$item['vorname'] = html_entity_decode($row->vorname);
			$item['nachname'] = html_entity_decode($row->nachname);
			$item['uid'] = html_entity_decode($row->uid);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit();
}

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'studienplan')
{
	header('Content-Type: application/json; charset=UTF-8');
	$searchItems = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	$aktiv = trim((isset($_REQUEST['aktiv']) ? $_REQUEST['aktiv'] : null));
	$studiensemester_kurzbz = trim((isset($_REQUEST['studiensemester_kurzbz']) && $_REQUEST['studiensemester_kurzbz'] != '' ? $_REQUEST['studiensemester_kurzbz'] : null));
	
	if ($aktiv == 'true')
	{
		$aktiv = true;
	}
	elseif ($aktiv == 'false')
	{
		$aktiv = false;
	}
	else
	{
		$aktiv = null;
	}
	
	// String aufsplitten und Sonderzeichen entfernen
	$searchItems = explode(' ', trim(str_replace(',', '', $searchItems), '!.?'));
	
	// Wenn nach dem TRIM keine Zeichen uebrig bleiben, dann abbrechen
	if (implode(',', $searchItems) == '')
		exit();
	
	$result_obj = array();
	// Array mit allen Studiengängen, allen aktiven Studienplänen, allen Fakultäten und allen Studiengangstypen in den Fakultäten aufbauen
	$oeArray = array();
	$i = 0;
	$studiengangsTypen = array(
		'b',
		'm'
	);
	
	$fakultaeten = new organisationseinheit();
	$fakultaeten->getByTyp('Fakultaet');
	
	// Fakultäten nach Studiengangstyp auflösen
	/*
	 * zB
	 * Bachelor Fakultät Life Science Engineering
	 * Bachelor Fakultät Electronic Engineering
	 * Master Fakultät Life Science Engineering
	 * Master Fakultät Electronic Engineering
	 */
	$stgTypen = new studiengang();
	$stgTypen->getAllTypes();
	foreach ($stgTypen->studiengang_typ_arr as $typ => $typbezeichnung)
	{
		if (in_array($typ, $studiengangsTypen))
		{
			$oeArray[$i]['bezeichnung'] = $typbezeichnung . ' (Alle)';
			
			// Studiengänge unterhalb der Fakultät laden
			$studiengang = new studiengang();
			$studiengang->loadStudiengaengeFromTyp($typ);
			
			foreach ($studiengang->result as $stgang)
			{
				$studienplan = new studienplan();
				$studienplan->getStudienplaeneFromSem($stgang->studiengang_kz, $studiensemester_kurzbz, 1);
				
				foreach ($studienplan->result as $plan)
				{
					$oeArray[$i]['studienplaene'][] = $plan->studienplan_id;
				}
			}
			$i ++;
			
			foreach ($fakultaeten->result as $fak)
			{
				$oeArray[$i]['bezeichnung'] = $typbezeichnung . ' Fakultät ' . $fak->bezeichnung;
				// Studiengänge unterhalb der Fakultät laden
				$childOes = new organisationseinheit();
				$childOesArray = $childOes->getChilds($fak->oe_kurzbz, 'Studiengang');
				
				// Letzte gültige Studienpläne aller passenden Studiengänge laden und in Array schreiben
				foreach ($childOesArray as $child)
				{
					$studiengang = new studiengang();
					$studiengang->getStudiengangFromOe($child, true);
					
					if ($studiengang->typ == $typ)
					{
						$studienplan = new studienplan();
						$studienplan->getStudienplaeneFromSem($studiengang->studiengang_kz, $studiensemester_kurzbz, 1);
						
						foreach ($studienplan->result as $plan)
						{
							$oeArray[$i]['studienplaene'][] = $plan->studienplan_id;
						}
					}
				}
				$i ++;
			}
		}
	}
	
	// Fakultäten direkt auflösen
	/*
	 * zB
	 * Fakultät Life Science Engineering
	 * Fakultät Electronic Engineering
	 * Fakultät Computer Science
	 * Fakultät Industrial Engineering
	 */
	foreach ($fakultaeten->result as $fak)
	{
		$oeArray[$i]['bezeichnung'] = ' Fakultät ' . $fak->bezeichnung;
		// Studiengänge unterhalb der Fakultät laden
		$childOes = new organisationseinheit();
		$childOesArray = $childOes->getChilds($fak->oe_kurzbz, 'Studiengang');
		
		// Letzte gültige Studienpläne aller passenden Studiengänge laden und in Array schreiben
		foreach ($childOesArray as $child)
		{
			$studiengang = new studiengang();
			$studiengang->getStudiengangFromOe($child, true);
			
			$studienplan = new studienplan();
			$studienplan->getStudienplaeneFromSem($studiengang->studiengang_kz, $studiensemester_kurzbz, 1);
			
			foreach ($studienplan->result as $plan)
			{
				$oeArray[$i]['studienplaene'][] = $plan->studienplan_id;
			}
		}
		$i ++;
	}
	
	foreach ($searchItems as $row)
	{
		foreach ($oeArray as $oe)
		{
			if (mb_stripos($oe['bezeichnung'], $row) !== false)
			{
				$value['studienplan_id'] = $oe['studienplaene'];
				$value['bezeichnung'] = $oe['bezeichnung'];
				$value['status'] = '';
				$item['disabled'] = false;
				$result_obj[] = $value;
			}
		}
	}
	
	$studienplan = new studienplan();
	$studienplan->searchStudienplaene($searchItems, $aktiv, $studiensemester_kurzbz);
	$status_arr = array();
	$studienordnung = new studienordnung();
	$studienordnung->getStatus();
	foreach ($studienordnung->result as $row_status)
		$status_arr[$row_status->status_kurzbz] = $row_status->bezeichnung;
	
	if (count($studienplan->result) == 0 && empty($result_obj))
	{
		// Wenn für das übergbene Studiensemester kein Studienplan gefunden wird, wird nochmal ohne Studiensemester gesucht
		$studienplan->searchStudienplaene($searchItems, $aktiv);
		
		$item['studienplan_id'] = '';
		$item['bezeichnung'] = 'Für ' . $studiensemester_kurzbz . ' ist kein gültiger Studienplan vorhanden';
		$item['status'] = '';
		$item['disabled'] = true;
		$result_obj[] = $item;
		foreach ($studienplan->result as $row)
		{
			$item['studienplan_id'] = html_entity_decode($row->studienplan_id);
			$item['bezeichnung'] = html_entity_decode($row->bezeichnung);
			$item['status'] = html_entity_decode($status_arr[$row->status_kurzbz]);
			$item['disabled'] = false;
			$result_obj[] = $item;
		}
	}
	elseif (count($studienplan->result) > 0)
	{
		foreach ($studienplan->result as $row)
		{
			$item['studienplan_id'] = html_entity_decode($row->studienplan_id);
			$item['bezeichnung'] = html_entity_decode($row->bezeichnung);
			$item['status'] = html_entity_decode($status_arr[$row->status_kurzbz]);
			$item['disabled'] = false;
			$result_obj[] = $item;
		}
	}
	else 
	{
		$item['studienplan_id'] = '';
		$item['bezeichnung'] = 'Kein passender Studienplan für "' . implode(', ', $searchItems) . '" gefunden';
		$item['status'] = '';
		$item['disabled'] = true;
		$result_obj[] = $item;
	}
	echo json_encode($result_obj);
	exit();
}

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'getStudienplan')
{
	$orgform_obj = new organisationsform();
	$orgform_obj->getAll();
	$orgform_arr = array();
	foreach ($orgform_obj->result as $row)
		$orgform_arr[$row->orgform_kurzbz] = $row->bezeichnung;
	
	$sprachen_obj = new sprache();
	$sprachen_obj->getAll();
	$sprachen_arr = array();
	
	foreach ($sprachen_obj->result as $row)
	{
		if (isset($row->bezeichnung_arr[$sprache]))
			$sprachen_arr[$row->sprache] = $row->bezeichnung_arr[$sprache];
		else
			$sprachen_arr[$row->sprache] = $row->sprache;
	}
	
	$studienplan_obj = new studienplan();
	
	if ($studienplan_obj->getStudienplaeneFromSem($_REQUEST['stg_kz'], $_REQUEST['studiensemester_kurzbz']))
	{
		$studienordnung_arr = array();
		$studienplan_arr = array();
		$data = array();
		
		foreach ($studienplan_obj->result as $row_sto)
		{
			$studienordnung_arr[$row_sto->studienordnung_id]['bezeichnung'] = $row_sto->bezeichnung_studienordnung;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['bezeichnung'] = $row_sto->bezeichnung_studienplan;
			
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['orgform_kurzbz'] = $row_sto->orgform_kurzbz;
			$studienplan_arr[$row_sto->studienordnung_id][$row_sto->studienplan_id]['sprache'] = $sprachen_arr[$row_sto->sprache];
		}
		$i = 0;
		foreach ($studienordnung_arr as $stoid => $row_sto)
		{
			$data[$i]['sto_bezeichnung'] = $db->convert_html_chars($row_sto['bezeichnung']);
			foreach ($studienplan_arr[$stoid] as $stpid => $row_stp)
			{
				$data[$i]['stpid'] = $stpid;
				$data[$i]['bezeichnung'] = $db->convert_html_chars($row_stp['bezeichnung']) . ' (' . $orgform_arr[$row_stp['orgform_kurzbz']] . ', ' . $row_stp['sprache'] . ')';
				$i ++;
			}
		}
		echo json_encode($data);
	}
	else
		echo $studienplan_obj->errormsg;
	exit();
}

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'ort_aktiv')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
		exit();
	
	$ort_auswahl = new ort();
	
	if ($ort_auswahl->filter($search, true))
	{
		$result_obj = array();
		foreach ($ort_auswahl->result as $row)
		{
			$item['ort_kurzbz'] = html_entity_decode($row->ort_kurzbz);
			$item['planbezeichnung'] = html_entity_decode($row->planbezeichnung);
			$item['bezeichnung'] = html_entity_decode($row->bezeichnung);
			$item['arbeitsplaetze'] = html_entity_decode($row->arbeitsplaetze);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit();
}
?>
