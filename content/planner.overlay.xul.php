<?php
require_once('../config/vilesci.config.inc.php');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

/*echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentenoverlay.xul.php?xulapp=planner"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.xul.php"?>';*/
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projekttask.overlay.xul.php"?>';
?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../locale/de-AT/planner.dtd"); ?>] -->

<overlay id="PlannerOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/planner.overlay.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>

<vbox id="box-projekt">
    <popupset>
	<popup id="projekttask-tree-popup">
	    <menuitem label="Entfernen" oncommand="LeDelete();" id="projekttask-tree-popup-entf" disabled="false"/>
	</popup>
    </popupset>
    <toolbox>
	<toolbar id="projekttask-nav-toolbar">
	    <toolbarbutton id="projekttask-toolbar-neu" label="Neues Projekt" oncommand="ProjektNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neues Projekt anlegen" />
	    <toolbarbutton id="projekttask-toolbar-del" label="Loeschen" oncommand="LeDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Lehreinheiten löschen"/>
	    <toolbarbutton id="projekttask-toolbar-refresh" label="Aktualisieren" oncommand="LvTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
	    <toolbarbutton id="projekttask-toolbar-lehrauftrag" label="Lehrauftrag" oncommand="LvCreateLehrauftrag()" disabled="false" image="../skin/images/person.gif" tooltiptext="Lehrauftrag ausdrucken" hidden="true"/>
	    <!--<toolbarbutton id="projekttask-toolbar-refresh" label="Load" oncommand="debugload()" disabled="false"/>-->
	</toolbar>
    </toolbox>
    <tree id="tree-projekt" onselect="onProjektSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="../rdf/projektphase.rdf.php?foo=<?php echo time(); ?>" ref="http://www.technikum-wien.at/projektphase/alle-projektphasen"
	enableColumnDrag="true"
    	ondraggesture="nsDragAndDrop.startDrag(event,lvbgrpDDObserver);"
	ondragdrop="nsDragAndDrop.drop(event,verbandtreeDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,verbandtreeDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,verbandtreeDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,verbandtreeDDObserver)"
	>
	<treecols>
	    <treecol id="tree-projekt-bezeichnung" label="Bezeichnung" flex="15" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-oe" label="OE" flex="2" hidden="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-projekt_kurzbz" label="Projekt" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-projekt_phase" label="Phase" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-projekt_phase_id" label="PhaseID" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-titel" label="Titel" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-nummer" label="Nummer" flex="1" hidden="false"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-beginn" label="Beginn" flex="1" hidden="false"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-projekt-ende" label="Ende" flex="1" hidden="false"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#oe_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_phase"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_phase_id"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#titel"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#nummer"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#beginn"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#ende"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
	</template>
    </tree>
</vbox>

<!--<tree id="tree-ort" onselect="onOrtSelect();"
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
</tree>-->

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
			<tab id="tab-projekttask" label="Tasks" />
			<tab id="tab-semester" label="Semesterplan" />
			<tab id="tab-studenten" label="Studenten" />
			<tab id="tab-lfvt" label="Lehrveranstaltung" />
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
			<!--  Projekttask  -->
			<vbox id="box-projekttask" />
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
