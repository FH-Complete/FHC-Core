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
		require_once('../../../config/vilesci.config.inc.php');
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
	include('wochendatum.inc.php');
	$tagsek=86400;
?>

<html>
<head>
<title>Check ID</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<H1>Einheitenplan wird übertragen</H1>

<?php
	// Studiengänge abfragen
	$sql_query="SELECT id, unr, wochentag, stunde, ort_kurzbz, lehrfach_nr, lektor_uid, einheit_kurzbz, studiengang_kz, semester, jahreswochen FROM untis
		WHERE ort_kurzbz IS NOT NULL AND lehrfach_nr IS NOT NULL AND lektor_uid IS NOT NULL AND studiengang_kz IS NOT NULL AND semester IS NOT NULL AND lehrfach LIKE '\\\\_%'";
	
	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_num_rows($result);	
	
	echo $num_rows.' Rows will be moved<BR>';
	flush();

	for ($i=0; $i<$num_rows; $i++)
	{
		if (($i%50)==0)
			echo '<BR>'.$i;
		echo '.';
		flush();
		$row=$db->db_fetch_object($result,$i);
		for ($w=1; $w<=53; $w++)
		{
			if (substr($row->jahreswochen,$w-1,1)=='1')
			{
				$date=$week_date[$w]+($tagsek*($row->wochentag-1));
				$date=getdate($date);
				$tag=$date[mday];
				$monat=$date[mon];
				$jahr=$date[year];
				$date=$jahr.'-'.$monat.'-'.$tag;
				$sql_query="INSERT INTO tbl_stundenplan (unr, ort_kurzbz, datum, stunde, lehrfach_nr, einheit_kurzbz, uid, studiengang_kz, semester, verband, gruppe) VALUES
					('$row->unr', '$row->ort_kurzbz', '$date', $row->stunde, $row->lehrfach_nr,'$row->einheit_kurzbz','$row->lektor_uid',$row->studiengang_kz,$row->semester,NULL,NULL)";
				$result_insert=$db->db_query($sql_query);
			}
		}
		$sql_query="DELETE FROM untis WHERE id=$row->id";
		$result_insert=$db->db_query($sql_query);
	}
?>
<BR>Finished!
</body>
</html>
