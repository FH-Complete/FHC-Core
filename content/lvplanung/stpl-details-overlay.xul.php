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

<overlay id="STPLDetailsOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/studenten.js" />

	<!-- ************************* -->
	<!-- *  Stundenplan Details  * -->
	<!-- ************************* -->
	<vbox id="vboxSTPLDetailsListe">
		<popupset>
			<menupopup id="stpldetail-treestpldetail-popup">
				<menuitem label="Bearbeiten" oncommand="STPLDetailEdit()" />
				<menuitem label="Entfernen" oncommand="STPLDetailDelete()" />
			</menupopup>
		</popupset>

		<tree id="treeStplDetails" seltype="single" hidecolumnpicker="false" flex="1"
			datasources="../rdf/lehrstunde.rdf.php" ref="http://www.technikum-wien.at/lehrstunde/alle"
			flags="dont-build-content"
			enableColumnDrag="true"
			style="margin:0px;"
			context="stpldetail-treestpldetail-popup"
			ondblclick="STPLDetailEdit()"
			>
		<treecols>
			<treecol id="lehreinheit_id" label="LE_ID" flex="2" primary="false"
				class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
				persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehreinheit_id"  />
			<splitter class="tree-splitter"/>
			<treecol id="stplLektor" label="LektorIn" flex="2" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lektor" />
			<splitter class="tree-splitter"/>
			<treecol id="stplLehrfachKurzbz" label="Fach" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrfach" />
			<splitter class="tree-splitter"/>
			<treecol id="stplLehrform" label="Form" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrform" />
			<splitter class="tree-splitter"/>
			<treecol id="stplLehrfachBezeichnung" label="Lehrfach" flex="20" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrfach_bez" />
			<splitter class="tree-splitter"/>
			<treecol id="stpl_studiengang" label="Studiengang" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#studiengang" />
			<splitter class="tree-splitter"/>
			<treecol id="stplSemester" label="S" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#sem" />
			<splitter class="tree-splitter"/>
			<treecol id="stplVerband" label="V" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#ver" />
			<splitter class="tree-splitter"/>
			<treecol id="gruppe" label="G" flex="1" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#grp" />
			<splitter class="tree-splitter"/>
			<treecol id="stpl_einheit" label="SpzGrp" flex="3" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe" />
			<splitter class="tree-splitter"/>
			<treecol id="stplOrt" label="Ort" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#ort_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="stpl_datum" label="Datum" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#datum" />
			<splitter class="tree-splitter"/>
			<treecol id="stpl_stunde" label="Std" flex="1" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#stunde" />
			<splitter class="tree-splitter"/>
			<treecol id="stplUNR" label="UNR" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#unr"  />
			<splitter class="tree-splitter"/>
			<treecol id="stundenplan_id" label="StundenplanID" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#id"  />
			<splitter class="tree-splitter"/>
			<treecol id="anzahlstudenten" label="AnzahlStudenten" flex="2" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anzahlstudenten"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-anmerkung" label="Anmerkung" flex="2" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anmerkung"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-anmerkung_lehreinheit" label="AnmerkungLE" flex="2" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anmerkung_lehreinheit"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-titel" label="Titel" flex="2" hidden="false"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#titel"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-gruppe_bezeichnung" label="Gruppe Bezeichnung" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe_bezeichnung"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-gruppe_beschreibung" label="Gruppe Beschreibung" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe_beschreibung"  />
			<splitter class="tree-splitter"/>
			<treecol id="stpl-details-overlay-lehrstunde-reservierung" label="Reservierung" flex="2" hidden="true"
				class="sortDirectionIndicator" persist="hidden, width, ordinal"
				sort="rdf:http://www.technikum-wien.at/lehrstunde/rdf#reservierung"  />
			<splitter class="tree-splitter"/>
		</treecols>

		<template>
			<rule>
				<treechildren>
					<treeitem uri="rdf:*">
   						<treerow>
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehreinheit_id"   />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lektor" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrfach" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrform" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#lehrfach_bez" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#studiengang" />
  							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#sem" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#ver" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#grp" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#ort_kurzbz" />
   							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#datum" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#stunde" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#unr" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#id" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anzahlstudenten" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anmerkung" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#anmerkung_lehreinheit" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#titel" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe_bezeichnung" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#gruppe_beschreibung" />
							<treecell label="rdf:http://www.technikum-wien.at/lehrstunde/rdf#reservierung" />
   						</treerow>
					</treeitem>
				</treechildren>
			</rule>
		</template>
		</tree>
	</vbox>
</overlay>
