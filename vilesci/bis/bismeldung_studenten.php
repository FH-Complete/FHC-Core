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
$studiensemester=new studiensemester($conn);
$ssem=$studiensemester->getaktorNext();
$zaehl=0;
$erhalter='';
$stgart='';
$orgform='';
$status='';
$datei='';
$aktstatus='';
$mob='';
$gast='';
$avon='';
$abis='';
$zweck='';
$bewerberM=array();
$bewerberW=array();

$qry="SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='".$ssem."';";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
	{
		$beginn=$row->start;
		$ende=$row->ende;
	}
}
if(strstr($ssem,"WS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 11, 15, date("Y")));
}
elseif(strstr($ssem,"SS"))
{
	$bisdatum=date("Y-m-d",  mktime(0, 0, 0, 04, 15, date("Y")));
}
else 
{
	echo "Ungültiges Semester!";
}
if(isset($_GET['stg_kz']))
{
	$stg_kz=$_GET['stg_kz'];
}
else 
{
	//$stg_kz=0;
	echo "<H2>Es wurde kein Studiengang ausgewählt!</H2>";
	exit;
}
function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$datumobj=new datum();

$qry="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='".$stg_kz."'";
if($result = pg_query($conn, $qry))
{
	if($row = pg_fetch_object($result))
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
		if($row->typ=='b')
		{
			$stgart=1;
		}
		elseif($row->typ=='m')
		{
			$stgart=2;
		}
		elseif($row->typ=='d')
		{
			$stgart=3;
		}
		else 
		{
			exit;
		}
		if($row->organisationsform=='n')
		{
			$orgform=1;
		}
		elseif($row->organisationsform=='b')
		{
			$orgform=2;
		}
		else 
		{
			exit;
		}
	}
}

$qry="SELECT DISTINCT ON(student_uid, nachname, vorname) *, public.tbl_person.person_id AS pers_id, tbl_abschlusspruefung.datum AS abdatum 
	FROM public.tbl_student 
	JOIN public.tbl_benutzer ON(student_uid=uid) 
	JOIN public.tbl_person USING (person_id) 
	JOIN public.tbl_prestudent USING (prestudent_id)
	JOIN public.tbl_prestudentrolle ON(tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id) 
	JOIN public.tbl_adresse ON(tbl_person.person_id=tbl_adresse.person_id)
	LEFT JOIN lehre.tbl_abschlusspruefung USING(student_uid) 
	WHERE heimatadresse IS TRUE AND bismelden IS TRUE 
	AND (studiensemester_kurzbz='".$ssem."') AND tbl_student.studiengang_kz='".$stg_kz."'
	AND (rolle_kurzbz='Student' OR rolle_kurzbz='Incoming' OR rolle_kurzbz='Outgiong'
		OR rolle_kurzbz='Praktikant' OR rolle_kurzbz='Diplomand' OR rolle_kurzbz='Absolvent' 
		OR rolle_kurzbz='Abbrecher' OR rolle_kurzbz='Unterbrecher') 
	ORDER BY student_uid, nachname, vorname
	";

