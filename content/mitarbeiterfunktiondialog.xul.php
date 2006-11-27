<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

if(isset($_GET['mitarbeiter_id']))
	$mitarbeiter_id = $_GET['mitarbeiter_id'];
else
	$mitarbeiter_id=null;
	
if(isset($_GET['funktion_id']))
	$funktion_id=$_GET['funktion_id'];
else 
	$funktion_id=null;
if(isset($_GET['MitarbeiterDetailStudiensemester_id']))
	$stsem_id=$_GET['MitarbeiterDetailStudiensemester_id'];
else 
	$stsem_id=null;
// rdf:null
?>

<?xml-stylesheet href="chrome://global/skin/global.css" type="text/css"?>

<window id="mitarbeiter-funktion-dialog" title="Email"		
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="Check()">
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />        
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiterfunktiondialog.js.php?funktion_id=<?php echo $funktion_id;?>" />


<textbox id="textbox-mitarbeiter-funktion-mitarbeiter_id" value="<?php echo $mitarbeiter_id;?>" hidden="true"/>
<textbox id="textbox-mitarbeiter-funktion-stsem_id" value="<?php echo $stsem_id;?>" hidden="true"/>
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
    		<label align="end" control="menulist-mitarbeiter-funktion-erhalter" value="Erhalter:"/>
    		<menulist id="menulist-mitarbeiter-funktion-erhalter"
			    		          oncommand="treeFunktionValueChange();">
			                <menupopup>
			      				<menuitem label="Technikum Wien" value="1"/>
			    			</menupopup>
			</menulist>
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-studiensemester" value="Studiensemester:"/>
    		<menulist id="menulist-mitarbeiter-funktion-studiensemester" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/studiensemester.rdf.php"
		              ref="http://www.technikum-wien.at/studiensemester/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
		                         value="rdf:http://www.technikum-wien.at/studiensemester/rdf#studiensemester_id"/>
		            </menupopup>
		         </template>
		    </menulist>    		
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-funktion" value="Funktion:"/>
    		<menulist id="menulist-mitarbeiter-funktion-funktion" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/funktion_id.rdf.php"
		              ref="http://www.technikum-wien.at/funktion_id/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/funktion_id/rdf#bezeichnung"
		                         value="rdf:http://www.technikum-wien.at/funktion_id/rdf#funktion"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>    	
    	<row>
    		<label control="menulist-mitarbeiter-funktion-studiengang" value="Studiengang:"/>
    		<menulist id="menulist-mitarbeiter-funktion-studiengang" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/studiengang.rdf.php"
		              ref="http://www.technikum-wien.at/studiengang/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/studiengang/rdf#name"
		                         value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_id"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-fachbereich" value="Fachbereich:"/>
    		<menulist id="menulist-mitarbeiter-funktion-fachbereich" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/fachbereich.rdf.php"
		              ref="http://www.technikum-wien.at/fachbereich/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/fachbereich/rdf#name"
		                         value="rdf:http://www.technikum-wien.at/fachbereich/rdf#fachbereich_id"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-beschart1" value="Beschäftigungsart 1:"/>
    		<menulist id="menulist-mitarbeiter-funktion-beschart1" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/beschaeftigungsart1.rdf.php"
		              ref="http://www.technikum-wien.at/beschaeftigungsart1/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/beschaeftigungsart1/rdf#bezeichnung"
                 				value="rdf:http://www.technikum-wien.at/beschaeftigungsart1/rdf#ba1code"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-beschart2" value="Beschäftigungsart 2:"/>
    		<menulist id="menulist-mitarbeiter-funktion-beschart2"
			    		          oncommand="treeFunktionValueChange();">
			                <menupopup>
			      				<menuitem label="befristet" value="1"/>
			      				<menuitem label="unbefristet" value="2" selected="true"/>
			    			</menupopup>
			</menulist>
    	</row>
    	<row>
    		<label align="end" control="menulist-mitarbeiter-funktion-ausmass" value="Ausmass:"/>
    		<menulist id="menulist-mitarbeiter-funktion-ausmass" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/ausmass.rdf.php"
		              ref="http://www.technikum-wien.at/ausmass/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/ausmass/rdf#bezeichnung"
                 				value="rdf:http://www.technikum-wien.at/ausmass/rdf#id"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<label control="menulist-mitarbeiter-funktion-verwendung" value="Verwendung:"/>
    		<menulist id="menulist-mitarbeiter-funktion-verwendung" oncommand="MitarbeiterFunktionValueChange();"
    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/verwendung.rdf.php"
		              ref="http://www.technikum-wien.at/verwendung/alle">
		         <template>
		            <menupopup>
		               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/verwendung/rdf#bezeichnung"
                 				value="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendungcode"/>
		            </menupopup>
		         </template>
		    </menulist>
    	</row>
    	<row>
    		<checkbox label="hauptberuflich" id="checkbox-mitarbeiter-funktion-hauptberuflich" checked="false" onclick="MitarbeiterFunktionHauptberuflichChange()"/>
    		<hbox flex="1">
    			<label control="menulist-mitarbeiter-funktion-hauptberuf" value="Hauptberuf: "/>
				<menulist id="menulist-mitarbeiter-funktion-hauptberuf" oncommand="MitarbeiterFunktionValueChange();"
	    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/hauptberuf.rdf.php"
			              ref="http://www.technikum-wien.at/hauptberuf/alle" flex="1" disabled="false">
			         <template>
			            <menupopup>
			               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/hauptberuf/rdf#bezeichnung"
			               			value ="rdf:http://www.technikum-wien.at/hauptberuf/rdf#hauptberuf_id" />
			            </menupopup>
			         </template>
			    </menulist>
			</hbox>
    	</row>
    	<row>
    		<checkbox label="Entwicklungsteam" id="checkbox-mitarbeiter-funktion-entwicklungsteam" checked="false" onclick="MitarbeiterFunktionEntwicklungsteamChange()"/>
    		<hbox flex="1">
    			<label control="menulist-mitarbeiter-funktion-qualifikation" value="Qualifikation:"/>
				<menulist id="menulist-mitarbeiter-funktion-qualifikation" oncommand="MitarbeiterFunktionValueChange();"
	    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/qualifikation.rdf.php"
			              ref="http://www.technikum-wien.at/qualifikation/alle" flex="1" disabled="true">
			         <template>
			            <menupopup>
			               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/qualifikation/rdf#bezeichnung"
	                 				value="rdf:http://www.technikum-wien.at/qualifikation/rdf#qualifikation_id"/>
			            </menupopup>
			         </template>
			    </menulist>
			</hbox>
    	</row>
		<row>
    		<label align="end" control="textbox-mitarbeiter-funktion-beschreibung" value="Beschreibung:"/>
    		<textbox id="textbox-mitarbeiter-funktion-beschreibung" maxlength="255" oninput="MitarbeiterFunktionValueChange()"/>
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
			<button label="OK" oncommand="MitarbeiterFunktionSave()"/>
			<button label="Abbrechen" oncommand="window.close()"/>			
		</row>
	</rows>
</grid>
</window>