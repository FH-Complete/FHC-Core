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
require_once('../../config/global.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

?>

<overlay id="StudentKonto"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- Zeugnis Overlay -->
<vbox id="student-konto" style="overflow:auto;margin:0px;" flex="1">
<popupset>
	<menupopup id="student-konto-tree-popup">
		<menuitem label="Entfernen" oncommand="StudentKontoDelete();" id="student-konto-tree-popup-kontodel" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="student-konto-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="4"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<hbox></hbox>
					<hbox>
						<!--
						<button id="student-konto-button-filterstudenten" oncommand="StudentKontoFilterStudenten('konto')" label="Studentenliste auf offene Buchungen filtern" tooltiptext="Liste aller Studenten mit offenen Buchungen"/>
						-->
						<!--
						<button id="student-konto-button-filterstudiengebuehr" oncommand="StudentKontoFilterStudenten('studiengebuehr')" label="nicht gebuchte Studiengebuehr" tooltiptext="Liste aller Studenten ohne Studiengebuehrbelastung"/>
						-->

						<vbox>
							<spacer flex="1" />
							<label value="Liste filtern auf nicht belastet: "/>
							<sapcer flex="1" />
						</vbox>
						<vbox flex="1">
							<spacer flex="1" />
							<menulist id="student-konto-menulist-filter-buchungstyp" disabled="false"
							          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php" flex="1"
							          ref="http://www.technikum-wien.at/buchungstyp/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
										  		  uri="rdf:*"/>
										</menupopup>
								</template>
							</menulist>
							<spacer flex="1" />
						</vbox>
						<button id="student-konto-button-filterbuchungstyp" oncommand="StudentKontoFilterBuchungstyp()" label="filtern"/>

					</hbox>
				</row>
				<row>
					<hbox></hbox>
					<hbox>
						<vbox>
							<spacer flex="1" />
							<label value="Liste filtern auf fehlende Gegenbuchungen: "/>
							<sapcer flex="1" />
						</vbox>
						<vbox flex="1">
							<spacer flex="1" />
							<menulist id="student-konto-menulist-filter-buchungstyp-offen" disabled="false"
							          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php" flex="1"
							          ref="http://www.technikum-wien.at/buchungstyp/liste" >
								<template>
									<menupopup>
										<menuitem value="alle"
												  label="alle Buchungstypen"/>
										<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
										  		  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
							<spacer flex="1" />
						</vbox>
						<button id="student-konto-button-filterbuchungstypoffen" oncommand="StudentKontoFilterStudenten('konto')" label="filtern"/>

					</hbox>
				</row>

				<row flex="1">
					<vbox flex="1">
						<hbox>
							<button id="student-konto-button-filter" value="alle" oncommand="StudentKontoFilter()" label="Offene anzeigen" disabled="true"/>
							<spacer flex="1" />
						</hbox>

						<label id="student-konto-label-filter" value="alle Buchungen:" hidden="true"/>

						<tree id="student-konto-tree" seltype="multi" hidecolumnpicker="false" flex="1"
							datasources="rdf:null" ref="http://www.technikum-wien.at/konto/liste"
							style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" enableColumnDrag="true"
							context="student-konto-tree-popup"
							flags="dont-build-content"
						>
						<!-- onselect="StudentKontoAuswahl()" - wird jetzt per JS gesetzt -->
							<treecols>
								<treecol id="student-konto-tree-buchungsdatum" label="Buchungsdatum" flex="1" hidden="false" primary="true"
									class="sortDirectionIndicator"
									sortActive="true"
									sortDirection="ascending"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungsdatum_iso"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-buchungstext" label="Buchungstext" flex="5" hidden="false"
								   class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungstext"/>
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-betrag" label="Betrag" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#betrag" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-studiensemester_kurzbz" label="StSem" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#studiensemester_kurzbz" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-buchungstyp_kurzbz" label="Typ" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungstyp_kurzbz" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-buchungsnr" label="buchungs_nr" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#buchungsnr" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-insertvon" label="Angelegt von" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#insertvon" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-insertamum" label="Anlagedatum" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#insertvon" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-studiengang" label="Studiengang" flex="2" hidden="true"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#studiengang_kuerzel" />
								<splitter class="tree-splitter"/>
								<treecol id="student-konto-tree-anmerkung" label="Anmerkung" flex="2" hidden="false"
									class="sortDirectionIndicator"
									sort="rdf:http://www.technikum-wien.at/konto/rdf#anmerkung" />
								<splitter class="tree-splitter"/>
							</treecols>

							<template>
								<treechildren flex="1" >
										<treeitem uri="rdf:*">
										<treerow>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungsdatum"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungstext"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#betrag"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#studiensemester_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungstyp_kurzbz"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#buchungsnr"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#insertvon"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#insertamum"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#studiengang_kuerzel"/>
											<treecell label="rdf:http://www.technikum-wien.at/konto/rdf#anmerkung"/>
										</treerow>
									</treeitem>
								</treechildren>
							</template>
						</tree>
					</vbox>
					<vbox flex="1">
                        <?php
                        $is_hidden = (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && ZAHLUNGSBESTAETIGUNG_ANZEIGEN) ? 'false' : 'true';
                        ?>
						<hbox>
							<button id="student-konto-button-neu" label="Neu" oncommand="StudentKontoNeu();" disabled="true"/>
							<button id="student-konto-button-gegenbuchung" label="Gegenbuchung" oncommand="StudentKontoGegenbuchung();" disabled="true"/>
							<box class="Datum" id="student-konto-textbox-gegenbuchungsdatum" disabled="true" tooltiptext="Optionales Datum der Gegenbuchung"/>
							<button id="student-konto-button-loeschen" label="Loeschen" oncommand="StudentKontoDelete();" disabled="true"/>
							<spacer flex="1"/>
							<button id="student-konto-button-zahlungsbestaetigung" label="Zahlungsbestaetigung drucken" oncommand="StudentKontoZahlungsbestaetigung();" disabled="true" hidden="<?php echo $is_hidden?>"/>
						</hbox>
						<vbox hidden="true">
							<label value="Buchungsnr" control="student-konto-textbox-buchungsnr"/>
							<textbox id="student-konto-textbox-buchungsnr" disabled="true"/>
						</vbox>
						<groupbox id="student-konto-groupbox">
						<caption label="Details"/>
							<grid id="student-konto-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Betrag" control="student-konto-textbox-betrag"/>
										<hbox>
					      					<textbox id="student-konto-textbox-betrag" disabled="true" maxlength="9" size="9"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Buchungsdatum" control="student-konto-textbox-buchungsdatum"/>
										<hbox>
											<box class="Datum" id="student-konto-textbox-buchungsdatum" disabled="true"/>
					      					<!--<textbox id="student-konto-textbox-buchungsdatum" disabled="true" maxlength="10" size="10"/>-->
					      					<spacer flex="1" />
					      				</hbox>
					      			</row>
					      			<row>
					      				<label value="Buchungstext" control="student-konto-textbox-buchungstext"/>
							      		<textbox id="student-konto-textbox-buchungstext" disabled="true" maxlength="256"/>
									</row>
									<?php
										// Mahnspanne wird nur angezeigt, wenn diese im Config aktiviert wurden
										if(!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE===true)
											$hidden='';
										else
											$hidden='hidden="true"';
									?>
									<row <?php echo $hidden; ?>>
										<label value="Mahnspanne" control="student-konto-textbox-mahnspanne"/>
										<hbox>
											<textbox id="student-konto-textbox-mahnspanne" disabled="true" maxlength="4" size="4"/>
											<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Typ" control="student-konto-menulist-buchungstyp"/>
										<menulist id="student-konto-menulist-buchungstyp" disabled="true"
												xmlns:TYP="http://www.technikum-wien.at/buchungstyp/rdf#"
										          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/buchungstyp/liste" >
											<template>
												<rule TYP:aktiv="false">
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
													  		  uri="rdf:*" style="text-decoration:line-through;"/>
													</menupopup>
												</rule>
												<rule>
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
													  		  uri="rdf:*"/>
													</menupopup>
												</rule>

											</template>
										</menulist>
									</row>
									<row>
										<label value="Studiensemester" control="student-konto-menulist-studiensemester"/>
										<menulist id="student-konto-menulist-studiensemester" disabled="true"
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
										<label value="Studiengang" control="student-konto-menulist-studiengang_kz"/>
										<menulist id="student-konto-menulist-studiengang_kz" disabled="true"
												xmlns:STG="http://www.technikum-wien.at/studiengang/rdf#"
										          datasources="<?php echo APP_ROOT ?>rdf/studiengang.rdf.php" flex="1"
										          ref="http://www.technikum-wien.at/studiengang/liste" >
											<template>
												<!--<rule STG:aktiv="false">
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
										        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
													  		  uri="rdf:*" style="text-decoration:line-through;"/>
													</menupopup>
												</rule>-->
												<rule>
													<menupopup>
													<menuitem value="rdf:http://www.technikum-wien.at/studiengang/rdf#studiengang_kz"
										        		      label="rdf:http://www.technikum-wien.at/studiengang/rdf#kuerzel"
													  		  uri="rdf:*"/>
													</menupopup>
												</rule>

											</template>
										</menulist>
									</row>
									<?php
									// Credit Points werden nur angezeigt, wenn diese im Config aktiviert wurden
									if(defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS=='true')
										$hidden='';
									else
										$hidden='hidden="true"';
									echo '	<row '.$hidden.'>
												<label value="Credit Points" control="student-konto-textbox-credit_points" '.$hidden.'/>
												<hbox '.$hidden.'>
													<textbox id="student-konto-textbox-credit_points" disabled="true" maxlength="9" size="9" '.$hidden.'/>
													<spacer flex="1" />
												</hbox>
											</row>';
									?>
									<row>
										<label value="Zahlungsreferenz" control="student-konto-textbox-zahlungsreferenz"/>
										<hbox>
					      					<textbox id="student-konto-textbox-zahlungsreferenz" disabled="true" maxlength="20" size="20"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Anmerkung" control="student-konto-textbox-anmerkung"/>
										<textbox id="student-konto-textbox-anmerkung" multiline="true"/>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="student-konto-button-speichern" oncommand="StudentKontoDetailSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>
</hbox>
</vbox>
</overlay>
