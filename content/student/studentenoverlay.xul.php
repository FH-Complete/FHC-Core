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

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentdetailoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentzeugnisoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentkontooverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentbetriebsmitteloverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentiooverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentnotenoverlay.xul.php"?>';
?>
<!DOCTYPE overlay >

<overlay id="StudentenOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentoverlay.js.php" />

			<!-- *************** -->
			<!-- *  Studenten  * -->
			<!-- *************** -->
			<vbox id="studentenEditor" persist="height">
			<popupset>
				<popup id="student-tree-popup">
					<menuitem label="Student aus dieser Gruppe Entfernen" oncommand="StudentGruppeDel();" id="student-tree-popup-gruppedel" hidden="false"/>
				</popup>
			</popupset>
				<hbox>
					<toolbox flex="1">
						<toolbar id="student-nav-toolbar">
						<!--<toolbarbutton id="student-toolbar-neu" label="Neuer Student" oncommand="StudentNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Student neu anlegen" />-->
						<!--<toolbarbutton id="student-toolbar-del" label="Löschen" oncommand="StudentDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Student löschen"/>-->
						<toolbarbutton id="student-toolbar-refresh" label="Aktualisieren" oncommand="StudentTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
						<toolbarbutton id="student-toolbar-buchung" label="Neue Buchung" oncommand="StudentKontoNeu()" disabled="false" tooltiptext="neue Buchung anlegen"/>
						<toolbarbutton id="student-toolbar-zeugnis" label="Zeugnis erstellen" oncommand="StudentCreateZeugnis()" disabled="false" tooltiptext="Zeugnis erstellen"/>
						<toolbarbutton id="student-toolbar-abbrecher" label="-> Abbrecher" oncommand="StudentAddRolle('Abbrecher','0')" disabled="false" tooltiptext="Student zum Abbrecher machen"/>
						<toolbarbutton id="student-toolbar-unterbrecher" label="-> Unterbrecher" oncommand="StudentAddRolle('Unterbrecher','0')" disabled="false" tooltiptext="Student zum Unterbrecher machen"/>
						<toolbarbutton id="student-toolbar-student" label="-> Student" oncommand="StudentUnterbrecherZuStudent()" disabled="false" tooltiptext="Ab/Unterbrecher wieder zum Studenten machen" hidden="true"/>
						<spacer flex="1"/>
						<label id="student-toolbar-label-anzahl"/>
						</toolbar>
					</toolbox>
				</hbox>

				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<tree id="student-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/student/alle"
						onselect="StudentAuswahl();"
						flags="dont-build-content"
						enableColumnDrag="true"
						style="margin:0px;"
						persist="hidden, height"
						ondraggesture="nsDragAndDrop.startDrag(event,studentDDObserver);"
						context="student-tree-popup"
				>
					<treecols>
	    				<treecol id="student-treecol-uid" label="UID" flex="1" primary="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#uid"  onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-titelpre" label="TitelPre" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#titelpre" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-vorname" label="Vorname" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#vorname" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-vornamen" label="Vornamen" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#vornamen" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-nachname" label="Nachname" flex="1" hidden="false"
	    					sortActive="true"
	    					sortDirection="ascending"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#nachname" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-titelpost" label="TitelPost" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#titelpost" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-geburtsdatum" label="Geburtsdatum" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum_iso" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-semester" label="Sem." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#semester" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-verband" label="Verb." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#verband" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-gruppe" label="Grp." flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#gruppe" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-studiengang_kz" label="StudiengangKz" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-matrikelnummer" label="Matrikelnummer" flex="1" hidden="false"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" onclick="StudentTreeSort()"/>
	    				<treecol id="student-treecol-prestudent_id" label="PreStudentID" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#prestudent_id" onclick="StudentTreeSort()"/>
	    				<treecol id="student-treecol-person_id" label="PersonID" flex="1" hidden="true"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#person_id" onclick="StudentTreeSort()"/>
					</treecols>

					<template>
						<rule>
	      					<treechildren>
	       						<treeitem uri="rdf:*">
	         						<treerow>
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#uid"   />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#titelpre" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#vorname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#vornamen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#nachname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#titelpost" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#semester" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#verband" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#gruppe" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#prestudent_id" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#person_id" />
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
					<tabbox id="student-tabbox" flex="3" orient="vertical">
						<tabs orient="horizontal" id="lehrveranstaltung-tabs">
							<tab id="student-tab-detail" label="Details" />
							<tab id="student-tab-prestudent" label="PreStudent" />
							<tab id="student-tab-konto" label="Konto" />
							<tab id="student-tab-zeugnis" label="Zeugnis" />
							<tab id="student-tab-betriebsmittel" label="Betriebsmittel" />
							<tab id="student-tab-io" label="Incoming/Outgoing" />
							<tab id="student-tab-noten" label="Noten" />
							<tab id="student-tab-kontakt" label="Kontakt" />
						</tabs>
						<tabpanels id="student-tabpanels-main" flex="1">
							<vbox id="student-detail"  style="margin-top:10px;" />
							<vbox id="student-prestudent"  style="margin-top:10px;" />
							<vbox id="student-konto"  style="margin-top:10px;" />
							<vbox id="student-zeugnis"  style="margin-top:10px;" />
							<vbox id="student-betriebsmittel"  style="margin-top:10px;" />
							<vbox id="student-io"  style="margin-top:10px;" />
							<vbox id="student-noten"  style="margin-top:10px;" />
							<iframe id="student-kontakt" src="" style="margin-top:10px;" />
						</tabpanels>
					</tabbox>	
				</vbox>
			</vbox>
</overlay>
