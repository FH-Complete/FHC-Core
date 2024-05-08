<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/variable.class.php');
require_once('../include/benutzer.class.php');

$user=get_uid();
$variable = new variable();
if(!$variable->loadVariables($user))
{
	die('Fehler beim Laden der Variablen:'.$variable->errormsg);
}

$benutzer = new benutzer();
$benutzer->load($user);

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

$person_id = filter_input(INPUT_GET, 'person_id');
$prestudent_id= filter_input(INPUT_GET, 'prestudent_id');

echo '
<!DOCTYPE overlay [';
require('../locale/'.$variable->variable->locale.'/fas.dtd');
echo ']>
';
?>

<window id="udf-window" title="udf"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadUDF(<?php echo "'".$person_id."','".$prestudent_id."'"; ?>);"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/udf.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox flex="1">
	<hbox flex="1">
		<iframe id="udfIFrame" editortype="html" src="about:blank" flex="1" type="content-primary" style="min-width: 100px; min-height: 100px; border: 0px; margin: 10px;"/>
	</hbox>
</vbox>

</window>