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
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/phrasen.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';

// Vertragsdetails: Anzeige wird über config Eintrag bestimmt
$is_hidden = (!defined('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN') || FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN == true) ? 'false' : 'true';

$sprache = getSprache();
$p = new phrasen($sprache);
?>

<overlay id="LehrveranstaltungDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- *************************** LEHREINHEIT DETAILS ************************* -->
<vbox id="lehrveranstaltung-detail" style="margin:0px;" >
	<popupset>
		<menupopup id="lehrveranstaltung-detail-gruppe-tree-popup">
			<menuitem label="Entfernen" oncommand="LeGruppeDel();" />
			<?php
			if($rechte->isBerechtigt('lv-plan/gruppenentfernen'))
			{
				echo '<menuseparator />';
				echo '<menuitem label="Stunden aus LV-Plan entfernen" oncommand="LeGruppeDelLVPlan();" />';
			}
			?>
		</menupopup>
	</popupset>
	<popupset>
		<menupopup id="lehrveranstaltung-detail-gruppe-direkt-tree-popup">
			<menuitem label="Entfernen" oncommand="LeGruppeDirektDel();" />
		</menupopup>
	</popupset>
	<popupset>
		<menupopup id="lehrveranstaltung-lektor-tree-popup">
			<menuitem id="lehrveranstaltung-lektor-tree-popup-label" label="Entfernen" oncommand="LeMitarbeiterDel();" />
			<?php
			if($rechte->isBerechtigt('lv-plan/lektorentfernen'))
			{
				echo '<menuseparator />';
				echo '<menuitem label="Stunden aus LV-Plan entfernen" oncommand="LeLektorDelLVPlan();" />';
			}
			?>
		</menupopup>
	</popupset>
	<popupset>
		<menupopup id="lehrveranstaltung-lvangebot-tree-popup">
			<menuitem label="Entfernen" oncommand="LvAngebotGruppeDel();" />
		</menupopup>
	</popupset>
	<!-- Hidden Fields -->
	<vbox hidden="true">
		<grid flex="1" style="padding:5px;">
		<columns  >
			<column  />
			<column style="min-width:240px" />
			<column />
			<column style="min-width:240px" />
	  	</columns>
	  	<rows>
			<row >
	  			<label value="NEW" />
				<checkbox id="lehrveranstaltung-detail-checkbox-new" hidden="false"/>
				<label value="Lehreinheit_id" />
				<textbox id="lehrveranstaltung-detail-textbox-lehreinheit_id" hidden="false"/>
			</row>
			<row >
	  			<label value="LVNR" />
	  			<textbox id="lehrveranstaltung-detail-textbox-lvnr" maxlength="20" disabled="true" />


			</row>
			<!--<row>
				<label value="Lehrveranstaltung" />
	  			<textbox id="lehrveranstaltung-detail-textbox-lehrveranstaltung" maxlength="20" disabled="true"/>
			</row>-->
		</rows>
		</grid>
	</vbox>
	<hbox>
	<groupbox orientation="horizontal" flex="1">
		<caption id="lehrveranstaltung-detail-groupbox-caption" label="Details" />
		<!--Details-->
		<hbox flex="1">
		<grid datasources="rdf:null"
		      ref="http://www.technikum-wien.at/tempus/lva/liste"
			  style="padding:5px;">
			<columns  >
				<column  />
				<column style="min-width:240px" />
				<column />
				<column style="min-width:240px" />
		  	</columns>
		  	<rows>
				<row>
					<label value="LV-ID" <?php echo ($rechte->isBerechtigt('lehre/lehrveranstaltung',null,'suid'))?'':'hidden="true"'; ?>/>
		  			<textbox id="lehrveranstaltung-detail-textbox-lehrveranstaltung" disabled="true" maxlength="20" <?php echo ($rechte->isBerechtigt('lehre/lehrveranstaltung',null,'suid'))?'':'hidden="true"'; ?>/>
					<label value="Gewicht" <?php echo (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)?'':'hidden="true"'; ?>/>
					<textbox id="lehrveranstaltung-detail-textbox-gewicht" disabled="true" maxlength="4" <?php echo (defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)?'':'hidden="true"'; ?>/>

				</row>
				<row>
		  			<label value="Lehrfach" />
					<menulist id="lehrveranstaltung-detail-menulist-lehrfach" disabled="true"
					          datasources="rdf:null" flex="1"
					          ref="http://www.technikum-wien.at/lehrveranstaltung/liste"  >
						<template>
							<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#lehrveranstaltung_id"
						              label="rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#bezeichnung ( rdf:http://www.technikum-wien.at/lehrveranstaltung/rdf#oe_kurzbz )"
									  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
					<label value="Lehrform" />
					<menulist id="lehrveranstaltung-detail-menulist-lehrform" disabled="true"
					          datasources="<?php echo APP_ROOT ?>rdf/lehrform.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/lehrform/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz rdf:http://www.technikum-wien.at/lehrform/rdf#bezeichnung"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
		  	  	</row>

				<row>
		  			<label value="Sprache" />
					<menulist id="lehrveranstaltung-detail-menulist-sprache"
					          datasources="<?php echo APP_ROOT; ?>rdf/sprache.rdf.php" flex="1"
							  ref="http://www.technikum-wien.at/sprachen/liste" disabled="true">
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
								          label="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
										  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
					<label value="UNR" />
		  	    	<textbox id="lehrveranstaltung-detail-textbox-unr" disabled="true" tooltiptext="Nur aendern wenn man weiss was man tut!"/>
				</row>

				<row>
					<label value="Studiensemester" />
					<menulist id="lehrveranstaltung-detail-menulist-studiensemester" disabled="true"
							  datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php" flex="0"
							  ref="http://www.technikum-wien.at/studiensemester/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
										  label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
										  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
					<label value="Lehre" />
					<checkbox id="lehrveranstaltung-detail-checkbox-lehre" disabled="true"/>
				</row>

				<row>
		  	    	<label value="Raumtyp" />
		  			<menulist id="lehrveranstaltung-detail-menulist-raumtyp" disabled="true"
		  			          datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/raumtyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
								          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz rdf:http://www.technikum-wien.at/raumtyp/rdf#beschreibung"
										  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
		  			<label value="Raumtyp alternativ" />
		  			<menulist id="lehrveranstaltung-detail-menulist-raumtypalternativ" disabled="true"
		  					  datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
				              ref="http://www.technikum-wien.at/raumtyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
								          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz rdf:http://www.technikum-wien.at/raumtyp/rdf#beschreibung"
										  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
		  		</row>

				<row>
					<label value=" " />
					<spacer />
					<spacer />
					<spacer />
				</row>

		  		<hbox>
					<label value="Start KW " />
					<textbox id="lehrveranstaltung-detail-textbox-startkw" width="50" disabled="true"/>
					<label value="     Stundenblockung " />
					<textbox id="lehrveranstaltung-detail-textbox-stundenblockung" width="50" disabled="true" />
					<label value="     Wochenrhythmus " />
					<textbox id="lehrveranstaltung-detail-textbox-wochenrythmus" width="50" disabled="true"/>
				</hbox>

			</rows>
		</grid>

		<vbox flex="1">
			<label value="<?php echo $p->t('lehrveranstaltung/DetailAnmerkung'); ?>" />
			<textbox id="lehrveranstaltung-detail-textbox-anmerkung" rows="5" multiline="true" disabled="true"/>

			<hbox>
				<button id="lehrveranstaltung-detail-button-save" label="speichern" oncommand="LeDetailSave();" disabled="true"/>
				<spacer flex="1" />
			</hbox>
		</vbox>
		</hbox>
	</groupbox>
	</hbox>

	<!-- ************* GRUPPEN ************** -->
	<vbox flex="1" id="lehrveranstaltung-detail-gruppen-box">
		<hbox flex="7">
			<vbox flex="2">
				<label id="lehrveranstaltung-detail-label-lehreinheitgruppe" value="Gruppen" />
				<tree id="lehrveranstaltung-detail-tree-lehreinheitgruppe" seltype="single" hidecolumnpicker="false" flex="1" disabled="false"
					  datasources="rdf:null"
					  ref="http://www.technikum-wien.at/lehreinheitgruppe/liste"
					  flags="dont-build-content"
					  style="border: 1px solid black; min-height: 100px;"
					  ondragdrop="nsDragAndDrop.drop(event,LeLvbgrpDDObserver)"
					  ondrop="nsDragAndDrop.drop(event,LeLvbgrpDDObserver)"
					  ondragover="nsDragAndDrop.dragOver(event,LeLvbgrpDDObserver)"
						  ondragenter="nsDragAndDrop.dragEnter(event,LeLvbgrpDDObserver)"
					  ondragexit="nsDragAndDrop.dragExit(event,LeLvbgrpDDObserver)"
					  context="lehrveranstaltung-detail-gruppe-tree-popup"
					  onkeypress="LvDetailGruppenTreeKeyPress(event)"
				>
					<treecols>
						<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-bezeichnung" label="Bezeichnung" flex="4" hidden="false"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#bezeichnung" />
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-beschreibung" label="Beschreibung" flex="2" hidden="true"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#beschreibung" />
						<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-studiengang" label="Studiengang" flex="2" hidden="true"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#studiengang_bezeichnung" />
						<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-semester" label="Semester" flex="2" hidden="true"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#semester" />
						<treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-lehreinheitgruppe_id" label="ID" flex="2" hidden="true"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#lehreinheitgruppe_id" />
						 <treecol id="lehrveranstaltung-lehreinheitgruppe-treecol-verplant" label="verplant" flex="1" hidden="false"  persist="hidden, width, ordinal"
									 class="sortDirectionIndicator"
									 sort="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#verplant" />
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#beschreibung"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#studiengang_bezeichnung"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#semester"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#lehreinheitgruppe_id"   />
										<treecell src="../skin/images/verplant_rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#verplant^.png"   />
					 				</treerow>
					 			</treeitem>
					 		</treechildren>
					 	</rule>
					</template>
				</tree>
			</vbox>
			<spacer flex="1"/>
			<vbox flex="2">
				<label id="lehrveranstaltung-detail-label-lehreinheitgruppe-direkt" value="Direkt zugeordnete Personen" />
				<tree id="lehrveranstaltung-detail-tree-lehreinheitgruppe-direkt" seltype="single" hidecolumnpicker="false" flex="1" disabled="false"
					  datasources="rdf:null"
					  ref="http://www.technikum-wien.at/lehreinheitdirekt"
					  flags="dont-build-content"
					  style="border: 1px solid black; min-height: 100px;"
					  context="lehrveranstaltung-detail-gruppe-direkt-tree-popup"
				>
					<treecols>
						<treecol id="lehrveranstaltung-lehreinheitgruppedirekt-treecol-uid" label="UID" flex="4" hidden="false"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#uid" />
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lehreinheitgruppedirekt-treecol-vorname" label="Vorname" flex="2" hidden="false"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#vorname" />
						<treecol id="lehrveranstaltung-lehreinheitgruppedirekt-treecol-nachname" label="Nachname" flex="2" hidden="false"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#nachname" />
						 <treecol id="lehrveranstaltung-lehreinheitgruppedirekt-treecol-gruppe_kurzbz" label="Gruppe" flex="2" hidden="true"  persist="hidden, width, ordinal"
								 class="sortDirectionIndicator"
								 sort="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#gruppe_kurzbz" />
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#uid"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#vorname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#nachname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitdirekt/rdf#gruppe_kurzbz"   />
									</treerow>
								</treeitem>
							</treechildren>
						</rule>
					</template>
				</tree>
				<hbox>
					<menulist id="lehrveranstaltung-lehreinheitgruppedirekt-textbox-user"
						editable="true" datasources="rdf:null" flex="1"
						ref="http://www.technikum-wien.at/benutzer"
						oninput="LeGruppeDirektLoad(this)">
							<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/benutzer/rdf#uid"
										label="rdf:http://www.technikum-wien.at/benutzer/rdf#nachname rdf:http://www.technikum-wien.at/benutzer/rdf#vorname rdf:http://www.technikum-wien.at/benutzer/rdf#uid rdf:http://www.technikum-wien.at/benutzer/rdf#studiengang rdf:http://www.technikum-wien.at/benutzer/rdf#semester"
										uri="rdf:*"/>
								</menupopup>
							</template>
					</menulist>
					<button label="zuordnen" oncommand="LeGruppeDirektAdd()" />
				</hbox>
			</vbox>
			<spacer flex="2"/>
		</hbox>
		<spacer flex="1"/>
	</vbox>

