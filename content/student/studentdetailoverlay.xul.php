<?php
/* Copyright (C) 2006 fhcomplete.org
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
require_once('../../config/global.config.inc.php');
require_once('../../include/variable.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

$user=get_uid();

$variable = new variable();
if(!$variable->loadVariables($user))
{
	die('Fehler beim Laden der Variablen:'.$variable->errormsg);
}
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>
<!DOCTYPE overlay [
	<?php require('../../locale/'.$variable->variable->locale.'/fas.dtd'); ?>
]>
<overlay id="StudentDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Student DETAILS -->
<vbox id="student-detail" style="overflow:auto;margin:0px;" flex="1">
		<vbox hidden="true">
			<label value="Neu"/>
			<checkbox id="student-detail-checkbox-new" checked="true" />
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
							<label value="Person ID" control="student-detail-textbox-person_id"/>
							<hbox><textbox id="student-detail-textbox-person_id" readonly="true" maxlength="16" size="16"/></hbox>
							<label value="Zugangscode" control="student-detail-zugangscode"/>
							<label id="label-student-detail-link_bewerbungstool" hidden="true" value=""></label>
							<label class="text-link" href="#" id="label-student-detail-zugangscode" value="" onclick="window.open(document.getElementById('label-student-detail-link_bewerbungstool').value)"/>

						</row>
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
							<hbox><textbox id="student-detail-textbox-svnr" disabled="true" maxlength="16" size="10"/></hbox>
							<label value="Ersatzkennzeichen" control="student-detail-textbox-ersatzkennzeichen"/>
							<hbox><textbox id="student-detail-textbox-ersatzkennzeichen" disabled="true" maxlength="10" size="15"/></hbox>
							<label value="Geburtszeit" control="student-detail-textbox-geburtszeit" hidden="true"/>
							<hbox><textbox id="student-detail-textbox-geburtszeit" disabled="true" maxlength="5" size="5" tooltiptext="Format: hh:mm Beispiel: 10:30" hidden="true"/></hbox>
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
							<label value="Matrikelnummer" control="student-detail-textbox-matr_nr"/>
							<hbox><textbox id="student-detail-textbox-matr_nr" disabled="true" maxlength="32" size="15"/></hbox>
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
							<menulist id="student-detail-menulist-geschlecht" disabled="true"
								datasources="<?php echo APP_ROOT ?>rdf/geschlecht.rdf.php"
								ref="http://www.technikum-wien.at/geschlecht">
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/geschlecht/rdf#geschlecht"
											label="rdf:http://www.technikum-wien.at/geschlecht/rdf#bezeichnung"
											uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Familienstand" control="student-detail-menulist-familienstand"/>
							<menulist id="student-detail-menulist-familienstand" disabled="true">
								<menupopup>
										<menuitem value="" label="--keine Auswahl--"/>
										<menuitem value="g" label="geschieden"/>
										<menuitem value="l" label="ledig"/>
										<menuitem value="v" label="verheiratet"/>
										<menuitem value="w" label="verwitwet"/>
								</menupopup>
							</menulist>
							<label value="Anzahl der Kinder" control="student-detail-textbox-anzahlderkinder" hidden="true"/>
							<textbox id="student-detail-textbox-anzahlderkinder" disabled="true" maxlength="2" hidden="true"/>
						</row>
						<row>
							<vbox>
								<label value="Foto" />
								<hbox>
									<button id="student-detail-button-image-upload" label="Upload" oncommand="StudentImageUpload();" disabled="true"/>
									<button id="student-detail-button-image-delete" label="Delete" oncommand="StudentImageDelete();" disabled="true"/>
									<spacer flex="1" />
								</hbox>
								<button id="student-detail-button-image-infomail" label="Infomail" oncommand="StudentImageInfomail();" disabled="true"/>
							</vbox>
							<hbox>
								<image src='' id="student-detail-image" style="margin-left:5px; width:90px; height:120px"/> <!--height="60" width="60"-->
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

			<vbox flex="1" >
				<groupbox id="student-detail-groupbox-student">
				<caption label="StudentIn" />
				<grid id="student-detail-grid-student" style="margin:4px;">
						<columns >
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
								<hbox><label id="student-detail-label-matrikelnummer" value="Personenkennzeichen" control="student-detail-textbox-matrikelnummer"/></hbox>
								<hbox><textbox id="student-detail-textbox-matrikelnummer" readonly="true" maxlength="15" size="15"/></hbox>
								<textbox id="student-detail-menulist-studiengang_kz" disabled="true" hidden="true" />
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
							<row>
								<?php
								// Wenn Alias Erstellung deaktiviert ist dann ist das Feld readonly
								// Es sei den die Person hat die Rechte es zu aendern
								if(defined('GENERATE_ALIAS_STUDENT') && GENERATE_ALIAS_STUDENT===false)
								{
									$readonly='readonly="true"';
									$rechte = new benutzerberechtigung();
									$rechte->getBerechtigungen($user);
									if($rechte->isBerechtigt('student/alias'))
										$readonly='';
								}
								else
								{
									$readonly='';
								}
								 ?>
								<label value="Alias" control="student-detail-textbox-alias" />
								<textbox id="student-detail-textbox-alias" <?php echo $readonly;?> disabled="true" maxlength="256" />
							</row>
						</rows>
				</grid>
				</groupbox>

				<hbox>
					<spacer flex="1" />
					<button id="student-detail-button-save" label="Speichern" oncommand="StudentDetailSave();" disabled="true"/>
				</hbox>

			</vbox>

		</vbox>
</vbox>

<!-- STUDENT PREStudent -->
<vbox id="student-prestudent" style="overflow:auto; margin:0px;" flex="1">
<popupset>
	<menupopup id="student-prestudent-rolle-tree-popup">
		<menuitem label="Bearbeiten" oncommand="StudentRolleBearbeiten();" id="student-prestudent-rolle-tree-popup-edit" hidden="false"/>
		<menuitem label="Status bestaetigen" oncommand="StudentPrestudentRolleBestaetigen();" id="student-prestudent-rolle-tree-popup-approve" hidden="false"/>
		<menuitem label="Neuen Status hinzufuegen" oncommand="StudentRolleAdd();" id="student-prestudent-rolle-tree-popup-add" hidden="false"/>
		<menuitem label="Entfernen" oncommand="StudentPrestudentRolleDelete();" id="student-prestudent-rolle-tree-popup-delete" hidden="false"/>
		<menuitem label="Status vorrücken" oncommand="StudentPrestudentRolleVorruecken();" id="student-prestudent-rolle-tree-popup-move_forward" hidden="false"/>
	</menupopup>
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
					<columns>
						<column flex="1"/>
						<column flex="5"/>
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
									ref="http://www.technikum-wien.at/zgv/alle"
									style="min-width: 130px">
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
							<label value="ZGV Nation" control="student-prestudent-menulist-zgvnation"/>
							<hbox>
								<menulist id="student-prestudent-menulist-zgvnation" disabled="true"
										datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
										ref="http://www.technikum-wien.at/nation/liste"
										style="min-width: 100px">
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
													label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
													uri="rdf:*"/>
										</menupopup>
									</template>
								</menulist>
							</hbox>
						</row>
						<row>
							<label value="ZGV Master" control="student-prestudent-menulist-zgvmastercode"/>
							<menulist id="student-prestudent-menulist-zgvmastercode" disabled="true"
									datasources="<?php echo APP_ROOT ?>rdf/zgvmaster.rdf.php?optional=true" flex="1"
									ref="http://www.technikum-wien.at/zgvmaster/alle"
									style="min-width: 130px" >
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
							</hbox>
							<label value="ZGV Master Nation" control="student-prestudent-menulist-zgvmasternation"/>
							<hbox>
								<menulist id="student-prestudent-menulist-zgvmasternation" disabled="true"
										datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php?optional=true" flex="1"
										ref="http://www.technikum-wien.at/nation/liste"
										style="min-width: 100px">
									<template>
										<menupopup>
											<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
													label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
													uri="rdf:*"/>
										</menupopup>
									</template>
								</menulist>
							</hbox>
						</row>
					</rows>
				</grid>

			</groupbox>
			<groupbox id="student-detail-groupbox-prestudent">
		<caption label="PrestudentIn" />
		<grid id="student-prestudent-grid-prestudent" style="margin:4px;" flex="1">
					<columns>
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
									ref="http://www.technikum-wien.at/aufmerksamdurch/alle">
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/aufmerksamdurch/rdf#aufmerksamdurch_kurzbz"
												label="rdf:http://www.technikum-wien.at/aufmerksamdurch/rdf#beschreibung"
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
							<label value="Aufnahmeschluessel" control="student-prestudent-menulist-aufnahmeschluessel" hidden="true"/>
							<menulist id="student-prestudent-menulist-aufnahmeschluessel" disabled="true"
								datasources="<?php echo APP_ROOT ?>rdf/aufnahmeschluessel.rdf.php?optional=true" flex="1"
								ref="http://www.technikum-wien.at/aufnahmeschluessel/alle" hidden="true">
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/aufnahmeschluessel/rdf#aufnahmeschluessel"
											label="rdf:http://www.technikum-wien.at/aufnahmeschluessel/rdf#bezeichnung"
											uri="rdf:*" hidden="true"/>
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

							<label value="Studientyp" control="student-detail-menulist-gsstudientyp"/>
							<menulist id="student-detail-menulist-gsstudientyp" disabled="true"
									datasources="<?php echo APP_ROOT ?>rdf/gsstudientyp.rdf.php" flex="1"
									ref="http://www.technikum-wien.at/gsstudientyp" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/gsstudientyp/rdf#gsstudientyp_kurzbz"
												label="rdf:http://www.technikum-wien.at/gsstudientyp/rdf#bezeichnung"
												uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<label value="Facheinschlaegig berufstaetig" control="student-prestudent-checkbox-facheinschlberuf" hidden="true"/>
							<checkbox id="student-prestudent-checkbox-facheinschlberuf" checked="true" disabled="true" hidden="true"/>
						</row>
					</rows>
				</grid>
				<grid style="margin:4px;" flex="1">
					<columns>
						<column flex="1"/>
						<column flex="12"/>
						<column flex="3"/>
						<column flex="3"/>
					</columns>
					<rows>
						<row>
							<label value="Anmerkung" control="student-prestudent-textbox-anmerkung"/>
							<textbox id="student-prestudent-textbox-anmerkung" disabled="true"/>
							<hbox>
								<label value="Bismelden" control="student-prestudent-checkbox-bismelden"/>
								<checkbox id="student-prestudent-checkbox-bismelden" checked="true" disabled="true"/>
							</hbox>
							<hbox>
								<label value="Dual" control="student-prestudent-checkbox-dual"/>
								<checkbox id="student-prestudent-checkbox-dual" checked="false" disabled="true"/>
							</hbox>
							<hbox>
								<label value="Priorität" control="student-prestudent-textbox-priorisierung"/>
								<?php
									$readonly = 'readonly="true"';
									$rechte = new benutzerberechtigung();
									$rechte->getBerechtigungen($user);
									if($rechte->isBerechtigt('basis/prestudent'))
										$readonly = '';
								?>
								<textbox id="student-prestudent-textbox-priorisierung" disabled="true" <?php echo $readonly ?>/>
							</hbox>
						</row>
						<row id="student-prestudent-row-mentor">
						<label value="MentorIn" control="student-prestudent-textbox-mentor"/>
						<hbox><textbox id="student-prestudent-textbox-mentor" disabled="true" size="40" maxlength="256"/></hbox>
						</row>
					</rows>
				</grid>
			</groupbox>
			<hbox>
				<spacer flex="1" />
				<button id="student-prestudent-button-save" label="Speichern" oncommand="StudentPrestudentSave();" disabled="true"/>
			</hbox>
			<hbox flex="1">
				<groupbox id="student-detail-groupbox-rollen" flex="3">
				<caption label="Status" />
						<tree id="student-prestudent-tree-rolle" seltype="single" hidecolumnpicker="false" flex="1"
								datasources="rdf:null" ref="http://www.technikum-wien.at/prestudentrolle/liste"
								style="margin-left:10px;margin-right:10px;margin-bottom:5px;min-height: 200px" height="200px" enableColumnDrag="true"
								flags="dont-build-content"
								context="student-prestudent-rolle-tree-popup"
								ondblclick="StudentRolleBearbeiten()"
						>
							<treecols>
								<treecol id="student-prestudent-tree-rolle-status_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#status_kurzbz"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-studiensemester_kurzbz" label="StSem" flex="2" hidden="false" persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-ausbildungssemester" label="Sem" flex="1" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester"
									sorthints="integer"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-lehrverband" label="Lehrverband" flex="1" hidden="false" persist="hidden, width, ordinal"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#lehrverband" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-datum" label="Datum" flex="2" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-orgform_kurzbz" label="Organisationsform" flex="2" hidden="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#orgform_kurzbz" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-prestudent_id" label="PrestudentInID" flex="2" hidden="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#prestudent_id" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-studienplan_id" label="StudienplanID" flex="2" hidden="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studienplan_id" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-studienplan_bezeichnung" label="Studienplan" flex="2" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studienplan_bezeichnung" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-anmerkung" label="Anmerkung" flex="2" hidden="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#anmerkung" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-bestaetigt_von" label="BestaetigtVon" flex="1" hidden="true" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bestaetigt_von" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-bestaetigt_am" label="BestaetigtAm" flex="1" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bestaetigt_Am" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-bewerbung_abgeschicktamum" label="AbgeschicktAm" flex="1" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bewerbung_abgeschicktamum" />
								<splitter class="tree-splitter"/>
								<treecol id="student-prestudent-tree-rolle-statusgrund" label="Statusgrund" flex="1" hidden="false" persist="hidden, width, ordinal"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#statusgrund" />
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<rule>
									<treechildren flex="1" >
										<treeitem uri="rdf:*">
											<treerow>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#status_kurzbz"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studiensemester_kurzbz"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#ausbildungssemester"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#lehrverband"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#datum"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#orgform_kurzbz"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#prestudent_id"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studienplan_id"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#studienplan_bezeichnung"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#anmerkung"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bestaetigt_von"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bestaetigt_am"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#bewerbung_abgeschicktamum"/>
												<treecell label="rdf:http://www.technikum-wien.at/prestudentrolle/rdf#statusgrund"/>
											</treerow>
										</treeitem>
									</treechildren>
								</rule>
							</template>
						</tree>
				</groupbox>
				<groupbox id="student-detail-groupbox-historie" flex="2">
					<caption label="Gesamthistorie" />
					<vbox flex="1">
						<tree id="historie-tree" seltype="single" hidecolumnpicker="true" flex="1"
							  datasources="rdf:null" ref="http://www.technikum-wien.at/prestudenthistorie/liste"
							  style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100px"
							  persist="hidden, height"
							  context="historie-tree-popup"
						>
							<treecols>
								<treecol id="historie-treecol-studiensemester_kurzbz" label="StSem" flex="1" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studiensemester_kurzbz" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-prioritaet" label="Prio" flex="1" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#prioritaet" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-studiengang" label="Stg" flex="1" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studiengang" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-orgform_kurzbz" label="Orgform" flex="1" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#orgform_kurzbz" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-studienplan_bezeichnung" label="Studienplan" flex="3" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studienplan_bezeichnung" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<!--<treecol id="historie-treecol-reihung_absolviert" label="Reihung absolviert" flex="2" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#reihung_absolviert" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>-->
								<treecol id="historie-treecol-uid" label="UID" flex="2" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#uid" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-status" label="Status" flex="4" hidden="false"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#status" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
								<treecol id="historie-treecol-prestudent_id" label="PrestudentID" flex="1" hidden="true"
										 class="sortDirectionIndicator"
										 sort="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#prestudent_id" onclick="historieTreeSort()"/>
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<rule>
									<treechildren>
										<treeitem uri="rdf:*">
											<treerow>
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studiensemester_kurzbz" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#prioritaet" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studiengang" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#orgform_kurzbz" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#studienplan_bezeichnung" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#uid" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#status" />
												<treecell properties="Aktiv_rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#aktiv rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#bold" label="rdf:http://www.technikum-wien.at/prestudenthistorie/rdf#prestudent_id" />
											</treerow>
										</treeitem>
									</treechildren>
								</rule>
							</template>
						</tree>
					</vbox>
				</groupbox>
			</hbox>
</vbox>

</overlay>
