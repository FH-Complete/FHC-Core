<?php
/* Copyright (C) 2016 fhcomplete.org
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
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="MobilitaetDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentmobilitaetoverlay.js.php" />
<vbox id="student-mobilitaet" style="overflow:auto;margin:0px;" flex="1">
<popupset>
	<menupopup id="student-mobilitaet-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentMobilitaetDelete();" id="student-mobilitaet-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>

<hbox flex="1">
<grid id="student-mobilitaet-grid-detail" style="margin:4px;" flex="1">
			<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="student-mobilitaet-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/mobilitaet"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						context="student-mobilitaet-tree-popup"
						flags="dont-build-content"
					>
						<treecols>
							<treecol id="student-mobilitaet-tree-studiensemester" label="StSem" flex="2" hidden="false" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#studiensemester"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-mobilitaet-tree-mobilitaetsprogramm" label="Mobilitaetsprogramm" flex="1" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#mobilitaetsprogramm"/>
							<splitter class="tree-splitter"/>
							<treecol id="student-mobilitaet-tree-status_kurzbz" label="Status" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#status_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="student-mobilitaet-tree-ausbildungssemester" label="Sem" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#ausbildungssemester" />
							<splitter class="tree-splitter"/>
							<treecol id="student-mobilitaet-tree-gsprogrammtyp_kurzbz" label="Typ" flex="2" hidden="false"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#gsprogrammtyp_kurzbz" />
							<splitter class="tree-splitter"/>
							<treecol id="student-mobilitaet-tree-mobilitaet_id" label="ID" flex="2" hidden="true"
								class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/mobilitaet/rdf#mobilitaet_id" />
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#studiensemester_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#mobilitaetsprogramm"/>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#status_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#ausbildungssemester"/>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#gsprogrammtyp_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/mobilitaet/rdf#mobilitaet_id"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="student-mobilitaet-button-neu" label="Neu" oncommand="StudentMobilitaetNeu();" disabled="true"/>
							<button id="student-mobilitaet-button-loeschen" label="Loeschen" oncommand="StudentMobilitaetDelete();" disabled="true"/>
						</hbox>
						<vbox hidden="true">
							<label value="Neu"/>
							<checkbox id="student-mobilitaet-detail-checkbox-neu" checked="true" />
							<label value="Uid"/>
							<textbox id="student-mobilitaet-detail-textbox-prestudent_id" disabled="true"/>
							<label value="gs ID"/>
							<textbox id="student-mobilitaet-detail-textbox-mobilitaet_id" disabled="true"/>
						</vbox>
						<groupbox id="student-mobilitaet-groupbox" flex="1">
						<caption label="Gemeinsame Studien"/>
							<grid id="student-mobilitaet-grid-detail" style="overflow:auto;margin:4px;" flex="1">
								<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Typ" control="student-mobilitaet-menulist-mobilitaetstyp"/>
										<menulist id="student-mobilitaet-menulist-mobilitaetstyp" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/mobilitaetstyp.rdf.php" flex="1"
												ref="http://www.technikum-wien.at/mobilitaetstyp" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/mobilitaetstyp/rdf#mobilitaetstyp_kurzbz"
															label="rdf:http://www.technikum-wien.at/mobilitaetstyp/rdf#bezeichnung"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Studiensemester" control="student-mobilitaet-menulist-studiensemester"/>
										<menulist id="student-mobilitaet-menulist-studiensemester" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php" flex="1"
												ref="http://www.technikum-wien.at/studiensemester/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
															label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Mobilitaetsprogramm" control="student-mobilitaet-menulist-mobilitaetsprogramm"/>
										<menulist id="student-mobilitaet-menulist-mobilitaetsprogramm" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/mobilitaetsprogramm.rdf.php?optional=true" flex="1"
												ref="http://www.technikum-wien.at/mobilitaetsprogramm/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/mobilitaetsprogramm/rdf#mobilitaetsprogramm_code"
															label="rdf:http://www.technikum-wien.at/mobilitaetsprogramm/rdf#kurzbz - rdf:http://www.technikum-wien.at/mobilitaetsprogramm/rdf#beschreibung"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Studienprogramm" control="student-mobilitaet-menulist-gsprogramm"/>
										<menulist id="student-mobilitaet-menulist-gsprogramm" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/gsprogramm.rdf.php" flex="1"
												ref="http://www.technikum-wien.at/gsprogramm" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/gsprogramm/rdf#gsprogramm_id"
															label="rdf:http://www.technikum-wien.at/gsprogramm/rdf#gsprogrammtyp_bezeichnung - rdf:http://www.technikum-wien.at/gsprogramm/rdf#bezeichnung"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Partner" control="student-mobilitaet-menulist-firma"/>
										<menulist id="student-mobilitaet-menulist-firma" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/firma.rdf.php?partner=true&amp;optional=true" flex="1"
												ref="http://www.technikum-wien.at/firma/liste" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
															label="rdf:http://www.technikum-wien.at/firma/rdf#name"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Status" control="student-mobilitaet-menulist-status"/>
										<menulist id="student-mobilitaet-menulist-status" disabled="true"
												datasources="<?php echo APP_ROOT ?>rdf/status.rdf.php" flex="1"
												ref="http://www.technikum-wien.at/status" >
											<template>
												<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/status/rdf#status_kurzbz"
															label="rdf:http://www.technikum-wien.at/status/rdf#status_kurzbz"
															uri="rdf:*"/>
													</menupopup>
											</template>
										</menulist>
									</row>
									<row>
										<label value="Ausbildungssemester" control="student-mobilitaet-textbox-ausbildungssemester"/>
										<hbox>
											<textbox id="student-mobilitaet-textbox-ausbildungssemester" disabled="true" size="1"/>
											<spacer flex="1" />
										</hbox>
									</row>
								</rows>
							</grid>
						</groupbox>
						<hbox>
							<spacer flex="1" />
							<button id="student-mobilitaet-button-kopie-speichern" oncommand="StudentMobilitaetSpeichern(true)" label="Als Kopie speichern" disabled="true"/>
							<button id="student-mobilitaet-button-speichern" oncommand="StudentMobilitaetSpeichern()" label="Speichern" disabled="true"/>
						</hbox>
					</vbox>
				</row>
		</rows>
</grid>
</hbox>
<spacer flex="1" />
</vbox>

</overlay>