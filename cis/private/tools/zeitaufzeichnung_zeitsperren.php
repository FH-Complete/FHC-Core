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
 * Author: Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
/**
 * Checks, if there is a zeitsperre for a certain date. It should not be possible
 * to add a zeitaufzeichnung with a holiday (or else) entry on the same day.
 */


require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/zeitsperre.class.php');

header('Content-Type: application/json; charset=utf-8');
$sprache = getSprache();
$p = new phrasen($sprache);

if ( isset($_GET['day']) )
{
	$auth = new authentication();
	$uid = $auth->getUser();
	$day = $_GET['day'];

	$zs = new zeitsperre();
	$sperreVorhanden = false;
	$typ = '';
	$zs->getSperreByDate($uid, $day, null, zeitsperre::NUR_BLOCKIERENDE_ZEITSPERREN);
	$result_obj = array();
	$now = new DateTime($day);
	$now = $now->format('d.m.Y');

	foreach ($zs->result as $z)
	{
		if ($z->zeitsperretyp_kurzbz)
		{
			$item['typ'] = $z->zeitsperretyp_kurzbz;
			$item['day'] = $now;
			$item['sperreVorhanden'] = true;
			$result_obj[] = $item;
		}
	}
	echo json_encode($result_obj);
} else {
	http_response_code(500);
	echo json_encode(array(
		array(
			"error" => 'missing parameter day'
		)
	));
}
