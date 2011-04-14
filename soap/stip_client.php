<?php 
require_once('../config/vilesci.config.inc.php');
require_once('stip.class.php'); 
?>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>STIP-Client</title>
	</head>
	<body>

		<form action="stip_client.php" method="post">
		  <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
		    <tr>
		      <td align="right">ErhKz:</td>
		      <td><input name="ErhKz" type="text" size="30" maxlength="3" value="<?php echo (isset($_REQUEST['ErhKz']) ? $_REQUEST['ErhKz'] : "005");?>"></td>
		    </tr>
		    <tr>
		      <td align="right">AnfragedatenID:</td>
		      <td><input name="AnfragedatenID" type="text" size="30" maxlength="40" value="<?php echo (isset($_REQUEST['AnfragedatenID']) ? $_REQUEST['AnfragedatenID'] : "");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Semester:</td>
		      <td><input name="Semester" type="text" size="30" maxlength="2" value="<?php echo (isset($_REQUEST['Semester']) ? $_REQUEST['Semester'] : "WS");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Studienjahr:</td>
		      <td><input name="Studienjahr" type="text" size="30" maxlength="7" value="<?php echo (isset($_REQUEST['Studienjahr']) ? $_REQUEST['Studienjahr'] : "2010/11");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">PersKz:</td>
		      <td><input name="PersKz" type="text" size="30" maxlength="11" value="<?php echo (isset($_REQUEST['PersKz']) ? $_REQUEST['PersKz'] : "0810256050");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">SVNR:</td>
		      <td><input name="Svnr" type="text" size="30" maxlength="10" value="<?php echo (isset($_REQUEST['Svnr']) ? $_REQUEST['Svnr'] : "1447081083");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Familienname:</td>
		      <td><input name="Familienname" type="text" size="30" maxlength="255" value="<?php echo (isset($_REQUEST['Familienname']) ? $_REQUEST['Familienname'] : "Cihlar");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Vorname:</td>
		      <td><input name="Vorname" type="text" size="30" maxlength="255" value="<?php echo (isset($_REQUEST['Vorname']) ? $_REQUEST['Vorname'] : "Markus");?>"></td>
		    </tr>
		        <tr>
		      <td align="right">Typ:</td>
		      <td><input name="Typ" type="text" size="30" maxlength="2" value="<?php echo (isset($_REQUEST['Typ']) ? $_REQUEST['Typ'] : "as");?>"></td>
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
	$client = new SoapClient(APP_ROOT."/soap/stip.soap.wsdl"); 
	
	
	$ErhKz = $_REQUEST['ErhKz'];
	$AnfragedatenID = $_REQUEST['AnfragedatenID']; 
	
	$bezieher = new stip(); 
	$bezieher->Semester = $_REQUEST['Semester'];
	$bezieher->Studienjahr = $_REQUEST['Studienjahr'];
	$bezieher->PersKz= $_REQUEST['PersKz'];
	$bezieher->SVNR= $_REQUEST['Svnr']; 
	$bezieher->Familienname= $_REQUEST['Familienname'];
	$bezieher->Vorname= $_REQUEST['Vorname'];
	$bezieher->Typ = $_REQUEST['Typ'];
	try
	{
		$response = $client->getStipDaten($ErhKz, $AnfragedatenID, $bezieher);
		echo var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
	 
}
?>