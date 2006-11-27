<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

include('../vilesci/config.inc.php');

echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiteroverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lehreinheitenoverlay.xul.php"?>';
/*echo '<?xul-overlay href="'.APP_ROOT.'content/studentenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lfvtoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/stpl-week-overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/stpl-semester-overlay.xul.php"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';*/

?>

<!DOCTYPE overlay >

<overlay id="FASOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<!--
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js"/>
-->
<tree id="tree-menu-mitarbeiter1" onselect="onMenuMitarbeiterSelect('tree-menu-mitarbeiter1');"
	seltype="single" hidecolumnpicker="true" flex="1"
	>
	<treecols>
	    <treecol id="tree-menu-mitarbeiter-col-name" label="Filter" primary="true" flex="1"/>
	    <treecol id="tree-menu-mitarbeiter-col-filter" label="ColFilter" hidden="true" flex="1"/>
	</treecols>

    <treechildren>
	    <treeitem>
	        <treerow>
	        	<treecell label="Alle"/>
	        	<treecell label="Alle"/>
	        </treerow>
	    </treeitem>
	    <treeitem>
			<treerow>
	        	<treecell label="FixAngestellte"/>
	        	<treecell label="FixAngestellteAlle"/>
	        </treerow>
	    </treeitem>
	    <treeitem>
			<treerow>
	        	<treecell label="FreiAngestellte"/>
	        	<treecell label="FreiAngestellteAlle"/>
	        </treerow>
	    </treeitem>

	    <treeitem container="true" open="true">
			<treerow>
			   	<treecell label="Aktive"/>
			   	<treecell label="Aktive"/>
			</treerow>
			<treechildren>
				<treeitem>
					<treerow>
					   	<treecell label="FixAngestellte"/>
					   	<treecell label="FixAngestellte"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="FreiAngestellte"/>
					   	<treecell label="FreiAngestellte"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Studiengangsleiter"/>
					   	<treecell label="Studiengangsleiter"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Fachbereichsleiter"/>
					   	<treecell label="Fachbereichsleiter"/>
					</treerow>
				</treeitem>
			</treechildren>
	    </treeitem>

	    <treeitem container="true" open="true">
			<treerow>
			   	<treecell label="Inaktive"/>
			   	<treecell label="Inaktive"/>
			</treerow>
			<treechildren>
				<treeitem>
					<treerow>
					   	<treecell label="Karenziert"/>
					   	<treecell label="Karenziert"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Ausgeschieden"/>
					   	<treecell label="Ausgeschieden"/>
					</treerow>
				</treeitem>
			</treechildren>
	    </treeitem>

	</treechildren>
</tree>

<tree id="tree-verband" onselect="onMenuVerbandSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="<?php echo APP_ROOT; ?>rdf/fas/student-verbaende.rdf.php" ref="http://www.technikum-wien.at/gruppen/liste"
	>
	<treecols>
		<treecol id="tree-verband-name" label="Name" flex="2" primary="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-verband-bezeichnung" label="Bezeichnung" flex="15"  hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-verband-studiengang_id" label="StudiengangID" flex="15"  hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-verband-gruppe_id" label="GruppenID" flex="15"  hidden="true"/>
	    <splitter class="tree-splitter"/>	    
	    <treecol id="tree-verband-ausbildungssemester_id" label="AusbildungssemesterID" flex="15"  hidden="true"/>
	    <splitter class="tree-splitter"/>	    
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	         	<treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#name"/>
	            <treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#studiengang_bezeichnung"/>
	            <treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#studiengang_id"/>
	            <treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_id"/>
	            <treecell label="rdf:http://www.technikum-wien.at/gruppen/rdf#ausbildungssemester_id"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-ort" onselect="onOrtSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="" ref="http://www.technikum-wien.at/tempus/ort/alle-orte">
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
	           <treecell label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#raumtyp"/>
	           <treecell label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#ort_kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#hierarchie"/>
	           <treecell label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#ort_bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#max_person"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-lektor" onselect="onLektorSelect();"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="mitarbeiter.rdf.php" ref="http://www.technikum-wien.at/tempus/mitarbeiter/alle">
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
	           		<treecell label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#kurzbz"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#nachname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#vornamen"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#titel"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#uid"/>
	         	</treerow>
	    	</treeitem>
	    </treechildren>
	    </rule>
  	</template>
</tree>
-->
<vbox id="vbox-main">
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs orient="horizontal">
			<tab id="tab-mitarbeiter" label="Mitarbeiter" oncommand="tabchange('mitarbeiter');" />
			<tab id="tab-lehreinheiten" label="Lehreinheiten" oncommand="tabchange('lehreinheiten');"/>
			<!--<tab id="tab-semester" label="Semesterplan" />
			<tab id="tab-studenten" label="Studenten" />-->
			<!-- <tab id="tab-lfvt" label="Lehrveranstaltung" /> -->
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
			<!--  Mitarbeiter  -->
			<vbox id="MitarbeiterEditor" />
			<vbox id="LehreinheitenEditor" />
			<!--  Wochenplan  -->
			<!-- <hbox id="hboxTimeTableWeek" /> -->
			<!--  Semesterplan  -->
			<!-- <vbox id="vboxTimeTableSemester" /> -->
			<!--  Studenten  -->
			<!-- <vbox id="studentenEditor" /> -->
			<!-- Lehrfachverteilung -->
            <!-- <vbox id="lfvtEditor" /> -->
		</tabpanels>
	</tabbox>
</vbox>

</overlay>