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

<!DOCTYPE overlay>

<overlay id="MitarbeiterDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<vbox id="mitarbeiter-detail-funktionen" flex="1" style="overflow:auto">

<groupbox id="mitarbeiter-detail-groupbox-verwendung" flex="1">
	<caption label="Verwendung" />
	<hbox flex="1">
		<tree id="mitarbeiter-tree-verwendung" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/bisverwendung/liste"
				onselect="MitarbeiterVerwendungSelect();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:5px;"
				persist="hidden, height"
				ondblclick="MitarbeiterVerwendungBearbeiten()"
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
				<treecol id="mitarbeiter-verwendung-treecol-vertragsstunden" label="Vertragsstunden" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#vertragsstunden"  onclick="MitarbeiterTreeVerwendungSort()"/>
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
				<treecol id="mitarbeiter-verwendung-treecol-updateamum" label="Geaendert am" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#updateamum"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-updatevon" label="Geaendert von" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#updatevon"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-insertamum" label="Angelegt am" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#insertamum"  onclick="MitarbeiterTreeVerwendungSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="mitarbeiter-verwendung-treecol-insertvon" label="Angelegt von" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bisverwendung/rdf#insertvon"  onclick="MitarbeiterTreeVerwendungSort()"/>
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
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#vertragsstunden" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beginn" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ende" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba1code" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#ba2code" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#beschausmasscode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#verwendungcode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#mitarbeiter_uid" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#hauptberufcode" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#bisverwendung_id" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#updateamum" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#updatevon" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#insertamum" />
								<treecell label="rdf:http://www.technikum-wien.at/bisverwendung/rdf#insertvon" />
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
		<vbox>
			<button id="mitarbeiter-verwendung-button-neu" label="Neu" disabled="true" oncommand="MitarbeiterVerwendungNeu()" />
			<button id="mitarbeiter-verwendung-button-bearbeiten" label="Bearbeiten" disabled="true" oncommand="MitarbeiterVerwendungBearbeiten()"/>
			<button id="mitarbeiter-verwendung-button-loeschen" label="Loeschen" disabled="true" oncommand="MitarbeiterVerwendungLoeschen()"/>
		</vbox>
	</hbox>


	<groupbox id="mitarbeiter-detail-groupbox-funktion" hidden="true">
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
			<hbox>
				<vbox>
					<button id="mitarbeiter-funktion-button-neu" label="Neu" disabled="true" oncommand="MitarbeiterFunktionNeu()"/>
					<button id="mitarbeiter-funktion-button-loeschen" label="Loeschen" disabled="true" oncommand="MitarbeiterFunktionLoeschen()"/>
				</vbox>
				<vbox flex="1">
					<checkbox id="mitarbeiter-funktion-detail-checkbox-neu" checked="true" hidden="true" />
					<textbox id="mitarbeiter-funktion-detail-textbox-studiengang" hidden="true" />
					<groupbox id="mitarbeiter-funktion-detail-groupbox" flex="1">
						<caption label="Details" />
						<grid id="mitarbeiter-funktion-detail-grid" style="margin:4px;" flex="1">
						  	<columns  >
								<column flex="1"/>
								<column flex="5"/>
							</columns>
							<rows>
								<row>
									<label value="Studiengang" control="mitarbeiter-funktion-detail-menulist-studiengang"/>
									<menulist id="mitarbeiter-funktion-detail-menulist-studiengang" disabled="true"
									          datasources="<?php echo APP_ROOT ?>rdf/studiengang.rdf.php" flex="1"
								              ref="http://www.technikum-wien.at/studiengang/liste" >
										<template>
											<menupopup>
												<menuitem value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_kz"
									        		      label="rdf:http://www.technikum-wien.at/studiengang/rdf#kuerzel"
												  		  uri="rdf:*"/>
												</menupopup>
										</template>
									</menulist>
								</row>
								<row>
									<label value="SWS" control="mitarbeiter-funktion-detail-textbox-sws"/>
									<textbox id="mitarbeiter-funktion-detail-textbox-sws" maxlenght="7" size="7" disabled="true"/>
								</row>
								<row>
									<spacer />
									<button id="mitarbeiter-funktion-detail-button-speichern" label="Speichern" disabled="true" oncommand="MitarbeiterFunktionSpeichern()" />
								</row>
							</rows>
						</grid>
					</groupbox>
				</vbox>
			</hbox>
		</hbox>
	</groupbox>
</groupbox>
<groupbox id="mitarbeiter-detail-groupbox-entwicklungsteam">
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
			<button id="mitarbeiter-entwicklungsteam-button-neu" label="Neu" disabled="true" oncommand="MitarbeiterEntwicklungsteamNeu()"/>
			<button id="mitarbeiter-entwicklungsteam-button-loeschen" label="Loeschen" disabled="true" oncommand="MitarbeiterEntwicklungsteamLoeschen()"/>
		</vbox>
		<vbox>
			<checkbox id="mitarbeiter-entwicklungsteam-detail-checkbox-neu" checked="true" hidden="true" />
			<textbox id="mitarbeiter-entwicklungsteam-detail-textbox-studiengang" hidden="true" />
			<groupbox id="mitarbeiter-entwicklungsteam-detail-groupbox" flex="1">
				<caption label="Details" />
				<grid id="mitarbeiter-entwicklungsteam-detail-grid" style="margin:4px;" flex="1">
				  	<columns  >
						<column flex="1"/>
						<column flex="5"/>
					</columns>
					<rows>
						<row>
							<label value="Studiengang" control="mitarbeiter-entwicklungsteam-detail-menulist-studiengang"/>
							<menulist id="mitarbeiter-entwicklungsteam-detail-menulist-studiengang" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/studiengang.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/studiengang/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_kz"
							        		      label="rdf:http://www.technikum-wien.at/studiengang/rdf#kuerzel"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Besondere Qualifikation" control="mitarbeiter-entwicklungsteam-detail-menulist-besqual"/>
							<menulist id="mitarbeiter-entwicklungsteam-detail-menulist-besqual" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/besonderequalifikation.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/besonderequalifikation/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/besonderequalifikation/rdf#besqualcode"
							        		      label="rdf:http://www.technikum-wien.at/besonderequalifikation/rdf#besqualbez"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Beginn" control="mitarbeiter-entwicklungsteam-detail-datum-beginn"/>
							<box id="mitarbeiter-entwicklungsteam-detail-datum-beginn" class="Datum" disabled="true"/>
						</row>
						<row>
							<label value="Ende" control="mitarbeiter-entwicklungsteam-detail-datum-ende"/>
							<box id="mitarbeiter-entwicklungsteam-detail-datum-ende" class="Datum" disabled="true"/>
						</row>
						<row>
							<spacer />
							<button id="mitarbeiter-entwicklungsteam-detail-button-speichern" label="Speichern" disabled="true" oncommand="MitarbeiterEntwicklungsteamSpeichern()" />
						</row>
					</rows>
				</grid>
			</groupbox>
		</vbox>
	</hbox>
</groupbox>
</vbox>
</overlay>
