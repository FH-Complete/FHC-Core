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
<!-- Student DETAILS -->
<vbox id="student-detail" style="margin:0px;">
		<hbox style="background:#eeeeee;margin:0px;padding:2px">
			<label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<spacer flex="1" />
			<button id="student-detail-button-save" label="Speichern" oncommand="StudentDetailSave();" disabled="true"/>
		</hbox>
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-detail-checkbox-new" checked="true" />      	
			<label value="Person_id"/>
      		<textbox id="student-detail-textbox-person_id" disabled="true"/>					
		</vbox>
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
      						<textbox id="student-detail-textbox-anrede" disabled="true" maxlength="16" size="16"/>
    					</row>
    					<row>
      						<label value="TitelPre" control="student-detail-textbox-titelpre"/>
      						<textbox id="student-detail-textbox-titelpre" disabled="true" maxlength="64" size="64"/>
    					</row>
    					<row>
      						<label value="TitelPost" control="student-detail-textbox-titelpost"/>
      						<textbox id="student-detail-textbox-titelpost" disabled="true" maxlength="32"/>
    					</row>
    					<row>
      						<label value="Vorname" control="student-detail-textbox-vorname"/>
      						<textbox id="student-detail-textbox-vorname" disabled="true" maxlength="32"/>
    					</row>
    					<row>
      						<label value="Vornamen" control="student-detail-textbox-vornamen"/>
      						<textbox id="student-detail-textbox-vornamen" disabled="true" maxlength="128"/>
    					</row>
    					<row>
      						<label value="Nachname" control="student-detail-textbox-nachname"/>
      						<textbox id="student-detail-textbox-nachname" disabled="true" maxlength="64"/>
    					</row>
    					<row>
      						<label value="Geburtsdatum" control="student-detail-textbox-geburtsdatum"/>
      						<textbox id="student-detail-textbox-geburtsdatum" disabled="true" maxlength="10"/>
    					</row>
    					<row>
      						<label value="Geburtsort" control="student-detail-textbox-geburtsort"/>
      						<textbox id="student-detail-textbox-geburtsort" disabled="true" maxlength="128"/>
    					</row>
    					<row>
      						<label value="Geburtszeit" control="student-detail-textbox-geburtszeit"/>
      						<textbox id="student-detail-textbox-geburtszeit" disabled="true" maxlength="5"/>
    					</row>
    					<row>
      						<label value="Foto" />
      						<hbox>
      							<image src='' id="student-detail-image" width='60' height='60'/>
      							<vbox>
      							<button id="student-detail-button-image-upload" label="Bild upload" oncommand="StudentImageUpload();" disabled="true"/>
      							</vbox>
      							<spacer flex="1" />
      						</hbox>
    					</row>
    					<row>
      						<label value="Anmerkung" control="student-detail-textbox-anmerkung"/>
      						<textbox id="student-detail-textbox-anmerkung" disabled="true"/>
    					</row>
    					<row>
      						<label value="Homepage" control="student-detail-textbox-homepage"/>
      						<textbox id="student-detail-textbox-homepage" disabled="true" maxlength="256"/>
    					</row>
    					<row>
      						<label value="SVNR" control="student-detail-textbox-svnr"/>
      						<textbox id="student-detail-textbox-svnr" disabled="true" maxlength="10"/>
    					</row>
    					<row>
      						<label value="Ersatzkennzeichen" control="student-detail-textbox-ersatzkennzeichen"/>
      						<textbox id="student-detail-textbox-ersatzkennzeichen" disabled="true" maxlength="10"/>
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
      						<textbox id="student-detail-textbox-anzahlderkinder" disabled="true" maxlength="2"/>
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
      						<textbox id="student-detail-textbox-matrikelnummer" disabled="true" maxlength="15"/>
    					</row>
    					<row>
      						<label value="Studiengang" control="student-detail-textbox-studiengang_kz"/>
      						<textbox id="student-detail-textbox-studiengang_kz" disabled="true" maxlength="4"/>
    					</row>
    					<row>
      						<label value="Semester" control="student-detail-textbox-semester"/>
      						<textbox id="student-detail-textbox-semester" disabled="true" maxlength="1"/>
    					</row>
    					<row>
      						<label value="Verband" control="student-detail-textbox-verband"/>
      						<textbox id="student-detail-textbox-verband" disabled="true" maxlength="1"/>
    					</row>
    					<row>
      						<label value="Gruppe" control="student-detail-textbox-gruppe"/>
      						<textbox id="student-detail-textbox-gruppe" disabled="true" maxlength="1"/>
    					</row>
				</rows>
			</grid>
		</hbox>
</vbox>

<!-- STUDENT PREStudent -->
<vbox id="student-prestudent" style="margin:0px;">
		<hbox style="background:#eeeeee;margin:0px;padding:2px">
			<label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<spacer flex="1" />
			<button id="student-prestudent-button-save" label="Speichern" oncommand="StudentPrestudentSave();" disabled="true"/>
		</hbox>
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-prestudent-checkbox-new" checked="true" />      	
			<label value="Person_id"/>
      		<textbox id="student-prestudent-textbox-person_id" disabled="true"/>					
		</vbox>
		<hbox flex="1">
			<grid id="student-prestudent-grid" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="3"/>
  					</columns>
  					<rows>
  						<row>
      						<label value="Aufmerksam durch" control="student-prestudent-menulist-aufmerksamdurch"/>
      						<menulist id="student-prestudent-menulist-aufmerksamdurch" disabled="false"
							          datasources="<?php echo APP_ROOT ?>rdf/aufmerksamdurch.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/aufmerksamdurch/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/aufmerksamdurch/rdf#aufmerksamdurch_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/aufmerksamdurch/rdf#aufmerksamdurch_kurzbz"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
    					</row>
    					<row>
      						<label value="Berufstaetigkeit" control="student-prestudent-menulist-berufstaetigkeit"/>
      						<menulist id="student-prestudent-menulist-berufstaetigkeit" disabled="false"
							          datasources="<?php echo APP_ROOT ?>rdf/berufstaetigkeit.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/berufstaetigkeit/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#bezeichnung"
							        		      label="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
    					</row>
						<row>
      						<label value="Ausbildung" control="student-prestudent-menulist-ausbildung"/>
      						<menulist id="student-prestudent-menulist-berufstaetigkeit" disabled="false"
							          datasources="<?php echo APP_ROOT ?>rdf/berufstaetigkeit.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/berufstaetigkeit/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#bezeichnung"
							        		      label="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
    					</row>
				</rows>
			</grid>
		</hbox>
</vbox>

</overlay>