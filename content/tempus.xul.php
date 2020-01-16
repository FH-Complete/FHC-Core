<?php
/* Copyright (C) 2014 fhcomplete.org
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
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');

$uid=get_uid();
$error_msg='';

loadVariables($uid);

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/tempus'))
	die('Sie haben keine Berechtigung fuer diese Seite');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';*/
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/tempusoverlay.xul.php"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css" ?>';
echo '<?xml-stylesheet href="datepicker/datepicker.css" type="text/css"?>';
?>

<!DOCTYPE window [
	<?php require("../locale/de-AT/tempus.dtd"); ?>
]>

<window
	id="tempus"
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
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
  <command id="menu-prefs-stpltable-stundenplan:command" oncommand="stpltableChange('stundenplan');"/>
  <command id="menu-prefs-stpltable-stundenplandev:command" oncommand="stpltableChange('stundenplandev');"/>
  <command id="menu-prefs-ignore_kollision:command" oncommand="variableChange('ignore_kollision','menu-prefs-ignore_kollision');"/>
  <command id="menu-prefs-ignore_zeitsperre:command" oncommand="variableChange('ignore_zeitsperre','menu-prefs-ignore_zeitsperre');"/>
  <command id="menu-prefs-ignore_reservierung:command" oncommand="variableChange('ignore_reservierung','menu-prefs-ignore_reservierung');"/>
  <command id="menu-prefs-alle_unr_mitladen:command" oncommand="variableChange('alle_unr_mitladen','menu-prefs-alle_unr_mitladen');"/>
  <command id="menu-prefs-allow_lehrstunde_drop:command" oncommand="variableChange('allow_lehrstunde_drop','menu-prefs-allow_lehrstunde_drop');"/>
  <command id="menu-prefs-kollision_student:command" oncommand="variableChange('kollision_student','menu-prefs-kollision_student');"/>
  <command id="menu-prefs-max_kollision:command" oncommand="variableChangeValue('max_kollision');"/>
  <command id="menu-extras-kollisionstudent:command" oncommand="KollisionStudentShow();"/>
  <command id="menu-extras-lvplanwartung:command" oncommand="LVPlanWartungShow();"/>
  <command id="menu-extras-rescheck:command" oncommand="ResCheckShow();"/>
  <command id="menu-extras-synclvplan:command" oncommand="SyncLVPlan();"/>
  <command id="menu-help-about:command" oncommand="OpenAboutDialog()"/>
  <command id="menu-help-manual:command" oncommand="OpenManualTempus();"/>
</commandset>

