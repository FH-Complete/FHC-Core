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

echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeiterdetailoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeiterfunktionoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeiterbuchungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeitervertragoverlay.xul.php"?>';
?>
<!DOCTYPE overlay >

<overlay id="MitarbeiterOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiter/mitarbeiteroverlay.js.php" />

<!-- **************** -->
<!-- *  MITARBEITER * -->
<!-- **************** -->
<vbox id="MitarbeiterEditor" persist="height">
<popupset>
	<menupopup id="mitarbeiter-tree-popup">
		<menuitem label="EMail versenden" oncommand="MitarbeiterSendMail();" id="mitarbeiter-tree-popup-mail" hidden="false"/>
		<menuitem label="Personendetails anzeigen" oncommand="MitarbeiterShowPersonendetails()" id="mitarbeiter-tree-popup-personendetails" hidden="false"/>
	</menupopup>
</popupset>
	<hbox>
		<toolbox flex="1">
			<toolbar id="mitarbeiter-nav-toolbar">
			<toolbarbutton id="mitarbeiter-toolbar-neu" label="Neu" oncommand="MitarbeiterNeu()" disabled="false" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Mitarbeiter anlegen"/>
			<toolbarbutton id="mitarbeiter-toolbar-export" label="Export" oncommand="MitarbeiterExport()" disabled="false" image="../skin/images/ExcelIcon.png" tooltiptext="Daten ins Excel Exportieren"/>
			<toolbarbutton id="mitarbeiter-toolbar-refresh" label="Aktualisieren" oncommand="MitarbeiterTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
			<textbox id="mitarbeiter-toolbar-textbox-suche" control="mitarbeiter-toolbar-button-search" onkeypress="MitarbeiterSearchFieldKeyPress(event)" />
			<button id="mitarbeiter-toolbar-button-search" oncommand="MitarbeiterSuche()" label="Suchen"/>
			<spacer flex="1"/>
			<label id="mitarbeiter-toolbar-label-anzahl"/>
			</toolbar>
		</toolbox>
	</hbox>

	<!-- ************ -->
	<!-- *   TREE   * -->
	<!-- ************ -->
	<tree id="mitarbeiter-tree" seltype="multi" hidecolumnpicker="false" flex="1"
			datasources="rdf:null" ref="http://www.technikum-wien.at/mitarbeiter/alle"
			onselect="MitarbeiterAuswahl();"
			flags="dont-build-content"
			enableColumnDrag="true"
			style="margin:0px;"
			persist="hidden, height"
			context="mitarbeiter-tree-popup"				
	>
		<treecols>
			<treecol id="mitarbeiter-treecol-uid" label="UID" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-anrede" label="Anrede" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-titelpre" label="TitelPre" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-nachname" label="Nachname" flex="1"  persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sortActive="true"
				sortDirection="ascending"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-vorname" label="Vorname" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-vornamen" label="Vornamen" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-titelpost" label="TitelPost" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-geburtsdatum" label="Geburtsdatum" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-geburtsdatum_iso" label="GeburtsdatumISO" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-svnr" label="SVNR" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#svnr"  onclick="MitarbeiterTreeSort()"/> 
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-ersatzkennzeichen" label="Ersatzkennzeichen" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ersatzkennzeichen"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-aktiv" label="Aktiv" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-personalnummer" label="PNr" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#personalnummer"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-kurzbz" label="Kurzbz" flex="1" persist="hidden, width, ordinal" hidden="false"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-lektor" label="Lektor" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#lektor"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-fixangestellt" label="Fix" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#fixangestellt"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-telefonklappe" label="Klappe" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#telefonklappe"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-ort_kurzbz" label="Buero" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ort_kurzbz"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-person_id" label="Person_id" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-insertamum" label="Angelegt am" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertamum_iso"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-insertvon" label="Angelegt von" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertvon"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-insertamum_iso" label="Angelegt am (ISO)" flex="1" persist="hidden, width, ordinal" hidden="true" ignoreincolumnpicker="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertamum_iso"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-updateamum" label="Geaendert am" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updateamum_iso"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-updatevon" label="Geaendert von" flex="1" persist="hidden, width, ordinal" hidden="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updatevon"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>
			<treecol id="mitarbeiter-treecol-updateamum_iso" label="Geaendert am (ISO)" flex="1" persist="hidden, width, ordinal" hidden="true" ignoreincolumnpicker="true"
				class="sortDirectionIndicator"
				sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updateamum_iso"  onclick="MitarbeiterTreeSort()"/>
			<splitter class="tree-splitter"/>

		</treecols>

		<template>
			<rule>
					<treechildren>
						<treeitem uri="rdf:*">
 						<treerow>
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen" />   							
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#svnr" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ersatzkennzeichen" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#personalnummer" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#lektor" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#fixangestellt" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#telefonklappe" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ort_kurzbz" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertamum" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertvon" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#insertamum_iso" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updateamum" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updatevon" />
   							<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#updateamum_iso" />
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
	<!-- *   TABS   * -->
	<!-- ************ -->
	<vbox flex="1"  style="overflow:auto;margin:0px;" persist="height">
		<tabbox id="mitarbeiter-tabbox" flex="3" orient="vertical">
			<tabs orient="horizontal" id="mitarbeiter-tabs">
				<tab id="mitarbeiter-tab-detail" label="Stammdaten" />
				<tab id="mitarbeiter-tab-kontakt" label="Kontaktdaten" />
				<tab id="mitarbeiter-tab-funktionen" label="BIS-Daten" />
				<tab id="mitarbeiter-tab-betriebsmittel" label="Betriebsmittel" />
				<tab id="mitarbeiter-tab-funktionen" label="Funktionen"  oncommand="MitarbeiterFunktionIFrameLoad()"/>
				<?php 
				if($rechte->isBerechtigt('buchung/mitarbeiter'))
					echo '<tab id="mitarbeiter-tab-buchung" label="Buchungen" />';
				if($rechte->isBerechtigt('vertrag/mitarbeiter'))
					echo '<tab id="mitarbeiter-tab-vertrag" label="Verträge" />';

				?>
			</tabs>
			<tabpanels id="mitarbeiter-tabpanels-main" flex="1">
				<vbox id="mitarbeiter-detail-stammdaten"  style="margin-top:10px;" />
				<iframe id="mitarbeiter-kontakt" src="" style="margin-top:10px;" />
				<vbox id="mitarbeiter-detail-funktionen"  style="margin-top:10px;" />
				<iframe id="mitarbeiter-betriebsmittel" src="" style="margin-top:10px;" />
				<iframe id="mitarbeiter-funktionen" src="" style="margin-top:10px;"/>
				<?php 
				if($rechte->isBerechtigt('buchung/mitarbeiter'))
					echo '<vbox id="mitarbeiter-buchung" style="margin-top:10px;" />';
				if($rechte->isBerechtigt('vertrag/mitarbeiter'))
					echo '<vbox id="mitarbeiter-vertrag" style="margin-top:10px;" />';
				?>				
			</tabpanels>
		</tabbox>	
	</vbox>
</vbox>
</overlay>
