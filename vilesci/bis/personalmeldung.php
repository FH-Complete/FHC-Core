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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/datum.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$error_log='';
$error_log1='';
$error_log_all="";
$stgart='';
$fehler='';
$v='';
$erhalter='';
$zaehl=0;
$eteam=array();
$studiensemester=new studiensemester();
if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem=$studiensemester->getaktorNext(1);		//aktuelles Semester

$datei='';
$mitarbeiterzahl=0;
$echt=0;
$frei=0;

$nichtmelden = array(11,91,92,94,999,203,145,204, 308, 182, 222);

$datumobj=new datum();

if(mb_strstr($stsem,"WS"))
{
	$studiensemester->load($stsem);
	$jahr = $datumobj->formatDatum($studiensemester->start, 'Y');
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, $jahr));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 11, 15, $jahr-1));
}
else
{
	echo "Fehler: Studiensemester muss ein Wintersemester sein";
	exit;
}

$qry="SELECT * FROM public.tbl_erhalter";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$erhalter = sprintf("%03s",trim($row->erhalter_kz));
	}
}

$qry="
	SELECT DISTINCT ON (UID) * 
	FROM 
		public.tbl_mitarbeiter 
		JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)
		JOIN public.tbl_person USING(person_id)
		JOIN bis.tbl_bisverwendung USING(mitarbeiter_uid)
	WHERE 
		bismelden 
		AND personalnummer>0 
		AND (tbl_bisverwendung.ende is NULL OR tbl_bisverwendung.ende>'$bisprevious')
	ORDER BY uid, nachname,vorname
	";

