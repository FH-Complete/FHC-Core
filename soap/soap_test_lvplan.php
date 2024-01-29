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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Test Client fuer LVPlan Webservice
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php'); 
require_once('../include/basis_db.class.php');

$method = (isset($_GET['method'])?$_GET['method']:'getLVPlanFromUser');

$getuid = get_uid(); 
if(!check_lektor($getuid) && !check_student($getuid))
	die('Sie haben keine Berechtigung für diese Seite'); 
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
        <script type="text/javascript" src="../include/js/jqXMLUtils.js"></script> 
        <script type="text/javascript" src="../include/js/jqSOAPClient.js"></script> 

        <title>SOAP TestClient für LVPlan</title>
	</head>
	<body>
	<h1>LVPlan WebService</h1>
	Webservice für die Abfrage des LVPlans
	<h2>Funktionen</h2>
	<ul>
		<li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getLVPlanFromUser'?>">getLVPlanFromUser</a> - Laedt den persönlichen LVPlan eines Benutzers</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getLVPlanFromLV'?>">getLVPlanFromLV</a> - Laedt den LVPlan einer Lehrveranstaltung</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getLVPlanFromStg'?>">getLVPlanFromStg</a> - Laedt den LVPlan eines Studiengangs</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getLVPlanFromOrt'?>">getLVPlanFromOrt</a> - Laedt den LVPlan eines Orts/Raumes</li>
	</ul>
	<a href ="<?php echo APP_ROOT.'soap/lvplan.wsdl.php'?>">Show WSDL</a></li>
	<br>
    <h2>Testformular</h2> 
        <?php 
        if($method=='getLVPlanFromUser')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getLVPlanFromUser" method="post">
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
	                    <td align="right">Von* :</td>
	                    <td><input id="von" name="von" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['von']) ? $_REQUEST['von'] : "")).'"></td>
	                </tr>	
	                <tr>
	                    <td align="right">Bis* :</td>
	                    <td><input id="bis" name="bis" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['bis']) ? $_REQUEST['bis'] : "")).'"></td>
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
	        	uid = document.getElementById("uid").value;
				von = document.getElementById("von").value;
				bis = document.getElementById("bis").value;
	        	
	            var soapBody = new SOAPObject("getLVPlanFromUser");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("uid")).val(uid);
				soapBody.appendChild(new SOAPObject("von")).val(von);
				soapBody.appendChild(new SOAPObject("bis")).val(bis);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("getLVPlanFromUser",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/lvplan.soap.php?"+gettimestamp();
	
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
        elseif($method=='getLVPlanFromLV')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getLVPlanFromLV" method="post">
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
	                    <td align="right">LehrveranstaltungID*:</td>
	                    <td><input id="lehrveranstaltung_id" name="lehrveranstaltung_id" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['lehrveranstaltung_id']) ? $_REQUEST['lehrveranstaltung_id'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">StudiensemesterKurzbz* :</td>
	                    <td><input id="stsem" name="stsem" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['stsem']) ? $_REQUEST['stsem'] : "")).'"></td>
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
	        	lehrveranstaltung_id = document.getElementById("lehrveranstaltung_id").value;
				stsem = document.getElementById("stsem").value;
	        		        	
	            var soapBody = new SOAPObject("getLVPlanFromLV");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("lehrveranstaltung_id")).val(lehrveranstaltung_id);
	            soapBody.appendChild(new SOAPObject("studiensemester_kurzbz")).val(stsem);
	            soapBody.appendChild(authentifizierung);
	            	
	            var sr = new SOAPRequest("getLVPlanFromLV",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/lvplan.soap.php?"+gettimestamp();
	
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
		elseif($method=='getLVPlanFromStg')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getLVPlanFromStg" method="post">
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
	                    <td align="right">StudiengangKZ :</td>
	                    <td><input id="studiengang_kz" name="studiengang_kz" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['studiengang_kz']) ? $_REQUEST['studiengang_kz'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Semester :</td>
	                    <td><input id="semester" name="semester" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Verband :</td>
	                    <td><input id="verband" name="verband" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['verband']) ? $_REQUEST['verband'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Gruppe :</td>
	                    <td><input id="gruppe" name="gruppe" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['gruppe']) ? $_REQUEST['gruppe'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Gruppe_kurzbz :</td>
	                    <td><input id="gruppe_kurzbz" name="gruppe_kurzbz" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['gruppe_kurzbz']) ? $_REQUEST['gruppe_kurzbz'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Von* :</td>
	                    <td><input id="von" name="von" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['von']) ? $_REQUEST['von'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Bis* :</td>
	                    <td><input id="bis" name="bis" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['bis']) ? $_REQUEST['bis'] : "")).'"></td>
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
	        	studiengang_kz = document.getElementById("studiengang_kz").value;
	        	semester = document.getElementById("semester").value;
	        	verband = document.getElementById("verband").value;
	        	gruppe = document.getElementById("gruppe").value;
	        	gruppe_kurzbz = document.getElementById("gruppe_kurzbz").value;
	        	von = document.getElementById("von").value;
				bis = document.getElementById("bis").value;  
	        	
	            var soapBody = new SOAPObject("getLVPlanFromStg");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("studiengang_kz")).val(studiengang_kz);
	            soapBody.appendChild(new SOAPObject("semester")).val(semester);
	            soapBody.appendChild(new SOAPObject("verband")).val(verband);
	            soapBody.appendChild(new SOAPObject("gruppe")).val(gruppe);
	            soapBody.appendChild(new SOAPObject("gruppe_kurzbz")).val(gruppe_kurzbz);
	            soapBody.appendChild(new SOAPObject("von")).val(von);
	            soapBody.appendChild(new SOAPObject("bis")).val(bis);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("getLVPlanFromStg",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/lvplan.soap.php?"+gettimestamp();
	
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
		elseif($method=='getLVPlanFromOrt')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getLVPlanFromOrt" method="post">
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
	                    <td align="right">Ort_kurzbz*:</td>
	                    <td><input id="ort_kurzbz" name="ort_kurzbz" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Von* :</td>
	                    <td><input id="von" name="von" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['von']) ? $_REQUEST['von'] : "")).'"></td>
	                </tr>	
	                <tr>
	                    <td align="right">Bis* :</td>
	                    <td><input id="bis" name="bis" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['bis']) ? $_REQUEST['bis'] : "")).'"></td>
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
				von = document.getElementById("von").value;
				bis = document.getElementById("bis").value;
	        		        	
	            var soapBody = new SOAPObject("getLVPlanFromOrt");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("ort_kurzbz")).val(ort_kurzbz);
	            soapBody.appendChild(new SOAPObject("von")).val(von);
	            soapBody.appendChild(new SOAPObject("bis")).val(bis);
	            soapBody.appendChild(authentifizierung);
	            	
	            var sr = new SOAPRequest("getLVPlanFromOrt",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/lvplan.soap.php?"+gettimestamp();
	
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
        echo '<a href="index.html">Zurück zur Übersicht</a><br>';
        
echo '<div id="output">';
class foo {};

if(isset($_REQUEST['submit']) && $_GET['method']=='getLVPlanFromUser')
{
	$client = new SoapClient(APP_ROOT."/soap/lvplan.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getLVPlanFromUser($_REQUEST['uid'], $_REQUEST['von'], $_REQUEST['bis'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='getLVPlanFromLV')
{
	$client = new SoapClient(APP_ROOT."/soap/lvplan.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];

        $response = $client->getLVPLanFromLV($_REQUEST['lehrveranstaltung_id'], $_REQUEST['stsem'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='getLVPlanFromStg')
{
	$client = new SoapClient(APP_ROOT."/soap/lvplan.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
		$studiengang_kz = $_REQUEST['studiengang_kz'];
		$semester = $_REQUEST['semester'];
		$verband = $_REQUEST['verband'];
		$gruppe = $_REQUEST['gruppe'];
		$gruppe_kurzbz = $_REQUEST['gruppe_kurzbz'];
		$von = $_REQUEST['von'];
		$bis = $_REQUEST['bis'];

        $response = $client->getLVPlanFromStg($studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz, $von, $bis,$authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
}
if(isset($_REQUEST['submit']) && $_GET['method']=='getLVPlanFromOrt')
{
	$client = new SoapClient(APP_ROOT."/soap/lvplan.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];

        $response = $client->getLVPLanFromOrt($_REQUEST['ort_kurzbz'], $_REQUEST['von'], $_REQUEST['bis'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
echo '</div>';
?>
