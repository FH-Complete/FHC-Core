<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/akte.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else
	$prestudent_id='';

if(isset($_GET['akte_id']))
	$akte_id=$_GET['akte_id'];
else
	$akte_id='';

$vorname = '';
$nachname = '';
if($prestudent_id!='')
{
	$prestudent = new prestudent();
	$prestudent->load($prestudent_id);

	$vorname = $prestudent->vorname;
	$nachname = $prestudent->nachname;
}
$db = new basis_db();
?>

<window id="intessent-dokumente-dialog" title="Dokumente"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="InteressentDokumenteDialogInit(<?php echo "'$prestudent_id','$akte_id'";?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/student/interessentdokumentedialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

<vbox>
<textbox id="interessent-dokumente-dialog-textbox-prestudent_id" value="" hidden="true" />
<groupbox id="interessent-dokumente-dialog-groupbox" flex="1">
	<caption label="Dokumentendetails<?php echo ($nachname!=''?" $nachname $vorname":'');?>"/>
		<grid id="interessent-dokumente-dialog-grid-detail" style="margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<row>
					<label value="Dokumenttyp" />
					<menulist id="interessent-dokumente-dialog-menulist-dokument_kurzbz"
						      datasources="<?php echo APP_ROOT; ?>rdf/dokumenttyp.rdf.php?ohne_dok=Zeugnis" flex="1"
						      ref="http://www.technikum-wien.at/dokumenttyp"
					>
						<template>
							<menupopup>
								<menuitem value="rdf:http://www.technikum-wien.at/dokumenttyp/rdf#dokument_kurzbz"
						    		      label="rdf:http://www.technikum-wien.at/dokumenttyp/rdf#bezeichnung"
								  		  uri="rdf:*"/>
							</menupopup>
						</template>
					</menulist>
				</row>
				<row id="interessent-dokumente-dialog-row-titel" hidden="false">
					<label value="Titel"/>
					<textbox id="interessent-dokumente-dialog-textbox-titel" />
				</row>
				<row id="interessent-dokumente-dialog-row-anmerkung" hidden="false">
					<label value="Anmerkung"/>
					<textbox multiline="true" rows="10" id="interessent-dokumente-dialog-textbox-anmerkung" />
				</row>
				<row id="interessent-dokumente-dialog-row-anmerkung" hidden="false">
					<label value="Nachreichung am" control="student-detail-textbox-nachgereicht_am"/>
					<hbox>
						<box class="Datum" id="interessent-dokumente-dialog-textbox-nachgereicht_am"/>
					</hbox>
				</row>
				<row id="interessent-dokumente-dialog-row-save" hidden="false">
					<spacer />
					<hbox>
						<spacer flex="1" />
						<button id="interessent-dokumente-dialog-button-speichern" oncommand="InteressentDokumenteDialogSpeichern()" label="Speichern" />
					</hbox>
				</row>
				<row hidden="false">
					<spacer />
					<label id="interessent-dokumente-dialog-label-nachgereicht" value=""/>
				</row>
				<row id="interessent-dokumente-dialog-row-anmerkung" hidden="false">
					<label value="Anmerkung der Person"/>
					<textbox multiline="true" rows="10" id="interessent-dokumente-dialog-label-anmerkung" readonly="true" />
				</row>
			</rows>
	</grid>
</groupbox>
</vbox>
</window>
