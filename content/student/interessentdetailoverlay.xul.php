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

<overlay id="interessentDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Interessent DETAILS -->
<vbox id="interessent-detail" style="margin:0px;" flex="1">
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="interessent-detail-checkbox-new" checked="true" />      	
			<label value="Person_id"/>
      		<textbox id="interessent-detail-textbox-person_id" disabled="true"/>					
      		<label value="Prestudent_id"/>
      		<textbox id="interessent-detail-textbox-prestudent_id" disabled="true"/>					
		</vbox>
		<vbox flex="1">
		<groupbox id="interessent-detail-groupbox-person">
			<caption label="Person" />
			<grid id="interessent-detail-grid-person" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>
  					</columns>
  					<rows>
    					<row>
      						<label value="Anrede" control="interessent-detail-textbox-anrede"/>
      						<hbox><textbox id="interessent-detail-textbox-anrede" disabled="true" maxlength="16" size="16"/></hbox>
      						<label value="TitelPre" control="interessent-detail-textbox-titelpre"/>
      						<textbox id="interessent-detail-textbox-titelpre" disabled="true" maxlength="64"/>
      						<label value="TitelPost" control="interessent-detail-textbox-titelpost"/>
      						<textbox id="interessent-detail-textbox-titelpost" disabled="true" maxlength="32"/>
    					</row>
    					<row>
    						<label value="Nachname" control="interessent-detail-textbox-nachname"/>
      						<textbox id="interessent-detail-textbox-nachname" disabled="true" maxlength="64"/>
      						<label value="Vorname" control="interessent-detail-textbox-vorname"/>
      						<textbox id="interessent-detail-textbox-vorname" disabled="true" maxlength="32"/>
      						<label value="Vornamen" control="interessent-detail-textbox-vornamen"/>
      						<textbox id="interessent-detail-textbox-vornamen" disabled="true" maxlength="128"/>
    					</row>
    					<row>
      						<label value="Geburtsdatum" control="interessent-detail-textbox-geburtsdatum"/>
      						<hbox>
      							<box class="Datum" id="interessent-detail-textbox-geburtsdatum" disabled="true"/>
      							<!--<textbox id="interessent-detail-textbox-geburtsdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
      						<label value="Geburtsort" control="interessent-detail-textbox-geburtsort"/>
      						<textbox id="interessent-detail-textbox-geburtsort" disabled="true" maxlength="128"/>
      						<label value="Geburtszeit" control="interessent-detail-textbox-geburtszeit"/>
      						<hbox><textbox id="interessent-detail-textbox-geburtszeit" disabled="true" maxlength="5" size="5" tooltiptext="Format: hh:mm Beispiel: 10:30"/></hbox>
    					</row>
    					<row>
      						<label value="SVNR" control="interessent-detail-textbox-svnr"/>
      						<hbox><textbox id="interessent-detail-textbox-svnr" disabled="true" maxlength="10" size="10"/></hbox>
      						<label value="Ersatzkennzeichen" control="interessent-detail-textbox-ersatzkennzeichen"/>
      						<hbox><textbox id="interessent-detail-textbox-ersatzkennzeichen" disabled="true" maxlength="10" size="10"/></hbox>
      						<label value="Aktiv" control="interessent-detail-checkbox-aktiv"/>
      						<checkbox id="interessent-detail-checkbox-aktiv" checked="true" disabled="true"/>
    					</row>
    					<row>
							<label value="Staatsbuergerschaft" control="interessent-detail-menulist-staatsbuergerschaft"/>
							<menulist id="interessent-detail-menulist-staatsbuergerschaft" disabled="true"
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
							<label value="Geburtsnation" control="interessent-detail-menulist-geburtsnation"/>
							<menulist id="interessent-detail-menulist-geburtsnation" disabled="true"
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
							<label value="Sprache" control="interessent-detail-menulist-sprache" />
							<menulist id="interessent-detail-menulist-sprache" disabled="true"
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
      						<label value="Geschlecht" control="interessent-detail-menulist-geschlecht"/>
      						<menulist id="interessent-detail-menulist-geschlecht" disabled="true">
								<menupopup>
										<menuitem value="m" label="maennlich"/>
										<menuitem value="w" label="weiblich"/>
								</menupopup>								
							</menulist>
							<label value="Familienstand" control="interessent-detail-menulist-familienstand"/>
      						<menulist id="interessent-detail-menulist-familienstand" disabled="true">
								<menupopup>
										<menuitem value="g" label="geschieden"/>
										<menuitem value="l" label="ledig"/>
										<menuitem value="v" label="verheiratet"/>
										<menuitem value="w" label="verwittwet"/>
								</menupopup>								
							</menulist>
							<label value="Anzahl der Kinder" control="interessent-detail-textbox-anzahlderkinder"/>
      						<textbox id="interessent-detail-textbox-anzahlderkinder" disabled="true" maxlength="2"/>
    					</row>
    					<row>
      						<vbox>
      							<label value="Foto" />
      							<hbox>
      								<button id="interessent-detail-button-image-upload" label="Upload" oncommand="InteressentImageUpload(event);" disabled="true"/>
      								<spacer flex="1" />
      							</hbox>
      						</vbox>
   							<hbox>
   								<image src='' id="interessent-detail-image" width="60" height="60" style="margin-left:5px;"/>
   								<spacer flex="1"/>
   							</hbox>
      						<label value="Anmerkung" control="interessent-detail-textbox-anmerkung"/>
      						<textbox id="interessent-detail-textbox-anmerkung" disabled="true" multiline="true"/>
      						<label value="Homepage" control="interessent-detail-textbox-homepage"/>
      						<vbox><textbox id="interessent-detail-textbox-homepage" disabled="true" maxlength="256"/></vbox>
    					</row>
				</rows>
			</grid>
			</groupbox>
    		<hbox>
    			<spacer flex="1" />
    			<button id="interessent-detail-button-save" label="Speichern" oncommand="InteressentDetailSave();" disabled="true"/>
    		</hbox>
		</vbox>
