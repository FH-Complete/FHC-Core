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

include('../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['id']) && is_numeric($_GET['id']))
	$id=$_GET['id'];
else 
	$id='';
	
?>

<window id="window-notiz" title="Notiz"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="NotizInit('<?php echo ($id!=''?$id:'');?>')"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/notiz.window.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
<script type="text/javascript">
var projekt_kurzbz = '<?php echo (isset($_GET['projekt_kurzbz'])?addslashes($_GET['projekt_kurzbz']):'');?>';
var projektphase_id = '<?php echo (isset($_GET['projektphase_id'])?addslashes($_GET['projektphase_id']):'');?>';
var projekttask_id = '<?php echo (isset($_GET['projekttask_id'])?addslashes($_GET['projekttask_id']):'');?>';
var uid = '<?php echo (isset($_GET['uid'])?addslashes($_GET['uid']):'');?>';
var person_id = '<?php echo (isset($_GET['person_id'])?addslashes($_GET['person_id']):'');?>';
var prestudent_id = '<?php echo (isset($_GET['prestudent_id'])?addslashes($_GET['prestudent_id']):'');?>';
var bestellung_id = '<?php echo (isset($_GET['bestellung_id'])?addslashes($_GET['bestellung_id']):'');?>';

var opener_id = '<?php echo (isset($_GET['opener_id'])?addslashes($_GET['opener_id']):'');?>';
</script>
<vbox>

<textbox id="notiz-textbox-notiz_id" hidden="true"/>

<groupbox id="notiz-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="notiz-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
      			<row>
      				<label value="Titel" control="notiz-textbox-titel"/>
		      		<textbox id="notiz-textbox-titel" maxlength="256"/>
				</row>
				<row>
      				<label value="Text" control="notiz-textbox-text"/>
		      		<textbox id="notiz-textbox-text" multiline="true"/>
				</row>
				<row>
      				<label value="Start" control="notiz-box-start"/>
		      		<box class="Datum" id="notiz-box-start"/>
				</row>
				<row>
      				<label value="Ende" control="notiz-box-ende"/>
		      		<box class="Datum" id="notiz-box-ende"/>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="notiz-button-speichern" oncommand="NotizSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>