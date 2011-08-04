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

/*echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projektphasedetail.overlay.xul.php"?>';*/
?>
<overlay id="ProjektphaseOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/projektphase.overlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Projekttask   * -->
	<!-- ************************ -->
	<vbox id="box-projektphase" flex="1" uid="" stg_kz="">
	<popupset>
		<popup id="projektphase-tree-popup">
			<menuitem label="Entfernen" oncommand="TaskDelete();" id="projektphase-tree-popup-entf" disabled="false"/>
		</popup>
	</popupset>
		<toolbox>
			<toolbar id="projektphase-nav-toolbar">
				<toolbarbutton id="projektphase-toolbar-neu" label="Neue Phase" oncommand="PhaseNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
				<toolbarbutton id="projektphase-toolbar-del" label="Loeschen" oncommand="PhaseDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
				<toolbarbutton id="projektphase-toolbar-refresh" label="Aktualisieren" oncommand="PhaseTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			</toolbar>
		</toolbox>

		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="tree-projektphase" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="../../rdf/projektphase.rdf.php?foo=<?php echo time(); ?>" ref="http://www.technikum-wien.at/projektphase/Systementwicklung/Lernquadrat"
				style="margin:0px;" enableColumnDrag="true"
				onselect="TaskAuswahl(this);"
 				persist="height"
 				flags="dont-build-content"
 				context="projektphase-tree-popup"
		>
			<treecols>
				<treecol id="treecol-projektphase-projekt_phase" label="Phase" flex="5" hidden="false" primary="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#bezeichnung"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-projekt_phase_id" label="PhaseID" flex="2" hidden="true"  persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#projektphase_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-projekt_kurzbz" label="Projekt" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#projektphase_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-beschreibung" label="Beschreibung" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#beschreibung"/>
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-start" label="Start" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#start" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-ende" label="Ende" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#ende" />
				<splitter class="tree-splitter"/>
				<treecol id="treecol-projektphase-budget" label="Budger" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projektphase/rdf#budget" />
			</treecols>

			<template>
				<rule>
					<treechildren>
						<treeitem uri="rdf:*">
							<treerow dbID="rdf:http://www.technikum-wien.at/projektphase/rdf#projektphase_id" >
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#bezeichnung"/>
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#projektphase_id"/>
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#beschreibung"/>
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#start"/>
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#ende"/>
								<treecell label="rdf:http://www.technikum-wien.at/projektphase/rdf#budget"/>
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
			<tabbox id="projektphase-tabbox" flex="3" orient="vertical">
				<tabs orient="horizontal" id="projektphase-tabs">
					<tab id="projektphase-tab-detail" label="Details" />
					<tab id="projektphase-tab-mantis" label="Mantis" />
				</tabs>
				<tabpanels id="projektphase-tabpanels-main" flex="1">
					<vbox id="projektphase-detail" />
					<vbox id="projektphase-mantis" />
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>