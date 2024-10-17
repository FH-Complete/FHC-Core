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
include('../../config/global.config.inc.php');

if(defined('FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN') && FAS_LV_LEKTORINNENZUTEILUNG_VERTRAGSDETAILS_ANZEIGEN)
{
	$showvertragsfilter = true;
}
else
{
	$showvertragsfilter = false;
}
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

<vbox style="margin:0px; width:250px" flex="1">
<toolbox>
		<toolbar id="toolbarTimeTableLeftWeek" tbautostretch="always" persist="collapsed">
			<toolbarbutton id="toolbarbuttonStplWeekRefresh"
				image="../skin/images/refresh.png"
				oncommand="onLVARefresh();"
				tooltiptext="Neu laden"
			/>
			<textbox id="tempus-lva-filter" size="10" oninput="onLVAFilter()" flex="1"/>
		</toolbar>
		<toolbar
			id="toolbarTimeTableFilterVertrag"
			tbautostretch="always"
			persist="collapsed"
			hidden="<?php echo ($showvertragsfilter?'false':'true');?>"
		>
		<toolbarbutton
			image="../public/images/icons/fa-user-clock.png"
			label="Alle"
			class="timetablefilter-vertrag"
			oncommand="onLVAFilterVertrag(this);"
			value=""
			id="toolbarTimeTableFilter-alle"
			tooltiptext="Filter Status Alle"
		/>
		<toolbarbutton
			image="../public/images/icons/fa-user-tag.png"
			label="Bestellt"
			class="timetablefilter-vertrag"
			oncommand="onLVAFilterVertrag(this);"
			value="bestellt"
			id="toolbarTimeTableFilter-bestellt"
			tooltiptext="Filter Status Bestellt"
		/>
		<toolbarbutton
			image="../public/images/icons/fa-user-check.png"
			label="Erteilt"
			class="timetablefilter-vertrag"
			oncommand="onLVAFilterVertrag(this);"
			value="erteilt"
			id="toolbarTimeTableFilter-erteilt"
			tooltiptext="Filter Status Erteilt"
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
	    			<row>
	    				<hbox class="StyleBox" flex="1"
	    					mystyle="background-color: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach_farbe">
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
	        			<hbox class="StyleBox" flex="1"
	    					mystyle="background-color: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach_farbe">
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
	    			<row>
						<hbox>
	    					<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrform"
	    						tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrfach_bez" />
							<spacer flex="1" />
							<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#vertragsstatus" class="tempus_vertrag_info"/>
						</hbox>
	       				<label align="right" value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#raumtyp"
	       					tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#raumtypalternativ" />
	    			</row>
	    			<row>
						<vbox flex="1">
	    				<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehrverband"
	    					tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#anmerkung" />
						</vbox>
	    				<toolbarbutton
		        			label="KW: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw"
	        				tooltiptext="Zu KW rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw springen"
	        				onclick="onJumpDateRel(event);"
							class="stplweekoverlay-toolbarbutton"
	        				kw="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#start_kw"
	       				/>
	    			</row>
	    			<row>
	    				<hbox>
							<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lektor" />
							<spacer flex="1"/>
							<label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#fixangestellt_info" class="tempus_lektor_fix_info"/>
						</hbox>
	    				<label value="WR: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#wochenrythmus Bl: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#stundenblockung"
	    					tooltiptext="Wochenrhythmus" />
	    			</row>
	    			<row>
	    				<label value="Offen: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#offenestunden / rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#semesterstunden"
	    					tooltiptext="Stunden: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#verplant / rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#semesterstunden" />
	    				<toolbarbutton align="start"
		        			label="Notizen: rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#anzahl_notizen"
							lehreinheit_id="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lehreinheit_id"
	        				onclick="StplWeekOpenNotiz(this)"
							class="stplweekoverlay-toolbarbutton"
	       				/>

	    			</row>
                    <row>
                        <label value="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#zeitverfuegbarkeit"
                               tooltiptext="rdf:http://www.technikum-wien.at/lehreinheit-lvplan/rdf#lektor hat in diesem Studiensemester verfügbare Zeiten" class="tempus_lektor_verfuegbarezeit"/>
                    </row>
	    		</rows>
	    	</grid>

	    </rule>
	</template>
</vbox>
<toolbox>

		<toolbar id="toolbarTimeTableSort" tbautostretch="always" persist="collapsed">
			<toolbarbutton
				image="../skin/images/down.png"
				oncommand="onLVASort(this);"
				value="lfDESC"
				id="toolbarTimeTableSort-lfDESC"
				tooltiptext="Lehrfach absteigend"
			/>
			<toolbarbutton
				image="../skin/images/up.png"
				oncommand="onLVASort(this);"
				value="lfASC"
				id="toolbarTimeTableSort-lfASC"
				tooltiptext="Lehrfach aufsteigend"
			/>
			<toolbarbutton
				image="../skin/images/user_down.png"
				oncommand="onLVASort(this);"
				value="lektorDESC"
				id="toolbarTimeTableSort-lektorDESC"
				tooltiptext="Lektor absteigend"
			/>
			<toolbarbutton
				image="../skin/images/user_up.png"
				oncommand="onLVASort(this);"
				value="lektorASC"
				id="toolbarTimeTableSort-lektorASC"
				tooltiptext="Lektor aufsteigend"
			/>
			<toolbarbutton
				image="../skin/images/clock_down.png"
				oncommand="onLVASort(this);"
				value="stundenDESC"
				checked="true"
				id="toolbarTimeTableSort-stundenDESC"
				tooltiptext="Offene Stunden absteigend"
			/>
			<toolbarbutton
				image="../skin/images/clock_up.png"
				oncommand="onLVASort(this);"
				value="stundenASC"
				id="toolbarTimeTableSort-stundenASC"
				tooltiptext="Offenen Stunden aufsteigend"
			/>
		</toolbar>
</toolbox>
</vbox>
</hbox>
</overlay>
