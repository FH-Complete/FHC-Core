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
require_once('../../include/stundenplan.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['id']) && is_numeric($_GET['id']))
	$id=$_GET['id'];
else
	$id='';

$datum_obj = new datum();
$db = new basis_db();

loadVariables(get_uid());

$stundenplan = new stundenplan($db_stpl_table);

if(!$stundenplan->load($id))
	die('Fehler beim Laden der Daten');

$studiengang = new studiengang();
$studiengang->load($stundenplan->studiengang_kz);

?>

<window id="stpl-details-dialog" title="Details"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="StplDetailsInit('<?php echo $datum_obj->convertISODate($stundenplan->datum); ?>','<?php echo $stundenplan->mitarbeiter_uid; ?>','<?php echo $id; ?>'); document.getElementById('stpl-details-dialog-textbox-titel').focus();"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lvplanung/stpl-details-dialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<commandset>
  <command id="stpl-details-dialog-command-save" oncommand="StplDetailsSpeichern()"/>
</commandset>
<vbox id="stpl-details-dialog-detail">
<textbox id="stpl-details-dialog-textbox-id" hidden="true" value="<?php echo $id; ?>"/>


<groupbox id="stpl-details-dialog-groupbox" flex="1">
	<caption label="Details"/>
		<grid id="stpl-details-dialog-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Titel" />
					<textbox id="stpl-details-dialog-textbox-titel" value="<?php echo htmlspecialchars($stundenplan->titel); ?>"  maxlength="32"/>
      			</row>
      			<row>
					<label value="Anmerkung" />
					<textbox id="stpl-details-dialog-textbox-anmerkung" multiline='true' value="<?php echo htmlspecialchars($stundenplan->anmerkung); ?>"  rows="6"/>
      			</row>
				<row>
					<spacer />
					<spacer />
				</row>
				<row>
					<label value="Studiengang"/>
					<label value="<?php echo $studiengang->kuerzel; ?>" id="stpl-details-dialog-label-studiengang"/>
				</row>
				<row>
					<label value="Semester" />
					<hbox>
						<textbox value="<?php echo htmlspecialchars($stundenplan->semester); ?>" id="stpl-details-dialog-textbox-semester" maxlength="1" size="1"/>
					</hbox>
      			</row>
      			<row>
					<label value="Verband" />
					<hbox>
						<textbox id="stpl-details-dialog-textbox-verband" value="<?php echo $stundenplan->verband; ?>" maxlength="1" size="1"/>
					</hbox>
      			</row>
      			<row>
					<label value="Gruppe" />
					<hbox>
						<textbox id="stpl-details-dialog-textbox-gruppe" value="<?php echo $stundenplan->gruppe; ?>" maxlength="1" size="1"/>
					</hbox>
      			</row>
      			<row>
      				<label value="Spezialgruppe" control="stpl-details-dialog-menulist-gruppe_kurzbz"/>
					<menulist id="stpl-details-dialog-menulist-gruppe_kurzbz"
					          flex="1">
							<menupopup>
							<?php
								echo "<menuitem value='' label='-- keine Auswahl --'/>\n";
								$qry = "SELECT gruppe_kurzbz
										FROM public.tbl_gruppe
										WHERE studiengang_kz='$stundenplan->studiengang_kz' AND semester='$stundenplan->semester'
										UNION
										SELECT gruppe_kurzbz
										FROM lehre.tbl_lehreinheitgruppe
										WHERE lehreinheit_id='$stundenplan->lehreinheit_id'";

								if($db->db_query($qry))
								{
									while($row = $db->db_fetch_object())
									{
										if($row->gruppe_kurzbz!='')
										{
											if($row->gruppe_kurzbz==$stundenplan->gruppe_kurzbz)
												$selected="selected='true'";
											else
												$selected='';

											echo "<menuitem value='$row->gruppe_kurzbz' label='$row->gruppe_kurzbz' $selected/>\n";
										}
									}
								}
							?>
							</menupopup>
					</menulist>
      			</row>
      			<row>
					<label value="UNr" />
					<textbox id="stpl-details-dialog-textbox-unr" value="<?php echo $stundenplan->unr; ?>"/>
      			</row>

      			<row>
					<label value="Fix" />
					<checkbox id="stpl-details-dialog-checkbox-fix" checked="<?php echo ($stundenplan->fix?'true':'false'); ?>"/>
      			</row>
      			<row>
      				<label value="Ort" control="stpl-details-dialog-menulist-ort_kurzbz"/>
					<menulist id="stpl-details-dialog-menulist-ort_kurzbz"
					          flex="1">
							<menupopup>
							<?php
								$qry = "SELECT ort_kurzbz FROM public.tbl_ort WHERE aktiv=true ORDER BY ort_kurzbz";

								if($db->db_query($qry))
								{
									while($row = $db->db_fetch_object())
									{
										if($row->ort_kurzbz!='')
										{
											if($row->ort_kurzbz==$stundenplan->ort_kurzbz)
												$selected="selected='true'";
											else
												$selected='';

											echo "<menuitem value='$row->ort_kurzbz' label='$row->ort_kurzbz' $selected/>\n";
										}
									}
								}
							?>
							</menupopup>
					</menulist>
      			</row>
      				<row>
      				<label value="Stunde" control="stpl-details-dialog-menulist-stunde"/>
					<menulist id="stpl-details-dialog-menulist-stunde"
					          flex="1">
							<menupopup>
							<?php
								$qry = "SELECT stunde FROM lehre.tbl_stunde ORDER BY stunde";

								if($db->db_query($qry))
								{
									while($row = $db->db_fetch_object())
									{
										if($row->stunde!='')
										{
											if($row->stunde==$stundenplan->stunde)
												$selected="selected='true'";
											else
												$selected='';

											echo "<menuitem value='$row->stunde' label='$row->stunde' $selected/>\n";
										}
									}
								}
							?>
							</menupopup>
					</menulist>
      			</row>
      			<row>
      				<label value="LektorIn" control="stpl-details-dialog-menulist-lektor"/>
					<menulist id="stpl-details-dialog-menulist-lektor"
							datasources="<?php echo APP_ROOT.'rdf/mitarbeiter.rdf.php?stg_kz='.$stundenplan->studiengang_kz.'&amp;lektor=true'.
							                    ' '.APP_ROOT.'rdf/mitarbeiter.rdf.php?mitarbeiter_uid='.$stundenplan->mitarbeiter_uid;?>"
							ref="http://www.technikum-wien.at/mitarbeiter/_alle" flex="1">
						<template>
							<menupopup>
								<menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
									value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
							</menupopup>
						</template>
					</menulist>
				</row>
				<row>
					<label value="Datum" control="stpl-details-dialog-box-datum"/>
					<hbox>
						<box class="Datum" id="stpl-details-dialog-box-datum"/>
					</hbox>
				</row>
			</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="stpl-details-dialog-button-speichern" command="stpl-details-dialog-command-save" label="speichern" accesskey="s"/>
	</hbox>
</groupbox>
</vbox>
</window>
