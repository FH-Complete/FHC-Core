<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Bachelorarbeitsdatensaetze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');


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
<title>Synchro - FAS -> Portal - Bachelorarbeit</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM bakkalaureatsarbeit;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Bachelorarbeit Sync\n------------------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$projektarbeit				=new projektarbeit($conn);
		$projektarbeit->projekttyp_kurzbz	='Bachelorarbeit';
		$projektarbeit->titel			=$row->titel;
		$projektarbeit->lehreinheit_id	='';
		$projektarbeit->student_uid		='';
		$projektarbeit->firma_id		='';
		$projektarbeit->note			=$row->note;
		$projektarbeit->punkte		=$row->punkte;
		$projektarbeit->beginn		='';
		$projektarbeit->ende		=$row->datum;
		$projektarbeit->faktor		='1.0';
		$projektarbeit->freigegeben	=$row->gesperrtbis==null?true:false;
		$projektarbeit->gesperrtbis		=$row->gesperrtbis;
		$projektarbeit->stundensatz	=$row->betreuerstundenhonorar;
		$projektarbeit->gesamtstunden	='';
		$projektarbeit->themenbereich	=$row->themenbereich;
		$projektarbeit->anmerkung		='';		
		//$projektarbeit->updateamum	=$row->;
		$projektarbeit->updatevon		="SYNC";
		//$projektarbeit->insertamum	=$row->;
		$projektarbeit->insertvon		="SYNC";
		$projektarbeit->ext_id		=$row->bakkalaureatsarbeit_pk;
		
		//lehreinheit_id ermitteln
		//projektbetreuer = betreuer, begutachter
		$qry="SELECT uid FROM student WHERE student_pk=".$row->student_fk.";";
		if($resultu = pg_query($conn_fas, $qry))
		{
			if($rowu=pg_fetch_object($resultu))
			{ 
				$projektarbeit->student_uid=$rowu->uid;
				$qry2="SELECT projektarbeit_id, ext_id FROM tbl_reihungstest WHERE ext_id=".$row->bakkalaureatsareit_pk.";";
				if($result2 = pg_query($conn, $qry2))
				{
					if(pg_num_rows($result2)>0) //eintrag gefunden
					{
						if($row2=pg_fetch_object($result2))
						{ 
							// update, wenn datensatz bereits vorhanden
							$projektarbeit->new=false;
							$projektarbeit->projektarbeit_id=$row2->projektarbeit_id;
						}
					}
					else 
					{
						// insert, wenn datensatz noch nicht vorhanden
						$projektarbeit->new=true;	
					}
				}
			}
		}
				
		
		if(!$error)
		{
			if(!$projektarbeit->save())
			{
				$error_log.=$projektarbeit->errormsg."\n";
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