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
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('mitarbeiter/stammdaten',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$error_log='';
$error_log1='';
$error_log_all="";
$mitarbeiter_data=array();
$mitarbeiter_gesamt=array();
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
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 9, 1, $jahr));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 9, 1, $jahr-1));
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
		AND (tbl_bisverwendung.ende is NULL OR tbl_bisverwendung.ende>".$db->db_add_param($bisprevious).")
	ORDER BY uid, nachname,vorname
	";

if($result = $db->db_query($qry))
{
	
	$datei.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
   <ErhKz>".$erhalter."</ErhKz>
   <MeldeDatum>".date("dmY", mktime(0, 0, 0, 11, 15, $jahr))."</MeldeDatum>
   <PersonalMeldung>";
	while($row = $db->db_fetch_object($result))
	{
		$mitarbeiter_data=array();
		
		$error_person = false;
		$person_content='';
		$qryet="SELECT * FROM bis.tbl_entwicklungsteam WHERE mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid).";";
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
		$mitarbeiter_data['uid']=$row->uid;
		$mitarbeiter_data['personalnummer']=sprintf("%015s",$row->personalnummer);
		$mitarbeiter_data['vorname']=$row->vorname;
		$mitarbeiter_data['nachname']=$row->nachname;
		
		$person_content.="
     <Person>
      <PersonalNummer>".sprintf("%015s",$row->personalnummer)."</PersonalNummer>
      <GeburtsDatum>".date("dmY", $datumobj->mktime_fromdate($row->gebdatum))."</GeburtsDatum>
      <Geschlecht>".strtoupper($row->geschlecht)."</Geschlecht>
      <HoechsteAbgeschlosseneAusbildung>".$row->ausbildungcode."</HoechsteAbgeschlosseneAusbildung>";
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)." AND habilitation=true;";
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
		$qryvw="SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)." AND (ende is null OR ende>".$db->db_add_param($bisprevious).") AND (beginn<".$db->db_add_param($bisdatum)." OR beginn is null);";
		if($resultvw=$db->db_query($qryvw))
		{
			if($db->db_num_rows($resultvw)>0)
			{
				$verwendung_data=array();
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
					$key = $rowvw->ba1code.'/'.$rowvw->ba2code.'/'.$rowvw->beschausmasscode.'/'.$rowvw->verwendung_code;
					$verwendung_data[$key]['ba1code']=$rowvw->ba1code;
					$verwendung_data[$key]['ba2code']=$rowvw->ba2code;
					$verwendung_data[$key]['beschausmasscode']=$rowvw->beschausmasscode;
					$verwendung_data[$key]['verwendung_code']=$rowvw->verwendung_code;
					
					
					
					//Studiengangsleiter
					$qryslt="SELECT 
								tbl_benutzerfunktion.*, tbl_studiengang.studiengang_kz 
							FROM public.tbl_benutzerfunktion JOIN public.tbl_studiengang USING(oe_kurzbz) 
							WHERE 
								uid=".$db->db_add_param($row->mitarbeiter_uid)." 
								AND funktion_kurzbz='Leitung' 
								AND (datum_von<".$db->db_add_param($bisdatum)." OR datum_von is null) 
								AND (datum_bis>".$db->db_add_param($bisprevious)." OR datum_bis is NULL)
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
								$verwendung_data[$key]['stgltg'][]=$rowslt->studiengang_kz;
							}
						}
					}
					//Funktionen
					$qryfkt="SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id=".$db->db_add_param($rowvw->bisverwendung_id)." AND studiengang_kz>0 AND studiengang_kz<10000;";
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
							
							$verwfkt_found=false;

							// Wenn mehrere Verwendungen vorhanden sind fuer und funktionen fuer die gleichen Studiengaenge
							// dann muessen diese zusammengezaehlt werden
							if(isset($verwendung_data[$key]['fkt']) && is_array($verwendung_data[$key]['fkt']))
							{
								foreach($verwendung_data[$key]['fkt'] as $key_verwfkt=>$row_verwfkt)
								{
									if($row_verwfkt['stgkz']==$rowfkt->studiengang_kz)
									{
										$verwendung_data[$key]['fkt'][$key_verwfkt]['sws']+=$rowfkt->sws;
										$verwfkt_found=true;
										break;
									}
								}
							}
							if(!$verwfkt_found)
							{
							$verwendung_data[$key]['fkt'][] = array(
								'stgkz'=>$rowfkt->studiengang_kz,
								'sws'=>$rowfkt->sws,
								'hauptberuflich'=>$rowvw->hauptberuflich,
								'hauptberufcode'=>$rowvw->hauptberufcode);
							}
						}
					}
				}
				
				//Verwendungen ausgeben
				foreach($verwendung_data as $row_verwendung)
				{
					$person_content.="
       <Verwendung>
              <BeschaeftigungsArt1>".$row_verwendung['ba1code']."</BeschaeftigungsArt1>
              <BeschaeftigungsArt2>".$row_verwendung['ba2code']."</BeschaeftigungsArt2>
              <BeschaeftigungsAusmass>".$row_verwendung['beschausmasscode']."</BeschaeftigungsAusmass>
              <VerwendungsCode>".$row_verwendung['verwendung_code']."</VerwendungsCode>";
					
					if(isset($row_verwendung['stgltg']))
					{
						foreach($row_verwendung['stgltg'] as $row_stgl)
						{
							$person_content.="
		                     <StgLeitung>
		                          <StgKz>".sprintf("%04s",$row_stgl)."</StgKz>
		                     </StgLeitung>";
						}
					}
					if(isset($row_verwendung['fkt']))
					{
						foreach($row_verwendung['fkt'] as $row_fkt)
						{
							$person_content.="
		                    <Funktion>
		                       <StgKz>".sprintf("%04s",$row_fkt['stgkz'])."</StgKz>
		                       <SWS>".$row_fkt['sws']."</SWS>";
								if($row_fkt['hauptberuflich']=='t')
								{
									$person_content.="
		                       <Hauptberuflich>J</Hauptberuflich>";
								}
								else
								{
									$person_content.="
		                       <Hauptberuflich>N</Hauptberuflich>
		                       <HauptberufCode>".$row_fkt['hauptberufcode']."</HauptberufCode>";
								}
								if(isset($eteam[$row_fkt['stgkz']]))
								{
									$person_content.="
		                       <Entwicklungsteam>J</Entwicklungsteam>
		                       <BesondereQualifikationCode>".$eteam[$row_fkt['stgkz']]."</BesondereQualifikationCode>";
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
				$qry_count="SELECT 1 FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid);
				if($result_count=$db->db_query($qry_count))
				{
					if($db->db_num_rows($result_count)==0)
					{
						//Keine Verwendung
						$v.="<u>$row->mitarbeiter_uid</u> hat keine Verwendung und wird ausgelassen<br>";
						$error_person = true;
					}
					else
					{
						//Keine Verwendung im Meldezeitraum
						//$v.="<u>$row->mitarbeiter_uid</u> hat keine Verwendung und wird ausgelassen<br>";
						$error_person = true;
					}
				}
			}		}
		$mitarbeiterzahl++;
			$person_content.="
     </Person>";
		if($error_log!='' || $error_log1!='')
		{
			if($error_person)
				$v.='<span style="color:gray;" >';
			$v.="<u>Bei Mitarbeiter (PersNr, UID, Vorname, Nachname) '".$row->personalnummer."','".$row->mitarbeiter_uid."', '".$row->nachname."', '".$row->vorname."': </u>\n";
			if($error_log!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Es fehlt: ".$error_log."\n";
			}
			$zaehl++;
			$v.="\n";
			$error_log='';
			if($error_person)
				$v.='</span>';
		}
		else 
		{
			if(!$error_person)
			{
				$datei.=$person_content;
				$mitarbeiter_gesamt[]=$mitarbeiter_data;
			}
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

echo '<h2>Folgende Personen werden gemeldet</h2>
Anzahl:'.count($mitarbeiter_gesamt).'
<table>
<thead>
	<tr>
		<th>UID</th>
		<th>Vorname</th>
		<th>Nachname</th>
		<th>Personalnummer</th>
	</tr>
</thead>
<tbody>';

foreach($mitarbeiter_gesamt as $row)
{
	echo '<tr>';
	echo '<td>'.$row['uid'].'</td>';
	echo '<td>'.$row['vorname'].'</td>';
	echo '<td>'.$row['nachname'].'</td>';
	echo '<td>'.$row['personalnummer'].'</td>';
	echo '</tr>';
}
echo '</tbody></table><br>';
echo '<a href="archiv.php?meldung='.$ddd.'&sem='.$stsem.'&typ=mitarbeiter&action=archivieren">Mitarbeiter-BIS-Meldung archivieren</a><br>';
echo "<a href=$ddd>XML-Datei f&uuml;r Mitarbeiter-BIS-Meldung</a><br><br>";
?>
