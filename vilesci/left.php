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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/**
 * Menue fuer Vilesci-Seite
 * Die Menuepunkt mit den zugehoerigen Links befinden sich in einem
 * Array welches includiert wird.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/'.EXT_FKT_PATH.'/vilesci_menu_main.inc.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');


$berechtigung=new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>VileSci Men&uuml;</title>
	<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/javascript">
	<!--
		function js_toggle_container(conid)
   		{
   			try
   			{
				if (document.getElementById(conid).style.display=='none')
				{
					document.getElementById(conid).style.display='block';
					document.getElementById(conid+'_dot').innerHTML='<img src="../skin/images/page_green.png" alt="page close" border="0">&nbsp;';
				}
	        	else
	        	{
					document.getElementById(conid).style.display='none';
					document.getElementById(conid+'_dot').innerHTML='<img src="../skin/images/page_go.png" alt="page go" border="0">&nbsp;';
				}
   			}
   			catch(e){alert(e)}
   			return false;
  		}
	//-->
	</script>
</head>



<body class="left_nav">
<!--
<div class="logo" style="background-color:#FFFFFF;"  onclick="self.location.href='index.php'">
		<img border="0" src="../skin/images/vilesci_logo.png" alt="VileSci (FASonline)" width="239px" title="VileSci" >
</div>
-->

<?php
if(isset($_GET['categorie']))
{
	$categorie=$_GET['categorie'];

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
	/*
	if ($berechtigung->isBerechtigt('admin'))
	{


		echo '
		<div class="logo">
			<div>
				<a href="admin/menu.html" target="main"><img src="../skin/images/application_go.png" alt="go" border="0">&nbsp;Admin</a>
			</div>
			<div>
				<a href="https://sdtools.technikum-wien.at" target="main"><img src="../skin/images/application_go.png" alt="go" border="0">&nbsp;SDTools</a>
			</div>
		</div>
			';


	}*/
	$menu = $menu[$categorie];
	echo '<h2>'.$menu['name'].'</h2>';
	$umbruch=true;

	foreach($menu AS $m1)
	{
		if (is_array($m1) && isset($m1['name']))
		{
			if (isset($m1['permissions']) && !checkpermission($m1['permissions']))
				continue;

			if (isset($m1['link']))
				echo '<a href="'.$m1['link'].'" ';
			if (isset($m1['target']))
				echo 'target="'.$m1['target'].'" ';
			if (isset($m1['link']))
				echo '>';

			if (isset($m1['name']) && !isset($m1['link']) )
			{
				if($umbruch)
					echo '<br />';
				echo '<strong style="font-size: smaller;">'.$m1['name'].'</strong><br />';
			}
			else
				echo $m1['name'];

			if (isset($m1['link']))
				echo '</a>';

			$umbruch=true;
			foreach($m1 AS $m2)
			{
				if (is_array($m2)  && isset($m2['name']))
				{
					if (isset($m2['permissions']) && !checkpermission($m2['permissions']))
						continue;
					if (isset($m2['link']))
						echo "\n\t\t".'<a href="'.$m2['link'].'" ';
					if (isset($m2['target']))
						echo 'target="'.$m2['target'].'" ';
					if (isset($m2['link']))
						echo '><img title="'.$m2['name'].'" src="../skin/images/square_blue.png" alt="page go" border="0">&nbsp;';
					if (isset($m2['name']))
						echo $m2['name'];
					if (isset($m2['link']))
						echo '</a><br>';
					$umbruch=false;
				}
			}


				echo '<br>';
		}
	}
}
?>
<!--
<hr>
<a href="index.html" target="_top"><img title="'.$m2['name'].'" src="../skin/images/application_home.png" alt="page go" border="0">&nbsp;Home</a>
-->
</body>
</html>
