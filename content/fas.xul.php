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
require_once('../config/vilesci.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/variable.class.php');
require_once('../include/addon.class.php');

$user=get_uid();

$error_msg='';

//$error_msg.=loadVariables($user);
$variable = new variable();
if(!$variable->loadVariables($user))
{
	die('Fehler beim Laden der Variablen:'.$variable->errormsg);
}

//$benutzer = new benutzer($conn);
//if(!$benutzer->loadVariables($user))
//	$error_msg = $benutzer->errormsg;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/fas'))
	die('Sie haben keine Berechtigung für diese Seite');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css" ?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/fasoverlay.xul.php"?>';
?>
<!DOCTYPE window [
	<?php require('../locale/'.$variable->variable->locale.'/fas.dtd'); ?>
]>

<window
	id="fas"
	title="&window.title; - &window.version;"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	orient="vertical"
	width="800"
  	height="600"
  	persist="screenX screenY width height sizemode"
  	onload="onLoad()"
  	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/tempus.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php"/>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>

<?php
// ADDONS
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	echo '<script type="application/x-javascript" src="'.APP_ROOT.'addons/'.$addon->kurzbz.'/content/init.js.php" />';
}
?>

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
  <command id="menu-prefs-stpltable-stundenplan:command" oncommand="stpltableChange('stundenplan');"/>
  <command id="menu-prefs-stpltable-stundenplandev:command" oncommand="stpltableChange('stundenplandev');"/>
  <command id="menu-prefs-kontofilterstg:command" oncommand="EinstellungenKontoFilterStgChange();"/>
  <command id="menu-prefs-number_displayed_past_studiensemester:command" oncommand="variableChangeValueIfNumber('number_displayed_past_studiensemester');"/>
  <command id="menu-statistic-lehrauftraege:command" oncommand="StatistikPrintLehrauftraege();"/>
  <command id="menu-statistic-lvplanung:command" oncommand="StatistikPrintLVPlanung();"/>
  <command id="menu-statistic-lvplanungexcel:command" oncommand="StatistikPrintLVPlanungExcel();"/>
  <command id="menu-statistic-lehrauftragsliste:command" oncommand="StatistikPrintLehrauftragsliste();"/>
  <command id="menu-statistic-projektarbeit:command" oncommand="StatistikPrintProjektarbeit();"/>
  <command id="menu-statistic-abschlusspruefung:command" oncommand="StatistikPrintAbschlusspruefung();"/>
  <command id="menu-statistic-bewerberstatistik-html:command" oncommand="StatistikPrintBewerberstatistik('');"/>
  <command id="menu-statistic-bewerberstatistik-excel:command" oncommand="StatistikPrintBewerberstatistik('xls');"/>
  <command id="menu-statistic-abgaengerstatistik:command" oncommand="StatistikPrintAbgaengerstatistik();"/>
  <command id="menu-statistic-fehlendedokumente:command" oncommand="StatistikPrintFehlendeDokumente();"/>
  <command id="menu-statistic-notenspiegel:command" oncommand="StatistikPrintNotenspiegel('html');"/>
  <command id="menu-statistic-notenspiegel-excel:command" oncommand="StatistikPrintNotenspiegel('xls');"/>
  <command id="menu-statistic-notenspiegel-excel-erweitert:command" oncommand="StatistikPrintNotenspiegelErweitert('xls');"/>
  <command id="menu-statistic-studienverlauf-student:command" oncommand="StatistikPrintStudienverlaufStudent();"/>
  <command id="menu-statistic-substatistik-studentenprosemester-excel:command" oncommand="StatistikPrintStudentenProSemester('xls');"/>
  <command id="menu-statistic-substatistik-studentenprosemester-html:command" oncommand="StatistikPrintStudentenProSemester('');"/>
  <command id="menu-statistic-substatistik-alvsstatistik-excel:command" oncommand="StatistikPrintALVSStatistik('xls');"/>
  <command id="menu-statistic-substatistik-alvsstatistik-html:command" oncommand="StatistikPrintALVSStatistik('');"/>
  <command id="menu-statistic-absolventenstatistik:command" oncommand="StatistikPrintAbsolventenstatistik();"/>
  <command id="menu-statistic-absolventenzahlen:command" oncommand="StatistikPrintAbsolventenZahlen();"/>
  <command id="menu-statistic-studentenstatistik:command" oncommand="StatistikPrintStudentenstatistik();"/>
  <command id="menu-statistic-oehbeitraege:command" oncommand="StatistikPrintOehBeitrag();"/>
  <command id="menu-statistic-mitarbeiterstatistik:command" oncommand="StatistikPrintMitarbeiterstatistik();"/>
  <command id="menu-statistic-studentendetails:command" oncommand="StatistikPrintStudentExportExtended();"/>
  <command id="menu-statistic-stromanalyse:command" oncommand="StatistikPrintStromanalyse();"/>
  <command id="menu-dokumente-bewerberakt:command" oncommand="StudentPrintBewerberakt(event);"/>
  <command id="menu-dokumente-inskriptionsbestaetigung:command" oncommand="StudentPrintInskriptionsbestaetigung(event);"/>
  <command id="menu-dokumente-zeugnis:command" oncommand="StudentCreateZeugnis('Zeugnis',event);"/>
  <command id="menu-dokumente-zeugniseng:command" oncommand="StudentCreateZeugnis('ZeugnisEng',event);"/>
  <command id="menu-dokumente-diplsupplement:command" oncommand="StudentCreateDiplSupplement(event);"/>
  <command id="menu-dokumente-studienerfolg-normal:command" oncommand="StudentCreateStudienerfolg(event, 'Studienerfolg');"/>
  <command id="menu-dokumente-studienerfolg-finanzamt:command" oncommand="StudentCreateStudienerfolg(event, 'Studienerfolg','finanzamt');"/>
  <command id="menu-dokumente-studienerfolg-allesemester-normal:command" oncommand="StudentCreateStudienerfolg(event, 'Studienerfolg','', '', 'true');"/>
  <command id="menu-dokumente-studienerfolg-allesemester-finanzamt:command" oncommand="StudentCreateStudienerfolg(event, 'Studienerfolg','finanzamt', '', 'true');"/>
  <command id="menu-dokumente-studienerfolgeng-normal:command" oncommand="StudentCreateStudienerfolg(event, 'StudienerfolgEng');"/>
  <command id="menu-dokumente-studienerfolgeng-finanzamt:command" oncommand="StudentCreateStudienerfolg(event, 'StudienerfolgEng','finanzamt');"/>
  <command id="menu-dokumente-studienerfolgeng-allesemester-normal:command" oncommand="StudentCreateStudienerfolg(event, 'StudienerfolgEng','', '', 'true');"/>
  <command id="menu-dokumente-studienerfolgeng-allesemester-finanzamt:command" oncommand="StudentCreateStudienerfolg(event, 'StudienerfolgEng','finanzamt', '', 'true');"/>
  <command id="menu-dokumente-accountinfoblatt:command" oncommand="PrintAccountInfoBlatt(event);"/>
  <command id="menu-dokumente-zutrittskarte:command" oncommand="PrintZutrittskarte();"/>
  <command id="menu-dokumente-studienblatt:command" oncommand="PrintStudienblatt(event);"/>
  <command id="menu-dokumente-studienblatt_englisch:command" oncommand="PrintStudienblattEnglisch(event);"/>
  <command id="menu-dokumente-pruefungsprotokoll:command" oncommand="StudentAbschlusspruefungPrintPruefungsprotokollMultiple(event,'de');"/>
  <command id="menu-dokumente-pruefungsprotokoll_englisch:command" oncommand="StudentAbschlusspruefungPrintPruefungsprotokollMultiple(event,'en');"/>
  <command id="menu-dokumente-pruefungsprotokoll2:command" oncommand="StudentAbschlusspruefungPrintPruefungsprotokollMultiple(event,'de2');"/>
  <command id="menu-dokumente-pruefungsprotokoll2_englisch:command" oncommand="StudentAbschlusspruefungPrintPruefungsprotokollMultiple(event,'en2');"/>
  <command id="menu-dokumente-pruefungszeugnis:command" oncommand="StudentAbschlusspruefungPrintPruefungszeugnisMultiple(event,'deutsch');"/>
  <command id="menu-dokumente-pruefungszeugnis_englisch:command" oncommand="StudentAbschlusspruefungPrintPruefungszeugnisMultiple(event,'englisch');"/>
  <command id="menu-dokumente-bescheid_deutsch:command" oncommand="StudentAbschlusspruefungPrintBescheidMultiple(event, 'deutsch')"/>
  <command id="menu-dokumente-bescheid_englisch:command" oncommand="StudentAbschlusspruefungPrintBescheidMultiple(event, 'englisch')"/>
  <command id="menu-dokumente-urkunde_deutsch:command" oncommand="StudentAbschlusspruefungPrintUrkundeMultiple(event, 'deutsch')"/>
  <command id="menu-dokumente-urkunde_englisch:command" oncommand="StudentAbschlusspruefungPrintUrkundeMultiple(event, 'englisch')"/>
  <command id="menu-dokumente-ausbildungsvertrag:command" oncommand="StudentPrintAusbildungsvertrag(event);"/>
  <command id="menu-dokumente-ausbildungsvertrag_englisch:command" oncommand="StudentPrintAusbildungsvertragEnglisch(event);"/>
  <command id="menu-cis-studienplan:command" oncommand="StudentCisStudienplan(event);"/>
  <command id="menu-cis-notenliste:command" oncommand="StudentCisNotenliste(event);"/>
  <command id="menu-messages-new:command" oncommand="MessageNew(event);"/>
  <command id="menu-extras-reihungstest:command" oncommand="ExtrasShowReihungstest();"/>
  <command id="menu-extras-firma:command" oncommand="ExtrasShowFirmenverwaltung();"/>
  <command id="menu-extras-lvverwaltung:command" oncommand="ExtrasShowLVverwaltung();"/>
  <command id="menu-extras-studienordnung:command" oncommand="ExtrasShowStudienordnung();"/>
  <command id="menu-extras-projektarbeitsbenotung:command" oncommand="ExtrasShowProjektarbeitsBenotung();"/>
  <command id="menu-extras-gruppenverwaltung:command" oncommand="ExtrasShowGruppenverwaltung();"/>
  <command id="menu-extras-lehrfachverwaltung:command" oncommand="ExtrasShowLehrfachverwaltung();"/>
  <command id="menu-extras-lektorenzuordnunginstitute:command" oncommand="ExtrasShowLektorenzuordnunginstitute();"/>
  <command id="menu-extras-preinteressentenuebernahme:command" oncommand="ExtrasShowPreinteressentenuebernahme();"/>
  <command id="menu-extras-projektarbeitsabgaben:command" oncommand="ExtrasShowProjektarbeitsabgaben();"/>
  <command id="menu-extras-aliquote_reduktion:command" oncommand="ExtrasShowAliquote_reduktion();"/>
  <command id="menu-bis-mitarbeiter-export:command" oncommand="BISMitarbeiterExport();"/>
  <command id="menu-bis-mitarbeiter-uebersicht:command" oncommand="BISMitarbeiterUebersicht();"/>
  <command id="menu-bis-studenten-export:command" oncommand="BISStudentenExport();"/>
  <command id="menu-bis-studenten-checkstudent:command" oncommand="BISStudentenPlausicheck();"/>
  <command id="menu-help-about:command" oncommand="OpenAboutDialog()"/>
  <command id="menu-help-manual:command" oncommand="OpenManual()"/>
