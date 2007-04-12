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
	
if(isset($_GET['telefonnummer_id']))
	$telefonnummer_id=$_GET['telefonnummer_id'];
else 
	$telefonnummer_id=null;

// rdf:null
?>

<?xml-stylesheet href="chrome://global/skin/global.css" type="text/css"?>

<window id="mitarbeiter-email-dialog" title="Email"		
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="Check()" >
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasold/fasoldoverlay.js.php" />        
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeitertelefonnummerdialog.js.php?telefonnummer_id=<?php echo $telefonnummer_id;?>" />


<textbox id="textbox-mitarbeiter-telefonnummer-person_id" value="<?php echo $person_id;?>" hidden="true"/>
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
    		<label control="menulist-mitarbeiter-telefonnummer-typ" value="Typ:"/>
    		<menulist id="menulist-mitarbeiter-telefonnummer-typ" oncommand="MitarbeiterTelefonnummerValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/telefonnummerntyp.rdf.php"
		              ref="http://www.technikum-wien.at/telefonnummerntyp/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/telefonnummerntyp/rdf#name"
		                         value="rdf:http://www.technikum-wien.at/telefonnummerntyp/rdf#typ"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-telefonnummer-nummer" value="Telefonnummer:"/>
    		<textbox id="textbox-mitarbeiter-telefonnummer-nummer" maxlength="255" oninput="MitarbeiterTelefonnummerValueChange()" value="+43-"/>
    	</row>
    	<row>
    		<label align="end" value="Eingabeformat:"/>
    		<label align="end" value="+43-01-123456"/>
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
			<button label="OK" oncommand="MitarbeiterTelefonnummerSave()"/>
			<button label="Abbrechen" oncommand="window.close()"/>			
		</row>
	</rows>
</grid>
</window>