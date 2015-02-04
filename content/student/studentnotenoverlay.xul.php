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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

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
		<menuitem label="Zertifikat erstellen" oncommand="StudentFFZertifikatPrint();" id="student-noten-tree-popup-ffzertifikat" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis erstellen" oncommand="StudentLVZeugnisPrint();" id="student-noten-tree-popup-lvzeugnis" hidden="false"/>
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
	<!-- onselect="StudentNotenAuswahl()" - wird jetzt per JS gesetzt -->
	
		<treecols>
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
			<treecol id="student-noten-tree-lehrform" label="Lehrform" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_lehrform" />
			<splitter class="tree-splitter"/>
			<treecol id="student-noten-tree-kurzbz" label="Kurzbz" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_kurzbz" />
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
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
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_lehrform"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_kurzbz"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</vbox>
	<vbox>
		<spacer flex="1"/>
		<button id="student-note-copy" label="&lt;=" style="font-weight: bold;" oncommand="StudentNotenMove();"/>
		<spacer flex="1"/>
	</vbox>
	
	<vbox flex="1">
	<label value='Lektor' />
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
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-mitarbeiter_uid" label="MitarbeiterUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-student_uid" label="StudentUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="student-lvgesamtnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id" />
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
	          datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php" flex="1"
	          ref="http://www.technikum-wien.at/note/liste" 
	          oncommand="StudentNoteSpeichern()">
		<template>
			<menupopup>
				<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
	        		      label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
				  		  uri="rdf:*"/>
				</menupopup>
		</template>
	</menulist>
	
	<button id="student-noten-button-speichern" oncommand="StudentNoteSpeichern()" label="Speichern" disabled="true" hidden="true"/>
	
	<spacer flex="1" />
</hbox>
</vbox>
</overlay>
