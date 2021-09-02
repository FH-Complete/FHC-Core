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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

if(isset($_GET['mitarbeiter_uid']))
	$mitarbeiter_uid=$_GET['mitarbeiter_uid'];
else
	die('MitarbeiterUID muss uebergeben werden');

if(isset($_GET['bisverwendung_id']))
	$bisverwendung_id = $_GET['bisverwendung_id'];
else
	$bisverwendung_id = '';

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';
?>

<!--<!DOCTYPE overlay>-->

<window id="mitarbeiter-verwendung-detail-overlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	onload="MitarbeiterVerwendungInit('<?php echo $mitarbeiter_uid."',".($bisverwendung_id!=''?$bisverwendung_id:"''");?>)"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeiterverwendungdialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<groupbox id="mitarbeiter-detail-groupbox-verwendung" flex="1">
	<caption label="Verwendung" />
	<grid id="mitarbeiter-verwendung-detail-grid" style="margin:4px;" flex="1">
	  	<columns  >
			<column flex="1"/>
			<column flex="5"/>
		</columns>
		<rows>
			<row>
				<label value="Beschaeftigungsart 1" control="mitarbeiter-verwendung-detail-menulist-beschart1"/>
				<menulist id="mitarbeiter-verwendung-detail-menulist-beschart1"
				          datasources="<?php echo APP_ROOT ?>rdf/beschaeftigungsart1.rdf.php" flex="1"
			              ref="http://www.technikum-wien.at/beschaeftigungsart1/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/beschaeftigungsart1/rdf#ba1code"
				        		      label="rdf:http://www.technikum-wien.at/beschaeftigungsart1/rdf#ba1bez"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
			</row>
			<row>
				<label value="Beschaeftigungsart 2" control="mitarbeiter-verwendung-detail-menulist-beschart2"/>
				<menulist id="mitarbeiter-verwendung-detail-menulist-beschart2"
				          datasources="<?php echo APP_ROOT ?>rdf/beschaeftigungsart2.rdf.php" flex="1"
			              ref="http://www.technikum-wien.at/beschaeftigungsart2/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/beschaeftigungsart2/rdf#ba2code"
				        		      label="rdf:http://www.technikum-wien.at/beschaeftigungsart2/rdf#ba2bez"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
			</row>
			<row>
				<label value="Beschaeftigungsausmass" control="mitarbeiter-verwendung-detail-menulist-ausmass"/>
				<menulist id="mitarbeiter-verwendung-detail-menulist-ausmass"
				          datasources="<?php echo APP_ROOT ?>rdf/beschaeftigungsausmass.rdf.php" flex="1"
			              ref="http://www.technikum-wien.at/beschaeftigungsausmass/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/beschaeftigungsausmass/rdf#beschausmasscode"
				        		      label="rdf:http://www.technikum-wien.at/beschaeftigungsausmass/rdf#beschausmassbez"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
			</row>
			<row>
				<label value="Verwendung" control="mitarbeiter-verwendung-detail-menulist-verwendung"/>
				<menulist id="mitarbeiter-verwendung-detail-menulist-verwendung"
				          datasources="<?php echo APP_ROOT ?>rdf/verwendung.rdf.php" flex="1"
			              ref="http://www.technikum-wien.at/verwendung/liste"
			              oncommand="MitarbeiterVerwendungVerwendungChange()">
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendung_code"
				        		      label="rdf:http://www.technikum-wien.at/verwendung/rdf#verwendungbez"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
			</row>
			<row>
				<label value="Hauptberuflich Lehrende(r)" id="mitarbeiter-verwendung-detail-label-hauptberuflich" control="mitarbeiter-verwendung-detail-checkbox-hauptberuflich"/>
      			<checkbox id="mitarbeiter-verwendung-detail-checkbox-hauptberuflich" checked="true" oncommand="MitarbeiterVerwendungDetailToggleHauptberuf()"/>
      		</row>
			<row>
				<label value="Hauptberuf" id="mitarbeiter-verwendung-detail-label-hauptberuf" control="mitarbeiter-verwendung-detail-menulist-hauptberuf"/>
				<menulist id="mitarbeiter-verwendung-detail-menulist-hauptberuf"
				          datasources="<?php echo APP_ROOT ?>rdf/hauptberuf.rdf.php?optional=true" flex="1"
			              ref="http://www.technikum-wien.at/hauptberuf/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/hauptberuf/rdf#hauptberufcode"
				        		      label="rdf:http://www.technikum-wien.at/hauptberuf/rdf#bezeichnung"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
			</row>
      		<row>
				<label value="Habilitation" control="mitarbeiter-verwendung-detail-checkbox-habilitation"/>
      			<checkbox id="mitarbeiter-verwendung-detail-checkbox-habilitation" checked="true"/>
      		</row>
      		<row>
      			<label value="Beginn" control="mitarbeiter-verwendung-detail-datum-beginn"/>
				<box class="Datum" id="mitarbeiter-verwendung-detail-datum-beginn" />
      		</row>
      		<row>
      			<label value="Ende" control="mitarbeiter-verwendung-detail-datum-ende"/>
				<box class="Datum" id="mitarbeiter-verwendung-detail-datum-ende" />
      		</row>
      		<row>
      			<label value="Vertragsstunden" control="mitarbeiter-verwendung-detail-textbox-vertragsstunden" />
      			<textbox id="mitarbeiter-verwendung-detail-textbox-vertragsstunden" />
      		</row>
			<row>
      			<label value="DV-Art" control="mitarbeiter-verwendung-detail-textbox-dv_art" />
      			<textbox id="mitarbeiter-verwendung-detail-textbox-dv_art" />
      		</row>
			<row>
      			<label value="Inkludierte Lehre" control="mitarbeiter-verwendung-detail-textbox-inkludierte_lehre" />
      			<textbox id="mitarbeiter-verwendung-detail-textbox-inkludierte_lehre" />
      		</row>
			<row>
				<label value="Zeitaufzeichnungspflichtig" control="mitarbeiter-verwendung-detail-checkbox-zeitaufzeichnungspflichtig"/>
      			<checkbox id="mitarbeiter-verwendung-detail-checkbox-zeitaufzeichnungspflichtig"/>
      		</row>
            <row>
                <label value="AZG-relevant" control="mitarbeiter-verwendung-detail-checkbox-azgrelevant"/>
                <checkbox id="mitarbeiter-verwendung-detail-checkbox-azgrelevant"/>
            </row>
            <row>
                <label value="Homeoffice" control="mitarbeiter-verwendung-detail-checkbox-homeoffice"/>
                <checkbox id="mitarbeiter-verwendung-detail-checkbox-homeoffice"/>
            </row>
      		<row>
      			<spacer />
      			<hbox>
      				<spacer flex="1"/>
      				<button id="mitarbeiter-verwendung-detail-button-speichern" label="Speichern" oncommand="MitarbeiterVerwendungDetailSpeichern()" />
      			</hbox>
      		</row>
		</rows>
	</grid>
</groupbox>

</window>