<keyset id="mainkeys">
  <key
     id        =  "menu-file-close:key"
     key       = "&menu-file-close.key;"
     observes  =  "menu-file-close:command"
     modifiers =  "accel" />
	<key id="keycode_ignore_kollision" keycode="VK_F9" oncommand="toggleIgnoreKollision()"/>
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
    <menu id="menu-edit" label="&menu-edit.label;" accesskey="&menu-edit.accesskey;" onclick="">
      <menupopup id="menu-edit-popup" onpopupshowing="loadUndoList();">
        <menu id="menu-edit-undo" label="&menu-edit-undo.label;"
           datasources="rdf:null"
           ref="http://www.technikum-wien.at/undo/liste"
        >
           	<template>
	        		<rule>
	     	 			<menupopup id="menu-edit-undo-popup">
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
				checked = ".($semester_aktuell==$stsem->studiensemester_kurzbz?"'true' ":"'false'")." />";
       		}
       ?>

      		</menupopup>
        </menu>
        <?php
        if($rechte->isBerechtigt('lv-plan'))
        {
        ?>	
        <menu id="menu-prefs-stpltable" label="&menu-prefs-stpltable.label;" accesskey="&menu-prefs-stpltable.accesskey;">
				<menupopup id="menu-prefs-stpltable-popup">
	        		<menuitem
	          			 id     	="menu-prefs-stpltable-stundenplan"
	          			 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplan:key"
	         			 label     	="&menu-prefs-stpltable-stundenplan.label;"
	         			 command   	="menu-prefs-stpltable-stundenplan:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplan.accesskey;"
	           			 checked	="<?php echo ($db_stpl_table=='stundenplan'?'true':'false');?>" />
	           		<menuitem
						 id        	="menu-prefs-stpltable-stundenplandev"
						 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplandev:key"
						 label     	="&menu-prefs-stpltable-stundenplandev.label;"
						 command   	="menu-prefs-stpltable-stundenplandev:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplandev.accesskey;"
	           			 checked	="<?php echo ($db_stpl_table=='stundenplandev'?'true':'false');?>" />
	      		</menupopup>
	      	</menu>
        <menuitem
			 id        	="menu-prefs-ignore_kollision"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_kollision:key"
			 label     	="&menu-prefs-ignore_kollision.label;"
			 command   	="menu-prefs-ignore_kollision:command"
   			 accesskey 	="&menu-prefs-ignore_kollision.accesskey;"
   			 checkbox   ="true"
   			 checked    ="<?php echo $ignore_kollision;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-ignore_zeitsperre"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_zeitsperre:key"
			 label     	="&menu-prefs-ignore_zeitsperre.label;"
			 command   	="menu-prefs-ignore_zeitsperre:command"
   			 accesskey 	="&menu-prefs-ignore_zeitsperre.accesskey;"
   			 checkbox   ="true"
   			 checked   ="<?php echo $ignore_zeitsperre;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-ignore_reservierung"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_reservierung:key"
			 label     	="&menu-prefs-ignore_reservierung.label;"
			 command   	="menu-prefs-ignore_reservierung:command"
   			 accesskey 	="&menu-prefs-ignore_reservierung.accesskey;"
   			 checkbox   ="true"
   			 checked   ="<?php echo $ignore_reservierung;?>"
   			 />
   		 <menuitem
			 id        	="menu-prefs-kollision_student"
			 type		="checkbox"
  			 key       	="menu-prefs-kollision_student:key"
			 label     	="&menu-prefs-kollision_student.label;"
			 command   	="menu-prefs-kollision_student:command"
   			 accesskey 	="&menu-prefs-kollision_student.accesskey;"
   			 checkbox   ="true"
   			 checked    ="<?php echo $kollision_student;?>"
   			 />
		<menuitem
			 id        	="menu-prefs-alle_unr_mitladen"
			 type		="checkbox"
  			 key       	="menu-prefs-alle_unr_mitladen:key"
			 label     	="&menu-prefs-alle_unr_mitladen.label;"
			 command   	="menu-prefs-alle_unr_mitladen:command"
   			 accesskey 	="&menu-prefs-alle_unr_mitladen.accesskey;"
   			 checkbox   ="true"
   			 checked    ="<?php echo $alle_unr_mitladen;?>"
   			 />
		<menuitem
			 id        	="menu-prefs-allow_lehrstunde_drop"
			 type		="checkbox"
  			 key       	="menu-prefs-allow_lehrstunde_drop:key"
			 label     	="&menu-prefs-allow_lehrstunde_drop.label;"
			 command   	="menu-prefs-allow_lehrstunde_drop:command"
   			 accesskey 	="&menu-prefs-allow_lehrstunde_drop.accesskey;"
   			 checkbox   ="true"
   			 checked    ="<?php echo $allow_lehrstunde_drop;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-max_kollision"
  			 key       	="menu-prefs-max_kollision:key"
			 label     	="&menu-prefs-max_kollision.label;"
			 command   	="menu-prefs-max_kollision:command"
   			 accesskey 	="&menu-prefs-max_kollision.accesskey;"
   			 value    	="<?php echo $max_kollision;?>"
   			 />
   		<?php
        }
        ?>
	    </menupopup>
    </menu>
    <menu id="menu-extras" label="&menu-extras.label;" accesskey="&menu-extras.accesskey;">
          <menupopup id="menu-extras-popup">
            <menuitem
               id        =  "menu-extras-kollisionstudent"
               key       =  "menu-extras-kollisionstudent:key"
               label     = "&menu-extras-kollisionstudent.label;"
               command   =  "menu-extras-kollisionstudent:command"
               accesskey = "&menu-extras-kollisionstudent.accesskey;"/>
            <menuitem
               id        =  "menu-extras-lvplanwartung"
               key       =  "menu-extras-lvplanwartung:key"
               label     = "&menu-extras-lvplanwartung.label;"
               command   =  "menu-extras-lvplanwartung:command"
               accesskey = "&menu-extras-lvplanwartung.accesskey;"/>
            <menuitem
               id        =  "menu-extras-rescheck"
               key       =  "menu-extras-rescheck:key"
               label     = "&menu-extras-rescheck.label;"
               command   =  "menu-extras-rescheck:command"
               accesskey = "&menu-extras-rescheck.accesskey;"/>
            <menuitem
               id        =  "menu-extras-synclvplan"
               key       =  "menu-extras-synclvplan:key"
               label     = "&menu-extras-synclvplan.label;"
               command   =  "menu-extras-synclvplan:command"
               accesskey = "&menu-extras-synclvplan.accesskey;"/>
          </menupopup>
    </menu>
    <menu id="menu-help" label="&menu-help.label;" accesskey="&menu-help.accesskey;">
          <menupopup id="menu-about-popup">
            <menuitem
               id        =  "menu-help-about"
               key       =  "menu-help-about:key"
               label     = "&menu-help-about.label;"
               command   =  "menu-help-about:command"
               accesskey = "&menu-help-about.accesskey;"/>
            <menuitem
               id        =  "menu-help-manual"
               key       =  "menu-help-manual:key"
               label     = "&menu-help-manual.label;"
               command   =  "menu-help-manual:command"
               accesskey = "&menu-help-manual.accesskey;"/>
          </menupopup>
    </menu>
  </menubar>
