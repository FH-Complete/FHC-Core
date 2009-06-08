<?php
	/**
	 *	kopiert von stdplan/profile/zeitwuensche.php mit dem Unterschied,
	 *  dass der User hier parametrisiert ist + Speichern läuft hier über
	 *  POST statt GET - ist aber Geschmacksache
	 *
	 */

	include('../config.inc.php');
	include('../../include/functions.inc.php');
	include('../../include/globals.inc.php');

	if (isset($_GET['uid']))
	{
		$uid=$_GET['uid'];
	} 
	else if (isset($_POST['uid']))
	{
		$uid=$_POST['uid'];
	}
	if (!isset($uid))
	{
		die( "uid nicht gesetzt");
	}

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Stundentabelleholen
	if(! $result_stunde=pg_exec($conn, "SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
		die(pg_last_error($conn));
	$num_rows_stunde=pg_numrows($result_stunde);

	// Zeitwuensche speichern
	if (isset($_POST['save']))
	{
		for ($t=1;$t<7;$t++)
			for ($i=0;$i<$num_rows_stunde;$i++)
			{
				$var='wunsch'.$t.'_'.$i;
				//echo $$var;
				$gewicht=$_POST[$var];
				$stunde=$i+1;
				$query="SELECT * FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid='$uid' AND stunde=$stunde AND tag=$t";
				if(! $erg_wunsch=pg_exec($conn, $query))
					die(pg_last_error($conn));
				$num_rows_wunsch=pg_num_rows($erg_wunsch);
				if ($num_rows_wunsch==0)
				{
					$query="INSERT INTO campus.tbl_zeitwunsch (mitarbeiter_uid, stunde, tag, gewicht) VALUES ('$uid', $stunde, $t, $gewicht)";
					if(!($erg=pg_exec($conn, $query)))
						die(pg_last_error($conn));
				}
				elseif ($num_rows_wunsch==1)
				{
					$query="UPDATE campus.tbl_zeitwunsch SET gewicht=$gewicht WHERE mitarbeiter_uid='$uid' AND stunde=$stunde AND tag=$t";
					//echo $query;
					if(!($erg=pg_exec($conn, $query)))
						die(pg_last_error($conn));
				}
				else
					die("Zuviele Eintraege!");
			}
	}

	if(!($erg=pg_exec($conn, "SELECT * FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid='$uid'")))
		die(pg_last_error($conn));
	$num_rows=pg_numrows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$tag=pg_result($erg,$i,"tag");
		$stunde=pg_result($erg,$i,"stunde");
		$gewicht=pg_result($erg,$i,"gewicht");
		$wunsch[$tag][$stunde]=$gewicht;
	}
	if(!isset($wunsch))
	{
		//6-16
		for ($i=1;$i<7;$i++)
		{
			for ($j=0;$j<17;$j++)
			{
				$wunsch[$i][$j]='1';
			}
		}
	}


	// Personendaten
	if(! $result=pg_exec($conn, "SELECT * FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid='$uid'"))
		die(pg_last_error($conn));
	if (pg_numrows($result)==1)
		$person=pg_fetch_object($result);

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<h2>Zeitw&uuml;nsche von <?php echo $person->titelpre.' '.$person->vornamen.' '.$person->nachname. ' '.$person->titelpost; ?></h2>

<FORM name="zeitwunsch" method="post" action="zeitwunsch.php?type=save">
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
			$index=$wunsch[$j][$i+1];
			if ($index=="")
				$index=1;
			$bgcolor=$cfgStdBgcolor[$index+3];
			echo '<TD align="center" bgcolor="'.$bgcolor.'"><INPUT align="right" type="text" name="wunsch'.$j.'_'.$i.'" size="2" maxlength="2" value="'.$index.'"></TD>';
		}
		echo '</TR>';
	}
	?>
  </TABLE>
  <br/>
  <INPUT type="hidden" name="uid" value="<?php echo $uid; ?>">
  <INPUT type="submit" name="save" value="Speichern">
</FORM>
<br>
<hr>
<H3>Erkl&auml;rung:</H3>
<P>Bitte kontrollieren/&auml;ndern Sie Ihre Zeitw&uuml;nsche und klicken Sie anschlie&szlig;end
  auf &quot;Speichern&quot;!<BR>
  <BR>
</P>
<TABLE width="50%" border="1" cellspacing="0" cellpadding="0" name="Zeitwerte">
  <TR>
    <TD><B>Wert</B></TD>
    <TD>
      <DIV align="center"><B>Bedeutung</B></DIV>
    </TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">2</DIV>
    </TD>
    <TD>Hier m&ouml;chte ich Unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">1</DIV>
    </TD>
    <TD>Hier kann ich Unterrichten</TD>
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
    <TD>Hier m&ouml;chte ich eher nicht</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-2</DIV>
    </TD>
    <TD>Hier nur in extremen Notf&auml;llen</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-3</DIV>
    </TD>
    <TD>Hier auf gar keinen Fall !!!</TD>
  </TR>
</TABLE>
<P>&nbsp;</P>
<H3>Folgende Punkte sind zu beachten:</H3>
<OL>
  <LI> Verwenden Sie den Wert -3 nur wenn Sie zu dieser Stunde wirklich nicht
    k&ouml;nnen, um eine bessere Optimierung zu erm&ouml;glichen.</LI>
  <LI>Es mu&szlig; f&uuml;r jede Stunde die tats&auml;chlich unterrichtet wird,
    mindestens das 2-fache an positiven Zeitw&uuml;nschen angegeben werden.<BR>
    Beispiel: Sie unterrichten 4 Stunden/Woche, dann m&uuml;ssen Sie mindesten
    8 Stunden im Raster mit positiven Werten ausf&uuml;llen.</LI>
</OL>
<P>Bei Problemen wenden Sie sich bitte an die <A href="mailto:stpl@technikum-wien.at">Stundenplanstelle</A>.</P>
<P>&nbsp;</P>
</body>
</html>
