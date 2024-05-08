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


if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
	$punktehidden = 'false';
else
	$punktehidden = 'true';

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
	<menupopup id="lehrveranstaltung-noten-tree-popup">
		<menuitem label="Entfernen" oncommand="LehrveranstaltungNotenDelete();" id="lehrveranstaltung-noten-tree-popup-delete" hidden="false"/>
		<menuitem label="Zertifikat erstellen" oncommand="LehrveranstaltungFFZertifikatPrint(event);" id="lehrveranstaltung-noten-tree-popup-ffzertifikat" hidden="false"/>
		<menuitem label="Zertifikat erstellen mit Signatur" oncommand="LehrveranstaltungFFZertifikatPrint(event, true);" id="lehrveranstaltung-noten-tree-popup-ffzertifikat" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Deutsch erstellen" oncommand="LehrveranstaltungLVZeugnisPrint(event, 'German');" id="lehrveranstaltung-noten-tree-popup-lvzeugnis" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Deutsch erstellen mit Signatur" oncommand="LehrveranstaltungLVZeugnisPrint(event, 'German', true);" id="lehrveranstaltung-noten-tree-popup-lvzeugnis" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Englisch erstellen" oncommand="LehrveranstaltungLVZeugnisPrint(event, 'English');" id="lehrveranstaltung-noten-tree-popup-lvzeugnis-englisch" hidden="false"/>
		<menuitem label="Lehrveranstaltungszeugnis Englisch erstellen mit Signatur" oncommand="LehrveranstaltungLVZeugnisPrint(event, 'English', true);" id="lehrveranstaltung-noten-tree-popup-lvzeugnis-englisch" hidden="false"/>
	</menupopup>
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
			<treecol id="lehrveranstaltung-noten-tree-student_vorname" label="Vorname" flex="2" hidden="false" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_vorname" onclick="LehrveranstaltungNotenTreeSort()" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-student_nachname" label="Nachname" flex="2" hidden="false" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_nachname" onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_bezeichnung" onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-note_bezeichnung" label="Note" flex="5" hidden="false" persist="hidden, width, ordinal"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note_bezeichnung" onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-uebernahmedatum" label="Uebernahmedatum" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#uebernahmedatum_iso" onclick="LehrveranstaltungNotenTreeSort()" />
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#benotungsdatum_iso"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiensemester_kurzbz"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-note" label="Note" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#note"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-student_uid" label="Uid" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_uid"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#lehrveranstaltung_id"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-studiengang" label="Studiengang" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang"  onclick="LehrveranstaltungNotenTreeSort()"/>
            <splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-studiengang_kz" label="Studiengang_kz" flex="1" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-verband" label="Verband" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#verband" />						
            <splitter class="tree-splitter"/>
            <treecol id="lehrveranstaltung-noten-tree-studiengang_kz_lv" label="LehrveranstaltungStudiengang_kz" flex="1" hidden="true" persist="hidden, width, ordinal"
                     class="sortDirectionIndicator"
                     sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz_lv"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-student_semester" label="Semester" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_semester"  onclick="LehrveranstaltungNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-noten-tree-punkte" label="Punkte" flex="2" hidden="true" persist="hidden, width, ordinal"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#punkte"  onclick="LehrveranstaltungNotenTreeSort()"/>
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
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#verband"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#studiengang_kz_lv"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#student_semester"/>
						<treecell label="rdf:http://www.technikum-wien.at/zeugnisnote/rdf#punkte"/>
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
	<label value="LektorIn" />
	<tree id="lehrveranstaltung-lvgesamtnoten-tree" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/lvgesamtnote/liste"
		style="margin-bottom:5px;" height="100%" enableColumnDrag="true"
		flags="dont-build-content"
	>

		<treecols>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student-vorname" label="Vorname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_vorname" onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student-nachname" label="Nachname" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_nachname" onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_bezeichnung" label="Lehrveranstaltung" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_bezeichnung" onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-note_bezeichnung" label="Note" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note_bezeichnung" onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-mitarbeiter_uid" label="MitarbeitendeUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#mitarbeiter_uid"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum" label="Benotungsdatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-benotungsdatum-iso" label="BenotungsdatumISO" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#benotungsdatum_iso"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-freigabedatum" label="Freigabedatum" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#freigabedatum_iso"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#studiensemester_kurzbz"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-note" label="Note" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#note"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-student_uid" label="StudierendeUID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#student_uid"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-lehrveranstaltung_id" label="LehrveranstaltungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#lehrveranstaltung_id"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="lehrveranstaltung-lvgesamtnoten-tree-punkte" label="Punkte" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#punkte"  onclick="LehrveranstaltungLVGesamtNotenTreeSort()"/>
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
						<treecell label="rdf:http://www.technikum-wien.at/lvgesamtnote/rdf#punkte"/>
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
				xmlns:NOTE="http://www.technikum-wien.at/note/rdf#"
				datasources="<?php echo APP_ROOT ?>rdf/note.rdf.php" flex="1"
				ref="http://www.technikum-wien.at/note/liste"
				oncommand="LehrveranstaltungNoteSpeichern()">
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
	<label value="Punkte" control="lehrveranstaltung-noten-textbox-punkte" hidden="<?php echo $punktehidden; ?>"/>
	<textbox id="lehrveranstaltung-noten-textbox-punkte" oninput="LehrveranstaltungNotenPunkteChange()" disabled="true" hidden="<?php echo $punktehidden; ?>"/>
	<button id="lehrveranstaltung-noten-button-speichern" oncommand="LehrveranstaltungNoteSpeichern()" label="Speichern" disabled="true" hidden="<?php echo $punktehidden; ?>"/>
	<spacer flex="1" />
	<button id="lehrveranstaltung-noten-button-import" label="Notenimport" oncommand="LehrveranstaltungNotenImport();" />
