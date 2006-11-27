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
	
if(isset($_GET['bankverbindung_id']))
	$bankverbindung_id=$_GET['bankverbindung_id'];
else 
	$bankverbindung_id=null;

// rdf:null
?>

<?xml-stylesheet href="chrome://global/skin/global.css" type="text/css"?>

<window id="mitarbeiter-bankverbindung-dialog" title="Email"		
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />        
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiterbankverbindungdialog.js.php?bankverbindung_id=<?php echo $bankverbindung_id;?>" />


<textbox id="textbox-mitarbeiter-bankverbindung-person_id" value="<?php echo $person_id;?>" hidden="true"/>
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
    		<label align="end" control="menulist-mitarbeiter-bankverbindung-typ" value="Konto Typ:"/>
	    	<menulist id="menulist-mitarbeiter-bankverbindung-typ"
    		          oncommand="MitarbeiterBankverbindungValueChange();">
                <menupopup>
      				<menuitem label="Privatkonto" value="1" selected="true"/>
      				<menuitem label="Firmenkonto" value="2"/>
    			</menupopup>
    		</menulist>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-name" value="Bank Bezeichnung:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-name" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-anschrift" value="Bank Anschrift:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-anschrift" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>    		
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-blz" value="BLZ:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-blz" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>    		
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-kontonr" value="Kontonummer:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-kontonr" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>    		
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-bic" value="BIC:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-bic" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>    		
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-bankverbindung-iban" value="IBAN:"/>
    		<textbox id="textbox-mitarbeiter-bankverbindung-iban" maxlength="255" oninput="MitarbeiterBankverbindungValueChange()"/>    		
    	</row>
    	<row>
    		<spacer />
    		<checkbox label="Verrechnungskonto" id="checkbox-mitarbeiter-bankverbindung-verrechnungskonto" checked="true" oncommand="MitarbeiterBankverbindungValueChange()"/>
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
			<button label="OK" oncommand="MitarbeiterBankverbindungSave()"/>
			<button label="Abbrechen" oncommand="window.close()"/>			
		</row>
	</rows>
</grid>
</window>