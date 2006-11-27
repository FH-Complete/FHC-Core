<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>

<!DOCTYPE overlay>

<overlay id="LehreinheitenDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<vbox id="LehreinheitenDetailEditor" flex="1" style="overflow:auto">

	<textbox id="textbox-lehreinheiten-detail-lehreinheit_id" hidden="true" />
	<tabbox id="tabbox-lehreinheitenDetail" flex="3" orient="vertical" hidden="false">
		<tabs orient="horizontal">
			<tab id="tab-lehreinheiten-daten" label="Daten"  />
			<tab id="tab-lehreinheiten-lektoren" label="Lektoren"  />
		</tabs>
		<tabpanels id="tabpanels-lehreinheiten-main" flex="1">
		  <!--Daten-->
 		  <vbox style="margin-top: 5px;">
				<textbox id="textbox-lehreinheiten-detail-studiengang" oninput="LehreinheitenDetailValueChange()" hidden="true"/>
				<textbox id="textbox-lehreinheiten-detail-studiensemester" oninput="LehreinheitenDetailValueChange()" hidden="true"/>
				<textbox id="textbox-lehreinheiten-detail-lehrveranstaltung" oninput="LehreinheitenDetailValueChange()" hidden="true"/>
				<textbox id="textbox-lehreinheiten-detail-lehreinheit_fk" oninput="LehreinheitenDetailValueChange()" hidden="true"/>
				<grid align="end" flex="1"
					 flags="dont-build-content"
					enableColumnDrag="true"
					>
					<columns>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
					</columns>
			
				<rows>
				   	<row>
						<label align="end" control="menulist-lehreinheiten-detail-fachbereich" value="Fachbereich:"/>
				   		<menulist id="menulist-lehreinheiten-detail-fachbereich" oncommand="LehreinheitenDetailValueChange();"
			    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/fachbereich.rdf.php"
					              ref="http://www.technikum-wien.at/fachbereich/alle">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/fachbereich/rdf#name"
					                         value="rdf:http://www.technikum-wien.at/fachbereich/rdf#fachbereich_id"/>
					            </menupopup>
					         </template>
					    </menulist>
					    <label align="end" control="menulist-lehreinheiten-detail-ausbildungssemester" value="Ausbildungssemester:"/>
				   		<menulist id="menulist-lehreinheiten-detail-ausbildungssemester" oncommand="LehreinheitenDetailsetGruppenMenulistDatasource();LehreinheitenDetailValueChange();"
			    		          datasources="rdf:null"
					              ref="http://www.technikum-wien.at/ausbildungssemester/liste">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/ausbildungssemester/rdf#name"
					                         value="rdf:http://www.technikum-wien.at/ausbildungssemester/rdf#ausbildungssemester_id"/>
					            </menupopup>
					         </template>
					    </menulist>
					    <label align="end" control="menulist-lehreinheiten-detail-gruppe" value="Gruppe:"/>
				   		<menulist id="menulist-lehreinheiten-detail-gruppe" oncommand="LehreinheitenDetailValueChange();"
			    		          datasources="rdf:null"
					              ref="http://www.technikum-wien.at/gruppen/liste">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/gruppen/rdf#fullname"
					                         value="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_id"/>
					            </menupopup>
					         </template>
					    </menulist>
			    	</row>
			    	
			    	<row>
						<label control="textbox-lehreinheiten-detail-bezeichnung" value="Bezeichnung: "/>
			    		<textbox id="textbox-lehreinheiten-detail-bezeichnung" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>			    		
			    		<label control="textbox-lehreinheiten-detail-kurzbezeichnung" value="Kurzbezeichnung: "/>
			    		<textbox id="textbox-lehreinheiten-detail-kurzbezeichnung" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>
			    		<label align="end" control="menulist-lehreinheiten-detail-lehrform" value="Lehrform:"/>
				   		<menulist id="menulist-lehreinheiten-detail-lehrform" oncommand="LehreinheitenDetailValueChange();"
			    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/lehrform.rdf.php"
					              ref="http://www.technikum-wien.at/lehrform/alle">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/lehrform/rdf#bezeichnung"
					                         value="rdf:http://www.technikum-wien.at/lehrform/rdf#lehrform_id"/>
					            </menupopup>
					         </template>
					    </menulist>
					    
			    	</row>
			
			    	<row>
						<label control="textbox-lehreinheiten-detail-nummer" value="Nummer: "/>
			    		<textbox id="textbox-lehreinheiten-detail-nummer" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>
					    <label align="end" control="menulist-lehreinheiten-detail-koordinator" value="Koordinator:"/>
				   		<menulist id="menulist-lehreinheiten-detail-koordinator" oncommand="LehreinheitenDetailValueChange();"
			    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/mitarbeiter.rdf.php?aktiv=true"
					              ref="http://www.technikum-wien.at/mitarbeiter/alle">
					         <template>
					            <menupopup>		            	
					               <menuitem value="-1" label="Kein Koordinator" />
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname^ rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
					                         value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitarbeiter_id"
					                         sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"
					                         />
					            </menupopup>
					         </template>
					    </menulist>		
			    	</row>
			    </rows>
			  
				</grid>
				
				<groupbox>
						<caption label="Kosten" />
						<vbox flex="1" style="margin: 5px;">
						<grid align="end" flex="1"
							 flags="dont-build-content"
							enableColumnDrag="true"
							>
							<columns>
							<column />
							<column flex="1"/>
							<column />
							<column flex="1"/>
							<column />
							<column flex="1"/>
							</columns>
							<rows>
								<row>
									<label control="textbox-lehreinheiten-detail-sws" value="Semesterwochenstunden: "/>
			    					<textbox id="textbox-lehreinheiten-detail-sws" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>
			    					<label control="textbox-lehreinheiten-detail-gesamtstunden" value="Gesamtstunden: "/>
			    					<textbox id="textbox-lehreinheiten-detail-gesamtstunden" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>
			    				</row>
			    				<row>
			    					<label control="textbox-lehreinheiten-detail-planlektoren" value="Anzahl der Lektoren: "/>
			    					<hbox><textbox id="textbox-lehreinheiten-detail-planlektoren" maxlength="2" oninput="LehreinheitenDetailValueChange()"/><spacer flex="1"/></hbox>
			    					<label control="textbox-lehreinheiten-detail-plankostenprolektor" value="Kosten pro Lektor: "/>
			    					<textbox id="textbox-lehreinheiten-detail-plankostenprolektor" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>			    		
									<label control="textbox-lehreinheiten-detail-planfaktor" value="Geplanter Faktor: "/>
						    		<textbox id="textbox-lehreinheiten-detail-planfaktor" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>		
			    				</row>
			    			</rows>
			    		</grid>
						</vbox>
				</groupbox>
				<groupbox>
						<caption label="LV-Planung" />
						<vbox flex="1" style="margin: 5px;">
						<grid align="end" flex="1"
							 flags="dont-build-content"
							enableColumnDrag="true"
							>
							<columns>
							<column />
							<column flex="1"/>
							<column />
							<column flex="1"/>
							<column />
							<column flex="1"/>
							</columns>
							<rows>
								<row>
    								<label align="end" control="menulist-lehreinheiten-detail-raumtyp" value="Raumtyp:"/>
							   		<menulist id="menulist-lehreinheiten-detail-raumtyp" oncommand="LehreinheitenDetailValueChange();"
						    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/raumtyp.rdf.php"
								              ref="http://www.technikum-wien.at/raumtyp/alle">
								         <template>
								            <menupopup>
								               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/raumtyp/rdf#bezeichnung"
								                         value="rdf:http://www.technikum-wien.at/raumtyp/rdf#raumtyp_id"/>
								            </menupopup>
								         </template>
								    </menulist>
								    <label align="end" control="menulist-lehreinheiten-detail-raumtypalternativ" value="Raumtyp Alternativ:"/>
							   		<menulist id="menulist-lehreinheiten-detail-raumtypalternativ" oncommand="LehreinheitenDetailValueChange();"
						    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/raumtyp.rdf.php"
								              ref="http://www.technikum-wien.at/raumtyp/alle">
								         <template>
								            <menupopup>
								               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/raumtyp/rdf#bezeichnung"
								                         value="rdf:http://www.technikum-wien.at/raumtyp/rdf#raumtyp_id"/>
								            </menupopup>
								         </template>
								    </menulist>
								</row>
								<row>			
									<label align="end" control="menulist-lehreinheiten-detail-wochenrythmus" value="Wochenrythmus:"/>
							   		<menulist id="menulist-lehreinheiten-detail-wochenrythmus" oncommand="LehreinheitenDetailValueChange();"
						    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/wochenrythmus.rdf.php"
								              ref="http://www.technikum-wien.at/wochenrythmus/alle">
								         <template>
								            <menupopup>
								               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/wochenrythmus/rdf#bezeichnung"
								                         value="rdf:http://www.technikum-wien.at/wochenrythmus/rdf#wochenrythmus_id"/>
								            </menupopup>
								         </template>
								    </menulist>			    		
						    		<label control="textbox-lehreinheiten-detail-kalenderwoche" value="Kalenderwoche: "/>
						    		<textbox id="textbox-lehreinheiten-detail-kalenderwoche" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>
						    		<label control="textbox-lehreinheiten-detail-stundenblockung" value="Stundenblockung: "/>
						    		<textbox id="textbox-lehreinheiten-detail-stundenblockung" maxlength="255" flex="1" oninput="LehreinheitenDetailValueChange()"/>		  
									</row>
			    			</rows>
			    		</grid>
			    		<hbox>
							<label control="textbox-lehreinheiten-detail-bemerkungen" value="Bemerkung:"/>
			  				<textbox id="textbox-lehreinheiten-detail-bemerkungen" multiline="true" oninput="LehreinheitenDetailValueChange()" flex="1"/>
			  			</hbox>
						</vbox>
				</groupbox>
				
				<hbox>
					<spacer flex="1"/>
					<button label="Abbrechen" oncommand="LehreinheitenTreeAuswahl();"/>
					<button label="Speichern" oncommand="LehreinheitenDetailSpeichern()"/>
				</hbox>
				<spacer flex="5"/>
			</vbox>