if($result = pg_query($conn, $qry))
{
	
$datei.="
<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Erhalter>
  <ErhKz>".$erhalter."</ErhKz>
  <MeldeDatum>".date("dmY", $datumobj->mktime_fromdate($bisdatum))."</MeldeDatum>
  <StudierendenBewerberMeldung>
    <StudiengangStamm>
      <StgKz>".$stg_kz."</StgKz>
      <StgArtCode>".$stgart."</StgArtCode>
      <OrgFormCode>".$orgform."</OrgFormCode>
      <StudiengangDetail>
        <OrgFormTeilCode>1</OrgFormTeilCode>
        <StgStartSemCode>1</StgStartSemCode>
";
	while($row = pg_fetch_object($result))
	{
		$qryadr="SELECT * from public.tbl_adresse WHERE heimatadresse IS TRUE AND person_id='".$row->pers_id."';";
		if(pg_num_rows(pg_query($conn,$qryadr))!=1)
		{
			$error_log1="Es sind ".pg_num_rows(pg_query($conn,$qryadr))." Heimatadressen eingetragen\n";
		}
		if($row->gebdatum<'1920-01-01' OR $row->gebdatum==null OR $row->gebdatum=='')
		{
			if($error_log!='')
			{
				$error_log=", Geburtsdatum ('".$row->gebdatum."')";
			}
			else 
			{
				$error_log.="Geburtsdatum ('".$row->gebdatum."')";
			}
		}
		if($row->geschlecht!='m' && $row->geschlecht!='w')
		{
			if($error_log!='')
			{
				$error_log.=", Geschlecht ('".$row->geschlecht."')";
			}
			else 
			{
				$error_log.="Geschlecht ('".$row->geschlecht."')";
			}
		}
		if($row->vorname=='' || $row->vorname==null)
		{
			if($error_log!='')
			{
				$error_log.=", Vorname ('".$row->vorname."')";
			}
			else 
			{
				$error_log.="Vorname ('".$row->vorname."')";
			}
		}
		if($row->nachname=='' || $row->nachname==null)
		{
			if($error_log!='')
			{
				$error_log.=", Nachname ('".$row->nachname."')";
			}
			else 
			{
				$error_log.="Nachname ('".$row->nachname."')";
			}
		}
		if(($row->svnr=='' || $row->svnr==null)&&($row->ersatzkennzeichen=='' || $row->ersatzkennzeichen==null))
		{
			if($error_log!='')
			{
				$error_log.=", SVNR ('".$row->svnr."') bzw. Erskz ('".$row->ersatzkennzeichen."')";
			}
			else 
			{
				$error_log.="SVNR ('".$row->svnr."') bzw. Erskz ('".$row->ersatzkennzeichen."')";
			}
		}
		if($row->staatsbuergerschaft=='' || $row->staatsbuergerschaft==null)
		{
			if($error_log!='')
			{
				$error_log.=", Staatsbürgerschaft ('".$row->staatsbuergerschaft."')";
			}
			else 
			{
				$error_log.="Staatsbürgerschaft ('".$row->staatsbuergerschaft."')";
			}
		}
		if($row->plz=='' || $row->plz==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-PLZ ('".$row->plz."')";
			}
			else 
			{
				$error_log.="Heimat-PLZ ('".$row->plz."')";
			}
		}
		if($row->gemeinde=='' || $row->gemeinde==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Gemeinde ('".$row->gemeinde."')";
			}
			else 
			{
				$error_log.="Heimat-Gemeinde ('".$row->gemeinde."')";
			}
		}
		if($row->strasse=='' || $row->strasse==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Strasse ('".$row->strasse."')";
			}
			else 
			{
				$error_log.="Heimat-Strasse ('".$row->strasse."')";
			}
		}
		if($row->nation=='' || $row->nation==null)
		{
			if($error_log!='')
			{
				$error_log.=", Heimat-Nation ('".$row->nation."')";
			}
			else 
			{
				$error_log.="Heimat-Nation ('".$row->nation."')";
			}
		}
		if($row->zgv_code=='' || $row->zgv_code==null)
		{
			if($error_log!='')
			{
				$error_log.=", ZugangCode ('".$row->zgv_code."')";
			}
			else 
			{
				$error_log.="ZugangCode ('".$row->zgv_code."')";
			}
		}
		if($row->zgvdatum=='' || $row->zgvdatum==null)
		{
			if($error_log!='')
			{
				$error_log.=", ZugangDatum ('".$row->zgvdatum."')";
			}
			else 
			{
				$error_log.="ZugangDatum ('".$row->zgvdatum."')";
			}
		}
		if($stgart=='m')
		{
			if($row->zgvmas_code=='' || $row->zgvmas_code==null)
			{
				if($error_log!='')
				{
					$error_log.=", ZugangMagStgCode ('".$row->zgvmas_code."')";
				}
				else 
				{
					$error_log.="ZugangMagStgCode ('".$row->zgvmas_code."')";
				}
			}
			if($row->zgvmadatum=='' || $row->zgvmadatum==null)
			{
				if($error_log!='')
				{
					$error_log.=", ZugangMagStgDatum ('".$row->zgvmadatum."')";
				}
				else 
				{
					$error_log.="ZugangMagStgDatum ('".$row->zgvmadatum."')";
				}
			}
		}
		$qrystatus="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='".$row->prestudent_id."' AND studiensemester_kurzbz='".$ssem."' ORDER BY insertamum desc, ext_id desc;";
		if($resultstatus = pg_query($conn, $qrystatus))
		{
			if($rowstatus = pg_fetch_object($resultstatus))
			{
				$sem=$rowstatus->ausbildungssemester;
				if($rowstatus->rolle_kurzbz=="Student" || $rowstatus->rolle_kurzbz=="Outgoing" 
					|| $rowstatus->rolle_kurzbz=="Incoming" || $rowstatus->rolle_kurzbz=='Praktikant' 
					|| $rowstatus->rolle_kurzbz=="Diplomand")
				{
					$status=1;
				}
				else if($rowstatus->rolle_kurzbz=="Unterbrecher" )
				{
					$status=2;
				}
				else if($rowstatus->rolle_kurzbz=="Absolvent" )
				{
					$status=3;
				}
				else if($rowstatus->rolle_kurzbz=="Abbrecher" )
				{
					$status=4;
				}
				else 
				{
					continue;
				}
				$aktstatus=$rowstatus->rolle_kurzbz;
			}
			else 
			{	
				continue;
			}
		}
		//bei Absolventen das Beendigungsdatum (Sponsion oder Abschlussprüfung) überprüfen
		if($aktstatus=='Absolvent')
		{
			if($row->abdatum=='' || $row->abdatum==null)
			{
				if($error_log!='')
				{
					$error_log.=", Datum der Abschlussprüfung ('".$row->abdatum."')";
				}
				else 
				{
					$error_log.="Datum der Abschlussprüfung ('".$row->abdatum."')";
				}
			}
			if($row->sponsion=='' || $row->sponsion==null)
			{
				if($error_log!='')
				{
					$error_log.=", Datum der Sponsion ('".$row->sponsion."')";
				}
				else 
				{
					$error_log.="Datum der Sponsion ('".$row->sponsion."')";
				}
			}
		}
		if($row->berufstaetigkeit_code=='' || $row->berufstaetigkeit_code==null)
		{
			if($error_log!='')
			{
				$error_log.=", Berufstätigkeitscode ('".$row->berufstaetigkeit_code."')";
			}
			else 
			{
				$error_log.="Berufstätigkeitscode ('".$row->berufstaetigkeit_code."')";
			}
		}
		if($error_log!='' OR $error_log1!='')
		{
			$v.="<u>Bei Student (UID, Vorname, Nachname) '".$row->student_uid."', '".$row->nachname."', '".$row->vorname."' ($row->rolle_kurzbz): </u>\n";
			if($error_log!='')
			{
				$v.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Es fehlt: ".$error_log."\n";
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
			
			$datei.="
         <Student>
          <PersKz>".trim($row->matrikelnr)."</PersKz>
          <GeburtsDatum>".date("dmY", $datumobj->mktime_fromdate($row->gebdatum))."</GeburtsDatum>
          <Geschlecht>".strtoupper($row->geschlecht)."</Geschlecht>
          <Vorname>".$row->vorname."</Vorname>
          <Familienname>".$row->nachname."<Familienname>";
			if($row->svnr!='')
			{
				$datei.="
          <SVNR>".$row->svnr."</SVNR>";
			}
			if($row->ersatzkennzeichen!='')
			{
				$datei.="
          <Ersatzkennzeichen>".$row->ersatzkennzeichen."</Ersatzkennzeichen>";
			}
			$datei.="
          <StaatsangehoerigkeitCode>".$row->staatsbuergerschaft."</StaatsangehoerigkeitCode>
          <HeimatPLZ>".$row->plz."</HeimatPLZ>
          <HeimatGemeinde>".$row->gemeinde."</HeimatGemeinde>
          <HeimatStrasse>".$row->strasse."</HeimatStrasse>
          <HeimatNation>".$row->nation."</HeimatNation>
          <ZugangCode>".$row->zgv_code."</ZugangCode>
          <ZugangDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvdatum))."</ZugangDatum>";
		          if($stgart==2)
		          {
		          		$datei.="
          <ZugangMagStgCode>".$row->zgvmas_code."</ZugangMagStgCode>
          <ZugangMagStgDatum>".date("dmY", $datumobj->mktime_fromdate($row->zgvmadatum))."</ZugangMagStgDatum>";
		          }
		          $qryad="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='".$row->prestudent_id."' AND rolle_kurzbz='Student' ORDER BY datum asc;";
		          if($resultad = pg_query($conn, $qryad))
			{
				if($rowad = pg_fetch_object($resultad))
				{
          			$datei.="
          <BeginnDatum>".date("dmY", $datumobj->mktime_fromdate($rowad->datum))."</BeginnDatum>";
				}
			}
          			if($aktstatus=='Absolvent')
			{
				$datei.="
          <Beendigungsdatum>".date("dmY", $datumobj->mktime_fromdate($row->sponsion))."</Beendigungsdatum>";
			}
			$datei.="
          <Ausbildungssemester>".$sem."</Ausbildungssemester>
          <StudStatusCode>".$status."</StudStatusCode>
          <BerufstaetigkeitCode>".$row->berufstaetigkeit_code."</BerufstaetigkeitCode>";
			if($aktstatus=='Incoming' OR $aktstatus=='Outgoing')
			{
				$qryio="SELECT * FROM bis.tbl_bisio WHERE student_uid='".$row->student_uid."';";
				if($resultio = pg_query($conn, $qryio))
				{
					if($rowio = pg_fetch_object($resultio))
					{
						$mob=$rowio->mobilitaetsprogramm_code;
						$gast=$rowio->nation_code;
						$avon=$rowio->von;
						$abis=$rowio->bis;
						$zweck=$rowio->zweck_code;
					}
				}
				$datei.="
          <IO>
          	 <Status>".$aktstatus."</Status>
            <MobilitaetsProgrammCode>".$mob."</MobilitaetsProgrammCode>
            <GastlandCode>".$gast."</GastlandCode>
            <AufenthaltVon>".$avon."</AufenthaltVon>";
	            		if($abis<$bisdatum)
	            		{
	            			$datei.="
            <AufenthaltBis>".$abis."</AufenthaltBis>";
				}
				$datei.="
            <AufenthaltZweckCode>".$zweck."</AufenthaltZweckCode>
          </IO>";
			}
			$datei.="
         </Student>";

		}
		
	}
}
//Bewerber
$qrybw="SELECT * FROM public.tbl_prestudent 
	JOIN public.tbl_prestudentrolle ON(tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id) 
	JOIN public.tbl_person USING(person_id)  
	WHERE bismelden IS TRUE AND (studiensemester_kurzbz='".$ssem."') AND tbl_prestudent.studiengang_kz='".$stg_kz."'
	AND rolle_kurzbz='Bewerber';
	";
