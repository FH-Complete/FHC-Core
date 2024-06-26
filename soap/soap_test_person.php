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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 * Test Client fuer Person Webservice
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php'); 
require_once('../include/basis_db.class.php');

$db = new basis_db();
$method = (isset($_GET['method'])?$_GET['method']:'getPersonFromUID');

$getuid = get_uid(); 
if(!check_lektor($getuid) && !check_student($getuid))
	die('Sie haben keine Berechtigung für diese Seite'); 
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
        <script type="text/javascript" src="../include/js/jqXMLUtils.js"></script> 
        <title>SOAP TestClient für Personen</title>
	</head>
	<body>
	<h1>Person Webservice</h1>
	Liefert Informationen über Personen
	<h2>Funktionen</h2>
	<ul>
		<li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getPersonFromUID'?>">getPersonFromUID</a> - Personendaten anhand der UID laden</li>
                <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=searchPerson'?>">searchPerson</a> - Personendaten anhand von Vorname, Nachname oder UID suchen</li>
        </ul>
	<br>
        <a href ="<?php echo APP_ROOT.'soap/person.wsdl.php'?>">Show WSDL </a>
	<br>    
	<h2>Testformular</h2>
        <?php 
        if($method=='getPersonFromUID')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getPersonFromUID" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username* :</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort* :</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">UID* :</td>
	                    <td><input id="uid" name="uid" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['uid']) ? $_REQUEST['uid'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
	                    </td>
	                </tr>
	            </table>
            </form>
            
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
	        	uid = document.getElementById("uid").value;
	        	
	            var soapBody = new SOAPObject("getPersonFromUID");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("uid")).val(uid);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("getPersonFromUID",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/person.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);                
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre>";
	                
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
        elseif($method=='searchPerson')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=searchPerson" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username* :</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort* :</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
	                </tr>
                        <tr>
	                    <td align="right">Suchbegriff* :</td>
	                    <td><input id="searchItems" name="searchItems" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['searchItems']) ? $_REQUEST['searchItems'] : "")).'"></td>
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
	       		
	            var soapBody = new SOAPObject("searchPerson");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	            soapBody.appendChild(authentifizierung);
	            
	
	            var sr = new SOAPRequest("searchPerson",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/person.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre>";
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
    echo '<a href="index.html">Zurück zur Übersicht</a><br>';
        
echo '<div id="output">';
class foo {};

if(isset($_REQUEST['submit']) && $_GET['method']=='getPersonFromUID')
{
	$client = new SoapClient(APP_ROOT."/soap/person.wsdl.php?".microtime(true));
        
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getPersonFromUID($_REQUEST['uid'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='searchPerson')
{
	$client = new SoapClient(APP_ROOT."/soap/person.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->searchPerson($_REQUEST['searchItems'],$authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}	 
}

echo '</div>';
?>
