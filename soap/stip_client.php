<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('stip.class.php');
$getuid=get_uid();
if(!check_lektor($getuid))
	die('Sie haben keine Berechtigung für diese Seite.');
$db = new basis_db();
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
		<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
		<title>STIP-Client</title>
	</head>
	<body>
		<h1>Testclient für Webservice Stipendienstelle</h1>
		<a href="stip.wsdl.php">Show WSDL</a> <br>
		<a href="stip_client_error.php">Error Tester</a>
		<br><br>
		<form action="stip_client.php" method="post">
		<table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
			<tr>
				<td align="right">Username:</td>
				<td><input name="username" type="text" size="30" maxlength="50" value="<?php echo $db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Passwort:</td>
				<td><input name="password" type="password" size="30" maxlength="50" value=""></td>
			</tr>
			<tr>
				<td align="right">ErhKz:</td>
				<td><input name="ErhKz" type="text" size="30" maxlength="3" value="<?php echo $db->convert_html_chars((isset($_REQUEST['ErhKz']) ? $_REQUEST['ErhKz'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">AnfragedatenID:</td>
				<td><input name="AnfragedatenID" type="text" size="30" maxlength="40" value="<?php echo $db->convert_html_chars((isset($_REQUEST['AnfragedatenID']) ? $_REQUEST['AnfragedatenID'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Semester:</td>
				<td><input name="Semester" type="text" size="30" maxlength="2" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Semester']) ? $_REQUEST['Semester'] : ""));?>"> WS | SS</td>
			</tr>
			<tr>
				<td align="right">Studienjahr:</td>
				<td><input name="Studienjahr" type="text" size="30" maxlength="7" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Studienjahr']) ? $_REQUEST['Studienjahr'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">PersKz:</td>
				<td><input name="PersKz" type="text" size="30" maxlength="11" value="<?php echo $db->convert_html_chars((isset($_REQUEST['PersKz']) ? $_REQUEST['PersKz'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Matrikelnummer:</td>
				<td><input name="Matrikelnummer" type="text" size="30" maxlength="11" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Matrikelnummer']) ? $_REQUEST['Matrikelnummer'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">StgKz:</td>
				<td><input name="StgKz" type="text" size="30" maxlength="11" value="<?php echo $db->convert_html_chars((isset($_REQUEST['StgKz']) ? $_REQUEST['StgKz'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">SVNR:</td>
				<td><input name="Svnr" type="text" size="30" maxlength="10" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Svnr']) ? $_REQUEST['Svnr'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Familienname:</td>
				<td><input name="Familienname" type="text" size="30" maxlength="255" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Familienname']) ? $_REQUEST['Familienname'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Vorname:</td>
				<td><input name="Vorname" type="text" size="30" maxlength="255" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Vorname']) ? $_REQUEST['Vorname'] : ""));?>"></td>
			</tr>
			<tr>
				<td align="right">Typ:</td>
				<td><input name="Typ" type="text" size="30" maxlength="2" value="<?php echo $db->convert_html_chars((isset($_REQUEST['Typ']) ? $_REQUEST['Typ'] : ""));?>"> AS | AG</td>
			</tr>
			<tr>
				<td align="right"></td>
				<td>
					<input type="submit" value=" Absenden " name="submit">
				</td>
			</tr>
		</table>
		</form>


<?php

if(isset($_REQUEST['submit']))
{
	$client = new SoapClient(APP_ROOT."/soap/stip.wsdl.php?".microtime(true));

	$username = $_REQUEST['username'];
	$passwort = $_REQUEST['password'];

	$ErhKz = $_REQUEST['ErhKz'];
	$AnfragedatenID = $_REQUEST['AnfragedatenID'];

	$bezieher = new stip();
	$bezieher->Semester = $_REQUEST['Semester'];
	$bezieher->Studienjahr = $_REQUEST['Studienjahr'];
	$bezieher->PersKz= $_REQUEST['PersKz'];
	$bezieher->Matrikelnummer= $_REQUEST['Matrikelnummer'];
	$bezieher->StgKz= $_REQUEST['StgKz'];
	$bezieher->SVNR= $_REQUEST['Svnr'];
	$bezieher->Familienname= $_REQUEST['Familienname'];
	$bezieher->Vorname= $_REQUEST['Vorname'];
	$bezieher->Typ = $_REQUEST['Typ'];
	$bezieher1 = new stip();
	$bezieher1->Semester = $_REQUEST['Semester'];
	$bezieher1->Studienjahr = $_REQUEST['Studienjahr'];
	$bezieher1->PersKz= $_REQUEST['PersKz'];
	$bezieher1->Matrikelnummer= $_REQUEST['Matrikelnummer'];
	$bezieher1->StgKz= $_REQUEST['StgKz'];
	$bezieher1->SVNR= $_REQUEST['Svnr'];
	$bezieher1->Familienname= $_REQUEST['Familienname'];
	$bezieher1->Vorname= $_REQUEST['Vorname'];
	$bezieher1->Typ = $_REQUEST['Typ'];

	try
	{
		$response_stip = $client->GetStipendienbezieherStip(array("userName"=>$username,"passWord"=>$passwort,"anfrageDaten"=>array("ErhKz"=>$ErhKz, "AnfragedatenID"=>$AnfragedatenID,"Stipendiumsbezieher"=>array($bezieher))));
		echo '<h2>Single Request Result</h2>';
		echo '<pre>'.print_r($response_stip->GetStipendienbezieherStipResult,true).'</pre>';
		echo '<h2>Multiple Request Result</h2>';
		$response_stip = $client->GetStipendienbezieherStip(array("userName"=>$username,"passWord"=>$passwort,"anfrageDaten"=>array("ErhKz"=>$ErhKz, "AnfragedatenID"=>$AnfragedatenID,"Stipendiumsbezieher"=>array($bezieher, $bezieher1))));
		echo '<pre>'.print_r($response_stip->GetStipendienbezieherStipResult, true).'</pre>';
	}
	catch(SoapFault $fault)
	{
		echo "SOAP Fault: (faultcode: ".$fault->faultcode.", faultstring: ".$fault->faultstring.")", E_USER_ERROR;
	}

}

?>
Legende:<br>
Antwortstatuscode: 1=gefunden; 2=nicht gefunden<br>
StudStatusCode: 1=aktiver Student; 2=Unterbrecher; 3=Absolvent; 4=Abbrecher<br>
Typ: AS = Antragsteller, AG = Angehöriger
</body>
</html>