</vbox>

<!-- ************************** LEKTORZUTEILUNG ********************** -->
<vbox id="lehrveranstaltung-lektorzuteilung">
	<hbox flex="1" style="padding: 10px">
		<vbox width="250">
			<hbox flex="1">
				<tree id="lehrveranstaltung-detail-tree-lehreinheitmitarbeiter" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null"
						ref="http://www.technikum-wien.at/lehreinheitmitarbeiter/liste"
						onselect="LeMitarbeiterAuswahl();" flags="dont-build-content"
						style="border: 1px solid black;"
						ondragdrop="nsDragAndDrop.drop(event,LeLektorDDObserver)"
						ondrop="nsDragAndDrop.drop(event,LeLektorDDObserver)"
						ondragover="nsDragAndDrop.dragOver(event,LeLektorDDObserver)"
						ondragenter="nsDragAndDrop.dragEnter(event,LeLektorDDObserver)"
						ondragexit="nsDragAndDrop.dragExit(event,LeLektorDDObserver)"
						context="lehrveranstaltung-lektor-tree-popup"
						onkeypress="LvDetailMitarbeiterTreeKeyPress(event)"
				>
					<treecols>
						<treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-nachname" label="Nachname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname"/>
					    <splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-vorname" label="Vorname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname"/>
					    <splitter class="tree-splitter"/>
					    <treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-mitarbeiter_uid" label="UID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid"/>
					    <splitter class="tree-splitter"/>
					    <treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-lehreinheit_id" label="LehreinheitID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id"/>
					    <splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lehreinheitmitarbeiter-treecol-verplant" label="Verplant" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#verplant"/>
					    <splitter class="tree-splitter"/>
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell properties="Lektor_rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname"   />
										<treecell properties="Lektor_rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname"   />
										<treecell properties="Lektor_rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid"   />
										<treecell properties="Lektor_rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id"   />
										<treecell src="../skin/images/verplant_rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#verplant^.png"   />
					 				</treerow>
					 			</treeitem>
					 		</treechildren>
					 	</rule>
				  	</template>
				</tree>
				<spacer />
			</hbox>
		</vbox>

		<vbox>
		<vbox>
		<groupbox>
			<caption label="LektorInnendaten" />
			<hbox flex="1" style="padding: 10px;">
			<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-lehreinheit_id" hidden="true"/>
			<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-mitarbeiter_uid" hidden="true"/>
			<grid align="end" flex="1"
			      flags="dont-build-content"
				  enableColumnDrag="true">
				<columns>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
				</columns>

				<rows>
					<row>
						<label align="end" control="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz" value="Lehrfunktion:"/>
						<menulist id="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lehrfunktion_kurzbz" disabled="true" oncommand="LeMitarbeiterValueChanged();"
		    		              datasources="<?php echo APP_ROOT; ?>rdf/lehrfunktion.rdf.php"
				                  ref="http://www.technikum-wien.at/lehrfunktion/liste" flex="1">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"
					                         value="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"/>
					            </menupopup>
					         </template>
				   		 </menulist>
				   		 <label align="end" control="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor" value="LektorIn:"/>
						 <menulist id="lehrveranstaltung-lehreinheitmitarbeiter-menulist-lektor" disabled="true" oncommand="LeMitarbeiterLektorChange(); LeMitarbeiterValueChanged();"
	    		                   datasources="<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php"
			                       ref="http://www.technikum-wien.at/mitarbeiter/_alle" flex="1">
				         <template>
				            <menupopup>
				               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
				                         value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
				            </menupopup>
				         </template>
			   		 	</menulist>
						<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung" value="<?php echo $p->t('lehrveranstaltung/LehreinheitmitarbeiterAnmerkung'); ?>"/>
						<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-anmerkung" disabled="true" maxlength="255" width="300" oninput="LeMitarbeiterValueChanged();"/>
					</row>
			    	<row>
    					<label id="lehrveranstaltung-lehreinheitmitarbeiter-label-semesterstunden" control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden" value="Semesterstunden: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-semesterstunden" disabled="true" maxlength="6" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten(); if(parseInt(this.value)) document.getElementById('lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden').value= parseInt(this.value)"/>
    					<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden" value="Planstunden: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-planstunden" disabled="true" maxlength="3" flex="1" oninput="LeMitarbeiterValueChanged();"/>
    				</row>
			    	<row>
			    		<label id="lehrveranstaltung-lehreinheitmitarbeiter-label-stundensatz" control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz" value="Stundensatz: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-stundensatz" disabled="true" maxlength="6" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten()"/>
						<label control="lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor" hidden="true" value="Faktor: "/>
    					<textbox id="lehrveranstaltung-lehreinheitmitarbeiter-textbox-faktor" hidden="true" disabled="true" maxlength="3" flex="1" oninput="LeMitarbeiterValueChanged();LeMitarbeiterGesamtkosten()"/>
						<label control="lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden" value="BIS-Melden: "/>
    					<checkbox id="lehrveranstaltung-lehreinheitmitarbeiter-checkbox-bismelden" disabled="true" flex="1" oncommand="LeMitarbeiterValueChanged();"/>
					</row>
					<row>
						<label value='Gesamtkosten:' />
    					<label id="lehrveranstaltung-lehreinheitmitarbeiter-label-gesamtkosten" value='' />
						<spacer />
						<button label="Speichern" disabled="true" id="lehrveranstaltung-lehreinheitmitarbeiter-button-save" oncommand="LeMitarbeiterSave();"/>
						<spacer />
						<spacer />
					</row>
    			</rows>
    			</grid>
			</hbox>
		</groupbox>
		</vbox>

        <!-- Vertragsdetails: Anzeige wird ueber config Eintrag bestimmt -->
        <vbox>
            <groupbox id="lehrveranstaltung-lehreinheitmitarbeiter-groupbox-vertragsdetails" hidden="<?php echo $is_hidden ?>">
                <caption label="Vertragsdetails" />
                <grid style="overflow:auto; padding:10px;" >
                <columns>
                    <column/>
                    <column/>
                    <column/>
                </columns>
                <rows>
                    <label id="lehrveranstaltung-lehreinheitmitarbeiter-label-vertrag_id" hidden="true" value=""/>
                    <row>
                        <label value="Vertragsstatus:"/>
                        <label id="lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstatus" value="" readonly="true" maxlength="8" size="6"/>
                        <button label="Stornieren" disabled="true" id="lehrveranstaltung-lehreinheitmitarbeiter-button-vertrag-stornieren"
                                oncommand="VertragStornieren();" flex="1"/>
                    </row>
                    <row>
                        <label value="Vertragsdetails lt. Urfassung" style="margin-bottom: 10px;"/>
                    </row>
                    <row>
                        <label value="Semesterstunden:" class="indent"/>
                        <label id="lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstunden" value="" readonly="true"/>
                    </row>
                    <row>
                        <label value="Studiensemester:" class="indent"/>
                        <label id="lehrveranstaltung-lehreinheitmitarbeiter-label-vertragsstunden_studiensemester_kurzbz" value="" readonly="true"/>
                    </row>
                </rows>
                </grid>
            </groupbox>
        </vbox>

		</vbox>
	</hbox>
