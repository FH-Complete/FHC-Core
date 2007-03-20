<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes" ?>';
echo "<?xml-stylesheet href=\"".APP_ROOT."content/lvplanung/lehrveranstaltung.css\" type=\"text/css\" ?>";


?>

<!DOCTYPE overlay >

<overlay id="StudentenOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>


<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/studenten.js" />



			<!-- *************** -->
			<!-- *  Studenten  * -->
			<!-- *************** -->
			<vbox id="studentenEditor">
				<hbox>
					<toolbox>
	  					<toolbar id="studentenToolbar">
	    					<toolbarbutton label="Neu"/>
							<toolbarbutton label="Speichern"/>
	    					<toolbarbutton label="Löschen"/>
						</toolbar>
					</toolbox>
					<spacer flex="1"/>
					<label id="std-label-anzahl" />
				</hbox>

				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<tree id="treeStudenten" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/student/alle"
						onselect="studentAuswahl();"
						flags="dont-build-content"
						enableColumnDrag="true"
						style="margin:0px;"
				>
					<treecols>
	    				<treecol id="uid" label="UID" flex="1" primary="false"
	    					class="sortDirectionIndicator"
	    					sortActive="true"
	    					sortDirection="ascending"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#uid"  />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="titel" label="Titel" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#titel"				/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="vornamen" label="Vornamen" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#vornamen" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="nachname" label="Nachname" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#nachname" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="geburtsdatum" label="Geburtsdatum" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="semester" label="Sem." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#semester" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="verband" label="Verb." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#verband" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="gruppe" label="Grp." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#gruppe" />
	    				<splitter class="tree-splitter"/>
	    				<treecol id="stg_bezeichnung" label="Studiengang" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" />
	    				<splitter class="tree-splitter"/>
						<treecol id="matrikelnummer" label="Matrikelnummer" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" />
					</treecols>

					<template>
						<rule>
	      					<treechildren>
	       						<treeitem uri="rdf:*">
	         						<treerow>
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#uid"   />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#titel" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#vornamen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#nachname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#semester" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#verband" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#gruppe" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" />
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

				<splitter collapse="after" persist="state">
					<grippy />
				</splitter>

				<!-- ************ -->
				<!-- *  Detail  * -->
				<!-- ************ -->
				<vbox flex="1"  style="overflow:auto;margin:0px;">
					<label value="Details" style="font-size:12pt;font-weight:bold;background:#eeeeee;margin:0px;padding:5px;" />

					<box class="studentDetail"  style="margin-top:10px;" />
				</vbox>
			</vbox>
</overlay>
