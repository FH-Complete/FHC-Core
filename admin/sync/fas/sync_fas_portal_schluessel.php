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
//* Synchronisiert Schlüsseldatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/schluessel.class.php');

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
<title>Synchro - FAS -> Portal - Schlüssel</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS-PORTAL - Synchronisation
 */


$qry = "SELECT * FROM person_schluessel ORDER BY person_fk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Schlüssel Sync\n--------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();	
			
		$error=false;
		$schluessel				=new schluessel($conn);
		$schluessel->person_id		='';
		$schluessel->schluesseltyp		='';
		$schluessel->nummer		=$row->nummer;
		$schluessel->kaution		=$row->betrag;
		$schluessel->ausgegebenam	=date('Y-m-d',strtotime(strftime($row->verliehenam)));
		$schluessel->updatevon		="SYNC";
		$schluessel->insertvon		="SYNC";
		$schluessel->ext_id			=$row->schluessel_fk;

		//Person_id feststellen
		$qry1="SELECT person_portal FROM public.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$schluessel->person_id=$row1->person_portal;
					//Schlüsseltyp feststellen
					$qry2="SELECT schluesseltyp FROM tbl_schluesseltyp WHERE ext_id=".$row->schluessel_fk.";";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								$schluessel->schluesseltyp=$row2->schluesseltyp;
								//Insert oder Update
								$qry3="SELECT schluessel_id FROM tbl_schluessel WHERE ext_id=".$row->schluessel_fk.";";
								if($result3 = pg_query($conn, $qry3))
								{
									if(pg_num_rows($result3)>0) //eintrag gefunden
									{
										if($row3=pg_fetch_object($result3))
										{ 
											// update , wenn datensatz bereits vorhanden
											$schluessel->schluessel_id=$row3->schluessel_id;
											$schluessel->new=false;
										}
									}
									else 
									{
										// insert, wenn datensatz noch nicht vorhanden
										$schluessel->new=true;
									}
								}
							}
						}
						else 
						{
							// insert, wenn datensatz noch nicht vorhanden
							$schluessel->new=true;
						}
					}
					else 
					{
						$error=true;
						$error_log.="schluesseltyp mit schluessel_fk: $row->schluessel_fk konnte in tbl_schluesseltyp nicht gefunden werden! (".pg_num_rows($result1).")\n";
						$anzahl_fehler++;
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
			if(!$schluessel->save())
			{
				$error_log.=$schluessel->errormsg."\n";
				$anzahl_fehler++;
			}
			else 
			{
				$anzahl_eingefuegt++;
			}
		}
	}		
}



//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");

?>
</body>
</html>