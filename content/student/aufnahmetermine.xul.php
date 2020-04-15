<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/variable.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php');

$user=get_uid();
$variable = new variable();
if(!$variable->loadVariables($user))
{
	die('Fehler beim Laden der Variablen:'.$variable->errormsg);
}

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

$prestudent_id = filter_input(INPUT_GET,'prestudent_id');

if(!defined('FAS_REIHUNGSTEST_PUNKTEUEBERNAHME') || FAS_REIHUNGSTEST_PUNKTEUEBERNAHME == true)
	$rt_uebernahme = true;
else
	$rt_uebernahme = false;
echo '
<!DOCTYPE overlay [';
require('../../locale/'.$variable->variable->locale.'/fas.dtd');
echo ']>
';
?>

<window id="aufnahmetermine-window" title="aufnahmetermine"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	onload="loadAufnahmeTermine(<?php echo "'".$prestudent_id."'"; ?>);"
>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/aufnahmetermine.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox flex="1">
<popupset>
	<menupopup id="aufnahmetermine-tree-popup">
		<menuitem label="Entfernen" oncommand="aufnahmetermineDelete();" id="aufnahmetermine-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<groupbox>
<caption label="Studiengang" />
<hbox style="padding-top: 10px">
	<?php
	if(!defined('FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN') || FAS_REIHUNGSTEST_AUFNAHMEGRUPPEN==true)
		$aufnahmegruppe_visibility='';
	else
		$aufnahmegruppe_visibility='hidden="true"';

	echo '<hbox '.$aufnahmegruppe_visibility.'>';
	?>
	<label value="Gruppe" control="aufnahmetermine-menulist-aufnahmegruppe"/>
	<menulist id="aufnahmetermine-menulist-aufnahmegruppe" disabled="false"
			datasources="rdf:null"
			ref="http://www.technikum-wien.at/gruppen/liste">
		<template>
			<menupopup>
				<menuitem value="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_kurzbz"
					label="rdf:http://www.technikum-wien.at/gruppen/rdf#bezeichnung"
					uri="rdf:*"/>
			</menupopup>
		</template>
	</menulist>
	</hbox>
	<label value="&aufnahmetermine-reihungstest.absolviert;" control="aufnahmetermine-checkbox-reihungstestangetreten"/>
	<checkbox id="aufnahmetermine-checkbox-reihungstestangetreten" checked="true"/>
	<label value="Gesamtpunkte" control="aufnahmetermine-textbox-gesamtpunkte"/>
	<textbox id="aufnahmetermine-textbox-gesamtpunkte" disabled="true" maxlength="8" size="8"/>
	<button id="aufnahmetermine-button-savegesamtpunkte" disabled="true" label="Speichern" oncommand="AufnahmeTermineSaveGesamtpunkte();"/>
	<button id="aufnahmetermine-button-calculatetotal" disabled="true" label="Gesamtpunkte berechnen" oncommand="AufnahmeTermineCalculateTotal();"/>
</hbox>
</groupbox>
<groupbox>
<caption label="Allgemein" />
<hbox flex="1">
<grid id="aufnahmetermine-grid-detail" style="overflow:auto;margin:4px;" flex="1">
			<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="aufnahmetermine-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/aufnahmetermine"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						onselect="AufnahmeTermineAuswahl()"
						context="aufnahmetermine-tree-popup"
						flags="dont-build-content"
					>

						<treecols>
							<treecol id="aufnahmetermine-tree-datum" label="Datum" flex="3" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#datum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-stufe" label="Stufe" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sortActive="true"
   								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#stufe"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-studiensemester" label="Studiensemester" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studiensemester"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-anmerkung" label="Anmerkung" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmerkung"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-anmeldedatum" label="Anmeldedatum" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmeldedatum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-ort" label="Ort" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#ort" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-punkte" label="Punkte" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#punkte" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-teilgenommen" label="Teilgen." flex="1" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#teilgenommen" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-ort_kurzbz" label="Ort" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#ort_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-rt_id" label="ReihungstestID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#rt_id" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-person_id" label="PersonID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#person_id" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-rt_person_id" label="RTPersonID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#rt_person_id" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-studienplan_bezeichnung" label="Studienplan" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_bezeichnung" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-studienplan_id" label="StudienplanID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_id" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-studienplan_studiengang" label="Stg" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_studiengang" />
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-datum_iso" label="DatumISO" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#datum_iso"/>
							<splitter class="tree-splitter"/>
							<treecol id="aufnahmetermine-tree-anmeldedatum_iso" label="AnmeldedatumISO" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmeldedatum_iso"/>
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow  properties="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#properties">
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#datum"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#stufe"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studiensemester"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmerkung"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmeldedatum"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#ort_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#punkte"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#teilgenommen"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#ort_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#rt_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#person_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#rt_person_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_bezeichnung"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#studienplan_studiengang"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#datum_iso"/>
										<treecell label="rdf:http://www.technikum-wien.at/aufnahmetermine/rdf#anmeldedatum_iso"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="aufnahmetermine-button-neu" label="Neu" oncommand="AufnahmeTermineNeu();"/>
							<button id="aufnahmetermine-button-loeschen" label="Loeschen" oncommand="AufnahmeTermineDelete();"/>
						</hbox>
						<vbox hidden="true">
							<label value="person_id" control="aufnahmetermine-textbox-person_id"/>
							<textbox id="aufnahmetermine-textbox-person_id" disabled="true"/>
                            <label value="studienplan_studiengang_kz" control="aufnahmetermine-textbox-studienplan_studiengang_kz"/>
                            <textbox id="aufnahmetermine-textbox-studienplan_studiengang_kz" disabled="true"/>
							<label value="Neu" control="aufnahmetermine-checkbox-neu"/>
							<checkbox id="aufnahmetermine-checkbox-neu" disabled="true" checked="false"/>
							<label value="rt_person_id" control="aufnahmetermine-textbox-rt_person_id"/>
							<textbox id="aufnahmetermine-textbox-rt_person_id" disabled="true"/>
						</vbox>
						<groupbox id="aufnahmetermine-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="aufnahmetermine-grid-detail" style="overflow:auto;margin:4px;" flex="1">
								<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Reihungstest / Interview" control="aufnahmetermine-menulist-reihungstest"/>
										<hbox>
										<menulist id="aufnahmetermine-menulist-reihungstest" disabled="true"
												datasources="rdf:null" flex="1" style="width:200px"
												ref="http://www.technikum-wien.at/reihungstest/alle">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/reihungstest/rdf#reihungstest_id"
												 		label="rdf:http://www.technikum-wien.at/reihungstest/rdf#bezeichnung"
														tooltiptext="rdf:http://www.technikum-wien.at/reihungstest/rdf#bezeichnung"
														uri="rdf:*"/>
												</menupopup>
											</template>
										</menulist>
										<toolbarbutton id="aufnahmetermine-button-reihungstest-refresh" image="../../skin/images/refresh.png" tooltiptext="Alle zukünftigen Reihungstests des Studiengangs laden" onclick="AufnahmeTermineReihungstestDropDownRefresh()"/>
										<toolbarbutton id="aufnahmetermine-button-reihungstest-open" image="../../skin/images/edit.png" tooltiptext="Zur Reihungstestverwaltung" onclick="AufnahmeTermineReihungstestEdit()"/>
										<spacer flex="1"/>
										</hbox>
									</row>
									<row>
										<label value="&tab-prestudent-aufnahme.anmeldung;" control="aufnahmetermine-textbox-anmeldungreihungstest"/>
										<hbox>
											<box class="Datum" id="aufnahmetermine-textbox-anmeldungreihungstest" disabled="true"/>
											<button id="aufnahmetermine-button-anmeldungreihungstest-heute" label="Heute" oncommand="AufnahmeTermineAnmeldungreihungstestHeute()" disabled="true" style="margin:0px;"/>
										</hbox>
									</row>
									<row>
										<label value="&tab-prestudent-aufnahme.absolviert;" control="aufnahmetermine-checkbox-teilgenommen"/>
										<checkbox id="aufnahmetermine-checkbox-teilgenommen" checked="true" disabled="true"/>
									</row>
									<row>
										<label value="Reihungstest Studienplan" control="aufnahmetermine-menulist-studienplan"/>
										<hbox>
										<menulist id="aufnahmetermine-menulist-studienplan" disabled="true"
												datasources="rdf:null" flex="1"
												ref="http://www.technikum-wien.at/studienplan">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/studienplan/rdf#studienplan_id"
														label="rdf:http://www.technikum-wien.at/studienplan/rdf#bezeichnung"
														uri="rdf:*"/>
												</menupopup>
											</template>
										</menulist>
										<spacer flex="1"/>
										</hbox>
									</row>
									<row>
										<label value="Punkte" control="aufnahmetermine-textbox-punkte" />
										<hbox>
											<textbox id="aufnahmetermine-textbox-punkte" disabled="true" maxlength="8" size="6"/>
											<toolbarbutton
												id="aufnahmetermine-button-reihungstest-punktesync"
												<?php
												if(!$rt_uebernahme)
													echo 'hidden="true"';
												?>
												image="../../skin/images/transmit.png"
												tooltiptext="Reihungstest Ergebnis holen"
												onclick="AufnahemTermineReihungstestPunkteTransmit()"/>
											<spacer flex="1" />
										</hbox>
                                    </row>
                                    <groupbox id="aufnahmetermine-groupbox-vergleich-endpunkte" hidden="true">
                                        <caption label="Vergleichswerte Reihungstestpunkte (Basisgebiete)"></caption>
                                        <vbox style="padding: 10px;">
                                            <spacer resize='none' height='10' flex="1"/>
                                            <row>
                                                <label value="Reihungstestpunkte (inkl. Physik)" control="aufnahmetermine-textbox-endpunkte-inkl-gebiete" style="margin-right: 7px;"/>
                                                <hbox>
                                                    <textbox id="aufnahmetermine-textbox-endpunkte-inkl-gebiete" readonly="true" maxlength="8" size="6" flex="1"/>
													<toolbarbutton image="../../skin/images/up.png" tooltiptext="Als Punkte setzen" onclick="setEndpunkteAsPunkte('aufnahmetermine-textbox-endpunkte-inkl-gebiete')"/>
                                                </hbox>
                                            </row>
                                            <row>
                                                <label value="Reihungstestpunkte (exkl. Physik)" control="aufnahmetermine-textbox-endpunkte-exkl-gebiete" />
                                                <hbox>
                                                   <textbox id="aufnahmetermine-textbox-endpunkte-exkl-gebiete" readonly="true" maxlength="8" size="6" flex="1"/>
													<toolbarbutton image="../../skin/images/up.png" tooltiptext="Als Punkte setzen" onclick="setEndpunkteAsPunkte('aufnahmetermine-textbox-endpunkte-exkl-gebiete')"/>
                                                </hbox>
                                            </row>
                                        </vbox>
                                    </groupbox>
								</rows>
							</grid>
							<hbox>
								<button id="aufnahmetermine-button-speichern" oncommand="AufnahmeTermineSpeichern()" label="Speichern" disabled="true"/>
								<spacer flex="1" />
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>

</hbox>
</groupbox>
<spacer flex="1" />
</vbox>
</window>
