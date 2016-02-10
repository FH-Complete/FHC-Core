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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */

require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';

  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
  
require_once('../../include/gebiet.class.php');
	
function getSpracheUser()
{
	if(isset($_SESSION['sprache_user']))
	{
		$sprache_user=$_SESSION['sprache_user'];
	}
	else
	{
		if(isset($_COOKIE['sprache_user']))
		{
			$sprache_user=$_COOKIE['sprache_user'];
		}
		else
		{
			$sprache_user=DEFAULT_LANGUAGE;
		}
		setSpracheUser($sprache_user);
	}
	return $sprache_user;
}

function setSpracheUser($sprache)
{
	$_SESSION['sprache_user']=$sprache;
	setcookie('sprache_user',$sprache,time()+60*60*24*30,'/');
}

if(isset($_GET['sprache_user']))
{
	$sprache_user = new sprache();
	if($sprache_user->load($_GET['sprache_user']))
	{
		setSpracheUser($_GET['sprache_user']);
	}
	else
		setSpracheUser(DEFAULT_LANGUAGE);
}

$sprache_user = getSpracheUser(); 

$p = new phrasen($sprache_user);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<script type="text/javascript">
function changeSprache(sprache)
{
	var content = '';
	content = parent.content.location.pathname+parent.content.location.search;
	
	window.location.href="topbar.php?sprache_user="+sprache;
	parent.menu.location.href="menu.php?sprache_user="+sprache;

	if (parent.content.location.search=='')
		parent.content.location.href=content+"?sprache_user="+sprache;
	else
		parent.content.location.href=content+"&sprache_user="+sprache;
}
</script>
<body>
<?php
echo '	<table style="background-image: url(../../skin/images/header_testtool.png); background-repeat: repeat-x;" width="100%" height="100%" cellspacing="0" cellpadding="0">
		<tr>
    		<td valign="top" align="left">
    			<a href="index.html" target="_top"><img class="header_logo" style="min-height:80%; left: 16px; top: 10%;" src="../../skin/styles/'.DEFAULT_STYLE.'/logo_250x130.png" alt="logo"></a>
    		</td>
    		<td align="right">
    		<select style="text-align: left; color: #0086CC; border: 1;" name="select">';
			$sprache = new sprache();
			$sprache->getAll(true);
			foreach($sprache->result as $row)
			{
				echo ' <option onclick="changeSprache(\''.$row->sprache.'\'); return false;" '.($row->sprache==$sprache_user?'selected':'').'>'.($row->bezeichnung_arr[getSprache()]).'&nbsp;&nbsp;</option>';
			}
			echo '	</select>&nbsp;&nbsp;</div>
  		</tr>
		</table>';
?>
</body>
</html>


















