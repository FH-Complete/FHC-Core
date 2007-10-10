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

<overlay id="StudentProjektarbeitOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
	
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentabschlusspruefung.js.php" />

<!-- Abschlusspruefung DETAILS -->
<vbox id="student-abschlusspruefung" style="overflow:auto;margin:10px;" flex="1">
<popupset>
	<popup id="student-abschlusspruefung-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentAbschlusspruefungLoeschen();" id="student-abschlusspruefung-tree-popup-delete" hidden="false"/>
		<menu id="student-abschlusspruefung-tree-popup-dokumente" label="Dokumente">
	      <menupopup id="menu-file-popup">
	        <menuitem label="Pruefungsprotokoll" oncommand="StudentAbschlusspruefungPrintPruefungsprotokoll()"/>
	        <menuitem label="Pruefungszeugnis" oncommand="StudentAbschlusspruefungPrintPruefungszeugnis()"/>
	        <menuitem label="Urkunde" oncommand="StudentAbschlusspruefungPrintUrkunde('deutsch')"/>
	        <menuitem label="Urkunde Englisch" oncommand="StudentAbschlusspruefungPrintUrkunde('englisch')"/>
	      </menupopup>
	    </menu>
	</popup>
</popupset>
	<hbox>
		<tree id="student-abschlusspruefung-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/abschlusspruefung/liste"
				onselect="StudentAbschlusspruefungAuswahl();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:0px;"
				persist="hidden, height"
				context="student-abschlusspruefung-tree-popup"
		>
			<treecols>
				<treecol id="student-abschlusspruefung-treecol-student_uid" label="UID" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#student_uid" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-vorsitz" label="Vorsitz" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#vorsitz_nachname" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-pruefer1" label="Pruefer1" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer1_nachname" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-pruefer2" label="Pruefer2" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer2_nachname" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-pruefer3" label="Pruefer3" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer3_nachname" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-abschlussbeurteilung_kurzbz" label="Abschlussbeurteilung" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#abschlussbeurteilung_kurzbz" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-datum" label="Datum" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#datum_iso" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-sponsion" label="Sponsion" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#sponsion_iso" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-anmerkung" label="Anmerkung" flex="1" persist="hidden, width" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#anmerkung" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-abschlusspruefung_id" label="Abschlusspruefung_id" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#abschlusspruefung_id" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
				<treecol id="student-abschlusspruefung-treecol-pruefungstyp_kurzbz" label="Typ" flex="1" persist="hidden, width" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefungstyp_kurzbz" onclick="StudentAbschlusspruefungTreeSort()"/>
				<splitter class="tree-splitter"/>
			</treecols>
	
			<template>
				<rule>
						<treechildren>
							<treeitem uri="rdf:*">
	 						<treerow>
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#student_uid" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#vorsitz_nachname" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer1_nachname" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer2_nachname" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefer3_nachname" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#abschlussbeurteilung_kurzbz" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#datum" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#sponsion" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#anmerkung" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#abschlusspruefung_id" />
	   							<treecell label="rdf:http://www.technikum-wien.at/abschlusspruefung/rdf#pruefungstyp_kurzbz" />
	 						</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
		</tree>
		<vbox>
			<button id="student-abschlusspruefung-button-neu" label="Neu" oncommand="StudentAbschlusspruefungNeu()" disabled="true" />
			<button id="student-abschlusspruefung-button-loeschen" label="Loeschen" oncommand="StudentAbschlusspruefungLoeschen()" disabled="true" />
		</vbox>
	</hbox>
	<textbox id="student-abschlusspruefung-textbox-abschlusspruefung_id" hidden="true" />
	<checkbox id="student-abschlusspruefung-checkbox-neu" hidden="true" />
