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
<overlay id="overlay-projekt-detail"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<!-- ************************ -->
	<!-- *  Projektdetail   * -->
	<!-- ************************ -->
	<vbox id="box-projekt-detail" flex="1">
		<!-- <description>Projekt Details</description> -->
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="checkbox-projekt-detail-neu" checked="true" />
		</vbox>
		<grid id="grid-projekt-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Projekt (KurzBz)" control="textbox-projekt-detail-projekt_kurzbz "/>
					<textbox id="textbox-projekt-detail-projekt_kurzbz"/>
				</row>
				<row>
					<label value="OE (Organisationseinheit)" control="textbox-projekt-detail-oe_kurzbz"/>
					<textbox id="textbox-projekt-detail-oe_kurzbz"/>
				</row>
				<row>
					<label value="Titel" control="textbox-projekt-detail-titel"/>
   					<textbox id="textbox-projekt-detail-titel"/>
				</row>
				<row>
					<label value="Nummer" control="textbox-projekt-detail-nummer"/>
   					<textbox id="textbox-projekt-detail-nummer"/>
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projekt-detail-beschreibung"/>
   					<textbox id="textbox-projekt-detail-beschreibung" multiline="true"/>
				</row>
				<row>
					<label value="Beginn" control="textbox-projekt-detail-beginn"/>
   					<textbox id="textbox-projekt-detail-beginn"/>
				</row>
				<row>
					<label value="Ende" control="textbox-projekt-detail-ende"/>
   					<textbox id="textbox-projekt-detail-ende"/>
				</row>
				<row>
					<label value="Budget" control="textbox-projekt-detail-budget"/>
   					<textbox id="textbox-projekt-detail-budget"/>
				</row>
			</rows>
		</grid>
		<hbox>
			<spacer flex="1" />
			<button id="button-projekt-detail-speichern" oncommand="saveProjektDetail()" label="Speichern" />
		</hbox>
	</vbox>

</overlay>