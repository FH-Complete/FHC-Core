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
		
	//Variablen setzen
	$stg_id=6;
	$stg_bez='ICSS';
	$datum_beginn='2003-09-01';
	$datum_ende='2004-02-09';
	// Stundenplantabelle abfragen
	$sql_query="SELECT count(*) AS stunden,unr, semester, lehrfach_id, fachbereich_kurzbz, uid,lektor FROM campus.vw_stundenplan";
	$sql_query.=" WHERE studiengang_kz=$stg_id AND datum>='$datum_beginn' AND datum<='$datum_ende'";
	$sql_query.=" GROUP BY unr, semester, lehrfach_id,  fachbereich_kurzbz, uid,lektor";
	$sql_query.=" ORDER BY semester,unr,uid";
	//echo $sql_query."<br>";
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_num_rows($result);
	else
		die($db->db_last_error());

	$cfgBorder=1;
	$cfgBgcolorOne='#DAD8D8';
  $cfgBgcolorTwo='#ECECEC';
	$cfgThBgcolor='#FFFFFF';
			
	// Daten in Array übernehmen
	for ($i=0;$i<$num_rows;$i++) 
	{
		$row=$db->db_fetch_object ($result, $i);
		$unterricht[$i]->lektor_kbz=$row->lektorkurzbz;
		$unterricht[$i]->lektor_id=$row->lektor_id;
		$unterricht[$i]->lehrfach_kbz=$row->lehrfachkurzbz;
		$unterricht[$i]->lehrfach_id=$row->lehrfach_id;
		$unterricht[$i]->unr=$row->unr;
		$unterricht[$i]->sem=$row->semester;
		$unterricht[$i]->stunden=$row->stunden;
	}		
?>
<html>
<head>
<title><?PHP echo $stg_bez; ?> - Lehrfachverteilung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<H1><?PHP echo $stg_bez; ?> - &Uuml;berblick des Stundenplans</H1>
<H2>Von <?PHP echo $datum_beginn; ?> bis <?PHP echo $datum_ende; ?></H2>
<hr>
<table border="<?php echo $cfgBorder;?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>"><th>Semester</th><th>UNr</th><th>Lehrfach</th><th>Lektor(en) [Stunden]</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		// Hintergrundfarbe wechseln
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		// Zeilenweise ausgeben
		echo "<tr bgcolor=$bgcolor>";
		echo '<td>'.$unterricht[$i]->sem.'</td><td>'.$unterricht[$i]->unr.'</td><td>'.$unterricht[$i]->lehrfach_kbz.'</td>';
		echo '<td>'.$unterricht[$i]->lektor_kbz.' ['.$unterricht[$i]->stunden.']';
		while ($unterricht[$i]->unr==$unterricht[$i+1]->unr)
			echo ', '.$unterricht[++$i]->lektor_kbz.' ['.$unterricht[$i]->stunden.']';
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
</body>
</html>