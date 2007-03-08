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
require_once('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes" ?>';
echo "<?xml-stylesheet href=\"".APP_ROOT."content/lfvt.css\" type=\"text/css\" ?>";

echo '<?xul-overlay href="'.APP_ROOT.'content/lfvtdetailoverlay.xul.php"?>';

?>

<overlay id="LFVTOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lfvtoverlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Lehrfachverteilung  * -->
	<!-- ************************ -->
	<vbox id="lfvtEditor" flex="1">
		<toolbox>
			<toolbar id="nav-toolbar">
			<toolbarbutton id="lfvt_toolbar_neu" label="Neue Lehreinheit" oncommand="lvaNeu();" disabled="true"/>
			<!--<toolbarbutton label="Neue LVA-Partizipierung" oncommand="lvaNeuPart();"/>-->
			<toolbarbutton id="lfvt_toolbar_del" label="Löschen" oncommand="lvaDelete();" disabled="true"/>
			<toolbarbutton id="lfvt_toolbar_refresh" label="Neu laden" oncommand="lfvt_tree_refresh()" disabled="false"/>
			</toolbar>
		</toolbox>



		<!-- ************* -->
		<!-- *  Auswahl  * -->
		<!-- ************* -->
		<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
		<tree id="treeLFVT" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/lehrveranstaltung_einheiten/liste"
				style="margin:0px;"
				onselect="lvaAuswahl(this);"

		>
			<treecols>
				<treecol id="lva_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true"
					class="sortDirectionIndicator"
					sortActive="true"
					sortDirection="ascending"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_lehrveranstaltung_id" label="Lehrveranstaltung_id" flex="2" hidden="true"
					class="sortDirectionIndicator"
					sortActive="true"
					sortDirection="ascending"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id"	/>
				<splitter class="tree-splitter"/>	    				
				<treecol id="lva_bezeichnung" label="Bezeichnung" flex="5" hidden="false"
				   class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_sprache" label="Sprache" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#sprache" />
				<splitter class="tree-splitter"/>
				<treecol id="lva_ects" label="ECTS" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#ects" />
				<splitter class="tree-splitter"/>
				<treecol id="lva_semesterstunden" label="Semesterstunden" flex="1" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semesterstunden"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_lehre" label="Lehre" flex="2" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehre"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_lehrform" label="Lehrform" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrform_kurzbz"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_stundenblockung" label="Blockung" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#stundenblockung"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_wochenrythmus" label="WR" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#wochenrythmus"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_startkw" label="StartKW" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#startkw"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_raumtyp" label="Raumtyp" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtyp"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_raumtypalternativ" label="RaumtypAlt" flex="5" hidden="true"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtypalternativ"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_gruppen" label="Gruppen" flex="5" hidden="false"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#gruppen"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_lektoren" label="Lektoren" flex="5" hidden="false"  
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lektoren"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_lehreinheit_id" label="Lehreinheit_id" flex="10" hidden="true"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehreinheit_id"/>
				<splitter class="tree-splitter"/>
				<treecol id="lva_anmerkung" label="Anmerkung" flex="5" hidden="false"
					class="sortDirectionIndicator"
					sort="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#anmerkung"/>
				<splitter class="tree-splitter"/>
			</treecols>

			<template>
				<treechildren flex="1" >
   					<treeitem uri="rdf:*">
						<treerow dbID="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id">	         						
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrveranstaltung_id"  />
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#bezeichnung"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#sprache"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#ects"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#semesterstunden"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehre"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehrform_kurzbz"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#stundenblockung"/>									
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#wochenrythmus"/>									
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#startkw"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtyp"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#raumtypalternativ"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#gruppen"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lektoren"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#lehreinheit_id"/>
							<treecell label="rdf:http://www.technikum-wien.at/lehrveranstaltung_einheiten/rdf#anmerkung"/>
						</treerow>
					</treeitem>
				</treechildren>
			</template>
		</tree>

		<splitter collapse="after" persist="state">
			<grippy />
		</splitter>

		<!-- ************ -->
		<!-- *  Detail  * -->
		<!-- ************ -->
		<vbox flex="1"  style="overflow:auto;margin:0px;">
			<tabbox id="lfvt_detail_tabbox" flex="3" orient="vertical">
				<tabs orient="horizontal">
					<tab id="lfvt_detail_tab_detail" label="Details" />
					<tab id="lfvt_detail_tab_lektor" label="Lektorenzuteilung" />
				</tabs>
				<tabpanels id="lfvt_detail_tabpanels-main" flex="1">	
					<vbox id="lfvt-detail" />
					<vbox id="lfvt-lektorzuteilung" />							
				</tabpanels>
			</tabbox>
		</vbox>
	</vbox>
</overlay>