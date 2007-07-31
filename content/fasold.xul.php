<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
include('../vilesci/config.inc.php');
include('../include/functions.inc.php');
include('../include/fas/benutzer.class.php');

$user = get_uid();

$error_msg='';

//Variablen laden
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	$error_msg = $benutzer->errormsg;


/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';*/
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/fas.css" type="text/css"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/fasold/fasoldoverlay.xul.php"?>';
?>

<!DOCTYPE window >

<window
	id="fas"
	title="FAS-Online Version 0.4 vom 07. November 2006"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	orient="vertical"
	width="800"
  	height="600"
  	persist="screenX screenY width height sizemode"
  	onload="onLoad()" onclose="return closeWindow()"
  	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasold/fasold.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<!--
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js"/>
-->

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
</commandset>

<keyset id="mainkeys">
  <key
     id        =  "menu-file-close:key"
     key       = "D"
     observes  =  "menu-file-close:command"
     modifiers =  "accel" />
</keyset>

<toolbox id="main-toolbox">
  <menubar id="menu">
    <menu id="menu-file" label="Datei" accesskey="D">
      <menupopup id="menu-file-popup">
        <menuitem
           id        =  "menu-file-close"
           key       =  "menu-file-close:key"
           label     = "Beenden"
           command   =  "menu-file-close:command"
           accesskey = "B"/>
      </menupopup>
    </menu>
    <menu id="menu-properties" label="Einstellungen" accesskey="E">
      <menupopup id="menu-properties-popup">
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
    <!-- <menu id="menu-prefs" label="&menu-prefs:label;" accesskey="&menu-prefs:accesskey;">
		<menupopup id="menu-prefs-popup">
			<menu id="menu-prefs-stpltable" label="&menu-prefs-stpltable:label;" accesskey="&menu-prefs-stpltable:accesskey;">
				<menupopup id="menu-prefs-stpltable-popup">
	        		<menuitem
	          			 id     	="menu-prefs-stpltable-stundenplan"
	          			 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplan:key"
	         			 label     	="&menu-prefs-stpltable-stundenplan:label;"
	         			 command   	="menu-prefs-stpltable-stundenplan:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplan:accesskey;"/>
	           		<menuitem
						 id        	="menu-prefs-stpltable-stundenplandev"
						 type		="radio"
	          			 key       	="menu-prefs-stpltable-stundenplandev:key"
						 label     	="&menu-prefs-stpltable-stundenplandev:label;"
						 command   	="menu-prefs-stpltable-stundenplandev:command"
	           			 accesskey 	="&menu-prefs-stpltable-stundenplandev:accesskey;"/>
	      		</menupopup>
	      	</menu>
	    </menupopup>
    </menu>
    <menu id="menu-help" label="&menu-help:label;" accesskey="&menu-help:accesskey;">
          <menupopup id="menu-about-popup">
            <menuitem
               id        =  "menu-help-close"
               key       =  "menu-help-close:key"
               label     = "&menu-help-close:label;"
               command   =  "menu-help-close:command"
               accesskey = "&menu-help-close:accesskey;"/>
          </menupopup>
    </menu> -->
  </menubar>
</toolbox>

<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs orient="horizontal">

			<tab id="tab-mitarbeiter1" label="Mitarbeiter" oncommand="tabchange('mitarbeiter');"/>
			<tab id="tab-verband" label="Verband" oncommand="tabchange('lehreinheiten');"/>
			<tab id="tab-ort" label="Ort" />
			<!--<tab id="tab-lektor" label="Lektor" />-->
		</tabs>
		<tabpanels id="tabpanels-left" flex="1" >
			<tree id="tree-menu-mitarbeiter1" />
			<tree id="tree-verband" />

			<!--<tree id="tree-ort" />
			<tree id="tree-lektor" />-->
		</tabpanels>
	</tabbox>
	<splitter collapse="before" persist="state">
		<grippy />
	</splitter>
	<vbox id="vbox-main" flex="15" />
</hbox>

<statusbar id="status-bar" persist="collapsed">
	<statusbarpanel class="statusbarpanel-iconic" id="logo-icon" />
	<statusbarpanel id="statusbarpanel-semester" label="<?php echo $benutzer->variable->semester_aktuell; ?>"/>
	<statusbarpanel id="statusbarpanel-db_table" label="<?php echo substr(CONN_STRING_FAS,strpos(CONN_STRING_FAS,'dbname=')+7,strpos(CONN_STRING_FAS,'user=')-strpos(CONN_STRING_FAS,'dbname=')-7); ?>"/>
	<statusbarpanel id="statusbarpanel-user" label="<?php echo $user; ?>"/>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel class="statusbarpanel-iconic" id="example-status" flex="1" />
	<statusbarpanel id="statusbarpanel-progressmeter" hidden='true'>
		<progressmeter id="identifier" mode="undetermined"/>
	</statusbarpanel>
</statusbar>
</window>