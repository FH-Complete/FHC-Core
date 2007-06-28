<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<!DOCTYPE overlay>

<overlay id="MitarbeiterDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<vbox id="mitarbeiter-detail-funktionen" flex="1" style="overflow:auto">

<groupbox id="mitarbeiter-detail-groupbox-verwendung" flex="1">
	<caption label="Verwendung" />
		<tree id="mitarbeiter-tree-verwendung" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/verwendung/alle"
				onselect="MitarbeiterVerwendungSelect();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:5px;"
				persist="hidden, height"						
		>
			<treecols>
				<treecol id="mitarbeiter-treecol-ba1bez" label="Beschaeftigungsart 1" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ba1bez"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-ba2bez" label="Beschaeftigungsart 2" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ba2bez"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-ausmass" label="Ausmass" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ausmass"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-verwendung" label="Verwendung" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendung"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-hauptberuf" label="Hauptberuf" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberuf"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-hauptberuflich" label="Hauptberuflich" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberuflich"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-habilitation" label="Habilitation" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#habilitation"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-beginn" label="Beginn" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#beginn"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-ende" label="Ende" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ende"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-ba1code" label="ba1code" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ba1code"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-ba2code" label="ba2code" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#ba2code"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-beschausmasscode" label="beschausmasscode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#beschausmasscode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-verwendungcode" label="verwendungcode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendungcode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-mitarbeiter_uid" label="mitarbeiter_uid" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#mitarbeiter_uid"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-treecol-hauptberufcode" label="hauptberufcode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberufcode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
	
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ba1bez" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ba2bez" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ausmass" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendung" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberuf" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberuflich" />   							
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#habilitation" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#beginn" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ende" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ba1code" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#ba2code" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#beschausmasscode" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendungcode" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/verwendung/rdf#hauptberufcode" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
</groupbox>


<groupbox id="mitarbeiter-detail-groupbox-funktion" flex="1">
	<caption label="Funktion" />
		<tree id="mitarbeiter-tree-funktion" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/funktion/alle"
				onselect="MitarbeiterFunktionSelect();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:5px;"
				persist="hidden, height"						
		>
			<treecols>
				<treecol id="mitarbeiter-funktion-treecol-studiengang" label="Studiengang" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/funktion/rdf#studiengang"  onclick="MitarbeiterTreeFunktionSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-funktion-treecol-sws" label="SWS" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/funktion/rdf#sws"  onclick="MitarbeiterTreeFunktionSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-funktion-treecol-bisverwendung_id" label="VerwendungID" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/funktioon/rdf#bisverwendung_id"  onclick="MitarbeiterTreeFunktionSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-funktion-treecol-studiengang_kz" label="Studiengang_kz" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/funktion/rdf#studiengang_kz"  onclick="MitarbeiterTreeFunktionSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
	
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/funktion/rdf#studiengang" />
								<treecell label="rdf:http://www.technikum-wien.at/funktion/rdf#sws" />
								<treecell label="rdf:http://www.technikum-wien.at/funktion/rdf#bisverwendung_id" />
								<treecell label="rdf:http://www.technikum-wien.at/funktion/rdf#studiengang_kz" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
</groupbox>
</vbox>
</overlay>
