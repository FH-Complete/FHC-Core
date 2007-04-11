<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';

?>

<!DOCTYPE overlay>

<overlay id="MitarbeiterDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<!-- datasources="rdf:null" ref="http://www.technikum-wien.at/mitarbeiter/alle" -->

<vbox id="MitarbeiterDetailEditor" flex="1" style="overflow:auto">
<tabbox id="tabbox-MitarbeiterDetail" flex="3" orient="vertical" hidden="false">
		<tabs orient="horizontal">
			<tab id="tab-mitarbeiter-person" label="Stammdaten"  />
			<tab id="tab-mitarbeiter-detail" label="Zusätzliche Daten"  />
		</tabs>
		<tabpanels id="tabpanels-mitarbeiter-main" flex="1">
 		  <vbox>
			<groupbox id='groupbox-personendaten'>
			<!--PersonenDaten-->
				<caption label="Personendaten" />
				<textbox id="textbox-mitarbeiter-detail-mitarbeiter_id" hidden="true" />
				<textbox id="textbox-mitarbeiter-detail-person_id" hidden="true" />
				<textbox id="textbox-mitarbeiter-detail-aktstatus" hidden="true" />
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
					<column />
					<column flex="1"/>
				</columns>

				<rows>
			    	<row>
			    		<label align="end" control="textbox-mitarbeiter-detail-anrede" value="Anrede:"/>
			    		<textbox id="textbox-mitarbeiter-detail-anrede" flex="1" value="" oninput="treeMitarbeiterValueChange()" onchange="MitarbeiterAnredeValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-titelpre" value="Titel (Pre):"/>
			    		<textbox id="textbox-mitarbeiter-detail-titelpre" flex="1" value="" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-titelpost" value="Titel (Post):"/>
			    		<textbox id="textbox-mitarbeiter-detail-titelpost" flex="1" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-uid" value="UID:"/>
						<hbox><textbox id="textbox-mitarbeiter-detail-uid" size="20" maxlength="20" oninput="treeMitarbeiterValueChange()"/><spacer /></hbox>
			    	</row>
			    	<row>
			    		<label control="textbox-mitarbeiter-detail-nachname" value="Nachname:"/>
			    		<textbox id="textbox-mitarbeiter-detail-nachname" class="pflichtfeld" flex="1" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-vorname" value="Vorname:"/>
			    		<textbox id="textbox-mitarbeiter-detail-vorname" class="pflichtfeld" flex="1" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-vornamen" value="Vornamen:"/>
			    		<textbox id="textbox-mitarbeiter-detail-vornamen" flex="1" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="textbox-mitarbeiter-detail-svnr" value="SVNR:" />
						<hbox><textbox id="textbox-mitarbeiter-detail-svnr" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()" onchange="MitarbeiterSVNRValueChange()"/><spacer /></hbox>
			    	</row>

			    	<row>
						<label control="textbox-mitarbeiter-detail-geburtsort" value="Geburtsort: "/>
			    		<textbox id="textbox-mitarbeiter-detail-geburtsort" maxlength="255" flex="1" oninput="treeMitarbeiterValueChange()"/>
			    		<label control="menulist-mitarbeiter-detail-staatsbuergerschaft" value="Staatsbürgerschaft:"/>
			    		<menulist id="menulist-mitarbeiter-detail-staatsbuergerschaft" oncommand="treeMitarbeiterValueChange();"
			    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/nation.rdf.php?ohnesperre=true"
					              ref="http://www.technikum-wien.at/nation/alle">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
					                         value="rdf:http://www.technikum-wien.at/nation/rdf#code"/>
					            </menupopup>
					         </template>
					    </menulist>
			    		<label control="menulist-mitarbeiter-detail-geburtsnation" value="Geburtsnation:"/>
			    		<menulist id="menulist-mitarbeiter-detail-geburtsnation" oncommand="treeMitarbeiterValueChange();"
			    		          datasources="<?php echo APP_ROOT; ?>rdf/fas/nation.rdf.php"
					              ref="http://www.technikum-wien.at/nation/alle">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
					                         value="rdf:http://www.technikum-wien.at/nation/rdf#code"/>
					            </menupopup>
					         </template>
					    </menulist>
					    <label control="textbox-mitarbeiter-detail-ersatzkennzeichen" value="Ersatzkennzeichen:"/>
			    		<textbox id="textbox-mitarbeiter-detail-ersatzkennzeichen" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/>
			    	</row>
			    	<row>
			    		<label control="textbox-mitarbeiter-detail-geburtsdatum" value="Gebutsdatum:"/>
			    		<hbox><textbox id="textbox-mitarbeiter-detail-geburtsdatum" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/><spacer /></hbox>
			    		<label control="button-mitarbeiter-detail-geschlecht" value="Geschlecht:"/>
			    		<button id='button-mitarbeiter-detail-geschlecht' label='maennlich' oncommand="MitarbeiterDetailGeschlechtChange()" class="change" />
			    		<label control="menulist-mitarbeiter-detail-familienstand" value="Familienstand: "/>
			    		<menulist id="menulist-mitarbeiter-detail-familienstand"
			    		          oncommand="treeMitarbeiterValueChange();">
			                <menupopup>
			      				<menuitem label="  ---  " value="0"/>
			      				<menuitem label="ledig" value="1" selected="true"/>
			      				<menuitem label="verheiratet" value="2"/>
			      				<menuitem label="geschieden" value="3"/>
			      				<menuitem label="verwitwet" value="4"/>
			    			</menupopup>
			    		</menulist>
			    		<label control="textbox-mitarbeiter-detail-anzahlderkinder" value="Anzahl Kinder:"/>
			    		<hbox><textbox id="textbox-mitarbeiter-detail-anzahlderkinder" size="2" maxlength="2" value="0" oninput="treeMitarbeiterValueChange()"/><spacer /></hbox>
			    	</row>
			    </rows>
			  </grid>
			  <grid align="end" flex="1"
					 flags="dont-build-content"
					enableColumnDrag="true"	class="style-groupbox"
					>
				<columns>
					<column />
					<column flex="2"/>
					<column flex="1"/>
					<column />
				</columns>

				<rows>

			  		<row>
			    		<label control="textbox-mitarbeiter-detail-bemerkung" value="Bemerkung:"/>
			    		<textbox id="textbox-mitarbeiter-detail-bemerkung" multiline="true" oninput="treeMitarbeiterValueChange()"/>
						<spacer flex="1"/>
						<checkbox label="Aktiv" id="checkbox-mitarbeiter-detail-aktiv" checked="true" oncommand="treeMitarbeiterValueChange()"/>
						<checkbox label="BIS Melden" id="checkbox-mitarbeiter-detail-bismelden" checked="true" oncommand="treeMitarbeiterValueChange()"/>
			    	</row>
			  	</rows>
			  </grid>

			</groupbox>
		<hbox>
			<groupbox flex="8">
				<caption label="Mitarbeiterdaten" />
			<grid align="end" flex="1"
					 flags="dont-build-content"
					enableColumnDrag="true"
					>
				<columns>
					<column flex="1"/>
					<column flex="1"/>
					<column flex="1"/>
					<column flex="1"/>
					<column flex="1"/>
					<column flex="1"/>
				</columns>

				<rows>
			    	<row>
			    		<label align="end" control="textbox-mitarbeiter-detail-personal_nr" value="Personalnummer:"/>
			    		<textbox id="textbox-mitarbeiter-detail-personal_nr" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/>
			    		<label align="end" control="textbox-mitarbeiter-detail-kurzbezeichnung" value="Kurzbezeichnung:"/>
						<hbox>
							<textbox id="textbox-mitarbeiter-detail-kurzbezeichnung"  class="pflichtfeld" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/>
							<button id='button-mitarbeiter-detail-gen_kurzbez' label='G' oncommand="MitarbeiterDetailKurzbzGenerate()"/>
						</hbox>
						<label align="end" control="textbox-mitarbeiter-detail-beginndatum" value="Beginndatum:"/>
			    		<hbox><textbox id="textbox-mitarbeiter-detail-beginndatum" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/><spacer /></hbox>
			    	</row>
			    	<row>
			    	    <label align="end" control="textbox-mitarbeiter-detail-stundensatz" value="Stundensatz:"/>
			    		<textbox id="textbox-mitarbeiter-detail-stundensatz" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/>
			    		<checkbox label="Habilitation" id="checkbox-mitarbeiter-detail-habilitation" checked="false" oncommand="treeMitarbeiterValueChange()"/>
			   			<spacer/>
						<checkbox label="ausgeschieden am" id="checkbox-mitarbeiter-detail-ausgeschieden" checked="false" oncommand="treeMitarbeiterValueChange(); MitarbeiterDetailAusgeschiedenChange();"/>
						<hbox><textbox id="textbox-mitarbeiter-detail-beendigungsdatum" disabled="true" size="10" maxlength="10" oninput="treeMitarbeiterValueChange()"/><spacer /></hbox>

			    	</row>
			  	</rows>
			 </grid>
			 <hbox class="style-groupbox">
			 	<label align="end" control="menulist-mitarbeiter-detail-ausbildung" value="Höchste abgeschlossene Ausbildung:"/>
			 	<menulist id="menulist-mitarbeiter-detail-ausbildung" oncommand="treeMitarbeiterValueChange();"
			              datasources="<?php echo APP_ROOT; ?>rdf/fas/ausbildung.rdf.php"
				          ref="http://www.technikum-wien.at/ausbildung/alle">
				    <template>
				       <menupopup>
					      <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/ausbildung/rdf#bezeichnung"
					                value="rdf:http://www.technikum-wien.at/ausbildung/rdf#ausbildung_id"/>
					      </menupopup>
					</template>
				</menulist>
			 </hbox>
			</groupbox>
			<spacer flex="2" />
			<vbox flex="1">
				<spacer flex="1"/>
				<hbox><button id="button-mitarbeiter-detail-zurueck" flex="1" disabled="true" label="Zurück" oncommand="MitarbeiterDetailZurueck();"/></hbox>
				<hbox><button id="button-mitarbeiter-detail-speichern" flex="1" disabled="true" label="Speichern" oncommand="saveMitarbeiter();"/></hbox>
				<spacer flex="1"/>
			</vbox>
			<spacer flex="2" />

		</hbox>



			<groupbox hidden="false" flex="1">
			<caption label="Funktionen" />
			<hbox flex="1" class="hbox-tree">
				<tree id="tree-liste-funktionen" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null"
						ref="http://www.technikum-wien.at/funktionen/alle"
						onselect="" flags="dont-build-content"
						enableColumnDrag="true"
						ondblclick="MitarbeiterDetailFunktionenBearbeiten()"
						>
					<treecols>
						<treecol id="tree-liste-funktionen-col-funktion_id" label="Funktion_id" flex="1"  hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#funktion_id" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-mitarbeiter_id" label="Mitarbeiter_id" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#mitarbeiter_id" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-studiensemester_bezeichnung" label="Studiensemester" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#studiensemester_id" />
					    <splitter class="tree-splitter"/>
					    <?php /*Wenn die ID nicht angezeigt wird, dann funktioniert das Sortieren von studiensemester_bezeichnung nicht richtig*/ ?>
					    <treecol id="tree-liste-funktionen-col-studiensemester_id" label="StudiensemesterID" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#studiensemester_id" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-erhalter_id" label="Erhalter" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#erhalter_bezeichnung" />
						<splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-studiengang_bezeichnung" label="Studiengang" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#studiengang_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-fachbereich_bezeichnung" label="Fachbereich" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#fachbereich_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-name" label="Name" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#name" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-funktion_bezeichnung" label="Funktion" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#funktion_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-beschart1" label="Beschäftigungsart 1" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#beschart1_bezeichnung" />
					    <splitter class="tree-splitter"/>
						<treecol id="tree-liste-funktionen-col-beschart2" label="Beschäftigungsart 2" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#beschart2_bezeichnung" />
					    <splitter class="tree-splitter"/>
						<treecol id="tree-liste-funktionen-col-verwendung" label="Verwendung" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#verwendung_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-hauptberuflich" label="Hauptberuflich" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#hauptberuflich" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-hauptberuf" label="Hauptberuf" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#hauptberuf_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-entwicklungsteam" label="Mitglied im Entwicklungsteam" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#entwicklungsteam" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-besonderequalifikation" label="Besondere Qualifikation" flex="1" hidden="true" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#besonderequalifikation_bezeichnung" />
					    <splitter class="tree-splitter"/>
					    <treecol id="tree-liste-funktionen-col-ausmass" label="Ausmass" flex="1" hidden="false" persist="hidden"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/funktionen/rdf#ausmass_bezeichnung" />

					</treecols>

					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#funktion_id"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#mitarbeiter_id"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#studiensemester_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#studiensemester_id"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#erhalter_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#studiengang_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#fachbereich_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#name"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#funktion_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#beschart1_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#beschart2_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#verwendung_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#hauptberuflich"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#hauptberuf_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#entwicklungsteam"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#besonderequalifikation_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/funktionen/rdf#ausmass_bezeichnung"   />
					 				</treerow>
					 			</treeitem>
					 		</treechildren>
					 	</rule>
				  	</template>

				</tree>
				<vbox>
					<button id="button-mitarbeiter-detail-funktionen-neu" disabled="true" label="Neu" oncommand="MitarbeiterDetailFunktionenNeu();"/>
					<button id="button-mitarbeiter-detail-funktionen-bearbeiten" disabled="true" label="Bearbeiten" oncommand="MitarbeiterDetailFunktionenBearbeiten();"/>
					<button id="button-mitarbeiter-detail-funktionen-loeschen" disabled="true" label="Löschen" oncommand="MitarbeiterDetailFunktionenLoeschen();"/>
					<spacer flex="1" />
					<button id="button-mitarbeiter-detail-funktionen-alleanzeigen" disabled="true" label="Alle Anzeigen" oncommand="MitarbeiterDetailFunktionenAlleAnzeigen();"/>
				</vbox>
			</hbox>
		</groupbox>

         </vbox>

