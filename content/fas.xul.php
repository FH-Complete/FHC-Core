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
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/fas/benutzer.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');

// Testumgebung
$user=get_uid();

$error_msg='';

//Variablen laden
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
$error_msg.=loadVariables($conn,$user);

$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	$error_msg = $benutzer->errormsg;
	
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';*/
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css" ?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/fasoverlay.xul.php"?>';
echo '<?xml-stylesheet href="datepicker/datepicker.css" type="text/css"?>';
?>
<!DOCTYPE window [
	<?php require("../locale/de-AT/fas.dtd"); ?>
]>

<!-- - <?php echo $semester_aktuell; ?>  -->
<window
	id="fas"
	title="&window.title; - Version &window.version;"
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

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
  <command id="menu-prefs-stpltable-stundenplan:command" oncommand="stpltableChange('stundenplan');"/>
  <command id="menu-prefs-stpltable-stundenplandev:command" oncommand="stpltableChange('stundenplandev');"/>
  <command id="menu-prefs-kontofilterstg:command" oncommand="EinstellungenKontoFilterStgChange();"/>
  <command id="menu-statistic-koordinatorstunden:command" oncommand="StatistikPrintKoordinatorstunden();"/>
  <command id="menu-statistic-lehrauftraege:command" oncommand="StatistikPrintLehrauftraege();"/>
  <command id="menu-statistic-lvplanung:command" oncommand="StatistikPrintLVPlanung();"/>
  <command id="menu-statistic-lehrauftragsliste:command" oncommand="StatistikPrintLehrauftragsliste();"/>
  <command id="menu-statistic-projektarbeit:command" oncommand="StatistikPrintProjektarbeit();"/>
  <command id="menu-statistic-abschlusspruefung:command" oncommand="StatistikPrintAbschlusspruefung();"/>
  <command id="menu-statistic-notenspiegel:command" oncommand="StatistikPrintNotenspiegel('html');"/>
  <command id="menu-statistic-notenspiegel-excel:command" oncommand="StatistikPrintNotenspiegel('xls');"/>
  <command id="menu-statistic-bewerberstatistik:command" oncommand="StatistikPrintBewerberstatistik();"/>
  <command id="menu-statistic-substatistik-studentenprosemester-excel:command" oncommand="StatistikPrintStudentenProSemester('xls');"/>
  <command id="menu-statistic-substatistik-studentenprosemester-html:command" oncommand="StatistikPrintStudentenProSemester('');"/>
  <command id="menu-statistic-substatistik-alvsstatistik-excel:command" oncommand="StatistikPrintALVSStatistik('xls');"/>
  <command id="menu-statistic-substatistik-alvsstatistik-html:command" oncommand="StatistikPrintALVSStatistik('');"/>  
  <command id="menu-statistic-substatistik-lvplanunggesamtsj-excel:command" oncommand="StatistikPrintLvPlanungGesamtSJ();"/>  
  <command id="menu-dokumente-inskriptionsbestaetigung:command" oncommand="StudentPrintInskriptionsbestaetigung();"/>
  <command id="menu-dokumente-zeugnis:command" oncommand="StudentCreateZeugnis();"/>
  <command id="menu-dokumente-diplsupplement:command" oncommand="StudentCreateDiplSupplement();"/>
  <command id="menu-dokumente-studienerfolg-normal:command" oncommand="StudentCreateStudienerfolg();"/>
  <command id="menu-dokumente-studienerfolg-finanzamt:command" oncommand="StudentCreateStudienerfolg('finanzamt');"/>
  <command id="menu-dokumente-studienerfolg-allesemester-normal:command" oncommand="StudentCreateStudienerfolg('', '', 'true');"/>
  <command id="menu-dokumente-studienerfolg-allesemester-finanzamt:command" oncommand="StudentCreateStudienerfolg('finanzamt', '', 'true');"/>
  <command id="menu-dokumente-accountinfoblatt:command" oncommand="PrintAccountInfoBlatt();"/>  
  <command id="menu-dokumente-pruefungsprotokoll:command" oncommand="StudentAbschlusspruefungPrintPruefungsprotokollMultiple();"/>
  <command id="menu-dokumente-pruefungszeugnis:command" oncommand="StudentAbschlusspruefungPrintPruefungszeugnisMultiple();"/>
  <command id="menu-dokumente-urkunde_deutsch:command" oncommand="StudentAbschlusspruefungPrintUrkundeMultiple('deutsch')"/>
  <command id="menu-dokumente-urkunde_englisch:command" oncommand="StudentAbschlusspruefungPrintUrkundeMultiple('englisch')"/>
  <command id="menu-extras-reihungstest:command" oncommand="ExtrasShowReihungstest();"/>
  <command id="menu-extras-firma:command" oncommand="ExtrasShowFirmenverwaltung();"/>
  <command id="menu-extras-lvverwaltung:command" oncommand="ExtrasShowLVverwaltung();"/>
  <command id="menu-extras-projektarbeitsbenotung:command" oncommand="ExtrasShowProjektarbeitsBenotung();"/>
  <command id="menu-bis-mitarbeiter-import:command" oncommand="BISMitarbeiterImport();"/>
  <command id="menu-bis-mitarbeiter-export:command" oncommand="BISMitarbeiterExport();"/>
  <command id="menu-bis-mitarbeiter-checkverwendung:command" oncommand="BISMitarbeiterCheckVerwendung();"/>
  <command id="menu-bis-mitarbeiter-checkfunktion:command" oncommand="BISMitarbeiterCheckFunktion();"/>
  <command id="menu-bis-studenten-plausibilitaetspruefung:command" oncommand="BISStudentenPlausicheck();"/>
  <command id="menu-bis-studenten-export:command" oncommand="BISStudentenExport();"/>
  <command id="menu-help-close:command" oncommand="OpenAboutDialog()"/>
  <command id="menu-help-todo:command" oncommand="OpenToDoDialog()"/>
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
       		$stsem_arr = $benutzer->getpossibilities('semester_aktuell');
       		foreach ($stsem_arr as $stsem)
       		{
  				echo "
			<menuitem
				id = 'menu-properies-studiensemester-name'
				label = '$stsem'
				type = 'radio'
				command = 'menu-properties-studiensemester:command'
				checked = ".($benutzer->variable->semester_aktuell==$stsem?"'true' ":"'false'")." />";
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
   			 checkbox   ="<?php echo $kontofilterstg;?>"
   			 />
	    </menupopup>
    </menu>
    <!-- ********** BERICHTE ********** -->
    <menu id="menu-statistic" label="&menu-statistic.label;" accesskey="&menu-statistic.accesskey;">
          <menupopup id="menu-statistic-popup">
            <menuitem
               id        =  "menu-statistic-koordinatorstunden"
               key       =  "menu-statistic-koordinatorstunden:key"
               label     = "&menu-statistic-koordinatorstunden.label;"
               command   =  "menu-statistic-koordinatorstunden:command"
               accesskey = "&menu-statistic-koordinatorstunden.accesskey;"/>
             <menuitem
               id        =  "menu-statistic-lehrauftraege"
               key       =  "menu-statistic-lehrauftraege:key"
               label     = "&menu-statistic-lehrauftraege.label;"
               command   =  "menu-statistic-lehrauftraege:command"
               accesskey = "&menu-statistic-lehrauftraege.accesskey;"/>
            <menuitem
               id        =  "menu-statistic-lvplanung"
               key       =  "menu-statistic-lvplanung:key"
               label     = "&menu-statistic-lvplanung.label;"
               command   =  "menu-statistic-lvplanung:command"
               accesskey = "&menu-statistic-lvplanung.accesskey;"/>
             <menuitem
               id        =  "menu-statistic-lehrauftragsliste"
               key       =  "menu-statistic-lehrauftragsliste:key"
               label     = "&menu-statistic-lehrauftragsliste.label;"
               command   =  "menu-statistic-lehrauftragsliste:command"
               accesskey = "&menu-statistic-lehrauftragsliste.accesskey;"/>
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
             <menu id="menu-statistic-subnotenspiegel" label="&menu-statistic-subnotenspiegel.label;" accesskey="&menu-statistic-subnotenspiegel.accesskey;">
				<menupopup id="menu-statistic-subnotenspiegel-popup">
		             <menuitem
		               id        =  "menu-statistic-notenspiegel-excel"
		               key       =  "menu-statistic-notenspiegel-excel:key"
		               label     = "&menu-statistic-notenspiegel-excel.label;"
		               command   =  "menu-statistic-notenspiegel-excel:command"
		               accesskey = "&menu-statistic-notenspiegel-excel.accesskey;"/>
		             <menuitem
		               id        =  "menu-statistic-notenspiegel"
		               key       =  "menu-statistic-notenspiegel:key"
		               label     = "&menu-statistic-notenspiegel.label;"
		               command   =  "menu-statistic-notenspiegel:command"
		               accesskey = "&menu-statistic-notenspiegel.accesskey;"/>
				</menupopup>
			</menu>
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
					<?php
					if($rechte->isBerechtigt('admin'))
					{
						echo '
						<menu id="menu-statistic-substatistik-lvplanunggesamtsj" label="&menu-statistic-substatistik-lvplanunggesamtsj.label;" accesskey="&menu-statistic-substatistik-lvplanunggesamtsj.accesskey;">
							<menupopup id="menu-statistic-substatistik-lvplanunggesamtsj-popup">
					             <menuitem
					               id        =  "menu-statistic-substatistik-lvplanunggesamtsj-excel"
					               key       =  "menu-statistic-substatistik-lvplanunggesamtsj-excel:key"
					               label     = "&menu-statistic-substatistik-lvplanunggesamtsj-excel.label;"
					               command   =  "menu-statistic-substatistik-lvplanunggesamtsj-excel:command"
					               accesskey = "&menu-statistic-substatistik-lvplanunggesamtsj-excel.accesskey;"/>
							</menupopup>
						</menu>
						';
					}
					?>
				</menupopup>
			</menu>
          <!--   <menuitem
               id        =  "menu-statistic-bewerberstatistik"
               key       =  "menu-statistic-bewerberstatistik:key"
               label     = "&menu-statistic-bewerberstatistik.label;"
               command   =  "menu-statistic-bewerberstatistik:command"
               accesskey = "&menu-statistic-bewerberstatistik.accesskey;"/>-->
          </menupopup>
    </menu>
    <!-- ********** DOKUMENTE ********** -->
    <menu id="menu-dokumente" label="&menu-dokumente.label;" accesskey="&menu-dokumente.accesskey;">
          <menupopup id="menu-dokumente-popup">
            <menuitem
               id        =  "menu-dokumente-inskriptionsbestaetigung"
               key       =  "menu-dokumente-inskriptionsbestaetigung:key"
               label     = "&menu-dokumente-inskriptionsbestaetigung.label;"
               command   =  "menu-dokumente-inskriptionsbestaetigung:command"
               accesskey = "&menu-dokumente-inskriptionsbestaetigung.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-zeugnis"
               key       =  "menu-dokumente-zeugnis:key"
               label     = "&menu-dokumente-zeugnis.label;"
               command   =  "menu-dokumente-zeugnis:command"
               accesskey = "&menu-dokumente-zeugnis.accesskey;"/>
            <menuitem
               id        =  "menu-dokumente-diplsupplement"
               key       =  "menu-dokumente-diplsupplement:key"
               label     = "&menu-dokumente-diplsupplement.label;"
               command   =  "menu-dokumente-diplsupplement:command"
               accesskey = "&menu-dokumente-diplsupplement.accesskey;"/>
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
	          if($result = pg_query($conn, $qry))
	          {
	          	while($row = pg_fetch_object($result))
	          	{
	          		$stsem_kurzbz = $row->studiensemester_kurzbz;
	          		
					echo '
					<menu id="menu-dokumente-studienerfolg-menu" label="'.$stsem_kurzbz.'">
					  <menupopup id="menu-dokumente-studienerfolg-menu-popup">
					    <menuitem
					       id        =  "menu-dokumente-studienerfolg-menu-normal"
					       key       =  "menu-dokumente-studienerfolg-normal:key"
					       label     = "&menu-dokumente-studienerfolg-normal.label;"
					       oncommand   =  "StudentCreateStudienerfolg(null, \''.$stsem_kurzbz.'\');"
					       accesskey = "&menu-dokumente-studienerfolg-normal.accesskey;"/>
					   	<menuitem
					       id        =  "menu-dokumente-studienerfolg-finanzamt"
					       key       =  "menu-dokumente-studienerfolg-finanzamt:key"
					       label     = "&menu-dokumente-studienerfolg-finanzamt.label;"
					       oncommand   =  "StudentCreateStudienerfolg(\'finanzamt\', \''.$stsem_kurzbz.'\');"
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
	         <menuitem
               id        =  "menu-dokumente-accountinfoblatt"
               key       =  "menu-dokumente-accountinfoblatt:key"
               label     = "&menu-dokumente-accountinfoblatt.label;"
               command   =  "menu-dokumente-accountinfoblatt:command"
               accesskey = "&menu-dokumente-accountinfoblatt.accesskey;"/>
			<menuseparator/>
			<menuitem
               id        =  "menu-dokumente-pruefungsprotokoll"
               key       =  "menu-dokumente-pruefungsprotokoll:key"
               label     = "&menu-dokumente-pruefungsprotokoll.label;"
               command   =  "menu-dokumente-pruefungsprotokoll:command"
               accesskey = "&menu-dokumente-pruefungsprotokoll.accesskey;"/>
			<menuitem
               id        =  "menu-dokumente-pruefungszeugnis"
               key       =  "menu-dokumente-pruefungszeugnis:key"
               label     = "&menu-dokumente-pruefungszeugnis.label;"
               command   =  "menu-dokumente-pruefungszeugnis:command"
               accesskey = "&menu-dokumente-pruefungszeugnis.accesskey;"/>
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
          </menupopup>
    </menu>
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
               id        =  "menu-extras-projektarbeitsbenotung"
               key       =  "menu-extras-projektarbeitsbenotung:key"
               label     = "&menu-extras-projektarbeitsbenotung.label;"
               command   =  "menu-extras-projektarbeitsbenotung:command"
               accesskey = "&menu-extras-projektarbeitsbenotung.accesskey;"/>
          </menupopup>
    </menu>
    <!-- ********** BIS ********** -->
    <menu id="menu-bis" label="&menu-bis.label;" accesskey="&menu-bis.accesskey;">
		<menupopup id="menu-bis-popup">
            <menu id="menu-bis-mitarbeiter" label="&menu-bis-mitarbeiter.label;" accesskey="&menu-bis-mitarbeiter.accesskey;">
         		<menupopup id="menu-bis-mitarbeiter-popup">
         			<menuitem
		               id        =  "menu-bis-mitarbeiter-checkverwendung"
		               key       =  "menu-bis-mitarbeiter-checkverwendung:key"
		               label     = "&menu-bis-mitarbeiter-checkverwendung.label;"
		               command   =  "menu-bis-mitarbeiter-checkverwendung:command"
		               accesskey = "&menu-bis-mitarbeiter-checkverwendung.accesskey;"/>
		            <menuitem
		               id        =  "menu-bis-mitarbeiter-checkfunktion"
		               key       =  "menu-bis-mitarbeiter-checkfunktion:key"
		               label     = "&menu-bis-mitarbeiter-checkfunktion.label;"
		               command   =  "menu-bis-mitarbeiter-checkfunktion:command"
		               accesskey = "&menu-bis-mitarbeiter-checkfunktion.accesskey;"/>
         			<menuitem
		               id        =  "menu-bis-mitarbeiter-export"
		               key       =  "menu-bis-mitarbeiter-export:key"
		               label     = "&menu-bis-mitarbeiter-export.label;"
		               command   =  "menu-bis-mitarbeiter-export:command"
		               accesskey = "&menu-bis-mitarbeiter-export.accesskey;"/>
		            <menuitem
		               id        =  "menu-bis-mitarbeiter-import"
		               key       =  "menu-bis-mitarbeiter-import:key"
		               label     = "&menu-bis-mitarbeiter-import.label;"
		               command   =  "menu-bis-mitarbeiter-import:command"
		               accesskey = "&menu-bis-mitarbeiter-import.accesskey;"/>
				</menupopup>
			</menu>
			<menu id="menu-bis-studenten" label="&menu-bis-studenten.label;" accesskey="&menu-bis-studenten.accesskey;">
         		<menupopup id="menu-bis-studenten-popup">
         			<menuitem
		               id        =  "menu-bis-studenten-plausibilitaetspruefung"
		               key       =  "menu-bis-studenten-plausibilitaetspruefung:key"
		               label     = "&menu-bis-studenten-plausibilitaetspruefung.label;"
		               command   =  "menu-bis-studenten-plausibilitaetspruefung:command"
		               accesskey = "&menu-bis-studenten-plausibilitaetspruefung.accesskey;"/>
		            <menuitem
		               id        =  "menu-bis-studenten-export"
		               key       =  "menu-bis-studenten-export:key"
		               label     = "&menu-bis-studenten-export.label;"
		               command   =  "menu-bis-studenten-export:command"
		               accesskey = "&menu-bis-studenten-export.accesskey;"/>
				</menupopup>
			</menu>
		</menupopup>
    </menu>
    <!-- HILFE -->
    <menu id="menu-help" label="&menu-help.label;" accesskey="&menu-help.accesskey;">
          <menupopup id="menu-about-popup">
            <menuitem
               id        =  "menu-help-close"
               key       =  "menu-help-close:key"
               label     = "&menu-help-close.label;"
               command   =  "menu-help-close:command"
               accesskey = "&menu-help-close.accesskey;"/>
            <menuitem
               id        =  "menu-help-todo"
               key       =  "menu-help-todo:key"
               label     = "&menu-help-todo.label;"
               command   =  "menu-help-todo:command"
               accesskey = "&menu-help-todo.accesskey;"/>
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
				echo '<tab id="tab-verband" label="Verband" onclick="ChangeTabVerband()"/>';
				echo '<tab id="tab-fachbereich" label="Fachbereich" onclick="ChangeTabsToLehrveranstaltung()"/>';
				echo '<tab id="tab-lektor" label="Lektor" onclick="ChangeTabsToLehrveranstaltung()"/>';
			}
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tab id="tab-menu-mitarbeiter" label="Mitarbeiter" onclick="document.getElementById(\'main-content-tabs\').selectedItem=document.getElementById(\'tab-mitarbeiter\');"/>';
			}
		?>
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
			{
				echo '<tree id="tree-verband" />';
				echo '<vbox id="vbox-fachbereich" />';
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
	<statusbarpanel class="statusbarpanel-iconic" id="logo-icon" />
	<statusbarpanel id="statusbarpanel-semester" label="<?php echo $semester_aktuell; ?>"/>
	<statusbarpanel id="statusbarpanel-db_table" label="<?php echo substr(CONN_STRING,strpos(CONN_STRING,'dbname=')+7,strpos(CONN_STRING,'user=')-strpos(CONN_STRING,'dbname=')-7); ?>"/>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel id="progress-panel" class="statusbarpanel-progress">
		<progressmeter id="statusbar-progressmeter" class="progressmeter-statusbar" mode="determined" value="0%"/>
	</statusbarpanel>
	<statusbarpanel class="statusbarpanel-iconic" id="example-status" />
</statusbar>
</window>