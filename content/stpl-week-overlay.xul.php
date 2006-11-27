<?php
header("Content-type: application/vnd.mozilla.xul+xml");
include('../vilesci/config.inc.php');
include('../include/functions.inc.php');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/stpl-details-overlay.xul.php"?>';
/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';
	echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';*/

// Testumgebung
if (!isset($REMOTE_USER))
	$REMOTE_USER='pam';

$uid=$REMOTE_USER;

if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Fehler: Es konnte keine Verbindung zum Server aufgebaut werden!';
//$error_msg.=loadVariables($conn,$REMOTE_USER);
// Stundentafel abfragen
$sql_query="SET datestyle TO ISO;SELECT * FROM tbl_stunde ORDER BY stunde";
if(!$result_stunde=pg_exec($conn, $sql_query))
	die(pg_last_error($this->conn));
$num_rows_stunde=pg_numrows($result_stunde);
?>

<!DOCTYPE overlay [
	<?php require("../locale/tempus.dtd"); ?>
]>

<overlay id="TempusOverlaySTPLWeek"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/stpl-week-overlay.js.php"/>

<hbox id="hboxTimeTableWeek">
<vbox id="vboxTimeTableWeek" flex="30">
<toolbox>
	<toolbar id="toolbarTimeTableJumpWeek" tbautostretch="always" persist="collapsed">
		<toolbarbutton id="toolbarbuttonJumpWeekMoreLeft"
			tooltiptext="16 Wochen zur??ck"
			image="../skin/images/moremoreleft.png"
			oncommand="onJumpDate(-16);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekMoreLeft"
			tooltiptext="4 Wochen zur??ck"
			image="../skin/images/moreleft.png"
			oncommand="onJumpDate(-4);"
		/>
		<toolbarbutton id="toolbarbuttonJumpWeekLeft"
			tooltiptext="1 Woche zur??ck"
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
	src="<?php echo APP_ROOT; ?>content/timetable-week.xul.php" />
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
	datasources="lehrveranstaltung.rdf.php"
	ref="http://www.technikum-wien.at/tempus/lehrveranstaltung/alle">
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
	       						idList="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lva_ids"
	       						aktion="lva_single"
	       						tooltiptext="Vorschlag/Setzen Block"
	        				/>
	        				<toolbarbutton
			        			image="../skin/images/lvaMulti.png"
	        					onclick="onLVAdoStpl(event);"
		       					idList="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lva_ids"
	        					aktion="lva_multi"
	        					ondraggesture="nsDragAndDrop.startDrag(event,lvaObserver)"
	        					tooltiptext="Vorschlag/Setzen MultiWeek"
	        				/>
	        			</hbox>
	        			<hbox>
	        				<toolbarbutton
		        				image="../skin/images/lvaSingleDel.png"
	        					onclick="onLVAdoStpl(event);"
	       						idList="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lva_ids"
	       						aktion="lva_stpl_del_single"
	       						tooltiptext="Löschen SingleWeek"
	        				/>
	        				<toolbarbutton
		        				image="../skin/images/lvaMultiDel.png"
	        					onclick="onLVAdoStpl(event);"
	       						idList="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lva_ids"
	       						aktion="lva_stpl_del_multi"
	       						tooltiptext="Löschen MultiWeek"
	        				/>
	        			</hbox>
	        		</row>
	    			<row style="background-color^: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lehrfach_farbe^;">
	    				<label value="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lehrfach rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lehrform"
	    					tooltiptext="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lehrfach_bez" />
	       				<label align="right" value="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#raumtyp"
	       					tooltiptext="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#raumtypalternativ" />
	    			</row>
	    			<row>
	    				<label value="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lehrverband"
	    					tooltiptext="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#anmerkung" />
	    				<toolbarbutton
		        			label="KW: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#start_kw      "
	        				tooltiptext="Zu KW rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#start_kw springen"
	        				onclick="onJumpDateRel(event);"
	        				kw="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#start_kw"
	       				/>
	    			</row>
	    			<row>
	    				<label value="rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#lektor" />
	    				<label value="WR: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#wochenrythmus"
	    					tooltiptext="Wochenrythmus"/>
	    			</row>
	    			<row>
	    				<label value="Offen: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#offenestunden h"
	    					tooltiptext="Stunden: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#verplant / rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#semesterstunden" />
	    				<label value="Block: rdf:http://www.technikum-wien.at/tempus/lehrveranstaltung/rdf#stundenblockung"
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