<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
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
$fehler='';
$maxsemester=0;
$v='';
$studiensemester=new studiensemester();
$ssem=$studiensemester->getaktorNext();
$psem=$studiensemester->getPrevious();
$zaehl=0;
$erhalter='';
$stgart='';
$orgform='';
$status='';
$datei='';
$aktstatus='';
$aktstatus_datum='';
$mob='';
$gast='';
$avon='';
$abis='';
$zweck='';
$bewerberM=array();
$bewerberW=array();
$bsem=array();
$stsem=array();
$usem=array();
$asem=array();
$absem=array();
$iosem=array();
$bsema=array();
$stsema=array();
$usema=array();
$asema=array();
$absema=array();
$iosema=array();
$bewerbercount=0;
$bewerbercountbb=0;
$bewerbercountvz=0;
$bewerberM1=array();
$bewerberW1=array();
$bsem1=array();
$stsem1=array();
$bewerberM2=array();
$bewerberW2=array();
$bsem2=array();
$stsem2=array();
$datei1='';
$datei2='';
$stgorg="";
$tabelle='';
$stlist='';
$bwlist='';
$plausi='';
$storgfor='';
$sponsion='';

//Beginn- und Endedatum des aktuellen Semesters
$qry="SET client_encoding TO Unicode;SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='".$ssem."';";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$beginn=$row->start;
		$ende=$row->ende;
	}
}
//Ermittlung aktuelles und letztes BIS-Meldedatum
if(mb_strstr($ssem,"WS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
}
elseif(mb_strstr($ssem,"SS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
	$bisprevious=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")-1));
}
else
{
	echo "Ung&uuml;ltiges Semester!";
}
//ausgewählter Lehrgang
if(isset($_GET['stg_kz']))
{
	if($_GET['stg_kz']<0)
	{
		$stg_kz=$_GET['stg_kz'];
	}
	else 
	{
		echo "<H2>Es wurde kein Lehrgang ausgew&auml;hlt!</H2>";
	}
}
else
{
	$stg_kz=228;
	//echo "<H2>Es wurde kein Lehrgang ausgew&auml;hlt!</H2>";
	//exit;
}
function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}
//plausicheck
if(isset($_GET['plausi']))
{
	$plausi=$_GET['plausi'];
}
$datumobj=new datum();

//Lehrgangsdaten auslesen
$qry="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='".$stg_kz."'";
if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		$stgart=$row->typ;
		$stgemail=$row->email;
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
		if($row->lgartcode<'1' || $row->lgartcode>'3')
		{
			echo "<H2>Es wurde keine gültige Lehrgangart ausgew&auml;hlt!</H2>";
			exit;
		}
	}
}

//Hauptselect
$qry="SELECT DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id, to_char(gebdatum, 'ddmmyy') AS vdat
	FROM public.tbl_student
	JOIN public.tbl_benutzer ON(student_uid=uid)
	JOIN public.tbl_person USING (person_id)
	JOIN public.tbl_prestudent USING (prestudent_id)
	JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
	WHERE bismelden IS TRUE
	AND tbl_student.studiengang_kz='".$stg_kz."'
	AND (((tbl_prestudentstatus.studiensemester_kurzbz='".$ssem."') AND (tbl_prestudentstatus.datum<='".$bisdatum."')
		AND (status_kurzbz='Student' OR status_kurzbz='Outgoing'
		OR status_kurzbz='Praktikant' OR status_kurzbz='Diplomand' OR status_kurzbz='Absolvent'
		OR status_kurzbz='Abbrecher' OR status_kurzbz='Unterbrecher'))
		OR ((tbl_prestudentstatus.studiensemester_kurzbz='".$psem."') AND (status_kurzbz='Absolvent'
		OR status_kurzbz='Abbrecher') AND tbl_prestudentstatus.datum>'".$bisprevious."')
		OR (status_kurzbz='Incoming' AND student_uid IN (SELECT student_uid FROM bis.tbl_bisio WHERE (tbl_bisio.bis>='".$bisprevious."')
			OR (tbl_bisio.von<'".$bisdatum."' AND (tbl_bisio.bis>='".$bisdatum."'  OR tbl_bisio.bis IS NULL))
	)))
	ORDER BY student_uid, nachname, vorname
	";

