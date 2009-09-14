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

include('../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	die('Parameter uid muss uebergeben werden');

?>

<window id="funktionen-window" title="Funktionen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadFunktionen('<?php echo $uid; ?>');"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/funktionen.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<hbox flex="1">
 	<!-- FUNKTIONEN -->
	<tree id="funktion-tree" seltype="single" hidecolumnpicker="false" flex="2"
			datasources="rdf:null" ref="http://www.technikum-wien.at/bnfunktion/liste"
			onclick="FunktionBearbeiten()"
			flags="dont-build-content"
			enableColumnDrag="true"
			style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100"
			persist="hidden, height"
		>
		<treecols>
			<treecol id="funktion-treecol-funktion" label="Funktion" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#funktion" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-oe_kurzbz" label="Organisationseinheit" flex="1" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#studiengang" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-semester" label="Semester" flex="1" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#semester" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-fachbereich" label="Institut" flex="1" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#fachbereich_kurzbz" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-uid" label="uid" flex="1" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#uid" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-benutzerfunktion_id" label="BenutzerfunktionID" flex="1" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#benutzerfunktion_id" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-studiengang_kz" label="StudiengangKZ" flex="1" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#studiengang_kz" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-funktion_kurzbz" label="FunktionKurzBz" flex="1" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#funktion_kurzbz" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-datum_von" label="GueltigVon" flex="1" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#datum_von_iso" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="funktion-treecol-datum_bis" label="GueltigBis" flex="1" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/bnfunktion/rdf#datum_bis_iso" onclick="FunktionTreeSort()"/>
			<splitter class="tree-splitter"/>
		</treecols>
	
		<template>
			<rule>
				<treechildren>
					<treeitem uri="rdf:*">
						<treerow>
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#funktion" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#organisationseinheit" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#semester" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#fachbereich_kurzbz" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#uid" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#benutzerfunktion_id" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#studiengang_kz" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#funktion_kurzbz" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#datum_von" />
							<treecell label="rdf:http://www.technikum-wien.at/bnfunktion/rdf#datum_bis" />
						</treerow>
					</treeitem>
				</treechildren>
			</rule>
		</template>
	</tree>

	<vbox flex="1">
		<hbox>
			<button id="funktion-button-neu" label="Neu" oncommand="FunktionNeu();" disabled="true"/>
			<button id="funktion-button-loeschen" label="Loeschen" oncommand="FunktionDelete();" disabled="true"/>
		</hbox>
		<vbox hidden="true">
			<label value="benutzerfunktion_id" control="funktion-textbox-benutzerfunktion_id"/>
			<textbox id="funktion-textbox-benutzerfunktion_id" disabled="true"/>
			<label value="Neu" control="funktion-checkbox-neu"/>
			<checkbox id="funktion-checkbox-neu" disabled="true" checked="false"/>
		</vbox>
		<groupbox id="funktion-groupbox" >
		<caption label="Details"/>
			<grid id="funktion-grid-detail" style="overflow:auto;margin:4px;" flex="1">
			  	<columns  >
					<column flex="1"/>
					<column flex="5"/>
				</columns>
				<rows>
					<row>
						<label value="Funktion" control="funktion-menulist-funktion"/>
						<menulist id="funktion-menulist-funktion" disabled="true"
						          datasources="<?php echo APP_ROOT.'rdf/funktion.rdf.php'; ?>" flex="1"
						          ref="http://www.technikum-wien.at/funktion/liste"
						          oncommand="FunktionToggleFachbereich()">
							<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/funktion/rdf#funktion_kurzbz"
						        		      label="rdf:http://www.technikum-wien.at/funktion/rdf#beschreibung"
						        		      fachbereich="rdf:http://www.technikum-wien.at/funktion/rdf#fachbereich"
						        		      semester="rdf:http://www.technikum-wien.at/funktion/rdf#semester"
									  		  uri="rdf:*"/>
								</menupopup>
							</template>
						</menulist>
					</row>
					<row>
						<label value="Organisationseinheit" control="funktion-menulist-oe_kurzbz"/>
						<menulist id="funktion-menulist-oe_kurzbz" disabled="true"
								  xmlns:ORGANISATIONSEINHEIT="http://www.technikum-wien.at/organisationseinheit/rdf#"
						          datasources="<?php echo APP_ROOT;?>rdf/organisationseinheit.rdf.php" flex="1"
						          ref="http://www.technikum-wien.at/organisationseinheit/liste" >
							<template>
								<rule ORGANISATIONSEINHEIT:aktiv='false'>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#organisationseinheittyp_kurzbz rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"
										  		  uri="rdf:*" style="text-decoration:line-through;"/>
										</menupopup>
								</rule>
								<rule>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#organisationseinheittyp_kurzbz rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"
										  		  uri="rdf:*"/>
										</menupopup>
								</rule>
							</template>
						</menulist>
					</row>
					<row>
						<label id="funktion-label-semester" value="Semester" control="funktion-menulist-semester"/>
						<menulist id="funktion-menulist-semester" disabled="true">
							<menupopup>
								<menuitem value="" label="-- Keine Auswahl --"/>
								<menuitem value="1" label="1"/>
								<menuitem value="2" label="2"/>
								<menuitem value="3" label="3"/>
								<menuitem value="4" label="4"/>
								<menuitem value="5" label="5"/>
								<menuitem value="6" label="6"/>
								<menuitem value="7" label="7"/>
								<menuitem value="8" label="8"/>
								<menuitem value="9" label="9"/>
							</menupopup>
						</menulist>
					</row>
					<row>
						<label value="Institut" id="funktion-label-fachbereich" control="funktion-menulist-fachbereich"/>
						<menulist id="funktion-menulist-fachbereich" disabled="true"
								  xmlns:FACHBEREICH="http://www.technikum-wien.at/fachbereich/rdf#"
						          datasources="<?php echo APP_ROOT;?>/rdf/fachbereich.rdf.php?optional=true" flex="1"
						          ref="http://www.technikum-wien.at/fachbereich/liste" >
							<template>
								<rule FACHBEREICH:aktiv="false">
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/fachbereich/rdf#kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/fachbereich/rdf#bezeichnung"
										  		  uri="rdf:*" style="text-decoration:line-through;"/>
									</menupopup>
								</rule>
								<rule>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/fachbereich/rdf#kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/fachbereich/rdf#bezeichnung"
										  		  uri="rdf:*"/>
									</menupopup>
								</rule>
							</template>
						</menulist>
					</row>
					<row>
						<label value="Gültig von" control="funktion-box-datum_von"/>
						<hbox>
							<box class="Datum" id="funktion-box-datum_von" disabled="true"/>
						</hbox>
					</row>
					<row>
						<label value="Gültig bis" control="funktion-box-datum_bis"/>
						<hbox>
							<box class="Datum" id="funktion-box-datum_bis" disabled="true"/>
						</hbox>
					</row>
				</rows>
			</grid>
			<hbox>
			<spacer flex="1" />
			<button id="funktion-button-speichern" oncommand="FunktionDetailSpeichern()" label="Speichern" disabled="true"/>
		</hbox>
		</groupbox>	
		<spacer/>	
	</vbox>	
</hbox>
</window>