</commandset>

<keyset id="mainkeys">
  <key
     id        =  "menu-file-close:key"
     key       = "&menu-file-close.key;"
     observes  =  "menu-file-close:command"
     modifiers =  "accel" />
</keyset>
<!-- MENUE -->
<toolbox id="main-toolbox">
  <menubar id="menu" >
  <!-- ******* DATEI ******* -->
    <menu id="menu-file" label="&menu-file.label;" accesskey="&menu-file.accesskey;">
      <menupopup id="menu-file-popup">
        <menuitem
           id        =  "menu-file-close"
           key       =  "menu-file-close:key"
           label     = "&menu-file-close.label;"
           command   =  "menu-file-close:command"
           accesskey = "&menu-file-close.accesskey;"/>
      </menupopup>
    </menu>
    <!-- ******** BEARBEITEN ********* -->
    <menu id="menu-edit" label="&menu-edit.label;" accesskey="&menu-edit.accesskey;" onclick="loadUndoList();">
      <menupopup id="menu-edit-popup">
        <menu id="menu-edit-undo" label="&menu-edit-undo.label;"
           datasources="rdf:null"
           ref="http://www.technikum-wien.at/undo/liste"
        >
           	<template>
	        		<rule>
	     	 			<menupopup>
				        		<menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/undo/rdf#beschreibung"
		            					  value="rdf:http://www.technikum-wien.at/undo/rdf#log_id"
		            					  onclick="UnDo(this.value, this.label);"/>
						</menupopup>
		          	</rule>
	        </template>
        </menu>
      </menupopup>
    </menu>
    <!-- *********** EINSTELLUNGEN ********** -->
    <menu id="menu-prefs" label="&menu-prefs.label;" accesskey="&menu-prefs.accesskey;">
		<menupopup id="menu-prefs-popup">
			<!--<menu id="menu-prefs-stpltable" label="&menu-prefs-stpltable.label;" accesskey="&menu-prefs-stpltable.accesskey;">
				<menupopup id="menu-prefs-stpltable-popup">
	        		<menuitem
	          			 id     	="menu-prefs-stpltable-stundenplan"
	          			 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplan:key"
	         			 label     	="&menu-prefs-stpltable-stundenplan.label;"
	         			 command   	="menu-prefs-stpltable-stundenplan:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplan.accesskey;"/>
	           		<menuitem
						 id        	="menu-prefs-stpltable-stundenplandev"
						 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplandev:key"
						 label     	="&menu-prefs-stpltable-stundenplandev.label;"
						 command   	="menu-prefs-stpltable-stundenplandev:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplandev.accesskey;"/>
	      		</menupopup>
	      	</menu>-->
	      	<menu
           id        =  "menu-properies-studiensemester"
           label     = "Studiensemester">
           <menupopup id="menu-properties-popup">
       <?php
       		$stsemobj = new studiensemester();
       		$stsemobj->getAll();
       		foreach ($stsemobj->studiensemester as $stsem)
       		{
  				echo "
			<menuitem
				id = 'menu-properies-studiensemester-name'
				label = '$stsem->studiensemester_kurzbz'
				type = 'radio'
				command = 'menu-properties-studiensemester:command'
				checked = ".($variable->variable->semester_aktuell==$stsem->studiensemester_kurzbz?"'true' ":"'false'")." />";
       		}
       ?>

      		</menupopup>
        </menu>
   		<menuitem
			 id        	="menu-prefs-kontofilterstg"
			 type		="checkbox"
  			 key       	="menu-prefs-kontofilterstg:key"
			 label     	="&menu-prefs-kontofilterstg.label;"
			 command   	="menu-prefs-kontofilterstg:command"
   			 accesskey 	="&menu-prefs-kontofilterstg.accesskey;"
   			 checkbox   ="true"
   			 checked   ="<?php echo $variable->variable->kontofilterstg;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-number_displayed_past_studiensemester"
 			 key       	="menu-prefs-number_displayed_past_studiensemester:key"
			 label     	="&menu-prefs-number_displayed_past_studiensemester.label;"
			 command   	="menu-prefs-number_displayed_past_studiensemester:command"
			 accesskey 	="&menu-prefs-number_displayed_past_studiensemester.accesskey;"
			 value    	="<?php echo (isset($variable->variable->number_displayed_past_studiensemester)?$variable->variable->number_displayed_past_studiensemester:'');?>"
			 />
	    </menupopup>
    </menu>
    <!-- ********** BERICHTE ********** -->
    <menu id="menu-statistic" label="&menu-statistic.label;" accesskey="&menu-statistic.accesskey;">
		<menupopup id="menu-statistic-popup">
			<!-- *** LEHRE *** -->
			<menu id="menu-statistic-lehre" label="&menu-statistic-lehre.label;" accesskey="&menu-statistic-lehre.accesskey;">
				<menupopup id="menu-statistic-lehre-popup">
					<menu id="menu-statistic-sublvplanung" label="&menu-statistic-sublvplanung.label;" accesskey="&menu-statistic-sublvplanung.accesskey;">
						<menupopup id="menu-statistic-substatistik-popup">
				            <menuitem
				               id        =  "menu-statistic-lvplanungexcel"
				               key       =  "menu-statistic-lvplanungexcel:key"
				               label     = "&menu-statistic-lvplanungexcel.label;"
				               command   =  "menu-statistic-lvplanungexcel:command"
				               accesskey = "&menu-statistic-lvplanungexcel.accesskey;"/>
				              <menuitem
				               id        =  "menu-statistic-lvplanung"
				               key       =  "menu-statistic-lvplanung:key"
				               label     = "&menu-statistic-lvplanung.label;"
				               command   =  "menu-statistic-lvplanung:command"
				               accesskey = "&menu-statistic-lvplanung.accesskey;"/>
				        </menupopup>
				    </menu>
					<menuitem
		               id        =  "menu-statistic-projektarbeit"
		               key       =  "menu-statistic-projektarbeit:key"
		               label     = "&menu-statistic-projektarbeit.label;"
		               command   =  "menu-statistic-projektarbeit:command"
		               accesskey = "&menu-statistic-projektarbeit.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-abschlusspruefung"
		               key       =  "menu-statistic-abschlusspruefung:key"
		               label     = "&menu-statistic-abschlusspruefung.label;"
		               command   =  "menu-statistic-abschlusspruefung:command"
		               accesskey = "&menu-statistic-abschlusspruefung.accesskey;"/>
          		</menupopup>
          	</menu>
          	<!-- *** Mitarbeiter *** -->
			<menu id="menu-statistic-mitarbeiter" label="&menu-statistic-mitarbeiter.label;" accesskey="&menu-statistic-mitarbeiter.accesskey;">
				<menupopup id="menu-statistic-mitarbeiter-popup">
					<menuitem
		               id        =  "menu-statistic-lehrauftragsliste"
		               key       =  "menu-statistic-lehrauftragsliste:key"
		               label     = "&menu-statistic-lehrauftragsliste.label;"
		               command   =  "menu-statistic-lehrauftragsliste:command"
		               accesskey = "&menu-statistic-lehrauftragsliste.accesskey;"/>
                    <menuitem
                        id        =  "menu-extras-lektorenzuordnunginstitute"
                        key       =  "menu-extras-lektorenzuordnunginstitute:key"
                        label     = "&menu-extras-lektorenzuordnunginstitute.label;"
                        command   =  "menu-extras-lektorenzuordnunginstitute:command"
                        accesskey = "&menu-extras-lektorenzuordnunginstitute.accesskey;"/>
				</menupopup>
          	</menu>
          	<!-- *** Student *** -->
			<menu id="menu-statistic-student" label="&menu-statistic-student.label;" accesskey="&menu-statistic-student.accesskey;">
				<menupopup id="menu-statistic-student-popup">
				<menuitem
	               id        =  "menu-statistic-fehlendedokumente"
	               key       =  "menu-statistic-fehlendedokumente:key"
	               label     = "&menu-statistic-fehlendedokumente.label;"
	               command   =  "menu-statistic-fehlendedokumente:command"
	               accesskey = "&menu-statistic-fehlendedokumente.accesskey;"/>
				<menuitem
	               id        =  "menu-statistic-studentendetails"
	               key       =  "menu-statistic-studentendetails:key"
	               label     = "&menu-statistic-studentendetails.label;"
	               command   =  "menu-statistic-studentendetails:command"
	               accesskey = "&menu-statistic-studentendetails.accesskey;"/>
				<menuitem
	               id        =  "menu-statistic-oehbeitraege"
	               key       =  "menu-statistic-oehbeitraege:key"
	               label     = "&menu-statistic-oehbeitraege.label;"
	               command   =  "menu-statistic-oehbeitraege:command"
	               accesskey = "&menu-statistic-oehbeitraege.accesskey;"/>
				<menu id="menu-statistic-subnotenspiegel" label="&menu-statistic-subnotenspiegel.label;" accesskey="&menu-statistic-subnotenspiegel.accesskey;">
					<menupopup id="menu-statistic-subnotenspiegel-popup">
						<menuitem
			               id        =  "menu-statistic-notenspiegel-excel"
			               key       =  "menu-statistic-notenspiegel-excel:key"
			               label     = "&menu-statistic-notenspiegel-excel.label;"
			               command   =  "menu-statistic-notenspiegel-excel:command"
			               accesskey = "&menu-statistic-notenspiegel-excel.accesskey;"/>
                        <menuitem
                                id        =  "menu-statistic-notenspiegel-excel-erweitert"
                                key       =  "menu-statistic-notenspiegel-excel-erweitert:key"
                                label     = "&menu-statistic-notenspiegel-excel-erweitert.label;"
                                command   =  "menu-statistic-notenspiegel-excel-erweitert:command"
                                accesskey = "&menu-statistic-notenspiegel-excel-erweitert.accesskey;"/>
						<menuitem
			               id        =  "menu-statistic-notenspiegel"
			               key       =  "menu-statistic-notenspiegel:key"
			               label     = "&menu-statistic-notenspiegel.label;"
			               command   =  "menu-statistic-notenspiegel:command"
			               accesskey = "&menu-statistic-notenspiegel.accesskey;"/>
					</menupopup>
				</menu>
				<menuitem
				   id        =  "menu-statistic-studienverlauf-student"
				   key       =  "menu-statistic-studienverlauf-student:key"
				   label     = "&menu-statistic-studienverlauf-student.label;"
				   command   =  "menu-statistic-studienverlauf-student:command"
				   accesskey = "&menu-statistic-studienverlauf-student.accesskey;"/>
            	</menupopup>
          	</menu>
			<!-- *** Statistik *** -->
			<menu id="menu-statistic-substatistik" label="&menu-statistic-substatistik.label;" accesskey="&menu-statistic-substatistik.accesskey;">
				<menupopup id="menu-statistic-substatistik-popup">
					<menu id="menu-statistic-substatistik-studentenprosemester" label="&menu-statistic-substatistik-studentenprosemester.label;" accesskey="&menu-statistic-substatistik-studentenprosemester.accesskey;">
						<menupopup id="menu-statistic-substatistik-studentenprosemester-popup">
				             <menuitem
				               id        =  "menu-statistic-substatistik-studentenprosemester-excel"
				               key       =  "menu-statistic-substatistik-studentenprosemester-excel:key"
				               label     = "&menu-statistic-substatistik-studentenprosemester-excel.label;"
				               command   =  "menu-statistic-substatistik-studentenprosemester-excel:command"
				               accesskey = "&menu-statistic-substatistik-studentenprosemester-excel.accesskey;"/>
				             <menuitem
				               id        =  "menu-statistic-substatistik-studentenprosemester-html"
				               key       =  "menu-statistic-substatistik-studentenprosemester-html:key"
				               label     = "&menu-statistic-substatistik-studentenprosemester-html.label;"
				               command   =  "menu-statistic-substatistik-studentenprosemester-html:command"
				               accesskey = "&menu-statistic-substatistik-studentenprosemester-html.accesskey;"/>
						</menupopup>
					</menu>
					<menu id="menu-statistic-substatistik-alvsstatistik" label="&menu-statistic-substatistik-alvsstatistik.label;" accesskey="&menu-statistic-substatistik-alvsstatistik.accesskey;">
						<menupopup id="menu-statistic-substatistik-alvsstatistik-popup">
				             <menuitem
				               id        =  "menu-statistic-substatistik-alvsstatistik-excel"
				               key       =  "menu-statistic-substatistik-alvsstatistik-excel:key"
				               label     = "&menu-statistic-substatistik-alvsstatistik-excel.label;"
				               command   =  "menu-statistic-substatistik-alvsstatistik-excel:command"
				               accesskey = "&menu-statistic-substatistik-alvsstatistik-excel.accesskey;"/>
				             <menuitem
				               id        =  "menu-statistic-substatistik-alvsstatistik-html"
				               key       =  "menu-statistic-substatistik-alvsstatistik-html:key"
				               label     = "&menu-statistic-substatistik-alvsstatistik-html.label;"
				               command   =  "menu-statistic-substatistik-alvsstatistik-html:command"
				               accesskey = "&menu-statistic-substatistik-alvsstatistik-html.accesskey;"/>
						</menupopup>
					</menu>
					<menu id="menu-statistic-substatistik-bewerberstatistik" label="&menu-statistic-substatistik-bewerberstatistik.label;" accesskey="&menu-statistic-substatistik-bewerberstatistik.accesskey;">
						<menupopup id="menu-statistic-substatistik-bewerberstatistik-popup">
				             <menuitem
				               id        =  "menu-statistic-bewerberstatistik-excel"
				               key       =  "menu-statistic-bewerberstatistik-excel:key"
				               label     = "&menu-statistic-bewerberstatistik-excel.label;"
				               command   =  "menu-statistic-bewerberstatistik-excel:command"
				               accesskey = "&menu-statistic-bewerberstatistik-excel.accesskey;"/>
				              <menuitem
				               id        =  "menu-statistic-bewerberstatistik-html"
				               key       =  "menu-statistic-bewerberstatistik-html:key"
				               label     = "&menu-statistic-bewerberstatistik-html.label;"
				               command   =  "menu-statistic-bewerberstatistik-html:command"
				               accesskey = "&menu-statistic-bewerberstatistik-html.accesskey;"/>
		               </menupopup>
					</menu>
					<menuitem
		               id        =  "menu-statistic-abgaengerstatistik"
		               key       =  "menu-statistic-abgaengerstatistik:key"
		               label     = "&menu-statistic-abgaengerstatistik.label;"
		               command   =  "menu-statistic-abgaengerstatistik:command"
		               accesskey = "&menu-statistic-abgaengerstatistik.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-absolventenstatistik"
		               key       =  "menu-statistic-absolventenstatistik:key"
		               label     = "&menu-statistic-absolventenstatistik.label;"
		               command   =  "menu-statistic-absolventenstatistik:command"
		               accesskey = "&menu-statistic-absolventenstatistik.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-absolventenzahlen"
		               key       =  "menu-statistic-absolventenzahlen:key"
		               label     = "&menu-statistic-absolventenzahlen.label;"
		               command   =  "menu-statistic-absolventenzahlen:command"
		               accesskey = "&menu-statistic-absolventenzahlen.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-studentenstatistik"
		               key       =  "menu-statistic-studentenstatistik:key"
		               label     = "&menu-statistic-studentenstatistik.label;"
		               command   =  "menu-statistic-studentenstatistik:command"
		               accesskey = "&menu-statistic-studentenstatistik.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-mitarbeiterstatistik"
		               key       =  "menu-statistic-mitarbeiterstatistik:key"
		               label     = "&menu-statistic-mitarbeiterstatistik.label;"
		               command   =  "menu-statistic-mitarbeiterstatistik:command"
		               accesskey = "&menu-statistic-mitarbeiterstatistik.accesskey;"/>
					<menuitem
		               id        =  "menu-statistic-stromanalyse"
		               key       =  "menu-statistic-stromanalyse:key"
		               label     = "&menu-statistic-stromanalyse.label;"
		               command   =  "menu-statistic-stromanalyse:command"
		               accesskey = "&menu-statistic-stromanalyse.accesskey;"/>
				</menupopup>
			</menu>
          </menupopup>
    </menu>
    <!-- ********** DOKUMENTE ********** -->
    <menu id="menu-dokumente" label="&menu-dokumente.label;" accesskey="&menu-dokumente.accesskey;">
          <menupopup id="menu-dokumente-popup">
          <menuitem
               id        =  "menu-dokumente-bewerberakt"
               key       =  "menu-dokumente-bewerberakt:key"
               label     = "&menu-dokumente-bewerberakt.label;"
               command   =  "menu-dokumente-bewerberakt:command"
               accesskey = "&menu-dokumente-bewerberakt.accesskey;"/>
          <menuitem
               id        =  "menu-dokumente-accountinfoblatt"
               key       =  "menu-dokumente-accountinfoblatt:key"
               label     = "&menu-dokumente-accountinfoblatt.label;"
               command   =  "menu-dokumente-accountinfoblatt:command"
               accesskey = "&menu-dokumente-accountinfoblatt.accesskey;"/>
          <menuitem
               id        =  "menu-dokumente-ausbildungsvertrag"
               key       =  "menu-dokumente-ausbildungsvertrag:key"
               label     = "&menu-dokumente-ausbildungsvertrag.label;"
               command   =  "menu-dokumente-ausbildungsvertrag:command"
               accesskey = "&menu-dokumente-ausbildungsvertrag.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-ausbildungsvertrag_englisch"
               key       =  "menu-dokumente-ausbildungsvertrag_englisch:key"
               label     = "&menu-dokumente-ausbildungsvertrag_englisch.label;"
               command   =  "menu-dokumente-ausbildungsvertrag_englisch:command"
               accesskey = "&menu-dokumente-ausbildungsvertrag_englisch.accesskey;"/>
          <menuitem
               id        =  "menu-dokumente-zutrittskarte"
               key       =  "menu-dokumente-zutrittskarte:key"
               label     = "&menu-dokumente-zutrittskarte.label;"
               command   =  "menu-dokumente-zutrittskarte:command"
               accesskey = "&menu-dokumente-zutrittskarte.accesskey;"/>
          <menuseparator/>
          <menuitem
               id        =  "menu-dokumente-inskriptionsbestaetigung"
               key       =  "menu-dokumente-inskriptionsbestaetigung:key"
               label     = "&menu-dokumente-inskriptionsbestaetigung.label;"
               command   =  "menu-dokumente-inskriptionsbestaetigung:command"
               accesskey = "&menu-dokumente-inskriptionsbestaetigung.accesskey;"/>
          <menuitem
               id        =  "menu-statistic-lehrauftraege"
               key       =  "menu-statistic-lehrauftraege:key"
               label     = "&menu-statistic-lehrauftraege.label;"
               command   =  "menu-statistic-lehrauftraege:command"
               accesskey = "&menu-statistic-lehrauftraege.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-studienblatt"
               key       =  "menu-dokumente-studienblatt:key"
               label     = "&menu-dokumente-studienblatt.label;"
               command   =  "menu-dokumente-studienblatt:command"
               accesskey = "&menu-dokumente-studienblatt.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-studienblatt_englisch"
               key       =  "menu-dokumente-studienblatt_englisch:key"
               label     = "&menu-dokumente-studienblatt_englisch.label;"
               command   =  "menu-dokumente-studienblatt_englisch:command"
               accesskey = "&menu-dokumente-studienblatt_englisch.accesskey;"/>
            <menu id="menu-dokumente-studienerfolg" label="&menu-dokumente-studienerfolg.label;" accesskey="&menu-dokumente-studienerfolg.accesskey;">
	          <menupopup id="menu-dokumente-studienerfolg-popup">
	          <menu id="menu-dokumente-studienerfolg-allesemester" label="&menu-dokumente-studienerfolg-allesemester.label;">
				  <menupopup id="menu-dokumente-studienerfolg-allesemester-popup">
				    <menuitem
				       id        =  "menu-dokumente-studienerfolg-allesemester-normal"
				       key       =  "menu-dokumente-studienerfolg-allesemester-normal:key"
				       label     = "&menu-dokumente-studienerfolg-allesemester-normal.label;"
				       command   =  "menu-dokumente-studienerfolg-allesemester-normal:command"
				       accesskey = "&menu-dokumente-studienerfolg-allesemester-normal.accesskey;"/>
				   	<menuitem
				       id        =  "menu-dokumente-studienerfolg-allesemester-finanzamt"
				       key       =  "menu-dokumente-studienerfolg-allesemester-finanzamt:key"
				       label     = "&menu-dokumente-studienerfolg-allesemester-finanzamt.label;"
				       command   =  "menu-dokumente-studienerfolg-allesemester-finanzamt:command"
				       accesskey = "&menu-dokumente-studienerfolg-allesemester-finanzamt.accesskey;"/>
				    </menupopup>
				</menu>
	          <?php

	          $qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 5";
	          $db = new basis_db();

	          if($db->db_query($qry))
	          {
	          	while($row = $db->db_fetch_object())
	          	{
	          		$stsem_kurzbz = $row->studiensemester_kurzbz;

					echo '
					<menu id="menu-dokumente-studienerfolg-menu" label="'.$stsem_kurzbz.'">
					  <menupopup id="menu-dokumente-studienerfolg-menu-popup">
					    <menuitem
					       id        =  "menu-dokumente-studienerfolg-menu-normal"
					       key       =  "menu-dokumente-studienerfolg-normal:key"
					       label     = "&menu-dokumente-studienerfolg-normal.label;"
					       oncommand   =  "StudentCreateStudienerfolg(event,\'Studienerfolg\',null, \''.$stsem_kurzbz.'\');"
					       accesskey = "&menu-dokumente-studienerfolg-normal.accesskey;"/>
					   	<menuitem
					       id        =  "menu-dokumente-studienerfolg-finanzamt"
					       key       =  "menu-dokumente-studienerfolg-finanzamt:key"
					       label     = "&menu-dokumente-studienerfolg-finanzamt.label;"
					       oncommand   =  "StudentCreateStudienerfolg(event,\'Studienerfolg\',\'finanzamt\', \''.$stsem_kurzbz.'\');"
					       accesskey = "&menu-dokumente-studienerfolg-finanzamt.accesskey;"/>
					    </menupopup>
					</menu>';
	          	}
	          }
	          	?>
	            <menuitem
	               id        =  "menu-dokumente-studienerfolg-normal"
	               key       =  "menu-dokumente-studienerfolg-normal:key"
	               label     = "&menu-dokumente-studienerfolg-normal.label;"
	               command   =  "menu-dokumente-studienerfolg-normal:command"
	               accesskey = "&menu-dokumente-studienerfolg-normal.accesskey;"/>
	           	<menuitem
	               id        =  "menu-dokumente-studienerfolg-finanzamt"
	               key       =  "menu-dokumente-studienerfolg-finanzamt:key"
	               label     = "&menu-dokumente-studienerfolg-finanzamt.label;"
	               command   =  "menu-dokumente-studienerfolg-finanzamt:command"
	               accesskey = "&menu-dokumente-studienerfolg-finanzamt.accesskey;"/>
	            </menupopup>
	        </menu>
	        <menu id="menu-dokumente-studienerfolgeng" label="&menu-dokumente-studienerfolgeng.label;" accesskey="&menu-dokumente-studienerfolgeng.accesskey;">
	          <menupopup id="menu-dokumente-studienerfolgeng-popup">
	          <menu id="menu-dokumente-studienerfolgeng-allesemester" label="&menu-dokumente-studienerfolgeng-allesemester.label;">
				  <menupopup id="menu-dokumente-studienerfolgeng-allesemester-popup">
				    <menuitem
				       id        =  "menu-dokumente-studienerfolgeng-allesemester-normal"
				       key       =  "menu-dokumente-studienerfolgeng-allesemester-normal:key"
				       label     = "&menu-dokumente-studienerfolgeng-allesemester-normal.label;"
				       command   =  "menu-dokumente-studienerfolgeng-allesemester-normal:command"
				       accesskey = "&menu-dokumente-studienerfolgeng-allesemester-normal.accesskey;"/>
				   	<menuitem
				       id        =  "menu-dokumente-studienerfolgeng-allesemester-finanzamt"
				       key       =  "menu-dokumente-studienerfolgeng-allesemester-finanzamt:key"
				       label     = "&menu-dokumente-studienerfolgeng-allesemester-finanzamt.label;"
				       command   =  "menu-dokumente-studienerfolgeng-allesemester-finanzamt:command"
				       accesskey = "&menu-dokumente-studienerfolgeng-allesemester-finanzamt.accesskey;"/>
				    </menupopup>
				</menu>
	          <?php

	          $qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 5";
	          $db = new basis_db();

	          if($db->db_query($qry))
	          {
	          	while($row = $db->db_fetch_object())
	          	{
	          		$stsem_kurzbz = $row->studiensemester_kurzbz;

					echo '
					<menu id="menu-dokumente-studienerfolg-menu" label="'.$stsem_kurzbz.'">
					  <menupopup id="menu-dokumente-studienerfolg-menu-popup">
					    <menuitem
					       id        =  "menu-dokumente-studienerfolgeng-menu-normal"
					       key       =  "menu-dokumente-studienerfolgeng-normal:key"
					       label     = "&menu-dokumente-studienerfolgeng-normal.label;"
					       oncommand   =  "StudentCreateStudienerfolg(event,\'StudienerfolgEng\',null, \''.$stsem_kurzbz.'\');"
					       accesskey = "&menu-dokumente-studienerfolgeng-normal.accesskey;"/>
					   	<menuitem
					       id        =  "menu-dokumente-studienerfolgeng-finanzamt"
					       key       =  "menu-dokumente-studienerfolgeng-finanzamt:key"
					       label     = "&menu-dokumente-studienerfolgeng-finanzamt.label;"
					       oncommand   =  "StudentCreateStudienerfolg(event,\'StudienerfolgEng\',\'finanzamt\', \''.$stsem_kurzbz.'\');"
					       accesskey = "&menu-dokumente-studienerfolgeng-finanzamt.accesskey;"/>
					    </menupopup>
					</menu>';
	          	}
	          }
	          	?>
	            <menuitem
	               id        =  "menu-dokumente-studienerfolgeng-normal"
	               key       =  "menu-dokumente-studienerfolgeng-normal:key"
	               label     = "&menu-dokumente-studienerfolgeng-normal.label;"
	               command   =  "menu-dokumente-studienerfolgeng-normal:command"
	               accesskey = "&menu-dokumente-studienerfolgeng-normal.accesskey;"/>
	           	<menuitem
	               id        =  "menu-dokumente-studienerfolgeng-finanzamt"
	               key       =  "menu-dokumente-studienerfolgeng-finanzamt:key"
	               label     = "&menu-dokumente-studienerfolgeng-finanzamt.label;"
	               command   =  "menu-dokumente-studienerfolgeng-finanzamt:command"
	               accesskey = "&menu-dokumente-studienerfolgeng-finanzamt.accesskey;"/>
	            </menupopup>
	        </menu>
			<menuseparator/>
						<menuitem
               id        =  "menu-dokumente-bescheid_deutsch"
               key       =  "menu-dokumente-bescheid_deutsch:key"
               label     = "&menu-dokumente-bescheid_deutsch.label;"
               command   =  "menu-dokumente-bescheid_deutsch:command"
               accesskey = "&menu-dokumente-bescheid_deutsch.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-diplsupplement"
               key       =  "menu-dokumente-diplsupplement:key"
               label     = "&menu-dokumente-diplsupplement.label;"
               command   =  "menu-dokumente-diplsupplement:command"
               accesskey = "&menu-dokumente-diplsupplement.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-pruefungsprotokoll2"
               key       =  "menu-dokumente-pruefungsprotokoll2:key"
               label     = "&menu-dokumente-pruefungsprotokoll2.label;"
               command   =  "menu-dokumente-pruefungsprotokoll2:command"
               accesskey = "&menu-dokumente-pruefungsprotokoll2.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-pruefungsprotokoll2_englisch"
               key       =  "menu-dokumente-pruefungsprotokoll2_englisch:key"
               label     = "&menu-dokumente-pruefungsprotokoll2_englisch.label;"
               command   =  "menu-dokumente-pruefungsprotokoll2_englisch:command"
               accesskey = "&menu-dokumente-pruefungsprotokoll2_englisch.accesskey;"/>
            <!--<menuitem
               id        =  "menu-dokumente-pruefungszeugnis"
               key       =  "menu-dokumente-pruefungszeugnis:key"
               label     = "&menu-dokumente-pruefungszeugnis.label;"
               command   =  "menu-dokumente-pruefungszeugnis:command"
               accesskey = "&menu-dokumente-pruefungszeugnis.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-pruefungszeugnis_englisch"
               key       =  "menu-dokumente-pruefungszeugnis_englisch:key"
               label     = "&menu-dokumente-pruefungszeugnis_englisch.label;"
               command   =  "menu-dokumente-pruefungszeugnis_englisch:command"
               accesskey = "&menu-dokumente-pruefungszeugnis_englisch.accesskey;"/>-->
			<menuitem
               id        =  "menu-dokumente-urkunde_deutsch"
               key       =  "menu-dokumente-urkunde_deutsch:key"
               label     = "&menu-dokumente-urkunde_deutsch.label;"
               command   =  "menu-dokumente-urkunde_deutsch:command"
               accesskey = "&menu-dokumente-urkunde_deutsch.accesskey;"/>
			<menuitem
               id        =  "menu-dokumente-urkunde_englisch"
               key       =  "menu-dokumente-urkunde_englisch:key"
               label     = "&menu-dokumente-urkunde_englisch.label;"
               command   =  "menu-dokumente-urkunde_englisch:command"
               accesskey = "&menu-dokumente-urkunde_englisch.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-zeugnis"
               key       =  "menu-dokumente-zeugnis:key"
               label     = "&menu-dokumente-zeugnis.label;"
               command   =  "menu-dokumente-zeugnis:command"
               accesskey = "&menu-dokumente-zeugnis.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-zeugniseng"
               key       =  "&menu-dokumente-zeugniseng.key;"
               label     = "&menu-dokumente-zeugniseng.label;"
               command   =  "menu-dokumente-zeugniseng:command"
               accesskey = "&menu-dokumente-zeugniseng.accesskey;"/>
          </menupopup>
    </menu>
    <!-- ***** CIS ***** -->
    <menu id="menu-cis" label="&menu-cis.label;" accesskey="&menu-cis.accesskey;">
          <menupopup id="menu-cis-popup">
            <menuitem
               id        =  "menu-cis-studienplan"
               key       =  "menu-cis-studienplan:key"
               label     = "&menu-cis-studienplan.label;"
               command   =  "menu-cis-studienplan:command"
               accesskey = "&menu-cis-studienplan.accesskey;"/>
            <menuitem
               id        =  "menu-cis-notenliste"
               key       =  "menu-cis-notenliste:key"
               label     = "&menu-cis-notenliste.label;"
               command   =  "menu-cis-notenliste:command"
               accesskey = "&menu-cis-notenliste.accesskey;"/>
          </menupopup>
    </menu>
	<?php
	if(!defined('FAS_MESSAGES') || FAS_MESSAGES==true)
	{
		?>
		<!-- ***** Messages ***** -->
	    <menu id="menu-messages" label="&menu-messages.label;" accesskey="&menu-messages.accesskey;">
	          <menupopup id="menu-messages-popup">
	            <menuitem
	               id        =  "menu-messages-new"
	               key       =  "menu-messages-new:key"
	               label     = "&menu-messages-new.label;"
	               command   =  "menu-messages-new:command"
	               accesskey = "&menu-messages-new.accesskey;"/>
	          </menupopup>
	    </menu>
		<?php
	}
	?>
    <!-- ***** Zusatzmenues inkludieren ***** -->
    <?php
    include('../include/'.EXT_FKT_PATH.'/fas_zusatzmenues.inc.php');
    ?>
    <!-- ********** EXTRAS ********* -->
    <menu id="menu-extras" label="&menu-extras.label;" accesskey="&menu-extras.accesskey;">
          <menupopup id="menu-extras-popup">
            <menuitem
               id        =  "menu-extras-reihungstest"
               key       =  "menu-extras-reihungstest:key"
               label     = "&menu-extras-reihungstest.label;"
               command   =  "menu-extras-reihungstest:command"
               accesskey = "&menu-extras-reihungstest.accesskey;"/>
            <menuitem
               id        =  "menu-extras-firma"
               key       =  "menu-extras-firma:key"
               label     = "&menu-extras-firma.label;"
               command   =  "menu-extras-firma:command"
               accesskey = "&menu-extras-firma.accesskey;"/>
            <menuitem
               id        =  "menu-extras-lvverwaltung"
               key       =  "menu-extras-lvverwaltung:key"
               label     = "&menu-extras-lvverwaltung.label;"
               command   =  "menu-extras-lvverwaltung:command"
               accesskey = "&menu-extras-lvverwaltung.accesskey;"/>
            <menuitem
               id        =  "menu-extras-studienordnung"
               key       =  "menu-extras-studienordnung:key"
               label     = "&menu-extras-studienordnung.label;"
               command   =  "menu-extras-studienordnung:command"
               accesskey = "&menu-extras-studienordnung.accesskey;"/>
             <menuitem
               id        =  "menu-extras-projektarbeitsabgaben"
               key       =  "menu-extras-projektarbeitsabgaben:key"
               label     = "&menu-extras-projektarbeitsabgaben.label;"
               command   =  "menu-extras-projektarbeitsabgaben:command"
               accesskey = "&menu-extras-projektarbeitsabgaben.accesskey;"/>
             <menuitem
               id        =  "menu-extras-aliquote_reduktion"
               key       =  "menu-extras-aliquote_reduktion:key"
               label     = "&menu-extras-aliquote_reduktion.label;"
               command   =  "menu-extras-aliquote_reduktion:command"
               accesskey = "&menu-extras-aliquote_reduktion.accesskey;"/>
             <menuitem
               id        =  "menu-extras-projektarbeitsbenotung"
               key       =  "menu-extras-projektarbeitsbenotung:key"
               label     = "&menu-extras-projektarbeitsbenotung.label;"
               command   =  "menu-extras-projektarbeitsbenotung:command"
               accesskey = "&menu-extras-projektarbeitsbenotung.accesskey;"/>
             <menuitem
               id        =  "menu-extras-gruppenverwaltung"
               key       =  "menu-extras-gruppenverwaltung:key"
               label     = "&menu-extras-gruppenverwaltung.label;"
               command   =  "menu-extras-gruppenverwaltung:command"
               accesskey = "&menu-extras-gruppenverwaltung.accesskey;"/>