if($result = $db->db_query($qry))
{
	
	$datei.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
   <ErhKz>".$erhalter."</ErhKz>
   <MeldeDatum>".date("dmY", $datumobj->mktime_fromdate($bisdatum))."</MeldeDatum>
   <PersonalMeldung>";
	while($row = $db->db_fetch_object($result))
	{
		$error_person = false;
		$person_content='';
		$qryet="SELECT * FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid='".$row->mitarbeiter_uid."';";
		if($resultet=$db->db_query($qryet))
		{
			while($rowet=$db->db_fetch_object($resultet))
			{
				$eteam[$rowet->studiengang_kz]=$rowet->besqualcode;
			}
		}
		$error_log='';
		
		if($row->gebdatum=='' || $row->gebdatum==NULL)
		{
				$error_log.=($error_log!=''?', ':'')."Geburtsdatum ('".$row->gebdatum."')";
		}
		if($row->geschlecht=='' || $row->geschlecht==NULL)
		{
				$error_log.=($error_log!=''?', ':'')."Geschlecht ('".$row->geschlecht."')";
		}
		if($row->ausbildungcode=='' || $row->ausbildungcode==NULL)
		{
				$error_log.=($error_log!=''?', ':'')."HoechsteAbgeschlosseneAusbildung ('".$row->ausbildungcode."')";
		}
		$person_content.="
     <Person>
      <PersonalNummer>".sprintf("%015s",$row->personalnummer)."</PersonalNummer>
      <GeburtsDatum>".date("dmY", $datumobj->mktime_fromdate($row->gebdatum))."</GeburtsDatum>
      <Geschlecht>".strtoupper($row->geschlecht)."</Geschlecht>
      <HoechsteAbgeschlosseneAusbildung>".$row->ausbildungcode."</HoechsteAbgeschlosseneAusbildung>";
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".addslashes($row->mitarbeiter_uid)."' AND habilitation=true;";
		if($resultvw=$db->db_query($qryvw))
		{
			if($db->db_num_rows($resultvw)>0)
			{
				$person_content.="
       <Habilitation>J</Habilitation>";
			}
			else
			{
				$person_content.="
       <Habilitation>N</Habilitation>";
			}
		}
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid='".addslashes($row->mitarbeiter_uid)."' AND (ende is null OR ende>'$bisprevious') AND (beginn<'$bisdatum' OR beginn is null);";
		if($resultvw=$db->db_query($qryvw))
		{
			if($db->db_num_rows($resultvw)>0)
			{
				while($rowvw=$db->db_fetch_object($resultvw))
				{
					if($rowvw->ba1code=='' || $rowvw->ba1code==NULL)
					{
							$error_log.=($error_log!=''?', ':'')."Beschaeftigungsart1 ('".$rowvw->ba1code."')";
					}
					if($rowvw->ba2code=='' || $rowvw->ba2code==NULL)
					{
							$error_log.=($error_log!=''?', ':'')."Beschaeftigungsart2 ('".$rowvw->ba2code."')";
					}
					if($rowvw->beschausmasscode=='' || $rowvw->beschausmasscode==NULL)
					{
							$error_log.=($error_log!=''?', ':'')."BeschaeftigungsAusmass ('".$rowvw->beschausmasscode."')";
					}
					if($rowvw->verwendung_code=='' || $rowvw->verwendung_code==NULL)
					{
							$error_log.=($error_log!=''?', ':'')."VerwendungsCode ('".$rowvw->verwendung_code."')";
					}
					if(!$rowvw->hauptberuflich && ($rowvw->hauptberufcode=='' || $rowvw->hauptberufcode==NULL))
					{
							$error_log.=($error_log!=''?', ':'')."Hauptberuf ('".$rowvw->hauptberufcode."')";
					}
					if($rowvw->ba1code==3)
					{
						$echt++;
					}
					if($rowvw->ba1code==4)
					{
						$frei++;
					}
					
					$person_content.="
       <Verwendung>
              <BeschaeftigungsArt1>".$rowvw->ba1code."</BeschaeftigungsArt1>
              <BeschaeftigungsArt2>".$rowvw->ba2code."</BeschaeftigungsArt2>
              <BeschaeftigungsAusmass>".$rowvw->beschausmasscode."</BeschaeftigungsAusmass>
              <VerwendungsCode>".$rowvw->verwendung_code."</VerwendungsCode>";
					//Studiengangsleiter
					$qryslt="SELECT 
								tbl_benutzerfunktion.*, tbl_studiengang.studiengang_kz 
							FROM public.tbl_benutzerfunktion JOIN public.tbl_studiengang USING(oe_kurzbz) 
							WHERE 
								uid='".addslashes($row->mitarbeiter_uid)."' 
								AND funktion_kurzbz='Leitung' 
								AND (datum_von<'$bisdatum' OR datum_von is null) 
								AND (datum_bis>'$bisprevious' OR datum_bis is NULL)
								AND studiengang_kz<10000;";
					if($resultslt=$db->db_query($qryslt))
					{
						while($rowslt=$db->db_fetch_object($resultslt))
						{
							if($rowslt->studiengang_kz=='' || $rowslt->studiengang_kz==NULL)
							{
									$error_log=($error_log!=''?', ':'')."StgKz(Leitung) ('".$rowslt->studiengang_kz."')";
							}
							if(!in_array($rowslt->studiengang_kz, $nichtmelden))
							{
							$person_content.="
	                     <StgLeitung>
	                          <StgKz>".sprintf("%04s",$rowslt->studiengang_kz)."</StgKz>
	                     </StgLeitung>";
							}
						}
					}
					//Funktionen
					$qryfkt="SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".$rowvw->bisverwendung_id."' AND studiengang_kz>0 AND studiengang_kz<10000;";
					if($resultfkt=$db->db_query($qryfkt))
					{
						while($rowfkt=$db->db_fetch_object($resultfkt))
						{
							if($rowfkt->studiengang_kz=='' || $rowfkt->studiengang_kz==NULL)
							{
									$error_log.=($error_log!=''?', ':'')."StgKz(Funktion) ('".$rowfkt->studiengang_kz."')";
							}
							if($rowfkt->sws=='' || $rowfkt->sws==NULL)
							{
									$error_log.=($error_log!=''?', ':'')."SWS ('".$rowfkt->sws."')";
							}
							if($rowvw->hauptberuflich=='' || $rowvw->hauptberuflich==NULL)
							{
									$error_log.=($error_log!=''?', ':'')."Hauptberuflich ('".$rowvw->hauptberuflich."')";
							}
							if(($rowvw->hauptberufcode=='' || $rowvw->hauptberufcode==NULL) && $rowvw->hauptberuflich=='f')
							{
									$error_log.=($error_log!=''?', ':'')."HauptberufCode ('".$rowvw->hauptberufcode."')";
							}
							if (isset($eteam[$rowfkt->studiengang_kz]))
							{
								if(($eteam[$rowfkt->studiengang_kz]=='' || $eteam[$rowfkt->studiengang_kz]==NULL))
								{
										$error_log.=($error_log!=''?', ':'')."BesondereQualifikationCode ('".$eteam[$rowfkt->studiengang_kz]."')";
								}
							}
							$person_content.="
	                    <Funktion>
	                       <StgKz>".sprintf("%04s",$rowfkt->studiengang_kz)."</StgKz>
	                       <SWS>".$rowfkt->sws."</SWS>";
							if($rowvw->hauptberuflich=='t')
							{
								$person_content.="
	                       <Hauptberuflich>J</Hauptberuflich>";
							}
							else
							{
								$person_content.="
	                       <Hauptberuflich>N</Hauptberuflich>
	                       <HauptberufCode>".$rowvw->hauptberufcode."</HauptberufCode>";
							}
							if(isset($eteam[$rowfkt->studiengang_kz]))
							{
								$person_content.="
	                       <Entwicklungsteam>J</Entwicklungsteam>
	                       <BesondereQualifikationCode>".$eteam[$rowfkt->studiengang_kz]."</BesondereQualifikationCode>";
							}
							else
							{
								$person_content.="
	                       <Entwicklungsteam>N</Entwicklungsteam>";
							}
						$person_content.="
	                    </Funktion>";
						}
					}
					$person_content.="
       </Verwendung>";
				}
			}
			else 
			{
				//Keine Verwendung
				$v.="<br><u>$row->mitarbeiter_uid</u> hat keine Verwendung und wird ausgelassen<br>";
				$error_person = true;
			}
		}
		$mitarbeiterzahl++;
			$person_content.="
     </Person>";
		if($error_log!='' || $error_log1!='')
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
		else 
		{
			if(!$error_person)
				$datei.=$person_content;
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
echo "<H1>BIS - Mitarbeiterdaten werden &uuml;berpr&uuml;ft (f&uuml;r Meldung ".$stsem." / $bisprevious - $bisdatum)</H1><br>";
echo "Anzahl Mitarbeiter: Gesamt: ".$mitarbeiterzahl." / echter Dienstvertrag: ".$echt." / freier Dienstvertrag: ".$frei."<br><br>";
echo "<H2>Nicht plausible BIS-Daten</H2><br>";
echo nl2br($v."<br><br>");

//Tabelle mit Ergebnissen ausgeben

$ddd='bisdaten/bismeldung_mitarbeiter.xml';
$dateiausgabe=fopen($ddd,'w');
fwrite($dateiausgabe,$datei);
fclose($dateiausgabe);

echo "<a href=$ddd>XML-Datei f&uuml;r Mitarbeiter-BIS-Meldung</a><br><br>";
?>