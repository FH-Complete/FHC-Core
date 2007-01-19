<?php
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');

	/*
	if (isset($REMOTE_USER))
		$uid=$REMOTE_USER;
	else
		$uid='pam';
	*/
	$uid = get_uid();

	if(isset($_GET['type']))
		$type=$_GET['type'];

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Stundentabelleholen
	if(! $result_stunde=pg_exec($conn, "SET search_path TO campus; SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
		die(pg_last_error($conn));
	$num_rows_stunde=pg_numrows($result_stunde);

	// Zeitwuensche speichern
	if (isset($type) && $type=='save')
	{
		for ($t=1;$t<7;$t++)
			for ($i=0;$i<$num_rows_stunde;$i++)
			{
				$var='wunsch'.$t.'_'.$i;
				//echo $$var;
				$gewicht=$_POST[$var];
				$stunde=$i+1;
				$query="SELECT * FROM tbl_zeitwunsch WHERE mitarbeiter_uid='$uid' AND stunde=$stunde AND tag=$t";
				if(! $erg_wunsch=pg_query($conn, $query))
					die(pg_last_error($conn));
				$num_rows_wunsch=pg_num_rows($erg_wunsch);
				if ($num_rows_wunsch==0)
				{
					$query="INSERT INTO tbl_zeitwunsch (uid, stunde, tag, gewicht) VALUES ('$uid', $stunde, $t, $gewicht)";
					if(!($erg=pg_exec($conn, $query)))
						die(pg_last_error($conn));
				}
				elseif ($num_rows_wunsch==1)
				{
					$query="UPDATE tbl_zeitwunsch SET gewicht=$gewicht WHERE mitarbeiter_uid='$uid' AND stunde=$stunde AND tag=$t";
					//echo $query;
					if(!($erg=pg_exec($conn, $query)))
						die(pg_last_error($conn));
				}
				else
					die("Zuviele Eintraege fuer!");
			}
	}

	if(!($erg=pg_query($conn, "SELECT * FROM tbl_zeitwunsch WHERE mitarbeiter_uid='$uid'")))
		die(pg_last_error($conn));
	$num_rows=pg_numrows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$tag=pg_result($erg,$i,"tag");
		$stunde=pg_result($erg,$i,"stunde");
		$gewicht=pg_result($erg,$i,"gewicht");
		$wunsch[$tag][$stunde]=$gewicht;
	}



	// Personendaten
	if(! $result=pg_exec($conn, "SELECT * FROM vw_benutzer WHERE uid='$uid'"))
		die(pg_last_error($conn));
	if (pg_numrows($result)==1)
		$person=pg_fetch_object($result);

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>

<body>
<H2><table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td>
		&nbsp;<a href="index.php">Userprofil</a> &gt;&gt;
		&nbsp;Zeitw&uuml;nsche
	</td>
	<td align="right"><A href="../lvplan/help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
	</tr>
	</table>
</H2>
<div align="right">Results: <?php echo $num_rows; ?> - <?php echo $uid; ?></div>
<H3>
	Zeitw&uuml;nsche von <?php echo $person->titelpre.' '.$person->vorname.' '.$person->nachname; ?>
</H3>
<FORM name="zeitwunsch" method="post" action="zeitwunsch.php?type=save">
  <TABLE>
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
			if (isset($wunsch[$j][$i+1]))
				$index=$wunsch[$j][$i+1];
			else
				$index=1;
			$id='bgcolor';
			$id.=$index+3;
			echo '<TD align="center" id="'.$id.'"><INPUT align="right" type="text" name="wunsch'.$j.'_'.$i.'" size="1" maxlength="2" value="'.$index.'"></TD>';
		}
		echo '</TR>';
	}
	?>
  </TABLE>
  <INPUT type="hidden" name="uid" value="<?php echo $uid; ?>">
  <INPUT type="submit" name="Abschicken" value="Speichern">
</FORM>
<br>
<hr>
<H3>Erkl&auml;rung:</H3>
<P>Bitte kontrollieren/&auml;ndern Sie Ihre Zeitw&uuml;nsche und klicken Sie anschlie&szlig;end
  auf &quot;Speichern&quot;!<BR><BR>
</P>
<TABLE width="50%" align="center" name="Zeitwerte">
  <TR>
    <TH><B>Wert</B></TH>
    <TH>
      <DIV align="center"><B>Bedeutung</B></DIV>
    </TH>
  </TR>
  <TR>
    <TD>
      <DIV align="right">2</DIV>
    </TD>
    <TD>Hier m&ouml;chte ich unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">1</DIV>
    </TD>
    <TD>Hier kann ich unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">0</DIV>
    </TD>
    <TD>keine Bedeutung</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-1</DIV>
    </TD>
    <TD>Hier nur in extremen Notf&auml;llen</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-2</DIV>
    </TD>
    <TD>Hier auf gar keinen Fall !!!</TD>
  </TR>
</TABLE>
<P>&nbsp;</P>
<H3>Folgende Punkte sind zu beachten:</H3>
<OL>
  <LI> Verwenden Sie den Wert -2 nur, wenn Sie zu dieser Stunde wirklich nicht
    k&ouml;nnen, um eine bessere Optimierung zu erm&ouml;glichen.</LI>
  <LI>Es sollten f&uuml;r jede Stunde die tats&auml;chlich unterrichtet wird,
    mindestens das 3-fache an positiven Zeitw&uuml;nschen angegeben werden.<BR>
    Beispiel: Sie unterrichten 4 Stunden/Woche, dann sollten Sie mindestens
    12 Stunden im Raster mit positiven Werten ausf&uuml;llen.</LI>
</OL>
<P>Bei Problemen wenden Sie sich bitte an die <A href="mailto:lvplan@technikum-wien.at">LV-Koordinationsstelle</A>.</P>
<P>&nbsp;</P>
</body>
</html>
