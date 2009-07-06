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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
 
?>
<html>
<head>
<title>Abgleich der Lehrfaecher</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<?php
	$sql_query='SELECT tbl_stundenplan.*, tbl_lehrfach.lehrform_kurzbz, tbl_lehrfach.kurzbz AS lehrfach, tbl_lehrfach.bezeichnung, tbl_lehrfach.farbe
			FROM tbl_stundenplan, tbl_lehrfach WHERE tbl_stundenplan.lehrfach_nr=tbl_lehrfach.lehrfach_nr
			AND (tbl_stundenplan.studiengang_kz!=tbl_lehrfach.studiengang_kz
				OR tbl_stundenplan.semester!=tbl_lehrfach.semester)';  //LIMIT 10000

	//echo $sql_query."<br>";
	$result=$db->db_query($sql_query);
	$num_rows=@$db->db_num_rows($result);
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($result,$i);
		$sql_query="SELECT lehrfach_nr FROM tbl_lehrfach WHERE studiengang_kz=$row->studiengang_kz
			AND semester=$row->semester AND kurzbz='$row->lehrfach' AND lehrform_kurzbz='$row->lehrform_kurzbz'";
		//echo $sql_query."<br>";
		if (!$res=$db->db_query($sql_query))
			echo $db->db_last_error().'<br>';
		else
			if ($db->db_num_rows($res)>=1)
			{
				$lehrfach_nr=$db->db_fetch_object($res);
				$lehrfach_nr=$lehrfach_nr->lehrfach_nr;
				$sql_query="update tbl_stundenplan set lehrfach_nr=$lehrfach_nr WHERE stundenplan_id=$row->stundenplan_id";
				//echo $sql_query."<br>";
				if (!$ergebniss=$db->db_query($sql_query))
					echo $db->db_last_error().'<br>';
			}
			else
			{
				$sql_query="INSERT INTO tbl_lehrfach (studiengang_kz,semester,kurzbz,lehrform_kurzbz,bezeichnung,fachbereich_id,farbe) VALUES ($row->studiengang_kz,$row->semester,'$row->lehrfach','$row->lehrform_kurzbz','$row->bezeichnung',0,'$row->farbe');";
				echo $sql_query.'<BR>';
				if (!$ergebniss=$db->db_query($sql_query))
					echo $db->db_last_error().'<br>';
			}
	}
	echo $num_rows.' Datensaetze abgeglichen! Fertig<br>';

?>

Datenabgleich abgeschlossen!
</body>
</html>
