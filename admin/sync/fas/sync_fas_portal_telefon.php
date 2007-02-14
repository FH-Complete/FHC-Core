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
//* Synchronisiert Telefondatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/kontakt.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

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
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Telefon</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */

//nation
$qry = "SELECT * FROM telefonnummer ORDER BY person_fk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Telefon Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();	
			
		$error=false;
		$kontakt				=new kontakt($conn);
		$kontakt->firma_id			='';
		If($row->typ<'20')
		{
			$kontakt->kontakttyp		='telefon';
		}
		elseif($row->typ>='20' && $row->typ<'30')
		{
			$kontakt->kontakttyp		='mobil';
		}
		elseif($row->typ>='30' && $row->typ<'40')
		{
			$kontakt->kontakttyp		='fax';
		}
		else
		{
			$kontakt->kontakttyp		='so.tel';
		}		
		$kontakt->anmerkung		=$row->name;
		$kontakt->kontakt			=$row->nummer;
		$kontakt->zustellung			=false;
		$kontakt->updatevon		="SYNC";
		$kontakt->insertvon			="SYNC";
		$kontakt->ext_id			=$row->telefonnummer_pk;
		
		//Person_id feststellen
		if($row->nummer!='')
		{
			$qry1="SELECT person_portal FROM public.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
			if($result1 = pg_query($conn, $qry1))
			{
				if(pg_num_rows($result1)>0) //eintrag gefunden
				{
					if($row1=pg_fetch_object($result1))
					{ 
						$qry2="SELECT kontakt_id, ext_id FROM tbl_kontakt WHERE ext_id=".$row->telefonnummer_pk." AND kontakttyp='telefon';";
						if($result2 = pg_query($conn, $qry2))
						{
							if(pg_num_rows($result2)>0) //eintrag gefunden
							{
								if($row2=pg_fetch_object($result2))
								{ 
									// update , wenn datensatz bereits vorhanden
									$kontakt->person_id=$row1->person_portal;
									$kontakt->kontakt_id=$row2->kontakt_id;
									$kontakt->new=false;
								}
							}
							else 
							{
								// insert, wenn datensatz noch nicht vorhanden
								$kontakt->new=true;
								$kontakt->person_id=$row1->person_portal;
							}
						}
					}
				}
				else 
				{
					$error=true;
					$error_log.="person mit person_fk: $row->person_fk konnte in tbl_syncperson nicht gefunden werden! (".pg_num_rows($result1).")\n";
					$anzahl_fehler++;
				}
			}
			If (!$error)
			{
				if(!$kontakt->save())
				{
					$error_log.=$kontakt->errormsg."\n";
					$anzahl_fehler++;
				}
				else 
				{
					$anzahl_eingefuegt++;
				}
			}
		}
	}		
}



//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
$error_log.="\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler";
mail($adress, 'SYNC Telefon', $error_log);
?>
</body>
</html>