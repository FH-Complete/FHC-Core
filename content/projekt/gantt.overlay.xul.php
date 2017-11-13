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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
/*echo '<?xml-stylesheet href="gantt.css" type="text/css"?>';*/
?>
<!DOCTYPE overlay >
<overlay id="gant"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/projekt/gantt.overlay.js.php" />


	<vbox id="box-ganttx" flex="1">
		<toolbox>
			<toolbar id="toolbar-bestellung-main">
				<toolbarbutton id="toolbarbutton-notiz-filter" label="Ansicht " type="menu">
					<menupopup>
						<menuitem id="toolbarbutton-menuitem-gantt-studienjahr" label="Studienjahr" type="radio" name="sort" oncommand="drawGantt()" tooltiptext="Anzeige Studienjahr" checked="true"/>
						<menuitem id="toolbarbutton-menuitem-gantt-kalenderjahr" label="Kalenderjahr" type="radio" name="sort" oncommand="drawGantt()" tooltiptext="Anzeige Kalenderjahr"/>
				      </menupopup>
				</toolbarbutton>
				<toolbarbutton id="toolbarbutton-gantt-zurueck" label="" oncommand="showYearMinus();" disabled="false" image="../skin/images/left.png" tooltiptext="Jahr zurück"/>
				<toolbarbutton id="toolbarbutton-gantt-jahr" label="Jahr" oncommand="showYear();" disabled="false" tooltiptext="aktuelles Jahr"/>
				<toolbarbutton id="toolbarbutton-gantt-vor" label="" oncommand="showYearPlus();" disabled="false" image="../skin/images/right.png" tooltiptext="Jahr vor"/>
				<toolbarbutton id="toolbarbutton-gantt-drucken" label="Drucken" oncommand="printGantt();" image="../skin/images/drucken.png" tooltiptext="Drucken"/>
                <label value="Beginn:" control="toolbarbutton-gantt-label-beginn"/>
                <box class="Datum" id="toolbarbutton-gantt-beginn"  disabled="false"/>
                <label value="Ende:" control="toolbarbutton-gantt-label-ende"/>
                <box class="Datum" id="toolbarbutton-gantt-ende"  disabled="false"/>
                <toolbarbutton id="toolbarbutton-gantt-anzeigen" label="anzeigen" oncommand="showZeitraum(document.getElementById('toolbarbutton-gantt-beginn').value, document.getElementById('toolbarbutton-gantt-ende').value)" disabled="false" tooltiptext="Zeite Zeitraum"/>
            </toolbar>
		</toolbox>
        <iframe id="iframe-gant-projekt" flex="5" src="" />
	</vbox>
</overlay>
