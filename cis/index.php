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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('config.inc.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>CIS - <?php echo CAMPUS_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<table class="tabcontent">
	  <tr>
	    <td width="170" class="tdwrap">
			<a href="index.html" target="_top">
				<div class="home_logo">&nbsp;</div>
			</a>
	    </td>
        <td id="header">
    	    	<div class="header_line" ></div>
        </td>
 	</tr>
   	 <tr>
			<td id="footer">&nbsp;</td>
    	    <td><?php require_once('../include/'.EXT_FKT_PATH.'/cis_menu_global.inc.php'); 	?></td>
	</tr>
</table>
<iframe id="menue" src="menu.php" name="menu" frameborder="0">
	No iFrames
</iframe>
<iframe id="content" src="public/news.php" name="content" frameborder="0">
	No iFrames
</iframe>
</body>
<!--<frameset rows="77,*,1" cols="*" frameborder="NO" border="0" framespacing="0">
	<frame src="topbar.html" name="topbar" scrolling="NO" noresize>
	<frameset rows="*" cols="200,*" framespacing="0" frameborder="NO" border="0">
		<frame src="menu.html" name="menu" scrolling="AUTO" noresize>
    	<frame src="public/news.php" name="content">
  	</frameset>
	<noframes>
		<body>
		<p>Diese Seite verwendet Frames. Frames werden von Ihrem Browser aber nicht	unterstützt.</p>
		</body>
	</noframes>
</frameset> -->
</html>
