<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert IO-Datensaetze von FAS DB in VILESCI DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../sync_config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$update=false;
$ausgabe='';
$ausgabe_io='';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - IO</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*************************
 * FAS - VILESCI - Synchronisation
 */


$qry = "SELECT *, creationdate::timestamp as insertamum FROM mobilitaetsprogramm;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("IO-Sync\n-------------\n");
	echo nl2br("IO-Synchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//echo "- ";
		//ob_flush();
		//flush();	
			
		$error=false;
		$update=false;
		$ausgabe_io='';
		$mobilitaetsprogramm_code 	= $row->programm;
		$nation_code 			= $row->gastland;
		$von 					= $row->von;
		$bis 					= $row->bis;
		$zweck_code 			= $row->zweck;
		//$student_uid 			= '';
		$updateamum 			= $row->insertamum;
		$updatevon 				= 'SYNC';
		$insertamum 				= $row->insertamum;
		//$insertvon 				= '';
		$ext_id				=$row->mobilitaetsprogramm_pk;
		
		//insertvon
		$qrycu="SELECT name FROM benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$insertvon=$rowcu->name;
			}
		}
		
		
		//student_id ermitteln
		$qry="SELECT student_uid, studiengang_kz FROM public.tbl_student WHERE ext_id='".$row->student_fk."';";
		if($resulto=pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$student_uid=$rowo->student_uid;
			}
			else 
			{
				$error=true;
				$error_log.="Student mit student_fk: $row->student_fk konnte nicht gefunden werden.\n   ".$qry."\n";
			}
		}
		
		if(!$error)
		{
			$qry="SELECT * FROM bis.tbl_bisio WHERE student_uid='".$student_uid."' AND ext_id='".$ext_id."';";
			if($result2=pg_query($conn,$qry))
			{
				if(pg_num_rows($result2)>0) 
				{
					//eintrag gefunden
					if($row2=pg_fetch_object($result2))
					{
						$update=false;			
						if($row2->mobilitaetsprogramm_code!=$mobilitaetsprogramm_code) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", Mobilitätsprogramm: '".$mobilitaetsprogramm_code."' (statt '".$row2->mobilitaetsprogramm_code."')";
							}
							else
							{
								$ausgabe_io="Mobilitätsprogramm: '".$mobilitaetsprogramm_code."' (statt '".$row2->mobilitaetsprogramm_code."')";
							}
						}
						if($row2->nation_code!=$nation_code) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", Nation: '".$nation_code."' (statt '".$row2->nation_code."')";
							}
							else
							{
								$ausgabe_io="Nation: '".$nation_code."' (statt '".$row2->nation_code."')";
							}
						}
						if($row2->von!=$von) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", von: '".$von."' (statt '".$row2->von."')";
							}
							else
							{
								$ausgabe_io="von: '".$von."' (statt '".$row2->von."')";
							}
						}
						if($row2->bis!=$bis) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", bis: '".$bis."' (statt '".$row2->bis."')";
							}
							else
							{
								$ausgabe_io="bis: '".$bis."' (statt '".$row2->bis."')";
							}
						}
						if($row2->zweck_code!=$zweck_code) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", Zweck: '".$zweck_code."' (statt '".$row2->zweck_code."')";
							}
							else
							{
								$ausgabe_io="Zweck: '".$zweck_code."' (statt '".$row2->zweck_code."')";
							}
						}
						if($row2->zweck_code!=$zweck_code) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", Zweck: '".$zweck_code."' (statt '".$row2->zweck_code."')";
							}
							else
							{
								$ausgabe_io="Zweck: '".$zweck_code."' (statt '".$row2->zweck_code."')";
							}
						}
						if($row2->updateamum!=$updateamum) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", updateamum: '".$updateamum."' (statt '".$row2->updateamum."')";
							}
							else
							{
								$ausgabe_io="updateamum: '".$updateamum."' (statt '".$row2->updateamum."')";
							}
						}
						if($row2->updatevon!=$updatevon) 
						{
							$update=true;
							if(strlen(trim($ausgabe_io))>0)
							{
								$ausgabe_io.=", updatevon: '".$updatevon."' (statt '".$row2->updatevon."')";
							}
							else
							{
								$ausgabe_io="updatevon: '".$updatevon."' (statt '".$row2->updatevon."')";
							}
						}
						if($update)
						{	
							$qry="UPDATE bis.tbl_bisio SET ".
								"mobilitaetsprogramm_code=".myaddslashes($mobilitaetsprogramm_code).", ".
								"nation_code=".myaddslashes(nation_code).", ".
								"von=".myaddslashes(von).", ".
								"bis=".myaddslashes(bis).", ".
								"zweck_code=".myaddslashes(zweck_code).", ".
								"student_uid=".myaddslashes(student_uid).", ".
								"updateamum=now(), ".
								"updatevon='SYNC', ".
								"WHERE bisio_id=".myaddslashes($row2->bisio_id).";";
								$ausgabe.="IO von Student: '".$student_uid."' aktualisiert: ".$ausgabe_io.".\n";
								$anzahl_update++;
						}
						else
						{
							$qry="select 1;";
						}
					}
				}
				else 
				{
					//einfügen
					$qry="INSERT INTO bis.tbl_bisio (mobilitaetsprogramm_code, nation_code, von, bis, zweck_code,
						student_uid, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES(".
						myaddslashes($mobilitaetsprogramm_code).", ".
						myaddslashes($nation_code).", ".
						myaddslashes($von).", ".
						myaddslashes($bis).", ".
						myaddslashes($zweck_code).", ".
						myaddslashes($student_uid).", ".
						myaddslashes($updateamum).", ".
						myaddslashes($updatevon).", ".
						myaddslashes($insertamum).", ".
						myaddslashes($insertvon).", ".
						myaddslashes($ext_id)." ".
						");";
						$ausgabe.="IO von Student: '".$student_uid."' angelegt.\n";
						$anzahl_eingefuegt++;
				}
				if(!pg_query($conn,$qry))
				{
					$error_log.= "*****\nFehler beim Speichern des IO-Datensatzes von Student: ".$student_uid."\n   ".$qry."\n";
					$anzahl_fehler++;
				}
			}
		}
		
	}
}

echo nl2br("IO-Synchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\n\n");

//echo nl2br($text);
echo nl2br("\n".$error_log);
echo nl2br("\n\nIO-Sync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n$ausgabe");
$ausgabe="IO-Sync:\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler IO von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC IO von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>