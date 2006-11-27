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
    				<toolbarbutton label="Neue LVA" oncommand="lvaNeu();" />
					<toolbarbutton label="Neue LVA-Partizipierung" oncommand="lvaNeuPart();"/>
    				<toolbarbutton label="Löschen" oncommand="lvaDelete();" />
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
	    				<treecol id="lvaUnr" label="UNR" flex="2" primary="true"
							class="sortDirectionIndicator"
							sortActive="true"
	    					sortDirection="ascending"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#unr"
						/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaLvnr" label="LVNR" flex="1" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lvnr" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaLehrfach" label="Lehrfach" flex="10" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrfach"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaLektor" label="Lektor" flex="5" hidden="false"
						   class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lektorPrettyPrint"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaSemester" label="S" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#semester" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaVerband" label="V" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#verband" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaGruppe" label="G" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#gruppe"/>
						<splitter class="tree-splitter"/>
						<treecol id="lvaEinheit" label="Einheit" flex="2" hidden="false"
							class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lva/rdf#einheit_kurzbz"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaRaumtyp" label="R.Typ." flex="5" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaRaumtypalternativ" label="R.Typ.alt." flex="5" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaSemesterstunden" label="Semesterstunden" flex="1" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaStundenblockung" label="Stundenblockung" flex="5" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaWochenrythmus" label="Wochenrythmus" flex="5" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaStart_kw" label="Start KW" flex="5" hidden="true"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="lvaStudiensemester_kurzbz" label="Studiensemester" flex="5" hidden="true"  />
						<splitter class="tree-splitter"/>
	    				<treecol id="lvaLehrform" label="Lehrform" flex="5" hidden="true"  />
					</treecols>

					<template>
						<treechildren flex="1" >
	       					<treeitem uri="rdf:*">
								<treerow dbID="rdf:http://www.technikum-wien.at/tempus/lva/rdf#dbID">
	         						<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#unr"  />
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lvnr"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrfach"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lektorPrettyPrint"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#semester"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#verband"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#gruppe"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#einheit_kurzbz"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtyp"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#raumtypalternativ"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#semesterstunden"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#stundenblockung"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#wochenrythmus"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#start_kw"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#studiensemester_kurzbz"/>
									<treecell label="rdf:http://www.technikum-wien.at/tempus/lva/rdf#lehrform"/>
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