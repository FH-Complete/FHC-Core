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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

/*echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/bestellungdetail.overlay.xul.php"?>';*/
?>
<overlay id="overlay-bestellung"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/bestellung.overlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *      Projekt         * -->
	<!-- ************************ -->
	<vbox id="box-bestellung" flex="1" uid="" stg_kz="">
	<popupset>
		<popup id="bestellung-tree-popup">
			<menuitem id="menuitem-popup-bestellung-entf" label="Entfernen" oncommand="ProjektDelete();" disabled="false"/>
		</popup>
	</popupset>
		<toolbox>
			<toolbar id="toolbar-bestellung-main">
				<toolbarbutton id="toolbarbutton-bestellung-neu" label="Neues Projekt" oncommand="ProjektNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
				<toolbarbutton id="toolbarbutton-bestellung-del" label="Loeschen" oncommand="ProjektDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
				<toolbarbutton id="toolbarbutton-bestellung-refresh" label="Aktualisieren" oncommand="ProjektTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			</toolbar>
		</toolbox>

		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="tree-bestellung" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="../../rdf/bestellung.rdf.php?foo=<?php echo time(); ?>" ref="http://www.technikum-wien.at/bestellung"
				style="margin:0px;" enableColumnDrag="true"
				onselect="onselectProjekt(this);"
 				persist="height"
 				flags="dont-build-content"
 				context="bestellung-tree-popup"
		>
			<treecols>
				<treecol id="treecol-bestellung-bestell_nr" label="Bestellnummer" flex="4" hidden="false" primary="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#bestell_nr"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-titel" label="Titel" flex="4" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#titel"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-titel" label="titel" flex="2" hidden="true"  persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#titel"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-oe_kurzbz" label="OE (Organisationseinheit)" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#oe_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-beschreibung" label="Beschreibung" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#beschreibung"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-beginn" label="Start" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#beginn" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-ende" label="Ende" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#ende" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-bestellung-betrag" label="Betrag" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/bestellung/rdf#betrag" />
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow dbID="rdf:http://www.technikum-wien.at/bestellung/rdf#bestell_nr" >
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#bestell_nr"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#titel"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#titel"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#oe_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#beschreibung"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#beginn"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#ende"/>
								<treecell label="rdf:http://www.technikum-wien.at/bestellung/rdf#budget"/>
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
			<tabbox id="tabbox-bestellung" flex="3" orient="vertical">
				<tabs id="tabs-bestellung-main" orient="horizontal">
					<tab id="tab-bestellung-detail" label="Details" />
					<tab id="tab-bestellung-ressource" label="Ressource" />
					<tab id="tab-bestellung-notizen" label="Notizen" />
				</tabs>
				<tabpanels id="tabpanels-bestellung-main" flex="1">
					<vbox id="box-bestellung-detail" />
					<vbox id="box-bestellung-ressource" />
					<box class="Notiz" id="box-bestellung-notizen"/>
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>
