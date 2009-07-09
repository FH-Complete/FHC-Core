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
	 *	Raumauslastung
	 *
	 */

		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
	
	require('../../include/globals.inc.php');
	$raum=array();


	if (isset($_POST['datum_beginn']))
		$datum_beginn=$_POST['datum_beginn'];
	else
		$datum_beginn=date('Y').'-'.(date('m')>7?'06':'01').'-01';
	if (isset($_POST['datum_ende']))
		$datum_ende=$_POST['datum_ende'];
	else
		$datum_ende=date('Y').'-'.(date('m')>7?'12':'07').'-30';
	if (isset($_POST['stunde_beginn']))
		$stunde_beginn=$_POST['stunde_beginn'];
	else
		$stunde_beginn=12;
	if (isset($_POST['stunde_ende']))
		$stunde_ende=$_POST['stunde_ende'];
	else
		$stunde_ende=16;

	$ts_beginn=mktime(0,0,0,substr($datum_beginn,5,2),substr($datum_beginn,8,2),substr($datum_beginn,0,4));
	$ts_ende=mktime(0,0,0,substr($datum_ende,5,2),substr($datum_ende,8,2),substr($datum_ende,0,4));

	$wochen=round(($ts_ende-$ts_beginn)/(60*60*24*7));

	//Stundenplandaten holen
	$sql_query="SELECT DISTINCT datum,stunde,ort_kurzbz, EXTRACT(DOW FROM datum) AS tag FROM lehre.tbl_stundenplan
					WHERE datum>='$datum_beginn' AND datum<='$datum_ende' AND stunde>=$stunde_beginn AND stunde<=$stunde_ende
					ORDER BY ort_kurzbz";

	if(!$result=$db->db_query($sql_query))
			die($db->db_last_error());
	//echo $sql_query;
	//Aufbereitung
	while ($row=$db->db_fetch_object($result))
	{
		$raum[$row->ort_kurzbz]->ort=$row->ort_kurzbz;
		if (!isset($raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl))
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl=1;
		else
			$raum[$row->ort_kurzbz]->last[$row->tag][$row->stunde]->anzahl++;
	}

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<form method="POST">
	Beginn:<input name="datum_beginn" value="<?php echo $datum_beginn; ?>" size="8" />
	Ende:<input name="datum_ende" value="<?php echo $datum_ende; ?>" size="8" />
	&nbsp;&nbsp;&nbsp;&nbsp;
	Stunde -> von:<input name="stunde_beginn" value="<?php echo $stunde_beginn; ?>" size="2" />
	bis:<input name="stunde_ende" value="<?php echo $stunde_ende; ?>" size="2" />
	<input type="submit">
</form>
<h2> Raumauslastung vom <?PHP echo $datum_beginn.' - '.$datum_ende.' ('.$wochen; ?> Wochen)</h2>
<TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
	<TR>
    <?php
    	$span=$stunde_ende-$stunde_beginn+1;
	echo "<th rowspan='2'>Ort</th><th colspan='$span'>Montag</th><th colspan='$span'>Dienstag</th><th colspan='$span'>Mittwoch</th>
		<th colspan='$span'>Donnerstag</th><th colspan='$span'>Freitag</th><th colspan='$span'>Samstag</th>";
	?>
    </TR>
	<TR>
    <?php
	echo '';
	for ($t=1;$t<7;$t++)
		for ($s=$stunde_beginn;$s<=$stunde_ende; $s++)
		{
			echo "<th>$s</th>";
		}
	?>
    </TR>
	<?php
	$anz_colors=count($cfgStdBgcolor)-1;
	foreach ($raum AS $ort)
	{
		echo '<TR><TD>'.$ort->ort.'</TD>';
	  	for ($t=1;$t<7;$t++)
			for ($s=$stunde_beginn;$s<=$stunde_ende; $s++)
			{
				if (!isset($ort->last[$t][$s]->anzahl))
					$ort->last[$t][$s]->anzahl=0;
				$bgcolor=$cfgStdBgcolor[$anz_colors-round(($ort->last[$t][$s]->anzahl)/($wochen/$anz_colors))];
				echo '<TD bgcolor="'.$bgcolor.'">';
				echo $ort->last[$t][$s]->anzahl;
				echo '</TD>';
			}
		echo '</TR>';
	}
	?>
</TABLE>
</body>
</html>
