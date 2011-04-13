<?php 
require_once('stip.class.php'); 
?>
<html>
	<head>
		<title>STIP-Client</title>
	</head>
	<body>

		<form action="stip_client.php" method="post">
		  <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
		    <tr>
		      <td align="right">ErhKz:</td>
		      <td><input name="ErhKz" type="text" size="30" maxlength="3" <?php echo "value=".$_REQUEST['ErhKz']; ?>></td>
		    </tr>
		    <tr>
		      <td align="right">AnfragedatenID:</td>
		      <td><input name="AnfragedatenID" type="text" size="30" maxlength="40" <?php echo "value=".$_REQUEST['AnfragedatenID']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">Semester:</td>
		      <td><input name="Semester" type="text" size="30" maxlength="2" <?php echo "value=".$_REQUEST['Semester']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">Studienjahr:</td>
		      <td><input name="Studienjahr" type="text" size="30" maxlength="7" <?php echo "value=".$_REQUEST['Studienjahr']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">PersKz:</td>
		      <td><input name="PersKz" type="text" size="30" maxlength="11" <?php echo "value=".$_REQUEST['PersKz']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">SVNR:</td>
		      <td><input name="Svnr" type="text" size="30" maxlength="10" <?php echo "value=".$_REQUEST['Svnr']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">Familienname:</td>
		      <td><input name="Familienname" type="text" size="30" maxlength="255" <?php echo "value=".$_REQUEST['Familienname']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">Vorname:</td>
		      <td><input name="Vorname" type="text" size="30" maxlength="255" <?php echo "value=".$_REQUEST['Vorname']; ?>></td>
		    </tr>
		        <tr>
		      <td align="right">Typ:</td>
		      <td><input name="Typ" type="text" size="30" maxlength="2" <?php echo "value=".$_REQUEST['Typ']; ?>></td>
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
	$client = new SoapClient("http://calva.technikum-wien.at/burkhart/fhcomplete/trunk/soap/stip.soap.wsdl"); 
	
	
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
	
	$response = $client->getStipDaten($ErhKz, $AnfragedatenID, $bezieher); 

	echo var_dump($response); 
}
?>