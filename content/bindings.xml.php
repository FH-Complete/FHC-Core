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
		<xul:textbox maxlength="10" xbl:inherits="disabled, value" size="10" tooltiptext="Format: DD.MM.JJJJ Beispiel: 31.12.2007"/>
	</content>
	<implementation>
		<property name="value" onget="return document.getAnonymousNodes(this)[0].value" >
			<setter>
				<![CDATA[
				document.getAnonymousNodes(this)[0].value = val;
				if(val!='')
				{
					if(CheckDatum(val))
						document.getAnonymousNodes(this)[0].style.backgroundColor="#FFFFFF";
					else
						document.getAnonymousNodes(this)[0].style.backgroundColor="#F46B6B";
				}
				else
				{
					if(!document.getAnonymousNodes(this)[0].disabled)
						document.getAnonymousNodes(this)[0].style.backgroundColor="#FFFFFF";
				}
					
				]]>
			</setter>
		</property>
		<property name="iso" onget="return ConvertDateToISO(document.getAnonymousNodes(this)[0].value)" >
			<setter>
				return false;
			</setter>
		</property>
		<property name="disabled" onget="return document.getAnonymousNodes(this)[0].disabled" >
			<setter>
				document.getAnonymousNodes(this)[0].disabled = val;
			</setter>
		</property>
	</implementation>
	<handlers>
		<handler event="input">
			 <![CDATA[
			var datum = document.getAnonymousNodes(this)[0].value;

			if(CheckDatum(datum))
				document.getAnonymousNodes(this)[0].style.backgroundColor="#FFFFFF";
			else
				document.getAnonymousNodes(this)[0].style.backgroundColor="#F46B6B";
			
			 ]]>
		</handler>
	</handlers>
  </binding>
</bindings>
