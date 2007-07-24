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
	require_once('../../../include/news.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die("Fehler beim oeffnen der Datenbankverbindung");


	function print_news($sql_conn)
	{
		$news_obj = new news($sql_conn);
		$news_obj->getnews(MAXNEWSALTER,'0','0');

		$zaehler=0;
		echo "<table>";
		foreach ($news_obj->result as $row)
		{
			$zaehler++;
			if($row->datum!='')
				$datum = date('d.m.Y',strtotime(strftime($row->datum)));
			else
				$datum='';

			if($row->semester == 0)
			{
				echo '<tr><td class="ContentHeader2"><p><small>'.$datum.' - '.$row->verfasser.' - [Allgemein]</small><br><b>'.$row->betreff.'</b><br></td></tr>';
			}
			else
			{
				echo '<tr><td class="ContentHeader2"><p><small>'.$datum.' - '.$row->verfasser.' - </small><br><b>'.$row->betreff.'</b><br></td></tr>';
			}

			echo "<tr><td class='MarkLine'>".str_replace("../../skin","../../../skin","$row->text")."</p></td></tr>";
		}
		echo "</table>";
		if($zaehler==0)
			echo '<p>Zur Zeit gibt es keine aktuellen News!</p>';
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader" width="70%"><font class="ContentHeader">&nbsp;Pinboard</font></td>
      </tr>
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td valign="top"><?php print_news($sql_conn); ?></td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>