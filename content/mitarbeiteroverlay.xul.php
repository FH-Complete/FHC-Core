<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiterdetailoverlay.xul.php"?>';
// rdf:null
?>

<!DOCTYPE overlay>

<overlay id="MitarbeiterOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/mitarbeiteroverlay.js.php" />
<!--
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
-->

<vbox id="MitarbeiterEditor" flex="1">
<keyset>
  <key id="delete-key" keycode="VK_DELETE" oncommand="MitarbeiterDelete();"/>
  <key id="delete-key" modifiers="control" key="N" oncommand="MitarbeiterNeu();"/>
</keyset>

<toolbox>
	<toolbar id="toolbar-MitarbeiterEditor">
    	<toolbarbutton id="toolbar-MitarbeiterEditor-neu" label=" Neu" oncommand="MitarbeiterNeu();" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Mitarbeiter anlegen" />
		<toolbarbutton id='toolbar-MitarbeiterEditor-loeschen' label=" Löschen" key="delete-key" oncommand="MitarbeiterDelete();" image="../skin/images/DeleteIcon.png" tooltiptext="Mitarbeiter löschen" />
		<toolbarbutton label=" Excel" oncommand="MitarbeiterExcelExport();" image="../skin/images/ExcelIcon.png" tooltiptext="Excel-Export" />
		<toolbarbutton label=" Neu Laden" oncommand="refreshtree(true);" image="../skin/images/refresh.png" tooltiptext="Liste neu laden" />
	</toolbar>
</toolbox>
<popupset>
	<popup id="tree-liste-mitarbeiter-popup">
		<menuitem label="EMail versenden" oncommand="TreeListeMitarbeiter_sendmail();" />
	</popup>
</popupset>
<tree id="tree-liste-mitarbeiter" seltype="multi" hidecolumnpicker="false" flex="1"
		datasources="rdf:null"
		ref="http://www.technikum-wien.at/mitarbeiter/alle"
		onselect="TreeListeMitarbeiterAuswahl();" flags="dont-build-content"
		enableColumnDrag="true"	style="margin:0px;"
		persist="height"
		context="tree-liste-mitarbeiter-popup"
		>
	<treecols>
		<treecol id="tree-liste-mitarbeiter-col-anrede" label="Anrede" flex="1"  hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-titelpre" label="Titel(Pre)" flex="2" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-vorname" label="Vorname" flex="2" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-vornamen" label="Vornamen" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-nachname" label="Nachname" flex="2" hidden="false" primary="true"
	    	class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-titelpost" label="Titel(Post)" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-personal_nr" label="PNr" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#personal_nr" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-geburtsdatum" label="Geburtsdatum" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-geburtsdatum_iso" label="Geburtsdatum_ISO" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-svnr" label="SVNR" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#svnr"  onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-ersatzkennzeichen" label="Ersatzkennzeichen" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ersatzkennzeichen" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
		<treecol id="tree-liste-mitarbeiter-col-uid" label="UID" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-kurzbezeichnung" label="Kuerzel" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbezeichnung" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-geschlecht" label="Geschlecht" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geschlecht" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-staatsbuergerschaft" label="Staatsb." flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#staatsbuergerschaft" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-aktstatus" label="Status" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktstatus_bezeichnung" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-akademischergrad" label="Akademischergrad" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#akademischergrad" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-familienstand" label="Familienstand" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#familienstand_bezeichnung" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-anzahlderkinder" label="Anzahlderkinder" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anzahlderkinder" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-geburtsnation" label="Geburtsnation" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsnation" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-beginndatum" label="Beginndatum" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#beginndatum" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-bemerkung" label="Bemerkung" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#bemerkung" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-habilitation" label="Habilitation" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#habilitation" onclick="TreeMitarbeiterSort()"/>
	    <!-- in tabelle funktion
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-mitgliedentwicklungsteam" label="MitgliedEntwicklungsteam" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitgliedentwicklungsteam" onclick="TreeMitarbeiterSort()"/>

	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-qualifikation" label="Qualifikation" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#qualifikation" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-hauptberuflich" label="Hauptberuflich" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#hauptberuflich" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-hauptberuf" label="Hauptberuf" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#hauptberuf" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-sws" label="SWS" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#sws" onclick="TreeMitarbeiterSort()"/>
	    -->
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-ausgeschieden" label="Ausgeschieden" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ausgeschieden" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-beendigungsdatum" label="Beendigungsdatum" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#beendigungsdatum" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-ausbildung" label="Ausbildung" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ausbildung_bezeichnung" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-stundensatz" label="Stundensatz" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#stundensatz" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-bismelden" label="BIS" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#bismelden" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-aktiv" label="Aktiv" flex="1" hidden="false" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" onclick="TreeMitarbeiterSort()"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-mitarbeiter_id" label="mitarbeiter_id" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitarbeiter_id" onclick="TreeMitarbeiterSort()"/>
	    	<splitter class="tree-splitter"/>
	    <treecol id="tree-liste-mitarbeiter-col-person_id" label="person_id" flex="1" hidden="true" persist="hidden"
	    	class="sortDirectionIndicator"
	    	sort="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id" onclick="TreeMitarbeiterSort()"/>
	</treecols>

	<template>
		<rule>
			<treechildren>
				<treeitem uri="rdf:*">
					<treerow>
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anrede"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vornamen" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpost" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#personal_nr" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum" />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsdatum_iso" />
	 					<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#svnr" />
	 					<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ersatzkennzeichen"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geschlecht"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#staatsbuergerschaft"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktstatus_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#akademischergrad"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#familienstand_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#anzahlderkinder"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#geburtsnation"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#beginndatum"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#bemerkung"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#habilitation"   />
						<!-- in tabelle funktion
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitgliedentwicklungsteam"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#qualifikation"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#hauptberuflich"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#hauptberuf"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#sws"   />
						-->
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ausgeschieden"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#beendigungsdatum"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#ausbildung_bezeichnung"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#stundensatz"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#bismelden"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#mitarbeiter_id"   />
						<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#person_id"   />
	 				</treerow>
	 			</treeitem>
	 		</treechildren>
	 	</rule>
  	</template>

</tree>

<splitter id="mitarbeiter-overlay-splitter" collapse="after" persist="state">
	<grippy />
</splitter>

<vbox id="MitarbeiterDetailEditor" persist="height"/>


</vbox>

</overlay>
