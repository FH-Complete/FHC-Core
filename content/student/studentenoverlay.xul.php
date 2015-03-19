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

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentdetailoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentzeugnisoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentkontooverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentiooverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentnotenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentpruefungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentanrechnungenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentabschlusspruefungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentprojektarbeitoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentgruppenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/interessentdokumenteoverlay.xul.php"?>';

if(isset($_GET['xulapp']))
	$xulapp=$_GET['xulapp'];
else 
	$xulapp='';
?>
<!DOCTYPE overlay >

<overlay id="StudentenOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/interessentoverlay.js.php" />

			<!-- *************** -->
			<!-- *  Studenten  * -->
			<!-- *************** -->
			<vbox id="studentenEditor">
			<popupset>
				<menupopup id="student-tree-popup" onpopupshown="">
					<menuitem label="Student aus dieser Gruppe entfernen" oncommand="StudentGruppeDel();" id="student-tree-popup-gruppedel" hidden="false"/>
					<menuitem label="EMail senden (intern)" oncommand="StudentSendMail();" id="student-tree-popup-mail" hidden="false"/>
					<menuitem label="EMail senden (privat)" oncommand="StudentSendMailPrivat();" id="student-tree-popup-mailprivat" hidden="false"/>
					<menuseparator />
					<menuitem label="Personendetails anzeigen" oncommand="StudentShowPersonendetails();" id="student-tree-popup-personendetails" hidden="false"/>
					<!--					
					<menuitem label="Interessenten löschen" oncommand="StudentDeleteInteressent();" id="student-tree-popup-deleteinteressent" hidden="false"/>
					-->
				</menupopup>
			</popupset>
				<hbox>
					<toolbox flex="1">
						<toolbar id="student-nav-toolbar">
						<?php 
						if($xulapp!='tempus')
						{
						?>
							<toolbarbutton id="interessent-toolbar-neu" label="Neu" oncommand="InteressentNeu()" disabled="false" image="../skin/images/NeuDokument.png" tooltiptext="Interessent neu anlegen" />

							<toolbarbutton id="student-toolbar-buchung" label="Neue Buchung" oncommand="StudentKontoNeu()" disabled="false" tooltiptext="neue Buchung anlegen"/>
							
							<toolbarbutton label="Status ändern " id="student-toolbar-status" type="menu">							
						      <menupopup id="student-status-menu-popup" >
								    <menuitem id="student-toolbar-abbrecher" label="-> Abbrecher" oncommand="StudentAddRolle('Abbrecher','0')" disabled="false" tooltiptext="Student zum Abbrecher machen" hidden="true"/>
									<menuitem id="student-toolbar-unterbrecher" label="-> Unterbrecher" oncommand="StudentAddRolle('Unterbrecher','0')" disabled="false" tooltiptext="Student zum Unterbrecher machen" hidden="true"/>
									<menuitem id="student-toolbar-student" label="-> Student" oncommand="StudentUnterbrecherZuStudent()" disabled="false" tooltiptext="Ab/Unterbrecher wieder zum Studenten machen" hidden="true"/>
									<menuitem id="student-toolbar-diplomand" label="-> Diplomand" oncommand="StudentAddRolle('Diplomand')" disabled="false" tooltiptext="Studenten zum Diplomanden machen" hidden="true"/>
									<menuitem id="student-toolbar-absolvent" label="-> Absolvent" oncommand="StudentAddRolle('Absolvent')" disabled="false" tooltiptext="Studenten zum Absolventen machen" hidden="true"/>
											
									<menuitem id="interessent-toolbar-zubewerber" label="-> Bewerber" oncommand="InteressentzuBewerber()" disabled="false" tooltiptext="Interessent zum Bewerber machen" hidden="true"/>
									<menuitem id="interessent-toolbar-aufgenommener" label="-> Aufgenommener" oncommand="InteressentAddRolle('Aufgenommener')" disabled="false" tooltiptext="Interessent zum Aufgenommenen machen" hidden="true"/>
									<menuitem id="interessent-toolbar-zustudent" label="-> Student" oncommand="InteressentzuStudent()" disabled="false" tooltiptext="Bewerber zu Studenten machen" hidden="true"/>									
									<menuitem id="interessent-toolbar-warteliste" label="-> Warteliste" oncommand="InteressentAddRolle('Wartender')" disabled="false" tooltiptext="Interessent zum Wartenden machen" hidden="true"/>
									<menuitem id="interessent-toolbar-absage" label="-> Absage" oncommand="InteressentAddRolle('Abgewiesener')" disabled="false" tooltiptext="Interessent zum Absager machen" hidden="true"/>    
						      </menupopup>
						    </toolbarbutton>
														
							<toolbarbutton id="student-toolbar-export" label="Export" oncommand="StudentExport()" disabled="false" image="../skin/images/ExcelIcon.png" tooltiptext="Daten ins Excel Exportieren"/>
						<?php
						}
						?>
							<toolbarbutton id="student-toolbar-refresh" label="Aktualisieren" oncommand="StudentTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
							<textbox id="student-toolbar-textbox-suche" control="student-toolbar-button-search" onkeypress="StudentSearchFieldKeyPress(event)" />
							<button id="student-toolbar-button-search" oncommand="StudentSuche()" label="Suchen"/>
						<?php
						if($xulapp!='tempus')
						{
						?>
							<toolbarbutton label="Filter " id="student-toolbar-filter" type="menu">							
						      <menupopup id="student-filter-menu-popup" >
								    <menuitem id="student-toolbar-filter-dokumente" label="fehlende Dokumente" oncommand="InteressentDokumenteFilter()" disabled="false" tooltiptext="Liste aller Studenten mit Fehlenden Dokumenten"/>
									<menuitem id="student-toolbar-filter-offenebuchungen" label="offene Buchungen" oncommand="StudentKontoFilterStudenten('konto')" disabled="false" tooltiptext="Liste aller Studenten mit offenen Buchungen"/>
									<menuitem id="student-toolbar-filter-studiengebuehr" label="nicht gebuchte Studiengebuehr" oncommand="StudentKontoFilterStudenten('studiengebuehr')" disabled="false" tooltiptext="Liste aller Studenten die noch nicht mit Studienbebuehr belastet wurden" />
									<menuitem id="student-toolbar-filter-zgvohnedatum" label="ZGV eingetragen ohne Datum" oncommand="StudentKontoFilterStudenten('zgvohnedatum')" disabled="false" tooltiptext="Liste aller Studenten die ZGV eingetragen haben bei denen aber kein ZGV Datum gesetzt ist" />
						      </menupopup>
						    </toolbarbutton>
						<?php
						}
						?>
							<spacer flex="1"/>
							<label id="student-toolbar-label-anzahl"/>
						</toolbar>
					</toolbox>
				</hbox>

				<!-- ************* -->
				<!-- *  Auswahl  * -->
				<!-- ************* -->
				<vbox flex="1">
				<vbox>
				<tree id="student-tree" seltype="multi" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/student/alle"
						<?php echo ($xulapp!='tempus'?'onselect="StudentAuswahl();"':'') ?>						
						flags="dont-build-content"
						enableColumnDrag="true"
						style="margin:0px; height:150px"
						persist="hidden, height"
						ondraggesture="nsDragAndDrop.startDrag(event,studentDDObserver);"
						context="student-tree-popup"
				>
					<treecols>
	    				<treecol id="student-treecol-uid" label="UID" flex="1" primary="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#uid"  onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-titelpre" label="TitelPre" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#titelpre" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
    					<treecol id="student-treecol-nachname" label="Nachname" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					sortActive="true"
	    					sortDirection="ascending"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#nachname" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-vorname" label="Vorname" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#vorname" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-vornamen" label="Vornamen" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#vornamen" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-titelpost" label="TitelPost" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#titelpost" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-svnr" label="SVNR" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#svnr" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-ersatzkennzeichen" label="Ersatzkennzeichen" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#ersatzkennzeichen" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-geburtsdatum" label="Geburtsdatum" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum_iso" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-geschlecht" label="Geschlecht" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#geschlecht" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-semester" label="Sem." flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#semester" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-verband" label="Verb." flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#verband" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-gruppe" label="Grp." flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#gruppe" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-studiengang" label="Studiengang" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#studiengang" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-studiengang_kz" label="Studiengang_kz" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-matrikelnummer" label="Personenkennzeichen" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-prestudent_id" label="PreStudentID" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#prestudent_id" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-person_id" label="PersonID" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#person_id" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-status" label="Status" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#status" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-mail_privat" label="EMail (Privat)" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#mail_privat" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-mail_intern" label="EMail (Intern)" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#mail_intern" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-anmerkungen" label="Anmerkungen" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#anmerkungen" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-anmerkungpre" label="AnmerkungPre" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#anmerkungpre" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-orgform" label="OrgForm" flex="1" hidden="false" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#orgform" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-aufmerksamdurch" label="Aufmerksamdurch" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#orgform" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-punkte" label="Gesamtpunkte" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#punkte" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-rt_punkte1" label="Punkte1" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#punkte1" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-rt_punkte2" label="Punkte2" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#punkte2" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-rt_punkte3" label="Punkte3" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#punkte3" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
	    				<treecol id="student-treecol-dual" label="Dual" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#dual_bezeichnung" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-matrnr" label="Matrikelnummer" flex="1" hidden="true" persist="hidden, width, ordinal"
	    					class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#matr_nr" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-studienplan" label="Studienplan" flex="1" hidden="false" persist="hidden, width, ordinal"
						class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#studienplan_bezeichnung" onclick="StudentTreeSort()"/>
					<splitter class="tree-splitter"/>
						<treecol id="student-treecol-mentor" label="Mentor" flex="1" hidden="true" persist="hidden, width, ordinal"
						class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#mentor" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
						<treecol id="student-treecol-aktiv" label="Aktiv" flex="1" hidden="false" persist="hidden, width, ordinal"
						class="sortDirectionIndicator"
	    					sort="rdf:http://www.technikum-wien.at/student/rdf#aktiv" onclick="StudentTreeSort()"/>
	    				<splitter class="tree-splitter"/>
					</treecols>

					<template>
						<rule>
	      					<treechildren>
	       						<treeitem uri="rdf:*">
	         						<treerow>
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#uid"   />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#titelpre" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#nachname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#vorname" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#vornamen" />	           							
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#titelpost" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#svnr" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#ersatzkennzeichen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#geburtsdatum" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#geschlecht" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#semester" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#verband" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#gruppe" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#studiengang" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#studiengang_kz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#matrikelnummer" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#prestudent_id" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#person_id" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#status" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#mail_privat" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#mail_intern" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#anmerkungen" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#anmerkungpre" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#orgform" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#aufmerksamdurch_kurzbz" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#punkte" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#punkte1" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#punkte2" />
									<treecell label="rdf:http://www.technikum-wien.at/student/rdf#punkte3" />
	           							<treecell label="rdf:http://www.technikum-wien.at/student/rdf#dual_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/student/rdf#matr_nr" />
									<treecell label="rdf:http://www.technikum-wien.at/student/rdf#studienplan_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/student/rdf#mentor" />
									<treecell label="rdf:http://www.technikum-wien.at/student/rdf#aktiv" />
	         						</treerow>
	       						</treeitem>
	      					</treechildren>
	      				</rule>
  					</template>
				</tree>
				</vbox>
				<?php
				if($xulapp!='tempus')
				{
				?>
				<splitter collapse="after" persist="state">
					<grippy />
				</splitter>

				<!-- ************ -->
				<!-- *  Detail  * -->
				<!-- ************ -->
				<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
					<tabbox id="student-tabbox" flex="3" orient="vertical">
						<tabs orient="horizontal" id="student-content-tabs">
							<tab id="student-tab-detail" label="Details" />
							<tab id="student-tab-kontakt" label="Kontakt" />
							<tab id="student-tab-prestudent" label="PreStudent" />
							<tab id="student-tab-dokumente" label="Dokumente" />
							<tab id="student-tab-konto" label="Konto" />
							<tab id="student-tab-betriebsmittel" label="Betriebsmittel" />
							<tab id="student-tab-io" label="In/Out" />
							<tab id="student-tab-noten" label="Noten" />
							<tab id="student-tab-zeugnis" label="Archiv" />
							<tab id="student-tab-pruefung" label="Prüfung" />
							<?php 
                            if($rechte->isBerechtigt('student/anrechnung'))
                                echo '<tab id="student-tab-anrechnungen" label="Anrechnungen" />';
                            ?>
							<tab id="student-tab-abschlusspruefung" label="AbschlussPrüfung" />
							<tab id="student-tab-projektarbeit" label="Projektarbeit" />
							<tab id="student-tab-gruppen" label="Gruppen" onclick="StudentGruppenLoadData();"/>
							<tab id="student-tab-funktionen" label="Funktionen" onclick="StudentFunktionIFrameLoad();"/>
							<tab id="student-tab-notizen" label="Notizen"/>
						</tabs>
						<tabpanels id="student-tabpanels-main" flex="1">
							<vbox id="student-detail"  style="margin-top:10px;" />
							<iframe id="student-kontakt" src="" style="margin-top:10px;" />
							<vbox id="student-prestudent"  style="margin-top:10px;" />
							<vbox id="interessent-dokumente"  style="margin-top:10px;" />
							<vbox id="student-konto"  style="margin-top:10px;" />
							<iframe id="student-betriebsmittel" src="" style="margin-top:10px;" />
							<vbox id="student-io"  style="margin-top:10px;" />
							<vbox id="student-noten"  style="margin-top:10px;" />
							<vbox id="student-zeugnis"  style="margin-top:10px;" />
							<vbox id="student-pruefung"  style="margin-top:10px;" />
							<?php 
                            if($rechte->isBerechtigt('student/anrechnung'))
                                echo '<vbox id="student-anrechnungen"  style="margin-top:10px;" />';
                            ?>
							<vbox id="student-abschlusspruefung"  style="margin-top:10px;" />
							<vbox id="student-projektarbeit"  style="margin-top:10px;" />
							<vbox id="student-gruppen"  style="margin-top:10px;" />
							<iframe id="student-funktionen" src="" style="margin-top:10px;" />
							<vbox id="student-box-notiz">
								<box class="Notiz" flex="1" id="student-box-notizen"/>
							</vbox>
						</tabpanels>
					</tabbox>
				</vbox>
				<?php
				}
				?>
			</vbox>
			</vbox>
</overlay>
