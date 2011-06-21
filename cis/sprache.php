<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Sprache Umschalten</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<table class="tabcontent">
	 
	<tr> 
	    <td>
				<?php
				echo 'Aktuelle Sprache: '.getSprache();
				echo '<br><br>Sprache wechseln:';
				
				$sprache = new sprache();
				$sprache->getAll(true);
				foreach($sprache->result as $row)
				{
					echo ' <a href="sprache.php?sprache='.$row->sprache.'" title="'.$row->sprache.'"><img src="../cms/image.php?src=flag&sprache='.$row->sprache.'" alt="'.$row->sprache.'"></a>';
				}
				?>
        </td>
	</tr>

</table>
</body>
</html>
