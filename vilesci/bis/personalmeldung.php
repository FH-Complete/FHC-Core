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


$error_log='';
$error_log1='';
$error_log_all="";
$stgart='';
$fehler='';
$v='';
$erhalter='';
$zaehl=0;
$eteam=array();
$studiensemester=new studiensemester($conn);
$ssem=$studiensemester->getaktorNext();		//aktuelles Semester
$psem=$studiensemester->getPrevious();		//voriges Semester
$bsem=$studiensemester->getBeforePrevious();		//vorjähriges Semester
$datei='';

$datumobj=new datum();

if(strstr($ssem,"WS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
}
/*elseif(strstr($ssem,"SS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")-1));
}*/
else
{
	echo "Ungültiges Semester!";
	exit;
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

$qry="SELECT DISTINCT ON (UID) * FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
	JOIN public.tbl_person USING(person_id)
	WHERE tbl_benutzer.aktiv AND bismelden AND personalnummer>1 AND mitarbeiter_uid!='_DummyLektor'
	ORDER BY uid, nachname,vorname
	";

/*
	AND (ende>now() OR ende IS NULL)
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
   <MeldeDatum>".date("dmY", $datumobj->mktime_fromdate($bisdatum))."</MeldeDatum>
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
		if($row->gebdatum=='' || $row->gebdatum==NULL)
		{
			if($error_log!='')
			{
				$error_log.=", Geburtsdatum ('".$row->gebdatum."')";
			}
			else
			{
				$error_log="Geburtsdatum ('".$row->gebdatum."')";
			}
		}
		if($row->geschlecht=='' || $row->geschlecht==NULL)
		{
			if($error_log!='')
			{
				$error_log.=", Geschlecht ('".$row->geschlecht."')";
			}
			else
			{
				$error_log="Geschlecht ('".$row->geschlecht."')";
			}
		}
		if($row->ausbildungcode=='' || $row->ausbildungcode==NULL)
		{
			if($error_log!='')
			{
				$error_log.=", HoechsteAbgeschlosseneAusbildung ('".$row->ausbildungcode."')";
			}
			else
			{
				$error_log="HoechsteAbgeschlosseneAusbildung ('".$row->ausbildungcode."')";
			}
		}
		$datei.="
     <Person>
      <PersonalNummer>".sprintf("%015s",$row->personalnummer)."</PersonalNummer>
      <GeburtsDatum>".date("dmY", $datumobj->mktime_fromdate($row->gebdatum))."</GeburtsDatum>
      <Geschlecht>".strtoupper($row->geschlecht)."</Geschlecht>
      <HoechsteAbgeschlosseneAusbildung>".$row->ausbildungcode."</HoechsteAbgeschlosseneAusbildung>";
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."' AND habilitation=true;";
		if($resultvw=pg_query($conn,$qryvw))
		{
			if(pg_num_rows($resultvw)>0)
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
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."' AND (ende is null OR ende>'$bisprevious') AND beginn<'$bisdatum';";
		if($resultvw=pg_query($conn,$qryvw))
		{
			while($rowvw=pg_fetch_object($resultvw))
			{
				if($rowvw->ba1code=='' || $rowvw->ba1code==NULL)
				{
					if($error_log!='')
					{
						$error_log.=", Beschaeftigungsart1 ('".$rowvw->ba1code."')";
					}
					else
					{
						$error_log="Beschaeftigungsart1 ('".$rowvw->ba1code."')";
					}
				}
				if($rowvw->ba2code=='' || $rowvw->ba2code==NULL)
				{
					if($error_log!='')
					{
						$error_log.=", Beschaeftigungsart2 ('".$rowvw->ba2code."')";
					}
					else
					{
						$error_log="Beschaeftigungsart2 ('".$rowvw->ba2code."')";
					}
				}
				if($rowvw->beschausmasscode=='' || $rowvw->beschausmasscode==NULL)
				{
					if($error_log!='')
					{
						$error_log.=", BeschaeftigungsAusmass ('".$rowvw->beschausmasscode."')";
					}
					else
					{
						$error_log="BeschaeftigungsAusmass ('".$rowvw->beschausmasscode."')";
					}
				}
				if($rowvw->verwendung_code=='' || $rowvw->verwendung_code==NULL)
				{
					if($error_log!='')
					{
						$error_log.=", VerwendungsCode ('".$rowvw->verwendung_code."')";
					}
					else
					{
						$error_log="VerwendungsCode ('".$rowvw->verwendung_code."')";
					}
				}
				if(!$rowvw->hauptberuflich && ($rowvw->hauptberufcode=='' || $rowvw->hauptberufcode==NULL))
				{
					if($error_log!='')
					{
						$error_log.=", Hauptberuf ('".$rowvw->hauptberufcode."')";
					}
					else 
					{
						$error_log="Hauptberuf ('".$rowvw->hauptberufcode."')";
					} 
				}
				$datei.="
       <Verwendung>
              <BeschaeftigungsArt1>".$rowvw->ba1code."</BeschaeftigungsArt1>
              <BeschaeftigungsArt2>".$rowvw->ba2code."</BeschaeftigungsArt2>
              <BeschaeftigungsAusmass>".$rowvw->beschausmasscode."</BeschaeftigungsAusmass>
              <VerwendungsCode>".$rowvw->verwendung_code."</VerwendungsCode>";
				//Studiengangsleiter
				$qryslt="SELECT * FROM public.tbl_benutzerfunktion WHERE uid='".$row->mitarbeiter_uid."' AND funktion_kurzbz='stgl';";
				if($resultslt=pg_query($conn,$qryslt))
				{
					while($rowslt=pg_fetch_object($resultslt))
					{
						if($rowslt->studiengang_kz=='' || $rowslt->studiengang_kz==NULL)
						{
							if($error_log!='')
							{
								$error_log.=", StgKz(Leitung) ('".$rowslt->studiengang_kz."')";
							}
							else
							{
								$error_log="StgKz(Leitung) ('".$rowslt->studiengang_kz."')";
							}
						}
						$datei.="
                     <StgLeitung>
                          <StgKz>".sprintf("%04s",$rowslt->studiengang_kz)."</StgKz>
                     </StgLeitung>";
					}
				}
				//Funktionen
				$qryfkt="SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".$rowvw->bisverwendung_id."' AND studiengang_kz>0 AND studiengang_kz<10000;";
				if($resultfkt=pg_query($conn,$qryfkt))
				{
					while($rowfkt=pg_fetch_object($resultfkt))
					{
						if($rowfkt->studiengang_kz=='' || $rowfkt->studiengang_kz==NULL)
						{
							if($error_log!='')
							{
								$error_log.=", StgKz(Funktion) ('".$rowfkt->studiengang_kz."')";
							}
							else
							{
								$error_log="StgKz(Funktion) ('".$rowfkt->studiengang_kz."')";
							}
						}
						if($rowfkt->sws=='' || $rowfkt->sws==NULL)
						{
							if($error_log!='')
							{
								$error_log.=", SWS ('".$rowfkt->sws."')";
							}
							else
							{
								$error_log="SWS ('".$rowfkt->sws."')";
							}
						}
						if($rowvw->hauptberuflich=='' || $rowvw->hauptberuflich==NULL)
						{
							if($error_log!='')
							{
								$error_log.=", Hauptberuflich ('".$rowvw->hauptberuflich."')";
							}
							else
							{
								$error_log="Hauptberuflich ('".$rowvw->hauptberuflich."')";
							}
						}
						if(($rowvw->hauptberufcode=='' || $rowvw->hauptberufcode==NULL) && $rowvw->hauptberuflich=='f')
						{
							if($error_log!='')
							{
								$error_log.=", HauptberufCode ('".$rowvw->hauptberufcode."')";
							}
							else
							{
								$error_log="HauptberufCode ('".$rowvw->hauptberufcode."')";
							}
						}
						if (isset($eteam[$rowfkt->studiengang_kz]))
						{
							if(($eteam[$rowfkt->studiengang_kz]=='' || $eteam[$rowfkt->studiengang_kz]==NULL))
							{
								if($error_log!='')
								{
									$error_log.=", BesondereQualifikationCode ('".$eteam[$rowfkt->studiengang_kz]."')";
								}
								else
								{
									$error_log="BesondereQualifikationCode ('".$eteam[$rowfkt->studiengang_kz]."')";
								}
							}
						}
						$datei.="
                    <Funktion>
                       <StgKz>".sprintf("%04s",$rowfkt->studiengang_kz)."</StgKz>
                       <SWS>".$rowfkt->sws."</SWS>";
						if($rowvw->hauptberuflich)
						{
							$datei.="
                       <Hauptberuflich>J</Hauptberuflich>";
						}
						else
						{
							$datei.="
                       <Hauptberuflich>N</Hauptberuflich>
                       <HauptberufCode>".$rowvw->hauptberufcode."</HauptberufCode>";
						}
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
				$datei.="
             </Verwendung>";
			}
		}
		$datei.="
         </Person>";
		if($error_log!='' OR $error_log1!='')
		{
			$v.="<u>Bei Mitarbeiter (PersNr, UID, Vorname, Nachname) '".$row->personalnummer."','".$row->mitarbeiter_uid."', '".$row->nachname."', '".$row->vorname."': </u>\n";
			if($error_log!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Es fehlt: ".$error_log."\n";
			}
			$zaehl++;
			$v.="\n";
			$error_log='';
		}
	}
	$datei.="
      </PersonalMeldung>
</Erhalter>";
}

echo '	<html><head><title>BIS - Meldung Mitarbeiter</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head><body>';
echo "<H1>BIS - Mitarbeiterdaten werden &uuml;berpr&uuml;ft!</H1>\n";
echo "<H2>Nicht plausible BIS-Daten (f&uuml;r Meldung ".$ssem."): </H2><br>";
echo nl2br($v."\n\n");

//Tabelle mit Ergebnissen ausgeben

$ddd='bisdaten/bismeldung_mitarbeiter.xml';
	$dateiausgabe=fopen($ddd,'w');
	fwrite($dateiausgabe,$datei);
	fclose($dateiausgabe);
echo "<a href=$ddd>XML-Datei f&uuml;r Mitarbeiter-BIS-Meldung</a><br><br>";
?>