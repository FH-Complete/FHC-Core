<?php 
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */

if(isset($_GET['search']))
	$search = $_GET['search'];
else 
	$search = '';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html lang="de_AT">

<head>
	<title>Betriebsmittel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
</head>

<frameset rows="400,*">
  	<frame src="betriebsmittel.php<?php echo ($search!=''?"?search=$search":"");?>" id="uebersicht" name="uebersicht" frameborder="0" />
  	<frame src="" id="detail" name="detail" frameborder="0" /><!-- betriebsmitteldetail.php -->
	<noframes>
		<body bgcolor="#FFFFFF">
			This application works only with a frames-enabled browser.<br />
			<a href="main.php">Use without frames</a>
		</body>
	</noframes>
</frameset>

</html>