</vbox>

<!-- interessent PREStudent -->
<vbox id="interessent-prestudent" style="margin:0px;" flex="1">
		
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="interessent-prestudent-checkbox-new" checked="false" />      	
			<label value="Person_id"/>
      		<textbox id="interessent-prestudent-textbox-person_id" disabled="true"/>
      		<label value="Prestudent_id"/>
      		<textbox id="interessent-prestudent-textbox-prestudent_id" disabled="true"/>
      		<label value="studiengang_kz"/>
      		<textbox id="interessent-prestudent-textbox-studiengang_kz" disabled="true"/>
		</vbox>
		<groupbox id="interessent-detail-groupbox-prestudent">
		<caption label="Prestudent" />
		<grid id="interessent-prestudent-grid-prestudent" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>    					
  					</columns>
  					<rows>
  						<row>
      						<label value="Aufmerksam durch" control="interessent-prestudent-menulist-aufmerksamdurch"/>
      						<menulist id="interessent-prestudent-menulist-aufmerksamdurch" disabled="true"
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
							<label value="Berufstaetigkeit" control="interessent-prestudent-menulist-berufstaetigkeit"/>
      						<menulist id="interessent-prestudent-menulist-berufstaetigkeit" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/berufstaetigkeit.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/berufstaetigkeit/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#code"
							        		      label="rdf:http://www.technikum-wien.at/berufstaetigkeit/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Ausbildung" control="interessent-prestudent-menulist-ausbildung"/>
      						<menulist id="interessent-prestudent-menulist-ausbildung" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/ausbildung.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/ausbildung/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/ausbildung/rdf#code"
							        		      label="rdf:http://www.technikum-wien.at/ausbildung/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
    					</row>
    					<row>
      						<label value="Aufnahmeschluessel" control="interessent-prestudent-menulist-aufnahmeschluessel"/>
      						<menulist id="interessent-prestudent-menulist-aufnahmeschluessel" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/aufnahmeschluessel.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/aufnahmeschluessel/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/aufnahmeschluessel/rdf#aufnahmeschluessel"
							        		      label="rdf:http://www.technikum-wien.at/aufnahmeschluessel/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Studiengang" control="interessent-prestudent-menulist-studiengang_kz"/>
							<menulist id="interessent-prestudent-menulist-studiengang_kz" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/studiengang.rdf.php" flex="1"
						              ref="http://www.technikum-wien.at/studiengang/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_kz"
							        		      label="rdf:http://www.technikum-wien.at/studiengang/rdf#kuerzel - rdf:http://www.technikum-wien.at/studiengang/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<vbox>
								<label value="Anmerkung" control="interessent-prestudent-textbox-anmerkung"/>
								<spacer flex="1" />
      						</vbox>
							<textbox id="interessent-prestudent-textbox-anmerkung" multiline="true" disabled="true"/>
							<label value="Facheinschlaegig berufstaetig" control="interessent-prestudent-checkbox-facheinschlberuf"/>
							<vbox>	
      							<checkbox id="interessent-prestudent-checkbox-facheinschlberuf" checked="true" disabled="true"/>
      							<spacer flex="1"/>
							</vbox>
      						<label value="Bismelden" control="interessent-prestudent-checkbox-bismelden"/>
      						<vbox>	
      							<checkbox id="interessent-prestudent-checkbox-bismelden" checked="true" disabled="true"/>
      							<spacer flex="1"/>
							</vbox>
    					</row>
    				</rows>
    			</grid>
    		</groupbox>
   			<groupbox id="interessent-detail-groupbox-zgv">
			<caption label="Zugangsvoraussetzung" />
				<grid id="interessent-prestudent-grid-zgv" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>    					
  					</columns>
  					<rows>
    					<row>
      						<label value="ZGV" control="interessent-prestudent-menulist-zgvcode"/>
      						<menulist id="interessent-prestudent-menulist-zgvcode" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/zgv.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/zgv/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/zgv/rdf#code"
							        		      label="rdf:http://www.technikum-wien.at/zgv/rdf#kurzbz"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="ZGV Ort" control="interessent-prestudent-textbox-zgvort"/>
      						<textbox id="interessent-prestudent-textbox-zgvort" disabled="true" maxlength="64"/>
      						<label value="ZGV Datum" control="interessent-prestudent-textbox-zgvdatum"/>
      						<hbox>
      							<box class="Datum" id="interessent-prestudent-textbox-zgvdatum" disabled="true"/>
      							<!--<textbox id="interessent-prestudent-textbox-zgvdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
    					</row>
    					<row>
      						<label value="ZGV Master" control="interessent-prestudent-menulist-zgvmastercode"/>
      						<menulist id="interessent-prestudent-menulist-zgvmastercode" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/zgvmaster.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/zgvmaster/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/zgvmaster/rdf#code"
							        		      label="rdf:http://www.technikum-wien.at/zgvmaster/rdf#kurzbz"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="ZGV Master Ort" control="interessent-prestudent-textbox-zgvmasterort"/>
      						<textbox id="interessent-prestudent-textbox-zgvmasterort" disabled="true" maxlength="64"/>
      						<label value="ZGV Master Datum" control="interessent-prestudent-textbox-zgvmasterdatum"/>
      						<hbox>
      							<box class="Datum" id="interessent-prestudent-textbox-zgvmasterdatum" disabled="true"/>
      							<!--<textbox id="interessent-prestudent-textbox-zgvmasterdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
    					</row>
    				</rows>
    			</grid>
    		</groupbox>
    		<groupbox id="interessent-detail-groupbox-reihungstest">
			<caption label="Reihungstest" />
				<grid id="interessent-prestudent-grid-reihungstest" style="overflow:auto;margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>
    					<column flex="1"/>
    					<column flex="5"/>    					
  					</columns>
  					<rows>
    					<row>
    						<label value="Anmeldung zum Reihungstest am" control="interessent-prestudent-textbox-anmeldungreihungstest"/>
      						<hbox>
      							<box class="Datum" id="interessent-prestudent-textbox-anmeldungreihungstest" disabled="true" />
      							<!--<textbox id="interessent-prestudent-textbox-anmeldungreihungstest" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      							<button id="interessent-prestudent-button-anmeldungreihungstest-heute" label="Heute" oncommand="InteressentAnmeldungreihungstestHeute()" disabled="true" style="margin:0px;"/>
      						</hbox>
      						<label value="Reihungstest" control="interessent-prestudent-menulist-reihungstest"/>
      						<menulist id="interessent-prestudent-menulist-reihungstest" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/reihungstest.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/reihungstest/alle" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/reihungstest/rdf#reihungstest_id"
							        		      label="rdf:http://www.technikum-wien.at/reihungstest/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
    					</row>
    					<row>
      						<label value="Zum Reihungstest angetreten" control="interessent-prestudent-checkbox-reihungstestangetreten"/>
      						<checkbox id="interessent-prestudent-checkbox-reihungstestangetreten" checked="true" disabled="true"/>
      						<label value="Reihungstestpunkte" control="interessent-prestudent-textbox-punkte"/>
      						<hbox><textbox id="interessent-prestudent-textbox-punkte" disabled="true" maxlength="9" size="9"/></hbox>
    					</row>
    				</rows>
    			</grid>
    		</groupbox>
    		<groupbox id="interessent-detail-groupbox-rollen">
			<caption label="Rollen" />
					<tree id="interessent-prestudent-tree-rolle" seltype="single" hidecolumnpicker="false" flex="1"
							datasources="rdf:null" ref="http://www.technikum-wien.at/prestudentrolle/liste"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100px" enableColumnDrag="true"
					>
						<treecols>
							<treecol id="interessent-prestudent-tree-rolle-rolle_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#rolle_kurzbz"/>
							<splitter class="tree-splitter"/>
							<treecol id="interessent-prestudent-tree-rolle-studiensemester_kurzbz" label="StSem" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
							<splitter class="tree-splitter"/>
							<treecol id="interessent-prestudent-tree-rolle-ausbildungssemester" label="Semester" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester" />
							<splitter class="tree-splitter"/>
							<treecol id="interessent-prestudent-tree-rolle-datum" label="Datum" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum" />									
							<splitter class="tree-splitter"/>
						</treecols>
			
						<template>
							<treechildren flex="1" >
			   					<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#rolle_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester"/>
										<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
			</groupbox>
			<hbox>
				<spacer flex="1" />
				<button id="interessent-prestudent-button-save" label="Speichern" oncommand="InteressentPrestudentSave();" disabled="true"/>
			</hbox>
</vbox>

</overlay>