<?php
/* Copyright (C) 2018 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Holt die Ergebnisse des Matrikelnummer Clearings des Datenverbundes
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin', null, 'suid'))
	die($rechte->errormsg);

$be = DVB_BILDUNGSEINRICHTUNG_CODE;
$username = DVB_USERNAME;
$passwort = DVB_PASSWORD;
$meldedatum = '20180415';


// OAuth Token holen
$curl = curl_init();
$url = DVB_PORTAL.'/dvb/oauth/token?grant_type=client_credentials';

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$headers = array(
	'Accept: application/json',
	'Content-Type: application/x-www-form-urlencoded',
	'Authorization: Basic '.base64_encode($username.":".$passwort),
	'User-Agent: FHComplete',
	'Connection: Keep-Alive',
	'Expect:',
	'Content-Length: 0'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$json_response = curl_exec($curl);
$curl_info = curl_getinfo($curl);
curl_close($curl);

if ($curl_info['http_code'] == '200')
{
	$authentication = json_decode($json_response);
	$token = $authentication->access_token;
}
else
{
	echo 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$json_response;
	exit;
}

// Clearing Daten holen
$curl = curl_init();

$url = DVB_PORTAL.'/rws/clearing/0.2/clearing-upload.xml';
$url .= '?be='.$be.'&meldedatum='.$meldedatum;

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$headers = array(
	'Accept: application/json',
	'Authorization: Bearer '.$token,
	'User-Agent: FHComplete',
	'Connection: Keep-Alive',
	'Expect:',
	'Content-Length: 0'
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($curl);
$curl_info = curl_getinfo($curl);
curl_close($curl);

if ($curl_info['http_code'] == '200')
{
	header("Content-type: application/xhtml+xml");
	echo $response;
}
else
{
	echo 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
	return false;
}
