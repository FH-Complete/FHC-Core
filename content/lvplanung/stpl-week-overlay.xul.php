<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
header("Content-type: application/vnd.mozilla.xul+xml");
include('../../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-details-overlay.xul.php"?>';
?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../../locale/de-AT/tempus.dtd"); ?>] -->

<overlay id="TempusOverlaySTPLWeek"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lvplanung/stpl-week-overlay.js.php"/>

<hbox id="hboxTimeTableWeek">
<vbox id="vboxTimeTableWeek" flex="30">
<toolbox>
	<toolbar id="toolbarTimeTableJumpWeek" tbautostretch="always" persist="collapsed">
		<toolbarbutton id="toolbarbuttonJumpWeekMoreLeft"
			tooltiptext="16 Wochen zurueck"
			image="../skin/images/moremoreleft.png"
			oncommand="onJumpDate(-16);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekMoreLeft"
			tooltiptext="4 Wochen zurueck"
			image="../skin/images/moreleft.png"
			oncommand="onJumpDate(-4);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekLeft"
			tooltiptext="1 Woche zurueck"
			image="../skin/images/left.png"
			oncommand="onJumpDate(-1);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekNow"
			tooltiptext="zur aktuellen KW"
			label="KW"
			oncommand="onJumpNow();"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekRight"
			tooltiptext="1 Woche vor"
			image="../skin/images/right.png"
			oncommand="onJumpDate(+1);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekMoreRight"
			tooltiptext="4 Wochen vor"
			image="../skin/images/moreright.png"
			oncommand="onJumpDate(+4);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekMoreRight"
			tooltiptext="16 Wochen vor"
			image="../skin/images/moremoreright.png"
			oncommand="onJumpDate(+16);"
		/>
		<separator orient="horizontal" class="thin" />
		<toolbarbutton id="toolbarbuttonStplWeekRefresh"
			tooltiptext="Neu laden"
			image="../skin/images/refresh.png"
			oncommand="onJumpDate(0);"
		/>
	</toolbar>
</toolbox>
<iframe id="iframeTimeTableWeek" name="TimeTableWeek" flex="5"
	src="<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php" />
<splitter collapse="after" persist="state">
	<grippy />
</splitter>

<vbox id="vboxSTPLDetailsListe" flex="2" />

</vbox>

<splitter collapse="after" persist="state">
	<grippy />
</splitter>

<vbox style="margin:0px;" flex="1">
<toolbox>
		<toolbar id="toolbarTimeTableLeftWeek" tbautostretch="always" persist="collapsed">
		<toolbarbutton id="toolbarbuttonStplWeekRefresh"
			image="../skin/images/refresh.png"
			oncommand="onLVARefresh();"
			tooltiptext="Neu laden"
		/>
		</toolbar>
</toolbox>

<vbox id="vboxLehrveranstalungPlanung" style="overflow:auto;margin:0px;" flex="1"
	datasources="../rdf/lehreinheit-lvplan.rdf.php"
	ref="http://www.technikum-wien.at/lehreinheit-lvplan/alle">
	<template>
	    <rule>
	    	<grid uri="rdf:*" class="lvaStundenplan">
	    		<columns>
	    			<column style="background-color:lightblue; border:1px solid black" />
	    			<column style="background-color:lightblue; border:1px solid black" />
	    		</columns>
	    		<rows>
	    			<row style="background-color:lightgreen; border:1px solid black">
	    				<hbox>
	    					<toolbarbutton
	        					image="../skin/images/lvaSingle.png"
	        					ondraggesture="nsDragAndDrop.startDrag(event,lvaObserver);"
	       						oncommand="onLVAdoStpl(event);"
	       						idList="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lva_ids"
	       						aktion="lva_single"
	       						tooltiptext="Vorschlag/Setzen Block"
	        				/>
	        				<toolbarbutton
			        			image="../skin/images/lvaMulti.png"
	        					onclick="onLVAdoStpl(event);"
		       					idList="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lva_ids"
	        					aktion="lva_multi"
	        					ondraggesture="nsDragAndDrop.startDrag(event,lvaObserver)"
	        					tooltiptext="Vorschlag/Setzen MultiWeek"
	        				/>
	        			</hbox>
	        			<hbox>
	        				<toolbarbutton
		        				image="../skin/images/lvaSingleDel.png"
	        					onclick="onLVAdoStpl(event);"
	       						idList="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lva_ids"
	       						aktion="lva_stpl_del_single"
	       						tooltiptext="Loeschen SingleWeek"
	        				/>
	        				<toolbarbutton
		        				image="../skin/images/lvaMultiDel.png"
	        					onclick="onLVAdoStpl(event);"
	       						idList="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lva_ids"
	       						aktion="lva_stpl_del_multi"
	       						tooltiptext="Loeschen MultiWeek"
	        				/>
	        			</hbox>
	        		</row>
	    			<row> <!-- style="background-color^: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach_farbe^;" -->
	    				<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrform"
	    					tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach_bez" />
	       				<label align="right" value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#raumtyp"
	       					tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#raumtypalternativ" />
	    			</row>
	    			<row>
	    				<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrverband"
	    					tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#anmerkung" />
	    				<toolbarbutton
		        			label="KW: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw      "
	        				tooltiptext="Zu KW rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw springen"
	        				onclick="onJumpDateRel(event);"
	        				kw="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw"
	       				/>
	    			</row>
	    			<row>
	    				<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lektor" />
	    				<label value="WR: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#wochenrythmus"
	    					tooltiptext="Wochenrythmus"/>
	    			</row>
	    			<row>
	    				<label value="Offen: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#offenestunden h"
	    					tooltiptext="Stunden: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#verplant / rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#semesterstunden" />
	    				<label value="Block: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#stundenblockung"
	    					tooltiptext="Stundenblockung" />
	    			</row>
	    		</rows>
	    	</grid>
	    </rule>
	</template>
</vbox>
</vbox>
</hbox>
</overlay>