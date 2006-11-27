<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';
if(isset($_GET['stg_id']))
{
	$parameter='?studiengang_id='.$_GET['stg_id'];
	if(isset($_GET['ausbildungssemester_id']) && $_GET['ausbildungssemester_id']!='0')
		$parameter.='&amp;ausbildungssemester_id='.$_GET['ausbildungssemester_id'];
}
else
	$parameter='';

?>


<window id="lehreinheiten-mitarbeiter-dialog" title="Mitarbeiter Auswahl"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lehreinheitenneudialog.js.php" />

<tree id="tree-liste-lehreinheiten-lehrveranstaltung" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="<?php echo APP_ROOT; ?>rdf/fas/lehrveranstaltungen.rdf.php<?php echo $parameter; ?>"
		ref="http://www.technikum-wien.at/lehrveranstaltung/liste"
		flags="dont-build-content"
		enableColumnDrag="true"	style="margin:5px;"
		persist="height"
		ondblclick="LehreinheitenLVAHinzufuegen()"
		>
	<treecols>
		<treecol id="tree-liste-lehreinheiten-lehrveranstaltung-col-lehrveranstaltung_id" label="LVA_id" flex="1"  hidden="true"
	    	class="sortDirectionIndicator" 
	    	sort="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-lehrveranstaltung-col-name" label="Name" flex="1"  hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#name" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-lehrveranstaltung-col-ausbildungssemester" label="Ausbildungssemester" flex="1"  hidden="false"
	    	class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
	    	sort="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#ausbildungssemester_bezeichnung" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-lehrveranstaltung-col-fachbereich" label="Fachbereich" flex="1"  hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#fachbereich_bezeichnung" />
	    <splitter class="tree-splitter"/>
	</treecols>

	<template>
		<rule>
			<treechildren>
				<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#name"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#ausbildungssemester_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#fachbereich_bezeichnung"   />
	 				</treerow>
	 			</treeitem>
	 		</treechildren>
	 	</rule>
  	</template>  	
</tree>
<vbox>
	<hbox flex="1">
		<spacer flex="1"/>
		<button id="button-lehreinheiten-mitarbeiter-auswahl" label="Hinzufuegen" oncommand="LehreinheitenLVAHinzufuegen();" />
	</hbox>
</vbox>
</window>