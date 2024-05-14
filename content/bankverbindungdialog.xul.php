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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

if(isset($_GET['bankverbindung_id']) && is_numeric($_GET['bankverbindung_id']))
	$bankverbindung_id=$_GET['bankverbindung_id'];
else
	$bankverbindung_id='';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id=$_GET['person_id'];
else
	$person_id='';
?>

<window id="bankverbindung-dialog" title="Bankverbindung"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="BankverbindungInit(<?php echo ($bankverbindung_id!=''?$bankverbindung_id:"''").','.($person_id!=''?$person_id:"''"); ?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/bankverbindungdialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>

<textbox id="bankverbindung-textbox-bankverbindung_id" hidden="true"/>
<textbox id="bankverbindung-textbox-person_id" hidden="true"/>
<checkbox id="bankverbindung-checkbox-neu" hidden="true"/>

<groupbox id="bankverbindung-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="bankverbindung-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Name" control="bankverbindung-textbox-name"/>
					<textbox id="bankverbindung-textbox-name" maxlength="64"/>
				</row>
				<row>
					<label value="Anschrift" control="bankverbindung-textbox-anschrift"/>
					<textbox id="bankverbindung-textbox-anschrift" maxlength="128"/>
      			</row>
				<row>
					<label value="IBAN" control="bankverbindung-textbox-iban"/>
   					<textbox id="bankverbindung-textbox-iban" checked="true"/>
      			</row>
				<row>
					<label value="BIC" control="bankverbindung-textbox-bic"/>
   					<textbox id="bankverbindung-textbox-bic" maxlength="64"/>
				</row>
				<row>
					<label value="Kontonummer" control="bankverbindung-textbox-kontonr"/>
   					<textbox id="bankverbindung-textbox-kontonr" checked="true"/>
      			</row>
      			<row>
					<label value="BLZ" control="bankverbindung-textbox-blz"/>
   					<textbox id="bankverbindung-textbox-blz" checked="true"/>
      			</row>
      			<row>
					<label value="Typ" control="bankverbindung-textbox-typ"/>
   					<menulist id="bankverbindung-menulist-typ" flex="1">
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
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="bankverbindung-button-speichern" oncommand="BankverbindungSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>