</vbox>

<!-- *************************** LV-ANGEBOT ************************* -->
<vbox id="lehrveranstaltung-lvangebot" style="margin:0px;" >
	<hbox style="padding: 10px;">
		<vbox width="600">
			<hbox flex="1">
				<tree id="lehrveranstaltung-lvangebot-tree-gruppen" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null"
						ref="http://www.technikum-wien.at/lvangebot/liste"
						onselect="LvAngebotGruppeAuswahl();" flags="dont-build-content"
						style="border: 1px solid black;"
						context="lehrveranstaltung-lvangebot-tree-popup"
						onkeypress="LvAngebotTreeKeyPress(event)"
				>
					<treecols>
						<treecol id="lehrveranstaltung-lvangebot-treecol-lvangebot_id" label="Lvangebot_id" flex="2" hidden="true"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#lvangebot_id"/>
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lvangebot-treecol-gruppe" label="Gruppe" flex="2" hidden="false"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#gruppe_kurzbz"/>
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lvangebot-treecol-plaetze_inc" label="Plätze Incoming" flex="2" hidden="false"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#plaetze_inc"/>
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lvangebot-treecol-plaetze_gesamt" label="Plätze gesamt" flex="2" hidden="false"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#plaetze_gesamt"/>
						<splitter class="tree-splitter"/>
						<treecol id="lehrveranstaltung-lvangebot-treecol-anmeldefenster_start" label="Anmeldefenster Start" flex="2" hidden="false"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#anmeldefenster_start"/>
						<treecol id="lehrveranstaltung-lvangebot-treecol-anmeldefenster_ende" label="Anmeldefenster Ende" flex="2" hidden="false"
							class="sortDirectionIndicator"
							sort="rdf:http://www.technikum-wien.at/lvangebot/rdf#anmeldefenster_ende"/>
						<splitter class="tree-splitter"/>
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#lvangebot_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#gruppe_kurzbz"/>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#plaetze_inc"/>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#plaetze_gesamt"/>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#anmeldefenster_start"/>
										<treecell label="rdf:http://www.technikum-wien.at/lvangebot/rdf#anmeldefenster_ende"/>
									</treerow>
								</treeitem>
							</treechildren>
						</rule>
					</template>
				</tree>
				<spacer />
			</hbox>
		</vbox>
		<vbox>
			<hbox>
				<button id="lehrveranstaltung-lvangebot-button-new" label="neuer Eintrag" oncommand="LvAngebotNew();" width="130"/>
			</hbox>
			<groupbox orientation="horizontal">
				<caption id="lehrveranstaltung-lvangebot-groupbox-caption" label="LV-Angebot" />
				<hbox flex="1">
					<grid>
						<columns>
							<column></column>
							<column></column>
						</columns>
						<rows>
							<row>
								<label value="Gruppe" />
								<menulist id="lehrveranstaltung-lvangebot-textbox-gruppe"
									editable="true" datasources="rdf:null" flex="1"
									ref="http://www.technikum-wien.at/gruppen/liste"
									oninput="LvAngebotGruppenLoad(this)">
										<template>
											<menupopup>
												<menuitem value="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_kurzbz"
													label="rdf:http://www.technikum-wien.at/gruppen/rdf#gruppe_kurzbz"
													uri="rdf:*"/>
											</menupopup>
										</template>
								</menulist>
							</row>
							<row>
								<checkbox label="Neue Gruppe anlegen" id="lehrveranstaltung-lvangebot-checkbox-gruppe" onclick="ToggleGruppe();"/>
							</row>
							<row>
								<label value="Plätze Incoming" />
								<textbox id="lehrveranstaltung-lvangebot-textbox-incoming" tooltiptext=""/>
							</row>
							<row>
								<label value="Plätze gesamt" />
								<textbox id="lehrveranstaltung-lvangebot-textbox-gesamt" tooltiptext=""/>
							</row>
							<row>
								<label value="Anmeldefenster Start" />
								<hbox>
									<box class="Datum" id="lehrveranstaltung-lvangebot-textbox-start"/>
								</hbox>
							</row>
							<row>
								<label value="Anmeldefenster Ende" />
								<hbox>
									<box class="Datum" id="lehrveranstaltung-lvangebot-textbox-ende"/>
								</hbox>
							</row>
							<row>
								<spacer flex="1" />
								<hbox align="right">
									<button id="lehrveranstaltung-lvangebot-button-save" label="speichern" oncommand="LvAngebotGruppeSave();" width="130"/>
								</hbox>
							</row>
						</rows>
					</grid>
				</hbox>
			</groupbox>
		</vbox>
	</hbox>
</vbox>
</overlay>
