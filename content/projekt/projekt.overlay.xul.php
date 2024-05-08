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

echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projektdetail.overlay.xul.php"?>';
?>
<overlay id="overlay-projekt"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/projekt.overlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *      Projekt         * -->
	<!-- ************************ -->
	<vbox id="box-projekt" flex="1" uid="" stg_kz="">
	<popupset>
		<popup id="projekt-tree-popup">
			<menuitem id="menuitem-popup-projekt-entf" label="Entfernen" oncommand="ProjektDelete();" disabled="false"/>
		</popup>
	</popupset>
		<toolbox>
			<toolbar id="toolbar-projekt-main">
				<toolbarbutton id="toolbarbutton-projekt-neu" label="Neues Projekt" oncommand="ProjektNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
				<toolbarbutton id="toolbarbutton-projekt-del" label="Loeschen" oncommand="ProjektDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
				<toolbarbutton id="toolbarbutton-projekt-refresh" label="Aktualisieren" oncommand="ProjektTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
				<toolbarbutton id="toolbarbutton-projekt-statusbericht" label="Statusbericht" oncommand="ProjektPrintStatusbericht()" disabled="false" image="../skin/images/drucken.png" tooltiptext="Statusbericht drucken"/>
                <toolbarbutton id="toolbarbutton-projekt-projektbeschreibung" label="Projektbeschreibung" oncommand="ProjektPrintProjektbeschreibung()" disabled="false" image="../skin/images/drucken.png" tooltiptext="Projektbeschreibung drucken"/>
			</toolbar>
		</toolbox>

		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="tree-projekt" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="../rdf/projekt.rdf.php?nocache=<?php echo time(); ?>" ref="http://www.technikum-wien.at/projekt/alle-projekte"
				style="margin:0px;height:250px" enableColumnDrag="true"
				onselect="onselectProjekt(this);"
 				persist="height"
 				flags="dont-build-content"
 				context="projekt-tree-popup"
		>
			<treecols>
				<treecol id="treecol-projekt-titel" label="Titel" flex="4" hidden="false" primary="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#titel"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-projekt_kurzbz" label="Projekt (kurzbz)" flex="4" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#projekt_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-nummer" label="Nummer" flex="2" hidden="true"  persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#nummer"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-oe_kurzbz" label="OE (Organisationseinheit)" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#oe_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-beschreibung" label="Beschreibung" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#beschreibung"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-beginn" label="Start" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#beginn_iso" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-ende" label="Ende" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#ende_iso" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-budget" label="Budget" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#budget" />
				<treecol id="treecol-projekt-beginniso" label="StartISO" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#beginn_iso" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-endeiso" label="EndeISO" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#ende_iso" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-aufwand_pt" label="Aufwand PT" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#aufwandpt" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projekt-anzahl_ma" label="Anzahl MA" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekt/rdf#anzahl_ma" />
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow dbID="rdf:http://www.technikum-wien.at/projekt/rdf#projekt_kurzbz" >
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#titel"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#projekt_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#nummer"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#oe_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#beschreibung"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#beginn"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#ende"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#budget"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#beginn_iso"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#ende_iso"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#aufwand_pt"/>
								<treecell label="rdf:http://www.technikum-wien.at/projekt/rdf#anzahl_ma"/>
							</treerow>
						</treeitem>
					</treechildren>
				</rule>
			</template>
		</tree>

		<splitter collapse="after" persist="state">
			<grippy />
		</splitter>

		<!-- ************ -->
		<!-- *  Detail  * -->
		<!-- ************ -->
		<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
			<tabbox id="tabbox-projekt" flex="3" orient="vertical">
				<tabs id="tabs-projekt-main" orient="horizontal">
					<tab id="tab-projekt-detail" label="Details" />
					<tab id="tab-projekt-ressource" label="Ressource" />
					<tab id="tab-projekt-notizen" label="Notizen" />
				</tabs>
				<tabpanels id="tabpanels-projekt-main" flex="1">
					<vbox id="box-projekt-detail" />
					<box class="Ressource" id="box-projekt-ressourcen"/>
					<box class="Notiz" id="box-projekt-notizen"/>
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>