</toolbox>
<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs orient="horizontal">
			<tab id="tab-verband" label="Verband" />
			<tab id="tab-ort" label="Ort" />
			<tab id="tab-fachbereich" label="Institut" onclick="ChangeTabsToLehrveranstaltung()"/>
			<tab id="tab-lektor" label="Lehrende" />
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
			<tree id="tree-verband" />
			<tree id="tree-ort" />
			<vbox id="vbox-fachbereich" />
			<vbox id="vbox-lektor" />
		</tabpanels>
	</tabbox>
	<splitter collapse="before" persist="state">
		<grippy />
	</splitter>
	<vbox id="vbox-main" flex="15" />
</hbox>

<statusbar id="status-bar" persist="collapsed">
	<statusbarpanel class="statusbarpanel-iconic" id="logo-icon" />
	<statusbarpanel>
		<toolbarbutton id="statusbarpanel-studiensemester-left"
				tooltiptext="1 Studiensemester zurueck"
				image="../skin/images/left.png"
				oncommand="studiensemesterChange('', -1)"
			/>
		<toolbarbutton id="statusbarpanel-semester" label="<?php echo $semester_aktuell; ?>" oncommand="getStudiensemesterVariable()"/>
		<toolbarbutton id="statusbarpanel-studiensemester-right"
				tooltiptext="1 Studiensemester vor"
				image="../skin/images/right.png"
				oncommand="studiensemesterChange('', 1)"
			/>
	</statusbarpanel>
	<?php
		if($rechte->isBerechtigt('system/developer'))
			echo '<statusbarpanel label="'.DB_NAME.'"/>';
	?>
	<statusbarpanel id="statusbarpanel-db_table" label="<?php echo $db_stpl_table; ?>"/>
	<statusbarpanel>
		<toolbarbutton id="statusbarpanel-ignore_kollision" label="Kollisionscheck <?php echo ($ignore_kollision=='true'?'AUS':'AN'); ?>" <?php echo ($ignore_kollision=='true'?'style="background-color: red;"':'');?> oncommand="updateignorekollision()"/>
	</statusbarpanel>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel id="progress-panel" class="statusbarpanel-progress">
		<progressmeter id="statusbar-progressmeter" class="progressmeter-statusbar" mode="determined" value="0%"/>
	</statusbarpanel>
	<statusbarpanel class="statusbarpanel-iconic" id="example-status" />
</statusbar>
</window>
