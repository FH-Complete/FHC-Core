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

if(isset($_GET['adresse_id']) && is_numeric($_GET['adresse_id']))
	$adresse_id=$_GET['adresse_id'];
else
	$adresse_id='';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id=$_GET['person_id'];
else
	$person_id='';
?>

<window id="adresse-dialog" title="Adresse"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="AdresseInit(<?php echo ($adresse_id!=''?$adresse_id:"''").','.($person_id!=''?$person_id:"''"); ?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/adressedialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>



<textbox id="adresse-textbox-adresse_id" hidden="true"/>
<textbox id="adresse-textbox-person_id" hidden="true"/>
<checkbox id="adresse-checkbox-neu" hidden="true"/>

<groupbox id="adresse-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="adresse-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Typ" control="adresse-menulist-typ"/>
					<menulist id="adresse-menulist-typ"
					          flex="1">
							<menupopup>
								<menuitem value="h" label="Hauptwohnsitz"/>
								<menuitem value="n" label="Nebenwohnsitz"/>
								<menuitem value="f" label="Firma"/>
								<menuitem value="r" label="Rechnungsadresse"/>
							</menupopup>
					</menulist>
				</row>
				<row>
					<label value="Strasse" control="adresse-textbox-strasse"/>
					<hbox>
						<textbox id="adresse-textbox-strasse" maxlength="256" size="30"/>
      					<spacer flex="1" />
      				</hbox>
      			</row>
      			<row>
					<label value="Nation" control="adresse-menulist-nation"/>
					<menulist id="adresse-menulist-nation"
					          datasources="<?php echo APP_ROOT ?>rdf/nation.rdf.php" flex="1"
					          ref="http://www.technikum-wien.at/nation/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/nation/rdf#nation_code"
					        		      label="rdf:http://www.technikum-wien.at/nation/rdf#kurztext"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
      			<row>
      				<label value="Plz" control="adresse-textbox-plz"/>
      				<hbox>
		      			<textbox id="adresse-textbox-plz" maxlength="16" size="5" oninput="AdresseLoadGemeinde(false)"/>
		      			<spacer flex="1" />
		      		</hbox>
				</row>
				<row>
					<label value="Gemeinde" />
					<!--<hbox>
						<textbox id="adresse-textbox-gemeinde" maxlength="256" size="30" />
						<spacer flex="1" />
      				</hbox>-->
      				<menulist id="adresse-textbox-gemeinde"
						  editable="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/gemeinde/liste"
				          oncommand="AdresseLoadOrtschaft(false)"
					>
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/gemeinde/rdf#name"
					        		      label="rdf:http://www.technikum-wien.at/gemeinde/rdf#name"
								  		  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Ortschaft" control="adresse-textbox-ort"/>
					<!--<hbox>
						<textbox id="adresse-textbox-ort" maxlength="256" size="30"/>
						<spacer flex="1" />
      				</hbox>-->
      				<menulist id="adresse-textbox-ort"
						  editable="true"
				          datasources="rdf:null" flex="1"
				          ref="http://www.technikum-wien.at/gemeinde/liste"
					>
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/gemeinde/rdf#ortschaftsname"
					        		      label="rdf:http://www.technikum-wien.at/gemeinde/rdf#ortschaftsname"
								  		  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Heimatadresse" control="adresse-checkbox-heimatadresse"/>
   					<checkbox id="adresse-checkbox-heimatadresse" checked="true"/>
      			</row>
      			<row>
   					<label value="Zustelladresse" control="adresse-checkbox-zustelladresse"/>
  					<checkbox id="adresse-checkbox-zustelladresse" checked="true"/>
				</row>
                <row>
                    <label value="Abweichender Empfänger (c/o)" control="adresse-textbox-co_name"/>
                    <hbox>
                        <textbox id="adresse-textbox-co_name" maxlength="256" size="30"/>
                        <spacer flex="1" />
                    </hbox>
                </row>
				<row>
   					<label value="Rechnungsadresse" control="adresse-checkbox-rechnungsadresse"/>
  					<checkbox id="adresse-checkbox-rechnungsadresse" checked="true"/>
				</row>
				<row>
					<label value="Firma" control="adresse-menulist-firma"/>
					<!--
					<menulist id="adresse-menulist-firma"
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
					<box class="Firma" id="adresse-menulist-firma" />
				</row>
				<row>
					<label value="Name" control="adresse-textbox-name"/>
					<hbox>
      					<textbox id="adresse-textbox-name" maxlength="256" size="30"/>
      					<spacer flex="1" />
      				</hbox>
				</row>
				<row>
					<label value="Anmerkung" control="adresse-textbox-anmerkung"/>
      				<textbox id="adresse-textbox-anmerkung" multiline="true"/>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="adresse-button-speichern" oncommand="AdresseSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>