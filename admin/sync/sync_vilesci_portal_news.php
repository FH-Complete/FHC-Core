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

//*
//* Synchronisiert Funktiondatensaetze von Vilesci DB in PORTAL DB
//*
//*

include('../../vilesci/config.inc.php');
include('../../include/fas/news.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_vilesci=pg_connect(CONN_STRING_VILESCI) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

function validate($row)
{
}

/*************************
 * VILESCI-PORTAL - Synchronisation
 */

//news
$qry = "SELECT * FROM tbl_news";

if($result = pg_query($conn_vilesci, $qry))
{
	echo nl2br("News Sync\n-----------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$news = new news($conn);
		$news->news_id		=$row->news_id;
		$news->betreff		=$row->betreff;
		$news->text			=$row->text;
		$news->semester		=$row->semester;
		$news->uid			=$row->uid;
		$news->studiengang_kz	=$row->studiengang_kz;
		$news->verfasser		=$row->verfasser;
		//$news->insertamum	='';
		$news->insertvon		='SYNC';
		$news->updateamum	=$row->updateamum;
		//$news->updatevon		=$row->updatevon;
		
		$qry = "SELECT news_id FROM tbl_news WHERE news_id='$row->news_id'";
			if($result1 = pg_query($conn, $qry))
			{		
				if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($row1=pg_fetch_object($result1))
					{
						//Newsdaten updaten
						$news->new=false;
						$news->news_id=$row->news_id;
					}
					else 
					{
						$error_log.="news_id von $row->news_id konnte nicht ermittelt werden\n";
						$error=true;
					}
				}
				else 
				{
					//News neu anlegen
					$news->new=true;
				}
				
				if(!$error)
				{
					$qryuid = "SELECT uid FROM tbl_benutzer WHERE uid = '$row->uid'";
					if($resultuid = pg_query($conn, $qryuid))
					{
						if (pg_num_rows($resultuid)>0)
						{
							if(!$news->save())
							{
								$error_log.=$news->errormsg."\n";
								$anzahl_fehler++;
							}
							else 
								$anzahl_eingefuegt++;
							}
						else 
						{
							$error_log.="uid von <b>$row->uid</b> konnte nicht in tbl_benutzer gefunden werden\n";
							$anzahl_fehler++;
						}
					}
					else 
					{
						$error_log.="Fehler beim Zugriff auf tbl_benuntzer\n";
						$anzahl_fehler++;	
					}
				}
				else 
					$anzahl_fehler++;
			}	
	}
	echo nl2br("abgeschlossen\n\n");
}
else
	$error_log .= 'Newsdatensaetze konnten nicht geladen werden';
	
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - News</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>