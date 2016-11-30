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
require_once '../../../include/phrasen.class.php';
require_once '../../../include/person.class.php';

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$sprache = getSprache();
$p=new phrasen($sprache);

if (isset($_POST['userid']))
{
	$login = $_REQUEST['userid'];
	$person = new person();

	session_start();

	$preincoming=$person->checkZugangscodeIncoming($login);

	//Zugangscode wird  überprüft
	if($preincoming != false)
	{
		$_SESSION['incoming/user'] = $login;
		$_SESSION['incoming/preincomingid'] = $preincoming;

		header('Location: incoming.php');
		exit;
	}
	else
	{
		$errormsg= $p->t('incoming/ungueltigerbenutzer');
	}
}

?>
<html>
	<head>
		<title>Incoming-Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	</head>
	<body>
	<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
		<td class="rand"></td>
		<td style="vertical-align: top" class="boxshadow">
			<table width="100%" border="0">
				<tr>
					<td style="padding: 20px; text-align: left; width: 33%"></td>
					<td style="padding: 20px; text-align: center; width: 33%"></td>
					<td style="padding: 20px; text-align: right; width: 33%"><?php
					echo $p->t("global/sprache")." ";
					echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English&'.$_SERVER['QUERY_STRING'].'">'.$p->t("global/englisch").'</a> |
					<a href="'.$_SERVER['PHP_SELF'].'?lang=German&'.$_SERVER['QUERY_STRING'].'">'.$p->t("global/deutsch").'</a><br>';?></td>
				</tr>
			</table>
			
			<table style="padding: 50px" border="0" width ="100%">
				<tr height="50%">
					<td align ="center" valign="center"><h3><?php echo $p->t('incoming/welcomeToUAS',array(CAMPUS_NAME));?></h3><br><br>
					 <img src="../../../skin/styles/<?php echo DEFAULT_STYLE ?>/logo.png"></td>
				</tr>
			</table>
			<table border ="0" width ="100%">
				<form action ="index.php" method="POST">
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center"><a href="registration.php"><?php echo $p->t('incoming/registration');?></a></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center"><input type="text" size="30" value="UserID" name ="userid" onfocus="this.value='';"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center"><input type="submit" value="Login" name="submit"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center"><?php if(isset($errormsg))
								echo $errormsg; ?>
				</tr>
				</form>
			</table>
		</td>
	<td class="rand">
	</td>
	</tr>
	</table>

	</body>

</html>