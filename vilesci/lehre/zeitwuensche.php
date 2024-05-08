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
 * Statistik der Zeitwuensche
 * Wenn der GET Parameter fix uebergeben wird, dann werden nur die 
 * Fixangestellten Mitarbeiter beruecksichtigt
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/globals.inc.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
$fix = isset($_GET['fix'])?true:false;

//Stundentabelleholen
if(! $result_stunde=$db->db_query("SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
	die($db->db_last_error());
$num_rows_stunde=$db->db_num_rows($result_stunde);

$qry = "SELECT DISTINCT mitarbeiter_uid AS uid FROM campus.tbl_zeitwunsch";

if($fix)
{	
	$fixwhere= " JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE fixangestellt";
	$qry.=$fixwhere;
}
else
	$fixwhere='';
 
if(!($erg=$db->db_query($qry)))
	die($db->db_last_error());
$anz_lektoren=$db->db_num_rows($erg);

$qry = "SELECT tag,stunde,gewicht+3 AS gewicht, count(*) AS anz FROM campus.tbl_zeitwunsch $fixwhere GROUP BY tag,stunde,gewicht;";
if(!($erg=$db->db_query($qry)))
	die($db->db_last_error());

$num_rows=$db->db_num_rows($erg);
for ($i = 0; $i < $num_rows; $i++)
{
	$row = $db->db_fetch_object($erg, $i);
	$wunsch[$row->tag][$row->stunde][$row->gewicht] = $row->anz;
}

?>

<html>
<head>
<title>Zeitwuensche</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<h2> Statistik der Zeitw&uuml;nsche - <?php echo ($fix?'Fixangestellte':'Alle').' Lektoren' ?></h2>
<a href="zeitwuensche.php?fix">Nur fixangestellte Lektoren anzeigen</a>
<br /><br />
Anzahl der Lektoren: <?PHP echo $anz_lektoren; ?>
<TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
	<TR>
    <?php
	echo '<th>Stunde<br>Beginn<br>Ende</th>';
	for ($i=0;$i<$num_rows_stunde; $i++)
	{
		$beginn=$db->db_result($result_stunde,$i,'"beginn"');
		$beginn=substr($beginn,0,5);
		$ende=$db->db_result($result_stunde,$i,'"ende"');
		$ende=substr($ende,0,5);
		$stunde=$db->db_result($result_stunde,$i,'"stunde"');
		echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
	}
	?>
    </TR>
	<?php
	for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[1][$j].'</TD>';
		if (isset($wunsch)) // Prevents warnings if no data are present
		{
			for ($i=0;$i<$num_rows_stunde;$i++)
			{
				$pos=$wunsch[$j][$i+1][4]+$wunsch[$j][$i+1][5];
				$neg=(isset($wunsch[$j][$i+1][3])?$wunsch[$j][$i+1][3]:0)+
					 (isset($wunsch[$j][$i+1][2])?$wunsch[$j][$i+1][2]:0)+
					 (isset($wunsch[$j][$i+1][1])?$wunsch[$j][$i+1][1]:0)+
					 (isset($wunsch[$j][$i+1][0])?$wunsch[$j][$i+1][0]:0);
				$bgcolor=isset($cfgStdBgcolor[round(14/$anz_lektoren*$pos)-4])?$cfgStdBgcolor[round(14/$anz_lektoren*$pos)-4]:'';
				echo '<TD bgcolor="'.$bgcolor.'">';
				echo '+:'.round(100/$anz_lektoren*$pos).'%<BR>';
				echo '-:'.round(100/$anz_lektoren*$neg).'%';
				echo '</TD>';
			}
		}
		echo '</TR>';
	}
	?>
</TABLE>
Details
<TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
	<TR>
    <?php
	echo '<th>Stunde<br>Beginn<br>Ende</th>';
	for ($i=0;$i<$num_rows_stunde; $i++)
	{
		$beginn=$db->db_result($result_stunde,$i,'"beginn"');
		$beginn=substr($beginn,0,5);
		$ende=$db->db_result($result_stunde,$i,'"ende"');
		$ende=substr($ende,0,5);
		$stunde=$db->db_result($result_stunde,$i,'"stunde"');
		echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
	}
	?>
    </TR>
	<?php
	for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[1][$j].'</TD>';
	  	for ($i=0;$i<$num_rows_stunde;$i++)
		{
			echo '<TD>';
			for ($g=5;$g>=0;$g--)
				if (isset($wunsch[$j][$i+1][$g]))
					echo ($g-3).':'.round(100/$anz_lektoren*$wunsch[$j][$i+1][$g]).'%<BR>';
			echo '</TD>';
		}
		echo '</TR>';
	}
	?>
</TABLE>
</body>
</html>
