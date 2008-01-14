<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
include('../vilesci/config.inc.php');
include('../include/functions.inc.php');
include('../include/benutzerberechtigung.class.php');
include('../include/fas/benutzer.class.php');

$uid=get_uid();
$error_msg='';

//Variablen laden
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
$error_msg.=loadVariables($conn,$uid);

$benutzer = new benutzer($conn);
$benutzer->loadVariables($uid);

loadVariables($conn, $uid);
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($uid);

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
	title="&window.title; &window.version;"
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

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
  <command id="menu-prefs-stpltable-stundenplan:command" oncommand="stpltableChange('stundenplan');"/>
  <command id="menu-prefs-stpltable-stundenplandev:command" oncommand="stpltableChange('stundenplandev');"/>
  <command id="menu-prefs-ignore_kollision:command" oncommand="variableChange('ignore_kollision','menu-prefs-ignore_kollision');"/>
  <command id="menu-prefs-ignore_zeitsperre:command" oncommand="variableChange('ignore_zeitsperre','menu-prefs-ignore_zeitsperre');"/>
  <command id="menu-prefs-ignore_reservierung:command" oncommand="variableChange('ignore_reservierung','menu-prefs-ignore_reservierung');"/>
  <command id="menu-help-todo:command" oncommand="HelpOpenToDo();"/>
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
        <?php
        if($rechte->isBerechtigt('admin'))
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
        <menuitem
			 id        	="menu-prefs-ignore_kollision"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_kollision:key"
			 label     	="&menu-prefs-ignore_kollision.label;"
			 command   	="menu-prefs-ignore_kollision:command"
   			 accesskey 	="&menu-prefs-ignore_kollision.accesskey;"
   			 checkbox   ="<?php echo $ignore_kollision;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-ignore_zeitsperre"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_zeitsperre:key"
			 label     	="&menu-prefs-ignore_zeitsperre.label;"
			 command   	="menu-prefs-ignore_zeitsperre:command"
   			 accesskey 	="&menu-prefs-ignore_zeitsperre.accesskey;"
   			 checkbox   ="<?php echo $ignore_zeitsperre;?>"
   			 />
   		<menuitem
			 id        	="menu-prefs-ignore_reservierung"
			 type		="checkbox"
  			 key       	="menu-prefs-ignore_reservierung:key"
			 label     	="&menu-prefs-ignore_reservierung.label;"
			 command   	="menu-prefs-ignore_reservierung:command"
   			 accesskey 	="&menu-prefs-ignore_reservierung.accesskey;"
   			 checkbox   ="<?php echo $ignore_reservierung;?>"
   			 />
   		<?php
        }
        ?>
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
            <menuitem
               id        =  "menu-help-todo"
               key       = "&menu-help-todo.key;"
               label     = "&menu-help-todo.label;"
               command   =  "menu-help-todo:command"
               accesskey = "&menu-help-todo.accesskey;"/>
          </menupopup>
    </menu>
  </menubar>
</toolbox>
<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs orient="horizontal">
			<tab id="tab-verband" label="Verband" />
			<tab id="tab-ort" label="Ort" />
			<tab id="tab-lektor" label="Lektor" />
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
			<tree id="tree-verband" />
			<tree id="tree-ort" />
			<tree id="tree-lektor" />
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
		<toolbarbutton id="statusbarpanel-semester" label="<?php echo $semester_aktuell; ?>"/>
		<toolbarbutton id="statusbarpanel-studiensemester-right"
				tooltiptext="1 Studiensemester vor"
				image="../skin/images/right.png"
				oncommand="studiensemesterChange('', 1)"
			/>
	</statusbarpanel>
	<statusbarpanel id="statusbarpanel-db_table" label="<?php echo $db_stpl_table; ?>"/>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel id="progress-panel" class="statusbarpanel-progress">
		<progressmeter id="statusbar-progressmeter" class="progressmeter-statusbar" mode="determined" value="0%"/>
	</statusbarpanel>
	<statusbarpanel class="statusbarpanel-iconic" id="example-status" />
</statusbar>
</window>