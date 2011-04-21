<?php
/* Copyright (C) 2010 Technikum-Wien
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
 

require_once '../../../config/cis.config.inc.php';
require_once 'auth.php';
require_once '../../../include/mobilitaetsprogramm.class.php';
require_once '../../../include/functions.inc.php';
require_once '../../../include/phrasen.class.php';

header('content-type: text/html; charset=utf-8');

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$sprache = getSprache(); 
$p=new phrasen($sprache); 

$mobility = new mobilitaetsprogramm(); 
$mobility->getAll(); 

?>

<html>
	<head>
		<title>Incomming-Verwaltung</title>
	</head>
	<body>
		<table width="100%" border="0">
			<tr>
				<td align="left"><a href="incoming.php">Administration</a> </td>
				<td align ="right"><?php 		
				echo $p->t("global/sprache")." ";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
				<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?></td>
			</tr>
		</table>
			
		<table width ="100%" border="1">
			<tr>
				<td colspan="2"> Titel Vorname Nachname Titel</td>
			</tr>	
			<tr>
				<td>Austauschprogramm</td>
				<?php 
						echo "<td><SELECT name='nation'>\n"; 
						echo "<option value='austausch_auswahl'>-- select --</option>\n";
						foreach ($mobility->result as $mob)
						{
							echo '<option value="'.$mob->mobilitaetsprogramm_code.'" >'.$mob->kurzbz."</option>\n";
						}
				?>		
			</tr>
			<tr>
				<td colspan="2"><a href="incoming.php">Lehrveranstalltungen auswählen</a></td>
			</tr>
			<tr>
				<td colspan="2"><a href="incoming.php">Learning Agreement erstellen</a></td>
			</tr>
			<tr>
				<td colspan="2"><a href="incoming.php"><?php echo $p->t('incoming/uploadvondateien');?></a></td>
			</tr>
		</table>
		<table width="100%" border="0">
			<tr>
				<td align="center"><a href="logout.php">Logout</a> </td>
			</tr>
		</table>
	</body>
</html>