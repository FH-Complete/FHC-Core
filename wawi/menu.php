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
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/'.EXT_FKT_PATH.'/wawi_menu_main.inc.php');

$user_original = get_original_uid();

$berechtigung_orig = new benutzerberechtigung();
$berechtigung_orig->getBerechtigungen($user_original);

if(isset($_GET['loginasuser']) && $berechtigung_orig->isBerechtigt('system/loginasuser'))
{
	login_as_user($_GET['uid']);
}

$user = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);


echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>WaWi Menue</title>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css">
</head>

<body class="menue">
<h3><a href="home.php" target="content">WaWi Home</a></h3>
<a href="WaWi.pdf" target="_blank"><img src="../skin/images/pdfpic.gif" /> Handbuch</a>
<hr>';

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

//Menue generieren aus wawi_menu_main.inc.php
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
			echo '<strong>'.$m1['name'].'</strong><br />';
		}
		else
			echo $m1['name'];
			
		if (isset($m1['link']))
			echo '</a>';
		echo '<br />';
		$umbruch=true;
		foreach($m1 AS $m2)
		{
			if (is_array($m2)  && isset($m2['name']))
			{
				if (isset($m2['permissions']) && !checkpermission($m2['permissions']))
					continue;
				
				if($m2['name']!='')
				{
					echo "\n\t\t".'<img title="'.$m2['name'].'" src="../skin/images/bullet_arrow_down.png" alt="page go" border="0">&nbsp;';
					if (isset($m2['link']))
						echo '<a href="'.$m2['link'].'" ';
					if (isset($m2['target']))
						echo 'target="'.$m2['target'].'" ';
					if (isset($m2['link']))
						echo '>';
					if (isset($m2['name']))
						echo $m2['name'];
					if (isset($m2['link']))
						echo '</a><br>';
					$umbruch=false;
				}
			
				foreach($m2 AS $m3)
				{
					if (is_array($m3)  && isset($m3['name']))
					{
						if (isset($m3['permissions']) && !checkpermission($m3['permissions']))
							continue;
						echo "\n\t\t&nbsp;&nbsp;&nbsp;".'<img title="'.$m3['name'].'" src="../skin/images/bullet_go.png" alt="page go" border="0">&nbsp;';
						if (isset($m3['link']))
							echo '<a href="'.$m3['link'].'" ';
						if (isset($m3['target']))
							echo 'target="'.$m3['target'].'" ';
						if (isset($m3['link']))
							echo '>';
						if (isset($m3['name']))
							echo $m3['name'];
						if (isset($m3['link']))
							echo '</a><br>';
						$umbruch=false;
					}
				}
				echo '<br />';
			}
		}
		

			echo '<br>';
	}
}

// Logout Button
echo '
<hr>

<a href="logout.php" target="_top"><b>Logout</b></a>
<br />
<p>
	<table cellpadding=2>
		<tr bgcolor="#c0cce0" >
			<th>Benutzer:</th>
			<td>'.$user.'</td>
		</tr>';

//Wenn der eingeloggte Benutzer nicht der original Benutzer ist, dann doe Original-UID anzeigen
if($user!=$user_original)
{
	echo '<tr bgcolor="#c0cce0" >
			<th>Benutzer Original:</th>
			<td><a href="'.$_SERVER['PHP_SELF'].'?loginasuser&uid='.$user_original.'">'.$user_original.'</a></td>
		</tr>';
}

// Formular zum Wechseln des Benutzers anzeigen wenn berechtigt
if($berechtigung_orig->isBerechtigt('system/loginasuser'))
{
	echo '<tr bgcolor="#c0cce0" >
			<th>Login as:</th>
			<td nowrap>
				<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
					<input type="text" name="uid" size="10">
					<input type="submit" name="loginasuser" value="Go">
				</form>
			</td>
		</tr>';
}
echo '
	</table>
</p><br>

</body>
</html>
';

?>