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

echo "<?xml-stylesheet href=\"".APP_ROOT."content/bindings.css\" type=\"text/css\" ?>";
?>

<overlay id="LehrveranstaltungNoten"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="lehrveranstaltung-noten" style="margin:0px;" flex="1">
<popupset>
	<popup id="lehrveranstaltung-noten-tree-popup">
		<menuitem label="Entfernen" oncommand="LehrveranstaltungNotenDelete();" id="lehrveranstaltung-noten-tree-popup-delete" hidden="false"/>
		<menuitem label="Freifaecher-Zertifikat erstellen" oncommand="LehrveranstaltungFFZertifikatPrint();" id="lehrveranstaltung-noten-tree-popup-ffzertifikat" hidden="false"/>
	</popup>
</popupset>
<hbox flex="1" style="margin-top: 10px;">
	<vbox flex="1">
	<label value="Zeugnis" />
	<tree id="lehrveranstaltung-noten-tree" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/zeugnisnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		onselect="LehrveranstaltungNotenAuswahl()"
		context="lehrveranstaltung-noten-tree-popup"
		flags="dont-build-content"
	>
	
		<treecols>
			<treecol id="lehrveranstaltung-noten-tree-student_vorname" label="Vorname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_vorname" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-student_nachname" label="Nachname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_nachname" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-uebernahmedatum" label="Uebernahmedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-student_uid" label="Uid" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id" />
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_vorname"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_nachname"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</vbox>
	<vbox>
		<spacer flex="1"/>
		<button id="lehrveranstaltung-note-copy" label="&lt;=" style="font-weight: bold;" oncommand="LehrveranstaltungNotenMove();"/>
		<spacer flex="1"/>
	</vbox>
	
	<vbox flex="1">
	<label value="Lektor" />
	<tree id="lehrveranstaltung-lvgesamtnoten-tree" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/lvgesamtnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		flags="dont-build-content"
	>
	
		<treecols>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student-vorname" label="Vorname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_vorname"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student-nachname" label="Nachname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_nachname"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-mitarbeiter_uid" label="MitarbeiterUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum_iso" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student_uid" label="StudentUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id" />
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_vorname"/>
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_nachname"/>
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
	<label value="Note" control="lehrveranstaltung-noten-menulist-note"/>
	<menulist id="lehrveranstaltung-noten-menulist-note" disabled="true"
	          datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php" flex="1"
	          ref="http://www.technikum-wien.at/note/liste" 
	          oncommand="LehrveranstaltungNoteSpeichern()">
		<template>
			<menupopup>
				<menuitem value="rdf:http://www.technikum-wien.at/note/rdf#note"
	        		      label="rdf:http://www.technikum-wien.at/note/rdf#bezeichnung"
				  		  uri="rdf:*"/>
				</menupopup>
		</template>
	</menulist>
	<button id="lehrveranstaltung-noten-button-speichern" oncommand="LehrveranstaltungNoteSpeichern()" label="Speichern" disabled="true" hidden="true"/>
	<spacer flex="1" />
	<button id="lehrveranstaltung-noten-button-import" label="Notenimport" oncommand="LehrveranstaltungNotenImport();" />
</hbox>
</vbox>
</overlay>