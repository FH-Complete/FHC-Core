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
	if((!isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='off'))
		&& strstr(APP_ROOT,'https')!==false)
	{
		header('Location: '.APP_ROOT.'cis/index_login.php?login=1');
		exit;
	}
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
	<link rel="stylesheet" href="../skin/jquery.css" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="rand"></td>
		<td class="boxshadow">
		<table cellspacing="0" cellpadding="0" class="header">
			<tr>
				<td align="center" valign="middle"  class="headerbar">
					<div class="header_logo"></div>
					<div class="cis_logo"></div>
					<br><br><br>
					<br>
					<br>
					<form action="index_login.php?login=1" method="POST">
					<input class="cis_login" type="submit" value="Login">
					</form>
					</center>
					</td>
				</tr>
				<tr style="height:10%;" >
					<td align="center" valign="bottom">
						<div style="color:grey">Powered by <a href="http://www.fhcomplete.info" target="blank">FH Complete</a></div>
						<br><br>
					</td>
				</tr>
			</table>
		</td>
		<td class="rand"></td>
	</tr>
</table>
</body>
</html>
