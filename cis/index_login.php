<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 *
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');

if(isset($_GET['login']))
{
	$uid = get_uid();

	if($uid!='')
	{
		header('Location: '.APP_ROOT.'cis/index.php');
	}
}

?>
<html>
<head>
<title>CIS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="rand"></td>
		<td class="boxshadow">
		<br><br><br><br><br>
		<center>
		<img src="../skin/images/logo.jpg" width="400px" />
		<br><br><br>
		Herzlich Willkommen am Campus Informationssystem der Fachhochschule Technikum Wien
		<br>
		<br>
		<form action="index_login.php?login=1" method="POST">
		<input type="submit" value="Login">
		</form>
		</center>
		</td>
		<td class="rand"></td>
	</tr>
</table>
</body>
</html>
