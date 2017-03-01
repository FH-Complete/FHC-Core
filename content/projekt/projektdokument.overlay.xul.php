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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>
<overlay id="overlay-projektdokument"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/projektdokument.overlay.js.php" />

	<!-- ******************************** -->
	<!-- *      Projektdokument         * -->
	<!-- ******************************** -->
	<vbox id="box-dokumente" flex="1" uid="" stg_kz="">
	<popupset>
		<popup id="projektdokument-tree-popup">
			<menuitem id="menuitem-popup-projektdokument-neueVersion" label="Neue Version hochladen" oncommand="ProjektDokumentNeueVersion();" disabled="false"/>
			<menuitem id="menuitem-popup-projektdokument-entf" label="Entfernen" oncommand="ProjektDokumentDelete();" disabled="false"/>
		</popup>
	</popupset>
		<toolbox>
			<toolbar id="toolbar-projektdokument-main">
				<toolbarbutton id="toolbarbutton-projektdokument-neu" label="Neues Dokument hinzufügen" oncommand="ProjektDokumentNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neues Dokument hochladen" />
				<toolbarbutton id="toolbarbutton-projektdokument-zuweisung" label="Dokument zuweisen" oncommand="ProjektDokumentZuweisen();" disabled="true" image="../skin/images/liste.gif" tooltiptext="Ein bereits hochgeladenes Dokument zuweisen" />
				<toolbarbutton id="toolbarbutton-projektdokument-del" label="Loeschen" oncommand="ProjektDokumentDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Dokument löschen"/>
				<toolbarbutton id="toolbarbutton-projektdokument-refresh" label="Aktualisieren" oncommand="ProjektDokumentTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			</toolbar>
		</toolbox>

		<tree id="tree-projektdokument" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/dms/liste"
				style="margin:0px;" enableColumnDrag="true"
				ondblclick="ProjektDokumentDoubleClick(this);"
 				persist="height"
 				flags="dont-build-content"
 				context="projektdokument-tree-popup"
		>
			<treecols>
				<treecol id="treecol-projektdokument-name" label="Name" flex="2" primary="true" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#name"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-projekt_kurzbz" label="Projekt (kurzbz)" flex="4" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#projekt_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-projektphase_id" label="ProjektphaseID" flex="2" hidden="true"  persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#projektphaseID"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-insertamum" label="Angelegt am" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#insertamum"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-updateamum" label="Letzte Aenderung am" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#updateamum" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-insertvon" label="Angelegt von" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#ende" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-updatevon" label="Letzte Aenderung von" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#updatevon" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektdokument-dms_id" label="DMSID" flex="4" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/dms/rdf#dms_id"/>
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow >
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#name"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#projekt_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#projektphase_id"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#insertamum"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#updateamum"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#insertvon"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#updatevon"/>
								<treecell label="rdf:http://www.technikum-wien.at/dms/rdf#dms_id"/>
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>
	</vbox>
</overlay>