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
require_once '../../../include/person.class.php';
require_once '../../../include/nation.class.php';
require_once '../../../include/functions.inc.php';
require_once '../../../include/phrasen.class.php';

header('content-type: text/html; charset=utf-8');

if(isset($_GET['lang']))
	setSprache($_GET['lang']);



$nation = new nation(); 
$nation->getAll($ohnesperre = true); 

$sprache = getSprache(); 
$p=new phrasen($sprache); 

?>

<html>
	<head>
		<title>Incoming-Registration</title>
	</head>
	<body bgcolor="F2F2F2">
	
		<table width="100%" border="0">
			<tr>
				<td align="left"><a href="index.php">Login</a> > Registration </td>
				<td align ="right"><?php 		
				echo $p->t("global/sprache")." ";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
				<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?></td>
			</tr>
		</table>
		
		<form action="registration.php" method="POST">
		<table border = "0" style="margin-left:40%; margin-top:10%;">
			<tr>
				<td><?php echo $p->t('global/titel');?> Pre</td>
				<td><input type="text" size="20" name="titel_pre"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/vorname');?></td>
				<td><input type="text" size="40" name="vorname"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/nachname');?></td>
				<td><input type="text" size="40" name="nachname"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/titel');?> Post</td>
				<td><input type="text" size="20" name="titel_post"></td>
			</tr>		
			<tr>
				<td><?php echo $p->t('global/strasse');?></td>
				<td><input type="text" size="40" name="strasse"></td>
			</tr>	
			<tr>
				<td><?php echo $p->t('global/plz');?></td>
				<td><input type="text" size="20" name="plz"></td>
			</tr>				
			<tr>
				<td><?php echo $p->t('global/ort');?></td>
				<td><input type="text" size="40" name="ort"></td>
			</tr>				
			<tr>
				<td>Nation</td>
				<?php 
				echo "<td><SELECT name='nation'>\n"; 
				echo "<option value='nat_auswahl'>-- select --</option>\n";
				foreach ($nation->nation as $nat)
				{
					echo '<option value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
				}
				?>							
			</tr>				
			<tr>
				<td>E-Mail</td>
				<td><input type="text" size="40" name="email"></td>
			</tr>	
			<tr>
				<td><?php echo $p->t('global/anmerkung');?></td>
				<td><textarea name="anmerkung" cols="30" rows="5"></textarea></td>
			</tr>	
			<tr>
				<td colspan="2" align = "center"><input type="submit" name="submit" value="Registration"></td>			
		</table>
	</form>
	</body>
</html>

<?php 
if(isset($_REQUEST['submit']))
{
	echo var_dump($_REQUEST); 
	
	$titel_pre = $_REQUEST['titel_pre'];
	$vorname = $_REQUEST['vorname']; 
	$nachname =$_REQUEST['nachname']; 
	$titel_post = $_REQUEST['titel_post'];
	$strasse = $_REQUEST['strasse']; 
	$plz = $_REQUEST['plz']; 
	$ort = $_REQUEST['ort']; 
	$nation_code = $_REQUEST['nation']; 
	$email = $_REQUEST['email']; 
	$anmerkung = $_REQUEST['anmerkung']; 
	
}
?>