<groupbox>
	<caption label="Details" />
	<grid align="end" flex="1"
		  flags="dont-build-content"
		  enableColumnDrag="true"
	>
		<columns  >
    		<column flex="1"/>
    		<column flex="5"/>
    		<column flex="1"/>
    		<column flex="5"/>
    	</columns>
  		<rows>
  			<row>
  				<label value="Typ" control="student-abschlusspruefung-menulist-typ" />
    			<menulist id="student-abschlusspruefung-menulist-typ"
						  disabled="true"
				          datasources="<?php echo APP_ROOT; ?>rdf/pruefungstyp.rdf.php?abschluss=true" flex="1"
				          ref="http://www.technikum-wien.at/pruefungstyp/liste" 
				          oncommand="StudentAbschlusspruefungTypChange()"
				>
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/pruefungstyp/rdf#pruefungstyp_kurzbz"
				        		      label="rdf:http://www.technikum-wien.at/pruefungstyp/rdf#beschreibung"
							  		  uri="rdf:*"/>
						</menupopup>
					</template>
				</menulist>
  			</row>
    		<row>
    			<label value="Vorsitz" control="student-abschlusspruefung-menulist-vorsitz" />
    			<menulist id="student-abschlusspruefung-menulist-vorsitz"
						  editable="true" disabled="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/mitarbeiter/liste" 
				          oninput="StudentAbschlusspruefungMenulistMitarbeiterLoad(this)">
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
				        		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
				        		      myvalue="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
							  		  uri="rdf:*"/>
						</menupopup>
					</template>
				</menulist>
				<label value="Pruefer1" id="student-abschlusspruefung-label-pruefer1" control="student-abschlusspruefung-menulist-pruefer1" />
    			<menulist id="student-abschlusspruefung-menulist-pruefer1"
						  editable="true" disabled="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/person/liste" 
				          oninput="StudentAbschlusspruefungMenulistPersonLoad(this)">
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/person/rdf#person_id"
				        		      label="rdf:http://www.technikum-wien.at/person/rdf#anzeigename"
							  		  uri="rdf:*"/>
						</menupopup>
					</template>
				</menulist>
    		</row>
    		<row>
    			<label value="Abschlussbeurteilung" control="student-abschlusspruefung-menulist-abschlussbeurteilung" />
    			<menulist id="student-abschlusspruefung-menulist-abschlussbeurteilung"
						  disabled="true"
				          datasources="<?php echo APP_ROOT;?>rdf/abschlussbeurteilung.rdf.php?optional=true" flex="1"
				          ref="http://www.technikum-wien.at/abschlussbeurteilung/liste" 
				>
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/abschlussbeurteilung/rdf#abschlussbeurteilung_kurzbz"
				        		      label="rdf:http://www.technikum-wien.at/abschlussbeurteilung/rdf#bezeichnung"
							  		  uri="rdf:*"/>
						</menupopup>
					</template>
				</menulist>
				<label value="Pruefer2" id="student-abschlusspruefung-label-pruefer2" control="student-abschlusspruefung-menulist-pruefer2" />
    			<menulist id="student-abschlusspruefung-menulist-pruefer2"
						  editable="true" disabled="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/person/liste" 
				          oninput="StudentAbschlusspruefungMenulistPersonLoad(this)">
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/person/rdf#person_id"
				        		      label="rdf:http://www.technikum-wien.at/person/rdf#anzeigename"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
    		</row>
    		<row>
    			<label value="Akademischer Grad" control="student-abschlusspruefung-menulist-akadgrad" />
    			<menulist id="student-abschlusspruefung-menulist-akadgrad"
						  disabled="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/akadgrad/liste" 
				>
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/akadgrad/rdf#akadgrad_id"
				        		      label="rdf:http://www.technikum-wien.at/akadgrad/rdf#titel"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
				<label value="Pruefer3" id="student-abschlusspruefung-label-pruefer3" control="student-abschlusspruefung-menulist-pruefer3" />
    			<menulist id="student-abschlusspruefung-menulist-pruefer3"
						  editable="true" disabled="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/person/liste" 
				          oninput="StudentAbschlusspruefungMenulistPersonLoad(this)">
					<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/person/rdf#person_id"
				        		      label="rdf:http://www.technikum-wien.at/person/rdf#anzeigename"
							  		  uri="rdf:*"/>
							</menupopup>
					</template>
				</menulist>
    		</row>
    		<row>
    			<vbox>
    				<label value="Datum" control="student-abschlusspruefung-datum-datum" />
    				<label value="Sponsion" control="student-abschlusspruefung-datum-sponsion" />
    			</vbox>
    			<vbox>
					<box class="Datum" id="student-abschlusspruefung-datum-datum"  disabled="true"/>
    				<box class="Datum" id="student-abschlusspruefung-datum-sponsion"  disabled="true"/>
    			</vbox>    		
		    	
		    	<label value="Anmerkung" control="student-abschlusspruefung-textbox-anmerkung" />
    			<textbox id="student-abschlusspruefung-textbox-anmerkung" multiline="true" maxlength="256" disabled="true"/>
    		</row>
    		
    		<row>
    			<spacer />
    			<spacer />
    			<spacer />
    			<hbox>
    				<spacer flex="1" />
    				<button id="student-abschlusspruefung-button-speichern" label="Speichern" oncommand="StudentAbschlusspruefungSpeichern()" disabled="true"/>
    			</hbox>
    		</row>
    	</rows>
    </grid>		
</groupbox>

<spacer flex="1" />
</vbox>
</overlay>