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
require_once ('../../include/berechtigung.class.php');
require_once ('../../include/organisationseinheit.class.php');
require_once ('../../include/sprache.class.php');
require_once ('../../include/wawi_kostenstelle.class.php');

if (! $db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$sprache = getSprache();

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'benutzer')
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

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'berechtigung')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
		exit();

	$berechtigung = new berechtigung();

	if ($berechtigung->searchBerechtigungen($search))
	{
		$result_obj = array();
		foreach ($berechtigung->result as $row)
		{
			$item['berechtigung_kurzbz'] = html_entity_decode($row->berechtigung_kurzbz);
			$item['beschreibung'] = html_entity_decode($row->beschreibung);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit();
}

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'oe_kurzbz')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
		exit();

	$search = array($search);
	$oe = new organisationseinheit();
	$oe->search($search);

	$stg = new studiengang();
	$stg->search($search);
	foreach($stg->result as $row)
	{
		if($row->aktiv===true)
			$oe->result[] = new organisationseinheit($row->oe_kurzbz);
	}

	if(is_array($oe->result) && count($oe->result) > 0)
	{
		$resultArray = array();
		foreach($oe->result as $row)
		{
			if($row->aktiv==true)
			{
				$resultArray[html_entity_decode($row->oe_kurzbz)] = array('organisationseinheittyp_kurzbz' => html_entity_decode($row->organisationseinheittyp_kurzbz),'bezeichnung' => html_entity_decode($row->bezeichnung));
			}
		}

		uasort($resultArray, function($a, $b)
		{
		    return $a['organisationseinheittyp_kurzbz'].$a['bezeichnung'] <=> $b['organisationseinheittyp_kurzbz'].$b['bezeichnung'];
		});

		$result_obj = array();
		foreach($resultArray as $key => $value)
		{
				$item['oe_kurzbz'] = $key;
				$item['organisationseinheittyp_kurzbz'] = $value['organisationseinheittyp_kurzbz'];
				$item['bezeichnung'] = $value['bezeichnung'];
				$result_obj[] = $item;
		}

		echo json_encode($result_obj);
	}
	exit();
}

if (isset($_REQUEST['autocomplete']) && $_REQUEST['autocomplete'] == 'kostenstelle')
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
		exit();

	$kst = new wawi_kostenstelle();

	if ($kst->getAll($search))
	{
		$result_obj = array();
		foreach ($kst->result as $row)
		{
			$item['kostenstelle_id'] = html_entity_decode($row->kostenstelle_id);
			$item['bezeichnung'] = html_entity_decode($row->bezeichnung);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit();
}

?>
