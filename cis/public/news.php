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
    require_once('../config.inc.php');
    require_once('../../include/news.class.php');

    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die("Fehler beim öffnen der Datenbankverbindung");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table id="inhalt" class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;News</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  	<td>
	  	<div id="news">
		  <?php

		  	$news = new news($conn);

		  	$news->getnews(MAXNEWSALTER,0,null);
		  	$zaehler=0;
		  	$open=true;
		  	foreach ($news->result as $row)
		  	{
		  		$zaehler++;
		  		//no comment
		  		$datum = date('d.m.Y',strtotime(strftime($row->datum)));

				//echo $datum.'&nbsp;'.$row->verfasser.'<br><br><strong>'.$row->betreff.'</strong><br>'.$row->text.'<br><br><br>
				echo '<div class="news">';
				echo '
					<div class="titel">
					<table width="100%">
						<tr>
							<td width="60%" align="left">'.$row->betreff.'</td>
							<!--<td width="30%" align="center"></td>-->
							<td width="30%" align="right" style="display: '.($open?'none':'block').'" id="'.$zaehler.'Mehr" ><a href="#" class="Item" onclick="return show(\''.$zaehler.'\')">mehr &gt;&gt;</a></td>
							<td width="30%" align="right" style="display: '.($open?'block':'none').'" id="'.$zaehler.'Verfasser">'.$row->verfasser.' <span style="font-weight: normal">( '.$datum.' )</span></td>
						</tr>
					</table>
					</div>
					<div class="text" style="display: '.($open?'block':'none').';" id="'.$zaehler.'Text">
					'.$row->text.'
					</div>
					';
				echo "</div><br />";
			}


			if($zaehler==0)
				echo 'Zur Zeit gibt es keine aktuellen News!';
		  ?>
		  </div>
		</td>
		<td>&nbsp;</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
