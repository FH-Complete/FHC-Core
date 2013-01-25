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
/**
 * Dieses Script liefert die FreeBusy Informationen aus dem Sogo Kalender
 * 
 * Aufruf: http://www.example.com/cis/public/freebusy_sogo.php/[uid]
 * zB
 * http://www.example.com/cis/public/freebusy_sogo.php/oesi
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/zeitsperre.class.php');
require_once('../../include/ical.class.php');
require_once('../../include/stunde.class.php');

$uid = mb_substr($_SERVER['PATH_INFO'],1);

$bn = new benutzer();
if(!$bn->load($uid))
	die('User invalid');	
	
$curl = curl_init(SOGO_SERVER.'dav/'.$uid.'/freebusy.ifb');

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                         
curl_setopt($curl, CURLOPT_USERPWD, SOGO_USER.':'.SOGO_PASSWD);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);                    
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);                          
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);                           
curl_setopt($curl, CURLOPT_USERAGENT, 'FH Complete');

$response = curl_exec($curl);                                          
$resultStatus = curl_getinfo($curl);                                   

if($resultStatus['http_code'] == 200) 
{
	header("Content-Type: text/calendar; charset=UTF-8");
    echo $response;
} 
else 
{
    echo 'Call Failed '.print_r($resultStatus);                         
}

?>