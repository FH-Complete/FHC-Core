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
	
if(isset($_GET['adress_id']))
	$adress_id=$_GET['adress_id'];
else 
	$adress_id=null;

// rdf:null
?>

<?xml-stylesheet href="chrome://global/skin/global.css" type="text/css"?>

<window id="mitarbeiter-adressen-dialog" title="Adressen"		
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasold/fasoldoverlay.js.php" />        
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiteradressendialog.js.php?adress_id=<?php echo $adress_id;?>" />


<textbox id="textbox-mitarbeiter-adressen-person_id" value="<?php echo $person_id;?>" hidden="true"/>
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
    		<label align="end" control="menulist-mitarbeiter-adresse-adresstyp" value="Adresstyp:"/>
	    	<menulist id="menulist-mitarbeiter-adressen-adresstyp"
    		          oncommand="MitarbeiterAdressenValueChange();">
                <menupopup>
      				<menuitem label="  ---  " value="0"/>
      				<menuitem label="Firmensitz" value="1"/>
      				<menuitem label="Hauptwohnsitz" value="2" selected="true"/>
      				<menuitem label="Nebenwohnsitz" value="3"/>
    			</menupopup>
    		</menulist>
    	</row>
    	<row>
    		<label align="end" control="menulist-mitarbeiter-adressen-nation" value="Nation:"/>
    		<menulist id="menulist-mitarbeiter-adressen-nation" oncommand="MitarbeiterAdressenValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/nation.rdf.php"
		              ref="http://www.technikum-wien.at/nation/alle" value='A'>
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext" 
		                         value="rdf:http://www.technikum-wien.at/nation/rdf#code"/>
		            </menupopup>
		         </template>
		    </menulist>    
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-adressen-strasse" value="Strasse:"/>
    		<textbox id="textbox-mitarbeiter-adressen-strasse" maxlength="255" oninput="MitarbeiterAdressenValueChange()"/>
    	</row>
    	<row>
    		<label align="end" control="menulist-mitarbeiter-adressen-plz" value="Plz:"/>
    		<hbox><textbox id="textbox-mitarbeiter-adressen-plz" size="10" maxlength="10" oninput="MitarbeiterAdressenValueChange()"/><spacer /></hbox>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-adressen-gemeinde" value="Gemeinde:"/>
    		<textbox id="textbox-mitarbeiter-adressen-gemeinde" maxlength="255" oninput="MitarbeiterAdressenValueChange()"/>
    	</row>
    	<row>
    		<label align="end" control="textbox-mitarbeiter-adressen-ort" value="Ort:"/>
    		<textbox id="textbox-mitarbeiter-adressen-ort" maxlength="255" oninput="MitarbeiterAdressenValueChange()"/>
    	</row>
    	<row>
    		<spacer />
    		<hbox>
    			<checkbox label="Zustelladresse" id="checkbox-mitarbeiter-adressen-zustelladresse" checked="true" onclick="MitarbeiterAdressenValueChange()"/>
    			<checkbox label="BIS-Meldeadresse" id="checkbox-mitarbeiter-adressen-bismeldeadresse" checked="true" onclick="MitarbeiterAdressenValueChange()"/>
    		</hbox>
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
			<button label="OK" oncommand="MitarbeiterAdressenSave()"/>
			<button label="Abbrechen" oncommand="window.close()"/>			
		</row>
	</rows>
</grid>
</window>