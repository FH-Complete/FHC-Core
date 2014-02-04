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
require_once('../cms/menu.inc.php');
require_once('../include/phrasen.class.php');
$sprache = getSprache();
$p = new phrasen($sprache);
//Output Buffering aktivieren
//Falls eine Authentifizierung benoetigt wird, muss ein Header
//gesendet werden. Dies funktioniert nur, wenn vorher nicht ausgegeben wurde
ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Menu</title>
<script type="text/javascript" src="../include/js/flexcroll.js"></script>
<link href="../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../include/js/jquery.js"></script>
<script type="text/javascript">
function treemenu(obj) 
{
	if (!obj.length) return;
	obj.find("ul.menu").each(function() 
	{
		if(!$(this).parent().find("a:first").hasClass("selected"))
		{
//			$(this).children(".menublock").each(function(){alert("a"+$(this).html())});

	//		if(!$(this).children(".menublock"))
				$(this).css("display", "none");
		}
	});

	$("li:not(:has(ul))").find("a").addClass("leaf");
	
	obj.find("a").click(function(e) 
	{
		//e.preventDefault();
		if($(this).hasClass("selected"))
		{
			$(this).removeClass("selected");
			$(this).parent().find("ul.menu:first").slideUp(400);
		}
		else
		{
			$(this).parent().siblings().each(function()
			{
				$(this).find("a:first").removeClass("selected");
				$(this).find("ul.menu:first").slideUp(400);
			});
			$(this).parent().find("ul.menu:first").slideDown(400);
			if (!$(this).hasClass("leaf")) 
			{
				$(this).addClass("selected");
			}
		}
		//window.setTimeout(function(){fleXenv.updateScrollBars();},500);
	});
}
$(document).ready(function() 
{
	treemenu($("#menu"));
});
	
</script>

</head>
<body style="margin:0; padding:0">
<div class="flexcroll">
<?php
		
	if(isset($_GET['content_id']) && $_GET['content_id']!='')
		$content_id=$_GET['content_id'];
	else
		$content_id=CIS_MENU_ENTRY_CONTENT;
	
	echo '<ul id="menu">';
	if($content_id!=CIS_MENU_ENTRY_CONTENT)
	{
		echo '<li><a href="?content_id='.CIS_MENU_ENTRY_CONTENT.'">&lt;&lt; '.$p->t('global/zurueck').'</a><br></li>';
	}
	require_once('../cms/menu.inc.php');
	drawSubmenu($content_id);
	
	//Gepufferten Output ausgeben
	ob_end_flush();
	echo '</ul>';
?>
</div>
</body>
</html>
