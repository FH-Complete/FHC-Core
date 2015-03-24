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
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/basis_db.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/lvplan',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$cfgBorder=0;
$cfgBgcolorOne='liste0';
$cfgBgcolorTwo='liste1';		
?>

<html>
<head>
<title>Stundenplan Check</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierung</H1>
<H2>Doppelbelegungen </H2>
<table border="<?php echo (isset($cfgBorder)?$cfgBorder:0);?>">
<tr>
<?php
	//Reservierungsdaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT count(*), datum, stunde, ort_kurzbz FROM campus.tbl_reservierung GROUP BY datum, stunde, ort_kurzbz HAVING (count(*)>1) ORDER BY datum, stunde, ort_kurzbz LIMIT 20";
	//echo $sql_query."<br>";
	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_num_rows($result);
	else
		die($db->db_last_error().' <a href="javascript:history.back()">Zur&uuml;ck</a>');			
		
	if ($num_rows>0)
	{
		$num_fields=$db->db_num_fields($result);
		$foo = 0;
		for ($i=0;$i<$num_fields; $i++)
	    	echo "<th>".$db->db_field_name($result,$i)."</th>";
		for ($j=0; $j<$num_rows;$j++)
		{
			$row=$db->db_fetch_row($result,$j);
			echo "<tr class='liste".($j%2)."'>";
	    	for ($i=0; $i<$num_fields; $i++)
				echo "<td>$row[$i]</td>";
			//echo "<td><a href=\"res_check_det.php?datum=$row[1]&stunde=$row[2]&ort_kurzbz=$row[3]\">Details</a></td>";
	    	echo "</tr>\n";
			$foo++;
		}
	}
	else
		echo "Keine Doppelbelegungen gefunden!";
?>
</table>
<H2>Kollisionen mit Stundenplan</H2>
<?php
	flush();
	//Reservierungsdaten ermitteln welche mit Stundenplan kollidieren
	$sql_query="SELECT reservierung_id, datum, stunde, ort_kurzbz, uid FROM campus.tbl_reservierung WHERE datum>=now() ORDER BY datum, stunde, ort_kurzbz";
	//echo $sql_query."<br>";
	$result_res=$db->db_query($sql_query);
	$num_rows_res=$db->db_num_rows($result_res);
	if ($num_rows_res>0)
	{
		echo $num_rows_res.' Einträge werden überprüft .';
		$foo = 0;
		for ($r=0;$r<$num_rows_res;$r++)
		{
			$row_res=$db->db_fetch_object($result_res,$r);
			$sql_query="SELECT * FROM lehre.vw_stundenplan WHERE datum=".$db->db_add_param($row_res->datum)." AND stunde=".$db->db_add_param($row_res->stunde)." AND ort_kurzbz=".$db->db_add_param($row_res->ort_kurzbz);
			//echo $sql_query."<br>";
			$result=$db->db_query($sql_query);
			$num_rows=$db->db_num_rows($result);
			//echo $num_rows;
			if ($num_rows>0)
			{
				echo '<table border="0"><tr>';
				$num_fields=$db->db_num_fields($result);
				echo "<th></th>";
				for ($i=0;$i<$num_fields; $i++)
	    			echo "<th>".$db->db_field_name($result,$i)."</th>";
				echo "</tr>\n";
				for ($j=0; $j<$num_rows;$j++)
				{
					$row=$db->db_fetch_row($result,$j);
					$rowo=$db->db_fetch_object($result,$j);
					echo "<tr class='liste".($j%2)."'>";
					//echo "<td><a href=\"res_check_det.php?datum=$rowo->datum&stunde=$rowo->stunde&ort_kurzbz=$rowo->ort_kurzbz\">Reservierung</a></td>";
	    			for ($i=0; $i<$num_fields; $i++)
								echo "<td>$row[$i]</td>";
					echo "</tr>\n";
					$foo++;
				}
				echo '</table>';
				flush();
			}
			if ($r%500==0)
				echo '<BR>'.$r;
			if ($r%10==0)
			{
				echo '.';
				flush();
			}
		}
	}
	else
		echo "Kein Eintrag gefunden!";
?>

</body>
</html>
