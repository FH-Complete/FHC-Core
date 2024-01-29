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
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/planner.css" type="text/css"?>';
if(isset($_GET['projekt_kurzbz']))
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
else
	$projekt_kurzbz='';
//echo $oe;
if(isset($_GET['projektphase_id']))
	$projektphase_id=$_GET['projektphase_id'];
else
	$projektphase_id='';
?>

<window id="window-projektdokument-zuweisen" title="Dokument zu Projekt zuweisen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="initProjektdokument(<?php echo ($projekt_kurzbz!=''?"'".$projekt_kurzbz."'":"''").','.($projektphase_id!=''?"'".$projektphase_id."'":"''"); ?>)"
        >
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/projektdokument.window.js.php" />
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/functions.js.php"></script>
<vbox>

<checkbox id="checkbox-projekt-neu" hidden="true"/>

<groupbox id="groupbox-projektdokument" flex="1">
	<caption label="Dokument suchen"/>
		<grid id="grid-projektdokument-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="5"/>
			</columns>
			<rows>
				<label value="Dokument" control="projektdokument-menulist-dokument" />
				    <menulist id="projektdokument-menulist-dokument"
							editable="true"
							datasources="rdf:null" flex="1"
							ref="http://www.technikum-wien.at/dms/liste"
							oninput="ProjektdokumentMenulistDokumentLoad(this);"
							oncommand=""
							>
						<template>
						<menupopup>
							<menuitem value="rdf:http://www.technikum-wien.at/dms/rdf#dms_id"
				        		      label="rdf:http://www.technikum-wien.at/dms/rdf#name"
							  		  uri="rdf:*"/>
						</menupopup>
						</template>
					</menulist>
		</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="button-projektdokument-zuweisen" oncommand="saveZuordnung()" label="Zuweisen" />
	</hbox>
</groupbox>
</vbox>
</window>