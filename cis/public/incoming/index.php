<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once '../../../config/cis.config.inc.php';

?>

<html>
	<head>
		<title>Inoming-Verwaltung</title>
	</head>
	<body>
		<form action ="abc" method="POST">
		<table border ="0" width ="100%" height="40%">
			<tr >
				<td align="center" valign="bottom"> <img src="../../../skin/images/tw_logo_02.jpg"></td>
			</tr>
		</table>
		<table border ="0" width ="100%">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center"><a href="registration.php">Registration</a></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center"><input type="text" size="30" value="UserID" onfocus="this.value='';"></td>
			</tr>
			<tr>
				<td align="center"><input type="password" size="30" value="UserID" onfocus="this.value='';"></td>
			</tr>
			<tr>
				<td align="center"><input type="submit" value="Login"></td>
			</tr>
		</table>
		</form>
	
	</body>

</html>