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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="interessent-dokumente-overlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/interessentdokumenteoverlay.js.php" />

<!-- Dokumente Overlay -->
<vbox id="interessent-dokumente" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="interessent-dokumente-tree-nichtabgegeben-popup" onpopupshowing="InteressentDokumenteTreeNichtAbgegebenPopupShowing()">
		<menuitem label="Upload" oncommand="InteressentDokumenteNichtabgegebenUpload();" id="interessent-dokumente-tree-nichtabgegeben-popup-upload" hidden="false"/>
		<menuitem label="Bearbeiten" oncommand="InteressentDokumenteNichtabgegebenBearbeiten();" id="interessent-dokumente-tree-nichtabgegeben-popup-edit" hidden="false"/>
		<menuitem label="Dokument löschen" oncommand="InteressentDokumenteNichtabgegebenEntfernen();" id="interessent-dokumente-tree-nichtabgegeben-popup-remove" hidden="false"/>
	</menupopup>
</popupset>
<popupset>
	<menupopup id="interessent-dokumente-tree-abgegeben-popup"  onpopupshowing="InteressentDokumenteTreeAbgegebenPopupShowing()">
		<menuitem label="Upload" oncommand="InteressentDokumenteAbgegebenUpload();" id="interessent-dokumente-tree-abgegeben-popup-upload" hidden="false"/>
		<menuitem label="Bearbeiten" oncommand="InteressentDokumenteAbgegebenBearbeiten();" id="interessent-dokumente-tree-abgegeben-popup-edit" hidden="false"/>
		<menuitem label="Dokument löschen" oncommand="InteressentDokumenteAbgegebenEntfernen();" id="interessent-dokumente-tree-abgegeben-popup-remove" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
	<groupbox flex="2">
		<caption label="Nicht Akzeptiert"/>
		<tree id="interessent-dokumente-tree-nichtabgegeben" seltype="multi" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/dokument/liste"
				ondblclick="ShowDokument()"
                flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:10px;"
				context="interessent-dokumente-tree-nichtabgegeben-popup"
		>
			<treecols>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-bezeichnung" label="Dokument" flex="6" primary="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#bezeichnung"  onclick="InteressentDokumenteNichtAbgegebenTreeSort()" />
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-dokument_kurzbz" label="Kurzbezeichnung" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#dokument_kurzbz" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-datum" label="Datum" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#datum" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-datumhochgeladen" label="Uploaddatum" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#datumhochgeladen" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-nachgereicht" label="Nachgereicht" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-vorhanden" label="Vorhanden" flex="1" hidden="false"
					class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#vorhanden" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-infotext" label="Infotext" flex="6" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#infotext" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-akte_id" label="Akte ID" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#akte_id" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-titel_intern" label="Titel" flex="6" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#titel_intern" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-anmerkung_intern" label="Anmerkung intern" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#anmerkung_intern" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-onlinebewerbung" label="Online" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#onlinebewerbung" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-pflicht" label="Pflicht" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#pflicht" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-nichtabgegeben-nachgereicht_am" label="Nachreichung Am" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht_am" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#bezeichnung"   />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#dokument_kurzbz" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#datum" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#datumhochgeladen" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht" />
								<treecell src="../skin/images/rdf:http://www.technikum-wien.at/dokument/rdf#vorhanden^.png" label="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht_am" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#infotext" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#akte_id" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#titel_intern" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#anmerkung_intern" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#onlinebewerbung" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#pflicht" />
								<treecell label="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht_am" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
	</groupbox>

	<vbox>
		<spacer flex="1" />
		<button id="interessent-dokumente-filter" oncommand="InteressentDokumenteFilter()" label="Filter" tooltiptext="Liste aller Studenten mit fehlenden Dokumenten" />
		<spacer flex="3"/>
		<button id="interessent-dokumente-upload" oncommand="InteressentDokumenteUpload()" label="Upload"/>
		<spacer flex="1" />
		<button id="interessent-dokumente-add" oncommand="InteressentDokumenteAdd()" label="=&gt;" style="font-weight: bold;"/>
		<spacer flex="1" />
		<button id="interessent-dokumente-remove" oncommand="InteressentDokumenteRemove()" label="&lt;="  style="font-weight: bold;"/>
		<spaver flex="4" />
	</vbox>

	<groupbox flex="2">
		<caption label="Akzeptiert"/>
		<tree id="interessent-dokumente-tree-abgegeben" seltype="multi" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/dokumentprestudent/liste"
				ondblclick="ShowDokumentAbgegeben()"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:10px;"
				context="interessent-dokumente-tree-abgegeben-popup"
		>
			<treecols>
				<treecol id="interessent-dokumente-tree-abgegeben-bezeichnung" label="Dokument" flex="1" primary="false"
					class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#bezeichnung"  onclick="InteressentDokumenteAbgegebenTreeSort()" />
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-datum" label="Akzeptiertdatum" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datum_iso" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-datumhochgeladen" label="Upload Datum" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datumhochgeladen" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-mitarbeiter_uid" label="Akzeptiert von" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#mitarbeiter_uid" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-dokument_kurzbz" label="Dokumentkurzbz" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#dokument_kurzbz" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-prestudent_id" label="PrestudentInID" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#prestudent_id" onclick="InteressentDokumenteAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-nachgereicht" label="Nachgereicht" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-infotext" label="Infotext" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#infotext" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-akte_id" label="Akte ID" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#akte_id" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-titel_intern" label="Titel" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#titel_intern" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-vorhanden" label="Vorhanden" flex="1" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#vorhanden" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-anmerkung_intern" label="Anmerkung intern" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#anmerkung_intern" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="interessent-dokumente-tree-abgegeben-nachgereicht_am" label="Nachreichung am" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dokument/rdf#nachgereicht_am" onclick="InteressentDokumenteNichtAbgegebenTreeSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#bezeichnung" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datum" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#datumhochgeladen" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#dokument_kurzbz" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#prestudent_id" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#nachgereicht" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#infotext" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#akte_id" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#titel_intern" />
								<treecell src="../skin/images/rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#vorhanden^.png"/>
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#anmerkung_intern" />
								<treecell label="rdf:http://www.technikum-wien.at/dokumentprestudent/rdf#nachgereicht_am" />
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
