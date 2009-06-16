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
	require_once('../../config.inc.php');
  require_once('../../../include/functions.inc.php');
  require_once('../../../include/benutzerberechtigung.class.php');
  require_once('../../../include/lehrveranstaltung.class.php');

  //Connection Herstellen
  if(!$sql_conn = pg_pconnect(CONN_STRING))
     die("Fehler beim oeffnen der Datenbankverbindung");

	$user = get_uid();

  $rechte= new benutzerberechtigung($sql_conn);
  $rechte->getBerechtigungen($user);

	if(check_lektor($user,$sql_conn))
       $is_lector=true;
  else
       $is_lector=false;

	function CutString($strVal, $limit)
	{
		if(strlen($strVal) > $limit)
		{
			return substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">
<!--
        __js_page_array = new Array();

        function js_toggle_container(conid) {

	if (document.getElementById) {
              var block = "table-row";

              if (navigator.appName.indexOf('Microsoft') > -1) {
                 block = 'block';
              }

              var status = __js_page_array[conid];
              if (status == null) {
                  status = "none";
              }

              if (status == "none") {
                 document.getElementById(conid).style.display = block;
                 __js_page_array[conid] = "visible";
              } else {
                 document.getElementById(conid).style.display = 'none';
                 __js_page_array[conid] = "none";
              }
              return false;
           } else {
             return true;
           }
        }
  //-->
</script>

<?php
	echo '<script language="JavaScript" type="text/javascript">';
		echo '	parent.content.location.href="pinboard.php"';
	echo '</script>';
?>
</head>

<body>
<table class="tabcontent">
  <tr>
    <td width="159" class='tdvertical' nowrap>
	  <table class="tabcontent" frame="rhs">
	    <tr>
          <td class='tdwrap'><a class="HyperItem" href="../../index.html" target="_top">&lt;&lt; HOME</a> </a></td>
  		</tr>
		<tr>
		  <td class='tdwrap'>&nbsp;</td>
		</tr>
		<?php
			$lv_obj = new lehrveranstaltung($sql_conn);
			if(!$lv_obj->load_lva('0',null, null, true,null,'bezeichnung'))
				echo "<tr><td>$lv_obj->errormsg</td></tr>";

			foreach($lv_obj->lehrveranstaltungen AS $row)
			{
				echo '<tr>';
				echo '	<td class="tdwrap">';
				echo "	  <li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"../lehre/lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".CutString($row->bezeichnung, 21)."</a></li>";
				echo '	</td>';
				echo '</tr>';
			}

		?>
		<tr><td>&nbsp;</td></tr>
		<tr>
          <td class='tdwrap'><a class="MenuItem" href="pinboard.php" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboard</a></td>
  		</tr>
  		<tr>
          <td class='tdwrap'><a href="anmeldung.php" target='content' class="MenuItem" onClick="js_toggle_container('Anmeldung')"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Anmeldung</a></td>
  		</tr>
		<tr>
          <td class='tdwrap'>
		  	<table class="tabcontent" id="Anmeldung" style="display: none;">
			  <tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="anmeldungsuebersicht.php" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
			  </tr>
			</table>
		  </td>
  		</tr>
		<tr>
          <td class='tdwrap'><a href="?Info &amp; Kommunikation" class="MenuItem" onClick="return(js_toggle_container('Info &amp; Kommunikation'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Info &amp; Kommunikation</a></td>
  		</tr>
		<tr>
          <td class='tdwrap'>
		  	<table class="tabcontent" id="Info &amp; Kommunikation" style="display: none;">
			  <tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="../../private/lvplan/" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Plan</a></td>
			  </tr>
	    	  <tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
			  </tr>
			  <tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="../../public/faq_upload.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;FAQ</a></td>
			  </tr>
			</table>
		  </td>
  		</tr>
		<?php
			if($is_lector || $rechte->isBerechtigt('admin'))
			{
				echo '<tr>';
				echo '  <td class="tdwrap"><a href="?Lektorenbereich" class="MenuItem" onClick="return(js_toggle_container(\'Lektorenbereich\'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lektorenbereich</a></td>';
				echo '</tr>';
				echo '<tr>';
				echo '  <td class="tdwrap">';
				echo '  	<table class="tabcontent" id="Lektorenbereich" style="display: none;">';
				echo '	  <tr>';
				echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
				echo '		<td class="tdwrap"><a class="Item" href="pinboardverwaltung.php" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboardverwaltung</a></td>';
				echo '	  </tr>';
				echo '	</table>';
				echo '  </td>';
				echo '</tr>';
			}
		?>
	  </table>
	</td>
</table>
</body>
</html>