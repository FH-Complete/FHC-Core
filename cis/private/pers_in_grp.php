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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<title>Personen im Mailverteiler</title>
<body id="inhalt">
<?php
  require_once('../../config/cis.config.inc.php');
  require_once('../../include/basis_db.class.php');
  require_once('../../include/functions.inc.php');
  require_once('../../include/studiensemester.class.php');
  if (!$db = new basis_db())
  		die('Fehler beim Oeffnen der Datenbankverbindung');
  
  if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();

	if(check_lektor($user))
       $is_lector=true;
  else
       $is_lector=false;       
       
?>
<table class="tabcontent">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">Nachname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">Vorname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">E-Mail</font></td>
	      </tr>


<?php
 		  //$sql_query = "SELECT vornamen AS vn,nachname AS nn,a.uid as uid FROM public.tbl_personmailgrp AS a, public.tbl_person AS b WHERE a.uid=b.uid AND a.mailgrp_kurzbz='$grp' ORDER BY nachname";
	  $qry = "SELECT uid, vorname, nachname FROM campus.vw_benutzer JOIN tbl_benutzergruppe USING (uid) WHERE gruppe_kurzbz='".addslashes($_GET['grp'])."' AND (studiensemester_kurzbz IS NULL OR studiensemester_kurzbz='$stsem') ORDER BY nachname, vorname";
	  if($result=$db->db_query($qry))
	  {
	  	while($row = $db->db_fetch_object($result))
	  	{
		  	echo "<tr>";
	      	echo "  <td>$row->nachname</td>";
	      	echo "  <td>$row->vorname</td>";
	      	echo "  <td><a href='mailto:$row->uid@".DOMAIN."' class='Item'>$row->uid@".DOMAIN."</a></td>";
	      	echo "</tr>";
		}
	  }
?>
</body></html>
