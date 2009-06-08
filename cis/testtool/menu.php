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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../config.inc.php');
require_once('../../include/gebiet.class.php');

session_start();

//Connection Herstellen
if(!$db_conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<?php
if (isset($_SESSION['pruefling_id']))
{
	echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="border-right-width:1px;border-right-color:#BCBCBC;">';
	echo '<tr><td nowrap>
			<a class="MenuItem" href="index.html" target="_top">
				<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Home
			</a>
		</td></tr>';
	echo '<tr><td nowrap><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Gebiet</td></tr>';
	echo '<tr><td nowrap>';
	echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Gebiet" style="display: visible;">';
	  	
	$qry = "SELECT * FROM testtool.vw_ablauf WHERE studiengang_kz='".addslashes($_SESSION['studiengang_kz'])."' AND semester='".addslashes($_SESSION['semester'])."' ORDER BY reihung";
	//echo $qry;
	if($result = pg_query($db_conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$gebiet = new gebiet($db_conn);
			if($gebiet->check_gebiet($row->gebiet_id))
			{
				echo '<tr>
							<td width="10" nowrap>&nbsp;</td>
					   		<td nowrap>
					   			<a class="Item" href="frage.php?gebiet_id='.$row->gebiet_id.'" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;'.$row->gebiet_bez.'</a>
					   		</td>
					   	</tr>';
			}
			else 
			{
				echo '<tr>
							<td width="10" nowrap>&nbsp;</td>
					   		<td nowrap>
					   			<span class="error"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;'.$row->gebiet_bez.' (invalid)</span>
					   		</td>
					   	</tr>';
			}
		}
	}
	echo '</table>';
	echo '</td></tr></table>';
}
else
{
	echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="border-right-width:1px;border-right-color:#BCBCBC;">';
	echo '<tr><td nowrap>
				<a class="HyperItem" href="index.html" target="_top">
					<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Login
				</a>
			</td></tr>';
	echo '</table>';
	echo '</td></tr></table>';
}
?>
</body>
</html>
