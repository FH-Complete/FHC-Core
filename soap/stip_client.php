<?php 
require_once('../config/vilesci.config.inc.php');
require_once('stip.class.php'); 
?>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../include/js/jqSOAPClient.js"></script> 
		<script type="text/javascript" src="../include/js/jquery.js"></script> 
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
		        <input type="button" onclick="sendSoap();" value="send Soap">
		      </td>
		    </tr>
		</table>
		</form>


<?php 

if(isset($_REQUEST['submit']))
{
	$client = new SoapClient("https://cis.technikum-wien.at/soap/stip.wsdl.php?".microtime()); 
	//$client = new SoapClient(APP_ROOT."/soap/stip.soap.wsdl", array('login'=>'stip','password'=>'stip'));
	
	$username = "test";
	$passwort = "foo";
	
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
	$bezieher1 = new stip(); 
	$bezieher1->Semester = $_REQUEST['Semester'];
	$bezieher1->Studienjahr = $_REQUEST['Studienjahr'];
	$bezieher1->PersKz= $_REQUEST['PersKz'];
	$bezieher1->SVNR= $_REQUEST['Svnr']; 
	$bezieher1->Familienname= $_REQUEST['Familienname'];
	$bezieher1->Vorname= $_REQUEST['Vorname'];
	$bezieher1->Typ = $_REQUEST['Typ'];
	
	$arrayBezieher = array($bezieher, $bezieher1);
	
	$stipbezieher = array($ErhKz, $AnfragedatenID, $arrayBezieher);
	
	
	class foo {};
	$obj = new foo();
	$obj->ErrorNumber = "errornumber"; 
	$obj->KeyAttribute = "keyattribute"; 
	$obj->KeyValues = "keyvalues"; 
	$obj->CheckAttribute ="checkattribute"; 
	$obj->CheckValue ="checkvalue"; 
	$obj->ErrorText = "errortext"; 
	
	try
	{
		//$response = $client->GetStipendienbezieherStip(array("userName"=>$username,"passWord"=>$passwort,"anfrageDaten"=>array("ErhKz"=>$ErhKz, "AnfragedatenID"=>$AnfragedatenID,"Stipendiumsbezieher"=>array($bezieher, $bezieher1))));
		$response_stip = $client->GetStipendienbezieherStip(array("userName"=>$username,"passWord"=>$passwort,"anfrageDaten"=>array("ErhKz"=>$ErhKz, "AnfragedatenID"=>$AnfragedatenID,"Stipendiumsbezieher"=>array($bezieher))));
		var_dump($response_stip);
		echo '<hr>';
		//var_dump($response_stip->Stipendiumsbezieher->StipendiumsbezieherAntwort);
		
		$response_error = $client->SendStipendienbezieherStipError(array("userName"=>"abc", "passWord"=>"test", "errorReport"=>array("ErhKz"=>"erhkz", "StateCode"=>"statecode", "StateMessage"=>"statemessage", "ErrorStatusCode"=>"errorstatuscode", "JobID"=>"jobid", "ErrorContent"=>array($obj))));
		var_dump($response_error);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: ".$fault->faultcode.", faultstring: ".$fault->faultstring.")", E_USER_ERROR;
	}
	 
}

?>
<script type="text/javascript">
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}
function sendSoap()
{
var soapBody = new SOAPObject("ns1:GetStipendienbezieherStip");
soapBody.appendChild(new SOAPObject("ns1:userName")).val('joe');
soapBody.appendChild(new SOAPObject("ns1:passWord")).val('waschl');
//soapBody.ns = Array();
//soapBody.ns['name']='ns1';
//soapBody.ns['uri']='http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized';
var anfrageDaten = new SOAPObject("ns1:anfrageDaten");
anfrageDaten.appendChild(new SOAPObject("ns1:ErhKz")).val('005');
anfrageDaten.appendChild(new SOAPObject("ns1:AnfragedatenID")).val('100');


var stipendiumsbezieher = new SOAPObject("ns1:Stipendiumsbezieher");
var stipendiumsbezieherAnfrage = new SOAPObject("ns1:StipendiumsbezieherAnfrage");
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Semester")).val('WS');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Studienjahr")).val('2010/11');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:PersKz")).val('0810256050');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Svnr")).val('1447081083');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Familienname")).val('Cihlar');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Vorname")).val('Markus');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Typ")).val('as');

stipendiumsbezieher.appendChild(stipendiumsbezieherAnfrage);
/*
var stipendiumsbezieherAnfrage = new SOAPObject("ns1:StipendiumsbezieherAnfrage");
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Semester")).val('WS');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Studienjahr")).val('2010/11');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:PersKz")).val('2222222222');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Svnr")).val('2222222222');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Familienname")).val('2Cihlar');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Vorname")).val('2Markus');
stipendiumsbezieherAnfrage.appendChild(new SOAPObject("ns1:Typ")).val('as');

stipendiumsbezieher.appendChild(stipendiumsbezieherAnfrage);
*/
anfrageDaten.appendChild(stipendiumsbezieher);
soapBody.appendChild(anfrageDaten);
var sr = new SOAPRequest("GetStipendienbezieherStip",soapBody);
sr.addNamespace('ns1','http://www.fhr.ac.at/BISWS/STIP/WebServices/Services/STIPServiceDecentralized');
SOAPClient.Proxy="https://cis.technikum-wien.at/soap/stip.soap.php?"+gettimestamp();

SOAPClient.SendRequest(sr, clb_saveProjektphase);
}

function clb_saveProjektphase(respObj)
{
try
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    var msg = respObj.Body[0].saveProjektphaseResponse[0].message[0].Text;
	window.opener.ProjektphaseTreeRefresh();
	window.close();
}
catch(e)
{
	var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	alert('Fehler: '+fehler);
}
}

</script>
