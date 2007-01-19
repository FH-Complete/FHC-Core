<?php
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');

	if (isset($REMOTE_USER))
		$uid=$REMOTE_USER;
	else
		$uid='tw01e061';

	// Verbindung aufbauen
	$conn=pg_pconnect(CONN_STRING) or die ("Unable to connect to SQL-Server");
	$sql_query="SET search_path TO campus;SELECT uid, nachname, vorname FROM vw_benutzer WHERE uid LIKE '$uid'";
	unset($uid);
	$result=pg_query($conn, $sql_query);
	if(!$result)
		echo "User not found!";
	else
	{
		$uid=pg_result($result,0,'"uid"');
		$nachname=pg_result($result,0,'"nachname"');
		$vornamen=pg_result($result,0,'"vorname"');
	}
	$sql_query="SELECT studiengang_kz, kurzbz, kurzbzlang, bezeichnung, typ FROM public.tbl_studiengang ORDER BY kurzbz";
	$result_stg=pg_query($conn, $sql_query);
	if(!$result_stg)
		die ("Studiengang not found!");
	$num_rows_stg=pg_numrows($result_stg);
	$sql_query="SELECT ort_kurzbz FROM public.tbl_ort WHERE aktiv AND lehre ORDER BY ort_kurzbz";
	$result_ort=pg_query($conn, $sql_query);
	if(!$result_ort)
		die("ort not found!");
	$num_rows_ort=pg_numrows($result_ort);
	$sql_query="SELECT uid, kurzbz FROM vw_mitarbeiter ORDER BY kurzbz";
	$result_lektor=pg_query($conn, $sql_query);
	if(!$result_lektor)
		die("lektor not found!");
	$num_rows_lektor=pg_numrows($result_lektor);
?>
<html>
<head>
<title>Lehrveranstaltungsplan</title>
<script language="JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<BODY>
	<H1>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>&nbsp;Lehrveranstaltungsplan</td>
			<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
	</table>
	</H1>
	<DIV align="right">Version: <?php echo VERSION; ?></DIV>
	<font class="beschriftung">Username: </font>
	<?php
		if (isset($uid))
			echo $uid;
		else
			echo 'nicht vorhanden! Bitte wenden Sie sich an den <A href="mailto:vilesci@technikum-wien.at">Admin</A>!';
	?><BR>
  	<DIV align="left">
  		<a href="stpl_week.php?pers_uid=<?php echo $uid; ?>"><?php echo $vornamen." ".$nachname;?></a>
		&nbsp; -> Ihr pers&ouml;nlicher Lehrveranstaltungsplan<BR>
		<a href="../profile/index.php">PROFIL</a>
		&nbsp; -> Hier k&ouml;nnen Sie Ihre Stammdaten kontrollieren.<BR>
	</DIV>
	<BR>
	<FORM name="Auswahl" action="stpl_week.php">
		<table width="100%" border="0" cellpadding="0" cellspacing="3">
		<tr>
			<td width="50%">
				<H2>&nbsp;Saalplan</H2>
			</td>
			<td width="50%">
				<H2>&nbsp;Lektorenplan</H2>
			</td>
		</tr>
		<tr>
			<td>
			<BR>
			<font class="beschriftung">Saal: </font>
			<select name="select" onChange="MM_jumpMenu('self',this,0)">
        		<option value="stpl_wekk.php" selected>... ??? ...</option>
        	  	<?php
				for ($i=0;$i<$num_rows_ort;$i++)
				{
					$row=pg_fetch_object ($result_ort, $i);
					echo "<option value=\"stpl_week.php?type=ort&ort_kurzbz=$row->ort_kurzbz\">$row->ort_kurzbz</option>";
				}
				?>
			</select>
			(Saalreservierung)<BR><BR>
			<A href="stpl_reserve_list.php">Reservierungsliste</A> (Reservierungen l&ouml;schen)<BR>
			</td>

			<td>
			<font class="beschriftung">Lektor: </font>
	  		<select name="lektor" onChange="MM_jumpMenu('self',this,0)">
			    	<option value="stpl_week.php" selected>... ??? ...</option>
			    	<?php
				for ($i=0;$i<$num_rows_lektor;$i++)
				{
					$row=pg_fetch_object ($result_lektor, $i);
					echo "<option value=\"stpl_week.php?type=lektor&pers_uid=$row->uid\">$row->kurzbz</option>";
				}
				?>
			</select>
			</td>
		</tr>
		</table>

		<H2>&nbsp;Lehr-Verband</H2>
		<table width="40%" border="0" cellpadding="0" cellspacing="3">
		<tr nowrap>
		<td width="20%" valign="middle">
			<font class="beschriftung"> Studiengang</font><BR>
			<select name="stg_kz" >
				<?php
				$num_rows=pg_numrows($result_stg);
				for ($i=0;$i<$num_rows;$i++)
				{
					$row=pg_fetch_object ($result_stg, $i);
					echo '<option value="'.$row->studiengang_kz.'">'.strtoupper($row->typ.$row->kurzbz)." ( $row->kurzbzlang - $row->bezeichnung )</option>";
				}
				?>
			</select>
		</td>
		<td valign="middle">
			<font class="beschriftung"> Sem</font><BR>
			<select name="sem">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			</select>
		</td>
		<td valign="middle">
			<font class="beschriftung"> Ver</font><BR>
			<select name="ver" >
			<option value="0" selected>*</option>
			<option value="A">A</option>
			<option value="B">B</option>
			<option value="C">C</option>
			<option value="D">D</option>
			</select>
		</td>
		<td valign="middle" >
			<font class="beschriftung"> Grp</font><BR>
			<select name="grp">
			<option value="0" selected>*</option>
			<option value="1">1</option>
			<option value="2">2</option>
			</select>
		</td>
		<TD valign="bottom">
			<INPUT type="hidden" name="type" value="verband">
			<INPUT type="submit" name="Abschicken" value="Go">
		</TD>
		</tr>
		</table>
	</form>
	<a href="verband_uebersicht.php">Lehrverb&auml;nde</a> -> &Uuml;bersicht der Lehrverb&auml;nde<BR>
<BR><BR><HR>
<P>Fehler und Feedback bitte an <A href="mailto:lvplan@technikum-wien.at">LV-Koordinationsstelle</A>.</P>
<!--
<P class=little>
    Erstellt am 24.8.2001 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.<BR>
    Letzte &Auml;nderung am 11.1.2005 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.
</P>
-->
</body>
</html>
