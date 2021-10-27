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
require_once('../../../include/datum.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/zeitsperre.class.php');


$sprache = getSprache();
$p = new phrasen($sprache);

if ((isset($_GET['uid'])) && (isset($_GET['day'])))
{
	$uid = $_GET['uid'];
	$day = $_GET['day'];

	$zs = new zeitsperre();
	$zs->getZeitsperrenForZeitaufzeichnung($uid, '180');
	$zeitsperren = $zs->result;

	if (array_key_exists($day, $zeitsperren))
	{
		echo '<span style="color:red"><b>'.$p->t('zeitaufzeichnung/zeitsperreVorhanden', [$day, $zeitsperren[$day]]).'</b></span><br>';
	}
	else
	{
		echo "";
	}
}
