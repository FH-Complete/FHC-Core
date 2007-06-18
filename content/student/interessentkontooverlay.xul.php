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

<overlay id="InteressentKonto"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="interessent-konto" style="margin:0px;" flex="1">
<popupset>
	<popup id="interessent-konto-tree-popup">
		<menuitem label="Entfernen" oncommand="InteressentKontoDelete();" id="interessent-konto-tree-popup-kontodel" hidden="false"/>
	</popup>
</popupset>
<hbox flex="1">
<grid id="interessent-konto-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>	
					<hbox>
						<spacer flex="1" />
						<button id="interessent-konto-button-filter" value="alle" oncommand="InteressentKontoFilter()" label="offene" disabled="true"/>
					</hbox>
					<spacer />
				</row>
				
				<row>
					<tree id="interessent-konto-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/konto/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						onselect="InteressentKontoAuswahl()"
						context="interessent-konto-tree-popup"
						flags="dont-build-content"
					>
					
						<treecols>
							<treecol id="interessent-konto-tree-buchungsdatum" label="Buchungsdatum" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungsdatum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="interessent-konto-tree-buchungstext" label="Buchungstext" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungstext"/>
							<splitter class="tree-splitter"/>
							<treecol id="interessent-konto-tree-betrag" label="Betrag" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#betrag" />
							<splitter class="tree-splitter"/>
							<treecol id="interessent-konto-tree-studiensemester_kurzbz" label="StSem" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#studiensemester_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="interessent-konto-tree-buchungstyp_kurzbz" label="Typ" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungstyp_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="interessent-konto-tree-buchungsnr" label="buchungs_nr" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungsnr" />
							<splitter class="tree-splitter"/>
						</treecols>
					
						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungsdatum"/>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungstext"/>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#betrag"/>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#studiensemester_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungstyp_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungsnr"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="interessent-konto-button-neu" label="Neu" oncommand="InteressentKontoNeu();" disabled="true"/>
							<button id="interessent-konto-button-gegenbuchung" label="Gegenbuchung" oncommand="InteressentKontoGegenbuchung();" disabled="true"/>
							<button id="interessent-konto-button-loeschen" label="Loeschen" oncommand="InteressentKontoDelete();" disabled="true"/>
							<spacer flex="1"/>
							<button id="interessent-konto-button-zahlungsbestaetigung" label="Zahlungsbestaetigung drucken" oncommand="InteressentKontoZahlungsbestaetigung();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="Buchungsnr" control="interessent-konto-textbox-buchungsnr"/>
							<textbox id="interessent-konto-textbox-buchungsnr" disabled="true"/>
						</vbox>
						<groupbox id="interessent-konto-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="interessent-konto-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Betrag" control="interessent-konto-textbox-betrag"/>
										<hbox>
					      					<textbox id="interessent-konto-textbox-betrag" disabled="true" maxlength="9" size="9"/>
					      					<spacer flex="1" />			
					      				</hbox>
									</row>
									<row>
										<label value="Buchungsdatum" control="interessent-konto-textbox-buchungsdatum"/>
										<hbox>
											<box class="Datum" id="interessent-konto-textbox-buchungsdatum" disabled="true"/>
					      					<!--<textbox id="interessent-konto-textbox-buchungsdatum" disabled="true" maxlength="10" size="10"/>-->
					      					<spacer flex="1" />			
					      				</hbox>
					      			</row>
					      			<row>
					      				<label value="Buchungstext" control="interessent-konto-textbox-buchungstext"/>
							      		<textbox id="interessent-konto-textbox-buchungstext" disabled="true" maxlength="256"/>
									</row>
									<row>
										<label value="Mahnspanne" control="interessent-konto-textbox-mahnspanne"/>
										<hbox>
											<textbox id="interessent-konto-textbox-mahnspanne" disabled="true" maxlength="4" size="4"/>
											<spacer flex="1" />			
					      				</hbox>
									</row>
									<row>
										<label value="Typ" control="interessent-konto-menulist-buchungstyp"/>
										<menulist id="interessent-konto-menulist-buchungstyp" disabled="true"
										          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/buchungstyp/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="interessent-konto-button-speichern" oncommand="InteressentKontoDetailSpeichern()" label="Speichern" disabled="true"/>
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