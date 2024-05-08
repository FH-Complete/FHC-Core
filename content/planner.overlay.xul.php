<?php
require_once('../config/vilesci.config.inc.php');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';


echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projekt.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projektphase.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projekttask.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projektdokument.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/gantt.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/bestellung.overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/ressource.overlay.xul.php"?>';
?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../locale/de-AT/planner.dtd"); ?>] -->

<overlay id="PlannerOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/planner.overlay.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/ressource.js.php"/>

<vbox id="box-projektmenue">
    <popupset>
	<popup id="projekttask-tree-popup">
	    <menuitem label="Entfernen" oncommand="LeDelete();" id="projekttask-tree-popup-entf" disabled="false"/>
	</popup>
    </popupset>
    <toolbox>
	<toolbar id="toolbar-projektmenue">
		<!--
	    <toolbarbutton id="toolbarbutton-projektmenue-neu" label="Neues Projekt" oncommand="ProjektNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neues Projekt anlegen" />
	    <toolbarbutton id="toolbarbutton-projektmenue-del" label="Loeschen" oncommand="ProjektDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Projekt löschen"/>
	    -->
	    <toolbarbutton id="toolbarbutton-projektmenue-refresh" label="Aktualisieren" oncommand="ProjektmenueRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
        <toolbarbutton anonid="toolbarbutton-projektmenue-filter" label="Filter" type="menu">
            <menupopup>
                <menuitem label="Alle Projekte Anzeigen" type="radio" name="filterProjekt" checked="true" oncommand="ProjektmenueRefresh('alle')" tooltiptext="Alle Projekte anzeigen"/>
                <menuitem label="nur aktuelle und kommende Projekte anzeigen" type="radio" name="filterProjekt" oncommand="ProjektmenueRefresh('kommende')" tooltiptext="nur aktuelle und kommende Projekte anzeigen"/>
                <menuitem label="nur aktuelle Projekte anzeigen" type="radio" name="filterProjekt" oncommand="ProjektmenueRefresh('aktuell')" tooltiptext="nur aktuelle Projekte anzeigen"/>
            </menupopup>
        </toolbarbutton>
	</toolbar>
    </toolbox>
    <!-- <?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?foo=<?php echo time(); ?> -->
    <tree id="tree-projektmenue" onselect="treeProjektmenueSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="<?php echo APP_ROOT; ?>rdf/projektphase.rdf.php?foo=<?php echo time(); ?>" ref="http://www.technikum-wien.at/projektphase"
	enableColumnDrag="true"
	ondragdrop="nsDragAndDrop.drop(event,projektTaskDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,projektTaskDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,projektTaskDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,projektTaskDDObserver)"
	>
	<treecols>
	    <treecol id="treecol-projektmenue-bezeichnung" label="Bezeichnung" flex="5" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-oe" label="OE" flex="2" hidden="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-projekt_kurzbz" label="Projekt" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-projekt_phase" label="Phase" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-projekt_phase_id" label="PhaseID" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-titel" label="Titel" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-typ" label="Typ" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-nummer" label="Nummer" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-beginn" label="Beginn" flex="2" hidden="false"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-projektmenue-ende" label="Ende" flex="2" hidden="false"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell src="../skin/images/rdf:http://www.technikum-wien.at/projektphase/rdf#typ^.png" label=" rdf:http://www.technikum-wien.at/projektphase/rdf#bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#oe_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_phase"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projekt_phase_id"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#titel"/>
	           <treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#typ"/>
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

<vbox id="vbox-main">
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs id="tabs-planner-main" orient="horizontal">
			<tab id="tab-projekte" label="Projekte" selected="true" />
			<tab id="tab-projektphase" label="Phasen" />
			<tab id="tab-projekttask" label="Tasks"/>
			<tab id="tab-dokumente" label="Dokumente" />
			<tab id="tab-ressourceauslastung" label="Ressourcen" />
			<tab id="tab-bestellung" label="Bestellungen" />
			<tab id="tab-ganttx" label="Gantt" />
			<tab id="tab-notiz" label="Eigene Notizen" />
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
			<vbox id="box-projekt" />
			<vbox id="box-projektphase" />
			<vbox id="box-projekttask" />
			<vbox id="box-dokumente" />
			<vbox id="box-ressource" />
			<vbox id="box-bestellung" />
			<vbox id="box-ganttx" />
            <vbox id="box-notiz" />
		</tabpanels>
	</tabbox>
</vbox>

<vbox id="box-notiz">
	<box class="Notiz" flex="1" id="box-notizen"/>
</vbox>

<vbox id="box-ressourcemenue">
    <toolbox>
	<toolbar id="toolbar-ressourcemenue">
	    <toolbarbutton id="toolbarbutton-ressourcemenue-neu" label="Neue Ressource" oncommand="RessourceNeu();" disabled="false" image="../skin/images/NeuDokument.png" tooltiptext="Neue Ressource anlegen" />
	    <toolbarbutton id="toolbarbutton-ressourcemenue-del" label="Loeschen" oncommand="RessourceDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Projekt löschen"/>
	    <toolbarbutton id="toolbarbutton-ressourcemenue-refresh" label="Aktualisieren" oncommand="RessourceRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
	</toolbar>
    </toolbox>
    <tree id="tree-ressourcemenue" onselect="treeRessourcemenueSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="rdf:null" ref="http://www.technikum-wien.at/ressource/liste"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,ressourceDDObserver);"
	ondragdrop="nsDragAndDrop.drop(event,ressourceDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,ressourceDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,ressourceDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,ressourceDDObserver)"
	>
	<treecols>
		<treecol id="treecol-ressourcemenue-bezeichnung" label="Bezeichnung" flex="2" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-description" label="Anzeige" flex="2"  hidden="false" />
	    <splitter class="tree-splitter"/>
	   	<treecol id="treecol-ressourcemenue-typ" label="typ" flex="5" hidden ="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-ressource_id" label="ID" flex="5" hidden ="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-beschreibung" label="Beschreibung" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-mitarbeiter_uid" label="MitarbeiterUID" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-student_uid" label="StudentInUID" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-betriebsmittel_id" label="BetriebsmittelID" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="treecol-ressourcemenue-firma_id" label="FirmaID" flex="1" hidden="true"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#rdf_description"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#typ"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#ressource_id"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#beschreibung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#mitarbeiter_uid"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#student_uid"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#betriebsmittel_id"/>
	           <treecell label="rdf:http://www.technikum-wien.at/ressource/rdf#firma_id"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
	</template>
    </tree>
</vbox>

</overlay>
