<?php
require_once('../config/vilesci.config.inc.php');
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<script type="text/javascript" language="JavaScript" src="../include/js/jquery.js"></script>
		<script type="text/javascript" language="JavaScript" src="../include/js/jqXMLUtils.js"></script>
		<script type="text/javascript" language="JavaScript" src="../include/js/jqSOAPClient.js"></script>


		<script type="text/javascript">
		function send()
		{
			var soapBody = new SOAPObject("saveProjektDaten");
			soapBody.appendChild(new SOAPObject("projekt_kurzbz")).val('test5');
			soapBody.appendChild(new SOAPObject("oe_kurzbz")).val('etw');
			var sr = new SOAPRequest("saveProjektDaten",soapBody);

			SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/projekt.soap.php";
			SOAPClient.SendRequest(sr, clb_insert);
		}

		function clb_insert(respObj)
		{
			alert('obj:'+respObj);
			try
			{
				var msg = respObj.Body[0].saveProjektDatenResponse[0].message[0].Text;
				alert('Antwort: '+msg);
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
<input type="button" onclick="send()" value="Sende Request">
</body>
</html>
