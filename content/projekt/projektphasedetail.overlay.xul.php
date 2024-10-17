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
 * 			Karl Burkhart <burkhart@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
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
		<groupbox flex="1">
			<caption id="caption-projektphase-detail" label="Neue Phase"/>
			<grid id="grid-projektphase-detail" style="overflow:auto;margin:4px;" flex="1">
			  	<columns  >
					<column flex="1"/>
					<column flex="5"/>
				</columns>
				<rows>

					<row>
						<label value="Projekt Kurzbz" control="textbox-projektphase-detail-projekt_kurzbz"/>
						<hbox>
							<textbox id="textbox-projektphase-detail-projekt_kurzbz" size="16" maxlength="16" readonly="true"/>
							<spacer />
							<label value="Projektphase ID" control="textbox-projektphase-detail-projektphase_id "/>
							<textbox id="textbox-projektphase-detail-projektphase_id" readonly="true" size="3"/>
							<spacer />
							<label value="Parent Projektphase" control="menulist-projektphase-detail-projektphase_fk"/>
				      		<menulist id="menulist-projektphase-detail-projektphase_fk"
					          datasources="rdf:null"
					          ref="http://www.technikum-wien.at/projektphase"
					          disabled="true"
					         >
								<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/projektphase/rdf#projektphase_id"
							        			      label="rdf:http://www.technikum-wien.at/projektphase/rdf#bezeichnung"
										  			  uri="rdf:*"/>
										</menupopup>

								</template>
							</menulist>
						</hbox>
					</row>
					<row>
						<label value="Bezeichnung" control="textbox-projektphase-detail-bezeichnung"/>
						<hbox>
	   						<textbox id="textbox-projektphase-detail-bezeichnung" maxlength="32" size="32" disabled="true"/>
	   						<spacer />
	   						<label value="Typ" control="textbox-projektphase-detail-typ"/>
							<hbox>
								<menulist id="textbox-projektphase-detail-typ" disabled="true">
										<menupopup>
											<menuitem value="Arbeitspaket" label="Arbeitspaket"/>
											<menuitem value="Milestone" label="Milestone"/>
											<menuitem value="Projektphase" label="Projektphase"/>
											<menuitem value="Service" label="Service"/>
										</menupopup>
								</menulist>

								<spacer />
							</hbox>
	   					</hbox>

					</row>
					<row>
						<label value="Beschreibung" control="menulist-projektphase-detail-ressource"/>
	   					<textbox id="textbox-projektphase-detail-beschreibung" multiline="true" disabled="true" rows="10"/>
					</row>
					<row>
	      				<label value="Verantwortung" control="textbox-projektphase-detail-ressource"/>
	   					<hbox>
				      		<menulist id="menulist-projektphase-detail-ressource"
					          datasources="rdf:null"
					          xmlns:RESSOURCE="http://www.technikum-wien.at/ressource/rdf#"
					          ref="http://www.technikum-wien.at/ressource/alle"
					          disabled="true"
					         >
								<template>

										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/ressource/rdf#ressource_id"
							        			      label="rdf:http://www.technikum-wien.at/ressource/rdf#bezeichnung ( rdf:http://www.technikum-wien.at/ressource/rdf#typ )"
										  			  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<spacer />
							<label value="Budget" control="textbox-projektphase-detail-budget"/>
							<textbox id="textbox-projektphase-detail-budget" size="12" maxlength="13" disabled="true"/>
	   						<spacer />
	   					</hbox>
					</row>
					<row>
						<label value="Start" control="textbox-projektphase-detail-start"/>
	   					<hbox>
							<box class="Datum" id="textbox-projektphase-detail-start" disabled="true"/>
							<label value="Ende" control="textbox-projektphase-detail-ende"/>
							<box class="Datum" id="textbox-projektphase-detail-ende" disabled="true"/>
	   					</hbox>
					</row>
					<row>
						<label value="Personentage" control="textbox-projektphase-detail-personentage"/>
						<hbox>
	   						<textbox id="textbox-projektphase-detail-personentage" size="4" maxlenght="5" disabled="true"/>
	   						<spacer />
	   						<label value="Farbe" control="textbox-projektphase-detail-farbe"/>
	   						<textbox id="textbox-projektphase-detail-farbe" size="7" maxlenght="7" disabled="true"/>
	   						<spacer />
	   					</hbox>
					</row>
                    <row>

                        <label value="Zeitaufzeichnung" control="checkbox-projektphase-detail-zeitaufzeichnung"/>
                        <hbox>
                            <checkbox id="checkbox-projektphase-detail-zeitaufzeichnung"/>
                            <spacer />
                        </hbox>

                    </row>
				</rows>
			</grid>
			<hbox>
				<spacer flex="1" />
				<button id="button-projektphase-detail-speichern" oncommand="saveProjektphaseDetail()" label="Speichern" disabled="true"/>
			</hbox>
		</groupbox>
	</vbox>
</overlay>
