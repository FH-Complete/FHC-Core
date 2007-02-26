<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes" ?>';
echo "<?xml-stylesheet href=\"".APP_ROOT."content/lfvt.css\" type=\"text/css\" ?>";



?>



<overlay id="LFVTOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

			<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
			<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lfvtoverlay.js.php" />

			<!-- ************************ -->
			<!-- *  Lehrfachverteilung  * -->
			<!-- ************************ -->
			<vbox id="lfvtEditor" flex="1">
				<toolbox>
  				<toolbar id="nav-toolbar">
    				<toolbarbutton id="lfvt_toolbar_neu" label="Neue Lehreinheit" oncommand="lvaNeu();" disabled="true"/>
					<!--<toolbarbutton label="Neue LVA-Partizipierung" oncommand="lvaNeuPart();"/>-->
    				<toolbarbutton id="lfvt_toolbar_del" label="Löschen" oncommand="lvaDelete();" disabled="true"/>
  				</toolbar>
				</toolbox>



				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<!-- Bem.: style="visibility:collapse" versteckt eine Spalte -->
				<tree id="treeLFVT" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/tempus/lva/liste"
						style="margin:0px;"
						onselect="lvaAuswahl(this);"

				>
					<treecols>
						<treecol id="lva_kurzbz" label="Kurzbz" flex="2" hidden="false" primary="true"
							class="sortDirectionIndicator"
							sortActive="true"
	    					sortDirection="ascending"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#kurzbz"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_lehrveranstaltung_id" label="Lehrveranstaltung_id" flex="2" hidden="true"
							class="sortDirectionIndicator"
							sortActive="true"
	    					sortDirection="ascending"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrveranstaltung_id"	/>
	    				<splitter class="tree-splitter"/>	    				
	    				<treecol id="lva_bezeichnung" label="Bezeichnung" flex="5" hidden="false"
						   class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#bezeichnung"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_sprache" label="Sprache" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#sprache" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_ects" label="ECTS" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#ects" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_semesterstunden" label="Semesterstunden" flex="1" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#semesterstunden"/>
						<splitter class="tree-splitter"/>
						<treecol id="lva_lehre" label="Lehre" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehre"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_lehrform" label="Lehrform" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrform_kurzbz"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_stundenblockung" label="Blockung" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#stundenblockung"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_wochenrythmus" label="WR" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#wochenrythmus"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_startkw" label="StartKW" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#startkw"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_raumtyp" label="Raumtyp" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtyp"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_raumtypalternativ" label="RaumtypAlt" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtypalternativ"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_gruppen" label="Gruppen" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#gruppen"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_lektoren" label="Lektoren" flex="5" hidden="true"  
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lektoren"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_lehreinheit_id" label="Lehreinheit_id" flex="10" hidden="true"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehreinheit_id"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lva_anmerkung" label="Anmerkung" flex="5" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#anmerkung"/>
	    				<splitter class="tree-splitter"/>
					</treecols>

					<template>
						<treechildren flex="1" >
	       					<treeitem uri="rdf:*">
								<treerow dbID="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrveranstaltung_id">	         						
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#kurzbz"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrveranstaltung_id"  />
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#bezeichnung"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#sprache"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#ects"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#semesterstunden"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehre"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrform_kurzbz"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#stundenblockung"/>									
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#wochenrythmus"/>									
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#startkw"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtyp"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtypalternativ"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#gruppen"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lektoren"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehreinheit_id"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#anmerkung"/>
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

				  	<box id="lvaDetail" class="lvaDetail"  style="margin:0px;" />

				</vbox>

			</vbox>
</overlay>