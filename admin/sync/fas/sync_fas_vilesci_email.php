<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert EMaildatensaetze von FAS DB in VILESCI DB
//*
//*ben�tigt: tbl_kontakttyp, tbl_syncperson

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
$ausgabe_email='';

function validate($row)
{
}
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - E-Mail</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS - VILESCI - Synchronisation
 */

//nation
$qry = "SELECT * FROM email ORDER BY person_fk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("E-Mail Sync\n-------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//echo "- ";
		//ob_flush();
		//flush();	
			
		$error=false;
		$kontakt				=new kontakt($conn);
		$kontakt->firma_id			='';
		$kontakt->kontakttyp		='email';
		$kontakt->anmerkung		=$row->name;
		$kontakt->kontakt			=$row->email;
		$kontakt->zustellung			=$row->zustelladresse=='J'?true:false;
		$kontakt->updatevon		="SYNC";
		$kontakt->insertvon			="SYNC";
		$kontakt->ext_id			=$row->email_pk;
		
		$update=false;
		$ausgabe_email='';
		//Person_id feststellen
		$qry1="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$qry2="SELECT * FROM tbl_kontakt WHERE ext_id=".$row->email_pk." AND kontakttyp='email';";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								
								if($row2->anmerkung!=$row->name)
								{
									$update=true;
									if(strlen(trim($ausgabe_email))>0)
									{
										$ausgabe_email.=", Name: '".trim($row->name)."'";
									}
									else
									{
										$ausgabe_email="Name: '".trim($row->name)."'";
									}
								}
								if($row2->kontakt!=$row->email)
								{
									$update=true;
									if(strlen(trim($ausgabe_email))>0)
									{
										$ausgabe_email.=", E-Mail: '".trim($row->email)."'";
									}
									else
									{
										$ausgabe_email="E-Mail: '".trim($row->email)."'";
									}
								}
								if($row2->zustellung!=($row->zustelladresse=='J'?'t':'f'))
								{
									$update=true;
									if(strlen(trim($ausgabe_email))>0)
									{
										$ausgabe_email.=", Zustelladresse: '".($row->zustelladresse=='J'?'true':'false')."'";
									}
									else
									{
										$ausgabe_email="Zustelladresse: '".($row->zustelladresse=='J'?'true':'false')."'";
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
				$ausgabe_email='';
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
						$ausgabe.="E-Mail $kontakt->kontakt eingef�gt!\n";
						$anzahl_eingefuegt++;
					}
					else 
					{
						if($update)
						{
							$ausgabe.="E-Mail $kontakt->kontakt ge�ndert: ".$ausgabe_email." !\n";
							$anzahl_update++;
						}
					}
				}
			}
		}
	}		
}



//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\n\nE-Mailsync:\nGesamt: $anzahl_quelle / Eingef�gt: $anzahl_eingefuegt / Ge�ndert: $anzahl_update / Fehler: $anzahl_fehler");
$ausgabe="E-Mailsync:\nGesamt: $anzahl_quelle / Eingef�gt: $anzahl_eingefuegt / Ge�ndert: $anzahl_update / Fehler: $anzahl_fehler\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Email von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Email von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>