if($resultbw = pg_query($conn, $qrybw))
{
	while($rowbw = pg_fetch_object($resultbw))
	{
		if($stgart==1 || $stgart==3)
		{
			if(strtoupper($rowbw->geschlecht)=='M')
			{
				If(!isset($bewerberM[$rowbw->zgv_code]))
				{
					$bewerberM[$rowbw->zgv_code]=0;
				}
				$bewerberM[$rowbw->zgv_code]++;
			}
			else 
			{
				If(!isset($bewerberW[$rowbw->zgv_code]))
				{
					$bewerberW[$rowbw->zgv_code]=0;
				}
				$bewerberW[$rowbw->zgv_code]++;
			}			
		}
		if($stgart==2)
		{	
			if(strtoupper($rowbw->geschlecht)=='M')
			{
				If(!isset($bewerberM[$rowbw->zgvmas_code]))
				{
					$bewerberM[$rowbw->zgvmas_code]=0;
				}
				$bewerberM[$rowbw->zgvmas_code]++;
			}
			else 
			{
				If(!isset($bewerberW[$rowbw->zgvmas_code]))
				{
					$bewerberW[$rowbw->zgvmas_code]=0;
				}
				$bewerberW[$rowbw->zgvmas_code]++;
			}
		}
	}
}

if($stgart==1 || $stgart==3)
{
	for($i=4;$i<100;$i++)
	{
		if(isset($bewerberM[$i]) || isset($bewerberW[$i]))
		{
			If(!isset($bewerberM[$i]))
			{
				$bewerberM[$i]=0;
			}
			If(!isset($bewerberW[$i]))
			{
				$bewerberW[$i]=0;
			}
			$datei.="
         <Bewerber>
           <ZugangCode>".$i."</ZugangCode>
           <AnzBewerberM>".$bewerberM[$i]."</AnzBewerberM>
           <AnzBewerberW>".$bewerberW[$i]."</AnzBewerberW>
         </Bewerber>";
		}
	}
}
if($stgart==2)
{
	for($i=1;$i<12;$i++)
	{
		if(isset($bewerberM[$i]) || isset($bewerberW[$i]))
		{
			if(!isset($bewerberM[$i]))
			{
				$bewerberM[$i]=0;	
			}
			if(!isset($bewerberW[$i]))
			{
				$bewerberW[$i]=0;	
			}
			$datei.="
         <Bewerber>
           <ZugangMagStgCode>".$i."</ZugangMagStgCode>
           <AnzBewerberM>".$bewerberM[$i]."</AnzBewerberM>
           <AnzBewerberW>".$bewerberW[$i]."</AnzBewerberW>
         </Bewerber>";
		}
	}
}
$datei.="
       </StudiengangDetail>
    </StudiengangStamm>
  </StudierendenBewerberMeldung>
</Erhalter>";
if(strlen($v)!='')
{
	echo '<html><head><title>BIS - Meldung Student - ('.$stg_kz.')</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		</head><body>';
	echo "<H1>BIS - Studentendaten werden überprüft! Studiengang: ".$stg_kz."</H1>\n";
	echo "<H2>Nicht plausible BIS-Daten (für Meldung ".$ssem."): </H2><br>";
	echo nl2br($v."\n\n");
	echo $datei;
}
else 
{
	header("Content-type: application/xhtml+xml");
	echo $datei;
}
?>