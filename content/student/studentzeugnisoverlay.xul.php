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
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="StudentZeugnis"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="student-zeugnis" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="student-zeugnis-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentAkteDel();" id="student-zeugnis-tree-popup-aktedel" hidden="false"/>
		<?php
		if($rechte->isBerechtigt('admin'))
		{
			echo '<menuitem label="Datei überschreiben" oncommand="StudentAkteUpload();" id="student-zeugnis-tree-popup-akteupload" hidden="false"/>';
		}
		?>
	</menupopup>
</popupset>
<hbox>
	<groupbox id="student-zeugnis-groupbox" flex="1">
	<caption label="Dokumente"/>
	<tree id="student-zeugnis-tree" seltype="single" hidecolumnpicker="false" flex="1"
		datasources="rdf:null" ref="http://www.technikum-wien.at/akte/liste"
		style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="150px" enableColumnDrag="true"
		ondblclick="StudentZeugnisAnzeigen()"
		context="student-zeugnis-tree-popup"
		flags="dont-build-content"
	>

		<treecols>
			<treecol id="student-zeugnis-tree-titel" label="Titel" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#titel"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-bezeichnung" label="Bezeichnung" flex="5" hidden="false"
			   class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#bezeichnung"/>
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-erstelltam" label="Erstelldatum" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#erstelltam" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-gedruckt" label="Gedruckt" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#gedruckt" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-akte_id" label="akte_id" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#akte_id" />
			<splitter class="tree-splitter"/>
		</treecols>

		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#titel"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#erstelltam"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#gedruckt"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#akte_id"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</groupbox>
	<!--
	<vbox id="student-zeugnis-buttons">
		<spacer flex="1"/>
		<button id="student-zeugnis-button-archivieren" label="aktuelles Zeugnis archivieren" disabled="false" oncommand="StudentZeugnisArchivieren()"/>
		<button id="student-zeugnis-button-archivieren-englisch" label="aktuelles Zeugnis archivieren (englisch)" disabled="false" oncommand="StudentZeugnisArchivieren('eng')"/>
		<button id="student-zeugnis-button-archivieren-diplomasupplement" label="Diplomasupplement archivieren" disabled="false" oncommand="StudentDiplomasupplementArchivieren()"/>
		<button id="student-bescheid-button-archivieren" label="Bescheid archivieren" disabled="false" oncommand="StudentBescheidArchivieren()"/>
		<button id="student-bescheid-button-archivieren-englisch" label="Bescheid archivieren (englisch)" disabled="false" oncommand="StudentBescheidArchivieren('eng')"/>
		<spacer flex="1"/>
	</vbox>
	-->
</hbox>
<hbox>
	<groupbox id="student-zeugnis-groupbox-archive">
		<caption label="Dokument archivieren" />
		<grid id="student-zeugnis-grid-archive" style="margin:4px;" flex="1">
				<columns  >
					<column flex="1"/>
					<column flex="5"/>
					<column flex="2"/>
				</columns>
				<rows>
					<row>
						<label value="Dokument" control="student-zeugnis-menulist-dokument"/>
						<menulist id="student-zeugnis-menulist-dokument" disabled="false">
							<menupopup>
									<menuitem id="student-zeugnis-menulist-dokument-zeugnis" value="Zeugnis" label="Zeugnis"/>
									<menuitem id="student-zeugnis-menulist-dokument-zeugniseng" value="ZeugnisEng" label="Zeugnis Englisch"/>
									<menuitem id="student-zeugnis-menulist-dokument-diplsupplement" value="DiplSupplement" label="Diplomasupplement"/>
									<menuitem id="student-zeugnis-menulist-dokument-bescheid" value="Bescheid" label="Bescheid"/>
									<menuitem id="student-zeugnis-menulist-dokument-bescheideng" value="BescheidEng" label="Bescheid Englisch"/>
							</menupopup>
						</menulist>
							<button id="student-zeugnis-button-archive" label="Archivieren" disabled="false" oncommand="StudentZeugnisDokumentArchivieren()"/>
					</row>
				</rows>
		</grid>
	</groupbox>
	<spacer flex="1" />
</hbox>
<vbox>
<hbox>
	<label value="Erweiterte Einstellungen" onclick="document.getElementById('student-zeugnis-archivieren-erweitert').hidden=false;"/>
	<image width="12px" height="12px" src="../skin/images/plus.png" onclick="document.getElementById('student-zeugnis-archivieren-erweitert').hidden=false;"/>
</hbox>
<hbox>
	<grid id="student-zeugnis-archivieren-erweitert" style="margin:4px;" hidden="true">
		<columns  >
			<column flex="1"/>
			<column flex="1"/>
		</columns>
		<rows>
			<row>
				<label value="Dokument elektronisch signieren"/>
				<checkbox id="student-zeugnis-checkbox-sign" checked="true" />
			</row>
			<row>
				<label value="Dokument herunterladbar"/>
				<checkbox id="student-zeugnis-checkbox-selfservice" checked="true" />
			</row>
		</rows>
	</grid>
	<spacer flex="1"/>
</hbox>
</vbox>
<spacer flex="8" />
</vbox>
</overlay>
