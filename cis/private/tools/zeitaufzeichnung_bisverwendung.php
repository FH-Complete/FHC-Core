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
 * Checks, if there is the possibility for homeoffice for the given bisverwendung of
 * a certain date.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/vertragsbestandteil.class.php');

if ((isset($_GET['uid'])) && (isset($_GET['day'])))
{
	$uid = get_uid();

	//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeitaufzeichnung
	//des uebergebenen Users anzeigen
	if (isset($_GET['uid']) && $_GET['uid'] != $uid)
	{
		$p = new phrasen();
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($uid);

		if ($rechte->isBerechtigt('admin'))
		{
			$uid = $_GET['uid'];
		}
		else
		{
			die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
		}
	}

	$day = $_GET['day'];

    $vbt = new vertragsbestandteil();
    $homeoffice = $vbt->hasHomeoffice($uid, $day);

	echo json_encode($homeoffice);

}
