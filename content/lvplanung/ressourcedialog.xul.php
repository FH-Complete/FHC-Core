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
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

$stunden = $_GET['stunde'];
$stplids = $_GET['stplid'];
$datum = $_GET['datum'];
?>
<window id="ressource-dialog" title="Ressource"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload='RessourceInit(<?php echo '"'.$datum.'"'.','. json_encode($stunden).','.json_encode($stplids);?>)'
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lvplanung/ressourcedialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/tempusoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox>
	<grid id="ressource-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="8"/>
				<column flex="1"/>
				<column flex="8"/>
			</columns>
			<rows>
				<row>
					<vbox>
					<groupbox>
						<caption label="Zugeteilte Ressourcen" />

						<tree id="ressource-zugeteilt-tree" seltype="multiple" hidecolumnpicker="false" flex="1"
							datasources="rdf:null"
							 ref="http://www.technikum-wien.at/stundenplanbetriebsmittel"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
							onselect="RessourceZugeteiltAuswahl()"
							flags="dont-build-content"
						>

							<treecols>
								<treecol id="ressource-zugeteilt-tree-beschreibung" label="Beschreibung" flex="4" hidden="false" primary="true"
									class="sortDirectionIndicator"
									sortActive="true"
									sortDirection="ascending"
									sort="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#beschreibung"/>
								<splitter class="tree-splitter"/>
								<treecol id="ressource-zugeteilt-tree-anmerkung" label="Anmerkung" flex="5" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#anmerkung" />
								<splitter class="tree-splitter"/>
								<treecol id="ressource-zugeteilt-tree-stunde" label="Stunde" flex="1" hidden="false"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#stunde"/>
								<splitter class="tree-splitter"/>
								<treecol id="ressource-zugeteilt-tree-stundenplan_betriebsmittel_id" label="stundenplan_betriebsmittel_id" flex="1" hidden="true"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#stundenplan_betriebsmittel_id"/>
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<treechildren flex="1" >
										<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#beschreibung"/>
											<treecell label="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#anmerkung"/>
											<treecell label="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#stunde"/>
											<treecell label="rdf:http://www.technikum-wien.at/stundenplanbetriebsmittel/rdf#stundenplan_betriebsmittel_id"/>
										</treerow>
									</treeitem>
								</treechildren>
							</template>
						</tree>
					</groupbox>
					</vbox>
					<vbox>
						<spacer flex="1"/>
						<button label="&lt;" oncommand="RessourceAdd()"></button>
						<button label="&gt;" oncommand="RessourceRemove()"></button>
						<spacer flex="1"/>
					</vbox>
					<vbox>
					<groupbox>
						<caption label="Freie Ressourcen" />
						<tree id="ressource-verplanbar-tree" seltype="singe" hidecolumnpicker="false" flex="1"
							datasources="rdf:null"
							 ref="http://www.technikum-wien.at/betriebsmittel/liste"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
							flags="dont-build-content"
						>

							<treecols>
								<treecol id="ressource-verplanbar-tree-beschreibung" label="Beschreibung" flex="4" hidden="false" primary="true"
									class="sortDirectionIndicator"
									sortActive="true"
									sortDirection="ascending"
									sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung"/>
								<splitter class="tree-splitter"/>
								<treecol id="ressource-verplanbar-tree-betriebsmittel_id" label="betriebsmittel_id" flex="1" hidden="true"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"/>
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<treechildren flex="1" >
										<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung"/>
											<treecell label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"/>
										</treerow>
									</treeitem>
								</treechildren>
							</template>
						</tree>
					</groupbox>
					</vbox>
				</row>
			</rows>
	</grid>
			<groupbox>
				<caption id="ressource-detail-caption" label="Details" />
				<textbox id="ressource-detail-stundenplan_betriebsmittel_id" hidden="true"></textbox>
				<textbox id="ressource-detail-anmerkung" multiline="true" rows="6" disabled="true"></textbox>
				<hbox>
					<spacer flex="1" />
					<button id="ressource-detail-speichern" label="Speichern" disabled="true" oncommand="RessourceSave()" />
				</hbox>
			</groupbox>

</vbox>
</window>