<!--         <menuitem
               id        =  "menu-extras-lehrfachverwaltung"
               key       =  "menu-extras-lehrfachverwaltung:key"
               label     = "&menu-extras-lehrfachverwaltung.label;"
               command   =  "menu-extras-lehrfachverwaltung:command"
               accesskey = "&menu-extras-lehrfachverwaltung.accesskey;"/> -->
             <menuitem
               id        =  "menu-extras-preinteressentenuebernahme"
               key       =  "menu-extras-preinteressentenuebernahme:key"
               label     = "&menu-extras-preinteressentenuebernahme.label;"
               command   =  "menu-extras-preinteressentenuebernahme:command"
               accesskey = "&menu-extras-preinteressentenuebernahme.accesskey;"/>
          </menupopup>
    </menu>
    <!-- ********** BIS ********** -->
    <menu id="menu-bis" label="&menu-bis.label;" accesskey="&menu-bis.accesskey;">
		<menupopup id="menu-bis-popup">
            <menu id="menu-bis-mitarbeiter" label="&menu-bis-mitarbeiter.label;" accesskey="&menu-bis-mitarbeiter.accesskey;">
         		<menupopup id="menu-bis-mitarbeiter-popup">
					<menuitem
		               id        =  "menu-bis-mitarbeiter-uebersicht"
		               key       =  "menu-bis-mitarbeiter-uebersicht:key"
		               label     = "&menu-bis-mitarbeiter-uebersicht.label;"
		               command   =  "menu-bis-mitarbeiter-uebersicht:command"
		               accesskey = "&menu-bis-mitarbeiter-uebersicht.accesskey;"/>
         			<menuitem
		               id        =  "menu-bis-mitarbeiter-export"
		               key       =  "menu-bis-mitarbeiter-export:key"
		               label     = "&menu-bis-mitarbeiter-export.label;"
		               command   =  "menu-bis-mitarbeiter-export:command"
		               accesskey = "&menu-bis-mitarbeiter-export.accesskey;"/>
				</menupopup>
			</menu>
			<menu id="menu-bis-studenten" label="&menu-bis-studenten.label;" accesskey="&menu-bis-studenten.accesskey;">
         		<menupopup id="menu-bis-studenten-popup">
		            <menuitem
		               id        =  "menu-bis-studenten-export"
		               key       =  "menu-bis-studenten-export:key"
		               label     = "&menu-bis-studenten-export.label;"
		               command   =  "menu-bis-studenten-export:command"
		               accesskey = "&menu-bis-studenten-export.accesskey;"/>
		           <menuitem
		               id        =  "menu-bis-studenten-checkstudent"
		               key       =  "menu-bis-studenten-checkstudent:key"
		               label     = "&menu-bis-studenten-checkstudent.label;"
		               command   =  "menu-bis-studenten-checkstudent:command"
		               accesskey = "&menu-bis-studenten-checkstudent.accesskey;"/>
				</menupopup>
			</menu>
		</menupopup>
    </menu>
    <!-- HILFE -->
    <menu id="menu-help" label="&menu-help.label;" accesskey="&menu-help.accesskey;">
          <menupopup id="menu-about-popup">
            <menuitem
               id        =  "menu-help-manual"
               key       =  "menu-help-manual:key"
               label     = "&menu-help-manual.label;"
               command   =  "menu-help-manual:command"
               accesskey = "&menu-help-manual.accesskey;"/>
            <menuitem
               id        =  "menu-help-about"
               key       =  "menu-help-about:key"
               label     = "&menu-help-about.label;"
               command   =  "menu-help-about:command"
               accesskey = "&menu-help-about.accesskey;"/>
          </menupopup>
    </menu>
  </menubar>
