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
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';


echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	die('Parameter person_id muss uebergeben werden');

if(isset($_GET['uid']))
{
	$basis = new basis();
	$uid = $basis->convert_html_chars($_GET['uid']);
}
else
	$uid='';
?>

<window id="Betriebsmittel"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	 onload="loadBetriebsmittel(<?php echo $person_id.",'".$uid."'"; ?>);"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/betriebsmitteloverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />


<vbox id="betriebsmittel" style="margin:0px;" flex="1">
<popupset>
	<menupopup id="betriebsmittel-tree-popup">
		<menuitem label="Entfernen" oncommand="BetriebsmittelDelete();" id="betriebsmittel-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="betriebsmittel-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="betriebsmittel-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/betriebsmittel/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						onselect="BetriebsmittelAuswahl()"
						context="betriebsmittel-tree-popup"
						flags="dont-build-content"
					>

						<treecols>
							<treecol id="betriebsmittel-tree-nummer" label="Nummer" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#nummer"/>
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-betriebsmitteltyp" label="Typ" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmitteltyp"/>
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-anmerkung" label="Anmerkung" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#anmerkung" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-kaution" label="Kaution" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#kaution" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-ausgegebenam" label="Ausgabedatum" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/konto/rdf#ausgegebenam_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-retouram" label="Retourdatum" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#retouram_iso" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-betriebsmittel_id" label="Betriebsmittel_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-person_id" label="Person_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#person_id" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-betriebsmittelperson_id" label="Betriebsmittlperson_id" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittelperson_id" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-beschreibung" label="Beschreibung" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung" />
							<splitter class="tree-splitter"/>
							<treecol id="betriebsmittel-tree-uid" label="UID" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#uid" />
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#nummer"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmitteltyp"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#anmerkung"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#kaution"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#ausgegebenam"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#retouram"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#person_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittelperson_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung"/>
										<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#uid"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="betriebsmittel-button-neu" label="Neu" oncommand="BetriebsmittelNeu();"/>
							<button id="betriebsmittel-button-loeschen" label="Loeschen" oncommand="BetriebsmittelDelete();"/>
							<spacer flex="1"/>
							<button id="betriebsmittel-button-uebernahmebestaetigung" label="Übernahmebestätigung" oncommand="BetriebsmittelPrintUebernahmebestaetigung();"/>
						</hbox>
						<vbox hidden="true">
							<label value="betriebsmittel_id" control="betriebsmittel-textbox-betriebsmittel_id"/>
							<textbox id="betriebsmittel-textbox-betriebsmittel_id" disabled="true"/>
							<label value="person_id" control="betriebsmittel-textbox-person_id"/>
							<textbox id="betriebsmittel-textbox-person_id" disabled="true"/>
							<label value="betriebsmittelperson_id" control="betriebsmittel-textbox-betriebsmittelperson_id"/>
							<textbox id="betriebsmittel-textbox-betriebsmittelperson_id" disabled="true"/>
							<label value="Neu" control="betriebsmittel-checkbox-neu"/>
							<checkbox id="betriebsmittel-checkbox-neu" disabled="true" checked="false"/>
						</vbox>
						<groupbox id="betriebsmittel-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="betriebsmittel-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Typ" control="betriebsmittel-menulist-betriebsmitteltyp"/>
										<menulist id="betriebsmittel-menulist-betriebsmitteltyp"
											disabled="true" flex="1"
											datasources="<?php echo APP_ROOT ?>rdf/betriebsmitteltyp.rdf.php"
											ref="http://www.technikum-wien.at/betriebsmitteltyp/liste"
											oncommand="BetriebsmittelTypChange();">
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/betriebsmitteltyp/rdf#betriebsmitteltyp"
										        		      label="rdf:http://www.technikum-wien.at/betriebsmitteltyp/rdf#beschreibung"
													  		  uri="rdf:*"/>
												</menupopup>
											</template>
										</menulist>
									</row>
									<row id="betriebsmittel-row-nummer">
										<label value="Nummer" control="betriebsmittel-textbox-nummer"/>
										<hbox>
											<textbox id="betriebsmittel-textbox-nummerold" hidden="true"/>
					      					<textbox id="betriebsmittel-textbox-nummer" disabled="true" maxlength="32"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row id="betriebsmittel-row-nummer2">
										<label value="Nummer 2" control="betriebsmittel-textbox-nummer2"/>
										<hbox>
					      					<textbox id="betriebsmittel-textbox-nummer2" disabled="true" maxlength="12"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row id="betriebsmittel-row-beschreibung">
										<label value="Beschreibung" control="betriebsmittel-textbox-beschreibung"/>
				      					<textbox id="betriebsmittel-textbox-beschreibung" disabled="true" multiline="true"/>
					      			</row>
					      			<row id="betriebsmittel-row-inventarnummer" hidden="true">
										<label value="Inventarnummer" control="betriebsmittel-menulist-inventarnummer"/>
				      					<menulist id="betriebsmittel-menulist-inventarnummer"
										  editable="true" disabled="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/betriebsmittel/liste"
								          oninput="BetriebsmittelMenulistInventarLoad(this)"
								          >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"
										        		      label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#inventarnummer rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung"
													  		  uri="rdf:*"/>
												</menupopup>
											</template>
										</menulist>
					      			</row>
					      			<row>
										<label value="Kaution" control="betriebsmittel-textbox-kaution"/>
										<hbox>
					      					<textbox id="betriebsmittel-textbox-kaution" disabled="true" maxlength="7"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Anmerkung" control="betriebsmittel-textbox-anmerkung"/>
				      					<textbox id="betriebsmittel-textbox-anmerkung" disabled="true" multiline="true"/>
					      			</row>
					      			<row>
										<label value="Ausgegeben am" control="betriebsmittel-textbox-ausgegebenam"/>
										<hbox>
											<box class="Datum" id="betriebsmittel-textbox-ausgegebenam" disabled="true"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Retour am" control="betriebsmittel-textbox-retouram"/>
										<hbox>
											<box class="Datum" id="betriebsmittel-textbox-retouram" disabled="true"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="betriebsmittel-button-speichern" oncommand="BetriebsmittelDetailSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>

</hbox>
<spacer flex="1" />
</vbox>
</window>
