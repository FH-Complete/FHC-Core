<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';

include('../vilesci/config.inc.php');

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentenoverlay.xul.php?xulapp=tempus"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-semester-overlay.xul.php"?>';
/*echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';*/

?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../locale/de-AT/tempus.dtd"); ?>] -->

<overlay id="TempusOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/tempusoverlay.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>

<tree id="tree-verband" onmouseup="onVerbandSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,lvbgrpDDObserver);"
	datasources="../rdf/lehrverbandsgruppe.rdf.php?prestudent=false" ref="http://www.technikum-wien.at/lehrverbandsgruppe/alle-verbaende"
	ondragdrop="nsDragAndDrop.drop(event,verbandtreeDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,verbandtreeDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,verbandtreeDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,verbandtreeDDObserver)"
	>
	<treecols>
	    <treecol id="bez" label="Bezeichnung" flex="15" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="stg" label="STG" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="stg_kz" label="KZ" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="sem" label="Sem" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="ver" label="Ver" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="grp" label="Grp" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="gruppe" label="SpzGruppe" flex="1" hidden="true"/>
	    <treecol id="typ" label="Typ" flex="1" persist="hidden, width, ordinal" hidden="true"/>
		<splitter class="tree-splitter"/>
	    <treecol id="stsem" label="StSem" flex="1" persist="hidden, width, ordinal" hidden="true"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#name"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stg"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stg_kz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#sem"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#ver"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#grp"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#gruppe"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#typ"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stsem"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-ort" onselect="onOrtSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="../rdf/ort.rdf.php" ref="http://www.technikum-wien.at/ort/alle-orte">
	<treecols>
	    <treecol id="raumtyp" label="Raumtyp" flex="2" primary="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="ort_kurzbz" label="Raum" flex="4" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="hierarchie" label="HI" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	     <treecol id="ort_bezeichnung" label="Bezeichnung" flex="3" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="max_person" label="MaxP" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="raumtypen" label="Raumtypen" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="stockwerk" label="Stockwerk" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="planbezeichnung" label="Planbezeichnung" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#raumtyp"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#ort_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#hierarchie"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#ort_bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#max_person"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#raumtypen"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#stockwerk"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#planbezeichnung"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>
<!--
<tree id="tree-lektor" onselect="onLektorSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,mitarbeiterDDObserver);"
	datasources="../rdf/mitarbeiter.rdf.php" ref="http://www.technikum-wien.at/mitarbeiter/_alle">
	<treecols>
	    <treecol id="kurzbz" label="Kuerzel" flex="2" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="nachname" label="Nachname" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="vornamen" label="Vornamen" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="titel" label="Titel" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="uid" label="UID" flex="1" hidden="true"/>
	</treecols>

	<template>
		<rule>
		<treechildren>
	       	<treeitem uri="rdf:*">
	         	<treerow>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
	         	</treerow>
	    	</treeitem>
	    </treechildren>
	    </rule>
  	</template>
</tree>
-->
<tree id="tree-lektor" onmouseup="onLektorSelect(event);"
	seltype="multi" hidecolumnpicker="false" flex="1"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,mitarbeiterDDObserver);"
	ondragdrop="nsDragAndDrop.drop(event,LektorFunktionDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,LektorFunktionDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,LektorFunktionDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,LektorFunktionDDObserver)"
	datasources="rdf:null" ref="http://www.technikum-wien.at/mitarbeiter/liste"
	context="fasoverlay-lektor-tree-popup"
	>
	<treecols>
	    <treecol id="kurzbz" label="Kuerzel" flex="2" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="nachname" label="Nachname" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="vorname" label="Vorname" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="titel" label="Titel" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="uid" label="UID" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="studiengang_kz" label="Studiengangkz" flex="1" hidden="true"/>
	</treecols>

	<template>
		<rule>
		<treechildren>
	       	<treeitem uri="rdf:*">
	         	<treerow>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#studiengang_kz"/>
	         	</treerow>
	    	</treeitem>
	    </treechildren>
	    </rule>
  	</template>
</tree>

<vbox id="vbox-main">
<popupset>
		<popup id="fasoverlay-lektor-tree-popup">
			<menuitem label="Mail senden" oncommand="LektorFunktionMail();" />
			<menuseparator />
			<menuitem label="Entfernen" oncommand="LektorFunktionDel();" />			
		</popup>
</popupset>
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs orient="horizontal">
			<tab id="tab-week" label="Wochenplan" />
			<tab id="tab-semester" label="Semesterplan" />
			<tab id="tab-studenten" label="Studenten" />
			<tab id="tab-lfvt" label="Lehrveranstaltung" />
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
			<!--  Wochenplan  -->
			<hbox id="hboxTimeTableWeek" />
			<!--  Semesterplan  -->
			<vbox id="vboxTimeTableSemester" />
			<!--  Studenten  -->
			<vbox id="studentenEditor" />
			<!-- Lehrfachverteilung -->
            <vbox id="LehrveranstaltungEditor" />
		</tabpanels>
	</tabbox>
</vbox>

</overlay>