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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 */

/**
 * Menue fuer Vilesci-Seite
 * Die Menuepunkt mit den zugehoerigen Links befinden sich in einem
 * Array welches includiert wird.
 */
	require('config.inc.php');
 	require('../include/functions.inc.php');
 	require('../include/benutzerberechtigung.class.php');
 	require_once('../include/'.EXT_FKT_PATH.'/vilesci_menu_main.inc.php');
 	$uid=get_uid();
 	$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
	$berechtigung=new benutzerberechtigung($conn);
	$berechtigung->getBerechtigungen($uid);
	if (!($berechtigung->isBerechtigt('admin') || 
		  $berechtigung->isBerechtigt('support') || 
		  $berechtigung->isBerechtigt('preinteressent') || 
		  $berechtigung->isBerechtigt('lehre') || 
		  $berechtigung->isBerechtigt('lv-plan') ))
		die ('Keine Berechtigung!');

	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>VileSci Men&uuml;</title>
	<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">
	<style type="text/css">
	</style>

	<script language="JavaScript" type="text/javascript">
	<!--
		function js_toggle_container(conid)
   		{
   			try
   			{
				if (document.getElementById(conid).style.display=='none')
				{
					document.getElementById(conid).style.display='block';
					document.getElementById(conid+'_dot').innerHTML='&#8211; ';
				}
	        	else
	        	{
					document.getElementById(conid).style.display='none';
					document.getElementById(conid+'_dot').innerHTML='+ ';
				}
   			}
   			catch(e){alert(e)}
   			return false;
  		}
	//-->
	</script>
</head>

<body style="background-color:#eeeeee;">
<div class="logo">
	<a href="intro.php" target="detail">
		<img src="../skin/images/logo.png" width="200" height="50" alt="VileSci (FASonline)" title="VileSci" />
	</a>
</div>

<?php
function checkpermission($permissions)
{
	global $berechtigung;
	
	$permission=false;
	foreach ($permissions as $perm)
	{
		if($berechtigung->isBerechtigt($perm))
		{
			$permission=true;
		}
	}
	return $permission;
}

if ($berechtigung->isBerechtigt('admin'))
{
	echo '<div>
			<a href="admin/menu.html" target="main">Admin</a>
		</div>
		<div>
			<a href="https://sdtools.technikum-wien.at" target="main">SDTools</a>
		</div><hr>';
}
foreach($menu AS $m)
{
	$opener=false;
	$hide=false;
	if (isset($m['opener']))
		if ($m['opener']=='true')
			$opener=true;
	if (isset($m['hide']))
		if ($m['hide']=='true')
			$hide=true;

	if (isset($m['permissions']) && !checkpermission($m['permissions']))
		continue;
	
	if ($opener)
	{
		echo '<SPAN style="cursor: pointer;" id="'.$m['name'].'_dot" onclick="js_toggle_container('."'".$m['name']."'".')" style="font-weight:bold">';
		if ($hide)
			echo '+ ';
		else
			echo '&#8211; ';
		echo '</SPAN>';
	}
	else
		echo '&curren; ';

	if (isset($m['link']))
		echo '<a href="'.$m['link'].'" ';
	if (isset($m['target']))
		echo 'target="'.$m['target'].'" ';
	if (isset($m['link']))
		echo '>';
	if (isset($m['name']))
		echo '<u><strong>'.$m['name'].'</strong></u>';
	if (isset($m['link']))
		echo '</a>';
	if ($hide)
		$display='none';
	else
		$display='block';
	echo "\n<DIV>\n".'<SPAN id="'.$m['name'].'" style="display:'.$display.'">';
	foreach($m AS $m1)
		if (is_array($m1) && isset($m1['name']))
		{
			$opener=false;
			$hide=false;
			if (isset($m1['opener']))
				if ($m1['opener']=='true')
					$opener=true;
			if (isset($m1['hide']))
				if ($m1['hide']=='true')
					$hide=true;
					
			if (isset($m1['permissions']) && !checkpermission($m1['permissions']))
				continue;
				
			if ($opener)
			{
				echo "\n\t".'<SPAN style="cursor: pointer;" onclick="js_toggle_container('."'".$m1['name']."'".')">';
				if ($hide)
					echo '+ ';
				else
					echo '&#8211; ';
				echo "\n\t\t</SPAN>";
			}
			else
				echo "\t &nbsp;&nbsp;&nbsp;&nbsp;&middot; ";

			if (isset($m1['link']))
				echo '<a href="'.$m1['link'].'" ';
			if (isset($m1['target']))
				echo 'target="'.$m1['target'].'" ';
			if (isset($m1['link']))
				echo '>';
			if (isset($m1['name']))
				echo '<strong>'.$m1['name'].'</strong>';
			if (isset($m1['link']))
				echo '</a>';
			if ($hide)
				$display='none';
			else
				$display='block';
						
			echo "\n\t<DIV>\n\t".'<SPAN id="'.$m1['name'].'" style="display:'.$display.'">';
			foreach($m1 AS $m2)
				if (is_array($m2)  && isset($m2['name']))
				{
					if (isset($m2['permissions']) && !checkpermission($m2['permissions']))
						continue;
					if (isset($m2['link']))
						echo "\n\t\t".'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&middot; <a href="'.$m2['link'].'" ';
					if (isset($m2['target']))
						echo 'target="'.$m2['target'].'" ';
					if (isset($m2['link']))
						echo '>';
					if (isset($m2['name']))
						echo $m2['name'];
					if (isset($m2['link']))
						echo '</a><BR />';
				}
			echo "\n\t</SPAN>\n\t</DIV>\n";
		}
	echo "\n</SPAN>\n</DIV>\n";
}

?>
</body>
</html>
