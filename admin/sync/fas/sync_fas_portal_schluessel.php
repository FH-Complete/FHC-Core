<?php
/* Copyright (C) 2006 Technikum-Wien
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
include('../../../include/betriebsmittel.class.php');
include('../../../include/betriebsmittelperson.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log = '';
$text = '';
$anzahl_quelle = 0;
$anzahl_eingefuegt = 0;
$anzahl_eingefuegt2 = 0;
$anzahl_fehler = 0;
$anzahl_fehler2 = 0;
$krit = '';

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
		$betriebsmittel				=new betriebsmittel($conn);
		//$betriebsmittel->betriebsmittel_id		='';
		$betriebsmittel->beschreibung		='';
		//$betriebsmittel->betriebsmitteltyp		='';
		$betriebsmittel->nummer			=$row->nummer;
		$betriebsmittel->reservieren		=false;
		$betriebsmittel->ort_kurzbz			=null;
		$betriebsmittel->updatevon			="SYNC";
		$betriebsmittel->insertvon			="SYNC";
		$betriebsmittel->ext_id			=$row->schluessel_fk;

		$betriebsmittelperson			=new betriebsmittelperson($conn);
		//$betriebsmittelperson->betriebsmittel_id	='';
		//$betriebsmittelperson->person_id	='';
		$betriebsmittelperson->anmerkung	='';
		$betriebsmittelperson->kaution		=$row->betrag;
		$betriebsmittelperson->ausgegebenam	=date('Y-m-d',strtotime(strftime($row->verliehenam)));
		$betriebsmittelperson->retouram		='';
		$betriebsmittelperson->updatevon		="SYNC";
		$betriebsmittelperson->insertvon		="SYNC";
		$betriebsmittelperson->ext_id		=$row->schluessel_fk;
		
		//Person_id feststellen
		$qry1="SELECT person_portal FROM public.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$betriebsmittelperson->person_id=$row1->person_portal;
					//Schlüsseltyp feststellen
					$qry2="SELECT * FROM public.tbl_syncschluesseltyp WHERE fas_typ='".$row->schluessel_fk."';";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								$betriebsmittel->betriebsmitteltyp=$row2->portal_typ;
								//Insert oder Update
								$qry3="SELECT betriebsmittel_id FROM public.tbl_betriebsmittel WHERE ext_id=".$row->schluessel_fk.";";
								if($result3 = pg_query($conn, $qry3))
								{
									if(pg_num_rows($result3)>0) //eintrag gefunden
									{
										if($row3=pg_fetch_object($result3))
										{ 
											// update , wenn datensatz bereits vorhanden
											$betriebsmittel->betriebsmittel_id=$row3->betriebsmittel_id;
											$betriebsmittelperson->betriebsmittel_id=$row3->betriebsmittel_id;
											$betriebsmittel->new=false;
										}
									}
									else 
									{
										// insert, wenn datensatz noch nicht vorhanden
										$betriebsmittel->new=true;
										$qry = "SELECT nextval('public.tbl_betriebsmittel_betriebsmittel_id_seq') as id;";
										if(!$row = pg_fetch_object(pg_query($conn, $qry)))
										{
											$error_log.= '\nFehler beim Auslesen der Betriebsmittel-Sequence';
											$error=true;
										}
										$betriebsmittel->betriebsmittel_id=$row->id;
										$betriebsmittelperson->betriebsmittel_id=$row->id;
									}
								}
							}
						}
						else 
						{
							// insert, wenn datensatz noch nicht vorhanden
							$betriebsmittel->new=true;
						}
					}
					else 
					{
						$error=true;
						$error_log.="betriebsmitteltyp mit schluessel_fk: $row->schluessel_fk konnte in tbl_betriebsmitteltyp nicht gefunden werden! \n";
						$anzahl_fehler++;
					}
				}
			}
			else 
			{
				$error=true;
				$error_log.="\nperson mit person_fk: $row->person_fk konnte in tbl_syncperson nicht gefunden werden! ";
				$anzahl_fehler++;
			}
		}
		If (!$error)
		{
			pg_query($conn,"BEGIN");
			if(!$betriebsmittel->save())
			{
				$error_log.=$betriebsmittel->errormsg."\n";
				$anzahl_fehler++;
				pg_query($conn,"ROLLBACK");
			}
			else 
			{
				$anzahl_eingefuegt++;
				//insert oder update?
				$qry3="SELECT betriebsmittel_id, person_id FROM public.tbl_betriebsmittelperson WHERE betriebsmittel_id=".$betriebsmittel->betriebsmittel_id." AND person_id=".$betriebsmittelperson->person_id.";";
				if($result3 = pg_query($conn, $qry3))
				{
					if(pg_num_rows($result3)>0) //eintrag gefunden
					{
						if($row3=pg_fetch_object($result3))
						{ 
							// update , wenn datensatz bereits vorhanden
							$betriebsmittelperson->new=false;
						}
					}
					else 
					{
						// insert, wenn datensatz noch nicht vorhanden
						$betriebsmittelperson->new=true;					
					}
				}
				else 
				{
					$error=true;
					$error_log.="\nFehler beim Zugriff auf tbl_betreibsmittelperson.";
				}
				if (!$error)
				{
					if(!$betriebsmittelperson->save())
					{
						$error_log.=$betriebsmittel->errormsg."\n";
						$anzahl_fehler2++;
						pg_query($conn,"ROLLBACK");
					}
					else 
					{
						$anzahl_eingefuegt2++;
						pg_query($conn,"COMMIT");
					}
				}
				else 
				{
					pg_query($conn, "ROLLBACK");
				}
			}
		}
	}		
}



//echo nl2br($text);
echo nl2br("\n\n".$error_log);
echo nl2br("\n"."Betriebsmittel:");
echo nl2br("\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler");
echo nl2br("\n"."Betriebsmittelperson:");
echo nl2br("\nGesamt: $anzahl_eingefügt / Eingefügt: $anzahl_eingefuegt2 / Fehler: $anzahl_fehler2");
$error_log="\nBetriebsmittel: \nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler\nBetriebsmittelperson: \nGesamt: $anzahl_eingefügt / Eingefügt: $anzahl_eingefuegt / Fehler: $anzahl_fehler\n".$error_log;
mail($adress, 'SYNC Schluessel', $error_log);
?>
</body>
</html>