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
	<hbox>
		<tree id="mitarbeiter-tree-verwendung" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/bisverwendung/liste"
				onselect="MitarbeiterVerwendungSelect();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:5px;"
				persist="hidden, height"						
		>
			<treecols>
				<treecol id="mitarbeiter-verwendung-treecol-ba1bez" label="Beschaeftigungsart 1" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba1bez"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-ba2bez" label="Beschaeftigungsart 2" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba2bez"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-ausmass" label="Ausmass" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ausmass"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-verwendung" label="Verwendung" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#verwendung"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-hauptberuf" label="Hauptberuf" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberuf"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-hauptberuflich" label="Hauptberuflich" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberuflich"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-habilitation" label="Habilitation" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#habilitation"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-beginn" label="Beginn" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beginn_iso"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-ende" label="Ende" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ende_iso"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-ba1code" label="ba1code" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba1code"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-ba2code" label="ba2code" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba2code"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-beschausmasscode" label="beschausmasscode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beschausmasscode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-verwendungcode" label="verwendungcode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#verwendungcode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-mitarbeiter_uid" label="mitarbeiter_uid" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#mitarbeiter_uid"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-hauptberufcode" label="hauptberufcode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberufcode"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-bisverwendung_id" label="bisverwendungID" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#bisverwendung_id"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
	
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba1bez" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba2bez" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ausmass" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#verwendung" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberuf" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberuflich" />   							
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#habilitation" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beginn" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ende" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba1code" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba2code" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beschausmasscode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#verwendungcode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberufcode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#bisverwendung_id" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
		<vbox>
			<button id="mitarbeiter-verwendung-button-neu" label="Neu" disabled="true" />
			<button id="mitarbeiter-verwendung-button-bearbeiten" label="Bearbeiten" disabled="true" />
			<button id="mitarbeiter-verwendung-button-loeschen" label="Loeschen" disabled="true" />
		</vbox>
	</hbox>


	<groupbox id="mitarbeiter-detail-groupbox-funktion" flex="1">
		<caption label="Funktion" />
		<hbox>
			<tree id="mitarbeiter-tree-funktion" seltype="single" hidecolumnpicker="false" flex="1"
					datasources="rdf:null" ref="http://www.technikum-wien.at/bisfunktion/liste"
					onselect="MitarbeiterFunktionSelect();"
					flags="dont-build-content"
					enableColumnDrag="true"
					style="margin:5px;"
					persist="hidden, height"						
			>
				<treecols>
					<treecol id="mitarbeiter-funktion-treecol-studiengang" label="Studiengang" flex="1" persist="hidden, width" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bisfunktion/rdf#studiengang"  onclick="MitarbeiterTreeFunktionSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-funktion-treecol-sws" label="SWS" flex="1" persist="hidden, width" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bisfunktion/rdf#sws"  onclick="MitarbeiterTreeFunktionSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-funktion-treecol-bisverwendung_id" label="VerwendungID" flex="1" persist="hidden, width" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bisfunktion/rdf#bisverwendung_id"  onclick="MitarbeiterTreeFunktionSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-funktion-treecol-studiengang_kz" label="Studiengang_kz" flex="1" persist="hidden, width" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bisfunktion/rdf#studiengang_kz"  onclick="MitarbeiterTreeFunktionSort()"/>
					<splitter class="tree-splitter"/>
				</treecols>
		
				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/bisfunktion/rdf#studiengang" />
									<treecell label="rdf:http://www.technikum-wien.at/bisfunktion/rdf#sws" />
									<treecell label="rdf:http://www.technikum-wien.at/bisfunktion/rdf#bisverwendung_id" />
									<treecell label="rdf:http://www.technikum-wien.at/bisfunktion/rdf#studiengang_kz" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
			<vbox>
				<button id="mitarbeiter-funktion-button-neu" label="Neu" disabled="true" />
				<button id="mitarbeiter-funktion-button-bearbeiten" label="Bearbeiten" disabled="true" />
				<button id="mitarbeiter-funktion-button-loeschen" label="Loeschen" disabled="true" />
			</vbox>
		</hbox>
	</groupbox>
</groupbox>
<groupbox id="mitarbeiter-detail-groupbox-entwicklungsteam" flex="1">
	<caption label="Entwicklungsteam" />
	<hbox>
		<tree id="mitarbeiter-tree-entwicklungsteam" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/entwicklungsteam/liste"
				onselect="MitarbeiterEntwicklungsteamSelect();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:5px;"
				persist="hidden, height"						
		>
			<treecols>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-studiengang" label="Studiengang" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#studiengang"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-besqual" label="Besondere Qualifikation" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#besqual"  onclick="MitarbeiterTreeEntwicklungteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-beginn" label="Beginn" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#beginn"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-ende" label="Ende" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#ende"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-studiengang_kz" label="StudiengangKZ" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#studiengang_kz"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-mitarbeiter_uid" label="MitarbeiterUID" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#mitarbeiter_uid"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-entwicklungsteam-treecol-besqualcode" label="Besqualcode" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#besqualcode"  onclick="MitarbeiterTreeEntwicklungsteamSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
	
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#studiengang" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#besqual" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#beginn" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#ende" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#studiengang_kz" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/entwicklungsteam/rdf#besqualcode" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
		<vbox>
			<button id="mitarbeiter-entwicklungsteam-button-neu" label="Neu" disabled="true" />
			<button id="mitarbeiter-entwicklungsteam-button-bearbeiten" label="Bearbeiten" disabled="true" />
			<button id="mitarbeiter-entwicklungsteam-button-loeschen" label="Loeschen" disabled="true" />
		</vbox>
	</hbox>
</groupbox>
</vbox>
</overlay>