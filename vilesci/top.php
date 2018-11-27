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
	die('Keine UID gefunde !  <a href="javascript:history.back()">Zur&uuml;ck</a>');


$berechtigung=new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if (!($berechtigung->isBerechtigt('basis/vilesci', null, 's')))
	die ('Keine Berechtigung!');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>VileSci Men&uuml;</title>
	<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>



<body  style="background-color:#cecf9c; margin:0; padding:0;">

<div class="logo">
<table class="logo">
<tr>
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
$firstcat = '';
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

	echo '<td style="padding-left: 20px; padding-right: 20px; text-align: center">';

	if($firstcat=='' && isset($m['link']) && $m['target']=='nav')
		$firstcat=$m['link'];
	if (isset($m['link']))
		echo '<a class="toplink" href="'.$m['link'].'" ';
	if (isset($m['target']))
		echo 'target="'.$m['target'].'" ';
	if (isset($m['link']))
		echo '>';

	if(isset($m['image']))
	{
		echo '<img src="../skin/images/'.$m['image'].'" width="32" height="32" /><br>';
	}
	if (isset($m['name']))
		echo '<strong>'.$m['name'].'</strong>';

	if (isset($m['link']))
		echo '</a>';

	if ($hide)
		$display='none';
	else
		$display='block';
	echo "\n<DIV >\n".'<SPAN id="'.$m['name'].'" style="display:'.$display.'">';

	echo "\n</SPAN>\n</DIV>\n";
	echo '</td>';
}
echo '
</tr>
</table>
</div>';
if($firstcat!='')
	echo "<script>parent.nav.location='$firstcat';</script>";
?>


</body>
</html>
