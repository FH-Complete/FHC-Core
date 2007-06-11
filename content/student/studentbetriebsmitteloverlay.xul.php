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

<overlay id="StudentBetriebsmittel"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="student-betriebsmittel" style="margin:0px;" flex="1">
<popupset>
	<popup id="student-betriebsmittel-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentBetriebsmittelDelete();" id="student-betriebsmittel-tree-popup-delete" hidden="false"/>
	</popup>
</popupset>
<hbox flex="1">
<grid id="student-betriebsmittel-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>								
				<row>
					<tree id="student-betriebsmittel-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/betriebsmittel/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						onselect="StudentBetriebsmittelAuswahl()"
						context="student-betriebsmittel-tree-popup"
					>
					
						<treecols>
							<treecol id="student-betriebsmittel-tree-nummer" label="Nummer" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#nummer"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-betriebsmitteltyp" label="Typ" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmitteltyp"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-anmerkung" label="Anmerkung" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#anmerkung" />
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-kaution" label="Kaution" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#kaution" />
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-ausgegebenam" label="Ausgabedatum" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#ausgegebenam_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-retouram" label="Retourdatum" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#retouram_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-betriebsmittel_id" label="Betriebsmittel_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id" />
							<splitter class="tree-splitter"/>
							<treecol id="student-betriebsmittel-tree-person_id" label="Person_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#person_id" />
							<splitter class="tree-splitter"/>
						</treecols>
					
						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#nummer"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmitteltyp"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#anmerkung"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#kaution"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#ausgegebenam"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#retouram"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#person_id"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="student-betriebsmittel-button-neu" label="Neu" oncommand="StudentBetriebsmittelNeu();" disabled="true"/>
							<button id="student-betriebsmittel-button-loeschen" label="Loeschen" oncommand="StudentBetriebsmittelDelete();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="betriebsmittel_id" control="student-betriebsmittel-textbox-betriebsmittel_id"/>
							<textbox id="student-betriebsmittel-textbox-betriebsmittel_id" disabled="true"/>
							<label value="person_id" control="student-betriebsmittel-textbox-person_id"/>
							<textbox id="student-betriebsmittel-textbox-person_id" disabled="true"/>
							<label value="Neu" control="student-betriebsmittel-checkbox-neu"/>
							<checkbox id="student-betriebsmittel-checkbox-neu" disabled="true" checked="false"/>
						</vbox>
						<groupbox id="student-betriebsmittel-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="student-betriebsmittel-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Typ" control="student-betriebsmittel-menulist-betriebsmitteltyp"/>
										<menulist id="student-betriebsmittel-menulist-betriebsmitteltyp" disabled="true"
										          datasources="<?php echo APP_ROOT ?>rdf/betriebsmitteltyp.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/betriebsmitteltyp/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/betriebsmitteltyp/rdf#betriebsmitteltyp"
										        		      label="rdf:http://www.technikum-wien.at/betriebsmitteltyp/rdf#betriebsmitteltyp"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Nummer" control="student-betriebsmittel-textbox-nummer"/>
										<hbox>
					      					<textbox id="student-betriebsmittel-textbox-nummer" disabled="true" maxlength="32"/>
					      					<spacer flex="1" />			
					      				</hbox>
									</row>
									<row>
										<label value="Beschreibung" control="student-betriebsmittel-textbox-beschreibung"/>
				      					<textbox id="student-betriebsmittel-textbox-beschreibung" disabled="true" multiline="true"/>
					      			</row>
					      			<row>
										<label value="Kaution" control="student-betriebsmittel-textbox-kaution"/>
										<hbox>
					      					<textbox id="student-betriebsmittel-textbox-kaution" disabled="true" maxlength="8"/>
					      					<spacer flex="1" />			
					      				</hbox>
									</row>
									<row>
										<label value="Anmerkung" control="student-betriebsmittel-textbox-anmerkung"/>
				      					<textbox id="student-betriebsmittel-textbox-anmerkung" disabled="true" multiline="true"/>
					      			</row>
					      			<row>
										<label value="Ausgegeben am" control="student-betriebsmittel-textbox-ausgegebenam"/>
										<hbox>
											<box class="Datum" id="student-betriebsmittel-textbox-ausgegebenam" disabled="true"/>
					      					<!--<textbox id="student-betriebsmittel-textbox-ausgegebenam" disabled="true" maxlength="10"/>-->
					      					<spacer flex="1" />			
					      				</hbox>
									</row>
									<row>
										<label value="Retour am" control="student-betriebsmittel-textbox-retouram"/>
										<hbox>
											<box class="Datum" id="student-betriebsmittel-textbox-retouram" disabled="true"/>
					      					<!--<textbox id="student-betriebsmittel-textbox-retouram" disabled="true" maxlength="10"/>-->
					      					<spacer flex="1" />			
					      				</hbox>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="student-betriebsmittel-button-speichern" oncommand="StudentBetriebsmittelDetailSpeichern()" label="Speichern" disabled="true"/>
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