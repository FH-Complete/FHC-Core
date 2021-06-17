<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Seite zur Wartung der Infoscreens
 */
require_once('../../config/vilesci.config.inc.php');		
require_once('../../include/infoscreen.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$user = get_uid();
	
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
	
if(!$rechte->isBerechtigt('basis/infoscreen'))
	die('Sie haben keine Berechtigung fuer diese Seite');
	
$datum_obj = new datum();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Infoscreen - Preview</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">		
</head>
<body>
<h1>Infoscreen - Preview</h1>
<?php
	$infoscreen = new infoscreen();
	$infoscreen->getAll();

	foreach($infoscreen->result as $row)
	{
		echo '
		<div style="float: left">
			<h2><center>',$row->bezeichnung,'</center></h2>
			<iframe style="width:500px; height:400px" src="../../cis/infoterminal/informationsbildschirm.php?ipadresse=',$row->ipadresse,'"></iframe>
		</div>';
	}
?>
 
</body>
</html>
