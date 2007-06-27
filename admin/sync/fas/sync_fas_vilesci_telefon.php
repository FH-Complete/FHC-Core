<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Telefondatensaetze von FAS DB in PORTAL DB
//*
//* benötigt: tbl_syncperson, tbl_kontakttyp

include('../../../vilesci/config.inc.php');
include('../../../include/kontakt.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$update=false;
$ausgabe='';
$ausgabe_telefon='';

function validate($row)
{
}
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Telefon</title>
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
	echo "Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."<br><br>";
	while($row = pg_fetch_object($result))
	{
		//echo "- ";
		//ob_flush();
		//flush();	
		$update=false;
		$ausgabe_telefon='';	
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
			$qry1="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
			if($result1 = pg_query($conn, $qry1))
			{
				if(pg_num_rows($result1)>0) //eintrag gefunden
				{
					if($row1=pg_fetch_object($result1))
					{ 
						$qry2="SELECT * FROM tbl_kontakt WHERE ext_id=".$row->telefonnummer_pk." AND (kontakttyp='telefon' OR kontakttyp='mobil' OR kontakttyp='fax' OR kontakttyp='so.tel');";
						if($result2 = pg_query($conn, $qry2))
						{
							if(pg_num_rows($result2)>0) //eintrag gefunden
							{
								if($row2=pg_fetch_object($result2))
								{ 
									if($row2->kontakttyp!=$kontakt->kontakttyp)
									{
										$update=true;
										if(strlen(trim($ausgabe_telefon))>0)
										{
											$ausgabe_telefon.=", Kontakttyp: '".$kontakt->kontakttyp."'";
										}
										else
										{
											$ausgabe_telefon="Kontakttyp: '".$kontakt->kontakttyp."'";
										}
									}
									if($row2->anmerkung!=$row->name)
									{
										$update=true;
										if(strlen(trim($ausgabe_telefon))>0)
										{
											$ausgabe_telefon.=", Anmerkung: '".$row->name."'";
										}
										else
										{
											$ausgabe_telefon="Anmerkung: '".$row->name."'";
										}
									}
									if($row2->kontakt!=$row->nummer)
									{
										$update=true;
										if(strlen(trim($ausgabe_telefon))>0)
										{
											$ausgabe_telefon.=", Kontakt: '".$row->nummer."'";
										}
										else
										{
											$ausgabe_telefon="Kontakt: '".$row->nummer."'";
										}
									}									
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
					$ausgabe_telefon='';
					$error=true;
					$error_log.="Person mit person_fk: $row->person_fk konnte in tbl_syncperson nicht gefunden werden!\n";
					$anzahl_fehler++;
				}
			}
			If (!$error)
			{
				if($kontakt->new || $update)
				{
					if(!$kontakt->save())
					{
						$error_log.=$kontakt->errormsg."\n";
						$anzahl_fehler++;
					}
					else 
					{
						if($kontakt->new)
						{
							$ausgabe.="Telefonnummer '$kontakt->kontakt' eingefügt!\n";
							$anzahl_eingefuegt++;
						}
						else 
						{
							if($update)
							{
								$ausgabe.="Telefonnummer '$kontakt->kontakt' geändert: ".$ausgabe_telefon." !\n";
								$anzahl_update++;
							}
						}
					}
				}
			}
		}
	}		
}

echo "Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."<br><br>";

//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler \n\n $ausgabe");
$ausgabe="Telefonsync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Telefon von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Telefon von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>