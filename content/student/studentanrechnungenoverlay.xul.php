<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="StudentAnrechnungen"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Pruefung Overlay -->
<vbox id="student-anrechnungen" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="student-anrechnungen-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentAnrechnungDelete();" id="student-anrechnungen-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="student-anrechnungen-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="2"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="student-anrechnungen-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/anrechnung/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						context="student-anrechnungen-tree-popup"
						flags="dont-build-content"
					>

						<treecols>
							<treecol id="student-anrechnungen-tree-anrechnung_id" label="Anrechnung ID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#anrechnung_id"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-lehrveranstaltung_id" label="Lehrveranstaltung ID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_id"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_bez"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-begruendung" label="Begründung" flex="2" hidden="false" ignoreincolumnpicker="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#begruendung"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-kompatible_lehrveranstaltung_id" label="kompatible Lehrveranstaltung ID" flex="5" hidden="true"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_id_kompatibel"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-kompatible_lehrveranstaltung_bezeichnung" label="kompatible Lehrveranstaltung" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_bez_kompatibel"/>
							<splitter class="tree-splitter"/>
                            <treecol id="student-anrechnungen-tree-status" label="Status" flex="2" hidden="false"
                                     class="sortDirectionIndicator"
                                     sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#status" />
                            <splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-genehmigt_von" label="genehmigt von" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#genehmigt_von" />
							<splitter class="tree-splitter"/>
							<treecol id="student-anrechnungen-tree-datum" label="Datum" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/anrechnung/rdf#insertamum" />
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#anrechnung_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_bez"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#begruendung"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_id_kompatibel"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#lehrveranstaltung_bez_kompatibel"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#status"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#genehmigt_von"/>
										<treecell label="rdf:http://www.technikum-wien.at/anrechnung/rdf#insertamum"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="student-anrechnungen-button-neu" label="Neu" oncommand="StudentAnrechnungNeu();" disabled="true"/>
							<button id="student-anrechnungen-button-loeschen" label="Löschen" oncommand="StudentAnrechnungDelete();" disabled="true"/>
							<button id="student-anrechnungen-button-notiz" label="Notiz hinzufügen" oncommand="StudentNotizNeu();" disabled="true"/>
						</hbox>
						<groupbox id="student-anrechnungen-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="student-anrechnungen-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Lehrveranstaltung" control="student-anrechnungen-menulist-lehrveranstaltung"/>
										<menulist id="student-anrechnungen-menulist-lehrveranstaltung" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/lehrveranstaltung/liste"
												  oncommand="StudentLoadKompatibleLvaDropDown()" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"
										        		      label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#bezeichnung Semester rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#semester rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrform_kurzbz"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Begründung" control="student-anrechnungen-menulist-begruendung"/>
										<menulist id="student-anrechnungen-menulist-begruendung" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/anrechnungbegruendung/liste"
												  oncommand="StudentAnrechnungShowKompatibleLvaDropDown()">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/anrechnungbegruendung/rdf#begruendung_id"
										        		      label="rdf:http://www.technikum-wien.at/anrechnungbegruendung/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row id="student-anrechnungen-menulist-kompatible_lehrveranstaltung-row" hidden="true">
										<label value="kompatible Lehrveranstaltung" control="student-anrechnungen-menulist-kompatible_lehrveranstaltung"/>
										<menulist id="student-anrechnungen-menulist-kompatible_lehrveranstaltung" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/lehrveranstaltung/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"
										        		      label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="genehmigt von" control="student-anrechnungen-menulist-genehmigt_von"/>
										<menulist id="student-anrechnungen-menulist-genehmigt_von" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/mitarbeiter/_alle" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
										        		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<textbox id="student-anrechnungen-prestudent_id" hidden="true" />
								<textbox id="student-anrechnungen-neu" hidden="true" />
								<button id="student-anrechnungen-button-speichern" oncommand="StudentAnrechnungDetailSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>
</hbox>
<spacer flex="1" />
</vbox>
</overlay>