if($result = $db->db_query($qry))
{

	$datei.="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
  <ErhKz>".$erhalter."</ErhKz>
  <MeldeDatum>".date("dmY", $datumobj->mktime_fromdate($bisdatum))."</MeldeDatum>
  <LehrgangMeldung>
      <LehrgangStamm>
         <LehrgangNr>".($stg_kz*(-1))."</LehrgangNr>
         <LehrgangArtCode>".$row->lgartcode."</LehrgangArtCode>";
	while($row = $db->db_fetch_object($result))
  	{
      	//Plausichecks
		$qryadr="SELECT * from public.tbl_adresse WHERE heimatadresse IS TRUE AND person_id='".$row->pers_id."';";
		$results=$db->db_query($qryadr);

		if($anz=$db->db_num_rows($results)!=1)
		{
			$error_log1="Es sind ".$anz." Heimatadressen eingetragen\n";
		}
		if($rowadr=$db->db_fetch_object($results))
		{
			$plz=$rowadr->plz;
			$gemeinde=$rowadr->gemeinde;
			$strasse=$rowadr->strasse;
			$nation=$rowadr->nation;
		}
		else
		{
			$plz='';
			$gemeinde='';
			$strasse='';
			$nation='';
		}
		if($row->gebdatum<'1920-01-01' OR $row->gebdatum==null OR $row->gebdatum=='')
		{
				$error_log.=(!empty($error_log)?', ':'')."Geburtsdatum ('".$row->gebdatum."')";
		}
		if($row->geschlecht!='m' && $row->geschlecht!='w')
		{
				$error_log.=(!empty($error_log)?', ':'')."Geschlecht ('".$row->geschlecht."')";
		}
		if($row->vorname=='' || $row->vorname==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Vorname ('".$row->vorname."')";
		}
		if($row->nachname=='' || $row->nachname==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Nachname ('".$row->nachname."')";
		}
		//SVNR muß¸ 10-stellig sein
		if($row->svnr!='' && $row->svnr!=null && mb_strlen(trim($row->svnr))!=10)
		{
				$error_log.=(!empty($error_log)?', ':'')."SVNR ('".trim($row->svnr)."') ist nicht 10 Zeichen lang";
		}
		//Ersatzkennzeichen muß 10-stellig sein
		if($row->ersatzkennzeichen!='' && $row->ersatzkennzeichen!=null && mb_strlen(trim($row->ersatzkennzeichen))!=10)
		{
				$error_log.=(!empty($error_log)?', ':'')."Ersatzkennzeichen ('".trim($row->ersatzkennzeichen)."') ist nicht 10 Zeichen lang";
		}
		//Vergleich der letzten 6 Stellen der SVNR mit Geburtsdatum - ausser bei 01.01. und 01.07.
		if($row->svnr!='' && $row->svnr!=null && substr($row->svnr,4,6)!=$row->vdat && substr($row->vdat,0,4)!='0101' && substr($row->vdat,0,4)!='0107')
		{
				$error_log.=(!empty($error_log)?', ':'')."SVNR ('".$row->svnr."') enth&auml;lt Geburtsdatum (".$row->gebdatum.") nicht";
		}
		//Vergleich der letzten 6 Stellen des Ersatzkennzeichen mit Geburtsdatum
		if($row->ersatzkennzeichen!='' && $row->ersatzkennzeichen!=null && substr($row->ersatzkennzeichen,4,6)!=$row->vdat)
		{
				$error_log.=(!empty($error_log)?', ':'')."Ersatzkennzeichen ('".$row->ersatzkennzeichen."') enth&auml;lt Geburtsdatum (".$row->gebdatum.") nicht";
		}
		if(($row->svnr=='' || $row->svnr==null)&&($row->ersatzkennzeichen=='' || $row->ersatzkennzeichen==null))
		{
				$error_log.=(!empty($error_log)?', ':'')."SVNR ('".$row->svnr."') bzw. ErsKz ('".$row->ersatzkennzeichen."') fehlt";
		}
		if($row->staatsbuergerschaft=='' || $row->staatsbuergerschaft==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Staatsb&uuml;rgerschaft ('".$row->staatsbuergerschaft."')";
		}
		if($plz=='' || $plz==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Heimat-PLZ ('".$plz."')";
		}
		if($gemeinde=='' || $gemeinde==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Heimat-Gemeinde ('".$gemeinde."')";
		}
		if($strasse=='' || $strasse==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."Heimat-Strasse ('".$strasse."')";
		}
		if($nation=='' || $nation==null)
		{

				$error_log.=(!empty($error_log)?', ':'')."Heimat-Nation ('".$nation."')";
		}
		if($row->zgv_code=='' || $row->zgv_code==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."ZugangCode ('".$row->zgv_code."')";
		}
		if($row->zgvdatum=='' || $row->zgvdatum==null)
		{
				$error_log.=(!empty($error_log)?', ':'')."ZugangDatum ('".$row->zgvdatum."')";
		}
		else
		{
			if($row->zgvdatum>date('Y-m-d'))
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangDatum liegt in der Zukunft ('".$row->zgvdatum."')";
			}
			if($row->zgvdatum<$row->gebdatum)
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangDatum ('".$row->zgvdatum."') kleiner als Geburtsdatum ('".$row->gebdatum."')";
			}
		}
		if($stgart==2)
		{
			if($row->zgvmas_code=='' || $row->zgvmas_code==null)
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangMagStgCode ('".$row->zgvmas_code."')";
			}
			if($row->zgvmadatum=='' || $row->zgvmadatum==null)
			{
					$error_log.=(!empty($error_log)?', ':'')."ZugangMagStgDatum ('".$row->zgvmadatum."')";
			}
			else
			{
				if($row->zgvmadatum>date("Y-m-d"))
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMagStgDatum liegt in der Zukunft ('".$row->zgvmadatum."')";
				}
				if($row->zgvmadatum<$row->zgvdatum)
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMagStgDatum ('".$row->zgvmadatum."') kleiner als Zugangdatum ('".$row->zgvdatum."')";
				}
				if($row->zgvmadatum<$row->gebdatum)
				{
						$error_log.=(!empty($error_log)?', ':'')."ZugangMagStgDatum ('".$row->zgvmadatum."') kleiner als Geburtsdatum ('".$row->gebdatum."')";
				}
			}
		}
		//Bestimmen der aktuellen Prestudentrolle (Status) und des akt. Ausbildungssemesters des Studenten
		$qrystatus="SELECT * FROM public.tbl_prestudentstatus
		WHERE prestudent_id='".$row->prestudent_id."' AND studiensemester_kurzbz='".$ssem."'
		AND (tbl_prestudentstatus.datum<'".$bisdatum."')
		ORDER BY datum desc, insertamum desc, ext_id desc;";
		if($resultstatus = $db->db_query($qrystatus))
		{
			if($db->db_num_rows($resultstatus)>0)
			{
				if($rowstatus = $db->db_fetch_object($resultstatus))
				{
					$qry1="SELECT count(*) AS dipl FROM public.tbl_prestudentstatus WHERE prestudent_id='".$row->prestudent_id."' AND status_kurzbz='Diplomand'";
					if($result1 = $db->db_query($qry1))
					{
						if($row1 = $db->db_fetch_object($result1))
						{
							$sem=$rowstatus->ausbildungssemester;
							if($sem>$maxsemester)
							{
								$sem=$maxsemester;
							}
							if($row1->dipl>1)
							{
								$sem=50;
							}
							if($row1->dipl>3)
							{
								$sem=60;
							}
						}
					}
					if($rowstatus->status_kurzbz=="Student" || $rowstatus->status_kurzbz=='Praktikant'
						|| $rowstatus->status_kurzbz=="Diplomand")
					{
						$status=1;
					}
					else if($rowstatus->status_kurzbz=="Unterbrecher" )
					{
						$status=2;
					}
					else if($rowstatus->status_kurzbz=="Absolvent" )
					{
						$status=3;
					}
					else if($rowstatus->status_kurzbz=="Abbrecher" )
					{
						$status=4;
					}
					else
					{
						$error_log='';
						$error_log1='';
						continue;
					}
					$aktstatus=$rowstatus->status_kurzbz;
					$aktstatus_datum=$rowstatus->datum;
					$storgform=$rowstatus->orgform_kurzbz;
				}
			}
			else
			{
				$qrystatus="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='".$row->prestudent_id."' AND studiensemester_kurzbz='".$psem."' AND (tbl_prestudentstatus.datum<'".$bisdatum."') ORDER BY datum desc, insertamum desc, ext_id desc;";
				if($resultstatus = $db->db_query($qrystatus))
				{
					if($rowstatus = $db->db_fetch_object($resultstatus))
					{
						$qry1="SELECT count(*) AS dipl FROM public.tbl_prestudentstatus WHERE prestudent_id='".$row->prestudent_id."' AND status_kurzbz='Diplomand'";
						if($result1 = $db->db_query($qry1))
						{
							if($row1 = $db->db_fetch_object($result1))
							{
								$sem=$rowstatus->ausbildungssemester;
								if($sem>$maxsemester)
								{
									$sem=$maxsemester;
								}
								if($row1->dipl>1)
								{
									$sem=50;
								}
								if($row1->dipl>3)
								{
									$sem=60;
								}
							}
						}
						if($rowstatus->status_kurzbz=="Absolvent" )
						{
							$status=3;
						}
						else if($rowstatus->status_kurzbz=="Abbrecher" )
						{
							$status=4;
						}
						else
						{
							$error_log='';
							$error_log1='';
							continue;
						}
						$aktstatus=$rowstatus->status_kurzbz;
						$aktstatus_datum=$rowstatus->datum;
						//$storgform=$rowstatus->orgform_kurzbz;
					}
				}
			}
		}
		//bei Absolventen das Beendigungsdatum (Sponsion oder Abschlussprüfung) überprüfen

		if($aktstatus=='Absolvent')
		{
			$qry_ap="SELECT * FROM lehre.tbl_abschlusspruefung WHERE student_uid='".$row->student_uid."' AND abschlussbeurteilung_kurzbz!='nicht' AND abschlussbeurteilung_kurzbz IS NOT NULL";
	      		if($result_ap = $db->db_query($qry_ap))
			{
				$ap=0;
				while($row_ap = $db->db_fetch_object($result_ap))
				{
					if($row_ap->datum=='' || $row_ap->datum==null)
					{
							$error_log.=(!empty($error_log)?', ':'')."Datum der Abschlusspr&uuml;fung ('".$row_ap->datum."')";
					}
					if($row_ap->sponsion=='' || $row_ap->sponsion==null)
					{
							$error_log.=(!empty($error_log)?', ':'')."Datum der Sponsion ('".$row_ap->sponsion."')";
					}
					$ap++;
					$sponsion=$row_ap->sponsion;
				}
				if($ap!=1)
				{
						$error_log.=(!empty($error_log)?', ':'').$ap." bestandene Abschlusspr&uuml;fungen";
				}
			}
		}
		if($error_log!='' OR $error_log1!='')
		{
			//Ausgabe der fehlenden Daten
			$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($row->status_kurzbz): </u>\n";
			if($error_log!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fehler: ".$error_log."\n";
			}
			if($error_log1!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$error_log1;
			}
			$zaehl++;
			$v.="\n";
			$error_log='';
			$error_log1='';
			continue;
		}
		else
		{
			//Erstellung der XML-Datei
				$datei.="
	 <LehrgangTeilnehmerIn>
            	  <PersKz>".trim($row->matrikelnr)."</PersKz>
            	  <GeburtsDatum>".date("dmY", $datumobj->mktime_fromdate($row->gebdatum))."</GeburtsDatum>
            	  <Geschlecht>".strtoupper($row->geschlecht)."</Geschlecht>";
				if(($row->svnr!='')&&($row->ersatzkennzeichen!=''))
				{
					//$datei.="
		          //<Vorname>".$row->vorname."</Vorname>
		          //<Familienname>".$row->nachname."</Familienname>";
					$datei.="
		          <SVNR>".$row->svnr."</SVNR>";
					$datei.="
		          <ErsKz>".$row->ersatzkennzeichen."</ErsKz>";
				}
				else
				{
					if($row->svnr!='')
					{
						$datei.="
                  <SVNR>".$row->svnr."</SVNR>";
					}
					if($row->ersatzkennzeichen!='')
					{
						//$datei.="
                  //<Vorname>".$row->vorname."</Vorname>
                  //<Familienname>".$row->nachname."</Familienname>";
						$datei.="
                  <ErsKz>".$row->ersatzkennzeichen."</ErsKz>";
					}
				}
				$datei.="
	          <StaatsangehoerigkeitCode>".$row->staatsbuergerschaft."</StaatsangehoerigkeitCode>
	          <HeimatPLZ>".$plz."</HeimatPLZ>
	          <HeimatGemeinde>".$gemeinde."</HeimatGemeinde>
	          <HeimatStrasse><![CDATA[".$strasse."]]></HeimatStrasse>
	          <HeimatNation>".$nation."</HeimatNation>
	          <ZugangCode>".$row->zgv_code."</ZugangCode>";
	          			if($row->zgvdatum!=null)
				{
					$datei.="
	          <ZugangDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvdatum))."</ZugangDatum>";
				}
				else
				{
					$datei.="
	          <ZugangDatum></ZugangDatum>";
				}
				//!!!stgart für Lehrgang überprüfen!!!
			          if($stgart==2)
			          {
			          		$datei.="
	          <ZugangMasterCode>".$row->zgvmas_code."</ZugangMasterCode>";
	          				if($row->zgvmadatum!=null)
					{
			          			$datei.="
	          <ZugangMasterDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvmadatum))."</ZugangMasterDatum>";
					}
					else
					{
						$datei.="
		<ZugangMasterDatum></ZugangMasterDatum>";
					}
			          }
			          $qryad="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='".$row->prestudent_id."' AND (status_kurzbz='Student'  OR status_kurzbz='Unterbrecher')  AND (tbl_prestudentstatus.datum<'".$bisdatum."') ORDER BY datum asc;";
			          if($resultad = $db->db_query($qryad))
				{
					if($rowad = $db->db_fetch_object($resultad))
					{
	          			$datei.="
	          <BeginnDatum>".date("dmY", $datumobj->mktime_fromdate($rowad->datum))."</BeginnDatum>";
					}
				}
	          			if($aktstatus=='Absolvent')
				{
					$datei.="
	          <BeendigungsDatum>".date("dmY", $datumobj->mktime_fromdate($aktstatus_datum))."</BeendigungsDatum>";
				}
				$datei.="
	          <TeilnehmerStatusCode>".$status."</TeilnehmerStatusCode>
	 </LehrgangTeilnehmerIn>";				
		}
	}
	$datei.="
     </LehrgangStamm>
  </LehrgangMeldung>
</Erhalter>";
	echo '	<html><head><title>BIS - Lehrgangsmeldung - ('.$stg_kz.')</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head><body>';
	echo "<H1>BIS - Studentendaten werden &uuml;berpr&uuml;ft! Lehrgang: ".$stg_kz."</H1>\n";
	if(strlen(trim($v))>0)
	{
		echo "<H2>Nicht plausible BIS-Daten (f&uuml;r Meldung ".$ssem."): </H2><br>";
		echo nl2br($v."\n\n");	
	}
	$ddd='bisdaten/bismeldung_'.$ssem.'_Lehrgang'.$stg_kz.'.xml';
	if(strtoupper($plausi)!='J')
	{
		$dateiausgabe=fopen($ddd,'w');
		fwrite($dateiausgabe,$datei);
		fclose($dateiausgabe);
	}	
	if(file_exists($ddd))
	{
		echo "<a href=$ddd>XML-Datei f&uuml;r BIS-Meldung Lehrgang ".$stg_kz."</a><br>";
	}

	//echo $datei;
	
}

?>