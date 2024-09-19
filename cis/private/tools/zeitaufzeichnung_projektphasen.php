<?php
/* Copyright (C) 2021 Technikum-Wien
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
 * Authors: Oliver Hacker <hacker@technikum-wien.at>
 			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/projektphase.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/mitarbeiter.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$mitarbeiter = new mitarbeiter();
$mitarbeiter->getUntergebene($user, true);
$untergebenen_arr = array();
$untergebenen_arr = $mitarbeiter->untergebene;

//Wenn User Administrator ist und UID uebergeben wurde, dann die Phasen
//des uebergebenen Users anzeigen
if (isset($_GET['uid']) && $user != $_GET['uid'])
{
 	$rechte = new benutzerberechtigung();
 	$rechte->getBerechtigungen($user);

 	if ($rechte->isBerechtigt('admin') || (in_array($_GET['uid'], $untergebenen_arr)))
 	{
 		$user = $_GET['uid'];
 	}
	else
	{
		$p = new phrasen();
		die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
	}
}

$datum_obj = new datum();

if (isset($_GET['projekt_kurzbz']))
{
	$projekt_kurzbz = $_GET['projekt_kurzbz'];
	$projektphase = new projektphase();

	if($projektphase->getProjectphaseForMitarbeiterByKurzBz($user, $projekt_kurzbz))
		$projektphasen_user = $projektphase->result;
	else
		$projektphasen_user = array();

	$pp_user_ids = array();
	foreach ($projektphasen_user as $pp_user)
	{
		array_push($pp_user_ids, $pp_user->projektphase_id);
	}

	if ($projektphase->getProjektphasen($projekt_kurzbz))
	{
		$result_obj = array();
		foreach ($projektphase->result as $row)
		{
			if (in_array($row->projektphase_id, $pp_user_ids))
			{
				$item['projektphase_id'] = $row->projektphase_id;
				$item['bezeichnung'] = $row->bezeichnung;
				$item['start'] = $datum_obj->formatDatum($row->start, 'd.m.Y');
				$item['ende'] = $datum_obj->formatDatum($row->ende, 'd.m.Y');
				$item['zeitaufzeichnung_erlaubt'] = $row->zeitaufzeichnung;
				$result_obj[] = $item;
			}
		}
		echo json_encode($result_obj);
	}
	exit;
}
