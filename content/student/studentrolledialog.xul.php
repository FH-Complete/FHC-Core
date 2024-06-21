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
require_once('../../config/global.config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/bismeldestichtag.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else
	$prestudent_id='';

if(isset($_GET['status_kurzbz']))
	$status_kurzbz=$_GET['status_kurzbz'];
else
	$status_kurzbz='';

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz=$_GET['studiensemester_kurzbz'];
else
	$studiensemester_kurzbz='';

if(isset($_GET['ausbildungssemester']))
	$ausbildungssemester=$_GET['ausbildungssemester'];
else
	$ausbildungssemester='';

if(isset($_GET['datum']))
	$datum=$_GET['datum'];
else
	$datum='';

$vorname = '';
$nachname = '';

$user=get_uid();
$db = new basis_db();

if($prestudent_id!='')
{
	$prestudent = new prestudent();
	$prestudent->load($prestudent_id);

	$vorname = $prestudent->vorname;
	$nachname = $prestudent->nachname;

	// Prüfen, ob Studnetrolle vor dem aktuellen Meldestichtag ist. In diesem Fall darf die Rolle nicht mehr bearbeitet werden.
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	$bismeldestichtag = new bismeldestichtag();
	$disabled = $bismeldestichtag->checkMeldestichtagErreicht($datum) && !$rechte->isBerechtigt('student/keine_studstatuspruefung', null, 'suid')
		? ' disabled="true"'
		: '';
}
?>

<window id="student-rolle-dialog" title="Status"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="StudentRolleInit(<?php echo "'$prestudent_id','$status_kurzbz','$studiensemester_kurzbz','$ausbildungssemester'";?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/studentrolledialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>
<textbox id="student-rolle-textbox-prestudent_id" value="" hidden="true" />
<groupbox id="student-rolle-groupbox" flex="1">
	<?php if ($disabled): ?>
		<label class="warning">Meldestichtag erreicht - Bearbeiten nicht mehr möglich</label>
	<?php endif; ?>
	<caption label="Details<?php echo ($nachname!=''?" $nachname $vorname":'');?>"/>
		<grid id="student-rolle-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row id="student-rolle-grid-row-textbox" hidden="false">
					<label value="Rolle"/>
					<textbox disabled="true" id="student-rolle-textbox-status_kurzbz" />
				</row>
				<row id="student-rolle-grid-row-menulist" hidden="true">
					<label value="Rolle"/>
					<menulist id="student-rolle-menulist-status_kurzbz" disabled="false" oncommand="StudentRolleChangeStatus()">
						<menupopup>
							<menuitem value="Interessent" label="InteressentIn"/>
							<menuitem value="Bewerber" label="BewerberIn"/>
							<menuitem value="Aufgenommener" label="Aufgenommene/r"/>
							<menuitem value="Student" label="StudentIn"/>
							<menuitem value="Unterbrecher" label="UnterbrecherIn"/>
							<menuitem value="Diplomand" label="DiplomandIn"/>
							<menuitem value="Incoming" label="Incoming"/>
						</menupopup>
					</menulist>
				</row>
				<row>
					<label value="Studiensemester" control="student-rolle-menulist-studiensemester"/>
					<menulist id="student-rolle-menulist-studiensemester"
					          datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php?order=desc" flex="1"
					          ref="http://www.technikum-wien.at/studiensemester/liste"<?php echo $disabled ?> >
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
					<menulist id="student-rolle-menulist-ausbildungssemester"<?php echo $disabled ?> >
						<menupopup>
						<?php

							if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false)
							{
								$maxsem=100;
							}
							else
							{
								$maxsem=10;
								$qry = "SELECT max(semester) as maxsem FROM public.tbl_lehrverband WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_prestudent WHERE prestudent_id=".$db->db_add_param($prestudent_id).")";
								if($result = $db->db_query($qry))
								{
									if($row = $db->db_fetch_object($result))
									{
										$maxsem = $row->maxsem;
									}
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
					$hidden = 'true';
					$qry = "SELECT mischform FROM public.tbl_prestudent JOIN public.tbl_studiengang USING(studiengang_kz) WHERE prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);
					if($result = $db->db_query($qry))
						if($row = $db->db_fetch_object($result))
							if($row->mischform=='t')
								$hidden='false';
				?>
				<row hidden="<?php echo $hidden; ?>">
					<label value="Organisationsform" control="student-rolle-menulist-orgform_kurzbz"/>
					<menulist id="student-rolle-menulist-orgform_kurzbz"<?php echo $disabled ?> >
						<menupopup>
						<menuitem value="" label="-- keine Auswahl --"/>
						<?php
						$qry = "SELECT orgform_kurzbz, bezeichnung FROM bis.tbl_orgform WHERE rolle ORDER BY bezeichnung";
						if($result = $db->db_query($qry))
						{
							while($row = $db->db_fetch_object($result))
							{
								echo '<menuitem value="'.$row->orgform_kurzbz.'" label="'.$row->bezeichnung.'"/>';
							}
						}
						?>
						</menupopup>
					</menulist>
				</row>
				<row>
					<label value="Datum" control="student-rolle-datum-datum"/>
					<box class='Datum' id="student-rolle-datum-datum"<?php echo $disabled ?>/>
				</row>
				<row>
					<label value="Bestätigt am" control="student-rolle-datum-bestaetigt_datum"/>
					<box class='Datum' id="student-rolle-datum-bestaetigt_datum"<?php echo $disabled ?> />
				</row>
				<?php
						$readonly = 'readonly="true"';
						$rechte = new benutzerberechtigung();
						$rechte->getBerechtigungen($user);
						if($rechte->isBerechtigt('basis/prestudentstatus'))
							$readonly = '';
					?>
				<row>
					<label value="Bewerbung abgeschickt am" control="student-rolle-datum-bewerbung_abgeschicktamum"/>
					<textbox id="student-rolle-datum-bewerbung_abgeschicktamum" <?php echo $readonly ?><?php echo $disabled ?>/>
				</row>
				<row>
					<label value="Studienplan" control="student-rolle-menulist-studienplan"/>
					<menulist id="student-rolle-menulist-studienplan"<?php echo $disabled ?> >
						<menupopup>
						<menuitem value="" label="-- keine Auswahl --"/>
						<?php
						$studienplan = new studienplan();
						$studienplan->getStudienplaene($prestudent->studiengang_kz);

						foreach($studienplan->result as $row)
						{
							echo '<menuitem value="'.$row->studienplan_id.'" label="'.$db->convert_html_chars($row->bezeichnung.' ('.$row->studienplan_id.')').'"/>';
						}
						?>
						</menupopup>
					</menulist>
				</row>
				<row>
					<label value="Anmerkung"/>
					<textbox id="student-rolle-textbox-anmerkung" multiline="true"<?php echo $disabled ?> />
				</row>
				<row>
					<label value="Aufnahmestufe"/>
					<menulist id="student-rolle-menulist-stufe" <?php echo empty($disabled) ? 'disabled="false"' : $disabled ?>>
						<menupopup>
							<menuitem value="" label="-- keine Auswahl --"/>
							<menuitem value="1" label="1"/>
							<menuitem value="2" label="2"/>
							<menuitem value="3" label="3"/>
						</menupopup>
					</menulist>
				</row>
				<row>
					<label value="Grund"/>
					<menulist id="student-rolle-menulist-statusgrund"
					          datasources="rdf:null" flex="1"
					          ref="http://www.technikum-wien.at/statusgrund"<?php echo $disabled ?> >
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/statusgrund/rdf#statusgrund_id"
								          label="rdf:http://www.technikum-wien.at/statusgrund/rdf#bezeichnung_mehrsprachig"
								          uri="rdf:*"/>
								</menupopup>
						</template>
					</menulist>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="student-rolle-button-speichern" oncommand="StudentRolleSpeichern()" label="Speichern"<?php echo $disabled ?> />
	</hbox>
</groupbox>
</vbox>
</window>
