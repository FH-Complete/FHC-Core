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
		<groupbox flex="1">
			<caption id="caption-projekt-detail" label="Neues Projekt"/>
			<grid id="grid-projekt-detail" style="overflow:auto;margin:4px;" flex="1">
			  	<columns  >
					<column flex="1"/>
					<column flex="5"/>
				</columns>
				<rows>
					<row>
						<label value="Projekt (KurzBz)" control="textbox-projekt-detail-projekt_kurzbz "/>
						<hbox>
							<textbox id="textbox-projekt-detail-projekt_kurzbz" size="16" maxlength="16" disabled="true"/>
							<spacer />
						</hbox>
					</row>

					<row>
						<label value="OE (Organisationseinheit)" control="textbox-projekt-detail-oe_kurzbz"/>
						<hbox>
							<menulist id="menulist-projekt-detail-oe_kurzbz"  disabled="true"
									  xmlns:ORGANISATIONSEINHEIT="http://www.technikum-wien.at/organisationseinheit/rdf#"
							          datasources="<?php echo APP_ROOT;?>rdf/organisationseinheit.rdf.php"
							          ref="http://www.technikum-wien.at/organisationseinheit/liste" >
								<template>
									<rule ORGANISATIONSEINHEIT:aktiv='false'>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"
								        		      label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#organisationseinheittyp_kurzbz rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"
											  		  uri="rdf:*" style="text-decoration:line-through;"/>
											</menupopup>
									</rule>
									<rule>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"
								        		      label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#organisationseinheittyp_kurzbz rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"
											  		  uri="rdf:*"/>
											</menupopup>
									</rule>
								</template>
							</menulist>
							<spacer />
						</hbox>
					</row>
					<row>
						<label value="Titel" control="textbox-projekt-detail-titel"/>
	   					<textbox id="textbox-projekt-detail-titel" maxlength="256"  disabled="true"/>
					</row>
					<row>
						<label value="Nummer" control="textbox-projekt-detail-nummer"/>
						<hbox>
	   						<textbox id="textbox-projekt-detail-nummer" maxlength="8"  disabled="true"/>
	   						<spacer />
	   					</hbox>
					</row>
					<row>
						<label value="Beschreibung" control="textbox-projekt-detail-beschreibung"/>
	   					<textbox id="textbox-projekt-detail-beschreibung" multiline="true"  disabled="true" rows="10"/>
					</row>
					<row style="background-color:#eeeeee">
						<label value="Projektwürdigkeit" control="textbox-projekt-detail-projektwuerdigkeit" style="font-weight:bold;"/>
						<hbox>
							<textbox id="textbox-projekt-detail-projektwuerdigkeit" size="16" maxlength="16" readonly="true" style="font-weight:bold;"/>
							<spacer />
						</hbox>
					</row>
					<row style="background-color:#eeeeee">
						<label value="Beginn" control="textbox-projekt-detail-beginn"/>
	   					<box class="Datum" id="textbox-projekt-detail-beginn"  disabled="true" onchange="makeProjektAnalyse()"/>

					</row>
					<row style="background-color:#eeeeee">
						<label value="Ende" control="textbox-projekt-detail-ende"/>
	   					<box class="Datum" id="textbox-projekt-detail-ende"  disabled="true" onchange="makeProjektAnalyse()"/>
					</row>
					<row style="background-color:#eeeeee">
						<label value="Budget" control="textbox-projekt-detail-budget"/>
						<hbox>
	   						<textbox id="textbox-projekt-detail-budget" size="12" maxlength="13"  disabled="true" onchange="makeProjektAnalyse()"/>
	   						<spacer />
	   					</hbox>
					</row>
					<row style="background-color:#eeeeee">
						<label value="Anzahl MA" control="textbox-projekt-anzahl_ma"/>
							<hbox>
	   					<textbox id="textbox-projekt-anzahl_ma"  size="7" maxlength="7" disabled="true" onchange="makeProjektAnalyse()"/>
	   					</hbox>
	   					<spacer />
	   			</row>
					<row style="background-color:#eeeeee">
						<label value="Aufwand PT" control="textbox-projekt-aufwand_pt" />
							<hbox>
	   					<textbox id="textbox-projekt-aufwand_pt"  size="7" maxlength="7" disabled="true" onchange="makeProjektAnalyse()"/>
	   					</hbox>
	   					<spacer />
	   			</row>
                    <row>
						<label value="Farbe" control="textbox-projekt-detail-farbe"/>
						<hbox>
	   						<textbox id="textbox-projekt-detail-farbe" size="7" maxlength="7"  disabled="true"/>
	   						<spacer />
	   					</hbox>
					</row>

					<row>
						<label value="Aufwandstyp" control="textbox-projekt-detail-aufwandstyp"/>
						<hbox>
							<menulist id="menulist-projekt-detail-aufwandstyp"  disabled="true"
									  xmlns:AUFWANDSTYP="http://www.technikum-wien.at/aufwandstyp/rdf#"
							          datasources="<?php echo APP_ROOT;?>rdf/aufwandstyp.rdf.php"
							          ref="http://www.technikum-wien.at/aufwandstyp" >
								<template>
									<menupopup >
										<menuitem value="rdf:http://www.technikum-wien.at/aufwandstyp/rdf#aufwandstyp_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/aufwandstyp/rdf#bezeichnung"
										  		  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
							<spacer />
						</hbox>
					</row>
                    <row>

                            <label value="Zeitaufzeichnung" control="checkbox-projekt-detail-zeitaufzeichnung"/>
                            <hbox>
                                <checkbox id="checkbox-projekt-detail-zeitaufzeichnung"/>
                                <spacer />
                            </hbox>

                    </row>
				</rows>
			</grid>
			<hbox>
				<spacer flex="1" />
				<button id="button-projekt-detail-speichern" oncommand="saveProjektDetail()" label="Speichern"  disabled="true"/>
			</hbox>
		</groupbox>
	</vbox>

</overlay>