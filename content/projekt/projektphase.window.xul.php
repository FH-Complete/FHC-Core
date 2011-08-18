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
	
// projektphase_id wenn das Formular zum bearbeiten verwendet wird
if(isset($_GET['projektphase_id']))
	$projektphase_id=$_GET['projektphase_id'];
else 
	$projektphase_id='';
//echo $oe;
// projekt_kurzbz muss immer gesetzt sein
if(isset($_GET['projekt_kurzbz']))
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
else 
	$errormsg='projekt_kurzbz ist nicht gesetzt';
// projektphase_fk wenn eine neue Phase unter einer bestehenden angelegt wird
if(isset($_GET['projektphase_fk']))
	$projektphase_fk=$_GET['projektphase_fk'];
else 
	$projektphase_fk='';
?>

<window id="window-projektphase-neu" title="Neue Projektphase anlegen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/projektphase.window.js.php" />
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<vbox>

<checkbox id="checkbox-projektphase-neu" hidden="true"/>

<groupbox id="groupbox-projektphase" flex="1">
	<caption label="Details"/>
		<grid id="grid-projektphase-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="ID" control="textbox-projektphase-projektphase_id "/>
					<textbox id="textbox-projektphase-projektphase_id" value="<?php echo $projektphase_id; ?>" maxlength="64"/>
				</row>
				<row>
					<label value="Projekt (Kurzbz)" control="textbox-projektphase-projekt_kurzbz"/>
					<textbox id="textbox-projektphase-projekt_kurzbz" value="<?php echo $projekt_kurzbz; ?>" maxlength="16"/>
				</row>
				<row>
					<label value="Projektphase (FK)" control="textbox-projektphase-projektphase_fk"/>
   					<textbox id="textbox-projektphase-projektphase_fk" value="<?php echo $projektphase_fk; ?>" maxlength="64"/>
				</row>				
				<row>
					<label value="Bezeichnung" control="textbox-projektphase-bezeichnung"/>
   					<textbox id="textbox-projektphase-bezeichnung" maxlength="32" />
				</row>
				<row>
					<label value="Beschreibung" control="textbox-projektphase-beschreibung"/>
   					<textbox id="textbox-projektphase-beschreibung"/>
				</row>
				<row>
					<label value="Start" control="textbox-projektphase-start"/>
   					<textbox id="textbox-projektphase-start"/>
				</row>
				<row>
					<label value="Ende" control="textbox-projektphase-ende"/>
   					<textbox id="textbox-projektphase-ende"/>
				</row>
				<row>
					<label value="Budget" control="textbox-projektphase-budget"/>
   					<textbox id="textbox-projektphase-budget"/>
				</row>
				<row>
					<label value="Personentage" control="textbox-projektphase-personentage"/>
   					<textbox id="textbox-projektphase-personentage"/>
				</row>
				<!--
				<row>
					<label value="Test" control="textbox-projektphase-test"/>
   					<menulist id="menulist-projektphase-test" flex="1">
							<menupopup>
								<menuitem value="p" label="Privatkonto"/>
								<menuitem value="f" label="Firmenkonto"/>
							</menupopup>
					</menulist>
      			</row>
      			<row>
					<label value="Verrechnungskonto" control="bankverbindung-textbox-verrechnung"/>
   					<checkbox id="bankverbindung-checkbox-verrechnung" checked="true"/>
      			</row>
      			-->
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="button-projektphase-speichern" oncommand="saveProjektphase()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>