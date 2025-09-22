<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>.
 */
/**
 * Testclient um STIP Error Meldungen zu simulieren
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
		<h1>Testclient für Webservice Stipendienstelle Error</h1>
		<a href="stip.wsdl.php">Show WSDL</a>
		<br><br>
		<form action="stip_client_error.php" method="post">
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
		      <td align="right">StateCode:</td>
		      <td>
				<select name="statecode">
				<option value="1" <?php echo (isset($_REQUEST['statecode']) && $_REQUEST['statecode']==1?'selected':'');?> >1 - in progress</option>
				<option value="2" <?php echo (isset($_REQUEST['statecode']) && $_REQUEST['statecode']==2?'selected':'');?> >2 - completed</option>
				</select>
			</td>
		    </tr>
		        <tr>
		      <td align="right">StateMessage:</td>
		      <td><input name="statemessage" type="text" size="30" maxlength="50" value="<?php echo $db->convert_html_chars((isset($_REQUEST['statemessage']) ? $_REQUEST['statemessage'] : ""));?>"></td>
		    </tr>
		        <tr>
		      <td align="right">ErrorStatusCode:</td>
		      <td>
				<select name="errorstatuscode">
				<option value="1" <?php echo (isset($_REQUEST['errorstatuscode']) && $_REQUEST['errorstatuscode']==1?'selected':'');?> >1 - successfull</option>
				<option value="2" <?php echo (isset($_REQUEST['errorstatuscode']) && $_REQUEST['errorstatuscode']==2?'selected':'');?> >2 - incomplete xml</option>
				<option value="3" <?php echo (isset($_REQUEST['errorstatuscode']) && $_REQUEST['errorstatuscode']==3?'selected':'');?> >3 - incomplete processing</option>
				<option value="4" <?php echo (isset($_REQUEST['errorstatuscode']) && $_REQUEST['errorstatuscode']==4?'selected':'');?> >4 - system error</option>
				</select>
			</td>
		    </tr>
		        <tr>
		      <td align="right">JobID:</td>
		      <td><input name="jobid" type="text" size="30" maxlength="30" value="<?php echo $db->convert_html_chars((isset($_REQUEST['jobid']) ? $_REQUEST['jobid'] : ""));?>"></td>
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
	$statecode = $_REQUEST['statecode'];
	$jobid = $_REQUEST['jobid'];
	$statemessage = $_REQUEST['statemessage'];
	$errorstatuscode = $_REQUEST['errorstatuscode'];

	try
	{
		$response_stip = $client->SendStipendienbezieherStipError(array("userName"=>$username,"passWord"=>$passwort,"errorReport"=>array("ErhKz"=>$ErhKz, "StateCode"=>$statecode,"StateMessage"=>$statemessage,"ErrorStatusCode"=>$errorstatuscode,"JobID"=>$jobid)));
		echo '<h2>Error Request Result sent</h2>';
	}
	catch(SoapFault $fault)
	{
    	echo "SOAP Fault: (faultcode: ".$fault->faultcode.", faultstring: ".$fault->faultstring.")", E_USER_ERROR;
	}

}

?>
</body>
</html>
