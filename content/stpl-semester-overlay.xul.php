<?php
header("Content-type: application/vnd.mozilla.xul+xml");
include('../vilesci/config.inc.php');
include('../include/functions.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
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

<overlay id="TempusOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/stpl-semester-overlay.js.php"/>

<vbox id="vboxTimeTableSemester" flex="1">
<toolbox>
	<toolbar id="toolbarTimeTableSemesterWeek" tbautostretch="always" persist="collapsed">
		<separator orient="horizontal" class="thin" />
		<toolbarbutton id="toolbarbuttonStplSemesterRefresh"
			image="../skin/images/refresh.png"
			oncommand="onSTPLSemesterRefresh();"
			tooltiptext="Neu Laden"
		/>
		<toolbarbutton id="toolbarbuttonStplSemesterPrint"
			image="../skin/images/drucken.png"
			oncommand="onSTPLSemesterPrint();"
			tooltiptext="Drucken"
		/>
	</toolbar>
</toolbox>

<iframe id="iframeTimeTableSemester" name="TimeTableSemester" flex="1"
	src="<?php echo APP_ROOT; ?>content/timetable-week.xul.php?semesterplan=true" />
</vbox>
</overlay>