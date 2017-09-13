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

if(!$rechte->isBerechtigt('basis/planner'))
	die('Sie haben keine Berechtigung fuer diese Seite');

header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';*/
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/planner.css" type="text/css"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/planner.overlay.xul.php"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css" ?>';
echo '<?xml-stylesheet href="datepicker/datepicker.css" type="text/css"?>';
?>

<!DOCTYPE window [
	<?php require("../locale/de-AT/planner.dtd"); ?>
]>

<window
	id="planner"
	title="&window.title; - &window.version;"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	orient="vertical"
	width="800"
  	height="600"
  	persist="screenX screenY width height sizemode"
  	onload="onLoad()"
  	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/planner.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>

<commandset id="maincommands">
  <command id="menu-file-close:command" oncommand="closeWindow();"/>
  <command id="menu-properties-studiensemester:command" oncommand="studiensemesterChange();"/>
</commandset>


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
  </menubar>
</toolbox>
<hbox flex="1">
	<tabbox id="tabbox-left" orient="vertical" flex="1">
		<tabs orient="horizontal">
			<tab id="tab-projekt" label="Projektmenue" />
			<tab id="tab-ressource" label="Ressourcemenue" />
		</tabs>
		<tabpanels id="tabpanels-left" flex="1">
			<vbox id="box-projektmenue" />
			<vbox id="box-ressourcemenue" />
		</tabpanels>
	</tabbox>
	<splitter collapse="before" persist="state">
		<grippy />
	</splitter>
	<vbox id="vbox-main" flex="15" />
</hbox>

<statusbar id="status-bar" persist="collapsed">
	<statusbarpanel class="statusbarpanel-iconic" id="logo-icon" />
	
	<?php
		if($rechte->isBerechtigt('system/developer'))
			echo '<statusbarpanel label="'.DB_NAME.'"/>';
	?>
	<statusbarpanel id="statusbarpanel-text" label="<?php echo htmlspecialchars($error_msg); ?>" flex="4" crop="right" />
	<statusbarpanel id="progress-panel" class="statusbarpanel-progress">
		<progressmeter id="statusbar-progressmeter" class="progressmeter-statusbar" mode="determined" value="0%"/>
	</statusbarpanel>
	<statusbarpanel class="statusbarpanel-iconic" id="example-status" />
</statusbar>
</window>
