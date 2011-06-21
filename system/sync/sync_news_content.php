<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/content.class.php');

$db = new basis_db();

$qry = 'SELECT * FROM campus.tbl_news';
$gesamt=0;
$fehler=0;
$ok=0;

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$gesamt++;
		$xml = '<news>
			<verfasser><![CDATA['.$row->verfasser.']]></verfasser>
			<betreff><![CDATA['.$row->betreff.']]></betreff>
			<text><![CDATA['.$row->text.']]></text>
			</news>';
		
		$content = new content();
		
		$content->template_kurzbz = 'news';
		$content->oe_kurzbz = 'etw';
		$content->updatevon = $row->updatevon;
		$content->updateamum = $row->updateamum;
		$content->insertamum = $row->insertamum;
		$content->insertvon = $row->insertvon;
		$content->aktiv = true;
		$content->menu_open=false;
		$content->content = $xml;
		$content->sichtbar = true;
		$content->sprache = 'German';
		$content->titel = $row->betreff;
		$content->version = 1;
		if($content->save(true))
		{
			if($content->saveContentSprache(true))
			{
				$qry = "UPDATE campus.tbl_news SET content_id='".$content->content_id."' WHERE news_id='".$row->news_id."';";
				
				if($db->db_query($qry))
				{
					echo "ID $row->news_id angelegt<br>";
					$ok++;
				}
				else
				{
					echo "News Update Failed $row->news_id<br>";
					$fehler++;
				}
			}
			else
			{
				echo "Content Sprache Update Failed $row->news_id<br>";
				$fehler++;
			}
		}
		else
		{
			echo "Content Update Failed $row->news_id<br>";
			$fehler++;
		}
	}
}
else
{
	echo 'Fehler beim Laden der News';
}

echo "
Gesamt: $gesamt<br>
OK: $ok<br>
Fehler: $fehler<br>
";

?>