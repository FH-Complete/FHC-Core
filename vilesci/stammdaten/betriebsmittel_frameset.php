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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html lang="de_AT">
<head>
	<title>VileSci-Betriebsmittel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
</head>

<frameset rows="40%,*">';

if(isset($_GET['searchstr']))
	$searchstr=$_GET['searchstr'];
else
	$searchstr='';

echo '	<frame src="betriebsmittel_uebersicht.php?searchstr='.$db->convert_html_chars($searchstr).'" id="betriebsmittel_uebersicht" name="betriebsmittel_uebersicht" frameborder="0" />';
echo '
  	<frame src="betriebsmittel_details.php" id="betriebsmittel_details" name="betriebsmittel_details" frameborder="0" />
	<noframes>
		<body bgcolor="#FFFFFF">
			This application works only with a frames-enabled browser.<br />
			<a href="main.php">Use without frames</a>
		</body>
	</noframes>
</frameset>

</html>';
?>