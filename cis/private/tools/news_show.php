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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript">

	function deleteEntry(id)
	{
		if(confirm("Soll dieser Eintrag wirklich gelöscht werden?") == true)
		{
			document.location.href = 'news_show.php?remove_id=' + id;
		}
	}

	function editEntry(id)
	{
		parent.news_entry.location.href = 'news_entry.php?news_id=' + id;
	}
</script>
</head>

<body>
<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once('../../../include/news.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');

	$user = get_uid();

    $rechte = new benutzerberechtigung($sql_conn);
    $rechte->getBerechtigungen($user);

	if(check_lektor($user,$sql_conn))
       $is_lector=true;

	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz') || $rechte->isBerechtigt('news') || $rechte->isBerechtigt('lehre'))
		$berechtigt=true;
	else
		$berechtigt=false;

	if($berechtigt)
	{
		if(isset($remove_id) && $remove_id != "")
		{
			$news = new news($sql_conn);
			if($news->delete($remove_id))
			{
				writeCISlog('DELETE NEWS','');
				echo '<script language="JavaScript">';
				echo '	document.location.href = "news_show.php"';
				echo '</script>';
				exit;
			}
			else
				echo 'Fehler beim L&ouml;schen des Eintrages';
		}
	}
?>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
        <tr>
	  	  <?php
		  	if(!$berechtigt)
				exit;
		  ?>
          <td>
		  	<table class="tabcontent">
			  <?php

				$news = new news($sql_conn);
				$news->getnews(0,0,null, true, '*', 0);

				$zaehler=0;
				$i=0;
				foreach($news->result as $row)
				{
					$i++;
					$zaehler++;
					$datum = date('d.m.Y',strtotime(strftime($row->datum)));

					echo "<tr>";

					if($i % 2 != 0)
					{
						echo '<td class="MarkLine">';
					}
					else
					{
						echo '<td>';
					}

					echo '  <table class="tabcontent">';
					echo '    <tr>';
					echo '      <td nowarp>';
					echo $datum.'&nbsp;'.$row->verfasser;
					echo '      </td>';
					echo '		<td align="right" nowrap>';
					echo '		  <a onClick="editEntry('.$row->news_id.');">Editieren</a>, <a onClick="deleteEntry('.$row->news_id.');">L&ouml;schen</a>';
					echo '		</td>';
					echo '    </tr>';
					echo '	  <tr>';
					echo '		<td>&nbsp;</td>';
					echo '	  </tr>';
					echo '  </table>';
					echo '  <strong>'.$row->betreff.'</strong><br>'.$row->text.'</td>';
					echo '</tr>';
					echo '<tr>';
					echo '  <td>&nbsp;</td>';
					echo '</tr>';
					echo '<tr>';
					echo '  <td>&nbsp;</td>';
					echo '</tr>';

				}
				if($zaehler==0)
					echo 'Zur Zeit gibt es keine aktuellen News!';
			  ?>
			</table>
		  </td>
        </tr>
    </table></td>
    <td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
