<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>
<overlay id="MitarbeiterBuchung"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeiterbuchung.js.php" />

<vbox id="mitarbeiter-buchung" style="overflow:auto;margin:0px;" flex="1">
<popupset>
	<menupopup id="mitarbeiter-buchung-tree-popup">
		<menuitem label="Entfernen" oncommand="MitarbeiterBuchungDelete();" id="mitarbeiter-buchung-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="mitarbeiter-buchung-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="4"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row flex="1">
					<vbox flex="1">
						<tree id="mitarbeiter-buchung-tree" seltype="multi" hidecolumnpicker="false" flex="1"
							datasources="rdf:null" ref="http://www.technikum-wien.at/wawi_buchung"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" enableColumnDrag="true"
							context="mitarbeiter-buchung-tree-popup"
							flags="dont-build-content"
						>
							<treecols>
								<treecol id="mitarbeiter-buchung-tree-buchungsdatum" label="Buchungsdatum" flex="2" hidden="false" primary="true"
									class="sortDirectionIndicator"
									sortActive="true"
									sortDirection="descending"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungsdatum_iso"/>
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-buchungstext" label="Buchungstext" flex="5" hidden="false"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstext"/>
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-betrag" label="Betrag" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#betrag" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-buchungstyp" label="Buchungstyp" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstyp" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-konto" label="Konto" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#konto" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-kostenstelle" label="Kostenstelle" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#kostenstelle" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-buchungstyp_kurzbz" label="BuchungstypKurzbz" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstyp_kurzbz" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-konto_id" label="KontoID" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#konto_id" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-kostenstelle_id" label="KostenstelleID" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#kostenstelle_id" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-buchung_id" label="BuchungID" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchung_id" />
								<splitter class="tree-splitter"/>
								<treecol id="mitarbeiter-buchung-tree-buchungsdatum_iso" label="BuchungsdatumISO" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungsdatum_iso" />
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<treechildren flex="1" >
										<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungsdatum"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstext"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#betrag"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstyp"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#konto"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#kostenstelle"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungstyp_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#konto_id"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#kostenstelle_id"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchung_id"/>
											<treecell label="rdf:http://www.technikum-wien.at/wawi_buchung/rdf#buchungsdatum_iso"/>
										</treerow>
									</treeitem>
								</treechildren>
							</template>
						</tree>
					</vbox>
					<vbox flex="1">
						<hbox>
							<button id="mitarbeiter-buchung-button-neu" label="Neu" oncommand="MitarbeiterBuchungNeu();" disabled="true"/>
							<button id="mitarbeiter-buchung-button-loeschen" label="Loeschen" oncommand="MitarbeiterBuchungDelete();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="BuchungID" control="mitarbeiter-buchung-textbox-buchung_id"/>
							<textbox id="mitarbeiter-buchung-textbox-buchung_id" disabled="true"/>
						</vbox>
						<groupbox id="mitarbeiter-buchung-groupbox">
						<caption label="Details"/>
							<grid id="mitarbeiter-buchung-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Betrag" control="mitarbeiter-buchung-textbox-betrag"/>
										<hbox>
					      					<textbox id="mitarbeiter-buchung-textbox-betrag" disabled="true" maxlength="9" size="9"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Buchungsdatum" control="mitarbeiter-buchung-textbox-buchungsdatum"/>
										<hbox>
											<box class="Datum" id="mitarbeiter-buchung-textbox-buchungsdatum" disabled="true"/>
					      					<spacer flex="1" />
					      				</hbox>
					      			</row>
					      			<row>
					      				<label value="Buchungstext" control="mitarbeiter-buchung-textbox-buchungstext"/>
							      		<textbox id="mitarbeiter-buchung-textbox-buchungstext" disabled="true" maxlength="512"/>
									</row>
									<row>
										<label value="Typ" control="mitarbeiter-buchung-menulist-buchungstyp"/>
										<menulist id="mitarbeiter-buchung-menulist-buchungstyp" disabled="true"
												xmlns:TYP="http://www.technikum-wien.at/wawi_buchungstyp/rdf#"
										          datasources="<?php echo APP_ROOT ?>rdf/wawi_buchungstyp.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/wawi_buchungstyp" >
											<template>
												<rule>
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/wawi_buchungstyp/rdf#buchungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/wawi_buchungstyp/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
												</rule>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Kostenstelle" control="mitarbeiter-buchung-menulist-kostenstelle"/>
										<menulist id="mitarbeiter-buchung-menulist-kostenstelle" disabled="true"
												xmlns:KST="http://www.technikum-wien.at/wawi_kostenstelle/rdf#"
										          datasources="<?php echo APP_ROOT ?>rdf/wawi_kostenstelle.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/wawi_kostenstelle" >
											<menupopup>
												<menuitem value=""
										       		      label="-- Keine Auswahl --"
												  		  />
											</menupopup>
											<template>
												<rule KST:aktiv="false">
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/wawi_kostenstelle/rdf#kostenstelle_id"
										        		      label="rdf:http://www.technikum-wien.at/wawi_kostenstelle/rdf#bezeichnung"
													  		  uri="rdf:*" style="text-decoration:line-through;"/>
													</menupopup>
												</rule>
												<rule>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/wawi_kostenstelle/rdf#kostenstelle_id"
										        		      label="rdf:http://www.technikum-wien.at/wawi_kostenstelle/rdf#bezeichnung"
													  		  uri="rdf:*"/>
													</menupopup>
												</rule>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Konto" control="mitarbeiter-buchung-menulist-konto"/>
										<menulist id="mitarbeiter-buchung-menulist-konto" disabled="true"
												xmlns:WAWIKONTO="http://www.technikum-wien.at/wawi_konto/rdf#"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/wawi_konto" >
											<template>
												<rule>
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/wawi_konto/rdf#konto_id"
										        		      label="rdf:http://www.technikum-wien.at/wawi_konto/rdf#beschreibung"
													  		  uri="rdf:*"/>
													</menupopup>
												</rule>
											</template>
										</menulist>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="mitarbeiter-buchung-button-konto" oncommand="MitarbeiterBuchungKontoAnlegen()" label="Konto anlegen" hidden="true"/>
								<button id="mitarbeiter-buchung-button-speichern" oncommand="MitarbeiterBuchungDetailSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>
</hbox>
</vbox>
</overlay>
