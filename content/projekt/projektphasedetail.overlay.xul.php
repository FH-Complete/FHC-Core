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
<overlay id="ProjektphaseDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<!-- ************************ -->
	<!-- *  projektphasedetail   * -->
	<!-- ************************ -->
	<vbox id="projektphase-detail" flex="1">
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="checkbox-projektphase-detail-neu" checked="true" />
		</vbox>
		<grid id="grid-projektphase-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Projektphase ID" control="textbox-projektphase-detail-projektphase_id "/>
					<textbox id="textbox-projektphase-detail-projektphase_id" readonly="true"/>
				</row>
				<row>
					<label value="Projekt Kurzbz" control="textbox-projektphase-detail-projekt_kurzbz"/>
					<textbox id="textbox-projektphase-detail-projekt_kurzbz"/>
				</row>
				<row>
					<label value="Projektphase FK" control="textbox-projektphase-detail-projektphase_fk "/>
					<textbox id="textbox-projektphase-detail-projektphase_fk"/>
				</row>
				<row>
					<label value="Bezeichnung" control="textbox-projektphase-detail-bezeichnung"/>
					<hbox>
   						<textbox id="textbox-projektphase-detail-bezeichnung" maxlength="32" size="32"/>
   						<spacer />
   					</hbox>
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projektphase-detail-beschreibung"/>
   					<textbox id="textbox-projektphase-detail-beschreibung" multiline="true"/>
				</row>
				<row>
					<label value="Start" control="textbox-projektphase-detail-start"/>
   					<box class="Datum" id="textbox-projektphase-detail-start"/>
				</row>
				<row>
					<label value="Ende" control="textbox-projektphase-detail-ende"/>
   					<box class="Datum" id="textbox-projektphase-detail-ende"/>
				</row>
				<row>
					<label value="Budget" control="textbox-projektphase-detail-budget"/>
					<hbox>
   						<textbox id="textbox-projektphase-detail-budget" size="12" maxlength="13"/>
   						<spacer />
   					</hbox>
				</row>
				<row>
					<label value="Personentage" control="textbox-projektphase-detail-personentage"/>
					<hbox>
   						<textbox id="textbox-projektphase-detail-personentage" size="4" maxlenght="5"/>
   						<spacer />
   					</hbox>
				</row>
			</rows>
		</grid>
		<hbox>
			<spacer flex="1" />
			<button id="button-projektphase-detail-speichern" oncommand="saveProjektphaseDetail()" label="Speichern" />
		</hbox>
	</vbox>
	
	<vbox id="projektphase-mantis" flex="1">
	<description>asdf Details</description>
	</vbox>
</overlay>