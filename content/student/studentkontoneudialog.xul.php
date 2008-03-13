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
echo '<?xml-stylesheet href="../datepicker/datepicker.css" type="text/css"?>';
?>

<window id="student-konto-neu-dialog" title="Neu"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="StudentKontoNeuInit()"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentkontoneudialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>
<groupbox id="student-konto-neu-groupbox" flex="1">
	<caption label="Details"/>
		<label id="student-konto-neu-label"/>
		<grid id="student-konto-neu-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Typ" control="student-konto-neu-menulist-buchungstyp"/>
					<menulist id="student-konto-neu-menulist-buchungstyp" 
					          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php?1" flex="1"
					          ref="http://www.technikum-wien.at/buchungstyp/liste"
					          oncommand="StudentKontoNeuDefaultBetrag()" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
					        		      standardbetrag="rdf:http://www.technikum-wien.at/buchungstyp/rdf#standardbetrag"
					        		      standardtext="rdf:http://www.technikum-wien.at/buchungstyp/rdf#standardtext"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Betrag" control="student-konto-neu-textbox-betrag"/>
					<hbox>
      					<textbox id="student-konto-neu-textbox-betrag" value="-0.00" maxlength="9" size="9"/>
      					<spacer flex="1" />			
      				</hbox>
				</row>
				<row>
					<label value="Buchungsdatum" control="student-konto-neu-textbox-buchungsdatum"/>
					<hbox>      					
      					<box class='Datum' id="student-konto-neu-textbox-buchungsdatum"/>
      					<spacer flex="1" />
      				</hbox>
      			</row>
      			<row>
      				<label value="Buchungstext" control="student-konto-neu-textbox-buchungstext"/>
		      		<textbox id="student-konto-neu-textbox-buchungstext"  maxlength="256"/>
				</row>
				<row>
					<label value="Mahnspanne" control="student-konto-neu-textbox-mahnspanne"/>
					<hbox>
						<textbox id="student-konto-neu-textbox-mahnspanne" value="30" maxlength="4" size="4"/>
						<spacer flex="1" />			
      				</hbox>
				</row>
				<row>
					<label value="Studiensemester" control="student-konto-neu-menulist-studiensemester"/>
					<menulist id="student-konto-neu-menulist-studiensemester" 
					          datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php" flex="1"
					          ref="http://www.technikum-wien.at/studiensemester/liste" >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
								  		  uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="student-konto-neu-button-speichern" oncommand="StudentKontoNeuSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>