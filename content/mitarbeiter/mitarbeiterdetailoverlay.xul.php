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
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>

<!DOCTYPE overlay>

<overlay id="MitarbeiterDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<vbox id="mitarbeiter-detail-stammdaten" flex="1" style="overflow:auto">
<textbox id="mitarbeiter-detail-textbox-person_id" hidden="true" />
		<groupbox id='groupbox-personendaten'>
		<!--PersonenDaten-->
			<caption label="Personendaten" />
		<grid align="end" flex="1"
				 flags="dont-build-content"
				enableColumnDrag="true"
				style="margin:4px;"
				>
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
									<label value="UID" control="mitarbeiter-detail-textbox-uid"/>
									<hbox><textbox id="mitarbeiter-detail-textbox-uid" disabled="true" maxlength="32" size="16" oninput="MitarbeiterDetailValueChange()"/></hbox>
									<spacer />
									<spacer />
									<label value="Aktiv" control="mitarbeiter-detail-checkbox-aktiv"/>
									<checkbox id="mitarbeiter-detail-checkbox-aktiv" checked="true" disabled="true" onchange="MitarbeiterDetailValueChange()"/>
							</row>
							<row>
									<label value="Anrede" control="mitarbeiter-detail-textbox-anrede"/>
									<hbox><textbox id="mitarbeiter-detail-textbox-anrede" disabled="true" maxlength="16" size="16" oninput="MitarbeiterDetailValueChange()"/></hbox>
									<label value="TitelPre" control="mitarbeiter-detail-textbox-titelpre"/>
									<textbox id="mitarbeiter-detail-textbox-titelpre" disabled="true" maxlength="64" oninput="MitarbeiterDetailValueChange()"/>
									<label value="TitelPost" control="mitarbeiter-detail-textbox-titelpost"/>
									<textbox id="mitarbeiter-detail-textbox-titelpost" disabled="true" maxlength="32" oninput="MitarbeiterDetailValueChange()"/>
							</row>
							<row>
								<label value="Nachname" control="mitarbeiter-detail-textbox-nachname"/>
									<textbox id="mitarbeiter-detail-textbox-nachname" disabled="true" maxlength="64" oninput="MitarbeiterDetailValueChange()"/>
									<label value="Vorname" control="mitarbeiter-detail-textbox-vorname"/>
									<textbox id="mitarbeiter-detail-textbox-vorname" disabled="true" maxlength="32" oninput="MitarbeiterDetailValueChange()"/>
									<label value="Vornamen" control="mitarbeiter-detail-textbox-vornamen"/>
									<textbox id="mitarbeiter-detail-textbox-vornamen" disabled="true" maxlength="128" oninput="MitarbeiterDetailValueChange()"/>
							</row>
							<row>
								<label value="Wahlname" control="mitarbeiter-detail-textbox-wahlname"/>
								<textbox id="mitarbeiter-detail-textbox-wahlname" disabled="true" maxlength="128" oninput="MitarbeiterDetailValueChange()"/>
							</row>
							<row <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?>>
									<label value="Geburtsdatum" control="mitarbeiter-detail-textbox-geburtsdatum" />
									<hbox>
										<box class="Datum" id="mitarbeiter-detail-textbox-geburtsdatum" disabled="true" oninput="MitarbeiterDetailValueChange()" />
									</hbox>
									<label value="Geburtsort" control="mitarbeiter-detail-textbox-geburtsort"/>
									<textbox id="mitarbeiter-detail-textbox-geburtsort" disabled="true" maxlength="128" oninput="MitarbeiterDetailValueChange()"/>
									<label value="Geburtszeit" control="mitarbeiter-detail-textbox-geburtszeit" hidden="true"/>
									<hbox><textbox id="mitarbeiter-detail-textbox-geburtszeit" disabled="true" maxlength="5" size="5" tooltiptext="Format: hh:mm Beispiel: 10:30" oninput="MitarbeiterDetailValueChange()" hidden="true"/></hbox>
							</row>
							<row <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?>>
									<label value="SVNR" control="mitarbeiter-detail-textbox-svnr"/>
									<hbox><textbox id="mitarbeiter-detail-textbox-svnr" disabled="true" maxlength="16" size="10" oninput="MitarbeiterDetailValueChange()"/></hbox><!--  oninput="MitarbeiterGenerateGebDatFromSVNR(); fuer automatisches eintragen der SVNR anhand des Geburtsdatums -->
									<label value="Ersatzkennzeichen" control="mitarbeiter-detail-textbox-ersatzkennzeichen"/>
									<hbox><textbox id="mitarbeiter-detail-textbox-ersatzkennzeichen" disabled="true" maxlength="10" size="10" oninput="MitarbeiterDetailValueChange()"/></hbox>
							</row>
				<row>
							<label value="Staatsbuergerschaft" control="mitarbeiter-detail-menulist-staatsbuergerschaft" <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?>/>
							<menulist id="mitarbeiter-detail-menulist-staatsbuergerschaft" disabled="true"
								datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
								ref="http://www.technikum-wien.at/nation/liste"  oncommand="MitarbeiterDetailValueChange()"
								<?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?> >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
											label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
											uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Geburtsnation" control="mitarbeiter-detail-menulist-geburtsnation" <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?> />
							<menulist id="mitarbeiter-detail-menulist-geburtsnation" disabled="true"
								datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
								ref="http://www.technikum-wien.at/nation/liste"  oncommand="MitarbeiterDetailValueChange()"
								<?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?> >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
											label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
											uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Sprache" control="mitarbeiter-detail-menulist-sprache" />
							<menulist id="mitarbeiter-detail-menulist-sprache" disabled="true"
								datasources="<?php echo APP_ROOT ?>rdf/sprache.rdf.php?optional=true" flex="1"
								ref="http://www.technikum-wien.at/sprachen/liste"  oncommand="MitarbeiterDetailValueChange()">
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
											label="rdf:http://www.technikum-wien.at/sprachen/rdf#anzeigename"
											uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
						</row>
							<row>
									<label value="Geschlecht" control="mitarbeiter-detail-menulist-geschlecht"/>
									<menulist id="mitarbeiter-detail-menulist-geschlecht" disabled="true"
										datasources="<?php echo APP_ROOT ?>rdf/geschlecht.rdf.php"
										ref="http://www.technikum-wien.at/geschlecht"
										oncommand="MitarbeiterDetailValueChange()">
										<template>
											<menupopup>
												<menuitem value="rdf:http://www.technikum-wien.at/geschlecht/rdf#geschlecht"
													label="rdf:http://www.technikum-wien.at/geschlecht/rdf#bezeichnung"
													uri="rdf:*"/>
												</menupopup>
										</template>
									</menulist>
							<label value="Familienstand" control="mitarbeiter-detail-menulist-familienstand" <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?> />
									<menulist id="mitarbeiter-detail-menulist-familienstand" disabled="true" oncommand="MitarbeiterDetailValueChange()" <?php echo ($rechte->isBerechtigt('mitarbeiter/persoenlich'))?'':'hidden="true"'; ?> >
								<menupopup>
										<menuitem value="" label="--keine Auswahl--"/>
										<menuitem value="g" label="geschieden"/>
										<menuitem value="l" label="ledig"/>
										<menuitem value="v" label="verheiratet"/>
										<menuitem value="w" label="verwitwet"/>
								</menupopup>
							</menulist>
							<label value="Anzahl der Kinder" control="mitarbeiter-detail-textbox-anzahlderkinder" hidden="true"/>
									<textbox id="mitarbeiter-detail-textbox-anzahlderkinder" disabled="true" maxlength="2" oninput="MitarbeiterDetailValueChange()" hidden="true"/>
							</row>
							<row>
									<vbox>
										<label value="Foto" />
										<hbox>
											<button id="mitarbeiter-detail-button-image-upload" label="Upload" oncommand="MitarbeiterImageUpload();" disabled="true"/>
											<button id="mitarbeiter-detail-button-image-delete" label="Delete" oncommand="MitarbeiterImageDelete();" disabled="true"/>
											<spacer flex="1" />
										</hbox>
										<button id="mitarbeiter-detail-button-image-infomail" label="Infomail" oncommand="MitarbeiterImageInfomail();" disabled="true"/>
									</vbox>
								<hbox>
									<image src='' id="mitarbeiter-detail-image" style="margin-left:5px; width:90px; height:120px"/> <!-- width="60" height="60" -->
									<spacer flex="1"/>
								</hbox>
									<label value="Anmerkung" control="mitarbeiter-detail-textbox-anmerkung"/>
									<textbox id="mitarbeiter-detail-textbox-anmerkung" disabled="true" multiline="true" oninput="MitarbeiterDetailValueChange()"/>
									<label value="Homepage" control="mitarbeiter-detail-textbox-homepage"/>
									<vbox><textbox id="mitarbeiter-detail-textbox-homepage" disabled="true" maxlength="256" oninput="MitarbeiterDetailValueChange()"/></vbox>
							</row>
				</rows>
			</grid>

		</groupbox>

		<!-- MITARBEITER DATEN -->

	<hbox>
		<groupbox flex="8">
			<caption label="MitarbeiterInnendaten" />
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
					<label align="end" control="mitarbeiter-detail-textbox-personalnummer" value="Personalnummer"/>
		    		<textbox id="mitarbeiter-detail-textbox-personalnummer" size="10" maxlength="10" disabled="true" oninput="MitarbeiterDetailValueChange()"/>
		    		<label align="end" control="mitarbeiter-detail-textbox-kurzbezeichnung" value="Kurzbezeichnung"/>
					<hbox>
						<textbox id="mitarbeiter-detail-textbox-kurzbezeichnung"  class="pflichtfeld" size="10" maxlength="10" disabled="true" oninput="MitarbeiterDetailValueChange()"/>
						<spacer />
					</hbox>
					<checkbox label="LektorIn" id="mitarbeiter-detail-checkbox-lektor" checked="false" disabled="true" onchange="MitarbeiterDetailValueChange()"/>
		    		<checkbox label="Kleriker" id="mitarbeiter-detail-checkbox-kleriker" checked="false" disabled="true" hidden="true" onchange="MitarbeiterDetailValueChange()"/>
		    	</row>
		    	<row>
		    	    <label align="end" control="mitarbeiter-detail-textbox-stundensatz" value="Stundensatz"/>
		    		<textbox id="mitarbeiter-detail-textbox-stundensatz" size="10" maxlength="10" disabled="true" oninput="MitarbeiterDetailValueChange()"/>
		    		<label align="end" control="mitarbeiter-detail-textbox-telefonklappe" value="Telefonklappe"/>
		    		<hbox>
	    				<textbox id="mitarbeiter-detail-textbox-telefonklappe" size="10" maxlength="10" disabled="true" oninput="MitarbeiterDetailValueChange()"/>
	    				<spacer />
	    			</hbox>
					<checkbox label="Fixangestellt" id="mitarbeiter-detail-checkbox-fixangestellt" checked="false" disabled="true" onchange="MitarbeiterDetailValueChange()"/>
					<spacer />
		    	</row>
		    	<row>
				    <label align="end" control="mitarbeiter-detail-menulist-ort_kurzbz" value="Buero"/>
				    <vbox>
					 	<menulist id="mitarbeiter-detail-menulist-ort_kurzbz" disabled="true"
					              datasources="<?php echo APP_ROOT; ?>rdf/orte.rdf.php?optional=true"
						          ref="http://www.technikum-wien.at/ort/liste" oncommand="MitarbeiterDetailValueChange()">
						    <template>
						       <menupopup>
							      <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/ort/rdf#anzeigename"
							                value="rdf:http://www.technikum-wien.at/ort/rdf#ort_kurzbz"/>
							      </menupopup>
							</template>
						</menulist>
						<spacer flex="1"/>
					</vbox>
					<label align="end" control="mitarbeiter-detail-menulist-standort" value="Standort"/>
					<vbox>
					 	<menulist id="mitarbeiter-detail-menulist-standort" disabled="true"
					              datasources="<?php echo APP_ROOT; ?>rdf/standort.rdf.php?optional=true&amp;firmentyp_kurzbz=Intern"
						          ref="http://www.technikum-wien.at/standort/liste" oncommand="MitarbeiterDetailValueChange()">
						    <template>
						       <menupopup>
							      <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/standort/rdf#bezeichnung"
							                            value="rdf:http://www.technikum-wien.at/standort/rdf#standort_id"/>
							      </menupopup>
							</template>
						</menulist>
						<spacer flex="1"/>
					</vbox>
					<checkbox label="Bismelden" id="mitarbeiter-detail-checkbox-bismelden" checked="false" disabled="true" onchange="MitarbeiterDetailValueChange()"/>
					<spacer />
		    	</row>
		    	<row>
		    		<label value="Anmerkung" control="mitarbeiter-detail-textbox-mitarbeiteranmerkung"/>
      				<textbox id="mitarbeiter-detail-textbox-mitarbeiteranmerkung" disabled="true" multiline="true" oninput="MitarbeiterDetailValueChange()"/>
      				<vbox>
      					<label value="Alias" control="mitarbeiter-detail-textbox-alias" oninput="MitarbeiterDetailValueChange()"/>
      				</vbox>
      				<vbox>
      					<textbox id="mitarbeiter-detail-textbox-alias" disabled="true" oninput="MitarbeiterDetailValueChange()"/>
      				</vbox>
      				<vbox>
      					<label align="end" control="mitarbeiter-detail-menulist-ausbildung" value="Ausbildung"/>
      				</vbox>
      				<vbox>
				 	<menulist id="mitarbeiter-detail-menulist-ausbildung" disabled="true"
				              datasources="<?php echo APP_ROOT; ?>rdf/ausbildung.rdf.php?optional=true"
					          ref="http://www.technikum-wien.at/ausbildung/alle" oncommand="MitarbeiterDetailValueChange()">
					    <template>
					       <menupopup>
						      <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/ausbildung/rdf#bezeichnung"
						                value="rdf:http://www.technikum-wien.at/ausbildung/rdf#code"/>
						      </menupopup>
						</template>
					</menulist>
				</vbox>
			</row>
			</rows>
		</grid>
		<hbox class="style-groupbox">

		</hbox>
		</groupbox>
	</hbox>
	<hbox>

		<spacer flex="1"/>
		<button id="mitarbeiter-detail-button-speichern" disabled="true" label="Speichern" oncommand="MitarbeiterSave();"/>
	</hbox>
</vbox>
</overlay>
