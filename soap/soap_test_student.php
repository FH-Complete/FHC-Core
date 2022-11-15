<?php
/* Copyright (C) 2012 Technikum-Wien
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
 * Authors:		Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Test Client für Studierenden Webservice
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');

$method = (isset($_GET['method'])?$_GET['method']:'uid');

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
        <title>SOAP TestClient für Studenten</title>
	</head>
    <body>
	<h1>Studierenden Webservice</h1>
	Liefert Informationen ueber Studierende
	<h2>Funktionen</h2>
	<ul>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=uid'?>">getStudentFromUid</a> - Liefert einen Studierenden anhand der UID</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=matrikelnummer'?>">getStudentFromMatrikelnummer</a> - Liefert einen Studierenden anhand der Matrikelnummer</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=studiengang'?>">getStudentFromStudiengang</a> - Liefert alle Studierende eines Studienganges / Semesters / Verbandes</li>
	</ul>
    <a href ="<?php echo APP_ROOT.'soap/student.wsdl.php'?>">Show WSDL </a>
	<br>
	<h2>Testformular</h2>
    <?php 
        
    if($method =='uid')
    {
        echo'
        <form action="'.$_SERVER["PHP_SELF"].'?method=uid" method="post">
        <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
            <tr>
                <td align="right">Name* :</td>
                <td><input name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Passwort* :</td>
                <td><input name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Student_uid* :</td>
                <td><input name="student_uid" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['student_uid']) ? $_REQUEST['student_uid'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right"></td>
                <td>
                    <input type="submit" value=" Absenden " name="submit_uid">
                    <input type="button" onclick="sendSoap();" value="send Soap">
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
            var soapBody = new SOAPObject("getStudentFromUid");
            var authentifizierung = new SOAPObject("authentifizierung");
            authentifizierung.appendChild(new SOAPObject("username")).val("");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("");

            soapBody.appendChild(new SOAPObject("student_uid")).val("");
            soapBody.appendChild(authentifizierung);

            var sr = new SOAPRequest("getStudentFromUid",soapBody);
            SOAPClient.Proxy="'.APP_ROOT.'/soap/student.soap.php?"+gettimestamp();

            SOAPClient.SendRequest(sr, clb_soapStudentFromUid);
        }

        function clb_soapStudentFromUid(respObj)
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
    else if($method =='matrikelnummer')
    {
        echo'
        <form action="'.$_SERVER["PHP_SELF"].'?method=matrikelnummer" method="post">
        <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
            <tr>
                <td align="right">Name* :</td>
                <td><input name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Passwort* :</td>
                <td><input name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Matrikelnummer* :</td>
                <td><input name="matrikelnummer" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['matrikelnummer']) ? $_REQUEST['matrikelnummer'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right"></td>
                <td>
                    <input type="submit" value=" Absenden " name="submit_matrikelnummer">
                    <input type="button" onclick="sendSoap();" value="send Soap">
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
            var soapBody = new SOAPObject("getStudentFromMatrikelnummer");
            var authentifizierung = new SOAPObject("authentifizierung");
            authentifizierung.appendChild(new SOAPObject("username")).val("");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("");

            soapBody.appendChild(new SOAPObject("matrikelnummer")).val("");
            soapBody.appendChild(authentifizierung);

            var sr = new SOAPRequest("getStudentFromMatrikelnummer",soapBody);
            SOAPClient.Proxy="'.APP_ROOT.'/soap/student.soap.php?"+gettimestamp();

            SOAPClient.SendRequest(sr, clb_soapStudentFromMatrikelnummer);
        }

        function clb_soapStudentFromMatrikelnummer(respObj)
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
    else if($method == 'studiengang')
    {
        echo'
        <form action="'.$_SERVER["PHP_SELF"].'?method=studiengang" method="post">
        <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
            <tr>
                <td align="right">Name* :</td>
                <td><input name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Passwort* :</td>
                <td><input name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Studiengang* :</td>
                <td><input name="studiengang" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['studiengang']) ? $_REQUEST['studiengang'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Ausbildungssemester :</td>
                <td><input name="semester" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Verband :</td>
                <td><input name="verband" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['verband']) ? $_REQUEST['verband'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right">Gruppe :</td>
                <td><input name="gruppe" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['gruppe']) ? $_REQUEST['gruppe'] : "")).'"></td>
            </tr>
            <tr>
                <td align="right"></td>
                <td>
                    <input type="submit" value=" Absenden " name="submit_studiengang">
                    <input type="button" onclick="sendSoap();" value="send Soap">
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
            var soapBody = new SOAPObject("getStudentFromStudiengang");
            var authentifizierung = new SOAPObject("authentifizierung");
            authentifizierung.appendChild(new SOAPObject("username")).val("");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("");

            soapBody.appendChild(new SOAPObject("studiengang")).val("");
            soapBody.appendChild(new SOAPObject("semester")).val("");
            soapBody.appendChild(new SOAPObject("verband")).val("");
            soapBody.appendChild(new SOAPObject("gruppe")).val("");
            soapBody.appendChild(authentifizierung);

            var sr = new SOAPRequest("getStudentFromStudiengang",soapBody);
            SOAPClient.Proxy="'.APP_ROOT.'/soap/student.soap.php?"+gettimestamp();

            SOAPClient.SendRequest(sr, clb_soapStudentFromStudiengang);
        }

        function clb_soapStudentFromStudiengang(respObj)
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
    echo '<a href="index.html">Zurück zur Übersicht</a><br>';
   
if(isset($_REQUEST['submit_matrikelnummer']))
{
	$client = new SoapClient(APP_ROOT."soap/student.wsdl.php?".microtime(true)); 
	
	try
	{
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
		$response = $client->getStudentFromMatrikelnummer($matrikelnummer = $_REQUEST['matrikelnummer'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
}

if(isset($_REQUEST['submit_uid']))
{
	$client = new SoapClient(APP_ROOT."soap/student.wsdl.php?".microtime(true)); 
	
	try
	{
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
		$response = $client->getStudentFromUid($student_uid = $_REQUEST['student_uid'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}

}

if(isset($_REQUEST['submit_studiengang']))
{
    $client = new SoapClient(APP_ROOT."soap/student.wsdl.php?".microtime(true)); 
	
	try
	{
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
		$response = $client->getStudentFromStudiengang($studiengang = $_REQUEST['studiengang'], $semester=$_REQUEST['semester'], $verband=$_REQUEST['verband'], $gruppe=$_REQUEST['gruppe'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
}

class foo{}
 ?>   
</body>
</html>
