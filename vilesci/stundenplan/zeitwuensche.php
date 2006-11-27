<?php
	/**
	 *	Statistik der Zeitwuensche
	 * 
	 */

	include('../config.inc.php');
	
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	   	
	//Stundentabelleholen
	if(! $result_stunde=pg_exec($conn, "SELECT * FROM tbl_stunde ORDER BY stunde"))
		die(pg_last_error($conn));
	$num_rows_stunde=pg_numrows($result_stunde);
		
	if(!($erg=pg_exec($conn, "SELECT DISTINCT uid FROM tbl_zeitwunsch")))
		die(pg_last_error($conn));
	$anz_lektoren=pg_numrows($erg);
	
	if(!($erg=pg_exec($conn, "SELECT tag,stunde,gewicht+3 AS gewicht, count(*) AS anz FROM tbl_zeitwunsch GROUP BY tag,stunde,gewicht;")))
		die(pg_last_error($conn));
	$num_rows=pg_numrows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($erg,$i);
		$wunsch[$row->tag][$row->stunde][$row->gewicht]=$row->anz;
	}
	
?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<h2> Statistik der Zeitw&uuml;nsche</h2>
Anzahl der Lektoren: <?PHP echo $anz_lektoren; ?>
<TABLE width="100%" border="1" cellspacing="0" cellpadding="0">
	<TR>
    <?php
	echo '<th>Stunde<br>Beginn<br>Ende</th>';
	for ($i=0;$i<$num_rows_stunde; $i++)
	{
		$beginn=pg_result($result_stunde,$i,'"beginn"');
		$beginn=substr($beginn,0,5);
		$ende=pg_result($result_stunde,$i,'"ende"');
		$ende=substr($ende,0,5);
		$stunde=pg_result($result_stunde,$i,'"stunde"');
		echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
	}
	?>
    </TR>
	<?php
	for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[$j].'</TD>';
	  	for ($i=0;$i<$num_rows_stunde;$i++)
		{
			$pos=$wunsch[$j][$i+1][4]+$wunsch[$j][$i+1][5];
			$neg=$wunsch[$j][$i+1][3]+$wunsch[$j][$i+1][2]+$wunsch[$j][$i+1][1]+$wunsch[$j][$i+1][0];
			$bgcolor=$cfgStdBgcolor[round(14/$anz_lektoren*$pos)-4];
			echo '<TD bgcolor="'.$bgcolor.'">';
			echo '+:'.round(100/$anz_lektoren*$pos).'%<BR>';
			echo '-:'.round(100/$anz_lektoren*$neg).'%';
			echo '</TD>';
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
		$beginn=pg_result($result_stunde,$i,'"beginn"');
		$beginn=substr($beginn,0,5);
		$ende=pg_result($result_stunde,$i,'"ende"');
		$ende=substr($ende,0,5);
		$stunde=pg_result($result_stunde,$i,'"stunde"');
		echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
	}
	?>
    </TR>
	<?php
	for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[$j].'</TD>';
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
