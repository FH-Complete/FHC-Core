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
<vbox id="student-detail" style="overflow:auto;margin:0px;" flex="1">
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-detail-checkbox-new" checked="true" />      	
			<label value="Person_id"/>
      		<textbox id="student-detail-textbox-person_id" disabled="true"/>					
		</vbox>
		<vbox flex="1">
		<groupbox id="student-detail-groupbox-person">
			<caption label="Person" />
			<grid id="student-detail-grid-person" style="margin:4px;" flex="1">
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
      						<label value="Anrede" control="student-detail-textbox-anrede"/>
      						<hbox><textbox id="student-detail-textbox-anrede" disabled="true" maxlength="16" size="16"/></hbox>
      						<label value="TitelPre" control="student-detail-textbox-titelpre"/>
      						<textbox id="student-detail-textbox-titelpre" disabled="true" maxlength="64"/>
      						<label value="TitelPost" control="student-detail-textbox-titelpost"/>
      						<textbox id="student-detail-textbox-titelpost" disabled="true" maxlength="32"/>
    					</row>
    					<row>
    						<label value="Nachname" control="student-detail-textbox-nachname"/>
      						<textbox id="student-detail-textbox-nachname" disabled="true" maxlength="64"/>
      						<label value="Vorname" control="student-detail-textbox-vorname"/>
      						<textbox id="student-detail-textbox-vorname" disabled="true" maxlength="32"/>
      						<label value="Vornamen" control="student-detail-textbox-vornamen"/>
      						<textbox id="student-detail-textbox-vornamen" disabled="true" maxlength="128"/>
    					</row>
    					<row>
      						<label value="Geburtsdatum" control="student-detail-textbox-geburtsdatum"/>
      						<hbox>
      							<box class="Datum" id="student-detail-textbox-geburtsdatum" disabled="true"/>
      							<!--<textbox id="student-detail-textbox-geburtsdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
      						<label value="Geburtsort" control="student-detail-textbox-geburtsort"/>
      						<textbox id="student-detail-textbox-geburtsort" disabled="true" maxlength="128"/>
      						<label value="Geburtsnation" control="student-detail-menulist-geburtsnation"/>
							<menulist id="student-detail-menulist-geburtsnation" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
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
      						<label value="SVNR" control="student-detail-textbox-svnr"/>
      						<hbox><textbox id="student-detail-textbox-svnr" disabled="true" maxlength="10" size="10"/></hbox>
      						<label value="Ersatzkennzeichen" control="student-detail-textbox-ersatzkennzeichen"/>
      						<hbox><textbox id="student-detail-textbox-ersatzkennzeichen" disabled="true" maxlength="10" size="10"/></hbox>
      						<label value="Geburtszeit" control="student-detail-textbox-geburtszeit"/>
      						<hbox><textbox id="student-detail-textbox-geburtszeit" disabled="true" maxlength="5" size="5" tooltiptext="Format: hh:mm Beispiel: 10:30"/></hbox>
    					</row>
    					<row>
							<label value="Staatsbuergerschaft" control="student-detail-menulist-staatsbuergerschaft"/>
							<menulist id="student-detail-menulist-staatsbuergerschaft" disabled="true"
							          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
						              ref="http://www.technikum-wien.at/nation/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
							        		      label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<spacer />
							<spacer />						
							
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
      						<label value="Geschlecht" control="student-detail-menulist-geschlecht"/>
      						<menulist id="student-detail-menulist-geschlecht" disabled="true">
								<menupopup>
										<menuitem value="m" label="maennlich"/>
										<menuitem value="w" label="weiblich"/>
								</menupopup>								
							</menulist>
							<label value="Familienstand" control="student-detail-menulist-familienstand"/>
      						<menulist id="student-detail-menulist-familienstand" disabled="true">
								<menupopup>
										<menuitem value="" label="--keine Auswahl--"/>
										<menuitem value="g" label="geschieden"/>
										<menuitem value="l" label="ledig"/>
										<menuitem value="v" label="verheiratet"/>
										<menuitem value="w" label="verwittwet"/>
								</menupopup>								
							</menulist>
							<label value="Anzahl der Kinder" control="student-detail-textbox-anzahlderkinder"/>
      						<textbox id="student-detail-textbox-anzahlderkinder" disabled="true" maxlength="2"/>
    					</row>
    					<row>
      						<vbox>
      							<label value="Foto" />
      							<hbox>
      								<button id="student-detail-button-image-upload" label="Upload" oncommand="StudentImageUpload();" disabled="true"/>
      								<spacer flex="1" />
      							</hbox>
      						</vbox>
   							<hbox>
   								<image src='' id="student-detail-image" style="margin-left:5px;"/> <!--height="60" width="60"-->
   								<spacer flex="1"/>
   							</hbox>
      						<label value="Anmerkung" control="student-detail-textbox-anmerkung"/>
      						<textbox id="student-detail-textbox-anmerkung" disabled="true" multiline="true"/>
      						<label value="Homepage" control="student-detail-textbox-homepage"/>
      						<vbox><textbox id="student-detail-textbox-homepage" disabled="true" maxlength="256"/></vbox>
    					</row>
				</rows>
			</grid>
			</groupbox>
			<hbox>
				<groupbox id="student-detail-groupbox-student" flex="1">
				<caption label="Student" />
				<grid id="student-detail-grid-student" style="margin:4px;" flex="1">
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
	    						<label value="UID" control="student-detail-textbox-uid"/>
	      						<hbox><textbox id="student-detail-textbox-uid" readonly="true" maxlength="16" size="16"/></hbox>
	    						<label value="Personenkennzeichen" control="student-detail-textbox-matrikelnummer"/>
	      						<hbox><textbox id="student-detail-textbox-matrikelnummer" readonly="true" maxlength="15" size="15"/></hbox>
	      						<!--<label value="Studiengang" control="student-detail-textbox-studiengang_kz"/>-->
	      						<textbox id="student-detail-menulist-studiengang_kz" disabled="true" hidden="true" />
	      						<!--
	      						<menulist id="student-detail-menulist-studiengang_kz" disabled="true"
								          datasources="<?php echo APP_ROOT ?>rdf/studiengang.rdf.php" flex="1"
							              ref="http://www.technikum-wien.at/studiengang/liste" >
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_kz"
								        		      label="rdf:http://www.technikum-wien.at/studiengang/rdf#kuerzel - rdf:http://www.technikum-wien.at/studiengang/rdf#bezeichnung"
											  		  uri="rdf:*"/>
											</menupopup>
									</template>
								</menulist>-->
	      						<label value="Aktiv" control="student-detail-checkbox-aktiv"/>
      							<checkbox id="student-detail-checkbox-aktiv" checked="true" disabled="true"/>
	    					</row>
	    					<row>
		    					<label value="Semester" control="student-detail-textbox-semester"/>
	      						<hbox><textbox id="student-detail-textbox-semester" disabled="true" maxlength="2" size="1"/></hbox>
	      						<label value="Verband" control="student-detail-textbox-verband"/>
	      						<hbox><textbox id="student-detail-textbox-verband" disabled="true" maxlength="1" size="1"/></hbox>
	      						<label value="Gruppe" control="student-detail-textbox-gruppe"/>
	      						<hbox><textbox id="student-detail-textbox-gruppe" disabled="true" maxlength="1" size="1"/></hbox>
	    					</row>
	    				</rows>
	    		</grid>
	    		</groupbox>
    			<vbox>
    				<spacer flex="1" />
    				<button id="student-detail-button-save" label="Speichern" oncommand="StudentDetailSave();" disabled="true"/>
    			</vbox>
    		</hbox>
		</vbox>
