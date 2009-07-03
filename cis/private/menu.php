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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrveranstaltung.class.php');

	if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

$cutlength=10;
$rechte=new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$fkt=new funktion();
$fkt->getAll($user);

$stg_obj = new studiengang();

if($stg_obj->getAll('kurzbzlang', false))
{
	$stg = array();
	foreach($stg_obj->result as $row)
		$stg[$row->studiengang_kz] = $row->kurzbzlang;
}
else
	die('Fehler beim Auslesen der Studiengaenge');


if(check_lektor($user))
   $is_lector=true;
else
   $is_lector=false;
  
if(check_student($user))
   $is_student=true;
else
   $is_student=false;

   function CutString($strVal, $limit)
	{
		if(strlen($strVal) > $limit+3)
		{
			return substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}

$qry = "SELECT aktiv FROM campus.vw_benutzer WHERE uid='$user'";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$aktiv = ($row->aktiv=='t'?true:false);
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">
<!--
	__js_page_array = new Array();

    function js_toggle_container(conid)
    {
		if (document.getElementById)
		{
        	var block = "table-row";
			if (navigator.appName.indexOf('Microsoft') > -1)
				block = 'block';
            var status = __js_page_array[conid];
            if (status == null)
            	status=document.getElementById(conid).style.display; //status = "none";
            if (status == "none")
            {
            	document.getElementById(conid).style.display = block;
            	__js_page_array[conid] = "visible";
            }
            else
            {
            	document.getElementById(conid).style.display = 'none';
            	__js_page_array[conid] = "none";
            }
            return false;
     	}
     	else
     		return true;
  	}
//-->
</script>

</head>

<body>
<?php
include('../../include/'.EXT_FKT_PATH.'/cis_menu_meincis.inc.php');
?>
</body>
</html>
