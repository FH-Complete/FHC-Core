<?php
	include("../../../include/functions.inc.php");
    include("../../config.inc.php");
    writeCISlog('START');
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
    {
       writeCISlog('STOP');
       die("Fehler beim öffnen der Datenbankverbindung");
    }
        
	$user = get_uid();
	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
	
	$sql_query = "SELECT DISTINCT kurzbzlang FROM public.tbl_studiengang WHERE studiengang_kz='$course_id'";
					
	$result = pg_exec($sql_conn, $sql_query);
	$row_stg_short = pg_fetch_object($result, 0);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<frameset rows="375,*" cols="*" framespacing="0"" frameborder="NO" border="0">
  <frame src="pinboard_entry.php?course_id=<?php echo $course_id; ?>&term_id=<?php echo $term_id; ?>" name="news_entry">
  <frame src="pinboard_show.php" name="news_window">
</frameset>
<noframes><body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Lektorenbereich - Pinboardverwaltung <?php echo $row_stg_short->kurzbzlang.', '.$term_id.'. Semester'; ?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>
		<?php	
			if(!$is_lector)
			{
				writeCISlog('STOP');
				die("Sie haben leider keine Berechtigung f&uuml;r diese Seite.");
			}
			writeCISlog('STOP');
		?>
		&nbsp;</td>
	  </tr>
    </table></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body></noframes>
</html>
