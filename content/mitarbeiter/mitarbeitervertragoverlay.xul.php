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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/vertrag.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>
<overlay id="Mitarbeitervertrag"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeitervertrag.js.php" />

<vbox id="mitarbeiter-vertrag" style="overflow:auto;margin:0px;" flex="1">
	<popupset>
		<menupopup id="mitarbeiter-vertrag-tree-vertragsstatus-popup">
<?php
	$vertrag = new vertrag();
	$vertrag->loadVertragsstatus();

	foreach($vertrag->result as $row)
	{
		echo '<menuitem label="'.$row->bezeichnung.'" oncommand="MitarbeiterVertragStatusAdd(\''.$row->vertragsstatus_kurzbz.'\');" hidden="false"/>';
	}
?>
		<menuseparator />
		<menuitem label="Eintrag löschen" oncommand="MitarbeiterVertragStatusDelete()" />
		</menupopup>
		<menupopup id="mitarbeiter-vertrag-tree-popup">
			<menuitem label="Bearbeiten" oncommand="MitarbeiterVertragEdit()" />
			<menuitem label="Entfernen" oncommand="MitarbeiterVertragDelete()" />
		</menupopup>
		<menupopup id="mitabeiter-vertrag-tree-detail-popup">
			<menuitem label="Entfernen" oncommand="MitarbeiterVertragDetailDelete()" />
		</menupopup>
	</popupset>
	<vbox flex="3">
		<vbox flex="1">
			<hbox>
				<toolbox flex="1">
					<toolbar>
						<toolbarbutton label="Filter " id="mitarbeiter-vertrag-filter" type="menu">
							<menupopup id="mitarbeiter-vertrag-menupopup-filter">
									<menuitem type="radio" value="alle" label="Alle Verträge anzeigen" oncommand="MitarbeiterVertragLoad()"/>
									<menuitem type="radio" value="offen" label="Offene Verträge anzeigen" checked="true" oncommand="MitarbeiterVertragLoad()"/>
							</menupopup>
						</toolbarbutton>
						<spacer flex="1" />
						<toolbarbutton id="mitarbeiter-vertrag-toolbarbutton-neu" tooltiptext="Neu" image="../skin/images/NeuDokument.png" label="Neuen Vertrag erstellen" oncommand="MitarbeiterVertragAddVertrag()" />
					</toolbar>
				</toolbox>
			</hbox>
			<tree id="mitarbeiter-vertrag-tree" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/vertrag"
				enableColumnDrag="true"
				flags="dont-build-content"
				context="mitarbeiter-vertrag-tree-popup"
				onselect="MitarbeiterVertragSelectVertrag();"
				ondblclick="MitarbeiterVertragEdit()"
			>
				<treecols>
					<treecol id="mitarbeiter-vertrag-tree-bezeichnung" label="Bezeichnung" flex="2" hidden="false" primary="true"
						class="sortDirectionIndicator"
						sortActive="true"
						sortDirection="descending"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#bezeichnung"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-betrag" label="Betrag" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#betrag" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragstyp" label="Vertragstyp" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragstyp_bezeichnung" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-status" label="Status" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#status" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsdatum" label="Vertragsdatum" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsdatum_iso" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragstyp_kurzbz" label="VertragstypKurzbz" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragstyp_kurzbz" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertrag_id" label="vertragID" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertrag_id" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsdatumiso" label="VertragsdatumISO" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsdatum_iso" />
					<splitter class="tree-splitter"/>
                    <treecol id="mitarbeiter-vertrag-tree-vertragsstunden" label="Vertragsstunden" flex="2" hidden="true"
                             class="sortDirectionIndicator"
                             sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsstunden" />
                    <splitter class="tree-splitter"/>
                    <treecol id="mitarbeiter-vertrag-tree-vertragsstunden_studiensemester_kurzbz" label="VertragsstundenStudiensemester" flex="2" hidden="true"
                             class="sortDirectionIndicator"
                             sort="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsstunden_studiensemester_kurzbz" />
                    <splitter class="tree-splitter"/>
				</treecols>

				<template>
					<treechildren flex="1" >
							<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#bezeichnung"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#betrag"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragstyp_bezeichnung"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#status"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsdatum"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragstyp_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertrag_id"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsdatum_iso"/>
                                <treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsstunden"/>
                                <treecell label="rdf:http://www.technikum-wien.at/vertrag/rdf#vertragsstunden_studiensemester_kurzbz"/>
							</treerow>
						</treeitem>
					</treechildren>
				</template>
			</tree>
		</vbox>
	</vbox>
	<hbox flex="2" id="mitarbeiter-vertrag-vbox-vertragsdetails">
		<vbox flex="2">
			<description>Vertragsdetails</description>
			<tree id="mitarbeiter-vertrag-tree-zugeordnet" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/vertragdetails"
				enableColumnDrag="true"
				context="mitabeiter-vertrag-tree-detail-popup"
				flags="dont-build-content"
			>
				<treecols>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-type" label="Typ" flex="2" hidden="false" primary="true"
						class="sortDirectionIndicator"
						sortActive="true"
						sortDirection="descending"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#type"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-betrag" label="Betrag" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betrag" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-bezeichnung" label="Bezeichnung" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#bezeichnung" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-studiensemester_kurzbz" label="Studiensemester" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#studiensemester_kurzbz" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-pruefung_id" label="PruefungID" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#pruefung_id" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-mitarbeiter_uid" label="mitarbeiter_uid" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#mitarbeiter_uid" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-projektarbeit_id" label="ProjektarbeitID" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#projektarbeit_id" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#lehreinheit_id" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-zugeordnet-betreuerart_kurzbz" label="BetreuerartKurzbz" flex="2" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragdetails/rdf#betreuerart_kurzbz" />
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
							</treerow>
						</treeitem>
					</treechildren>
				</template>
			</tree>
		</vbox>
		<vbox flex="2">
		    <vbox flex="1">
			<description>Vertragsstatus</description>
			<tree id="mitarbeiter-vertrag-tree-vertragsstatus" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/vertragsstatus"
				enableColumnDrag="true"
				context="mitarbeiter-vertrag-tree-vertragsstatus-popup"
				flags="dont-build-content"
				onselect="MitarbeiterVertragSelectVertragsstatus();"
			>
				<treecols>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_bezeichnung" label="Status" flex="2" hidden="false" primary="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertragsstatus_bezeichnung"/>
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-datum" label="Datum" flex="2" hidden="false"
						class="sortDirectionIndicator"
						sortActive="true"
						sortDirection="descending"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#datum" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-vertrag_id" label="VertragID" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertrag_id" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-uid" label="User" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#uid" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-vertragsstatus_kurzbz" label="VertragsstatusKurzbz" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertragsstatus_kurzbz" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-insertvon" label="insertvon" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#insertvon" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-insertamum" label="insertamum" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#insertamum" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-updatevon" label="updatevon" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#updatevon" />
					<splitter class="tree-splitter"/>
					<treecol id="mitarbeiter-vertrag-tree-vertragsstatus-updateamum" label="updateamum" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#updateamum" />

				</treecols>

				<template>
					<treechildren flex="1" >
							<treeitem uri="rdf:*">
							<treerow>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertragsstatus_bezeichnung"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#datum"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertrag_id"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#uid"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#vertragsstatus_kurzbz"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#insertvon"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#insertamum"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#updatevon"/>
								<treecell label="rdf:http://www.technikum-wien.at/vertragsstatus/rdf#updateamum"/>
							</treerow>
						</treeitem>
					</treechildren>
				</template>
			</tree>
		    </vbox>
		    <vbox>
			    <grid align="end" flex="1">
				<columns  >
				    <column flex="1"/>
				    <column flex="5"/>
				</columns>
				<rows>
				    <row>
					<label value="Vertragsdatum" control="mitarbeiter-vertrag-vertragsstatus-textbox-vertragsdatum"/>
					<hbox>
					    <box class="Datum" id="mitarbeiter-vertrag-vertragsstatus-textbox-vertragsdatum" disabled="true"/>
					    <button id="mitarbeiter-vertrag-vertragsstatus-datum-speichern" label="Speichern" oncommand="MitarbeiterVertragVertragsstatusUpdate()" />
					</hbox>
				    </row>
				</rows>
			    </grid>
		    </vbox>
		</vbox>
	</hbox>
</vbox>
</overlay>
