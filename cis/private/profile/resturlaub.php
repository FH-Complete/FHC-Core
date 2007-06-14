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
// **
// * @brief Uebersicht der Resturlaubstage

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/resturlaub.class.php');
	
	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	   	
	$uid = get_uid();
?>

<html>
<head>
	<title>Resturlaubstage</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>

<body>
	<H2>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>&nbsp;Resturlaubstage</td>
			</tr>
		</table>
	</H2>

	<TABLE >
    <TR class="liste">
    	<TH>Nachname</TH>
    	<TH>Vorname</TH>
    	<TH>Resturlaubstage</TH>
    	<TH>Mehrarbeitsstunden</TH>
    	<TH>Letzte Aenderung</TH>
	</TR>

	<?php
	$obj=new resturlaub($conn);
	$obj->getResturlaubFixangestellte();
	$i=0;
	
	foreach ($obj->result as $row)
	{
		echo '<TR class="liste'.($i%2).'">';
		echo "<TD>$row->nachname</TD><TD>$row->vorname $row->vornamen</TD>";
		echo "<TD>$row->resturlaubstage</TD>";
		echo "<TD>$row->mehrarbeitsstunden</TD>";
		echo "<TD>$row->updateamum</TD>";
		echo '</TR>';
		$i++;
	}
	?>

  </TABLE>
</body>
</html>
