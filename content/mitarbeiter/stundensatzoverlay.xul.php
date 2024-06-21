<?php

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['mitarbeiter_uid']))
	$mitarbeiter_uid = $_GET['mitarbeiter_uid'];
else
	die('Parameter mitarbeiter_uid muss uebergeben werden');

?>

<window id="stundensaetze-window" title="Stundensaetze"
		xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
		onload="loadStundensaetze('<?php echo $mitarbeiter_uid; ?>');"
>
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/stundensatzoverlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

	<hbox flex="1">
		<!-- STUNDENSÄTZE -->
		<vbox flex="4">
			<tree id="stundensaetze-tree" seltype="single" hidecolumnpicker="false" flex="2"
				  datasources="rdf:null" ref="http://www.technikum-wien.at/stundensatz/liste"
				  onselect="StundensatzBearbeiten()"
				  flags="dont-build-content"
				  enableColumnDrag="true"
				  style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100"
				  persist="hidden, height"
			>
				<treecols>
					<treecol id="stundensatz-treecol-stundensatz_id" label="StundensatzID" flex="1" hidden="true"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatz_id"/>
					<splitter class="tree-splitter"/>
					<treecol id="stundensatz-treecol-stundensatz" label="Stundensatz" flex="1" hidden="false"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatz"/>
					<splitter class="tree-splitter"/>
					<treecol id="stundensatz-treecol-oe_kurzbz_bezeichnung" label="Unternehmen" flex="1" hidden="false"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#oe_kurzbz_bezeichnung"/>
					<splitter class="tree-splitter"/>
					<treecol id="stundensatz-treecol-stundensatz-typ" label="Stundensatztyp" flex="1" hidden="false"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatztyp_bezeichnung"/>
					<splitter class="tree-splitter"/>
					<treecol id="stundensatz-treecol-gueltig_von" label="Gültig von" flex="1" hidden="false"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#gueltig_von"/>
					<splitter class="tree-splitter"/>
					<treecol id="stundensatz-treecol-gueltig_bis" label="Gültig bis" flex="1" hidden="false"
							 class="sortDirectionIndicator"
							 sort="rdf:http://www.technikum-wien.at/stundensatz/rdf#gueltig_bis"/>
					<splitter class="tree-splitter"/>
				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatz_id" />
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatz" />
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#oe_kurzbz_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#stundensatztyp_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#gueltig_von" />
									<treecell label="rdf:http://www.technikum-wien.at/stundensatz/rdf#gueltig_bis" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
		</vbox>
		<vbox flex="1">
			<hbox>
				<button id="mitarbeiter-stundensatz-button-neu" label="Neu" oncommand="StundensatzNeu();" disabled="true"/>
				<button id="mitarbeiter-stundensatz-button-loeschen" label="Loeschen" oncommand="StundensatzDelete();" disabled="true"/>
			</hbox>
			<vbox hidden="true">
				<label value="Stundensatz" control="mitarbeiter-stundensatz-textbox-stundensatz_id"/>
				<textbox id="mitarbeiter-stundensatz-textbox-stundensatz_id" disabled="true"/>
			</vbox>
			<groupbox id="mitarbeiter-stundensatz-groupbox">
				<caption label="Details"/>
				<grid id="mitarbeiter-stundensatz-grid-detail" style="overflow:auto;margin:4px;" flex="1">
					<columns  >
						<column flex="1"/>
						<column flex="5"/>
					</columns>
					<rows>
						<row>
							<label value="Stundensatz" control="mitarbeiter-stundensatz-textbox-stundensatz"/>
							<hbox>
								<textbox id="mitarbeiter-stundensatz-textbox-stundensatz" disabled="true" maxlength="9" size="9"/>
								<spacer flex="1" />
							</hbox>
						</row>
						<row>
							<label value="Stundensatztyp" control="mitarbeiter-stundensatz-textbox-typ"/>
							<menulist id="mitarbeiter-stundensatz-textbox-typ"
									  flex="1"
									  disabled="true"
									  xmlns:STUNDENSATZTYP="http://www.technikum-wien.at/stundensatztyp/rdf#"
									  datasources="<?php echo APP_ROOT;?>rdf/stundensatztyp.rdf.php"
									  ref="http://www.technikum-wien.at/stundensatztyp/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/stundensatztyp/rdf#typ"
												  label="rdf:http://www.technikum-wien.at/stundensatztyp/rdf#bezeichnung"
												  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Unternehmen" control="mitarbeiter-stundensatz-textbox-unternehmen"/>
							<menulist id="mitarbeiter-stundensatz-textbox-unternehmen"
									  flex="1"
									  disabled="true"
									  xmlns:ORGANISATIONSEINHEIT="http://www.technikum-wien.at/organisationseinheit/rdf#"
									  datasources="<?php echo APP_ROOT;?>rdf/organisationseinheit.rdf.php?onlyRoots=true"
									  ref="http://www.technikum-wien.at/organisationseinheit/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"
												  label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"
												  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
						</row>
						<row>
							<label value="Gültig von" control="mitarbeiter-stundensatz-textbox-gueltig-von"/>
							<hbox>
								<box class="Datum" id="mitarbeiter-stundensatz-textbox-gueltig-von" disabled="true"/>
								<spacer flex="1" />
							</hbox>
						</row>
						<row>
							<label value="Gültig bis" control="mitarbeiter-stundensatz-textbox-gueltig-bis"/>
							<hbox>
								<box class="Datum" id="mitarbeiter-stundensatz-textbox-gueltig-bis" disabled="true"/>
								<spacer flex="1" />
							</hbox>
						</row>
					</rows>
				</grid>
				<hbox>
					<spacer flex="1" />
					<button id="mitarbeiter-stundensatz-button-speichern" oncommand="StundensatzDetailSpeichern()" label="Speichern" disabled="true"/>
				</hbox>
			</groupbox>
		</vbox>
	</hbox>
</window>
