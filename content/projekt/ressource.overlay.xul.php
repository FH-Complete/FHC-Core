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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');
/*require_once('../../include/projektbenutzer.class.php');
$pb= new projektbenutzer();
$pb->load();
$pb->getUIDs();*/
//$datum=$pb->jump_week(time(),0);
//$showWeeks=15;

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>

<overlay id="overlay-ressource"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:svg="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/ressource.overlay.js.php" />
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Projekttask   * -->
	<!-- ************************ -->
	<vbox id="box-ressource" flex="1" uid="" stg_kz="">
		<tabbox id="ressource-tabbox" flex="3" orient="vertical">
			<tabs orient="horizontal" id="ressource-tabs">
				<tab id="tab-ressource-projekt" label="Projekte" />
				<tab id="tab-ressource-projektphase" label="Projektphasen" />
				<tab id="tab-ressource-projekttask" label="Projekttasks" />
			</tabs>
			<tabpanels id="tabpanels-ressource-main" flex="1">
				<vbox>
					<toolbox>
						<toolbar>
							<toolbarbutton id="toolbarbutton-ressource-projekt-aktualisieren" label="Aktualisieren" oncommand="document.getElementById('iframe-ressource-projekt').setAttribute('src','<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=projekt&amp;'+gettimestamp());" image="../skin/images/refresh.png" tooltiptext="Neu laden"/>
							<toolbarbutton id="toolbarbutton-ressource-projekt-drucken" label="Drucken" oncommand="foo = window.open('<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=projekt');foo.print();" image="../skin/images/drucken.png" tooltiptext="Drucken"/>
						</toolbar>
					</toolbox>
					<iframe id="iframe-ressource-projekt" flex="5" src="<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?empty" />
				</vbox>
				<vbox>
					<toolbox>
						<toolbar>
							<toolbarbutton id="toolbarbutton-ressource-projektphase-aktualisieren" label="Aktualisieren" oncommand="reloadRessourcePhasen();" image="../skin/images/refresh.png" tooltiptext="Neu laden"/>
							<toolbarbutton id="toolbarbutton-ressource-projektphase-drucken" label="Drucken" oncommand="RessourcePrintPhasen()" image="../skin/images/drucken.png" tooltiptext="Drucken"/>
						</toolbar>
					</toolbox>

					<iframe id="iframe-ressource-projektphase" flex="5" src="about:blank" />
				</vbox>
				<vbox>
					<toolbox>
						<toolbar>
							<toolbarbutton id="toolbarbutton-ressource-projekttask-aktualisieren" label="Aktualisieren" oncommand="reloadRessourceTasks();" image="../skin/images/refresh.png" tooltiptext="Neu laden"/>
							<toolbarbutton id="toolbarbutton-ressource-projektask-drucken" label="Drucken" oncommand="RessourcePrintTask()" image="../skin/images/drucken.png" tooltiptext="Drucken"/>
						</toolbar>
					</toolbox>

					<iframe id="iframe-ressource-projekttask" flex="5" src="about:blank" />
				</vbox>
			</tabpanels>
		</tabbox>
	</vbox>
</overlay>
