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
 * 			Gerald Raab <erald.raab@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

$projekt_ressource_id = $_GET["id"];

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/ressource.class.php');

$ressource = new ressource();
$ressource->getSingleProjektRessource($projekt_ressource_id);
$aufwand = $ressource->aufwand;
$funktion_kurzbz = $ressource->funktion_kurzbz;
$ressource_id = $ressource->ressource_id;
$projektphase_id = $ressource->projektphase_id;
$beschreibung = $ressource->beschreibung;
$projekt_kurzbz = $ressource->projekt_kurzbz;

if ($funktion_kurzbz == "Leitung")
{
	$leitung_sel = ' selected="true"';
	$mitarbeiter_sel = '';
}
else
{
	$leitung_sel = '';
	$mitarbeiter_sel = ' selected="true"';
}


header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
?>

<window id="window-ressource-neu" title="Projektressource verwalten"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/functions.js.php"></script>
	<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>content/projekt/projekt_ressource.window.js.php"></script>
	<script type="text/javascript">
	var projekt_ressource_id = '<?php echo $projekt_ressource_id ?>';
	var aufwand = '<?php echo $aufwand; ?>';
	var funktion_kurzbz = '<?php echo $funktion_kurzbz; ?>';
	var projekt_kurzbz = '<?php echo $projekt_kurzbz; ?>';
	var projektphase_id = '<?php echo $projektphase_id; ?>';
	var ressource_id = '<?php echo $ressource_id; ?>';
	var funktion_kurzbz = '<?php echo $funktion_kurzbz; ?>';
	var beschreibung = '<?php echo $beschreibung; ?>';
	</script>

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
					<label value="ProjektRessourceID" control="textbox-ressource-projekt_ressource_id"/>
					<textbox id="textbox-ressource-projekt_ressource_id" value="<?php echo $projekt_ressource_id; ?>" disabled="true" />
				</row>
				<row>
					<label value="Funktion" control="textbox-ressource-funktionradio"/>
						<radiogroup>
							<radio id="leitung" label="Leitung" <?php echo $leitung_sel; ?>/>
	  						<radio id="mitarbeiter" label="MitarbeiterIn" <?php echo $mitarbeiter_sel; ?>/>
						</radiogroup>
				</row>
				<row>
					<label value="Aufwand" control="textbox-ressource-aufwand"/>
					<textbox id="textbox-ressource-aufwand" value="<?php echo $aufwand; ?>" maxlength="256"/>
				</row>

		</rows>
	</grid>
	<hbox>
		<spacer flex="1" />
		<button id="button-ressource-speichern" oncommand="updateProjektRessource()" label="Speichern" />
	</hbox>
</groupbox>
</vbox>
</window>
