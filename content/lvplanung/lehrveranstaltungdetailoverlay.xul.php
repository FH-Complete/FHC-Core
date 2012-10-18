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
require_once('../../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="LehrveranstaltungDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- *************************** LEHREINHEIT DETAILS ************************* -->
<vbox id="lehrveranstaltung-detail" style="margin:0px;" >
	<popupset>
		<popup id="lehrveranstaltung-detail-gruppe-tree-popup">
			<menuitem label="Entfernen" oncommand="LeGruppeDel();" />
		</popup>
	</popupset>
	<popupset>
		<popup id="lehrveranstaltung-lektor-tree-popup">
			<menuitem label="Entfernen" oncommand="LeMitarbeiterDel();" />
		</popup>
	</popupset>

	<!-- Hidden Fields -->
	<vbox hidden="true">
		<grid flex="1" style="padding:5px;">
		<columns  >
			<column  />
			<column style="min-width:240px" />
			<column />
			<column style="min-width:240px" />
	  	</columns>
	  	<rows>
			<row >
	  			<label value="NEW" />
				<checkbox id="lehrveranstaltung-detail-checkbox-new" hidden="false"/>
				<label value="Lehreinheit_id" />
				<textbox id="lehrveranstaltung-detail-textbox-lehreinheit_id" hidden="false"/>
			</row>
			<row >
	  			<label value="LVNR" />
	  			<textbox id="lehrveranstaltung-detail-textbox-lvnr" maxlength="20" disabled="true" />


			</row>
			<row>
				<label value="Lehrveranstaltung" />
	  			<textbox id="lehrveranstaltung-detail-textbox-lehrveranstaltung" maxlength="20" disabled="true"/>
			</row>
		</rows>
		</grid>
	</vbox>
	<hbox>
	<groupbox orientation="horizontal" flex="1">
		<caption id="lehrveranstaltung-detail-groupbox-caption" label="Details" />
		<!--Details-->
		<hbox flex="1">
		<grid datasources="rdf:null"
		      ref="http://www.technikum-wien.at/tempus/lva/liste"
			  style="padding:5px;">
			<columns  >
				<column  />
				<column style="min-width:240px" />
				<column />
				<column style="min-width:240px" />
		  	</columns>
		  	<rows>
				<row>
		  			<label value="Lehrfach" />
					<menulist id="lehrveranstaltung-detail-menulist-lehrfach" disabled="true"
					          datasources="rdf:null" flex="1"
					          ref="http://www.technikum-wien.at/lehrfach/liste"  >
						<template>
							<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/lehrfach/rdf#lehrfach_id"
						              label="rdf:http://www.technikum-wien.at/lehrfach/rdf#bezeichnung ( rdf:http://www.technikum-wien.at/lehrfach/rdf#fachbereich_kurzbz )"
									  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
					<label value="Lehrform" />
					<menulist id="lehrveranstaltung-detail-menulist-lehrform" disabled="true"
					          datasources="<?php echo APP_ROOT ?>rdf/lehrform.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/lehrform/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz rdf:http://www.technikum-wien.at/lehrform/rdf#bezeichnung"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
		  	  	</row>

				<row>
		  			<label value="Sprache" />
					<menulist id="lehrveranstaltung-detail-menulist-sprache"
					          datasources="<?php echo APP_ROOT; ?>rdf/sprache.rdf.php" flex="1"
							  ref="http://www.technikum-wien.at/sprachen/liste" disabled="true">
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
								          label="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
										  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
					<label value="UNR" />
		  	    	<textbox id="lehrveranstaltung-detail-textbox-unr" disabled="true" tooltiptext="Nur aendern wenn man weiss was man tut!"/>
				</row>

				<row>
					<label value="Studiensemester" />
					<menulist id="lehrveranstaltung-detail-menulist-studiensemester" disabled="true"
							  datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php" flex="0"
							  ref="http://www.technikum-wien.at/studiensemester/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
										  label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
										  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
					<label value="Lehre" />
					<checkbox id="lehrveranstaltung-detail-checkbox-lehre" disabled="true"/>
				</row>

				<row>
		  	    	<label value="Raumtyp" />
		  			<menulist id="lehrveranstaltung-detail-menulist-raumtyp" disabled="true"
		  			          datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/raumtyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
								          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz rdf:http://www.technikum-wien.at/raumtyp/rdf#beschreibung"
										  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
		  			<label value="Raumtyp alternativ" />
		  			<menulist id="lehrveranstaltung-detail-menulist-raumtypalternativ" disabled="true"
		  					  datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/raumtyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
								          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz rdf:http://www.technikum-wien.at/raumtyp/rdf#beschreibung"
										  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
		  		</row>

				<row>
					<label value=" " />
					<spacer />
					<spacer />
					<spacer />
				</row>

		  		<hbox>
					<label value="Start KW " />
					<textbox id="lehrveranstaltung-detail-textbox-startkw" width="50" disabled="true"/>
					<label value="     Stundenblockung " />
					<textbox id="lehrveranstaltung-detail-textbox-stundenblockung" width="50" disabled="true" />
					<label value="     Wochenrythmus " />
					<textbox id="lehrveranstaltung-detail-textbox-wochenrythmus" width="50" disabled="true"/>
				</hbox>

			</rows>
		</grid>

		<vbox flex="1">
			<label value=" Anmerkung" />
			<textbox id="lehrveranstaltung-detail-textbox-anmerkung" rows="5" multiline="true" disabled="true"/>

			<hbox>
				<spacer flex="1" />
				<button id="lehrveranstaltung-detail-button-save" label="speichern" oncommand="LeDetailSave();" disabled="true"/>
			</hbox>
		</vbox>
		</hbox>
	</groupbox>
	</hbox>

	<!-- ************* GRUPPEN ************** -->
	<label id="lehrveranstaltung-detail-label-lehreinheitgruppe" value="Gruppen" />
	<vbox flex="1">
		<hbox flex="7">
			<tree id="lehrveranstaltung-detail-tree-lehreinheitgruppe" seltype="single" hidecolumnpicker="false" flex="1" disabled="false"
				  datasources="rdf:null"
				  ref="http://www.technikum-wien.at/lehreinheitgruppe/liste"
				  flags="dont-build-content"
				  style="border: 1px solid black;"
				  ondragdrop="nsDragAndDrop.drop(event,LeLvbgrpDDObserver)"
				  ondragover="nsDragAndDrop.dragOver(event,LeLvbgrpDDObserver)"
  				  ondragenter="nsDragAndDrop.dragEnter(event,LeLvbgrpDDObserver)"
				  ondragexit="nsDragAndDrop.dragExit(event,LeLvbgrpDDObserver)"
				  context="lehrveranstaltung-detail-gruppe-tree-popup"
				  onkeypress="LvDetailGruppenTreeKeyPress(event)"
			>
				<treecols>
					<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-bezeichnung" label="Bezeichnung" flex="2" hidden="false"  persist="hidden, width, ordinal"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#bezeichnung" />
					<splitter class="tree-splitter"/>
					<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-beschreibung" label="Beschreibung" flex="2" hidden="true"  persist="hidden, width, ordinal"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#beschreibung" />
					<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-studiengang" label="Studiengang" flex="2" hidden="true"  persist="hidden, width, ordinal"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#studiengang_bezeichnung" />
					<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-semester" label="Semester" flex="2" hidden="true"  persist="hidden, width, ordinal"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#semester" />
					<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-lehreinheitgruppe_id" label="ID" flex="2" hidden="true"  persist="hidden, width, ordinal"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#lehreinheitgruppe_id" />
				</treecols>
				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#bezeichnung"   />
									<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#beschreibung"   />
									<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#studiengang_bezeichnung"   />
									<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#semester"   />
									<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#lehreinheitgruppe_id"   />
				 				</treerow>
				 			</treeitem>
				 		</treechildren>
				 	</rule>
				</template>
			</tree>
			<spacer flex="2"/>
		</hbox>
		<spacer flex="1"/>
	</vbox>

</vbox>

<!-- ************************** LEKTORZUTEILUNG ********************** -->
<vbox id="lehrveranstaltung-lektorzuteilung">
	<hbox flex="1" style="padding: 10px">
		<vbox width="250">
			<hbox flex="1">
				<tree id="lehrveranstaltung-detail-tree-lehreinheitmitarbeiter" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null"
						ref="http://www.technikum-wien.at/lehreinheitmitarbeiter/liste"
						onselect="LeMitarbeiterAuswahl();" flags="dont-build-content"
						style="border: 1px solid black;"
						ondragdrop="nsDragAndDrop.drop(event,LeLektorDDObserver)"
						ondragover="nsDragAndDrop.dragOver(event,LeLektorDDObserver)"
						ondragenter="nsDragAndDrop.dragEnter(event,LeLektorDDObserver)"
						ondragexit="nsDragAndDrop.dragExit(event,LeLektorDDObserver)"
						context="lehrveranstaltung-lektor-tree-popup"
						onkeypress="LvDetailMitarbeiterTreeKeyPress(event)"
				>
					<treecols>
						<treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-nachname" label="Nachname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname"/>
					    <splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-vorname" label="Vorname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname"/>
					    <splitter class="tree-splitter"/>
					    <treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid" label="UID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid"/>
					    <splitter class="tree-splitter"/>
					    <treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id"/>
					    <splitter class="tree-splitter"/>
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id"   />
					 				</treerow>
					 			</treeitem>
					 		</treechildren>
					 	</rule>
				  	</template>
				</tree>
				<spacer />
			</hbox>
		</vbox>

		<vbox>
		<hbox>
		<groupbox>
			<caption label="Lektorendaten" />
			<vbox flex="1">
			<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-lehreinheit_id" hidden="true"/>
			<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-mitarbeiter_uid" hidden="true"/>
			<grid align="end" flex="1"
			      flags="dont-build-content"
				  enableColumnDrag="true">
				<columns>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
				</columns>

				<rows>
					<row>
						<label align="end" control="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz" value="Lehrfunktion:"/>
						<menulist id="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz" disabled="true" oncommand="LeMitarbeiterValueChanged();"
		    		              datasources="<?php echo APP_ROOT; ?>rdf/lehrfunktion.rdf.php"
				                  ref="http://www.technikum-wien.at/lehrfunktion/liste" flex="1">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"
					                         value="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"/>
					            </menupopup>
					         </template>
				   		 </menulist>
				   		 <label align="end" control="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor" value="Lektor:"/>
						 <menulist id="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor" disabled="true" oncommand="LeMitarbeiterLektorChange(); LeMitarbeiterValueChanged();"
	    		                   datasources="<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php"
			                       ref="http://www.technikum-wien.at/mitarbeiter/_alle" flex="1">
				         <template>
				            <menupopup>
				               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
				                         value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
				            </menupopup>
				         </template>
			   		 	</menulist>
					</row>
			    	<row>
    					<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden" value="Semesterstunden: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden" disabled="true" maxlength="6" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten(); if(parseInt(this.value)) document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').value= parseInt(this.value)"/>
    					<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden" value="Planstunden: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden" disabled="true" maxlength="3" flex="1" oninput="LeMitarbeiterValueChanged();"/>
    				</row>
			    	<row>
			    		<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz" value="Stundensatz: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz" disabled="true" maxlength="6" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten()"/>
    					<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor" hidden="true" value="Faktor: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor" hidden="true" disabled="true" maxlength="3" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten()"/>
					</row>
					<row>
			    		<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung" value="Anmerkung: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung" disabled="true" maxlength="256" flex="1" oninput="LeMitarbeiterValueChanged();"/>
    					<label control="lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden" value="BIS-Melden: "/>
    					<checkbox id="lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden" disabled="true" flex="1" oncommand="LeMitarbeiterValueChanged();"/>
					</row>
    			</rows>
    			</grid>
    			<hbox flex="1">
    				<!--<spacer flex="1" />-->
    				<hbox flex="1">
    					<label value='Gesamtkosten:' />
    					<label id="lehrveranstaltung-lehreinheitmitarbeiter-label-gesamtkosten" value='' />
    				</hbox>
					<button label="Speichern" disabled="true" id="lehrveranstaltung-lehreinheitmitarbeiter-button-save" oncommand="LeMitarbeiterSave();"/>
				</hbox>
			</vbox>
		</groupbox>
		</hbox>
		</vbox>
		<spacer flex="1" />
	</hbox>
</vbox>
</overlay>