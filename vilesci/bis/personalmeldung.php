<?php
/* Copyright (C) 2007
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */


require('../config.inc.php');
require('../../include/studiensemester.class.php');
require('../../include/datum.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");



$error_log='';
$error_log1='';
$error_log_all="";
$stgart='';
$fehler='';
$v='';
$erhalter='';
$eteam=array();
$studiensemester=new studiensemester($conn);
$ssem=$studiensemester->getaktorNext();

if(strstr($ssem,"WS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
}
elseif(strstr($ssem,"SS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")-1));
}
else 
{
	echo "Ungültiges Semester!";
}

$qry="SELECT * FROM public.tbl_erhalter";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		if(strlen(trim($row->erhalter_kz))==1)
		{
			$erhalter='00'.trim($row->erhalter_kz);
		}
		elseif(strlen(trim($row->erhalter_kz))==2)
		{
			$erhalter='0'.trim($row->erhalter_kz);
		}
		else 
		{
			$erhalter=$row->erhalter_kz;
		}
	}
}

$qry="SELECT * FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) 
	JOIN public.tbl_person USING(person_id)   
	WHERE aktiv AND bismelden AND (ende>now() OR ende IS NULL)  
	";
/*
	bis.tbl_bisverwendung USING (mitarbeiter_uid)
	bis.tbl_bisfunktion USING(bisverwendung_id) 
	bis.tbl_entwicklungsteam USING(mitarbeiter_uid) 
	public.tbl_benutzerfunktion
*/
if($result = pg_query($conn, $qry))
{
	
	$datei.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
   <ErhKz>".$erhalter."</ErhKz>
   <MeldeDatum>".$bisdatum."</MeldeDatum>
   <PersonalMeldung>";
	while($row = pg_fetch_object($result))
	{
		$qryet="SELECT * FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."';";
		if($resultet=pg_query($conn,$qryet))
		{
			while($rowet=pg_fetch_object($resultet))
			{
				$eteam[$rowet->studiengang_kz]=$rowet->besqualcode;
			}
		}
		$datei.="<Person>
      <PersonalNummer>".sprintf("%15s",$row->personalnummer)."</PersonalNummer>
      <GeburtsDatum>".$row->gebdatum."</GeburtsDatum>
      <Geschlecht>".$row->geschlecht."</Geschlecht>
      <HoechsteAbgeschlosseneAusbildung>".$row->ausbildungcode."</HoechsteAbgeschlosseneAusbildung>";
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."' WHERE habilitation=true;";
		if($resultvw=pg_query($conn,$qryvw))
		{
			if(pg_num_rows($result_vw)>0)
			{
				$datei.="
       <Habilitation>J</Habilitation>";
			}
			else 
			{
				$datei.="
       <Habilitation>N</Habilitation>";
			}
		}
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."';";
		if($resultvw=pg_query($conn,$qryvw))
		{
			while($rowvw=pg_fetch_object($resultvw))
			{
				$datei.="
           <Verwendung>
	   <BeschaeftigungsArt1>".$rowvw->bacode1."</BeschaeftigungsArt1>
              <BeschaeftigungsArt2>".$rowvw->bacode2."</BeschaeftigungsArt2>
              <BeschaeftigungsAusmass>".$rowvw->beschausmasscode."</BeschaeftigungsAusmass>
              <VerwendungsCode>".$row->verwendung_code."</VerwendungsCode>";
				//Studiengangsleiter
				$qryslt="SELECT * FROM public.tbl_benutzerfunktion WHERE uid='".$row->mitarbeiter_uid."' AND funktion_kurzbz='stgl';";
				if($resultslt=pg_query($conn,$qryslt))
				{
					while($rowslt=pg_fetch_object($resultslt))
					{
						$datei.="
                     <StgLeitung>
                          <StgKz>".sprintf("%04s",$rowslt->studiengang_kz)."</StgKz>
                     <StgLeitung>";
					}
				}
				//Funktionen
				$qryfkt="SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".$rowvw->bisverwendung_id."' ;";
				if($resultfkt=pg_query($conn,$qryfkt))
				{
					while($rowfkt=pg_fetch_object($resultfkt))
					{
						$datei.="
                    <Funktion>
                       <StgKz>".sprintf("%04s",$rowfkt->studiengang_kz)."</StgKz>
                       <SWS>$rowfkt->sws</SWS>
                       <Hauptberuflich>$rowvw->hauptberuflich=='t'?'J':'N'</Hauptberuflich>";
						if(isset($eteam[$rowfkt->studiengang_kz]))
						{
							$datei.="
                       <Entwicklungsteam>J</Entwicklungsteam>
                       <BesondereQualifikationCode>".$eteam[$rowfkt->studiengang_kz]."</BesondereQualifikationCode>";
						}
						else 
						{
							$datei.="
                       <Entwicklungsteam>N</Entwicklungsteam>";
						}
					$datei.="
                    </Funktion>";
					}
				}
				$datei."
             </Verwendung>";
			}
		}
		$datei.="
         </Person>";
	}
	$datei.="
      </PersonalMeldung>
</Erhalter>";
}
echo '	<html><head><title>BIS - Meldung Mitarbeiter</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head><body>';
echo "<H1>BIS - Mitarbeiterdaten werden überprüft!</H1>\n";
//echo "<H2>Nicht plausible BIS-Daten (für Meldung ".$ssem."): </H2><br>";
echo nl2br($v."\n\n");

//Tabelle mit Ergebnissen ausgeben

	
$ddd='bisdaten/bismeldung_mitarbeiter.xml';
	$dateiausgabe=fopen($ddd,'w');
	fwrite($dateiausgabe,$datei);
	fclose($dateiausgabe);
echo "<a href=$ddd>XML-Datei f&uuml;r Mitarbeiter-BIS-Meldung</a><br><br>";
?>