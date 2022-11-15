<?php 
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');

$uid = get_uid();
if(!check_lektor($uid))
	die('Sie haben keine Berechtigung für diese Seite.');
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
	
	<title>Test-Client</title>
	
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
			var soapBody = new SOAPObject("myTest");
			soapBody.appendChild(new SOAPObject("foo")).val('foo');
			var sr = new SOAPRequest("myTest",soapBody);
			
			SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/test.soap.php?"+gettimestamp();
			SOAPClient.SendRequest(sr, clb_response);
		}
		function clb_response(respObj)
		{
			try
			{
			    var msg = respObj.Body[0].myTestResponse[0].message[0].Text;
			    alert(msg);
			}
			catch(e)
			{
				var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
				alert('Fehler: '+fehler);
			}
		}
	</script>
</head>
<body>
	<h1>Testclient für Webservices</h1>
	
	<form action="test_client.php" method="post">
		<input type="submit" value="Test PHP" name="submit">
		<input type="button" onclick="sendSoap();" value="Test JS">
	</form>

<?php 

if(isset($_REQUEST['submit']))
{
	$url = APP_ROOT."soap/test.wsdl.php?".microtime(true);
	$client = new SoapClient($url);
	
	echo 'URL: '.$url;
	echo '<br><br>';
	try
	{
		$response = $client->myTest('foo');
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: ".$fault->faultcode.", faultstring: ".$fault->faultstring.")", E_USER_ERROR;
	}
	 
}

?>
</body>
</html>
