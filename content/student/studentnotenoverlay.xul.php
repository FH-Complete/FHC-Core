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
require_once('../../config/global.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
	$punktehidden = 'false';
else
	$punktehidden = 'true';

echo "<?xml-stylesheet href=\"".APP_ROOT."content/bindings.css\" type=\"text/css\" ?>";
?>

<overlay id="StudentKonto"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="student-noten" style="overflow:auto;margin:0px;" flex="1">
<popupset>
	<menupopup id="student-noten-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentNotenDelete();" id="student-noten-tree-popup-delete" hidden="false"/>
		<menuitem label="Zertifikat erstellen" oncommand="StudentFFZertifikatPrint(event);" id="student-noten-tree-popup-ffzertifikat" hidden="false"/>
		<menuitem label="Zertifikat archivieren mit Signatur" oncommand="StudentFFZertifikatPrint(event, true);" id="student-noten-tree-popup-ffzertifikat" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis erstellen" oncommand="StudentLVZeugnisPrint(event, 'German');" id="student-noten-tree-popup-lvzeugnis" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis archivieren mit Signatur" oncommand="StudentLVZeugnisPrint(event, 'German', true);" id="student-noten-tree-popup-lvzeugnis" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Englisch erstellen" oncommand="StudentLVZeugnisPrint(event, 'English');" id="student-noten-tree-popup-lvzeugnis_eng" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Englisch archivieren mit Signatur" oncommand="StudentLVZeugnisPrint(event, 'English', true);" id="student-noten-tree-popup-lvzeugnis_eng" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1" style="margin-top: 10px;">
	<vbox flex="1">
	<label value='Zeugnis' />
	<tree id="student-noten-tree" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/zeugnisnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		context="student-noten-tree-popup"
		flags="dont-build-content"
	>
		<treecols>
			<treecol id="student-noten-tree-zeugnis" label="Zeugnis" hidden="false" persist="hidden, width, ordinal"
				class="sortDirectionIndicator" tooltiptext="Zeugnisoption deaktiviert"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#Zeugnis"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false" primary="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-note_bezeichnung" label="Note" flex="5" hidden="false" persist="hidden, width, ordinal"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-uebernahmedatum" label="Uebernahmedatum" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-note" label="Note" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-student_uid" label="Uid" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-studiengang" label="Studiengang" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-studiengang_kz" label="Studiengang_kz" flex="1" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-studiengang_lv" label="StudiengangLV" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_lv" />
            <splitter class="tree-splitter"/>
            <treecol id="student-noten-tree-studiengang_kz_lv" label="Studiengang_kzLV" flex="1" hidden="true" persist="hidden, width, ordinal"
                     class="sortDirectionIndicator"
                     sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz_lv" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-semester_lv" label="SemesterLV" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#semester_lv" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-ects_lv" label="ECTS" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#ects_lv" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-lehrform" label="Lehrform" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_lehrform" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-kurzbz" label="Kurzbz" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-punkte" label="Punkte" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#punkte" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-lehrveranstaltung_bezeichnung_english" label="Englisch" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#NOTE:lehrveranstaltung_bezeichnung_english" />
			<splitter class="tree-splitter"/>
		</treecols>

		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell src="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#zeugnis"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_lv"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz_lv"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#semester_lv"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#ects_lv"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_lehrform"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#punkte"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung_english"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</vbox>
	<vbox>
		<spacer flex="1"/>
		<button id="student-note-copy" label="&lt;=" style="font-weight: bold;" oncommand="StudentNotenMove();"/>
		<spacer id="student-note-copy-antrag-spacer" flex="2"/>
		<button id="student-note-copy-antrag" label="&lt;=" style="font-weight: bold;" oncommand="StudentNotenMoveFromAntrag();"/>
		<spacer flex="1"/>
	</vbox>

	<vbox flex="1">
	<label value='LektorIn' />
	<tree id="student-lvgesamtnoten-tree" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/lvgesamtnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		flags="dont-build-content"
	>

		<treecols>
			<treecol id="student-lvgesamtnoten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"
				onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung" onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-mitarbeiter_uid" label="MitarbeiterInUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum_iso"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-student_uid" label="StudentInUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-punkte" label="Punkte" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#punkte"  onclick="StudentLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
		</treecols>

		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#punkte"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	<label id="student-antragnoten-tree-label" value="Wiederholung" hidden="true"/>
	<tree id="student-antragnoten-tree" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/antragnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		flags="dont-build-content"
	>

		<treecols>
			<treecol id="student-antragnoten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#lehrveranstaltung_bezeichnung"
				onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#note_bezeichnung" onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-mitarbeiter_uid" label="MitarbeiterInUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#mitarbeiter_uid"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#benotungsdatum_iso"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#benotungsdatum_iso"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#freigabedatum_iso"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#studiensemester_kurzbz"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-note" label="NoteID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#note"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-prestudent_id" label="PrestudentInID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#prestudent_id"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#lehrveranstaltung_id"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-antragnoten-tree-studierendenantrag_lehrveranstaltung_id" label="StudierendenantragLehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/antragnote/rdf#studierendenantrag_lehrveranstaltung_id"  onclick="StudentAntragNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
		</treecols>

		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#mitarbeiter_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#benotungsdatum_iso"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#freigabedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#prestudent_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#lehrveranstaltung_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/antragnote/rdf#studierendenantrag_lehrveranstaltung_id"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</vbox>
</hbox>
<hbox>
	<label value="Note" control="student-noten-menulist-note"/>
	<menulist id="student-noten-menulist-note" disabled="true"
				xmlns:NOTE="http://www.technikum-wien.at/note/rdf#"
				datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php" flex="1"
				ref="http://www.technikum-wien.at/note/liste"
				oncommand="StudentNoteSpeichern()">
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
	<label value="Punkte" control="student-noten-textbox-punkte" hidden="<?php echo $punktehidden; ?>"/>
	<textbox id="student-noten-textbox-punkte" oninput="StudentNotenPunkteChange()" disabled="true" hidden="<?php echo $punktehidden; ?>"/>

	<button id="student-noten-button-speichern" oncommand="StudentNoteSpeichern()" label="Speichern" disabled="true" hidden="<?php echo $punktehidden; ?>"/>

	<spacer flex="1" />
</hbox>
</vbox>
</overlay>
