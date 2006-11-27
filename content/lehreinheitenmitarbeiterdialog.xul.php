<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';

?>


<window id="lehreinheiten-mitarbeiter-dialog" title="Mitarbeiter Auswahl"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lehreinheitenmitarbeiterdialog.js.php" />

<tree id="tree-liste-lehreinheiten-mitarbeiter" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="<?php echo APP_ROOT; ?>rdf/fas/mitarbeiter.rdf.php?aktiv=true"
		ref="http://www.technikum-wien.at/mitarbeiter/alle"
		flags="dont-build-content"
		enableColumnDrag="true"	style="margin:5px;"
		persist="height"
		>
	<treecols>
		<treecol id="tree-liste-lehreinheiten-mitarbeiter-col-anrede" label="Anrede" flex="1"  hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-titelpre" label="Titel(Pre)" flex="2" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-vorname" label="Vorname" flex="2" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-vornamen" label="Vornamen" flex="1" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-nachname" label="Nachname" flex="2" hidden="false" primary="true"
	    	class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-titelpost" label="Titel(Post)" flex="1" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost" />
	    <splitter class="tree-splitter"/>
		<treecol id="tree-liste-lehreinheiten-mitarbeiter-col-uid" label="UID" flex="1" hidden="false"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid" />
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-mitarbeiter_id" label="mitarbeiter_id" flex="1" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitarbeiter_id" />
	    	<splitter class="tree-splitter"/>
	    <treecol id="tree-liste-lehreinheiten-mitarbeiter-col-person_id" label="person_id" flex="1" hidden="true"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id" />
	</treecols>

	<template>
		<rule>
			<treechildren>
				<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitarbeiter_id"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id"   />
	 				</treerow>
	 			</treeitem>
	 		</treechildren>
	 	</rule>
  	</template>

</tree>
<vbox>
	<hbox flex="1">
		<spacer flex="1"/>
		<button id="button-lehreinheiten-mitarbeiter-auswahl" label="Hinzufuegen" oncommand="LehreinheitenMitarbeiterHinzufuegen();" />
	</hbox>
</vbox>
</window>