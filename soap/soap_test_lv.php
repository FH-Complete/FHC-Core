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
 * Testclient für Lehrveranstaltung Webservice
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');

$method = (isset($_GET['method'])?$_GET['method']:'studiengang');

$getuid = get_uid(); 
if(!check_lektor($getuid) && !check_student($getuid))
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
        <script type="text/javascript" src="../include/js/jqXMLUtils.js"></script> 
        <title>SOAP TestClient für Lehrveranstaltungen</title>
	</head>
	<body>
	<h1>Lehrveranstaltung Webservice</h1>
	Liefert Informationen über Lehrveranstaltungen
	<h2>Funktionen</h2>
	<ul>
		<li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=studiengang'?>">GetLehrveranstaltungFromStudiengang</a> - Laedt die Lehrveranstaltungen eines Studienganges</li>
        <li><a href ="<?php echo $_SERVER['PHP_SELF'].'?method=id'?>">GetLehrveranstaltungFromId</a> - Laedt eine Lehrveranstaltung anhand der LehrveranstaltungsID</li>
	</ul>
	<br>
    <a href ="<?php echo APP_ROOT.'soap/lehrveranstaltung.wsdl.php'?>">Show WSDL </a>
	<br>
	<h2>Testformular</h2>        
        <?php if($method=='studiengang')
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
                    <td align="right">Studiensemester* :</td>
                    <td><input name="semester" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "")).'"></td>
                </tr>    
                <tr>
                    <td align="right">Ausbildungssemester :</td>
                    <td><input name="aussemester" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['aussemester']) ? $_REQUEST['aussemester'] : "")).'"></td>
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
            authentifizierung.appendChild(new SOAPObject("username")).val("");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("");

            soapBody.appendChild(new SOAPObject("studiengang")).val("");
            soapBody.appendChild(new SOAPObject("semester")).val("");
            soapBody.appendChild(new SOAPObject("ausbildungssemester")).val("");
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
                    <td align="right">Name* :</td>
                    <td><input name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
                </tr>
                <tr>
                    <td align="right">Passwort* :</td>
                    <td><input name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
                </tr>
                <tr>
                    <td align="right">Lehrveranstaltung_id* :</td>
                    <td><input name="lv_id" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['lv_id']) ? $_REQUEST['lv_id'] : "")).'"></td>
                </tr>
                <tr>
                    <td align="right">Studiensemester :</td>
                    <td><input name="semester" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['semester']) ? $_REQUEST['semester'] : "")).'"></td>
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
            authentifizierung.appendChild(new SOAPObject("username")).val("foo");
            authentifizierung.appendChild(new SOAPObject("passwort")).val("bar");

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
        
        echo '<a href="index.html">Zurück zur Übersicht</a><br>';


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
</body>
</html>
