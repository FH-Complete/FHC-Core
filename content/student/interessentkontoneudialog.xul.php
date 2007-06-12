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

include('../../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
?>

<window id="interessent-konto-neu-dialog" title="Neu"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="InteressentKontoNeuInit()"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/interessentkontoneudialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>
<groupbox id="interessent-konto-neu-groupbox" flex="1">
	<caption label="Details"/>
		<label id="interessent-konto-neu-label"/>
		<grid id="interessent-konto-neu-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Betrag" control="interessent-konto-neu-textbox-betrag"/>
					<hbox>
      					<textbox id="interessent-konto-neu-textbox-betrag" value="-0.0" maxlength="9" size="9"/>
      					<spacer flex="1" />			
      				</hbox>
				</row>
				<row>
					<label value="Buchungsdatum" control="interessent-konto-neu-textbox-buchungsdatum"/>
					<hbox>
						<box class="Datum" id="interessent-konto-neu-textbox-buchungsdatum" value="<?php echo date('d.m.Y');?>" />
      					<!--<textbox id="interessent-konto-neu-textbox-buchungsdatum" value="<?php echo date('Y-m-d');?>" maxlength="10" size="10"/>-->
      					<spacer flex="1" />			
      				</hbox>
      			</row>
      			<row>
      				<label value="Buchungstext" control="interessent-konto-neu-textbox-buchungstext"/>
		      		<textbox id="interessent-konto-neu-textbox-buchungstext"  maxlength="256"/>
				</row>
				<row>
					<label value="Mahnspanne" control="interessent-konto-neu-textbox-mahnspanne"/>
					<hbox>
						<textbox id="interessent-konto-neu-textbox-mahnspanne" value="30" maxlength="4" size="4"/>
						<spacer flex="1" />			
      				</hbox>
				</row>
				<row>
					<label value="Typ" control="interessent-konto-neu-menulist-buchungstyp"/>
					<menulist id="interessent-konto-neu-menulist-buchungstyp" 
					          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php" flex="1"
					          ref="http://www.technikum-wien.at/buchungstyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="interessent-konto-neu-button-speichern" oncommand="InteressentKontoNeuSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>