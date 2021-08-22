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

echo '<?xul-overlay href="'.APP_ROOT.'content/projekt/projekttaskdetail.overlay.xul.php"?>';

?>
<overlay id="ProjekttaskOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/projekttask.overlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>

	<!-- ************************ -->
	<!-- *  Projekttask   * -->
	<!-- ************************ -->
	<vbox id="box-projekttask" flex="1" uid="" stg_kz="">
	<popupset>
		<menupopup id="projekttask-tree-popup">
			<menuitem label="Entfernen" oncommand="TaskDelete();" id="projekttask-tree-popup-entf" disabled="false"/>
		</menupopup>
	</popupset>
		<toolbox>
			<toolbar id="projekttask-nav-toolbar">
				<toolbarbutton id="projekttask-toolbar-neu" label="Neuer Task" oncommand="TaskNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
				<toolbarbutton id="projekttask-toolbar-del" label="Loeschen" oncommand="TaskDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
				<toolbarbutton id="projekttask-toolbar-refresh" label="Aktualisieren" oncommand="TaskTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
				<toolbarbutton anonid="toolbarbutton-notiz-filter" label="Filter " type="menu">
					<menupopup>
                        <menuitem label="Erledigte Tasks" type="radio" name="sort" oncommand="LoadTasks(currentProjektPhaseID,'erledigt')" tooltiptext="Erledigte Tasks anzeigen"/>
						<menuitem label="Offene Tasks" type="radio" name="sort" oncommand="LoadTasks(currentProjektPhaseID,'offen')" tooltiptext="Offene Tasks anzeigen"/>
						<menuitem label="Alle Tasks" type="radio" name="sort" oncommand="LoadTasks(currentProjektPhaseID,'alle')" tooltiptext="Alle Tasks anzeigen"/>
				      </menupopup>
				</toolbarbutton>
			</toolbar>
		</toolbox>

		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="projekttask-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/projekttask"
				style="margin:0px;height:500px" enableColumnDrag="true"
				ondraggesture="nsDragAndDrop.startDrag(event,taskDDObserver);"

				onselect="onselectProjekttask(this);"
				onclick="ProjekttaskUpdateErledigt(event);"
 				persist="height"
 				context="projekttask-tree-popup"
 				flags="dont-build-content"
		>
			<treecols>
				<treecol id="projekttask-treecol-bezeichnung" label="Bezeichnung" flex="5" hidden="false" primary="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#bezeichnung"/>
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-projektphase_id" label="ProjektphaseID" flex="2" hidden="true"  persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#projektphase_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-projekttask_id" label="ProjekttaskID" flex="2" hidden="true" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#projekttask_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-beschreibung" label="Beschreibung" flex="5" hidden="true" persist="hidden, width, ordinal"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#beschreibung"/>
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-aufwand" label="Aufwand" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#aufwand" />
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-mantis_id" label="MantisID" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#mantis_id" />
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-scrumsprint_id" label="ScrumSprintID" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#scrumsprint_id" />
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-ende" label="Ende" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#ende" />
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-ressource_bezeichnung" label="Ressource" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#ressource_bezeichnung" />
				<splitter class="tree-splitter"/>
				<treecol id="projekttask-treecol-erledigt" label="Erledigt" flex="2" hidden="false" persist="hidden, width, ordinal"
					class="sortDirectionIndicator" type="checkbox"
					sort="rdf:http://www.technikum-wien.at/projekttask/rdf#erledigt" />
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<treechildren flex="1" >
   					<treeitem uri="rdf:*">
						<treerow dbID="rdf:http://www.technikum-wien.at/projekttask/rdf#projekttask_id" >
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#bezeichnung"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#projektphase_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#projekttask_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#beschreibung"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#aufwand"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#mantis_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#scrumsprint_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#ende"/>
							<treecell label="rdf:http://www.technikum-wien.at/projekttask/rdf#ressource_bezeichnung"/>
							<treecell label="erledigt" value="rdf:http://www.technikum-wien.at/projekttask/rdf#erledigt"/>
						</treerow>
					</treeitem>
				</treechildren>
			</template>
		</tree>

		<splitter collapse="after" persist="state">
			<grippy />
		</splitter>

		<!-- ************ -->
		<!-- *  Detail  * -->
		<!-- ************ -->
		<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
			<tabbox id="projekttask-tabbox" flex="3" orient="vertical">
				<tabs orient="horizontal" id="projekttask-tabs">
					<tab id="projekttask-tab-detail" label="Details" />

					<tab id="projekttask-tab-notizen" label="Notizen" />
				</tabs>
				<tabpanels id="projekttask-tabpanels-main" flex="1">
					<vbox id="box-projekttask-detail" />

					<box class="Notiz" id="box-projekttask-notizen"/>
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>
