<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lehreinheitendetailoverlay.xul.php"?>';
// rdf:null
?>

<!DOCTYPE overlay>

<overlay id="LehreinheitenOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lehreinheitenoverlay.js.php" />
<!--
<script src="chrome://global/content/nsTransferable.js"/>
-->
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lehreinheitenDragnDrop.js.php" />

<vbox id="LehreinheitenEditor" flex="1">
<keyset>
  <key id="lehreinheiten-delete-key" keycode="VK_DELETE" oncommand="LehreinheitenDelete();"/>
  <key id="lehreinheiten-new-key" modifiers="control" key="N" oncommand="LehreinheitenNeu();"/>
</keyset>

<toolbox>
	<toolbar id="toolbar-LehreinheitenEditor">
    	<toolbarbutton id="toolbar-LehreinheitenEditor-neu" label=" Neu" key="lehreinheiten-new-key" oncommand="LehreinheitenNeu();" image="../skin/images/NeuDokument.png" tooltiptext="Neue Lehreinheit anlegen" />
		<toolbarbutton id='toolbar-LehreinheitenEditor-loeschen' label=" Löschen" key="lehreinheiten-delete-key" oncommand="LehreinheitenDelete();" image="../skin/images/DeleteIcon.png" tooltiptext="Lehreinheiten löschen" />		
		<toolbarbutton label=" Neu Laden" oncommand="RefreshLehreinheitenTree()" image="../skin/images/refresh.png" tooltiptext="Liste neu laden" />
	</toolbar>
</toolbox>
<textbox id="textbox-lehreinheiten-ausbildungssemester_id" oninput="LehreinheitenDetailValueChange()" hidden="true"/>

<tree id="tree-liste-lehreinheiten" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null"
		ref="http://www.technikum-wien.at/lehreinheiten/liste"
		onselect="LehreinheitenTreeAuswahl();" flags="dont-build-content"
		enableColumnDrag="true"
        ondraggesture="treeDragGesture(event);" 
        ondragenter="treeDragEnter(event);"
        ondragover="return DragOverContentArea(event);" 
        ondragexit="treeDragExit(event);" 
        ondragdrop="treeDragDrop(event);"
		>
	<treecols>
		<treecol id="tree-liste-lehreinheiten-col-bezeichnung" label="Bezeichnung" flex="2" hidden="false" primary="true"
	    	class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#bezeichnung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
		<treecol id="tree-liste-lehreinheiten-col-studiengang" label="Studiengang" flex="1" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#studiengang_kurzbz" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-studiensemester" label="Studiensemester" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#studiensemester_kurzbz" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-fachbereich" label="Fachbereich" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#fachbereich_bezeichnung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-ausbildungssemester" label="Ausbildungssemester" flex="2" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#ausbildungssemester_kurzbz" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-lehrform" label="Lehrform" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#lehrform_kurzbz" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-gruppe" label="Gruppe" flex="2" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gruppe_kurzbz" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-nummer" label="Nummer" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#nummer" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>	    
	    <treecol id="tree-liste-lehreinheiten-col-kurzbezeichnung" label="Kurzbezeichnung" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#kurzbezeichnung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-semesterwochenstunden" label="Semesterwochenstunden" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#semesterwochenstunden" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-gesamtstunden" label="Gesamtstunden" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gesamtstunden" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-plankostenprolektor" label="Plankosten pro Lektor" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#plankostenprolektor" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-planfaktor" label="Planfaktor" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#planfaktor" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-planlektoren" label="Planlektoren" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#planlektoren" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-raumtyp_bezeichnung" label="Raumtyp" flex="2" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#raumtyp_bezeichnung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-raumtypalternativ_bezeichnung" label="Raumtyp Alternativ" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#raumtypalternativ_bezeichnung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-bemerkungen" label="Bemerkungen" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#bemerkungen" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-wochenrythmus" label="Wochenrythmus" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#wochenrythmus" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-kalenderwoche" label="Kalenderwoche" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#kalenderwoche" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-stundenblockung" label="Stundenblockung" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#stundenblockung" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-koordinator_nachname" label="Koordinator Nachname" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_nachname" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-col-koordinator_vorname" label="Koordinator Vorname" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_vorname" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>	
	    <treecol id="tree-liste-lehreinheiten-col-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#lehreinheit_id" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>    
	    <treecol id="tree-liste-lehreinheiten-col-koordinator_id" label="Koordinator_id" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_id" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>   
   	    <treecol id="tree-liste-lehreinheiten-col-gruppe_id" label="Gruppe_id" flex="2" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gruppe_id" onclick="LehreinheitenTreeSort()"/>
	    <splitter class="tree-splitter"/>

	</treecols>

	<template>
		<rule>
			<treechildren>
				<treeitem container="true" open="true" uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#studiengang_kurzbz"    />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#studiensemester_kurzbz"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#fachbereich_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#ausbildungssemester_kurzbz"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#lehrform_kurzbz"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gruppe_kurzbz"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#nummer"   />		
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#kurzbezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#semesterwochenstunden"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gesamtstunden"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#plankostenprolektor"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#planfaktor"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#planlektoren"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#raumtyp_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#raumtypalternativ_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#bemerkungen"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#wochenrythmus"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#kalenderwoche"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#stundenblockung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_nachname"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_vorname"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#lehreinheit_id"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#koordinator_id"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehreinheiten/rdf#gruppe_id"   />
	 				</treerow>
	 			</treeitem>
	 		</treechildren>
	 	</rule>
  	</template>

</tree>

<splitter id="lehreinheiten-overlay-splitter" collapse="after" persist="state">
	<grippy />
</splitter>

<vbox id="LehreinheitenDetailEditor" persist="height"/>


</vbox>
</overlay>