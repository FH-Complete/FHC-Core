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

include('../../config/vilesci.config.inc.php');
include('../../config/global.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';
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
					          datasources="<?php echo APP_ROOT ?>rdf/buchungstyp.rdf.php?aktiv=true" flex="1"
					          ref="http://www.technikum-wien.at/buchungstyp/liste"
					          oncommand="StudentKontoNeuDefaultBetrag()" >
						<template>
							<rule>
								<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/buchungstyp/rdf#buchungstyp_kurzbz"
					        		      label="rdf:http://www.technikum-wien.at/buchungstyp/rdf#beschreibung"
					        		      standardbetrag="rdf:http://www.technikum-wien.at/buchungstyp/rdf#standardbetrag"
					        		      standardtext="rdf:http://www.technikum-wien.at/buchungstyp/rdf#standardtext"
					        		      <?php
											// Credit Points werden nur angezeigt, wenn diese im Config aktiviert wurden
												if(defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS=='true')
													echo 'credit_points="rdf:http://www.technikum-wien.at/buchungstyp/rdf#credit_points"';
											?>
										  anmerkung="rdf:http://www.technikum-wien.at/buchungstyp/rdf#anmerkung"
								  		  uri="rdf:*"/>
								</menupopup>
							</rule>
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
				<?php
					// Mahnspanne wird nur angezeigt, wenn diese im Config aktiviert wurden
					if(!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE===true)
						$hidden='';
					else
						$hidden='hidden="true"';
				?>
				<row <?php echo $hidden;?>>
					<label value="Mahnspanne" control="student-konto-neu-textbox-mahnspanne"/>
					<hbox>
						<textbox id="student-konto-neu-textbox-mahnspanne" value="30" maxlength="4" size="4"/>
						<spacer flex="1" />
      				</hbox>
				</row>
				<row>
					<label value="Studiensemester" control="student-konto-neu-menulist-studiensemester"/>
					<menulist id="student-konto-neu-menulist-studiensemester"
					          datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php?order=desc" flex="1"
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
				<?php
					// Credit Points werden nur angezeigt, wenn diese im Config aktiviert wurden
					if(defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS=='true')
						$hidden='';
					else
						$hidden='hidden="true"';

					echo '	<row '.$hidden.'>
								<label value="Credit Points" control="student-konto-neu-textbox-credit_points" '.$hidden.'/>
								<hbox '.$hidden.'>
									<textbox id="student-konto-neu-textbox-credit_points" maxlength="9" size="9" value="0.00" '.$hidden.'/>
									<spacer flex="1" />
								</hbox>
							</row>';
				?>
				<row>
					<label value="Anmerkung" control="student-konto-neu-textbox-anmerkung"/>
						<textbox id="student-konto-neu-textbox-anmerkung" multiline="true"/>
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
