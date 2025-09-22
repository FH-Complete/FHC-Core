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
require_once('../../config/global.config.inc.php');


if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
	$punktehidden = 'false';
else
	$punktehidden = 'true';

if(defined('FAS_STUDSTATUS_SHOW_KOMM_PRFG_HINT') && FAS_STUDSTATUS_SHOW_KOMM_PRFG_HINT)
    $show_komm_prfg_hint = true;
else
    $show_komm_prfg_hint = false;

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="StudentPruefung"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Pruefung Overlay -->
<vbox id="student-pruefung" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="student-pruefung-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentPruefungDelete();" id="student-pruefung-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="student-pruefung-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="2"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<vbox>
						<hbox>
							<spacer flex="1"/>
							<button type="checkbox" id="student-pruefung-button-filterstsem" label="Alle Studiensemester anzeigen" oncommand="StudentPruefungFilterStsem();" disabled="true"/>
						</hbox>
					<tree id="student-pruefung-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/pruefung/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						context="student-pruefung-tree-popup"
						flags="dont-build-content"
					>

						<treecols>
							<treecol id="student-pruefung-tree-datum" label="Datum" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#datum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-datumISO" label="DatumISO" flex="2" hidden="true" ignoreincolumnpicker="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#datum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#lehrveranstaltung_bezeichnung"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-note_bezeichnung" label="Note" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#note_bezeichnung" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-anmerkung" label="Anmerkung" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#anmerkung" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-typ" label="Typ" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#pruefungstyp_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-pruefung_id" label="PruefungID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#pruefung_id" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#lehreinheit_id" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-student_uid" label="StudentInUID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#student_uid" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-mitarbeiter_uid" label="MitarbeiterInUID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#mitarbeiter_uid" />
							<splitter class="tree-splitter"/>
							<treecol id="student-pruefung-tree-punkte" label="Punkte" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/pruefung/rdf#punkte" />
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#datum"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#datum_iso"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#lehrveranstaltung_bezeichnung"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#note_bezeichnung"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#anmerkung"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#pruefungstyp_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#pruefung_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#lehreinheit_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#student_uid"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#mitarbeiter_uid"/>
										<treecell label="rdf:http://www.technikum-wien.at/pruefung/rdf#punkte"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					</vbox>
					<vbox>
						<hbox>
							<button id="student-pruefung-button-neu" label="Neu" oncommand="StudentPruefungNeu();" disabled="true"/>
							<button id="student-pruefung-button-loeschen" label="Loeschen" oncommand="StudentPruefungDelete();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="Pruefung_id" control="student-pruefung-textbox-pruefung_id"/>
							<textbox id="student-pruefung-textbox-pruefung_id" disabled="true"/>
							<label value="Neu" control="student-pruefung-checkbox-neu"/>
							<checkbox id="student-pruefung-checkbox-neu" disabled="true" checked="false"/>
						</vbox>
						<groupbox id="student-pruefung-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="student-pruefung-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Lehrveranstaltung" control="student-pruefung-menulist-lehrveranstaltung"/>
										<menulist id="student-pruefung-menulist-lehrveranstaltung" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/lehrveranstaltung/liste"
										          oncommand="StudentPruefungLVAChange()">
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
										<label value="LV-Teil" control="student-pruefung-menulist-lehreinheit"/>
										<menulist id="student-pruefung-menulist-lehreinheit" disabled="true"
										          datasources="rdf:null" flex="1" style="max-width: 600px"
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
										<label value="MitarbeiterIn" control="student-pruefung-menulist-mitarbeiter"/>
										<menulist id="student-pruefung-menulist-mitarbeiter" disabled="true"
										          datasources="rdf:null" flex="1"
										          ref="http://www.technikum-wien.at/mitarbeiter/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
										        		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Typ" control="student-pruefung-menulist-typ"/>
										<menulist id="student-pruefung-menulist-typ" disabled="true"
										          datasources="<?php echo APP_ROOT; ?>rdf/pruefungstyp.rdf.php?abschluss=false" flex="1"
										          ref="http://www.technikum-wien.at/pruefungstyp/liste"
										          oncommand="StudentPruefungTypChange()">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/pruefungstyp/rdf#pruefungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/pruefungstyp/rdf#beschreibung"
													  		  uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Note" control="student-pruefung-menulist-note"/>
										<menulist id="student-pruefung-menulist-note" disabled="true"
												  xmlns:NOTE="http://www.technikum-wien.at/note/rdf#"
												  datasources="<?php echo APP_ROOT; ?>rdf/note.rdf.php" flex="1"
												  ref="http://www.technikum-wien.at/note/liste" >
											<template>
												<rule NOTE:aktiv='false'>
													<menupopup>
														<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
																  label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
																  uri="rdf:*" style="text-decoration:line-through;"/>
													</menupopup>
												</rule>
												<rule>
													<menupopup>
														<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
																  label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
																  uri="rdf:*"/>
													</menupopup>
												</rule>
											</template>
										</menulist>
									</row>
									<row hidden="<?php echo $punktehidden; ?>">
										<label value="Punkte" control="student-pruefung-textbox-punkte"/>
										<hbox>
											<textbox id="student-pruefung-textbox-punkte" disabled="true" checked="false"/>
											<spacer flex="1" />
										</hbox>
									</row>
									<row>
										<label value="Datum" control="student-pruefung-textbox-datum"/>
										<hbox>
					      					<box class="Datum" id="student-pruefung-textbox-datum" disabled="true"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
<?php if($show_komm_prfg_hint) { ?>
									<row id="student-pruefung-textbox-datum-hinweis" hidden="true">
										<label><image width="20" class="message-icon"/></label>
										<textbox readonly="true" multiline="true" rows="3" value="Bitte bei Neuanlage einer kommissionellen Prüfung das Datum der Noteneintragung (i. d. R. heute) eintragen, um den korrekten Fristenablauf der Wiederholung zu ermöglichen. Das Datum der Prüfung bitte im Anmerkungsfeld vermerken."></textbox>
									</row>
<?php } ?>
					      			<row>
					      				<label value="Anmerkung" control="student-pruefung-textbox-anmerkung"/>
							      		<textbox id="student-pruefung-textbox-anmerkung" disabled="true" maxlength="256"/>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="student-pruefung-button-speichern" oncommand="StudentPruefungDetailSpeichern()" label="Speichern" disabled="true"/>
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
