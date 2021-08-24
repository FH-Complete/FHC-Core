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
require_once('../../../include/bisverwendung.class.php');


// if (isset($_GET['day']))
// {
// 	$day = $_GET['day'];
// 	echo " es ist ein schöner Tag: " . $day;
// 	echo "<br> bisid: ";
//
//
// }

// if (isset($_GET['uid'])){
// 	$uid = $_GET['uid'];
// 	$verwendung = new bisverwendung();
// 	$verwendung->getLastAktVerwendung($uid);
// 	echo $bvId = $verwendung->bisverwendung_id;
// }

if ((isset($_GET['uid'])) && (isset($_GET['day']))) {

	$uid = $_GET['uid'];
	//$day = $_GET['day'];

	//$day = '2021-08-15';
	$day = "2021-08-15";


	$verwendung = new bisverwendung();
	$verwendung->getLastAktVerwendung($uid); //haut hin
	//$verwendung->getVerwendungDatum($uid, $day); //hier kommt nix
	//$verwendung ->getVerwendungDatumMonat($uid, $day);
	//
	echo $bvId = $verwendung->bisverwendung_id;
	echo "check " . $uid . " on day " . $day;

}
