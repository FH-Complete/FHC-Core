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

?>
<overlay id="ProjekttaskDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<!-- ************************ -->
	<!-- *  Projekttaskdetail   * -->
	<!-- ************************ -->
	<vbox id="box-projekttask-detail" flex="1">
		<grid id="grid-projekttask-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Task ID" control="textbox-projekttask-detail-projekttask_id "/>
					<textbox id="textbox-projekttaskdetail-projekttask_id" disabled="true"/>
				</row>
				<row>
					<label value="Projektphase ID" control="textbox-projekttask-detail-projektphase_id"/>
					<textbox id="textbox-projekttaskdetail-projektphase_id"/>
				</row>
				<row>
					<label value="Bezeichnung" control="textbox-projekttask-detail-bezeichnung"/>
   					<textbox id="textbox-projekttask-detail-bezeichnung"/>
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projekttask-detail-beschreibung"/>
   					<textbox id="textbox-projekttask-detail-beschreibung" multiline="true"/>
				</row>
				<row>
					<label value="Aufwand" control="textbox-projekttask-detail-aufwand"/>
   					<textbox id="textbox-projekttask-detail-aufwand"/>
				</row>
				<row>
					<label value="MantisID" control="textbox-projekttask-detail-mantis_id"/>
   					<textbox id="textbox-projekttask-detail-mantis_id"/>
				</row>
			</rows>
		</grid>
		<hbox>
			<spacer flex="1" />
			<button id="button-projekttask-detail-speichern" oncommand="saveProjekttaskDetail()" label="Speichern" />
		</hbox>
	</vbox>
	
	<vbox id="projekttask-mantis" flex="1">
	<description>Mantis Details</description>
	</vbox>
</overlay>