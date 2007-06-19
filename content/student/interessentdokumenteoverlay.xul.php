<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="interessent-dokumente-overlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Dokumente Overlay -->
<vbox id="interessent-dokumente" style="margin:0px;" flex="1">
<hbox flex="1">
	<groupbox flex="1">
		<caption label="Noch nicht abgegeben"/>
		<tree id="interessent-dokumente-tree-nichtabgegeben" seltype="multi" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/dokument/liste"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:10px;"
		>
			<treecols>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-bezeichnung" label="Dokument" flex="1" primary="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#bezeichnung"  onclick="InteressentDokumenteNichtAbgegebenTreeSort()" />
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz" label="Kurzbezeichnung" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#dokument_kurzbz" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
		
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#bezeichnung"   />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#dokument_kurzbz" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
	</groupbox>
	
	<vbox>
		<spacer flex="4"/>
		<button id="interessent-dokumente-add" oncommand="InteressentDokumenteAdd()" label="=&gt;" style="font-weight: bold;"/>
		<spacer flex="1" />
		<button id="interessent-dokumente-remove" oncommand="InteressentDokumenteRemove()" label="&lt;="  style="font-weight: bold;"/>
		<spaver flex="4" />
	</vbox>
	
	<groupbox flex="3">
		<caption label="Abgegeben"/>
		<tree id="interessent-dokumente-tree-abgegeben" seltype="multi" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/dokumentprestudent/liste"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:10px;"
		>
			<treecols>
				<treecol id="interessent-dokumente-tree-abgegeben-bezeichnung" label="Dokument" flex="1" primary="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#bezeichnung"  onclick="InteressentDokumenteAbgegebenTreeSort()" />
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-datum" label="Datum" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datum_iso" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-mitarbeiter_uid" label="Abgegeben bei" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#mitarbeiter_uid" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-dokument_kurzbz" label="Dokumentkurzbz" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#dokument_kurzbz" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-prestudent_id" label="PrestudentID" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#prestudent_id" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
		
			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#bezeichnung" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datum" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#dokument_kurzbz" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#prestudent_id" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
	</groupbox>
</hbox>
</vbox>
</overlay>