<?php
/* Copyright (C) 2014 fhcomplete.org
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

include('../../config/vilesci.config.inc.php');
include('../../include/addon.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';
if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id='';

if(isset($_GET['vertrag_id']) && is_numeric($_GET['vertrag_id']))
	$vertrag_id = $_GET['vertrag_id'];
else
	$vertrag_id='';
?>

<window id="mitarbeiter-vertrag-neu-dialog" title="Neu"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="MitarbeiterVertragNeuInit(<?php echo "'".$person_id."','".$vertrag_id."'"; ?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeitervertragneudialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<?php
// ADDONS
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'addons/'.$addon->kurzbz.'/content/init.js.php" />';
}
?>

<vbox flex="1">
	<hbox hidden="true">
		<label value="VertragID" control="mitarbeiter-vertrag-neu-textbox-vertrag_id" />
		<textbox id="mitarbeiter-vertrag-neu-textbox-vertrag_id" value=""/>
	</hbox>
	<description>Die folgenden Lehraufträge sind noch keinem Vertrag zugeordnet.
	Markieren Sie die Lehraufträge um diese dem Vertrag zuzuordnen:</description>
	<tree id="mitarbeiter-vertrag-tree-nichtzugeordnet" seltype="multi" hidecolumnpicker="false" flex="1"
	datasources="rdf:null" ref="http://www.technikum-wien.at/vertragdetails"
		enableColumnDrag="true"
		context="mitarbeiter-vertrag-tree-popup"
		flags="dont-build-content"
		onselect="MitarbeiterVertragNeuSelectEntry()"
	>
		<treecols>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-type" label="Typ" flex="2" hidden="false" primary="true"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="descending"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#type"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-betrag" label="Betrag" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betrag" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-bezeichnung" label="Bezeichnung" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#bezeichnung" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#studiensemester_kurzbz" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-pruefung_id" label="PruefungID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#pruefung_id" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-mitarbeiter_uid" label="mitarbeiter_uid" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#mitarbeiter_uid" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-projektarbeit_id" label="ProjektarbeitID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#projektarbeit_id" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#lehreinheit_id" />
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-betreuerart_kurzbz" label="BetreuerartKurzbz" flex="2" hidden="true"
                class="sortDirectionIndicator"
                 sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betreuerart_kurzbz"/>
            <splitter class="tree-splitter"/>
            <treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-vertragsstunden" label="Vertragsstunden" flex="2"
                 hidden="true"
                 class="sortDirectionIndicator"
                 sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#vertragsstunden"/>
            <splitter class="tree-splitter"/>
            <treecol id="mitarbeiter-vertrag-tree-nichtzugeordnet-vertragsstunden_studiensemester_kurzbz"
                 label="VertragsstundenStudiensemester" flex="2" hidden="true"
                 class="sortDirectionIndicator"
                 sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#vertragsstunden_studiensemester_kurzbz"/>
            <splitter class="tree-splitter"/>
		</treecols>

		<template>
			<treechildren flex="1" >
					<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#type"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betrag"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#bezeichnung"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#studiensemester_kurzbz"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#pruefung_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#mitarbeiter_uid"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#projektarbeit_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#lehreinheit_id"/>
						<treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betreuerart_kurzbz"/>
                        <treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#vertragsstunden"/>
                        <treecell label="rdf:http://www.technikum-wien.at/vertragdetails/rdf#vertragsstunden_studiensemester_kurzbz"/>
					</treerow>
				</treeitem>
			</treechildren>
		</template>
	</tree>
	<hbox>
		<grid id="mitarbeiter-buchung-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="8"/>
			</columns>
			<rows id="mitarbeiter-buchung-grid-detail-rows">
				<row>
					<label value="Vertragsdatum" control="mitarbeiter-vertrag-neu-box-vertragsdatum" />
					<hbox>
						<box class="Datum" id="mitarbeiter-vertrag-neu-box-vertragsdatum"/>
						<spacer />
					</hbox>
				</row>
				<row>
					<label value="Bezeichnung" control="mitarbeiter-vertrag-neu-textbox-bezeichnung" />
					<hbox>
						<textbox id="mitarbeiter-vertrag-neu-textbox-bezeichnung" value=""/>
						<spacer />
					</hbox>
				</row>
				<row>
					<label value="Typ" control="mitarbeiter-vertrag-neu-menulist-vertragstyp"/>
					<menulist id="mitarbeiter-vertrag-neu-menulist-vertragstyp" disabled="false"
							  datasources="<?php echo APP_ROOT ?>rdf/vertragstyp.rdf.php"
							  ref="http://www.technikum-wien.at/vertragstyp">
						<menupopup>
							<menuitem value=""
						   		      label="-- Keine Auswahl --"
							  		  />
						</menupopup>
						<template>
							<rule>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/vertragstyp/rdf#vertragstyp_kurzbz"
										  label="rdf:http://www.technikum-wien.at/vertragstyp/rdf#vertragstyp_bezeichnung"
								  		  uri="rdf:*"/>
								</menupopup>
							</rule>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Betrag" control="mitarbeiter-vertrag-neu-textbox-betrag" />
					<hbox>
						<textbox id="mitarbeiter-vertrag-neu-textbox-betrag" value="" size="10" />
						<spacer />
					</hbox>
				</row>
                <?php if (!empty($vertrag_id))
                {
                    echo '
                        <row>
                            <label value="Stunden (Vertrags-Urfassung)" control="mitarbeiter-vertrag-neu-textbox-vertragsstunden"/>
                            <hbox>
                                <textbox id="mitarbeiter-vertrag-neu-textbox-vertragsstunden" value="" disabled = "true"  size="10"/>
                                <spacer/>
                            </hbox>
                        </row>
                        <row>
                            <label value="Studiensemester (Vertrags-Urfassung)"
                                   control="mitarbeiter-vertrag-neu-textbox-vertragsstunden_studiensemester_kurzbz"/>
                            <hbox>
                                <textbox id="mitarbeiter-vertrag-neu-textbox-vertragsstunden_studiensemester_kurzbz"
                                         value="" disabled = "true" size="10" />
                                <spacer/>
                            </hbox>
                        </row>
                    ';
                }
                ?>
				<row>
					<label value="Anmerkung" control="mitarbeiter-vertrag-neu-textbox-anmerkung" />
					<textbox id="mitarbeiter-vertrag-neu-textbox-anmerkung" value="" size="100" multiline="true"/>
				</row>
			</rows>
		</grid>
	</hbox>
	<button id="mitarbeiter-vertrag-neu-button-speichern" label="Vertrag erstellen" oncommand="MitarbeiterVertragNeuGenerateVertrag()" />
</vbox>
</window>
