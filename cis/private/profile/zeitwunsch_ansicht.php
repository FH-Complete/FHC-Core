<?php
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');

if (isset($REMOTE_USER))
	$uid=$REMOTE_USER;
else
	$uid='pam';

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($erg=pg_exec($conn, "SELECT * FROM zeitwunsch WHERE zeitwunsch.lektor_id=$lkid")))
		die(pg_last_error($conn));
	$num_rows=pg_numrows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$tag=pg_result($erg,$i,"tag");
		$stunde=pg_result($erg,$i,"stunde_id");
		$gewicht=pg_result($erg,$i,"gewicht");
		$wunsch[$tag][$stunde]=$gewicht;
	}
	if(!($erg_std=pg_exec($conn, "SELECT * FROM stunde ORDER BY id")))
		die(pg_last_error($conn));
	$num_rows_std=pg_numrows($erg_std);
	for ($i=0;$i<$num_rows_std;$i++)
	{
		$beginn[$i]=pg_result($erg_std,$i,"beginn");
		$ende[$i]=pg_result($erg_std,$i,"ende");
	}

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/style.css.php" type="text/css">
</head>

<body id="inhalt">
<h4>Zeitw&uuml;nsche von <?php echo $titel.' '.$vornamen.' '.$nachname; ?></h4>
Results: <?php echo $num_rows; ?><br>
Username: <?php echo $uid; ?><br>
<TABLE width="100%" border="1">
    <TR>
    <TD align="center"><B>Stunde</B><BR><SMALL>Beginn<BR>Ende</SMALL></TD>
	<?php
	  	for ($i=1;$i<=$num_rows_std;$i++)
			echo '<TD align="center"><B>'.$i.'</B><BR><SMALL>'.$beginn[$i-1].'<BR>'.$ende[$i-1].'</SMALL></TD>';
	?>
    </TR>
	<?php
	for ($j=1; $j<7; $j++)
	{
		echo '<TR><TD>'.$tagbez[$j].'</TD>';
	  	for ($i=0;$i<$num_rows_std;$i++)
		{
			$index=$wunsch[$j][$i+1];
			if ($index=="")
				$index=1;
			$bgcolor=$cfgStdBgcolor[$index+3];
			echo '<TD align="center" bgcolor="'.$bgcolor.'">'.$index.'</TD>';
		}
		echo '</TR>';
	}
	?>
  </TABLE>
  <INPUT type="hidden" name="lkid" value="<?php echo $lkid; ?>">
  <INPUT type="hidden" name="titel" value="<?php echo $titel; ?>">
  <INPUT type="hidden" name="vornamen" value="<?php echo $vornamen; ?>">
  <INPUT type="hidden" name="nachname" value="<?php echo $nachname; ?>">
<br>
<hr>
<H3>Ekl&auml;rung:</H3>
<P>Bitte kontrollieren Sie Ihre Zeitw&uuml;nsche, &auml;nderungen per Mail bitte
  an <A class="Item" href="mailto:<?php echo MAIL_LVPLAN;?>">Stundenplan</A>!<BR>
  <BR>
</P>
<TABLE width="35%" border="1" cellspacing="0" name="Zeitwerte">
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
    <TD>Hier m&ouml;chte ich Unterrichen</TD>
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
  <LI>Es m&uuml;ssen f&uuml;r jede Stunde die tats&auml;chlich unterrichtet wird,
    mindestens das 1,5 fache an positiven Zeitw&uuml;nschen angegeben werden.<BR>
    Beispiel: Sie unterrichten 4Stunden/Woche, dann m&uuml;ssen Sie mindesten
    6 Stunden im Raster mit positiven Werten ausf&uuml;llen.</LI>
</OL>
<P>Bei Problemen wenden Sie sich bitte an die <A class="Item" href="mailto:<?php echo MAIL_LVPLAN;?>">Stundenplanstelle</A>.</P>
<P>&nbsp;</P>
</body>
</html>
