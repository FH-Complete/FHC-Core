<?php
	include('../vilesci/config.inc.php');
	header("Content-type: application/vnd.mozilla.xul+xml");
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
?>
<bindings xmlns="http://www.mozilla.org/xbl"
          xmlns:xul="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
		  xmlns:xbl="http://www.mozilla.org/xbl"
		  xmlns:html="http://www.w3.org/1999/xhtml"
		  >

  <binding id="Datum">
  	<content>
		<xul:textbox id="binding-datefield-textbox" maxlength="10" size="10"/>
	</content>
	<implementation>
		<property name="value" onget="return document.getElementById('binding-datefield-textbox').value" >
			<setter>
				return val;
			</setter>
		</property>
	</implementation>
	<handlers>
		<handler event="input">
			 <![CDATA[
			var datum = document.getElementById('binding-datefield-textbox').value;

			if(CheckDatum(datum))
				document.getElementById('binding-datefield-textbox').style.backgroundColor="#FFFFFF";
			else
				document.getElementById('binding-datefield-textbox').style.backgroundColor="#F46B6B";
			
			 ]]>
		</handler>
	</handlers>
  </binding>
</bindings>
