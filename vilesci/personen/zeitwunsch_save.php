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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
	include('../../include/functions.inc.php');
	
	
	if(!($erg_std=$db->db_query("SELECT * FROM stunde ORDER BY id")))
		die($db->db_last_error());
	$num_rows_std=$db->db_num_rows($erg_std);
	for ($t=1;$t<7;$t++)
		for ($i=0;$i<$num_rows_std;$i++)
		{
			$var='wunsch'.$t.'_'.$i;
			//echo $$var;
			$gewicht=$$var;
			$stunde=$i+1;
			$query="SELECT * FROM zeitwunsch WHERE lektor_id=$lkid AND stunde_id=$stunde AND tag=$t";
			if(!($erg_wunsch=$db->db_query($query)))
				die($db->db_last_error());
			$num_rows_wunsch=$db->db_num_rows($erg_wunsch);
			if ($num_rows_wunsch==0)
			{
				$query="INSERT INTO zeitwunsch (lektor_id, stunde_id, tag, gewicht) VALUES ($lkid, $stunde, $t, $gewicht)";
				if(!($erg=$db->db_query($query)))
					die($db->db_last_error());
			}
			elseif ($num_rows_wunsch==1)
			{
				$id=$db->db_result($erg_wunsch,0,"id");
				$query="UPDATE zeitwunsch SET lektor_id=$lkid, stunde_id=$stunde, tag=$t, gewicht=$gewicht WHERE id=$id";
				if(!($erg=$db->db_query($query)))
					die($db->db_last_error());
			}
			else
				die("Zuviele Eintraege fuer!");
		}

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
<META http-equiv="refresh" content="2;URL=zeitwunsch.php?lkid=<?php echo $lkid.'&vornamen='.$vornamen.'&nachname='.$nachname.'&titel='.$titel; ?>">
</head>

<body class="background_main">
<h4>Zeitw&uuml;nsche von
  <?php echo $titel.' '.$vornamen.' '.$nachname; ?>
  sind aktualisiert!</h4>
<A href="zeitwunsch.php?lkid=<?php echo $lkid.'&vornamen='.$vornamen.'&nachname='.$nachname.'&titel='.$titel; ?>">&lt;&lt;
Zur&uuml;ck</A><br>


</body>
</html>
