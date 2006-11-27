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

<!DOCTYPE overlay [
	<?php require("../locale/tempus.dtd"); ?>
]>

<overlay id="STPLDetailsOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>


<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/studenten.js" />



			<!-- ************************* -->
			<!-- *  Stundenplan Details  * -->
			<!-- ************************* -->
			<vbox id="vboxSTPLDetailsListe">


				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<tree id="treeStplDetails" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="" ref="http://www.technikum-wien.at/tempus/lehrstunde/liste"
						flags="dont-build-content"
						enableColumnDrag="true"
						style="margin:0px;"
				>
					<treecols>
	    				<treecol id="stplUNR" label="UNR" flex="2" primary="false"
	    					class="sortDirectionIndicator"
	    					sortActive="true"
	    					sortDirection="ascending"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#unr"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="stplLektor" label="Lektor" flex="2" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lektor" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="stplLehrfachKurzbz" label="Fach" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrfach" />
	    				<splitter class="tree-splitter"/>
						<treecol id="stplLehrform" label="Form" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrform" />
	    				<splitter class="tree-splitter"/>
						<treecol id="stplLehrfachBezeichnung" label="Lehrfach" flex="20" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrfach_bez" />
	    				<splitter class="tree-splitter"/>
						<treecol id="stpl_studiengang" label="Studiengang" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#studiengang" />
						<splitter class="tree-splitter"/>
	    				<treecol id="stplSemester" label="S" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#semester" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="stplVerband" label="V" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#verband" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="gruppe" label="G" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#gruppe" />
						<splitter class="tree-splitter"/>
	    				<treecol id="stpl_einheit" label="Einheit" flex="3" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#einheit" />
						<splitter class="tree-splitter"/>
	    				<treecol id="stplOrt" label="Ort" flex="2" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#ort_kurzbz" />
						<splitter class="tree-splitter"/>
						<treecol id="stpl_datum" label="Datum" flex="2" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#datum" />
						<splitter class="tree-splitter"/>
	    				<treecol id="stpl_stunde" label="Std" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#stunde" />
	    				<splitter class="tree-splitter"/>

					</treecols>

					<template>
						<rule>
	      					<treechildren>
	       						<treeitem uri="rdf:*">
	         						<treerow>
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#unr"   />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lektor" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrfach" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrform" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#lehrfach_bez" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#studiengang" />
	         							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#semester" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#verband" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#gruppe" />
										<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#einheit" />
										<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#ort_kurzbz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#datum" />
										<treecell label="rdf:http://www.technikum-wien.at/tempus/lehrstunde/rdf#stunde" />
	           						</treerow>
	       						</treeitem>
	      					</treechildren>
	      				</rule>
  					</template>

<!--
					<template>
						<rule>
							<conditions>
								<content uri="?uri" />
								<member container="?uri" child="?student" />
							</conditions>
							<bindings>
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#uid" object="?uid" />
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#titel" object="?titel" />
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#vornamen" object="?vornamen" />
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#nachname" object="?nachname" />
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#geburtsdatum" object="?geburtsdatum" />
								<binding subject="?student" predicate="http://www.technikum-wien.at/tempus/studenten/rdf#aktiv" object="?aktiv" />
							</bindings>
							<action>
								<treechildren>
	       							<treeitem uri="?student">
	         							<treerow>
	           								<treecell label="?uid"   />
	           								<treecell label="?titel" />
	           								<treecell label="?vornamen" />
	           								<treecell label="?nachname" />
	           								<treecell label="?geburtsdatum" />
	           								<treecell label="?aktiv" />
	         							</treerow>
	       							</treeitem>
	      						</treechildren>
							</action>
	      				</rule>
  					</template>
-->

				</tree>


			</vbox>
</overlay>
