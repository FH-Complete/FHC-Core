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

<overlay id="StudentProjektarbeitOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentprojektarbeit.js.php" />

<!-- Projektarbeit DETAILS -->
<vbox id="student-projektarbeit" style="overflow:auto;" flex="1">
<popupset>
	<popup id="student-projektarbeit-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentProjektarbeitLoeschen();" id="student-projektarbeit-tree-popup-delete" hidden="false"/>
	</popup>
</popupset>

	<hbox flex="1">
		<tree id="student-projektarbeit-tree" seltype="single" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/projektarbeit/liste"
			style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
			onselect="StudentProjektarbeitAuswahl()"
			context="student-projektarbeit-tree-popup"
			flags="dont-build-content"
		>
		
			<treecols>
				<treecol id="student-projektarbeit-tree-projekttyp_kurzbz" label="Typ" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sortActive="true"
					sortDirection="ascending"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#projekttyp_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-titel" label="Titel" flex="1" hidden="false"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#titel"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-note" label="Note" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#note" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-punkte" label="Punkte" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#punkte" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-beginn" label="Beginn" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#beginn" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-ende" label="Ende" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#ende" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-faktor" label="Faktor" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#faktor" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-freigegeben" label="Freigegeben" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#freigegeben" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-gesperrtbis" label="Gesperrt bis" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#gesperrtbis" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-stundensatz" label="stundensatz" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#stundensatz" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-gesamtstunden" label="Gesamtstunden" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#gesamtstunden" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-themenbereich" label="Themenbereich" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#themenbereich" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-anmerkung" label="Anmerkung" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#anmerkung" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-projektarbeit_id" label="ProjektarbeitID" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#projektarbeit_id" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#lehreinheit_id" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-student_uid" label="StudentUID" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#student_uid" />
				<splitter class="tree-splitter"/>
				<treecol id="student-projektarbeit-tree-firma_id" label="FirmaID" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektarbeit/rdf#firma_id" />
				<splitter class="tree-splitter"/>
			</treecols>
		
			<template>
				<treechildren flex="1" >
						<treeitem uri="rdf:*">
						<treerow>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#projekttyp_kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#titel"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#note"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#punkte"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#beginn"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#ende"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#faktor"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#freigegeben"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#gesperrtbis"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#stundensatz"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#gesamtstunden"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#themenbereich"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#anmerkung"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#projektarbeit_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#lehreinheit_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#student_uid"/>
							<treecell label="rdf:http://www.technikum-wien.at/projektarbeit/rdf#firma_id"/>
						</treerow>
					</treeitem>
				</treechildren>
			</template>
		</tree>
		<vbox>
			<button id="student-projektarbeit-button-neu" label="Neu" oncommand="StudentProjektarbeitNeu();" disabled="true"/>
			<button id="student-projektarbeit-button-loeschen" label="Loeschen" oncommand="StudentProjektarbeitLoeschen();" disabled="true"/>
		</vbox>
	</hbox>
	<hbox>						
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-projektarbeit-checkbox-neu" checked="true" />
	  		<label value="Projektarbeit ID"/>
	  		<textbox id="student-projektarbeit-textbox-projektarbeit_id" disabled="true"/>
		</vbox>
		<groupbox flex="1">
		<caption label="Details"/>
				<grid style="margin:4px;" flex="1">
				  	<columns  >
						<column flex="1"/>
						<column flex="5"/>
					</columns>
					<rows>
						<row>
							<label value="Titel" control="student-projektarbeit-textbox-titel"/>
							<textbox id="student-projektarbeit-textbox-titel" disabled="true" maxlength="256" />
						</row>
						<row>
							<label value="Themenbereich" control="student-projektarbeit-textbox-themenbereich"/>
							<textbox id="student-projektarbeit-textbox-themenbereich" disabled="true" maxlength="64"/>
						</row>
						<row>
							<label value="Typ" control="student-projektarbeit-menulist-projekttyp"/>
							<menulist id="student-projektarbeit-menulist-projekttyp" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/projekttyp.rdf.php" flex="1"
							          ref="http://www.technikum-wien.at/projekttyp/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/projekttyp/rdf#projekttyp_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/projekttyp/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Firma" control="student-projektarbeit-menulist-firma"/>
							<menulist id="student-projektarbeit-menulist-firma" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/firma.rdf.php?optional=true" flex="1"
							          ref="http://www.technikum-wien.at/firma/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
							        		      label="rdf:http://www.technikum-wien.at/firma/rdf#name"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Lehrveranstaltung" control="student-projektarbeit-menulist-lehrveranstaltung"/>
							<menulist id="student-projektarbeit-menulist-lehrveranstaltung" disabled="true"
							          datasources="rdf:null" flex="1"
							          ref="http://www.technikum-wien.at/lehrveranstaltung/liste" 
							          oncommand="StudentProjektarbeitLVAChange()"
							          >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"
							        		      label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#bezeichnung (rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#semester Sem)"
										  		  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Lehreinheit" control="student-projektarbeit-menulist-lehreinheit"/>
							<menulist id="student-projektarbeit-menulist-lehreinheit" disabled="true"
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
					</rows>
				</grid>
				<grid style="margin:4px;" flex="1">
				  	<columns  >
						<column flex="1"/>
						<column flex="5"/>
						<column flex="1"/>
						<column flex="5"/>
					</columns>
					<rows>
						<row>
							<label value="Gesamtpunkte" control="student-projektarbeit-textbox-punkte" />
							<hbox>
								<textbox id="student-projektarbeit-textbox-punkte" maxlength="5" size="5" disabled="true"/>
							</hbox>
							<label value="Gesamtnote" control="student-projektarbeit-menulist-note"/>
							<menulist id="student-projektarbeit-menulist-note" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php?optional=true" flex="1"
							          ref="http://www.technikum-wien.at/note/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
							        		      label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
										  		  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Beginn" control="student-projektarbeit-datum-beginn"/>
							<box class="Datum" id="student-projektarbeit-datum-beginn" disabled="true" />
							<label value="Ende" control="student-projektarbeit-datum-ende"/>
							<box class="Datum" id="student-projektarbeit-datum-ende" disabled="true" />
						</row>
						<row>
							<label value="Freigegeben" control="student-projektarbeit-checkbox-freigegeben"/>
							<checkbox id="student-projektarbeit-checkbox-freigegeben" disabled="true" />
							<label value="Gesperrt bis" control="student-projektarbeit-datum-gesperrtbis"/>
							<box class="Datum" id="student-projektarbeit-datum-gesperrtbis" disabled="true" />
						</row>
						<row>
							<label value="Anmerkung" control="student-projektarbeit-textbox-anmerkung"/>
							<textbox id="student-projektarbeit-textbox-anmerkung" disabled="true" maxlength="256"/>
						</row>
						<row>
							<spacer />
							<hbox>
								<spacer flex="1" />
								<button id="student-projektarbeit-button-speichern" oncommand="StudentProjektarbeitSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</row>
					</rows>
				</grid>
				<vbox hidden="true">
					<!-- Stundensatz/Faktor und Gesamtstunden werden nicht mehr benoetigt -->
					<label value="Stundensatz" control="student-projektarbeit-textbox-stundensatz"/>
					<hbox>
						<textbox id="student-projektarbeit-textbox-stundensatz" disabled="true" maxlength="5" size="5"/>
					</hbox>
					<label value="Faktor" control="student-projektarbeit-textbox-faktor"/>
					<hbox>
						<textbox id="student-projektarbeit-textbox-faktor" disabled="true" maxlength="3" size="3"/>
					</hbox>
				
					<label value="Gesamtstunden" control="student-projektarbeit-textbox-gesamtstunden"/>
					<hbox>
						<textbox id="student-projektarbeit-textbox-gesamtstunden" disabled="true" maxlength="8" size="8"/>
					</hbox>
				</vbox>
			</groupbox>
			<groupbox flex="1">
				<caption label="Betreuer"/>
				<hbox>
					<tree id="student-projektbetreuer-tree" seltype="single" hidecolumnpicker="false" flex="1"
						  datasources="rdf:null" ref="http://www.technikum-wien.at/projektbetreuer/liste"
						  style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						  onselect="StudentProjektbetreuerAuswahl()"
						  context="student-projektbetreuer-tree-popup"
						  flags="dont-build-content"
					>					
							<treecols>
								<treecol id="student-projektbetreuer-tree-nachname" label="Nachname" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sortActive="true"
									sortDirection="ascending"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_nachname"/>
								<treecol id="student-projektbetreuer-tree-vorname" label="Vorname" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_vorname"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-name" label="Name" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#name"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-note" label="Note" flex="1" hidden="false"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#note"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-faktor" label="Faktor" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#faktor" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-punkte" label="Punkte" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#punkte" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-stunden" label="Stunden" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#stunden" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-stundensatz" label="Stundensatz" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#stundensatz" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-betreuerart_kurzbz" label="Art" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#betreuerart_kurzbz" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-person_id" label="Person_id" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_id" />
								<splitter class="tree-splitter"/>
								<treecol id="student-projektbetreuer-tree-projektarbeit_id" label="ProjektarbeitID" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#projektarbeit_id" />
								<splitter class="tree-splitter"/>
							</treecols>
						
							<template>
								<treechildren flex="1" >
										<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_nachname"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_vorname"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#name"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#note"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#faktor"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#punkte"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#stunden"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#stundensatz"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#betreuerart_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#person_id"/>
											<treecell label="rdf:http://www.technikum-wien.at/projektbetreuer/rdf#projektarbeit_id"/>
										</treerow>
									</treeitem>
								</treechildren>
							</template>
						</tree>
						<vbox>
							<button id="student-projektbetreuer-button-neu" label="Neu" oncommand="StudentProjektbetreuerNeu();" disabled="true"/>
							<button id="student-projektbetreuer-button-loeschen" label="Loeschen" oncommand="StudentProjektbetreuerLoeschen();" disabled="true"/>
						</vbox>
					</hbox>
					<vbox hidden="true">
						<textbox id="student-projektbetreuer-textbox-person_id" />
						<textbox id="student-projektbetreuer-textbox-betreuerart_kurzbz_old" />
						<checkbox id="student-projektbetreuer-checkbox-neu" />
						<label value="Name" control="student-projektbetreuer-textbox-name"/>
						<textbox id="student-projektbetreuer-textbox-name" disabled="true" maxlength="32"/>
					</vbox>
					<grid style="margin:4px;" flex="1">
					  	<columns  >
							<column flex="1"/>
							<column flex="5"/>
						</columns>
						<rows>
							<row>
								<label value="Betreuer" control="student-projektbetreuer-menulist-person" />
				    			<menulist id="student-projektbetreuer-menulist-person"
										  editable="true" disabled="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/person/liste" 
								          oninput="StudentProjektbetreuerMenulistPersonLoad(this)"
								          oncommand="StudentProjektbetreuerLoadMitarbeiterDaten()">
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/person/rdf#person_id"
								        		      label="rdf:http://www.technikum-wien.at/person/rdf#anzeigename"
											  		  uri="rdf:*"/>
										</menupopup>
									</template>
								</menulist>
							</row>
							<row>
								<button id="student-projektbetreuer-button-neueperson" label="Neue Person anlegen" oncommand="StudentProjektbetreuerNeuePerson()" disabled="true"/>
								<button id="student-projektbetreuer-button-kontaktdaten" label="Kontakttdaten bearbeiten" oncommand="StudentProjektbetreuerKontaktdaten()" disabled="true"/>
							</row>
							<row>
								<label value="Art" control="student-projektbetreuer-menulist-betreuerart"/>
								<menulist id="student-projektbetreuer-menulist-betreuerart" disabled="true"
								          datasources="<?php echo APP_ROOT ?>rdf/betreuerart.rdf.php" flex="1"
								          ref="http://www.technikum-wien.at/betreuerart/liste" >
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/betreuerart/rdf#betreuerart_kurzbz"
								        		      label="rdf:http://www.technikum-wien.at/betreuerart/rdf#beschreibung"
											  		  uri="rdf:*"/>
										</menupopup>
									</template>
								</menulist>
							</row>
							<row>
								<label value="Note" control="student-projektbetreuer-menulist-note"/>
								<menulist id="student-projektbetreuer-menulist-note" disabled="true"
								          datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php?optional=true" flex="1"
								          ref="http://www.technikum-wien.at/note/liste" >
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
								        		      label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
											  		  uri="rdf:*"/>
										</menupopup>
									</template>
								</menulist>
							</row>
							<row>
								<label value="Punkte" control="student-projektbetreuer-textbox-punkte"/>
								<textbox id="student-projektbetreuer-textbox-punkte" disabled="true" maxlength="6"/>
							</row>
							<row>
								<label value="Stunden" control="student-projektbetreuer-textbox-stunden"/>
								<textbox id="student-projektbetreuer-textbox-stunden" disabled="true" maxlength="8"/>
							</row>
							<row>
								<label value="Stundensatz" control="student-projektbetreuer-textbox-stundensatz"/>
								<textbox id="student-projektbetreuer-textbox-stundensatz" disabled="true" maxlength="5"/>
							</row>
							<row>
								<label value="Faktor" control="student-projektbetreuer-textbox-faktor"/>
								<textbox id="student-projektbetreuer-textbox-faktor" disabled="true" maxlength="3"/>
							</row>
							<row>
								<spacer />
								<hbox>
									<spacer flex="1" />
									<button id="student-projektbetreuer-button-speichern" label="Speichern" oncommand="StudentProjektbetreuerSpeichern()" />
								</hbox>
							</row>
						</rows>
					</grid>
			</groupbox>
	</hbox>		

<spacer flex="1" />
</vbox>

</overlay>