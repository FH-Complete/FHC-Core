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
			<treecol id="student-zeugnis-tree-gedruckt" label="Gedruckt" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#gedruckt" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-akte_id" label="akte_id" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#akte_id" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-signiert" label="Signiert" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#signiert" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-stud_selfservice" label="Selfservice" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/akte/rdf#stud_selfservice" />
			<splitter class="tree-splitter"/>
			<treecol id="student-zeugnis-tree-akte_akzeptiertamum" label="AkzeptiertAmUm" flex="2" hidden="false"
					 class="sortDirectionIndicator"
					 sort="rdf:http://www.technikum-wien.at/akte/rdf#stud_akzeptiertamum" />
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
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#signiert"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#stud_selfservice"/>
						<treecell label="rdf:http://www.technikum-wien.at/akte/rdf#stud_akzeptiertamum"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	</groupbox>
</hbox>
<hbox>
	<groupbox id="student-zeugnis-groupbox-archive">
		<caption label="Dokument archivieren" />
		<grid id="student-zeugnis-grid-archive" style="margin:4px;" flex="3">
				<columns  >
					<column flex="1"/>
					<column flex="8"/>
					<column flex="2"/>
				</columns>
				<rows>
					<row>
						<label value="Dokument" control="student-zeugnis-menulist-dokument"/>
						<menulist id="student-zeugnis-menulist-dokument"
							datasources="../rdf/vorlage.rdf.php" flex="1"
							ref="http://www.technikum-wien.at/vorlage"
							style="min-width:300px" >
							<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/vorlage/rdf#vorlage_kurzbz"
									          label="rdf:http://www.technikum-wien.at/vorlage/rdf#bezeichnung"
									          uri="rdf:*"/>
									</menupopup>
							</template>
						</menulist>
						<button id="student-zeugnis-button-archive" label="Archivieren" disabled="false" oncommand="StudentZeugnisDokumentArchivieren()"/>
					</row>
				</rows>
		</grid>
	</groupbox>
	<spacer flex="1" />
</hbox>
<vbox>
</vbox>
<spacer flex="8" />
</vbox>
</overlay>
