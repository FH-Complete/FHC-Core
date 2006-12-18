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
//* Synchronisiert Adressendatensaetze von Vilesci DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/adresse.class.php');
include('../../../include/firma.class.php');

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

//nation
$qry = "SELECT * FROM adresse";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Adresse Sync\n--------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$adresse				=new adresse($conn);
		$adresse->name			=$row->name;
		$adresse->strasse			=$row->strasse;
		$adresse->plz			=$row->plz;
		$adresse->ort			=$row->ort;
		$adresse->gemeinde		=$row->gemeinde;
		$adresse->nation			=$row->nation;
		$adresse->typ			=$row->typ;
		$adresse->heimatadresse		=$row->bismeldeadresse=='J'?true:false;
		$adresse->zustelladresse		=$row->zustelladresse=='J'?true:false;
		$adresse->firma_id			=null;
		//$adresse->updateamum		=$row->;
		$adresse->updatevon		="SYNC";
		//$adresse->insertamum		=$row->;
		$adresse->insertvon			="SYNC";
		$adresse->ext_id			=$row->adresse_pk;

		//person_id herausfinden
		$qry1="SELECT person_portal FROM public.tbl_syncperson WHERE person_fas=".$row->adresse_pk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$adresse->person_id=$row1->person_portal;
					$adresse->new=true;
					
					//firma eintragen
					if ($row->typ==1)
					{
						$anzahl_quelle2++;
						$firma=new firma($conn);
						$firma->name=$row->bezeichnung;
						$firma->anmerkung=null;
						$firma->ext_id=$row->adresse_pk;
						$qry2="SELECT firma_id, ext_id FROM tbl_firma WHERE ext_id=".$row->adresse_pk.";";
						if($result2 = pg_query($conn, $qry2))
						{
							if(pg_num_rows($result2)>0) //eintrag gefunden
							{
								if($row2=pg_fetch_object($result2))
								{
									$firma->new=false;	
									$firma->firma_id=$row2->firma_id;	
								}
								else 
								{
									$error=true;
									$error_log.="firma mit adresse_pk: $row->adresse_pk konnte nicht ermittelt werden!\n";
								}
							}
							else
							{
								$firma->new=true;
							}
						} 
						if(!$error)
						{
							if(!$firma->save())
							{
								$error_log.=$firma->errormsg."\n";
								$anzahl_fehler2++;
							}
							else 
							{
								$anzahl_eingefuegt2++;
							}											
							$adresse->firma_id=$firma->firma_id;
						}
					}
				}
				else 
				{
					$error=true;
					$error_log.="adresse mit adresse_pk: ".$row->adresse_pk." konnte nicht ermittelt werden! (".pg_num_rows($result1).")\n";
					$anzahl_fehler++;
				}
			}
			else 
			{
				$error=true;
				$error_log.="adresse mit adresse_pk: $row->adresse_pk konnte nicht gefunden werden! (".pg_num_rows($result1).")\n";
				$anzahl_fehler++;
			}
		}
		
		if(!$error)
		{
			if(!$adresse->save())
			{
				$error_log.=$adresse->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
			{
				$anzahl_eingefuegt++;
			}
		}
	}		
}

?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Adresse</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nAdresse\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
echo nl2br("\nFirma\nGesamt: $anzahl_quelle2 / Eingefügt: $anzahl_eingefuegt2 / Fehler: $anzahl_fehler2");

?>
</body>
</html>