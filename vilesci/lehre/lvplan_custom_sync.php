<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>LV-Plan Syncronisation</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<h2>LV-Plan Synronisation</h2>
<?php
	echo '
	<a href="../../system/sync/sync_stpldev_stpl.php">LV-Plan Sync - Normal - mit Mail</a><br><br>
	<a href="../../system/sync/sync_stpldev_stpl.php?sendmail=false">LV-Plan Sync - Normal - ohne Mails</a><br>
	<br>
	<fieldset>
	<legend>Sync für speziellen Studiengang / Zeitraum</legend>
	<form action="../../system/sync/sync_stpldev_stpl.php" method="GET">
	<input type="hidden" name="custom" value="true">
	<table>
		<tr>
			<td>Studiengang</td>
			<td><SELECT name="studiengang_kz">';
		
	$stg = new studiengang();
	$stg->getAll('typ, kurzbz');
	
	foreach($stg->result as $row)
	{
		echo '<option value="'.$row->studiengang_kz.'">'.$row->kuerzel.' ('.$row->kurzbzlang.')</option>';
	}
	echo '</SELECT>
			</td>
		</tr>
		<tr>
			<td>Von</td>
			<td><input type="text" name="von" size="10" value="'.date('Y-m-d').'"></td>
		</tr>
		<tr>
			<td>Bis</td>
			<td><input type="text" name="bis" size="10" value="'.date('Y-m-d').'"></td>
		</tr>
		<tr>
			<td>Mails senden</td>
			<td><input type="checkbox" name="mail"></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Start"></td>
		</tr>	
	</table>
	</form>
	</fieldset>';
?>
</body>
</html>