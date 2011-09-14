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
echo '<?xml-stylesheet href="gantt.css" type="text/css"?>';
?>

<overlay id="GanttOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:svg="http://www.w3.org/2000/svg" 
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<!-- <script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/gantt.overlay.js.php" />-->
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Projekttask   * -->
	<!-- ************************ -->
	<vbox id="box-gantt" flex="1" uid="" stg_kz="">
	<popupset>
		<popup id="projektphase-tree-popup">
			<menuitem label="Entfernen" oncommand="TaskDelete();" id="projektphase-tree-popup-entf" disabled="false"/>
		</popup>
	</popupset>
		<toolbox>
			<toolbar id="projektphase-nav-toolbar">
				<toolbarbutton id="toolbarbutton-gantt-zoomin" label="Zoom In" oncommand="PhaseNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
				<toolbarbutton id="toolbarbutton-gantt-zoomout" label="Zoom Out" oncommand="PhaseDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
				<toolbarbutton id="toolbarbutton-gantt-print" label="Drucken" oncommand="PhaseTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			</toolbar>
		</toolbox>

		<stack flex="1">
		<vbox flex="1">
			<svg-shape id="background-circle" flex="1" type="circle" />
		</vbox>
		<vbox flex="1">
			<spacer flex="2"/>
			<svg-shape flex="1" id="svg-button" type="rect" radius="12" label="Projekt Lernquadrat"/>
			<spacer flex="2"/>
			<hbox flex="4">
				<svg-shape flex="1" id="phase1" type="rect" label="Analysephase"/>
				<svg-shape flex="1" id="circ2" type="circle" label="2" />
				<svg-shape flex="1" id="circ3" type="circle" label="3" />
			</hbox>
			<spacer flex="1"/>
		</vbox>
		</stack>


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