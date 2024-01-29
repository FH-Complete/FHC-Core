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

include('../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

if(isset($_GET['kontakt_id']) && is_numeric($_GET['kontakt_id']))
	$kontakt_id=$_GET['kontakt_id'];
else
	$kontakt_id='';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id=$_GET['person_id'];
else
	$person_id='';
?>

<window id="kontakt-dialog" title="Kontakt"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="KontaktInit(<?php echo ($kontakt_id!=''?$kontakt_id:"''").','.($person_id!=''?$person_id:"''"); ?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/kontaktdialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>

<textbox id="kontakt-textbox-kontakt_id" hidden="true"/>
<textbox id="kontakt-textbox-person_id" hidden="true"/>
<checkbox id="kontakt-checkbox-neu" hidden="true"/>

<groupbox id="kontakt-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="kontakt-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Typ" control="kontakt-menulist-typ"/>
					<menulist id="kontakt-menulist-typ"
					          datasources="<?php echo APP_ROOT ?>rdf/kontakttyp.rdf.php" flex="1"
					          ref="http://www.technikum-wien.at/kontakttyp/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/kontakttyp/rdf#kontakttyp"
					        		      label="rdf:http://www.technikum-wien.at/kontakttyp/rdf#beschreibung"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Kontakt" control="kontakt-textbox-kontakt"/>
					<textbox id="kontakt-textbox-kontakt" maxlength="128"/>
      			</row>
				<row>
					<label value="Anmerkung" control="kontakt-textbox-anmerkung"/>
   					<textbox id="kontakt-textbox-anmerkung" maxlength="64"/>
				</row>
      			<row>
					<label value="Zustellung" control="kontakt-checkbox-zustellung"/>
   					<checkbox id="kontakt-checkbox-zustellung" checked="true"/>
      			</row>

				<row>
					<label value="Firma / Standort" control="kontakt-menulist-firma"/>
					<!--<menulist id="kontakt-menulist-firma"
					          datasources="<?php echo APP_ROOT ?>rdf/firma.rdf.php?optional=true" flex="1"
					          ref="http://www.technikum-wien.at/firma/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
					        		      label="rdf:http://www.technikum-wien.at/firma/rdf#name"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
					-->
					<box class="Standort" id="kontakt-menulist-firma" />
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="kontakt-button-speichern" oncommand="KontaktSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>