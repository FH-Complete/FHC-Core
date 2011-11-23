<?php 
require_once('../config/vilesci.config.inc.php');
?>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../include/js/jqSOAPClient.js"></script> 
		<script type="text/javascript" src="../include/js/jquery.js"></script> 
		<title>Semesterticket-Client</title>
	</head>
	<body>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		  <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
		    <tr>
		      <td align="right">token:</td>
		      <td><input name="token" type="text" size="30" maxlength="15" value="<?php echo (isset($_REQUEST['token']) ? $_REQUEST['token'] : "");?>"></td>
		    </tr>
		    <tr>
		      <td align="right">Matrikelnummer:</td>
		      <td><input name="matrikelnummer" type="text" size="30" maxlength="15" value="<?php echo (isset($_REQUEST['matrikelnummer']) ? $_REQUEST['matrikelnummer'] : "");?>"></td>
		    </tr>
		    <tr>
		      <td align="right">Familienname:</td>
		      <td><input name="name" type="text" size="30" maxlength="255" value="<?php echo (isset($_REQUEST['familienname']) ? $_REQUEST['familienname'] : "");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Vorname:</td>
		      <td><input name="vorname" type="text" size="30" maxlength="255" value="<?php echo (isset($_REQUEST['vorname']) ? $_REQUEST['vorname'] : "");?>"></td>
		    </tr>
		  	<tr>
		      <td align="right">Geburtsdatum:</td>
		      <td><input name="geburtsdatum" type="text" size="30" maxlength="10" value="<?php echo (isset($_REQUEST['geburtsdatum']) ? $_REQUEST['geburtsdatum'] : "");?>"></td>
		    </tr>
		   <tr>
		      <td align="right">Postleitzahl:</td>
		      <td><input name="postleitzahl" type="text" size="30" maxlength="10" value="<?php echo (isset($_REQUEST['postleitzahl']) ? $_REQUEST['postleitzahl'] : "");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Semesterkuerzel:</td>
		      <td><input name="semesterkuerzel" type="text" size="30" maxlength="3" value="<?php echo (isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "11W");?>"></td>
		    </tr>
		    <tr>
		      <td align="right"></td>
		      <td>
		        <input type="submit" value=" Absenden " name="submit">
		        <input type="button" onclick="sendSoap();" value="send Soap">
		      </td>
		    </tr>
		</table>
		</form>


<?php 

if(isset($_REQUEST['submit']))
{
	$client = new SoapClient(APP_ROOT."/soap/semesterticket.wsdl.php?".microtime()); 
	
	try
	{
		class foo {}; 
		$obj = new foo(); 
		$obj->token = $_REQUEST['token'];
		$obj->matrikelnummer = $_REQUEST['matrikelnummer'];
		$obj->name = $_REQUEST['name'];
		$obj->vorname = $_REQUEST['vorname'];
		$obj->geburtsdatum = $_REQUEST['geburtsdatum'];
		$obj->postleitzahl = $_REQUEST['postleitzahl'];
		$obj->semesterkuerzel = $_REQUEST['semesterkuerzel'];
		
		$response = $client->verifyData($obj);
		//$response = $client->verifyData(array('token'=>$_REQUEST['token'], 'matrikelnummer'=>$_REQUEST['matrikelnummer'], 'name'=>$_REQUEST['name'], 'vorname'=>$_REQUEST['vorname'], 'geburtsdatum'=>$_REQUEST['geburtsdatum'], 'postleitzahl'=>$_REQUEST['postleitzahl'], 'semesterkuerzel'=>$_REQUEST['semesterkuerzel']));
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
	 
}

?>