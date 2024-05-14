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

require_once('../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

$lehreinheit_id = filter_input(INPUT_GET, 'lehreinheit_id');
$lehrveranstaltung_id = filter_input(INPUT_GET, 'lehrveranstaltung_id');
$mitarbeiter_uid = filter_input(INPUT_GET,'mitarbeiter_uid');
$student_uid = filter_input(INPUT_GET,'student_uid');

?>

<window id="termine-window" title="termine"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadTermine(<?php echo "'".$lehreinheit_id."','".$lehrveranstaltung_id."','".$mitarbeiter_uid."','".$student_uid."'"; ?>);"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/termine.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox flex="1">
<popupset>
	<menupopup id="termine-tree-popup">
		<menuitem label="Anwesenheit umschalten" oncommand="TermineToggleAnwesenheit();" id="termine-tree-popup-toggle-anwesenheit" hidden="false"/>
		<menuitem label="Anwesenheit umschalten" oncommand="TermineToggleAnwesenheitMitarbeiter();" id="termine-tree-popup-togglemitarbeiter-anwesenheit" hidden="false"/>
	</menupopup>
</popupset>
	<groupbox id="termine-groupbox-termine" flex="1">
		<caption label="Termine" />
		<vbox flex="1">
            <hbox>
                <spacer flex="1" />
                <button type="checkbox" id="termine-button-stpltable" label="Stundenplan" oncommand="TermineChangeSTPLTable();"/>
            </hbox>
			<tree id="termine-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/termine"
				persist="hidden, height"
				context="termine-tree-popup"
			>
				<treecols>
					<treecol id="termine-treecol-datum" label="Datum" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#datum_iso" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-stundevon" label="Stunde Von" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#stundevon" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-stundebis" label="Stunde Bis" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#stundebis" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-uhrzeitvon" label="Von" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#uhrzeitvon" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-uhrzeitbis" label="Bis" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#uhrzeitbis" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-gruppen" label="Gruppen" flex="3" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#gruppen" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-ort" label="Ort" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#ort" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-lektor" label="LektorIn" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#lektor" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-lehrfach" label="Lehrfach" flex="4" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#lehrfach" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-anwesend" label="Anwesend" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#anwesend" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
                    <treecol id="termine-treecol-titel" label="Titel" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#titel" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-datum_iso" label="DatumISO" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#datum_iso" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="termine-treecol-lehreinheit_id" label="LehreinheitID" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/termine/rdf#lehreinheit_id" onclick="termineTreeSort()"/>
					<splitter class="tree-splitter"/>

				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow properties="rdf:http://www.technikum-wien.at/termine/rdf#kollision">
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#datum" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#stundevon" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#stundebis" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#uhrzeitvon" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#uhrzeitbis" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#gruppen" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#ort" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#lektor" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#lehrfach" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#anwesend" />
                                    <treecell label="rdf:http://www.technikum-wien.at/termine/rdf#titel" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#datum_iso" />
									<treecell label="rdf:http://www.technikum-wien.at/termine/rdf#lehreinheit_id" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
		</vbox>
	</groupbox>
	<button label="Exportieren" oncommand="TermineExport()"/>
</vbox>
</window>
