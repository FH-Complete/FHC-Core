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
require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
?>

<window id="window-ressource-neu" title="Neue Ressource anlegen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/projekt.window.js.php" />
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/functions.js.php"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/ressource.js.php"></script>

<vbox>

<checkbox id="checkbox-ressource-neu" hidden="true"/>
<groupbox id="groupbox-ressource" flex="1">
	<caption label="Details"/>
		<grid id="grid-ressource-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="MitarbeiterIn" control="ressource-menulist-mitarbeiter" />
				    <menulist id="ressource-menulist-mitarbeiter"
										  editable="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/mitarbeiter/liste"
								          oninput="RessourceMenulistMitarbeiterLoad(this);"
								          oncommand=""
								         >
						<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"
				        		      label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname ( rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid )"
							  		  uri="rdf:*"/>
						</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="StudentIn" control="ressource-menulist-student" />
				    <menulist id="ressource-menulist-student"
										  editable="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/student/alle"
								          oninput="RessourceMenulistStudentLoad(this);"
								          oncommand=""
								         >
						<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/student/rdf#uid"
				        		      label="rdf:http://www.technikum-wien.at/student/rdf#vorname rdf:http://www.technikum-wien.at/student/rdf#nachname ( rdf:http://www.technikum-wien.at/student/rdf#uid )"
							  		  uri="rdf:*"/>
						</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Betriebsmittel" control="ressource-menulist-betriebsmittel" />
				    <menulist id="ressource-menulist-betriebsmittel"
										  editable="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/betriebsmittel/liste"
								          oninput="RessourceMenulistBetriebsmittelLoad(this)"
								          oncommand=""
								         >
						<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#betriebsmittel_id"
				        		      label="rdf:http://www.technikum-wien.at/betriebsmittel/rdf#beschreibung ( rdf:http://www.technikum-wien.at/betriebsmittel/rdf#inventarnummer )"
							  		  uri="rdf:*"/>
						</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Firma" control="ressource-menulist-firma" />
				    <menulist id="ressource-menulist-firma"
										  editable="true"
								          datasources="rdf:null" flex="1"
								          ref="http://www.technikum-wien.at/firma/liste"
								          oninput="RessourceMenulistFirmaLoad(this)"
								          oncommand=""
								         >
						<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/firma/rdf#firma_id"
				        		      label="rdf:http://www.technikum-wien.at/firma/rdf#name"
							  		  uri="rdf:*"/>
						</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Bezeichnung" control="textbox-ressource-bezeichnung"/>
					<textbox id="textbox-ressource-bezeichnung" maxlength="256"/>
				</row>
				<row>
					<label value="Beschreibung" control="textbox-ressource-beschreibung"/>
   					<textbox id="textbox-ressource-beschreibung" multiline="true"/>
				</row>
		</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="button-ressource-speichern" oncommand="saveRessource()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>