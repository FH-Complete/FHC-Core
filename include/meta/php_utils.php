<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/benutzerberechtigung.class.php');


function returnAJAX($success, $obj)
{
	//if there is an error
	if(error_get_last())
		$ret = array(
		"erfolg" => false,
		);
	else if(!$success)
	{
		$ret = array(
		"erfolg" => false,
		"message" => $obj,
		);
	}
	//if we dont have a valid user
	else if (!$getuid = get_uid())
	{
		$ret = array(
		"erfolg" => false,
		);
	}
	//if everything worked fine
	else
	{
		$ret = array(
		"erfolg" => true,
		"user" => $getuid,
		"info" => $obj,
		);
	}
	echo json_encode($ret);
	if($ret["erfolg"] === false)
		die("");
}
?>
