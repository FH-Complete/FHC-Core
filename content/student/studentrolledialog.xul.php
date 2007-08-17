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
if(isset($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else 
	$prestudent_id='';
	
if(isset($_GET['rolle_kurzbz']))
	$rolle_kurzbz=$_GET['rolle_kurzbz'];
else 
	$rolle_kurzbz='';
	
if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz=$_GET['studiensemester_kurzbz'];
else 
	$studiensemester_kurzbz='';
	
if(isset($_GET['ausbildungssemester']))
	$ausbildungssemester=$_GET['ausbildungssemester'];
else 
	$ausbildungssemester='';
?>

<window id="student-rolle-dialog" title="Neu"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="StudentRolleInit(<?php echo "'$prestudent_id','$rolle_kurzbz','$studiensemester_kurzbz','$ausbildungssemester'";?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentrolledialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>
<textbox id="student-rolle-textbox-prestudent_id" value="" hidden="true" />
<groupbox id="student-rolle-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="student-rolle-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Rolle"/>
					<textbox disabled="true" id="student-rolle-textbox-rolle_kurzbz" />
				</row>
				<row>
					<label value="Studiensemester" control="student-rolle-menulist-studiensemester"/>
					<menulist id="student-rolle-menulist-studiensemester" 
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
      			<row>
      				<label value="Ausbildungssemester" control="student-rolle-menulist-ausbildungssemester"/>
					<menulist id="student-rolle-menulist-ausbildungssemester" >
						<menupopup>
							<menuitem value="1" label="1"/>
							<menuitem value="2" label="2"/>
							<menuitem value="3" label="3"/>
							<menuitem value="4" label="4"/>
							<menuitem value="5" label="5"/>
							<menuitem value="6" label="6"/>
							<menuitem value="7" label="7"/>
							<menuitem value="8" label="8"/>
							<menuitem value="9" label="9"/>
							<menuitem value="10" label="10"/>							
						</menupopup>
					</menulist>
      			</row>
				<row>
					<label value="Datum" control="student-rolle-datum-datum"/>
					<box class='Datum' id="student-rolle-datum-datum" />
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="student-rolel-button-speichern" oncommand="StudentRolleSpeichern()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>