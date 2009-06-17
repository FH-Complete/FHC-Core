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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');

	// Init Variable
	$user = get_uid();
	$course_id=(isset($_GET['course_id'])?$_GET['course_id']:(isset($_POST['course_id'])?$_POST['course_id']:0));
	$term_id=(isset($_GET['term_id'])?$_GET['term_id']:(isset($_POST['term_id'])?$_POST['term_id']:0));
	$news_id=(isset($_GET['news_id'])?$_GET['news_id']:(isset($_POST['news_id'])?$_POST['news_id']:0));

	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;

	$sql_query = "SELECT DISTINCT kurzbzlang FROM public.tbl_studiengang WHERE studiengang_kz='$course_id'";
	$result = pg_query($sql_conn, $sql_query);
	$row_stg_short = pg_fetch_object($result, 0);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<frameset rows="375,*" cols="*" framespacing="0"" frameborder="NO" border="0">
  <frame src="pinboard_entry.php?course_id=<?php echo $course_id; ?>&term_id=<?php echo $term_id; ?>" name="news_entry">
  <frame src="pinboard_show.php" name="news_window">
</frameset>
<noframes><body id="inhalt">
<table class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
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
				die('Sie haben leider keine Berechtigung f&uuml;r diese Seite.');
		?>
		&nbsp;</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body></noframes>
</html>
