<?php
require_once('../config/vilesci.config.inc.php');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentenoverlay.xul.php?xulapp=tempus"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-semester-overlay.xul.php"?>';
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
		<splitter class="tree-splitter"/>
	    <treecol id="orgform" label="Organisationsform" flex="1" persist="hidden, width, ordinal" hidden="true"/>
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
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#orgform"/>
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
	    <treecol id="raumtyp" label="Raumtyp" flex="3" primary="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="ort_kurzbz" label="Raum" flex="4" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="hierarchie" label="HI" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	     <treecol id="ort_bezeichnung" label="Bezeichnung" flex="3" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="max_person" label="MP" tooltiptext="Max. Personenanzahl" flex="1" hidden="false" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="raumtypen" label="Raumtypen" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="stockwerk" label="Stockwerk" flex="1" hidden="true" persist="hidden, width, ordinal"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="planbezeichnung" label="Planbezeichnung" flex="1" hidden="true" persist="hidden, width, ordinal"/>
        <splitter class="tree-splitter"/>
        <treecol id="arbeitsplaetze" label="AP" tooltiptext="Arbeitsplätze" flex="1" hidden="false" persist="hidden, width, ordinal"/>
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
	           <treecell label="rdf:http://www.technikum-wien.at/ort/rdf#arbeitsplaetze"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<vbox id="vbox-lektor">
	<hbox>
<!--		<spacer flex="1" />-->
		<toolbox>
			<toolbar id="toolbarLektorTreeFilter" tbautostretch="always" persist="collapsed">
				<toolbarbutton id="toolbarbuttonLektorTreeRefresh"
							   image="../skin/images/refresh.png"
							   oncommand="onLektorRefresh();"
							   tooltiptext="Neu laden"
				/>
				<textbox id="tempus-lektor-filter" size="30" oninput="onLektorFilter()" flex="1"/>
			</toolbar>
		</toolbox>
<!--		<spacer flex="1" />-->
	</hbox>
	<tree id="tree-lektor" onmouseup="onLektorSelect(event);"
		  seltype="multi" hidecolumnpicker="false" flex="1"
		  enableColumnDrag="true"
		  ondraggesture="nsDragAndDrop.startDrag(event,mitarbeiterDDObserver);"
		  ondrop="nsDragAndDrop.drop(event,LektorFunktionDDObserver)"
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
			<splitter class="tree-splitter"/>
			<treecol id="tree-lektor-fixangestellt" label="Fixangestellt" flex="1" hidden="true"/>
		</treecols>

		<template>
			<rule>
				<treechildren>
					<treeitem uri="rdf:*">
						<treerow>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#studiengang_kz"/>
							<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#fixangestellt"/>
						</treerow>
					</treeitem>
				</treechildren>
			</rule>
		</template>
	</tree>
</vbox>

<vbox id="vbox-main">
<popupset>
		<menupopup id="fasoverlay-lektor-tree-popup">
            <menuitem label="Zeitwünsche einsehen" oncommand="LektorFunktionLoadZeitwunschAdminUrl();" />
            <menuseparator />
			<menuitem label="Mail senden" oncommand="LektorFunktionMail();" />
			<menuseparator />
			<menuitem label="Entfernen" oncommand="LektorFunktionDel();" />
		</menupopup>
</popupset>
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs orient="horizontal">
			<tab id="tab-week" label="Wochenplan" />
			<tab id="tab-semester" label="Semesterplan" />
			<tab id="tab-studenten" label="StudentInnen" />
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
<vbox id="vbox-fachbereich">
	<hbox>
	<spacer flex="1" />
	<toolbarbutton label="Laden/Aktualisieren" image="../skin/images/refresh.png" tooltiptext="Liste neu laden" oncommand="FachbereichTreeRefresh()"/>
	<spacer flex="1" />
	</hbox>
	<tree id="tree-fachbereich" onmouseup="onFachbereichSelect(event);"
		seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/fachbereich/liste">
		<treecols>
		    <treecol id="fachbereich-treecol-bezeichnung" label="Bezeichnung" flex="3" primary="true" />
		    <splitter class="tree-splitter"/>
		    <treecol id="fachbereich-treecol-kurzbz" label="Kurzbz" flex="2" hidden="true"/>
		    <splitter class="tree-splitter"/>
		    <treecol id="fachbereich-treecol-farbe" label="Farbe" flex="1" hidden="true"/>
		    <splitter class="tree-splitter"/>
		     <treecol id="fachbereich-treecol-stg_kz" label="Stg_kz" flex="1" hidden="true"/>
		     <splitter class="tree-splitter"/>
		     <treecol id="fachbereich-treecol-uid" label="UID" flex="1" hidden="true"/>
		</treecols>

		<template>
		    <rule>
		      <treechildren>
		       <treeitem uri="rdf:*">
		         <treerow>
		           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#bezeichnung"/>
		           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#kurzbz"/>
		           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#farbe"/>
		           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#studiengang_kz"/>
		           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#uid"/>
		         </treerow>
		       </treeitem>
		      </treechildren>
		    </rule>
	  </template>
	</tree>
</vbox>

</overlay>
