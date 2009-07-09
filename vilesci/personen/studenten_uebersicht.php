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

 /**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */

 
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
			
	include('../../include/functions.inc.php');

	if(!($erg=$db->db_query("SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kurzbz, bezeichnung FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
?>

<html>
<head>
<title>Studenten Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Studenten Übersicht</h4>

<table border="1">
<tr>
<?php
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=@$db->db_fetch_object($erg, $i);
		echo "<th>$row->kurzbz<BR><SMALL>$row->bezeichnung</SMALL></th>";
	}
?>
</tr>
<tr bgcolor="#DDDDDD" valign="top">
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		echo "<td nowrap>";
		$row=$db->db_fetch_object($erg, $i);
		$stg_kzbz=$row->kurzbz;
		$stg_kz=$row->studiengang_kz;
		$sql_query="SELECT DISTINCT semester FROM public.tbl_student WHERE studiengang_kz=$stg_kz ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=$db->db_query($sql_query)))
			die($db->db_last_error());
		$nr_sem=$db->db_num_rows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=$db->db_fetch_object($result_sem, $j);
			echo "<a class=\"h1\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester\">$stg_kzbz-$row_sem->semester</a><br>";

			$sql_query="SELECT DISTINCT verband FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=$db->db_query($sql_query)))
				die($db->db_last_error());
			$nr_ver=$db->db_num_rows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=$db->db_fetch_object($result_ver, $k);
				echo "&nbsp;- <a class=\"linkblue\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband\">$stg_kzbz-$row_sem->semester$row_ver->verband</a><br>";

				$sql_query="SELECT DISTINCT gruppe FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=$db->db_query($sql_query)))
					die($db->db_last_error());
				$nr_grp=$db->db_num_rows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=$db->db_fetch_object($result_grp, $l);
					echo "&nbsp;&nbsp;- <a class=\"linkgreen\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband&grp=$row_grp->gruppe\">$stg_kzbz-$row_sem->semester$row_ver->verband$row_grp->gruppe</a><br>";

				}
			}
		}
		echo"</td>";
	}
?>
</tr>
</table>
</body>
</html>
