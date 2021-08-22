<?php
/* Copyright (C) 2006 fhcomplete.org
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
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungdetailoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungnotenoverlay.xul.php"?>';
?>
<overlay id="LehrveranstaltungOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lvplanung/lehrveranstaltungoverlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Lehrveranstaltung   * -->
	<!-- ************************ -->
	<vbox id="LehrveranstaltungEditor" flex="1" uid="" stg_kz="">
	<popupset>
		<menupopup id="lehrveranstaltung-tree-popup">
			<menuitem label="Entfernen" oncommand="LeDelete();" id="lehrveranstaltung-tree-popup-entf" disabled="false"/>
		</menupopup>
	</popupset>
		<toolbox>
			<toolbar id="lehrveranstaltung-nav-toolbar">
			<toolbarbutton id="lehrveranstaltung-toolbar-neu" label="Neuer LV-Teil" oncommand="LeNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neue Lehreinheit anlegen" />
			<toolbarbutton id="lehrveranstaltung-toolbar-del" label="Loeschen" oncommand="LeDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Lehreinheiten löschen"/>
			<toolbarbutton id="lehrveranstaltung-toolbar-refresh" label="Aktualisieren" oncommand="LvTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			<toolbarbutton id="lehrveranstaltung-toolbar-lehrauftrag" label="Lehrauftrag" oncommand="LvCreateLehrauftrag()" disabled="false" image="../skin/images/person.gif" tooltiptext="Lehrauftrag ausdrucken" hidden="true"/>

			<toolbarbutton label="Ausbildungssemester " id="lehrveranstaltung-toolbar-filter-ausbildungssemester" type="menu" hidden="true">
				<menupopup id="lehrveranstaltung-toolbar-popup-filter-ausbildungssemester" >
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-alle" type="radio" checked="true" label="Alle Semester" oncommand="FilterLehrveranstaltungAusbsem('')" disabled="false" tooltiptext="Alle Semester anzeigen"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-1" type="radio" label="1. Semester" oncommand="FilterLehrveranstaltungAusbsem('1')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-2" type="radio" label="2. Semester" oncommand="FilterLehrveranstaltungAusbsem('2')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-3" type="radio" label="3. Semester" oncommand="FilterLehrveranstaltungAusbsem('3')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-4" type="radio" label="4. Semester" oncommand="FilterLehrveranstaltungAusbsem('4')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-5" type="radio" label="5. Semester" oncommand="FilterLehrveranstaltungAusbsem('5')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-6" type="radio" label="6. Semester" oncommand="FilterLehrveranstaltungAusbsem('6')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-7" type="radio" label="7. Semester" oncommand="FilterLehrveranstaltungAusbsem('7')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-8" type="radio" label="8. Semester" oncommand="FilterLehrveranstaltungAusbsem('8')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-9" type="radio" label="9. Semester" oncommand="FilterLehrveranstaltungAusbsem('9')" disabled="false"/>
					<menuitem id="lehrveranstaltung-toolbar-filter-ausbildungssemester-10" type="radio" label="10. Semester" oncommand="FilterLehrveranstaltungAusbsem('10')" disabled="false"/>
			    </menupopup>
			</toolbarbutton>
			<textbox id="lehrveranstaltung-toolbar-textbox-suche" control="lehrveranstaltung-toolbar-button-search" onkeypress="LehrveranstaltungSearchFieldKeyPress(event)" style="width: 300px" />
			<button id="lehrveranstaltung-toolbar-button-search" oncommand="LehrveranstaltungSuche()" label="Suchen"/>
			<spacer flex="1" />
			<toolbarbutton id="lehrveranstaltung-toolbar-opensubtrees" label="Aufklappen" tooltiptext="Klappt die Untermenüs auf - mehrmaliges klicken möglich um weiter aufzuklappen" oncommand="LvTreeOpenAllSubtrees()" disabled="false" image="../skin/images/tree-diagramm.png" />
			</toolbar>
		</toolbox>

		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="lehrveranstaltung-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/lehrveranstaltung_einheiten"
				style="margin:0px;" enableColumnDrag="true"
				onselect="LeAuswahl(this);"
 				persist="height"
 				onkeypress="LvTreeKeyPress(event)"
 				flags="dont-build-content"
 				context="lehrveranstaltung-tree-popup"
		>
			<treecols>
				<treecol id="lehrveranstaltung-treecol-kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-bezeichnung" label="Bezeichnung" flex="5" hidden="false" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-bezeichnung_english" label="Bezeichnung Englisch" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung_english"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-sprache" label="Sprache" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#sprache" />
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-ects" label="ECTS" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#ects" />
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-semesterstunden" label="Semesterstunden" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semesterstunden"/>
				<treecol id="lehrveranstaltung-treecol-planstunden" label="Planstunden" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#planstunden"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lehre" label="Lehre" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehre"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lehrform" label="Lehrform" flex="5" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrform_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-stundenblockung" label="Blockung" flex="5" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#stundenblockung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-wochenrythmus" label="WR" flex="5" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#wochenrythmus"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-startkw" label="StartKW" flex="5" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#startkw"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-raumtyp" label="Raumtyp" flex="5" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtyp"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-raumtypalternativ" label="RaumtypAlt" flex="5" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtypalternativ"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-gruppen" label="Gruppen" flex="5" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#gruppen"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lektoren" label="Lektoren" flex="5" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lektoren"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-anmerkung" label="Anmerkung" flex="5" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#anmerkung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-fachbereich" label="Institut" flex="1" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#fachbereich"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-studiengang" label="Studiengang" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studiengang"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-semester" label="Semester" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semester"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-orgform_kurzbz" label="Organisationsform" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#orgform_kurzbz"	/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lehrveranstaltung_id" label="Lehrveranstaltung_id" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id"	/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lehreinheit_id" label="Lehreinheit_id" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehreinheit_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-studienplan_id" label="studienplan_id" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studienplan_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-studienplan_bezeichnung" label="Studienplan" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studienplan_bezeichnung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-lehrtyp_kurzbz" label="Lehrtyp" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrtyp_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="lehrveranstaltung-treecol-studiensemester_kurzbz" label="Studiensemester" flex="1" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studiensemester_kurzbz"/>
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<treechildren flex="1" >
   					<treeitem uri="rdf:*">
						<treerow properties="lehrveranstaltung_rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrtyp_kurzbz" dbID="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id" >
							<treecell src="../skin/images/lehrtyp_rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrtyp_kurzbz^.png" label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung_english"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#sprache"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#ects"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semesterstunden"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#planstunden"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehre"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrform_kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#stundenblockung"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#wochenrythmus"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#startkw"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtyp"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtypalternativ"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#gruppen"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lektoren"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#anmerkung"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#fachbereich"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studiengang"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semester"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#orgform_kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id"  />
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehreinheit_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studienplan_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studienplan_bezeichnung"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrtyp_kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#studiensemester_kurzbz"/>
						</treerow>
					</treeitem>
				</treechildren>
			</template>
		</tree>

		<splitter collapse="after" persist="state">
			<grippy />
		</splitter>

		<!-- ************ -->
		<!-- *  Detail  * -->
		<!-- ************ -->
		<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
			<tabbox id="lehrveranstaltung-tabbox" flex="3" orient="vertical">
				<tabs orient="horizontal" id="lehrveranstaltung-tabs">
					<tab id="lehrveranstaltung-tab-detail" label="Details" />
					<tab id="lehrveranstaltung-tab-lektor" label="LektorInnenzuteilung" />
					<tab id="lehrveranstaltung-tab-noten" label="Noten" />
					<tab id="lehrveranstaltung-tab-notizen" label="Notizen" />
					<tab id="lehrveranstaltung-tab-lvangebot" label="LV-Angebot" />
					<tab id="lehrveranstaltung-tab-termine" label="Termine" onclick="LehrveranstaltungTermineIFrameLoad()"/>
					<?php
                    if($rechte->isBerechtigt('student/anwesenheit'))
						echo '<tab id="lehrveranstaltung-tab-anwesenheit" label="Anwesenheit" onclick="LehrveranstaltungAnwesenheitIFrameLoad();"/>';
					?>

				</tabs>
				<tabpanels id="lehrveranstaltung-tabpanels-main" flex="1">
					<vbox id="lehrveranstaltung-detail" />
					<vbox id="lehrveranstaltung-lektorzuteilung" />
					<vbox id="lehrveranstaltung-noten" />
					<vbox id="lehrveranstaltung-notiz">
						<box class="Notiz" flex="1" id="lehrveranstaltung-box-notizen"/>
					</vbox>
					<vbox id="lehrveranstaltung-lvangebot" />
					<iframe id="lehrveranstaltung-termine" src="" style="margin-top:10px;" />
					<?php
                       if($rechte->isBerechtigt('student/anwesenheit'))
							echo '<iframe id="lehrveranstaltung-anwesenheit" src="" style="margin-top:10px;" />';
                     ?>
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>
