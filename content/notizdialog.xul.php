<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

if(isset($_GET['projekt_kurzbz']))
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
else
	$projekt_kurzbz='';

if(isset($_GET['projektphase_id']) && is_numeric($_GET['projektphase_id']))
	$projektphase_id=$_GET['projektphase_id'];
else
	$projektphase_id='';

if(isset($_GET['projekttask_id']) && is_numeric($_GET['projekttask_id']))
	$projekttask_id=$_GET['projekttask_id'];
else
	$projekttask_id='';

if(isset($_GET['uid']))
	$uid=$_GET['uid'];
else
	$uid='';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id=$_GET['person_id'];
else
	$person_id='';

if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else
	$prestudent_id='';

if(isset($_GET['bestellung_id']) && is_numeric($_GET['bestellung_id']))
	$bestellung_id=$_GET['bestellung_id'];
else
	$bestellung_id='';

if(isset($_GET['user']) && is_numeric($_GET['user']))
	$user=$_GET['user'];
else
	$user='';

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id']))
	$lehreinheit_id=$_GET['lehreinheit_id'];
else
	$lehreinheit_id='';

if(isset($_GET['anrechnung_id']) && is_numeric($_GET['anrechnung_id']))
	$anrechnung_id=$_GET['anrechnung_id'];
else
	$anrechnung_id='';

?>

<window id="notiz-dialog" title="Notiz"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="NotizInit(<?php echo "'".$projekt_kurzbz."','".$projektphase_id."','".$projekttask_id."','".$uid."','".$person_id."','".$prestudent_id."','".$bestellung_id."','".$user."','".$lehreinheit_id."','".$anrechnung_id."'";?>)"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/notizdialog.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jquery.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqSOAPClient.js"></script>
<script type="text/javascript" language="JavaScript" src="<?php echo APP_ROOT; ?>include/js/jqXMLUtils.js"></script>

<vbox flex="1">
	<box class="Notiz" flex="1" id="notiz-dialog-box-notiz" />
</vbox>
</window>
