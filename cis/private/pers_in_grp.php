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
  require_once('../../config/cis.config.inc.php');
  require_once('../../include/basis_db.class.php');
  require_once('../../include/functions.inc.php');
  require_once('../../include/studiensemester.class.php');
  require_once('../../include/phrasen.class.php');
  
  $sprache = getSprache();
  $p = new phrasen($sprache);
  
  if (!$db = new basis_db())
  		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
  
  if (!$user=get_uid())
		die($p->t('global/fehlerBeimErmittelnDerUID'));

	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();

	if(check_lektor($user))
       $is_lector=true;
  else
       $is_lector=false;       
       
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript">	
	$(document).ready(function() 
		{ 
		    $("#table").tablesorter(
			{
				sortList: [[0,0]],
				widgets: [\'zebra\'],
			}); 
		} 
	);
	</script>
	<title>'.$p->t('mailverteiler/personenImVerteiler').'</title>
</head>
<body id="inhalt">';

	$qry = "SELECT uid, vorname, nachname FROM campus.vw_benutzer JOIN tbl_benutzergruppe USING (uid) WHERE gruppe_kurzbz='".addslashes($_GET['grp'])."' AND (studiensemester_kurzbz IS NULL OR studiensemester_kurzbz='".addslashes($stsem)."') ORDER BY nachname, vorname";
	if($result=$db->db_query($qry))
	{
		echo '<p>'.$row=$db->db_num_rows($result).' '.$p->t('mailverteiler/personen');
	}
	
	echo'<table class="tablesorter" id="table">
		<thead>
		<tr>
			<th>'.$p->t('global/nachname').'</th>
			<th>'.$p->t('global/vorname').'</th>
			<th>'.$p->t('global/mail').'</th>
		</tr></thead><tbody>';


 		  //$sql_query = "SELECT vornamen AS vn,nachname AS nn,a.uid as uid FROM public.tbl_personmailgrp AS a, public.tbl_person AS b WHERE a.uid=b.uid AND a.mailgrp_kurzbz='$grp' ORDER BY nachname";
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
echo '
		</tbody></table>
	</body>
</html>';
?>