</hbox>
<?php
if(defined('FAS_GESAMTNOTE_PRUEFUNGSHONORAR') && FAS_GESAMTNOTE_PRUEFUNGSHONORAR)
{
	echo '
<hbox>
	<groupbox id="lehrveranstaltung-noten-groupbox-pruefung">
	<caption label="Prüfungshonorar" />
	<vbox>
		<hbox>
			<label value="MitarbeiterIn" control="lehrveranstaltung-noten-pruefung-menulist-mitarbeiter"/>
			<menulist id="lehrveranstaltung-noten-pruefung-menulist-mitarbeiter"
				      datasources="'.APP_ROOT.'rdf/mitarbeiter.rdf.php" flex="1"
				      ref="http://www.technikum-wien.at/mitarbeiter/_alle"
		              minwidth="250"
				      >
				<template>
					<menupopup>
		                <menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
				    		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname ( rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid )"
						  		  uri="rdf:*"/>
					</menupopup>
				</template>
			</menulist>
			<label value="Prüfungstyp" control="lehrveranstaltung-noten-pruefung-menulist-vertragstyp"/>
			<menulist id="lehrveranstaltung-noten-pruefung-menulist-vertragstyp"
				      datasources="'.APP_ROOT.'rdf/vertragstyp.rdf.php" flex="1"
				      ref="http://www.technikum-wien.at/vertragstyp"
		              minwidth="250"
				      >
				<template>
					<menupopup>
		                <menuitem value="rdf:http://www.technikum-wien.at/vertragstyp/rdf#vertragstyp_kurzbz"
				    		      label="rdf:http://www.technikum-wien.at/vertragstyp/rdf#vertragstyp_bezeichnung"
						  		  uri="rdf:*"/>
					</menupopup>
				</template>
			</menulist>
			<label value="Satz pro Prüfung" control="lehrveranstaltung-noten-pruefung-textbox-satz"/>
			<textbox id="lehrveranstaltung-noten-pruefung-textbox-satz" size="2" oninput="LehrveranstaltungNotenPruefungCalculate()"/>
			<label value="Anzahl Prüfungen" control="lehrveranstaltung-noten-pruefung-textbox-anzahl"/>
			<textbox id="lehrveranstaltung-noten-pruefung-textbox-anzahl" size="2" oninput="LehrveranstaltungNotenPruefungCalculate()"/>
			<label value="0.0" id="lehrveranstaltung-noten-pruefung-label-gesamt"/>
			<spacer flex="1"/>
		</hbox>
		<hbox>
			<label value="Anmerkung" control="lehrveranstaltung-noten-pruefung-textbox-anmerkung"/>
			<textbox id="lehrveranstaltung-noten-pruefung-textbox-anmerkung" size="60"/>
			<label value="Vertragsdatum" control="lehrveranstaltung-noten-pruefung-box-datum"/>
			<box class="Datum" id="lehrveranstaltung-noten-pruefung-box-datum"/>
			<button id="lehrveranstaltung-noten-pruefung-button-save" label="Speichern" oncommand="LehrveranstaltungNotenPruefungSave();" />
		</hbox>
	</vbox>
	</groupbox>
</hbox>';
}
?>
</vbox>
</overlay>
