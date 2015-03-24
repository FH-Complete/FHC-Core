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
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/lvplan',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');


	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="SELECT count(*), datum, stunde, ort_kurzbz, studiengang_kz, semester, verband, gruppe FROM lehre.tbl_stundenplan GROUP BY datum, stunde, ort_kurzbz, studiengang_kz, semester, verband, gruppe HAVING (count(*)>1) ORDER BY datum, stunde, ort_kurzbz LIMIT 20";
	//echo $sql_query."<br>";

	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_num_rows($result);
	else
		die($db->db_last_error().' <a href="javascript:history.back()">Zur&uuml;ck</a>');			
			
		
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
<H1>Mehrfachbelegungen</H1>
<table border="<?php echo $cfgBorder;?>">
<tr>
<?php 
if ($num_rows>0)
{
	$num_fields=$db->db_num_fields($result);
	$foo = 0;
	for ($i=0;$i<$num_fields; $i++)
	    echo "<th>".$db->db_field_name($result,$i)."</th>";		
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=$db->db_fetch_row($result,$j);
		$bgcolor = $cfgBgcolorOne;
		$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		echo "<tr class='liste".($j%2)."'>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td>$row[$i]</td>";		
	    echo "</tr>\n";
		$foo++;
	}
}
else
	echo "Kein Eintrag gefunden!";
?>
</table>
</body>
</html>
