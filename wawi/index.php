<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/wawi.config.inc.php');
require_once('auth.php');

$menu = isset($_GET['menu'])?$_GET['menu']:'menu.php';
$content = isset($_GET['content'])?$_GET['content']:'home.php';

//Vorhandene Parameter an Content dazuhaengen
$first=true;
foreach($_GET as $name=>$param)
{
	if($name!='menu' && $name!='content')
	{
		if($first)
		{
			$content.="?$name=$param";
			$first=false;
		}
		else
			$content.="&$name=$param";
	}
}
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>WaWi - Warenwirtschaft</title>
</head>
	<frameset cols="200,*" framespacing="1" border="1">
	  <frame src="'.$menu.'" name="menu" frameborder="0"/>
	  <frame src="'.$content.'" name="content" frameborder="0"/>
	  <noframes>
	    <body>
	      <h1>Error</h1>
	      <p>Ihr Browser unterstuetzt leider keine Frames</p>
	    </body>
	  </noframes>
	</frameset>
</html>
';
?>