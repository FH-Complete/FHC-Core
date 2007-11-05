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

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Connection');
	
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
				<row id="student-rolle-grid-row-textbox" hidden="false">
					<label value="Rolle"/>
					<textbox disabled="true" id="student-rolle-textbox-rolle_kurzbz" />
				</row>
				<row id="student-rolle-grid-row-menulist" hidden="true">
					<label value="Rolle"/>
					<menulist id="student-rolle-menulist-rolle_kurzbz" disabled="false">
						<menupopup>
							<menuitem value="Interessent" label="Interessent"/>
							<menuitem value="Bewerber" label="Bewerber"/>
							<menuitem value="Student" label="Student"/>
						</menupopup>
					</menulist>
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
						<?php
							$maxsem=10;
							$qry = "SELECT max(semester) as maxsem FROM public.tbl_lehrverband WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_prestudent WHERE prestudent_id='".addslashes($prestudent_id)."')";
							if($result = pg_query($conn, $qry))
							{
								if($row = pg_fetch_object($result))
								{
									$maxsem = $row->maxsem;
								}
							}
														
							for($i=0;$i<=$maxsem;$i++)
							{
								echo '<menuitem value="'.$i.'" label="'.$i.'"/>';
							}
						?>
						</menupopup>
					</menulist>
      			</row>
      			<?php
      				$hidden='true';
      				$qry = "SELECT orgform_kurzbz FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE prestudent_id='$prestudent_id'";
      				if($result = pg_query($conn, $qry))
      					if($row = pg_fetch_object($result))
      						if($row->orgform_kurzbz=='VBB')
      							$hidden='false';
      			?>
      			<row hidden="<?php echo $hidden; ?>">		
      				<label value="Organisationsform" control="student-rolle-menulist-orgform_kurzbz"/>
					<menulist id="student-rolle-menulist-orgform_kurzbz" >
						<menupopup>
						<menuitem value="" label="-- keine Auswahl --"/>
						<menuitem value="VZ" label="Vollzeit"/>
						<menuitem value="BB" label="Berufsbegleitend"/>
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