</toolbox>
<!-- MENUE ENDE -->

<!-- TABS -->
<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs id="menu-content-tabs" orient="horizontal">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
			{
				echo '<tab id="tab-verband" label="Verband" onclick="ChangeTabVerband();"/>';
				//echo '<tab id="tab-fachbereich" label="Institut" onclick="ChangeTabsToLehrveranstaltung()"/>';
				echo '<tab id="tab-organisationseinheit" label="Org.einheit" tooltiptext="Organisationseinheit" onclick="ChangeTabsToLehrveranstaltung()"/>';
				echo '<tab id="tab-lektor" label="Lehrende" onclick="ChangeTabsToLehrveranstaltung()"/>';
			}
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tab id="tab-menu-mitarbeiter" label="Mitarbeitende" onclick="document.getElementById(\'main-content-tabs\').selectedItem=document.getElementById(\'tab-mitarbeiter\');"/>';
			}
		?>
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
			{
				echo '<tree id="tree-verband" />';
				//echo '<vbox id="vbox-fachbereich" />';
				echo '<vbox id="vbox-organisationseinheit" />';
				echo '<tree id="tree-lektor" />';
			}
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tree id="tree-menu-mitarbeiter"/>';
			}
		?>
		</tabpanels>
	</tabbox>
	<splitter collapse="before" persist="state">
		<grippy />
	</splitter>
	<vbox id="vbox-main" flex="15" />
</hbox>
<!-- TABS ENDE -->
<!-- STATUSBAR -->
<statusbar id="status-bar" persist="collapsed">

	<statusbarpanel>
		<toolbarbutton id="statusbarpanel-studiensemester-left"
				tooltiptext="1 Studiensemester zurueck"
				image="../skin/images/left.png"
				oncommand="studiensemesterChange('', -1)"
			/>
		<toolbarbutton id="statusbarpanel-semester" label="<?php echo $variable->variable->semester_aktuell; ?>" oncommand="getStudiensemesterVariable()"/>
		<toolbarbutton id="statusbarpanel-studiensemester-right"
				tooltiptext="1 Studiensemester vor"
				image="../skin/images/right.png"
				oncommand="studiensemesterChange('', 1)"
			/>
	</statusbarpanel>
	<statusbarpanel id="statusbarpanel-db_table" label="<?php echo DB_NAME; ?>"/>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel id="progress-panel" class="statusbarpanel-progress">
		<progressmeter id="statusbar-progressmeter" class="progressmeter-statusbar" mode="determined" value="0%"/>
	</statusbarpanel>

</statusbar>
</window>
