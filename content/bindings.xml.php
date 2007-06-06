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
		<xul:textbox id="binding-datefield-textbox" value="asdf"/>
	</content>
	<implementation>
		<property name="value" onget="return document.getElementById('binding-datefield-textbox').value" >
			<setter>
				return val;
			</setter>
		</property>
	</implementation>
	<handlers>
		<handler event="blur" phase="capturing">
			 <![CDATA[
			var datum = document.getElementById('binding-datefield-textbox').value;
			
			var pattern = /^\d{2}\.\d{2}\.\d{4}$/
					
			if(!pattern.exec(datum))
			{
				alert("Das Datum muss im Format tt.mm.yyyy eingegeben werden!");
				//document.getElementById('binding-datefield-textbox').focus();
				return false;
			}
			 ]]>
		</handler>
	</handlers>
  </binding>
</bindings>