</vbox>

<!-- STUDENT PREStudent -->
<vbox id="student-prestudent" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<popup id="student-prestudent-rolle-tree-popup">
		<menuitem label="Bearbeiten" oncommand="StudentRolleBearbeiten();" id="student-prestudent-rolle-tree-popup-edit" hidden="false"/>
		<menuitem label="Entfernen" oncommand="StudentPrestudentRolleDelete();" id="student-prestudent-rolle-tree-popup-delete" hidden="false"/>		
	</popup>
</popupset>
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-prestudent-checkbox-new" checked="false" />      	
			<label value="Person_id"/>
      		<textbox id="student-prestudent-textbox-person_id" disabled="true"/>
      		<label value="Prestudent_id"/>
      		<textbox id="student-prestudent-textbox-prestudent_id" disabled="true"/>
      		<label value="studiengang_kz"/>
      		<textbox id="student-prestudent-textbox-studiengang_kz" disabled="true"/>
		</vbox>
		
   			<groupbox id="student-detail-groupbox-zgv">
			<caption id="student-detail-groupbox-caption" label="Zugangsvoraussetzung" />
				<grid id="student-prestudent-grid-zgv" style="margin:4px;" flex="1">
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
      						<label value="ZGV" control="student-prestudent-menulist-zgvcode"/>
      						<menulist id="student-prestudent-menulist-zgvcode" disabled="true"
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
							<label value="ZGV Ort" control="student-prestudent-textbox-zgvort"/>
      						<textbox id="student-prestudent-textbox-zgvort" disabled="true" maxlength="64"/>
      						<label value="ZGV Datum" control="student-prestudent-textbox-zgvdatum"/>
      						<hbox>
      							<box class='Datum' id="student-prestudent-textbox-zgvdatum" disabled="true"/>
      							<!--<textbox id="student-prestudent-textbox-zgvdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
    					</row>
    					<row>
      						<label value="ZGV Master" control="student-prestudent-menulist-zgvmastercode"/>
      						<menulist id="student-prestudent-menulist-zgvmastercode" disabled="true"
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
							<label value="ZGV Master Ort" control="student-prestudent-textbox-zgvmasterort"/>
      						<textbox id="student-prestudent-textbox-zgvmasterort" disabled="true" maxlength="64"/>
      						<label value="ZGV Master Datum" control="student-prestudent-textbox-zgvmasterdatum"/>
      						<hbox>
      							<box class='Datum' id="student-prestudent-textbox-zgvmasterdatum" disabled="true"/>
      							<!--<textbox id="student-prestudent-textbox-zgvmasterdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      						</hbox>
    					</row>
    				</rows>
    			</grid>
    		</groupbox>
    		<groupbox id="student-detail-groupbox-reihungstest">
			<caption label="Reihungstest" />
				<grid id="student-prestudent-grid-reihungstest" style="margin:4px;" flex="1">
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
    						<label value="Anmeldung zum Reihungstest am" control="student-prestudent-textbox-anmeldungreihungstest"/>
      						<hbox>
      							<box class="Datum" id="student-prestudent-textbox-anmeldungreihungstest" disabled="true"/>
      							<!--<textbox id="student-prestudent-textbox-zgvmasterdatum" disabled="true" maxlength="10" size="10" tooltiptext="Format: JJJJ-MM-DD Beispiel: 1970-01-31"/>-->
      							<button id="student-prestudent-button-anmeldungreihungstest-heute" label="Heute" oncommand="StudentAnmeldungreihungstestHeute()" disabled="true" style="margin:0px;"/>
      						</hbox>
      						<label value="Reihungstest" control="student-prestudent-menulist-reihungstest"/>
      						<hbox>
	      						<menulist id="student-prestudent-menulist-reihungstest" disabled="true"
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
							
								<toolbarbutton id="student-prestudent-button-reihungstest-refresh" image="../skin/images/refresh.png" tooltiptext="Liste neu laden" onclick="StudentReihungstestDropDownRefresh()"/>
								
							</hbox>
    					</row>
    					<row>
      						<label value="Zum Reihungstest angetreten" control="student-prestudent-checkbox-reihungstestangetreten"/>
      						<checkbox id="student-prestudent-checkbox-reihungstestangetreten" checked="true" disabled="true"/>
      						<label value="Reihungstestpunkte" control="student-prestudent-textbox-punkte"/>
      						<hbox><textbox id="student-prestudent-textbox-punkte" disabled="true" maxlength="9" size="9"/></hbox>
    					</row>
    				</rows>
    			</grid>
    		</groupbox>
    		<groupbox id="student-detail-groupbox-prestudent">
		<caption label="Prestudent" />
		<grid id="student-prestudent-grid-prestudent" style="margin:4px;" flex="1">
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
      						<label value="Aufmerksam durch" control="student-prestudent-menulist-aufmerksamdurch"/>
      						<menulist id="student-prestudent-menulist-aufmerksamdurch" disabled="true"
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
							<label value="Berufstaetigkeit" control="student-prestudent-menulist-berufstaetigkeit"/>
      						<menulist id="student-prestudent-menulist-berufstaetigkeit" disabled="true"
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
							<label value="Ausbildung" control="student-prestudent-menulist-ausbildung"/>
      						<menulist id="student-prestudent-menulist-ausbildung" disabled="true"
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
      						<label value="Aufnahmeschluessel" control="student-prestudent-menulist-aufnahmeschluessel"/>
      						<menulist id="student-prestudent-menulist-aufnahmeschluessel" disabled="true"
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
							<label value="Studiengang" control="student-prestudent-menulist-studiengang_kz"/>
							<menulist id="student-prestudent-menulist-studiengang_kz" disabled="true"
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
							
							<label value="Facheinschlaegig berufstaetig" control="student-prestudent-checkbox-facheinschlberuf"/>
							<checkbox id="student-prestudent-checkbox-facheinschlberuf" checked="true" disabled="true"/>
							
    					</row>
    				</rows>
    			</grid>
    			<grid style="margin:4px;" flex="1">
				  	<columns  >
    					<column flex="1"/>
    					<column flex="11"/>
    					<column flex="1"/>
    					<column flex="5"/>    					
  					</columns>
  					<rows>
  						<row>
		    				<label value="Anmerkung" control="student-prestudent-textbox-anmerkung"/>
		      				<textbox id="student-prestudent-textbox-anmerkung" disabled="true"/>
		      				<label value="Bismelden" control="student-prestudent-checkbox-bismelden"/>
		      				<checkbox id="student-prestudent-checkbox-bismelden" checked="true" disabled="true"/>
						</row>
					</rows>
				</grid>      			
    		</groupbox>
    		<groupbox id="student-detail-groupbox-rollen">
			<caption label="Status" />
					<tree id="student-prestudent-tree-rolle" seltype="single" hidecolumnpicker="false" flex="1"
							datasources="rdf:null" ref="http://www.technikum-wien.at/prestudentrolle/liste"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100px" enableColumnDrag="true"
							flags="dont-build-content"
							context="student-prestudent-rolle-tree-popup"
							ondblclick="StudentRolleBearbeiten()"
					>
						<treecols>
							<treecol id="student-prestudent-tree-rolle-rolle_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#rolle_kurzbz"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-prestudent-tree-rolle-studiensemester_kurzbz" label="StSem" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-prestudent-tree-rolle-ausbildungssemester" label="Semester" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester" />
							<splitter class="tree-splitter"/>
							<treecol id="student-prestudent-tree-rolle-datum" label="Datum" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum" />									
							<splitter class="tree-splitter"/>
							<treecol id="student-prestudent-tree-rolle-orgform_kurzbz" label="Organisationsform" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#orgform_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="student-prestudent-tree-rolle-prestudent_id" label="PrestudentID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#prestudent_id" />
							<splitter class="tree-splitter"/>
						</treecols>
			
						<template>
							<rule>
								<treechildren flex="1" >
				   					<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#rolle_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester"/>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum"/>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#orgform_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#prestudent_id"/>
										</treerow>
									</treeitem>
								</treechildren>
							</rule>
						</template>
					</tree>
				<!--<hbox>
				<menulist id="student-prestudent-rolle-menulist-rolle_kurzbz" disabled="false"
				          datasources="<?php echo APP_ROOT ?>rdf/rolle.rdf.php"
			              ref="http://www.technikum-wien.at/rolle/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/rolle/rdf#rolle_kurzbz"
				        		      label="rdf:http://www.technikum-wien.at/rolle/rdf#rolle_kurzbz"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
				<menulist id="student-prestudent-rolle-menulist-studiensemester" disabled="false"
				          datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php"
			              ref="http://www.technikum-wien.at/studiensemester/liste" >
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
				        		      label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
				<menulist id="student-prestudent-rolle-menulist-semester" disabled="false">
					<menupopup>
						<menuitem value="1" label="1"/>
						<menuitem value="2" label="2"/>
						<menuitem value="3" label="3"/>
						<menuitem value="4" label="4"/>
						<menuitem value="5" label="5"/>
						<menuitem value="6" label="6"/>
						<menuitem value="7" label="7"/>
						<menuitem value="8" label="8"/>
					</menupopup>
				</menulist>
				<button id="student-prestudent-rolle-button-save" label="Rolle hinzufuegen" disabled="false" oncommand="StudentRolleAdd()" />
				</hbox>-->
			</groupbox>
			<hbox>
				<spacer flex="1" />
				<button id="student-prestudent-button-save" label="Speichern" oncommand="StudentPrestudentSave();" disabled="true"/>
			</hbox>
</vbox>

</overlay>