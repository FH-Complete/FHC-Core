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
ob_start();
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
	$id = '';
	
if(isset($_GET['menu']))
	$menu = $_GET['menu'];
else
	$menu = 'menu.php?content_id='.$id;
	
if(isset($_GET['content']))
	$content = $_GET['content'];
else
	$content = '../cms/news.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>CIS - <?php echo CAMPUS_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../skin/jquery.css" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
	<script src="../include/js/jquery.js" type="text/javascript"></script>
</head>
<script type="text/javascript">
function changeSprache(sprache)
{
	var menu = '';
	var content = '';
	menu = document.getElementById('menue').contentWindow.location.href;
	content = document.getElementById('content').contentWindow.location.href;
	menu = escape(menu);
	content = escape(content);
	
	window.location.href="index.php?sprache="+sprache+"&content_id=<?php echo $id;?>&menu="+menu+"&content="+content;
}
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}
function loadampel()
{
	$('#ampel').load('ampel.php?'+gettimestamp());
}
</script>
<body style="margin-top:0; padding-top:0" onload="loadampel()">
<table class="tabcontent">
	 <tr>
	 	<td></td>
	 	<td width="100%" ></td>
	 	<td width="100%" ></td>
	 	<td></td>
	 </tr>
	<tr>
	    <td width="170" class="tdwrap" onclick="self.location.href='index.php'">
			<div class="home_logo">&nbsp;</div>
	    </td>
        <td id="header" colspan="2" valign="top">
    	    <div class="header_line" ></div>	
        </td>
        <td nowrap>
        	<div style="font-size: 10px; text-align: right">
        		<i>Powered by <a href="http://fhcomplete.technikum-wien.at/" target="blank">FH Complete 2.0</a></i>        		
        	</div>
        </td>
        
	</tr>
	<tr>
		<td></td>
   	 	<td align="right" nowrap  colspan="2">
   	 		<span id="ampel"></span>
   	 		<?php require_once('../include/'.EXT_FKT_PATH.'/cis_menu_global.inc.php'); 	?>
   	 		    <?php
  
				$sprache = new sprache();
				$sprache->getAll(true);
				foreach($sprache->result as $row)
				{
					echo ' <a href="#'.$row->sprache.'" title="'.$row->sprache.'" onclick="changeSprache(\''.$row->sprache.'\'); return false;"><img src="../cms/image.php?src=flag&sprache='.$row->sprache.'" alt="'.$row->sprache.'"></a>';
				}
			?>
   	 	</td>
   	    <td align="right" nowrap>
			<form name="searchform" action="private/tools/suche.php" method="GET" target="content" style="display:inline">
        	<input type="search" size="10" name="search" placeholder="Suchbegriff ..."/>
        	<img src="../skin/images/search.png" height="14px" onclick="document.searchform.submit()" class="suchicon"/>
        </form>
   	    </td>
	</tr>
</table>
<iframe id="menue" src="<?php echo $menu; ?>" name="menu" frameborder="0">
	No iFrames
</iframe>
<iframe id="content" src="<?php echo $content; ?>" name="content" frameborder="0">
	No iFrames
</iframe>
</body>
</html>
<?php
ob_end_flush();
?>