<!-- Seite 2 -->

        <vbox>
				<grid align="end" flex="1"
						 flags="dont-build-content"
						enableColumnDrag="true"
						>
					<columns>
						<column flex="6"/>
						<column flex="1"/>
					</columns>

					<rows>
				    	<row>
				    		<vbox>
				    		<groupbox hidden="false">
								<caption label="Adressen" />
								<hbox class="hbox-tree">
									<tree id="tree-liste-adressen" seltype="multi" hidecolumnpicker="false" flex="1"
											datasources="rdf:null"
											ref="http://www.technikum-wien.at/adressen/alle"
											onselect="" flags="dont-build-content"
											enableColumnDrag="true"
											>
										<treecols>
											<treecol id="tree-liste-adressen-col-adresse_id" label="Adresse_id" flex="1"  hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#adresse_id" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-bismeldeadresse" label="BisMeldeAdresse" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#bismeldeadresse" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-gemeinde" label="Gemeinde" flex="2" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#gemeinde" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-name" label="Name" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#name" />
										    <splitter class="tree-splitter"/>
							    			<treecol id="tree-liste-adressen-col-nation" label="Nation" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#nation" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-plz" label="Plz" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#plz" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-ort" label="Ort" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#ort" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-adressen-col-strasse" label="Strasse" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#strassse" />
										    <splitter class="tree-splitter"/>
										    <!-- ist das selbe wie name
											<treecol id="tree-liste-adressen-col-typ" label="Typ" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#typ" />
										    <splitter class="tree-splitter"/>
										    -->
										    <treecol id="tree-liste-adressen-col-zustelladresse" label="Zustelladresse" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/adressen/rdf#zustelladresse" />
										    <splitter class="tree-splitter"/>
										</treecols>

										<template>
											<rule>
												<treechildren>
													<treeitem uri="rdf:*">
														<treerow>
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#adresse_id"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#bismeldeadresse"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#gemeinde"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#name"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#nation"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#plz"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#ort"   />
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#strasse"   />
															<!--<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#typ"   />-->
															<treecell label="rdf:http://www.technikum-wien.at/adressen/rdf#zustelladresse"   />
										 				</treerow>
										 			</treeitem>
										 		</treechildren>
										 	</rule>
									  	</template>

									</tree>
									<vbox>
										<button id="button-mitarbeiter-detail-adressen-neu" disabled="true" label="Neu" oncommand="MitarbeiterDetailAdressenNeu();"/>
										<button id="button-mitarbeiter-detail-adressen-bearbeiten" disabled="true" label="Bearbeiten" oncommand="MitarbeiterDetailAdressenBearbeiten();"/>
										<button id="button-mitarbeiter-detail-adressen-loeschen" disabled="true" label="Löschen" oncommand="MitarbeiterDetailAdressenLoeschen();"/>
									</vbox>
								</hbox>
							</groupbox>

				    		<groupbox hidden="false">
								<caption label="Emailadressen" />
								<hbox class="hbox-tree">
									<tree id="tree-liste-email" seltype="multi" hidecolumnpicker="false" flex="1"
											datasources="rdf:null"
											ref="http://www.technikum-wien.at/email/alle"
											onselect="" flags="dont-build-content"
											enableColumnDrag="true"
											>
										<treecols>
											<treecol id="tree-liste-email-col-email_id" label="Email_id" flex="1"  hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/email/rdf#email_id" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-email-col-email" label="Email" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/email/rdf#email" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-email-col-name" label="Name" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/email/rdf#name" />
										    <splitter class="tree-splitter"/>
										    <!-- ist das gleiche wie name
										    <treecol id="tree-liste-email-col-typ" label="Typ" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/email/rdf#typ" />
										    <splitter class="tree-splitter"/>
										    -->
										    <treecol id="tree-liste-email-col-zustelladresse" label="Zustelladresse" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/email/rdf#zustelladresse" />
										    <splitter class="tree-splitter"/>
										</treecols>

										<template>
											<rule>
												<treechildren>
													<treeitem uri="rdf:*">
														<treerow>
															<treecell label="rdf:http://www.technikum-wien.at/email/rdf#email_id"   />
															<treecell label="rdf:http://www.technikum-wien.at/email/rdf#email"   />
															<treecell label="rdf:http://www.technikum-wien.at/email/rdf#name"   />
															<!--<treecell label="rdf:http://www.technikum-wien.at/email/rdf#typ"   />-->
															<treecell label="rdf:http://www.technikum-wien.at/email/rdf#zustelladresse"   />
										 				</treerow>
										 			</treeitem>
										 		</treechildren>
										 	</rule>
									  	</template>

									</tree>
									<vbox>
										<button id="button-mitarbeiter-detail-email-neu" disabled="true" label="Neu" oncommand="MitarbeiterDetailEmailNeu();"/>
										<button id="button-mitarbeiter-detail-email-bearbeiten" disabled="true" label="Bearbeiten" oncommand="MitarbeiterDetailEmailBearbeiten();"/>
										<button id="button-mitarbeiter-detail-email-loeschen" disabled="true" label="Löschen" oncommand="MitarbeiterDetailEmailLoeschen();"/>
									</vbox>
								</hbox>
							</groupbox>

							<groupbox hidden="false">
								<caption label="Telefonnummern" />
								<hbox class="hbox-tree">
									<tree id="tree-liste-telefonnummern" seltype="multi" hidecolumnpicker="false" flex="1"
											datasources="rdf:null"
											ref="http://www.technikum-wien.at/telefonnummern/alle"
											onselect="" flags="dont-build-content"
											enableColumnDrag="true"
											>
										<treecols>
											<treecol id="tree-liste-telefonnummern-col-telefonnummer_id" label="Telefonnummer_id" flex="1"  hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/telefonnummern/rdf#telefonnummer_id" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-telefonnummern-col-name" label="Name" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/telefonnummern/rdf#name" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-telefonnummern-col-nummer" label="Nummer" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/telefonnummern/rdf#nummer" />
										    <!--<splitter class="tree-splitter"/>
										    <treecol id="tree-liste-telefonnummern-col-typ" label="Typ" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/telefonnummern/rdf#typ" />
										    -->
										</treecols>

										<template>
											<rule>
												<treechildren>
													<treeitem uri="rdf:*">
														<treerow>
															<treecell label="rdf:http://www.technikum-wien.at/telefonnummern/rdf#telefonnummer_id"   />
															<treecell label="rdf:http://www.technikum-wien.at/telefonnummern/rdf#name"   />
															<treecell label="rdf:http://www.technikum-wien.at/telefonnummern/rdf#nummer"   />
															<!--<treecell label="rdf:http://www.technikum-wien.at/telefonnummern/rdf#typ"   />-->
										 				</treerow>
										 			</treeitem>
										 		</treechildren>
										 	</rule>
									  	</template>

									</tree>
									<vbox>
										<button id="button-mitarbeiter-detail-telefonnummern-neu" disabled="true" label="Neu" oncommand="MitarbeiterDetailTelefonnummernNeu();"/>
										<button id="button-mitarbeiter-detail-telefonnummern-bearbeiten" disabled="true" label="Bearbeiten" oncommand="MitarbeiterDetailTelefonnummernBearbeiten();"/>
										<button id="button-mitarbeiter-detail-telefonnummern-loeschen" disabled="true" label="Löschen" oncommand="MitarbeiterDetailTelefonnummernLoeschen();"/>
									</vbox>
								</hbox>
							</groupbox>
							<groupbox hidden="false">
								<caption label="Bankverbindungen" />
								<hbox class="hbox-tree">
									<tree id="tree-liste-bankverbindungen" seltype="multi" hidecolumnpicker="false" flex="1"
											datasources="rdf:null"
											ref="http://www.technikum-wien.at/bankverbindungen/alle"
											onselect="" flags="dont-build-content"
											enableColumnDrag="true"
											>
										<treecols>
											<treecol id="tree-liste-bankverbindungen-col-bankverbindung_id" label="Bankverbindung_id" flex="1"  hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#bankverbindungen_id" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-name" label="Name" flex="2" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#name" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-anschrift" label="Anschrift" flex="2" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#anschrift" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-blz" label="BLZ" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#blz" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-bic" label="BIC" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#bic" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-kontonummer" label="Kontonummer" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#kontonummer" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-iban" label="IBAN" flex="1" hidden="true"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#iban" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-verrechnungskonto" label="Verrechnungskonto" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#verrechnungskonto" />
										    <splitter class="tree-splitter"/>
										    <treecol id="tree-liste-bankverbindungen-col-typ" label="Typ" flex="1" hidden="false"
										    	class="sortDirectionIndicator"
										    	sort="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#typ_name" />
										</treecols>

										<template>
											<rule>
												<treechildren>
													<treeitem uri="rdf:*">
														<treerow>
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#bankverbindung_id"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#name"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#anschrift"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#blz"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#bic"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#kontonummer"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#iban"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#verrechnungskonto"   />
															<treecell label="rdf:http://www.technikum-wien.at/bankverbindungen/rdf#typ_name"   />
										 				</treerow>
										 			</treeitem>
										 		</treechildren>
										 	</rule>
									  	</template>

									</tree>
									<vbox>
										<button id="button-mitarbeiter-detail-bankverbindungen-neu" disabled="true" label="Neu" oncommand="MitarbeiterDetailBankverbindungenNeu();"/>
										<button id="button-mitarbeiter-detail-bankverbindungen-bearbeiten" disabled="true" label="Bearbeiten" oncommand="MitarbeiterDetailBankverbindungenBearbeiten();"/>
										<button id="button-mitarbeiter-detail-bankverbindungen-loeschen" disabled="true" label="Löschen" oncommand="MitarbeiterDetailBankverbindungenLoeschen();"/>
									</vbox>
								</hbox>
							</groupbox>
							</vbox>

						</row>
					</rows>
				</grid>
			</vbox>
		</tabpanels>
	</tabbox>
</vbox>

</overlay>
