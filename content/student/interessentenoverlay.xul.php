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

require_once('../../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-15" standalone="yes" ?>';

echo '<?xul-overlay href="'.APP_ROOT.'content/student/interessentdetailoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/interessentdokumenteoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/interessentkontooverlay.xul.php"?>';
?>
<!DOCTYPE overlay >

<overlay id="InteressentenOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/interessentoverlay.js.php" />

			<!-- ******************* -->
			<!-- *  Interessenten  * -->
			<!-- ******************* -->
			<vbox id="InteressentenEditor" persist="height">
				<hbox>
					<toolbox flex="1">
						<toolbar id="nav-toolbar">
						<toolbarbutton id="interessent-toolbar-neu" label="Neu" oncommand="InteressentNeu()" disabled="false" image="../skin/images/NeuDokument.png" tooltiptext="Interessent neu anlegen" />
						<toolbarbutton id="interessent-toolbar-zubewerber" label="-> Bewerber" oncommand="InteressentzuBewerber()" disabled="false" tooltiptext="Interessent zum Bewerber machen" />
						<toolbarbutton id="interessent-toolbar-zustudent" label="-> Student" oncommand="InteressentzuStudent()" disabled="false" tooltiptext="Bewerber zu Studenten machen" />
						<toolbarbutton id="interessent-toolbar-aufgenommener" label="-> Aufgenommener" oncommand="InteressentAddRolle('Aufgenommener')" disabled="false" tooltiptext="Interessent zum Aufgenommenen machen"/>
						<toolbarbutton id="interessent-toolbar-warteliste" label="-> Warteliste" oncommand="InteressentAddRolle('Wartender')" disabled="false" tooltiptext="Interessent zum Wartenden machen"/>
						<toolbarbutton id="interessent-toolbar-absage" label="-> Abgewiesener" oncommand="InteressentAddRolle('Abgewiesener')" disabled="false" tooltiptext="Interessent zum Abgewiesenen machen"/>
						<toolbarbutton id="interessent-toolbar-buchung" label="Neue Buchung" oncommand="InteressentKontoNeu()" disabled="false" tooltiptext="neue Buchung anlegen"/>
						<toolbarbutton id="interessent-toolbar-refresh" label="Aktualisieren" oncommand="InteressentTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>

						<spacer flex="1"/>
						<label id="interessent-toolbar-label-anzahl"/>
						</toolbar>
					</toolbox>
				</hbox>

				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<tree id="interessent-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/interessent/alle"
						onselect="InteressentAuswahl();"
						flags="dont-build-content"
						enableColumnDrag="true"
						style="margin:0px;"
						persist="hidden, height"
				>
					<treecols>
	    				<treecol id="interessent-treecol-titelpre" label="TitelPre" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#titelpre" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-anrede" label="Anrede" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#anrede" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-vorname" label="Vorname" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#vorname" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-vornamen" label="Vornamen" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#vornamen" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-nachname" label="Nachname" flex="2" hidden="false" primary="true"
	    					sortActive="true"
	    					sortDirection="ascending"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#nachname" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-titelpost" label="TitelPost" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#titelpost" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-geburtsdatum" label="Geburtsdatum" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#geburtsdatum_iso" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-svnr" label="SVNR" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#svnr" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-ersatzkennzeichen" label="ErsatzKz" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#ersazkennzeichen" onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-geschlecht" label="Geschlecht" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#geschlecht"  onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-status" label="Status" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#status"  onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-studiengang_kz" label="StudiengangKz" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#studiengang_kz"  onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-prestudent_id" label="PreStudentID" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#prestudent_id"  onclick="InteressentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="interessent-treecol-person_id" label="PersonID" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/interessent/rdf#person_id"  onclick="InteressentTreeSort()"/>
					</treecols>

					<template>
						<rule>
	      					<treechildren>
	       						<treeitem uri="rdf:*">
	         						<treerow>
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#titelpre" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#anrede" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#vorname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#vornamen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#nachname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#titelpost" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#geburtsdatum" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#svnr" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#ersatzkennzeichen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#geschlecht" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#status" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#studiengang_kz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#prestudent_id" />
	           							<treecell label="rdf:http://www.technikum-wien.at/interessent/rdf#person_id" />
	         						</treerow>
	       						</treeitem>
	      					</treechildren>
	      				</rule>
  					</template>
				</tree>

				<splitter collapse="after" persist="state">
					<grippy />
				</splitter>

				<!-- ************ -->
				<!-- *  Detail  * -->
				<!-- ************ -->
				<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
					<tabbox id="interessent-tabbox" flex="3" orient="vertical">
						<tabs orient="horizontal" id="interessent-tabs">
							<tab id="interessent-tab-detail" label="Details" />
							<tab id="interessent-tab-prestudent" label="PreStudent" />
							<tab id="interessent-tab-dokumente" label="Dokumente" />
							<tab id="interessent-tab-konto" label="Konto" />
						</tabs>
						<tabpanels id="interessent-tabpanels-main" flex="1">
							<vbox id="interessent-detail"  style="margin-top:10px;" />
							<vbox id="interessent-prestudent"  style="margin-top:10px;" />
							<vbox id="interessent-dokumente"  style="margin-top:10px;" />
							<vbox id="interessent-konto"  style="margin-top:10px;" />
						</tabpanels>
					</tabbox>				
				</vbox>
			</vbox>
</overlay>
