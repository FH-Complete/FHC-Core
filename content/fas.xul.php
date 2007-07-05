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
  <command id="menu-statistic-koordinatorstunden:command" oncommand="StatistikPrintKoordinatorstunden();"/>
  <command id="menu-statistic-lehrauftraege:command" oncommand="StatistikPrintLehrauftraege();"/>
  <command id="menu-statistic-lvplanung:command" oncommand="StatistikPrintLVPlanung();"/>
  <command id="menu-statistic-lehrauftragsliste:command" oncommand="StatistikPrintLehrauftragsliste();"/>
  <command id="menu-dokumente-inskriptionsbestaetigung:command" oncommand="StudentPrintInskriptionsbestaetigung();"/>
  <command id="menu-help-close:command" oncommand="OpenAboutDialog()"/>
</commandset>

<keyset id="mainkeys">
  <key
     id        =  "menu-file-close:key"
     key       = "&menu-file-close.key;"
     observes  =  "menu-file-close:command"
     modifiers =  "accel" />
</keyset>

<toolbox id="main-toolbox">
  <menubar id="menu" >
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
    <menu id="menu-prefs" label="&menu-prefs.label;" accesskey="&menu-prefs.accesskey;">
		<menupopup id="menu-prefs-popup">
			<menu id="menu-prefs-stpltable" label="&menu-prefs-stpltable.label;" accesskey="&menu-prefs-stpltable.accesskey;">
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
	      	</menu>
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
	    </menupopup>
    </menu>
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
          </menupopup>
    </menu>
    <menu id="menu-dokumente" label="&menu-dokumente.label;" accesskey="&menu-dokumente.accesskey;">
          <menupopup id="menu-dokumente-popup">
            <menuitem
               id        =  "menu-dokumente-inskriptionsbestaetigung"
               key       =  "menu-dokumente-inskriptionsbestaetigung:key"
               label     = "&menu-dokumente-inskriptionsbestaetigung.label;"
               command   =  "menu-dokumente-inskriptionsbestaetigung:command"
               accesskey = "&menu-dokumente-inskriptionsbestaetigung.accesskey;"/>
          </menupopup>
    </menu>
    <menu id="menu-help" label="&menu-help.label;" accesskey="&menu-help.accesskey;">
          <menupopup id="menu-about-popup">
            <menuitem
               id        =  "menu-help-close"
               key       =  "menu-help-close:key"
               label     = "&menu-help-close.label;"
               command   =  "menu-help-close:command"
               accesskey = "&menu-help-close.accesskey;"/>
          </menupopup>
    </menu>
  </menubar>
</toolbox>
<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs id="menu-content-tabs" orient="horizontal">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('lva-verwaltung'))
			{
				echo '<tab id="tab-verband" label="Verband" />';
				echo '<tab id="tab-fachbereich" label="Fachbereich" />';
				echo '<tab id="tab-lektor" label="Lektor" />';
			}
			if($rechte->isBerechtigt('admin','0') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tab id="tab-menu-mitarbeiter" label="Mitarbeiter" onclick="document.getElementById(\'main-content-tabs\').selectedItem=document.getElementById(\'tab-mitarbeiter\');"/>';
			}
		?>
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('lva-verwaltung'))
			{
				echo '<tree id="tree-verband" />';
				echo '<tree id="tree-fachbereich" />';
				echo '<tree id="tree-lektor" />';
			}
			if($rechte->isBerechtigt('admin','0') || $rechte->isBerechtigt('mitarbeiter'))
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