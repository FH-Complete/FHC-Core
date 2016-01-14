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
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="ioDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Incomming/Outgoing DETAILS -->
<vbox id="student-io" style="overflow:auto;margin:0px;" flex="1">
<popupset>
	<menupopup id="student-io-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentIODelete();" id="student-io-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>

<hbox flex="1">
<grid id="student-io-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="student-io-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/bisio/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						context="student-io-tree-popup"
						flags="dont-build-content"
					>
					<!-- onselect="StudentIOAuswahl()" - wird jetzt per JS gesetzt -->
						<treecols>
							<treecol id="student-io-tree-mobilitaetsprogramm_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#mobilitaetsprogramm_kurzbz"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-io-tree-nation_code" label="Nation" flex="1" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#nation_code"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-io-tree-von" label="Von" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#von_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="student-io-tree-bis" label="Bis" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#bis_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="student-io-tree-zweck_kurzbz" label="Zweck" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#zweck_bezeichnung" />
							<splitter class="tree-splitter"/>
							<treecol id="student-io-tree-bisio_id" label="bisio_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/bisio/rdf#bisio_id" />
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#mobilitaetsprogramm_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#nation_code"/>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#von"/>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#bis"/>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#zweck_bezeichnung"/>
										<treecell label="rdf:http://www.technikum-wien.at/bisio/rdf#bisio_id"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="student-io-button-neu" label="Neu" oncommand="StudentIONeu();" disabled="true"/>
							<button id="student-io-button-loeschen" label="Loeschen" oncommand="StudentIODelete();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="Neu"/>
							<checkbox id="student-io-detail-checkbox-neu" checked="true" />
							<label value="Uid"/>
								<textbox id="student-io-detail-textbox-prestudent_id" disabled="true"/>
								<textbox id="student-io-detail-textbox-uid" disabled="true"/>
								<label value="BisIO ID"/>
								<textbox id="student-io-detail-textbox-bisio_id" disabled="true"/>
						</vbox>
						<groupbox id="student-io-groupbox" flex="1">
						<caption label="BIS"/>
							<grid id="student-io-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Von" control="student-io-textbox-von"/>
										<hbox>
											<box class="Datum" id="student-io-textbox-von" disabled="true"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Bis" control="student-io-textbox-bis"/>
										<hbox>
											<box class="Datum" id="student-io-textbox-bis" disabled="true"/>
					      					<!--<textbox id="student-io-textbox-bis" disabled="true" maxlength="10" size="10"/>-->
					      					<spacer flex="1" />
					      				</hbox>
					      			</row>
									<row>
										<label value="Mobilitaetsprogramm" control="student-io-menulist-mobilitaetsprogramm"/>
										<menulist id="student-io-menulist-mobilitaetsprogramm" disabled="true"
										          datasources="<?php echo APP_ROOT ?>rdf/mobilitaetsprogramm.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/mobilitaetsprogramm/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/mobilitaetsprogramm/rdf#mobilitaetsprogramm_code"
										        		      label="rdf:http://www.technikum-wien.at/mobilitaetsprogramm/rdf#kurzbz"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Gastnation" control="student-io-menulist-nation"/>
										<menulist id="student-io-menulist-nation" disabled="true"
										          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/nation/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
										        		      label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Zweck" control="student-io-menulist-zweck"/>
										<menulist id="student-io-menulist-zweck" disabled="true"
										          datasources="<?php echo APP_ROOT ?>rdf/zweck.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/zweck/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/zweck/rdf#zweck_code"
										        		      label="rdf:http://www.technikum-wien.at/zweck/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>

								</rows>
							</grid>
						</groupbox>
						<groupbox id="student-io-groupbox" flex="1">
						<caption label="Outgoing (Zeugnis)"/>
							<grid id="student-io-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Lehrveranstaltung" control="student-io-menulist-lehrveranstaltung"/>
										<menulist id="student-io-menulist-lehrveranstaltung" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/lehrveranstaltung/liste"
										          oncommand="StudentIOLVAChange()">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"
										        		      label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#bezeichnung Semester rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#semester"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Lehreinheit" control="student-io-menulist-lehreinheit"/>
										<menulist id="student-io-menulist-lehreinheit" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/lehreinheit/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/lehreinheit/rdf#lehreinheit_id"
										        		      label="rdf:http://www.technikum-wien.at/lehreinheit/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Ort" control="student-io-textbox-ort"/>
										<textbox id="student-io-textbox-ort" disabled="true" />
									</row>
									<row>
										<label value="Universitaet" control="student-io-textbox-universitaet"/>
										<textbox id="student-io-textbox-universitaet" disabled="true" />
									</row>
								</rows>
							</grid>
						</groupbox>
						<hbox>
							<spacer flex="1" />
							<button id="student-io-button-speichern" oncommand="StudentIODetailSpeichern()" label="Speichern" disabled="true"/>
						</hbox>
					</vbox>
				</row>
		</rows>
</grid>
</hbox>
<spacer flex="1" />
</vbox>

</overlay>
