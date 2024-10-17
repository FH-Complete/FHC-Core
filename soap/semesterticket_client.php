<?php
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');

$getuid = get_uid();
if(!check_lektor($getuid))
	die('Sie haben keine Berechtigung für diese Seite');

$db = new basis_db();
?>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../include/js/jqSOAPClient.js"></script>
		<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
		<title>Semesterticket-Client</title>
	</head>
	<body>
		<h1>Testclient für Webservice Wiener Linien</h1>
		<a href="semesterticket.wsdl.php">Show WSDL</a>
		<br><br>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		  <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
		    <tr>
		      <td align="right">token:</td>
		      <td><input name="token" type="text" size="30" maxlength="15" value="<?php echo $db->convert_html_chars((isset($_REQUEST['token']) ? $_REQUEST['token'] : ""));?>"> Anfrage ID - zufälliger Text</td>
		    </tr>
		    <tr>
		      <td align="right">Matrikelnummer:</td>
		      <td><input name="matrikelnummer" type="text" size="30" maxlength="15" value="<?php echo $db->convert_html_chars((isset($_REQUEST['matrikelnummer']) ? $_REQUEST['matrikelnummer'] : ""));?>"></td>
		    </tr>
		    <tr>
		      <td align="right">Familienname:</td>
		      <td><input name="name" type="text" size="30" maxlength="255" value="<?php echo $db->convert_html_chars((isset($_REQUEST['name']) ? $_REQUEST['name'] : ""));?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Vorname:</td>
		      <td><input name="vorname" type="text" size="30" maxlength="255" value="<?php echo $db->convert_html_chars((isset($_REQUEST['vorname']) ? $_REQUEST['vorname'] : ""));?>"></td>
		    </tr>
		  	<tr>
		      <td align="right">Geburtsdatum:</td>
		      <td><input name="geburtsdatum" type="text" size="30" maxlength="10" value="<?php echo $db->convert_html_chars((isset($_REQUEST['geburtsdatum']) ? $_REQUEST['geburtsdatum'] : ""));?>"> Format: YYYY-MM-DD</td>
		    </tr>
		   <tr>
		      <td align="right">Postleitzahl:</td>
		      <td><input name="postleitzahl" type="text" size="30" maxlength="10" value="<?php echo $db->convert_html_chars((isset($_REQUEST['postleitzahl']) ? $_REQUEST['postleitzahl'] : ""));?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Semesterkuerzel:</td>
		      <td><input name="semesterkuerzel" type="text" size="30" maxlength="3" value="<?php echo $db->convert_html_chars((isset($_REQUEST['semesterkuerzel']) ? $_REQUEST['semesterkuerzel'] : "16W"));?>"> Format 16W für WS2016; 16S für SS2016</td>
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
	$client = new SoapClient(APP_ROOT."/soap/semesterticket.wsdl.php?".microtime(true));

	try
	{
		class foo {};
		$obj = new foo();
		$obj->token = $_REQUEST['token'];
		$obj->Matrikelnummer = $_REQUEST['matrikelnummer'];
		$obj->Name = $_REQUEST['name'];
		$obj->Vorname = $_REQUEST['vorname'];
		$obj->Geburtsdatum = $_REQUEST['geburtsdatum'];
		$obj->Postleitzahl = $_REQUEST['postleitzahl'];
		$obj->Semesterkuerzel = $_REQUEST['semesterkuerzel'];

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
<pre>
* mögliche Fehlercodes
 * 	1: Kein aufrechtes Studium
 *	2: Fehlerhafter Request
 *	3: Student wurde nicht gefunden
 *	4: Fehler Geburtsdatum
 *	5: Fehler Postleitzahl
 *	6: Fehler Vorname
 *	7: Fehler Nachname
 *	8: Fehler Semester
 *	9: Fehler Matrikelnummer
</pre>
</body>
</html>
