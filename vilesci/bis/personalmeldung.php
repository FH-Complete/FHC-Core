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

//Check, ob jeder Mitarbeiter nur eine Verwendung hat
$qryall='SELECT uid,nachname,vorname, count(bisverwendung_id)  
	FROM campus.vw_mitarbeiter LEFT OUTER JOIN bis.tbl_bisverwendung ON (uid=mitarbeiter_uid) 
	WHERE aktiv AND bismelden AND (ende>now() OR ende IS NULL) 
	GROUP BY uid,nachname,vorname HAVING count(bisverwendung_id)!=1 ORDER by nachname,vorname;';
if($resultall = pg_query($conn, $qryall))
{
	$num_rows_all=pg_num_rows($resultall);
	echo "<H2>Bei $num_rows_all aktiven Mitarbeitern sind die aktuellen Verwendungen nicht plausibel</H2>";
	while($rowall=pg_fetch_object($resultall))
	{
		$i=0;
		$qry="SELECT * FROM bis.tbl_bisverwendung 
			JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) 
			JOIN public.tbl_person USING(person_id) 
			JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
			WHERE tbl_benutzer.aktiv=TRUE AND bismelden=TRUE 
			AND (ende>now() OR ende IS NULL) AND mitarbeiter_uid='".$rowall->uid."';";
		if($result = pg_query($conn, $qry))
		{
			$num_rows=pg_num_rows($result);
			if($num_rows>1)
			{
				while($row=pg_fetch_object($result))
				{
					if($i==0)
					{
						echo "<br><u>Aktiv(e) Mitarbeiter(in) ".$row->nachname." ".$row->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
						$i++;
					}
					echo "Verwendung Code ".$row->verwendung_code.", Beschäftigungscode ".$row->ba1code.", ".$row->ba2code.", mit Ausmaß ".$row->beschausmasscode.", ".$row->beginn." - ".$row->ende."<br>";
				}
			}
			elseif($num_rows==0)
				echo "<br><u>Aktiv(e) Mitarbeiter(in): ".$rowall->nachname." ".$rowall->vorname." hat ".$num_rows." aktuelle Verwendungen:</u><br>";
		}
	}
}


//Funktionen prüfen
//	neue Fkt. anlegen
//	vorhandene auf sws prüfen
$qry="SET client_encoding TO Unicode;
	SELECT * FROM lehre.tbl_lehreinheit_mitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
	JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) 
	JOIN public.tbl_person USING(person_id) 
	WHERE studiensemester_kurzbz='".$psem."' OR studiensemester_kurzbz='".$bsem."'
	";





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
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."';";
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
				$qryfkt="SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".$rowvw->bisverwendung_id."' ;";
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
						if(($rowvw->hauptberufcode=='' || $rowvw->hauptberufcode==NULL) && $rowvw->hauptberuflich)
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