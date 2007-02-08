<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';

include('../vilesci/config.inc.php');

echo '<?xul-overlay href="'.APP_ROOT.'content/studentenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lfvtoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/stpl-week-overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/stpl-semester-overlay.xul.php"?>';
/*echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';*/

?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../locale/de-AT/tempus.dtd"); ?>] -->

<overlay id="TempusOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/tempusoverlay.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js"/>

<tree id="tree-verband" onselect="onVerbandSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="../rdf/lehrverbandsgruppe.rdf.php" ref="http://www.technikum-wien.at/lehrverbandsgruppe/alle-verbaende"
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
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#name"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgrupperdf#stg"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgrupperdf#stg_kz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgrupperdf#sem"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#ver"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#grp"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#gruppe"/>
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
	    <treecol id="raumtyp" label="Raumtyp" flex="2" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="ort_kurzbz" label="Raum" flex="4" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="hierarchie" label="HI" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	     <treecol id="ort_bezeichnung" label="Bezeichnung" flex="3" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="max_person" label="MaxP" flex="1" hidden="true"/>
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
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-lektor" onselect="onLektorSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="../rdf/mitarbeiter.rdf.php" ref="http://www.technikum-wien.at/mitarbeiter/alle">
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

<vbox id="vbox-main">
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
			<!--  Studenten
			<vbox id="studentenEditor" /> -->
			<!-- Lehrfachverteilung
            <vbox id="lfvtEditor" /> -->
		</tabpanels>
	</tabbox>
</vbox>

</overlay>