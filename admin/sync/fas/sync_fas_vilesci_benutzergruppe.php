<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Benutzergruppendatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_quelle_student=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$nicht_gefunden=0;
$ausgabe='';
$ausgabe_slv='';
$ausgabe_all='';
$update=false;

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Benutzergruppe</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry="SELECT * FROM person left join student on person_pk=person_fk
WHERE uid IS NOT null AND uid<>'' AND perskz IS NOT null AND perskz<>'' 
ORDER BY Familienname, Vorname;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Benutzergruppe Sync\n----------------------\n");
	$anzahl_quelle_student=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$uid					="";
		$gruppe_kurzbz			="";
		$studiensemester_kurzbz		="";
		$updateamum			="";
		$updatevon				="SYNC";
		$insertamum				=$row->creationdate;
		$insertvon				=$row->creationuser;
		$ext_id				="";
		
		$error=false;
		$error_log="";
		
		$qry4="SELECT * from public.tbl_benutzer WHERE ext_id='".$row->student_pk."';";
		if($result4 = pg_query($conn, $qry4))
		{
			if(pg_num_rows($result4)<1)
			{
				$error=true;
				$error_log_fas.="Benutzer mit ext_id='".$row->student_pk."' nicht gefunden! ('".$row->perskz."', '".$row->familienname."', '".$row->vorname."')\n";
				$nicht_gefunden++;
			}
			else 
			{
				if($row4=pg_fetch_object($result4))
				{
					$uid=$row4->uid;
				}	
			}
		}
		if(!$error)
		{
			$qry1="SELECT gruppe_fk FROM student_gruppe WHERE student_fk='".$row->student_pk."' ORDER BY gruppe_fk;";
			if($result1 = pg_query($conn_fas, $qry1))
			{
				while($row1=pg_fetch_object($result1))
				{ 
					$error=false;
					$error_log="";
					$anzahl_quelle++;
					$qry2="SELECT * FROM sync.tbl_syncgruppe WHERE fas_gruppe='".$row1->gruppe_fk."';";
					$gruppe_kurzbz="";	
					if($result2 = pg_query($conn, $qry2))
					{
						if($row2=pg_fetch_object($result2))
						{
							//echo nl2br("qry2=".$qry2."\n");
							$gruppe_kurzbz=$row2->vilesci_gruppe;
						}
						else 
						{
							$error=true;
						}
					}
					else 
					{
						$error=true;
						$error_log="Fehler beim Zugriff auf Tablelle tbl_gruppe.".$qry2;
						$anzahl_fehler++;
					}
					$qry3="SELECT studiensemester_fk FROM gruppe WHERE gruppe_pk='".$row1->gruppe_fk."';";	
					$studiensemester_kurzbz="";
					if($result3 = pg_query($conn_fas, $qry3))
					{
						if($row3=pg_fetch_object($result3))
						{
							$qry4="SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ext_id='".$row3->studiensemester_fk."';";
							if($result4 = pg_query($conn, $qry4))
							{
								if($row4=pg_fetch_object($result4))
								{
									$studiensemester_kurzbz=$row4->studiensemester_kurzbz;
								}
							}
							else 
							{
								$error=true;
								$error_log="Fehler beim Zugriff auf Tablelle tbl_studiensemester.".$qry4;
								$anzahl_fehler++;
							}
						}
						else 
						{
							$error=true;
						}
					}
					else 
					{
						$error=true;
						$error_log="Fehler beim Zugriff auf Tablelle gruppe.".$qry3;
						$anzahl_fehler++;
					}
					
					if($gruppe_kurzbz!="" && $studiensemester_kurzbz!="" && !$error && $uid!="")
					{
						$qrychk2="SELECT * FROM public.tbl_benutzergruppe WHERE uid='".$uid."' AND gruppe_kurzbz='".$gruppe_kurzbz."';";
						//echo nl2br("...".$qrychk2."\n");
						if($resultchk2 = pg_query($conn, $qrychk2))
						{
							if($rowchk2=pg_fetch_object($resultchk2))
							{
								//update
								
								if($rowchk2->uid!=$uid || $rowchk2->gruppe_kurzbz!=$gruppe_kurzbz || $rowchk2->studiensemester_kurzbz!=$studiensemester_kurzbz)
								{
									$qrybg="UPDATE public.tbl_benutzergruppe SET ".
									"uid=".myaddslashes($uid).", ".
									"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz).", ".
									"studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz).", ".
									"updateamum=now(), ".
									"updatevon='SYNC'".
									"WHERE uid='".$uid."' AND gruppe_kurzbz='".$gruppe_kurzbz."';";
									if($resultbg=pg_query($conn, $qrybg))
									{
										$anzahl_update++;
										$ausgabe="Benutzergruppe auf UID='".$uid."', Gruppe='".$gruppe_kurzbz."', Studiensemester='".$studiensemester_kurzbz."' geändert (statt UID='".$rowchk2->uid."', Gruppe='".$rowchk2->gruppe_kurzbz."', Studiensemester='".$rowchk2->studiensemester_kurzbz."').\n";
									}
									else 
									{
										$anzahl_fehler++;
										$error_log="Fehler beim Ändern in Tabelle tbl_benutzergruppe. ".$qrybg."\n";
									}
								}
							}
							else 
							{						
								//insert
								$qrybg="INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, studiensemester_kurzbz, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES( ".
								myaddslashes($uid).", ".
								myaddslashes($gruppe_kurzbz).", ".
								myaddslashes($studiensemester_kurzbz).", ".
								myaddslashes($insertamum).", ".
								myaddslashes($insertvon).", ".
								"now(), ".
								"'SYNC', ".
								myaddslashes($ext_id).");";
								if($resultbg=pg_query($conn, $qrybg))
								{
									$ausgabe="Benutzergruppe mit UID='".$uid."', Gruppe='".$gruppe_kurzbz."', Studiensemester='".$studiensemester_kurzbz."' angelegt.\n"; 
									$anzahl_eingefuegt++;
								}
								else 
								{
									$anzahl_fehler++;
									$error_log="Fehler beim Einfügen in Tabelle tbl_benutzergruppe. ".$qrybg."\n";
								}
							}
						}		
					}
					//echo nl2br($ext_id.", ".$error);
					$ausgabe_all.=$ausgabe;
					$error_log_fas.=$error_log;
					$ausgabe='';
					$error_log='';
				}
			}
		}
	}
}

//echo nl2br($text);
echo nl2br("\nBenutzergruppe\nStudenten: $anzahl_quelle_student / Gruppen: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n");
echo nl2br("\nStudenten, die in tbl_benutzer nicht gefunden wurden: ".$nicht_gefunden."\n".$error_log_fas);
echo nl2br ("\n\n".$ausgabe_all);
$ausgabe="\nBenutzergruppe\nStudenten: $anzahl_quelle_student / Gruppen: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler."
."\n\n".$ausgabe_all;

if(strlen(trim($error_log_fas))>0)
{
	mail($adress, 'SYNC-Fehler Benutzergruppe von '.$_SERVER['HTTP_HOST'], "Studenten, die in tbl_benutzer nicht gefunden wurden: ".$nicht_gefunden."\n".$error_log_fas,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Benutzergruppe von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");

?>
</body>
</html>