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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/projektbenutzer.class.php');
$pb= new projektbenutzer();
$pb->load();
$pb->getUIDs();
$datum=$pb->jump_week(time(),0);

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
?>

<overlay id="overlay-ressource"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:svg="http://www.w3.org/2000/svg" 
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
>

	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />
	<!-- <script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/ressource.overlay.js.php" />-->
	<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />

	<!-- ************************ -->
	<!-- *  Projekttask   * -->
	<!-- ************************ -->
	<vbox id="box-ressource" flex="1" uid="" stg_kz="">
		<popupset>
			<popup id="popup-ressource">
				<menuitem label="Entfernen" oncommand="TaskDelete();" id="ressource-tree-popup-entf" disabled="false"/>
			</popup>
		</popupset>
		<tabbox id="ressource-tabbox" flex="3" orient="vertical">
			<tabs orient="horizontal" id="ressource-tabs">
				<tab id="tab-ressource-projekt" label="Projekte" />
				<tab id="tab-ressource-projektphase" label="Projektphasen" />
			</tabs>
			<tabpanels id="tabpanels-ressource-main" flex="1">
				<box orient="vertical" id="box-ressource-projekt">
					<toolbox>
						<toolbar id="toolbar-ressource-projektase-nav-toolbar">
							<toolbarbutton id="toolbarbutton-ressource-zoomin" label="Zoom In" oncommand="PhaseNeu();" disabled="true" image="../skin/images/NeuDokument.png" tooltiptext="Neuen Task anlegen" />
							<toolbarbutton id="toolbarbutton-ressource-zoomout" label="Zoom Out" oncommand="PhaseDelete();" disabled="true" image="../skin/images/DeleteIcon.png" tooltiptext="Task löschen"/>
							<toolbarbutton id="toolbarbutton-ressource-print" label="Drucken" oncommand="PhaseTreeRefresh()" disabled="false" image="../skin/images/refresh.png" tooltiptext="Liste neu laden"/>
						</toolbar>
					</toolbox>
					<grid id="grid-ressource-projekt">
						<columns>
							<column style="background-color:lightblue; border:1px solid black" />
							<?php
								for ($i=0; $i<20; $i++)
									echo '<column />';
							?>
						</columns>
						<rows>
							<row style="background-color:lightgreen; border:1px solid black">
								<label value="Ressource" />
								<?php
								for ($i=0; $i<20; $i++)
									echo '<box><label value="KW ',($pb->kw($pb->jump_week($datum,$i))),' " /></box>';
								?>
							</row>
							<?php
							//echo count($pb->uids);
							foreach ($pb->uids as $uid)
							{
								echo '<row style="background-color:lightgreen; border:1px solid black">
									<label value="',$uid,'" />';
								for ($j=0; $j<20; $j++)
									echo '<box><label value=" ',($pb->getProjektePerUID($uid,$pb->jump_week($datum,$i))),' " /></box>';
								echo '</row>';
							}
							?>
						</rows>
					</grid>
				</box>
				<vbox id="ressource-ressource" />
			</tabpanels>
		</tabbox>
	</vbox>
</overlay>