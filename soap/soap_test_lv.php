<?php 
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php'); 

$method = (isset($_GET['method'])?$_GET['method']:'studiengang');

$getuid = get_uid(); 
if(!check_lektor($getuid))
	die('Sie haben keine Berechtigung für diese Seite'); 
?>
<html>
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <script type="text/javascript" src="../include/js/jqSOAPClient.js"></script> 
        <script type="text/javascript" src="../include/js/jquery.js"></script> 
        <script type="text/javascript" src="../include/js/jqXMLUtils.js"></script> 
        <title>SOAP TestClient für Lehrveranstaltungen</title>
	</head>
	<body>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=studiengang'?>">GetLehrveranstaltungFromStudiengang</a><br>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=id'?>">GetLehrveranstaltungFromId</a><br><br>
        <a href ="<?php echo APP_ROOT.'soap/lehrveranstaltung.wsdl.php'?>">Show WSDL </a><br><br>
        
        <?php if($method=='studiengang')
        {
            echo'
            <form action="'.$_SERVER["PHP_SELF"].'?method=studiengang" method="post">
            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
                <tr>
                    <td align="right">Name:</td>
                    <td><input name="username" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['username']) ? $_REQUEST['username'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Passwort:</td>
                    <td><input name="passwort" type="password" size="30" maxlength="255" value="'.(isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Studiengang:</td>
                    <td><input name="studiengang" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['studiengang']) ? $_REQUEST['studiengang'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Semester:</td>
                    <td><input name="semester" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "").'"></td>
                </tr>    
                <tr>
                    <td align="right">Ausbildungssemester* :</td>
                    <td><input name="aussemester" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['aussemester']) ? $_REQUEST['aussemester'] : "").'"></td>
                </tr>  
                <tr>
                    <td align="right"></td>
                    <td>
                        <input type="submit" value=" Absenden " name="submit">
                        <input type="button" onclick="sendSoap();" value="send Soap">
                    </td>
                </tr>
            </table>
        </form>';
        echo '
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
            var soapBody = new SOAPObject("getLehrveranstaltungFromStudiengang");
            var authentifizierung = new SOAPObject("authentifizierung");
            authentifizierung.appendChild(new SOAPObject("username")).val("burkhart");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("R3sid3nt");

            soapBody.appendChild(new SOAPObject("studiengang")).val("bif");
            soapBody.appendChild(new SOAPObject("semester")).val("WS2010");
            soapBody.appendChild(new SOAPObject("ausbildungssemester")).val("3");
            soapBody.appendChild(authentifizierung);

            var sr = new SOAPRequest("getLehrveranstaltungFromStudiengang",soapBody);
            SOAPClient.Proxy="'.APP_ROOT.'/soap/lehrveranstaltung.soap.php?"+gettimestamp();

            SOAPClient.SendRequest(sr, clb_saveProjektphase);
        }

        function clb_saveProjektphase(respObj)
        {
            try
            {
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                alert("OK");
                var msg = respObj.Body[0].saveProjektphaseResponse[0].message[0].Text;
                window.opener.ProjektphaseTreeRefresh();
                window.close();
            }
            catch(e)
            {
                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
                alert("Fehler: "+fehler);
            }
        }

        </script>';
        }
        else if($method=='id')
        {
            echo'
            <form action="'.$_SERVER["PHP_SELF"].'?method=id" method="post">
            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
                <tr>
                    <td align="right">Name:</td>
                    <td><input name="username" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['username']) ? $_REQUEST['username'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Passwort:</td>
                    <td><input name="passwort" type="password" size="30" maxlength="255" value="'.(isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Lehrveranstaltung_id:</td>
                    <td><input name="lv_id" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['lv_id']) ? $_REQUEST['lv_id'] : "").'"></td>
                </tr>
                <tr>
                    <td align="right">Semester* :</td>
                    <td><input name="semester" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "").'"></td>
                </tr>    
                <tr>
                    <td align="right"></td>
                    <td>
                        <input type="submit" value=" Absenden " name="submit">
                        <input type="button" onclick="sendSoap();" value="send Soap">
                    </td>
                </tr>
            </table>
        </form>';
        echo '
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
            var soapBody = new SOAPObject("getLehrveranstaltungFromId");
            var authentifizierung = new SOAPObject("authentifizierung");
            authentifizierung.appendChild(new SOAPObject("username")).val("burkhart");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("R3sid3nt");

            soapBody.appendChild(new SOAPObject("lehrveranstaltung_id")).val("222");
            soapBody.appendChild(new SOAPObject("semester")).val("");
            soapBody.appendChild(authentifizierung);

            var sr = new SOAPRequest("getLehrveranstaltungFromId",soapBody);
            SOAPClient.Proxy="'.APP_ROOT.'/soap/lehrveranstaltung.soap.php?"+gettimestamp();

            SOAPClient.SendRequest(sr, clb_saveProjektphase);
        }

        function clb_saveProjektphase(respObj)
        {
            try
            {
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                alert("OK");
                var msg = respObj.Body[0].saveProjektphaseResponse[0].message[0].Text;
                window.opener.ProjektphaseTreeRefresh();
                window.close();
            }
            catch(e)
            {
                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
                alert("Fehler: "+fehler);
            }
        }

        </script>';
        }


class foo {};

if(isset($_REQUEST['submit']) && $_GET['method']=='studiengang')
{
	$client = new SoapClient(APP_ROOT."/soap/lehrveranstaltung.wsdl.php?".microtime(true)); 
	
	try
	{
      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getLehrveranstaltungFromStudiengang($studiengang = $_REQUEST['studiengang'], $semester =$_REQUEST['semester'] , $ausbildungssemester=$_REQUEST['aussemester'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}

if(isset($_REQUEST['submit']) && $_GET['method']=='id')
{
	$client = new SoapClient(APP_ROOT."/soap/lehrveranstaltung.wsdl.php?".microtime(true)); 
	
	try
	{
      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
		$response = $client->getLehrveranstaltungFromId($lehrveranstaltungs_id = $_REQUEST['lv_id'], $semester =$_REQUEST['semester'] ,  $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
	 
}



?>

