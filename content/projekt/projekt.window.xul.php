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

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	
if(isset($_GET['oe']))
	$oe=$_GET['oe'];
else 
	$oe='';
//echo $oe;
if(isset($_GET['projekt_kurzbz']))
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
else 
	$projekt_kurzbz='';
?>

<window id="window-projekt-neu" title="Neues Projekt anlegen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="initProjekt(<?php echo ($projekt_kurzbz!=''?$projekt_kurzbz:"''"); ?>)"
        >
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/projekt.window.js.php" />
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/functions.js"></script>
<vbox>

<checkbox id="checkbox-projekt-neu" hidden="true"/>

<groupbox id="groupbox-projekt" flex="1">
	<caption label="Details"/>
		<grid id="grid-projekt-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="OE (Organisationseinheit)" control="textbox-projekt-oe"/>
					<textbox id="textbox-projekt-oe" value="<?php echo $oe; ?>" maxlength="32"/>
				</row>
				<row>
					<label value="Kurzbezeichnung" control="textbox-projekt-projekt_kurzbz"/>
					<textbox id="textbox-projekt-projekt_kurzbz" maxlength="16"/>
				</row>
				<row>
					<label value="Titel" control="textbox-projekt-titel"/>
   					<textbox id="textbox-projekt-titel" maxlength="256"/>
				</row>				
				<row>
					<label value="Nummer" control="textbox-projekt-nummer"/>
   					<textbox id="textbox-projekt-nummer" maxlength="8"/>
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projekt-beschreibung"/>
   					<textbox id="textbox-projekt-beschreibung" multiline="true"/>
				</row>
				<row>
					<label value="Beginn" control="textbox-projekt-beginn"/>
   					<textbox id="textbox-projekt-beginn"/>
				</row>
      			<row>
					<label value="Ende" control="textbox-projekt-ende"/>
   					<textbox id="textbox-projekt-ende"/>
      			</row>
			<!-- <row>
					<label value="Test" control="textbox-projekt-test"/>
   					<menulist id="menulist-projekt-test" flex="1">
							<menupopup>
								<menuitem value="p" label="Privatkonto"/>
								<menuitem value="f" label="Firmenkonto"/>
							</menupopup>
					</menulist>
      			</row>
      			<row>
					<label value="Verrechnungskonto" control="bankverbindung-textbox-verrechnung"/>
   					<checkbox id="bankverbindung-checkbox-verrechnung" checked="true"/>
      			</row>-->
		</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="button-projekt-speichern" oncommand="saveProjekt()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>