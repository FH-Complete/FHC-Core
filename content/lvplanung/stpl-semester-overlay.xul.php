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

include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');

echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
/*echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';*/

$uid=get_uid();

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Fehler: Es konnte keine Verbindung zum Server aufgebaut werden!';
//$error_msg.=loadVariables($conn,$REMOTE_USER);
// Stundentafel abfragen
$sql_query="SET datestyle TO ISO; SELECT * FROM lehre.tbl_stunde ORDER BY stunde";
if(!$result_stunde=pg_exec($conn, $sql_query))
	die(pg_last_error($this->conn));
$num_rows_stunde=pg_numrows($result_stunde);
?>

<!DOCTYPE overlay>
<!-- [<?php require_once("../../locale/de-AT/tempus.dtd"); ?>] -->

<overlay id="TempusOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/lvplanung/stpl-semester-overlay.js.php"/>

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
	src="<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php?semesterplan=true" />
</vbox>
</overlay>