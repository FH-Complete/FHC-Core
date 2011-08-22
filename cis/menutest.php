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
<script language="JavaScript" type="text/javascript">
<!--
	var __js_page_array = new Array();
    function js_toggle_container(conid)
    {
		if (document.getElementById)
		{
        	var block = "table-row";
			if (navigator.appName.indexOf('Microsoft') > -1)
				block = 'block';
				
			// Aktueller Anzeigemode ermitteln	
            var status = __js_page_array[conid];
            if (status == null)
			{
		 		if (document.getElementById && document.getElementById(conid)) 
				{  
					status=document.getElementById(conid).style.display;
				} else if (document.all && document.all[conid]) {      
					status=document.all[conid].style.display;
		      	} else if (document.layers && document.layers[conid]) {                          
				 	status=document.layers[conid].style.display;
		        }							
			}	
			
			// Anzeigen oder Ausblenden
            if (status == 'none')
            {
		 		if (document.getElementById && document.getElementById(conid)) 
				{  
					document.getElementById(conid).style.display = 'block';
				} else if (document.all && document.all[conid]) {      
					document.all[conid].style.display='block';
		      	} else if (document.layers && document.layers[conid]) {                          
				 	document.layers[conid].style.display='block';
		        }				
            	__js_page_array[conid] = 'block';
            }
            else
            {
		 		if (document.getElementById && document.getElementById(conid)) 
				{  
					document.getElementById(conid).style.display = 'none';
				} else if (document.all && document.all[conid]) {      
					document.all[conid].style.display='none';
		      	} else if (document.layers && document.layers[conid]) {                          
				 	document.layers[conid].style.display='none';
		        }				
            	__js_page_array[conid] = 'none';
            }
            return false;
     	}
     	else
     		return true;
  	}
//-->
</script>
</head>
<body style="margin:0; padding:0">
<table class="menue">
	<tr>
		<td>&nbsp;</td>
	</tr>
<?php
	//TODO: ins config
	define('CIS_MENU_ENTRY_CONTENT',28);
	
	if(isset($_GET['content_id']) && $_GET['content_id']!='')
		$content_id=$_GET['content_id'];
	else
		$content_id=CIS_MENU_ENTRY_CONTENT;
	
	if($content_id!=CIS_MENU_ENTRY_CONTENT)
	{
		echo '<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td><a class="HyperItem" href="?content_id='.CIS_MENU_ENTRY_CONTENT.'">&lt;&lt; '.$p->t('lvplan/home').'</a></td>
				</tr>
				<tr><td></td></tr>';
	}
	require_once('../cms/menu.inc.php');
	drawSubmenu($content_id);
	
	//Gepufferten Output ausgeben
	ob_end_flush();
?>
</table>

</body>
</html>