<!--Lektoren Tab-->
			<vbox>
				<hbox flex="1" style="padding: 10px">
					<vbox width="250">
						<hbox flex="1">
							<tree id="tree-liste-mitarbeiterlehreinheiten" seltype="single" hidecolumnpicker="false" flex="1"
									datasources="rdf:null"
									ref="http://www.technikum-wien.at/mitarbeiterlehreinheiten/alle"
									onselect="MitarbeiterLehreinheitenTreeAuswahl();" flags="dont-build-content"
									style="border: 1px solid black;"
							>
								<treecols>
									<treecol id="tree-liste-mitarbeiterlehreinheiten-col-nachname" label="Nachname" flex="2" hidden="false"
								    	class="sortDirectionIndicator"
								    	sort="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#nachname" onclick="LehreinheitenTreeSort()"/>
								    <splitter class="tree-splitter"/>
									<treecol id="tree-liste-mitarbeiterlehreinheiten-col-vorname" label="Vorname" flex="2" hidden="false"
								    	class="sortDirectionIndicator"
								    	sort="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#vorname" onclick="LehreinheitenTreeSort()"/>
								    <splitter class="tree-splitter"/>		
								    <treecol id="tree-liste-mitarbeiterlehreinheiten-col-mitarbeiter_lehreinheit_id" label="MitarbeiterLehreinheitID" flex="2" hidden="true"
								    	class="sortDirectionIndicator"
								    	sort="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#mitarbeiter_lehreinheit_id" onclick="LehreinheitenTreeSort()"/>
								    <splitter class="tree-splitter"/>						
								</treecols>
								<template>
									<rule>
										<treechildren>
											<treeitem uri="rdf:*">
												<treerow>
													<treecell label="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#nachname"   />
													<treecell label="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#vorname"   />
													<treecell label="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheiten/rdf#mitarbeiter_lehreinheit_id"   />
								 				</treerow>
								 			</treeitem>
								 		</treechildren>
								 	</rule>
							  	</template>							
							</tree>
							<spacer />
						</hbox>
						
						<hbox>
							<button label="+" oncommand="MitarbeiterLehreinheitenAdd()" style="max-width: 30px;"/>
							<button label="-" oncommand="MitarbeiterLehreinheitenDel()" style="max-width: 30px;"/>
						</hbox>
					</vbox>
					<vbox>
					<hbox>
					<groupbox>
						<caption label="Lektorendaten" />
						<vbox flex="1">
						<textbox id="textbox-lehreinheiten-detail-lektoren-lehreinheit_id" hidden="true"/>
						<textbox id="textbox-lehreinheiten-detail-lektoren-mitarbeiter_lehreinheit_id" hidden="true"/>
						<grid align="end" flex="1"
								 flags="dont-build-content"
								enableColumnDrag="true"
								>
							<columns>
								<column />
								<column flex="1"/>
								<column />
								<column flex="1"/>								
							</columns>
			
							<rows>
								<row>
									<label align="end" control="menulist-lehreinheiten-detail-funktion" value="Funktion:"/>
									<menulist id="menulist-lehreinheiten-detail-funktion" disabled="true" oncommand="LehreinheitenDetailLektorValueChanged();"
					    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/mitarbeiterlehreinheitenfunktionen.rdf.php"
							              ref="http://www.technikum-wien.at/mitarbeiterlehreinheitenfunktionen/alle" flex="1">
								         <template>
								            <menupopup>
								               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheitenfunktionen/rdf#bezeichnung"
								                         value="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheitenfunktionen/rdf#funktion_id"/>
								            </menupopup>
								         </template>
							   		 </menulist>
							   		 <label align="end" control="menulist-lehreinheiten-detail-mitarbeiterauswahl" value="Lektor:"/>
							   		 <hbox flex="1">
										<menulist id="menulist-lehreinheiten-detail-mitarbeiterauswahl" disabled="true" oncommand="LehreinheitenDetailLektorValueChanged();"
					    		          datasources="rdf:*"
							              ref="http://www.technikum-wien.at/mitarbeiterlehreinheitenauswahl/alle" flex="1">
								         <template>
								            <menupopup>
								               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheitenauswahl/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiterlehreinheitenauswahl/rdf#vorname"
								                         value="rdf:http://www.technikum-wien.at/mitarbeiterlehreinheitenauswahl/rdf#mitarbeiter_id"/>
								            </menupopup>
								         </template>
							   		 	</menulist>
							   		 	<button label='+' id="button-lehreinheiten-detail-lektoren-auswahladd" disabled="true" oncommand='OpenMitarbeiterAuswahlDialog()' style="max-width: 30px;"/>
							   		 </hbox>
								</row>
							
						    	<row>						    	
			    					<label control="textbox-lehreinheiten-detail-lektoren-kosten" value="Kosten: "/>
			    					<textbox id="textbox-lehreinheiten-detail-lektoren-kosten" disabled="true" maxlength="255" flex="1" oninput="LehreinheitenDetailLektorValueChanged()"/>		  
									<label control="textbox-lehreinheiten-detail-lektoren-gesamtstunden" value="Gesamtstunden: "/>
			    					<textbox id="textbox-lehreinheiten-detail-lektoren-gesamtstunden" disabled="true" maxlength="255" flex="1" oninput="LehreinheitenDetailLektorValueChanged()"/>		  
			    				</row>
						    	<row>						    	
			    					<label control="textbox-lehreinheiten-detail-lektoren-faktor" value="Faktor: "/>
			    					<textbox id="textbox-lehreinheiten-detail-lektoren-faktor" disabled="true" maxlength="255" flex="1" oninput="LehreinheitenDetailLektorValueChanged()"/>		  
									<label control="textbox-lehreinheiten-detail-lektoren-differenz" value="Differenz: "/>
			    					<textbox id="textbox-lehreinheiten-detail-lektoren-differenz" maxlength="255" flex="1" oninput="LehreinheitenDetailLektorValueChanged()" disabled="true"/>
			    				</row>
			    			</rows>
			    			</grid>
			    			<hbox flex="1">
			    				<spacer flex="1" />
								<button label="Speichern" disabled="true" id="button-lehreinheiten-detail-lektoren-save" oncommand="MitarbeiterLehreinheitenZuteilungSave();"/>
							</hbox>
						</vbox>
					</groupbox>
					</hbox>
					</vbox>
					<spacer flex="1" />
				</hbox>		
			</vbox>
		</tabpanels>
</tabbox>
</vbox>
</overlay>