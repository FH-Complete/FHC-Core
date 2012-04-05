<?php 
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php'); 

$method = (isset($_GET['method'])?$_GET['method']:'getOrtFromKurzbz');

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
        <title>SOAP TestClient für Orte</title>
	</head>
	<body>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getOrtFromKurzbz'?>">getOrtFromKurzbz</a><br>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getRaeume'?>">getRaeume</a><br>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=searchRaum'?>">searchRaum</a><br>
        <a href ="<?php echo APP_ROOT.'soap/ort.wsdl.php'?>">Show WSDL </a><br><br>
        
        <?php 
        if($method=='getOrtFromKurzbz')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getOrtFromKurzbz" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['username']) ? $_REQUEST['username'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.(isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Ort_Kurzbz:</td>
	                    <td><input id="ort_kurzbz" name="ort_kurzbz" type="text" size="30" maxlength="10" value="'.(isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
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
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
	        	ort_kurzbz = document.getElementById("ort_kurzbz").value;
	        	
	            var soapBody = new SOAPObject("getOrtFromKurzbz");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("ort_kurzbz")).val(ort_kurzbz);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("getOrtFromKurzbz",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/ort.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                //var msg = respObj.Body[0].getOrtFromKurzbzResponse[0].message[0].Text;
	                data = JSON.stringify(respObj.Body[0]);
	                //data=data.replace(/[[{]/g,"<br>[{");
	                //data=data.replace(/[}]}]]/g,"}]}]<br>");
	                
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre";
	                
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
        }
        elseif($method=='getRaeume')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getRaeume" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['username']) ? $_REQUEST['username'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.(isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Raumtyp:</td>
	                    <td><input id="raumtyp_kurzbz" name="raumtyp_kurzbz" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['raumtyp_kurzbz']) ? $_REQUEST['raumtyp_kurzbz'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
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
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
	        	raumtyp_kurzbz = document.getElementById("raumtyp_kurzbz").value;
	        		        	
	            var soapBody = new SOAPObject("getRaeume");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("raumtyp_kurzbz")).val(raumtyp_kurzbz);
	            soapBody.appendChild(authentifizierung);
	            
	
	            var sr = new SOAPRequest("getRaeume",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/ort.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                //var msg = respObj.Body[0].getRaeumeResponse[0].message[0].Text;
	                data = JSON.stringify(respObj.Body[0]);
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre";
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
		}
		elseif($method=='searchRaum')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=searchRaum" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	            	<tr>
	            		<td align="right">Datum:</td>
	                    <td><input id="datum" name="datum" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['datum']) ? $_REQUEST['datum'] : "").'"></td>
	                </tr>
	                <tr>
	            		<td align="right">Zeit-Von:</td>
	                    <td><input id="zeit_von" name="zeit_von" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['zeit_von']) ? $_REQUEST['zeit_von'] : "").'"></td>
	                </tr>
	                <tr>
	            		<td align="right">Zeit-Bis:</td>
	                    <td><input id="zeit_bis" name="zeit_bis" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['zeit_bis']) ? $_REQUEST['zeit_bis'] : "").'"></td>
	                </tr>
	                <tr>
	            		<td align="right">Raumtyp:</td>
	                    <td><input id="raumtyp" name="raumtyp" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['raumtyp']) ? $_REQUEST['raumtyp'] : "").'"></td>
	                </tr>
	                <tr>
	            		<td align="right">Anzahl Personen</td>
	                    <td><input id="anzahl_personen" name="anzahl_personen" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['anzahl_personen']) ? $_REQUEST['anzahl_personen'] : "").'"></td>
	                </tr>
	                <tr>
	            		<td align="right">Reservierung</td>
	                    <td><input id="reservierung" name="reservierung" type="checkbox" size="30" maxlength="255" '.(isset($_REQUEST['reservierung']) ? 'checked' : "").'></td>
	                </tr>
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.(isset($_REQUEST['username']) ? $_REQUEST['username'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.(isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "").'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
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
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
	        	datum = document.getElementById("datum").value;
	        	zeit_von = document.getElementById("zeit_von").value;
	        	zeit_bis = document.getElementById("zeit_bis").value;
	        	raumtyp = document.getElementById("raumtyp").value;
	        	anzahl_personen = document.getElementById("anzahl_personen").value;
	        	reservierung = document.getElementById("reservierung").checked;
	        	
	        		        	
	            var soapBody = new SOAPObject("searchRaum");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("datum")).val(datum);
	            soapBody.appendChild(new SOAPObject("zeit_von")).val(zeit_von);
	            soapBody.appendChild(new SOAPObject("zeit_bis")).val(zeit_bis);
	            soapBody.appendChild(new SOAPObject("raumtyp")).val(raumtyp);
	            soapBody.appendChild(new SOAPObject("anzahl_personen")).val(anzahl_personen);
	            soapBody.appendChild(new SOAPObject("reservierung")).val(reservierung);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("searchRaum",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/ort.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre";
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
		}
        
echo '<div id="output">';
class foo {};

if(isset($_REQUEST['submit']) && $_GET['method']=='getOrtFromKurzbz')
{
	$client = new SoapClient(APP_ROOT."/soap/ort.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getOrtFromKurzbz($_REQUEST['ort_kurzbz'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='getRaeume')
{
	$client = new SoapClient(APP_ROOT."/soap/ort.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $raumtyp_kurzbz=(isset($_REQUEST['raumtyp_kurzbz'])?$_REQUEST['raumtyp_kurzbz']:null);
        $response = $client->getRaeume($raumtyp_kurzbz,$authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='searchRaum')
{
	$client = new SoapClient(APP_ROOT."/soap/ort.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->searchRaum($_REQUEST['datum'],$_REQUEST['zeit_von'],$_REQUEST['zeit_bis'],$_REQUEST['raumtyp'],$_REQUEST['anzahl_personen'],isset($_REQUEST['reservierung']),$authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
echo '</div>';
?>
