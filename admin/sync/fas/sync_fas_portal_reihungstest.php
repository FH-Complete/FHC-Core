<?php
/* Copyright (C) 2007 Technikum-Wien
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
//* Synchronisiert Reihungstestdatensaetze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../../../include/reihungstest.class.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_fehler2=0;

function validate($row)
{
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Reihungstest</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM reihungstest ORDER BY datum;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Reihungstest Sync\n-------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$reihungstest				=new reihungstest($conn);
		$studiengang_kz			='';
		$reihungstest->ort_kurzbz		='';
		$reihungstest->anmerkung		=$row->raum;
		$reihungstest->datum		=$row->datum;
		$reihungstest->uhrzeit		=$row->uhrzeit;
		//$reihungstest->updateamum	=$row->;
		$reihungstest->updatevon		="SYNC";
		//$reihungstest->insertamum	=$row->;
		$reihungstest->insertvon		="SYNC";
		$reihungstest->ext_id		=$row->reihungstest_pk;
	
		
		//echo nl2br ($reihungstest->ext_id."\n");

		$qry2="SELECT reihungstest_id, ext_id FROM tbl_reihungstest WHERE ext_id=".$row->reihungstest_pk.";";
		if($result2 = pg_query($conn, $qry2))
		{
			if(pg_num_rows($result2)>0) //eintrag gefunden
			{
				if($row2=pg_fetch_object($result2))
				{ 
					// update adresse, wenn datensatz bereits vorhanden
					$reihungstest->new=false;
					$reihungstest->reihungstest_id=$row2->reihungstest_id;
				}
			}
			else 
			{
				// insert, wenn datensatz noch nicht vorhanden
				$reihungstest->new=true;	
			}
		}
				
		
		if(!$error)
		{
			if(!$reihungstest->save())
			{
				$error_log.=$reihungstest->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
			{
				$anzahl_eingefuegt++;
				echo "- ";
				ob_flush();
				flush();
			}
		}
		flush();	
	}	
}


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>