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
require_once('../../../include/functions.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/coodle.class.php');

$user = get_uid();

if(!isset($_POST['work']))
	die('Parameter Work missing');

$work = $_POST['work'];

switch($work)
{
	case 'addressource':	
		if(isset($_POST['coodle_id']))
			$coodle_id=$_POST['coodle_id'];
		else
			die('CoodleID Missing');

		if(isset($_POST['id']))
			$id=$_POST['id'];
		else
			die('RessourceID Missing');
		if(isset($_POST['typ']))
			$typ=$_POST['typ'];
		else
			die('Typ Missing');
		
		$coodle = new coodle();
		if(!$coodle->load($coodle_id))
			die('Fehler: '.$coodle->errormsg);
	
		if($coodle->ersteller_uid!=$user)
			die('Diese Aktion ist nur durch den Ersteller der Umfrage möglich');

		$uid='';
		$ort='';
		$email='';
		$name='';
		switch($typ)
		{
			case 'Ort': $ort = $id; break;
			case 'Person': $uid = $id; break;
			case 'Extern': $email = $id; $name=''; break; // TODO Name setzen
			default: die('Ungueltiger Typ'); break;
		}		

		if($coodle->RessourceExists($coodle_id, $uid, $ort, $email))
			die('Ressource ist bereits zugeteilt');

		$coodle->coodle_id = $coodle_id;
		$coodle->uid = $uid;
		$coodle->ort_kurzbz = $ort;
		$coodle->email = $email;
		$coodle->name = $name;
		$coodle->zugangscode = ''; //TODO
		$coodle->insertamum = date('Y-m-d H:i:s');
		$coodle->insertvon = $user;
		$coodle->updateamum = date('Y-m-d H:i:s');
		$coodle->updatevon = $user;

		if($coodle->saveRessource(true))
			echo 'true';
		else
			echo 'Fehler beim Speichern:'.$coodle->errormsg;

		break;
	case 'removeressource':
		if(isset($_POST['coodle_id']))
			$coodle_id=$_POST['coodle_id'];
		else
			die('CoodleID Missing');

		if(isset($_POST['id']))
			$id=$_POST['id'];
		else
			die('RessourceID Missing');
		if(isset($_POST['typ']))
			$typ=$_POST['typ'];
		else
			die('Typ Missing');
		
		$coodle = new coodle();
		if(!$coodle->load($coodle_id))
			die('Fehler: '.$coodle->errormsg);
	
		if($coodle->ersteller_uid!=$user)
			die('Diese Aktion ist nur durch den Ersteller der Umfrage möglich');

		$uid='';
		$ort='';
		$email='';
		$name='';
		switch($typ)
		{
			case 'Ort': $ort = $id; break;
			case 'Person': $uid = $id; break;
			case 'Extern': $email = $id; break;
			default: die('Ungueltiger Typ'); break;
		}
		if($coodle_ressource_id = $coodle->RessourceExists($coodle_id, $uid, $ort, $email))
		{
			if($coodle->deleteRessource($coodle_ressource_id))
				echo 'true';
			else
				echo 'Fehler:'.$coodle->errormsg;
		}
		else
		{
			echo 'Ressource nicht gefunden';
		}
		break;
	default:
			die('Invalid Work Parameter');
}
?>
