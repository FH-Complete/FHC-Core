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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/sprache.class.php');

if(isset($_GET['sprache']))
{
	$sprache = new sprache();
	if($sprache->load($_GET['sprache']))
		setSprache($_GET['sprache']);
	else
		die('Sprache invalid');
}
if(isset($_GET['content_id']))
	$id = $_GET['content_id'];
else
	$id = 28;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>CIS - <?php echo CAMPUS_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<table class="tabcontent">
	 
	<tr>
	    <td width="170" class="tdwrap" onclick="self.location.href='indextest.php'">
			<div class="home_logo">&nbsp;</div>
	    </td>
        <td id="header" width="100%">
    	    	<div class="header_line" ></div>
        </td>
		<td nowrap class="tdwrap">
			<div style="font-size: 10px;"><i>Powered by <a href="http://fhcomplete.technikum-wien.at/" target="blank">FH Complete 2.0</a></i></div>
		</td>
	</tr>

   	 <tr>
   	 <td></td>
   	 <td align="right">
   	 <?php
				$sprache = new sprache();
				$sprache->getAll(true);
				foreach($sprache->result as $row)
				{
					echo ' <a href="indextest.php?sprache='.$row->sprache.'&content_id='.$id.'" title="'.$row->sprache.'"><img src="../cms/image.php?src=flag&sprache='.$row->sprache.'" alt="'.$row->sprache.'"></a>';
				}
				?>
				</td>
   	    <td nowrap><?php require_once('../include/'.EXT_FKT_PATH.'/cis_menu_global.inc.php'); 	?></td>
	</tr>
</table>
<iframe id="menue" src="menutest.php?content_id=<?php echo $id; ?>" name="menu" frameborder="0">
	No iFrames
</iframe>
<!-- <iframe id="content" src="public/news.php" name="content" frameborder="0">  -->
<iframe id="content" src="public/news.php" name="content" frameborder="0">
	No iFrames
</iframe>
</body>
</html>
