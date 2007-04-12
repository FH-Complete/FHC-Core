<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id=null;
	
if(isset($_GET['email_id']))
	$email_id=$_GET['email_id'];
else 
	$email_id=null;

// rdf:null
?>

<?xml-stylesheet href="chrome://global/skin/global.css" type="text/css"?>

<window id="mitarbeiter-email-dialog" title="Email"		
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasold/fasoldoverlay.js.php" />        
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiteremaildialog.js.php?email_id=<?php echo $email_id;?>" />


<textbox id="textbox-mitarbeiter-email-person_id" value="<?php echo $person_id;?>" hidden="true"/>
<grid align="end" flex="1"
		 flags="dont-build-content"
		enableColumnDrag="true"	style="margin:0px;"
		>
	<columns>
		<column />
		<column flex="1"/>
	</columns>
	
	<rows>
    	<row>
    		<label align="end" control="menulist-mitarbeiter-email-name" value="Art:"/>
	    	<menulist id="menulist-mitarbeiter-email-name"
    		          oncommand="MitarbeiterEmailValueChange();">
                <menupopup>
      				<menuitem label="Private Emailadresse" value="1" selected="true"/>
      				<menuitem label="Emailadresse in der Firma" value="2"/>
      				<menuitem label="Emailadresse an der Fachhochschule" value="3"/>
      				
    			</menupopup>
    		</menulist>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-email-email" value="Emailadresse:"/>
    		<textbox id="textbox-mitarbeiter-email-email" maxlength="255" oninput="MitarbeiterEmailValueChange()"/>
    	</row>
    	<row>
    		<spacer />
   			<checkbox label="Zustelladresse" id="checkbox-mitarbeiter-email-zustelladresse" checked="true" onclick="MitarbeiterEmailValueChange()"/>
    	</row>
    </rows>
</grid>
<grid align="end" flex="1"
		 flags="dont-build-content"
		enableColumnDrag="true"	style="margin:0px;"
		>
	<columns>
		<column flex="5"/>
		<column flex="1"/>
		<column flex="1"/>
	</columns>
	<rows>
		<row>
			<spacer />
			<button label="OK" oncommand="MitarbeiterEmailSave()"/>
			<button label="Abbrechen" oncommand="window.close()"/>			
		</row>
	</rows>
</grid>
</window>