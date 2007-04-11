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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="StudentDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<vbox id="student-detail" style="margin:0px;">
		<hbox style="background:#eeeeee;margin:0px;padding:2px">
			<label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<spacer flex="1" />
			<button id="student-detail-button-save" label="Speichern" oncommand="StudentDetailSave();" disabled="true"/>
		</hbox>
		<hbox flex="1">
			<grid id="student-detail-grid" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="3"/>
  					</columns>
  					<rows>
    					<row>
      						<label value="UID" control="student-detail-textbox-uid"/>
      						<textbox id="student-detail-textbox-uid" disabled="true"/>
    					</row>    					
    					<row>
      						<label value="Anrede" control="student-detail-textbox-anrede"/>
      						<textbox id="student-detail-textbox-anrede" disabled="true"/>
    					</row>
    					<row>
      						<label value="TitelPre" control="student-detail-textbox-titelpre"/>
      						<textbox id="student-detail-textbox-titelpre" disabled="true"/>
    					</row>
    					<row>
      						<label value="TitelPost" control="student-detail-textbox-titelpost"/>
      						<textbox id="student-detail-textbox-titelpost" disabled="true"/>
    					</row>
    					<row>
      						<label value="Vorname" control="student-detail-textbox-vorname"/>
      						<textbox id="student-detail-textbox-vorname" disabled="true"/>
    					</row>
    					<row>
      						<label value="Vornamen" control="student-detail-textbox-vornamen"/>
      						<textbox id="student-detail-textbox-vornamen" disabled="true" />
    					</row>
    					<row>
      						<label value="Nachname" control="student-detail-textbox-nachname"/>
      						<textbox id="student-detail-textbox-nachname" disabled="true"/>
    					</row>
    					<row>
      						<label value="Geburtsdatum" control="student-detail-textbox-geburtsdatum"/>
      						<textbox id="student-detail-textbox-geburtsdatum" disabled="true"/>
    					</row>
    					<row>
      						<label value="Geburtsort" control="student-detail-textbox-geburtsort"/>
      						<textbox id="student-detail-textbox-geburtsort" disabled="true"/>
    					</row>
    					<row>
      						<label value="Geburtszeit" control="student-detail-textbox-geburtszeit"/>
      						<textbox id="student-detail-textbox-geburtszeit" disabled="true"/>
    					</row>
    					<row>
      						<label value="Foto" />
      						<hbox><image src='' id="student-detail-image" width='60' height='60'/><spacer flex="1" /></hbox>
    					</row>
    					<row>
      						<label value="Anmerkung" control="student-detail-textbox-anmerkung"/>
      						<textbox id="student-detail-textbox-anmerkung" disabled="true"/>
    					</row>
    					<row>
      						<label value="Homepage" control="student-detail-textbox-homepage"/>
      						<textbox id="student-detail-textbox-homepage" disabled="true"/>
    					</row>
    					<row>
      						<label value="SVNR" control="student-detail-textbox-svnr"/>
      						<textbox id="student-detail-textbox-svnr" disabled="true"/>
    					</row>
    					<row>
      						<label value="Ersatzkennzeichen" control="student-detail-textbox-ersatzkennzeichen"/>
      						<textbox id="student-detail-textbox-ersatzkennzeichen" disabled="true"/>
    					</row>
    					<row>
      						<label value="Familienstand" control="student-detail-menulist-familienstand"/>
      						<menulist id="student-detail-menulist-familienstand" disabled="true">
								<menupopup>
										<menuitem value="g" label="geschieden"/>
										<menuitem value="l" label="ledig"/>
										<menuitem value="v" label="verheiratet"/>
										<menuitem value="w" label="verwittwet"/>
								</menupopup>								
							</menulist>
    					</row>
    					<row>
      						<label value="Geschlecht" control="student-detail-menulist-geschlecht"/>
      						<menulist id="student-detail-menulist-geschlecht" disabled="true">
								<menupopup>
										<menuitem value="m" label="maennlich"/>
										<menuitem value="w" label="weiblich"/>
								</menupopup>								
							</menulist>
    					</row>
    					<row>
      						<label value="Aktiv" control="student-detail-checkbox-aktiv"/>
      						<checkbox id="student-detail-checkbox-aktiv" checked="true" disabled="true"/>
    					</row>
    					<row>
      						<label value="Anzahl der Kinder" control="student-detail-textbox-anzahlderkinder"/>
      						<textbox id="student-detail-textbox-anzahlderkinder" disabled="true"/>
    					</row>
  						<row>
							<label value="Staatsbuergerschaft" control="student-detail-menulist-staatsbuergerschaft"/>
							<menulist id="student-detail-menulist-staatsbuergerschaft" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/nation/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
							        		      label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Geburtsnation" control="student-detail-menulist-geburtsnation"/>
							<menulist id="student-detail-menulist-geburtsnation" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/nation/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
							        		      label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Sprache" control="student-detail-menulist-sprache" />
							<menulist id="student-detail-menulist-sprache" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/sprache.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/sprachen/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
							        		      label="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
      						<label value="Matrikelnummer" control="student-detail-textbox-matrikelnummer"/>
      						<textbox id="student-detail-textbox-matrikelnummer" disabled="true"/>
    					</row>
				</rows>
			</grid>
		</hbox>
</vbox>
</overlay>