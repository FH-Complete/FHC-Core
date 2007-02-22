<?php
/* Copyright (C) 2007 
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Synchronisiert Studentendatensaetze von FAS DB in PORTAL DB
 *
*/
require_once('../../../vilesci/config.inc.php');
require_once('../../../include/datum.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$error_log='';
$text = '';
$anzahl_eingefuegt=0;
$anzahl_fehler=0;

$new_person=false;
$new_prestudent=false;
$new_student=false;
$new_benutzer=false;
$new_rolle=false;
$i=0;
$notest=0;
$anzahl_person=0;
$anzahl_fehler_person=0;
$anzahl_student=0;
$anzahl_fehler_student=0;
$anzahl_pre=0;
$anzahl_fehler_pre=0;
$anzahl_benutzer=0;
$anzahl_fehler_benutzer=0;
$anzahl_nichtstudenten=0;
$rolle_kurzbz=array(1=>"Interessent", 2=>"Bewerber", 3=>"Student", 4=>"Ausserordentlicher", 5=>"Abgewiesener", 6=>"Aufgenommener", 7=>"Wartender", 8=>"Abbrecher", 9=>"Unterbrecher", 10=>"Outgoing", 11=>"Incoming", 12=>"Praktikant", 13=>"Diplomant", 14=>"Absolvent");
$studiensemester_kurzbz=array(2=>"wS2002",3=>"SS2003",4=>"WS2003",5=>"SS2004",6=>"WS2004",7=>"SS2005",8=>"WS2005",9=>"SS2006",10=>"WS2006",11=>"SS2007",12=>"WS2007",13=>"SS2008",14=>"WS2008");


$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Student</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php


$qry = "SELECT * FROM person JOIN student ON person_fk=person_pk WHERE uid NOT LIKE '\_dummy%' ORDER BY uid desc";
$datum_obj=new datum();
if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("\n Sync Student\n--------------\n\n");
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();
		
		$error_log='';
		//$text='';
		$error=false;
		//Attribute Person
		$staatsbuergerschaft=$row->staatsbuergerschaft;
		$geburtsnation=$row->gebnation;
		$sprache='German';
		$anrede=$row->anrede;
		$titelpost=$row->postnomentitel;
		$titelpre=$row->titel;
		$nachname=$row->familienname;			
		$vorname=$row->vorname;
		$vornamen=$row->vornamen;
		$gebdatum=$row->gebdat;
		$gebort=$row->gebort;
		$gebzeit=''; //bei insert auslassen
		$foto=''; //bei insert auslassen
		$anmerkungen=$row->bemerkung;
		$homepage='';
		$svnr=$row->svnr;
		$ersatzkennzeichen=$row->ersatzkennzeichen;
		if ($row->familienstand<='0')
		{
			$familienstand=null;
		}
		if ($row->familienstand=='1')
		{
			$familienstand='l';
		}
		if ($row->familienstand=='2')
		{
			$familienstand='v';
		}
		if ($row->familienstand=='3')
		{
			$familienstand='g';
		}
		if ($row->familienstand=='4')
		{
			$familienstand='w';
		}
		$geschlecht=strtolower($row->geschlecht);
		$anzahlkinder=$row->anzahlderkinder;
		//$aktiv=($row->aktiv=='t'?true:false);
		$insertvon='SYNC';
		$insertamum='';
		$updateamum='';
		$updatevon='SYNC';
		$ext_id_person=$row->person_pk;
				
		//Attribute Benutzer
		$uid='';
		$person_id='';
		$aktiv='';
		$alias='';
		$ext_id_benutzer=$row->student_pk;
		
		//Attribute Prestudent
		$aufmerksamdurch_kurzbz='';
		$person_id='';
		$studiengang_kz='';
		$berufstaetigkeit_code=$row->berufstaetigkeit;
		if($berufstaetigkeit_code<0)
		{
			$berufstaetigkeit_code=null;
		}
		$ausbildungcode='';
		$zgv_code=$row->zgv;
		$zgvort=$row->zgvort;
		$zgvdatum=$row->zgvdatum;
		$zgvmas_code=$row->zgvmagister;
		$zgvmaort=$row->zgvmagisterort;
		$zgvmadatum=$row->zgvmagisterdatum;
		$facheinschlberuf=($row->berufstaetigkeit=='J'?true:false);
		$reihungstest_id='';
		$punkte=$row->punkte;
		$ext_id_pre=$row->person_pk;
		$anmeldungreihungstest='';
		$reihungstestangetreten=($row->angetreten=='J'?true:false);
		//bismelden		

		//Attribute Student
		$student_uid=$row->uid;
		$matrikelnr=$row->perskz;		
		$prestudent_id='';
		//studiengang_kz bei prestudent
		$semester='';
		$verband='';
		$gruppe='';
		$ext_id_student=$row->student_pk;
		


		
		if($zgv_code<=0 or $zgv_code=='')
		{
			$zgv_code=null;
		}
		if($zgvmas_code<=0 or $zgvmas_code=='')
		{
			$zgvmas_code=null;
		}
				
		//Ermittlung der Daten des Reihungstests
		$qry="SELECT student_fk, reihungstest_fk, anmeldedatum FROM student_reihungstest WHERE student_fk='".$row->student_pk."';";
		if($result_rt1 = pg_query($conn_fas, $qry))
		{		
			if($row_rt1=pg_fetch_object($result_rt1))
			{
				$qry="SELECT reihungstest_id FROM public.tbl_reihungstest WHERE ext_id='".$row_rt1->reihungstest_fk."';";
				if($result_rt2 = pg_query($conn, $qry))
				{		
					if($row_rt2=pg_fetch_object($result_rt2))
					{
						$reihungstest_id=$row_rt2->reihungstest_id;
						$anmeldungreihungstest=$row_rt1->anmeldedatum;
					}
					else 
					{
						$error_log.="Reihungstest_id von $row_rt1->reihungstest_fk konnte nicht gefunden werden.\n";
						$error=true;	
					}	
				}
				else 
				{
					$error_log.="Reihungstest von $row_rt1->reihungstest_fk wurde nicht gefunden.\n";
					$error=true;	
				}
			}
			else 
			{
				$error_log.="Kein Reihungstest von Student $row->familienname, $row->vorname gefunden!\n";	
				$reihungstest_id='';
				$anmeldungreihungstest='';
				$notest++;
			}
		}
		
		//Student aktiv?
		$qry="SELECT * FROM (SELECT status, creationdate FROM student_ausbildungssemester WHERE student_fk= '".$row->student_pk."'  AND
		studiensemester_fk=(SELECT studiensemester_pk FROM studiensemester WHERE aktuell='J') ORDER BY 2 DESC LIMIT 1) as abc
		WHERE status IN ('3', '10', '11', '12', '13');";

		if($resultu = pg_query($conn_fas, $qry))
		{
			if(pg_num_rows($resultu)>0)
			{
				$aktiv=true;
			}
			else 
			{
				$aktiv=false;
			}
		}
		else
		{
			$error=true;
			$error_log.='Fehler beim Holen des aktuellen Status bei student_pk: '.$row->student_pk;	
		}

		//Start der Transaktion
		pg_query($conn,'BEGIN;');
		
		//Reihenfolge: person - prestudent - student - benutzer
		
		//insert oder update bei person?
		$qry="SELECT person_id FROM public.tbl_benutzer WHERE uid='$row->uid'";

		if($resultu = pg_query($conn, $qry))
		{
			if(pg_num_rows($resultu)>0 && $row->uid!='') //wenn dieser eintrag schon vorhanden ist
			{
				if($rowu=pg_fetch_object($resultu))
				{
					//update
					$person_id=$rowu->person_id;
					$new_person=false;
				}
				else 
				{
					$error=true;
					$error_log.="benutzer von $row->uid konnte nicht ermittelt werden\n";
				}
			}	
			else 
			{
				$qry="SELECT person_fas, person_portal FROM public.tbl_syncperson WHERE person_fas='$row->person_pk'";
				if($result_sync1 = pg_query($conn, $qry))
				{
					if(pg_num_rows($result_sync1)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($row_sync1=pg_fetch_object($result_sync1))
						{ 
							//update
							$person_id=$row_sync1->person_portal;
							$new_person=false;
						}
						else 
						{
							$error=true;
							$error_log.="person von $row->person_pk konnte nicht ermittelt werden\n";
						}
					}
					else
					{
						//vergleich svnr und ersatzkennzeichen
						$qry="SELECT person_id FROM public.tbl_person 
							WHERE ('$row->svnr' is not null AND '$row->svnr'<> '' AND svnr = '$row->svnr') 
								OR ('$row->ersatzkennzeichen' is not null AND '$row->ersatzkennzeichen' <> '' AND ersatzkennzeichen = '$row->ersatzkennzeichen')";
						if($resultz = pg_query($conn, $qry))
						{
							if(pg_num_rows($resultz)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowz=pg_fetch_object($resultz))
								{
									$new_person=false;
									$person_id=$rowz->person_id;
								}
								else 
								{
									$error=true;
									$error_log.="person mit svnr: $row->svnr bzw. ersatzkennzeichen: $row->ersatzkennzeichen konnte nicht ermittelt werden (".pg_num_rows($resultz).")\n";
								}
							}
							else 
							{
								//insert
								$new_person=true;
							}
						}		
					}
				}					
			}	
		}
		if($new_person)
		{
			//insert person
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, 
			                    gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id)
			        VALUES('.myaddslashes($sprache).','.
					myaddslashes($anrede).','.
					myaddslashes($titelpost).','.
				        myaddslashes($titelpre).','.
				        myaddslashes($nachname).','.
				        myaddslashes($vorname).','.
				        myaddslashes($vornamen).','.
				        myaddslashes($gebdatum).','.
				        myaddslashes($gebort).','.
				        myaddslashes($gebzeit).','.
				        myaddslashes($foto).','.
				        myaddslashes($anmerkungen).','.
				        myaddslashes($homepage).','.
				        myaddslashes($svnr).','.
				        myaddslashes($ersatzkennzeichen).','.
				        myaddslashes($familienstand).','.
				        myaddslashes($anzahlkinder).','.
				        ($aktiv?'true':'false').','.
				        "now()".','.
				        myaddslashes($insertvon).','.
				        "now()".','.
				        myaddslashes($updatevon).','.
				        myaddslashes($geschlecht).','.
				        myaddslashes($geburtsnation).','.
				        myaddslashes($staatsbuergerschaft).','.
				        myaddslashes($ext_id_person).');';
		}
		else 
		{
			//update person
			//person_id auf gueltigkeit pruefen
			if(!is_numeric($person_id))
			{				
				$error=true;
				$error_log.= 'person_id muss eine gueltige Zahl sein: '.$nachname;
			}
			
			//update nur wenn änderungen gemacht
			$qry="SELECT * FROM public.tbl_person WHERE person_id='$person_id';";
			if($result1 = pg_query($conn, $qry))
			{
				while($row1 = pg_fetch_object($result1))
				{
					$update=false;			
					if($row1->sprache!=$sprache) 				$update=true;
					if($row1->anrede!=$anrede) 				$update=true;
					if($row1->titelpost!=$titelpost) 				$update=true;
					if($row1->titelpre!=$titelpre) 				$update=true;
					if($row1->nachname!=$nachname) 			$update=true;
					if($row1->vorname!=$vorname) 				$update=true;
					if($row1->vornamen!=$vornamen) 				$update=true;
					if($row1->gebdatum!=$gebdatum) 				$update=true;
					if($row1->gebort!=$gebort) 					$update=true;
					//if($row1->gebzeit!=$gebzeit) 				$update=true;
					//if($row1->foto!=$foto) 					$update=true;
					if($row1->anmerkungen!=$anmerkungen) 		$update=true;
					if($row1->homepage!=$homepage) 			$update=true;
					if($row1->svnr!=$svnr) 					$update=true;
					if($row1->ersatzkennzeichen!=$ersatzkennzeichen) 	$update=true;
					if($row1->familienstand!=$familienstand) 			$update=true;
					if($row1->anzahlkinder!=$anzahlkinder) 			$update=true;
					if($row1->aktiv!=$aktiv) 					$update=true;
					if($row1->geburtsnation!=$geburtsnation) 		$update=true;
					if($row1->geschlecht!=$geschlecht) 			$update=true;
					if($row1->staatsbuergerschaft!=$staatsbuergerschaft)	$update=true;
					
					
					if($update)
					{
						$qry = 'UPDATE public.tbl_person SET'.
						       ' sprache='.myaddslashes($sprache).','.
						       ' anrede='.myaddslashes($anrede).','.
						       ' titelpost='.myaddslashes($titelpost).','.
						       ' titelpre='.myaddslashes($titelpre).','.
						       ' nachname='.myaddslashes($nachname).','.
						       ' vorname='.myaddslashes($vorname).','.
						       ' vornamen='.myaddslashes($vornamen).','.
						       ' gebdatum='.myaddslashes($gebdatum).','.
						       ' gebort='.myaddslashes($gebort).','.
						       //' gebzeit='.myaddslashes($gebzeit).','.
						       //' foto='.myaddslashes($foto).','.
						       ' anmerkungen='.myaddslashes($anmerkungen).','.
						       //' homepage='.myaddslashes($homepage).','.
						       ' svnr='.myaddslashes($svnr).','.
						       ' ersatzkennzeichen='.myaddslashes($ersatzkennzeichen).','.
						       ' familienstand='.myaddslashes($familienstand).','.
						       ' anzahlkinder='.myaddslashes($anzahlkinder).','.
						       ' aktiv='.($aktiv?'true':'false').','.
						       ' geschlecht='.myaddslashes($geschlecht).','.
						       ' geburtsnation='.myaddslashes($geburtsnation).','.
						       ' staatsbuergerschaft='.myaddslashes($staatsbuergerschaft).','.
						       " insertamum=now()".','.
				        		       ' insertvon='.myaddslashes($insertvon).','.
				        		       " updateamum=now()".','.
				        		       " updatevon=".myaddslashes($updatevon).','.
						       ' ext_id='.myaddslashes($ext_id_person).
						       ' WHERE person_id='.myaddslashes($person_id).';';
					}
				}
			}
		}
		if(pg_query($conn,$qry))
		{
			if($new_person)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if($rowu=pg_fetch_object(pg_query($conn,$qry)))
					$person_id=$rowu->id;
				else
				{					
					$error=true;
					$error_log.='Person-Sequence konnte nicht ausgelesen werden';
				}
			}			
			//Eintrag Synctabelle
			$qryz="SELECT person_fas FROM tbl_syncperson WHERE person_fas='$row->person_pk' AND person_portal='$person_id'";
			if($resultz = pg_query($conn, $qryz))
			{
				if(pg_num_rows($resultz)==0) //wenn dieser eintrag noch nicht vorhanden ist
				{
					$qry='INSERT INTO tbl_syncperson (person_fas, person_portal)'.
						'VALUES ('.$row->person_pk.', '.$person_id.');';
					$resulti = pg_query($conn, $qry);
				}
			}
		}
		else
		{			
			$error=true;
			$error_log.='Fehler beim Speichern des Person-Datensatzes:'.$nachname.' '.$qry."\n".pg_errormessage($conn)."\n";
		}
		
		if(!$error)
		{
			$anzahl_person++;
			//Weitere Reihenfolge: prestudent - student - benutzer
			
			//Prestudent schon vorhanden?
			$qry="SELECT prestudent_id FROM public.tbl_prestudent WHERE ext_id='".$row->student_pk."';";
			if($resultu = pg_query($conn, $qry))
			{
				if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$prestudent_id=$rowu->prestudent_id;
						$new_prestudent=false;		
					}
					else $new_prestudent=true;
				}
				else $new_prestudent=true;
			}
			else
			{
				$error=true;
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_prestudent bei student_pk: '.$row->student_pk;	
			}
			
			//Studiengang ermitteln
			$qry="SELECT studiengang_kz FROM public.tbl_studiengang WHERE ext_id='".$row->studiengang_fk."';";
			if($resultu = pg_query($conn, $qry))
			{
				if(pg_num_rows($resultu)>0) 
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$studiengang_kz=$rowu->studiengang_kz;
					}
				}
				else 
				{
					$error_log.=$qry." STUDIENGANG NICHT GEFUNDEN!!! ";
				}
			}
			if($row->aufmerksamdurch=='1')		$aufmerksamdurch_kurzbz='k.A.';
			else if($row->aufmerksamdurch=='2')	$aufmerksamdurch_kurzbz='Internet';
			else if($row->aufmerksamdurch=='3')	$aufmerksamdurch_kurzbz='Zeitungen';
			else if($row->aufmerksamdurch=='4')	$aufmerksamdurch_kurzbz='Werbung';
			else if($row->aufmerksamdurch=='5')	$aufmerksamdurch_kurzbz='Mundpropaganda';
			else if($row->aufmerksamdurch=='6')	$aufmerksamdurch_kurzbz='FH-Führer';
			else if($row->aufmerksamdurch=='7')	$aufmerksamdurch_kurzbz='BEST Messe';
			else if($row->aufmerksamdurch=='8')	$aufmerksamdurch_kurzbz='Partnerfirma';
			else if($row->aufmerksamdurch=='9')	$aufmerksamdurch_kurzbz='Schule';
			else if($row->aufmerksamdurch=='10')	$aufmerksamdurch_kurzbz='Bildungstelefon';
			else if($row->aufmerksamdurch=='11')	$aufmerksamdurch_kurzbz='TGM';
			else if($row->aufmerksamdurch=='12')	$aufmerksamdurch_kurzbz='Abgeworben';
			else if($row->aufmerksamdurch=='13')	$aufmerksamdurch_kurzbz='Technikum Wien';
			else if($row->aufmerksamdurch=='14')	$aufmerksamdurch_kurzbz='Aussendungen';
			else if($row->aufmerksamdurch=='15')	$aufmerksamdurch_kurzbz='offene Tür';
			else $aufmerksamdurch_kurzbz='k.A.';
			
			
			if($new_prestudent)
			{
				//insert prestudent
				
				$qry = 'INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, studiengang_kz,
					berufstaetigkeit_code, zgv_code, zgvort, zgvdatum, zgvmas_code, zgvmaort, zgvmadatum,
					facheinschlberuf, reihungstest_id, punkte, anmeldungreihungstest, reihungstestangetreten,
					insertamum, insertvon, updateamum, updatevon, ext_id)
				        	VALUES('.myaddslashes($aufmerksamdurch_kurzbz).', '.
					myaddslashes($person_id).', '.
					myaddslashes($studiengang_kz).', '.
					myaddslashes($berufstaetigkeit_code).', '.
					myaddslashes($zgv_code).', '.
					myaddslashes($zgvort).', '.
					myaddslashes($zgvdatum).', '.
					myaddslashes($zgvmas_code).', '.
					myaddslashes($zgvmaort).', '.
					myaddslashes($zgvmadatum).', '.
					($facheinschlberuf?'true':'false').', '.
					myaddslashes($reihungstest_id).', '.
					myaddslashes($punkte).', '.
					myaddslashes($anmeldungreihungstest).', '.
					($reihungstestangetreten?'true':'false').', '.
					"now()".', '.
					"'SYNC', ".
					"now()".', '.
					"'SYNC', ".
					myaddslashes($ext_id_pre).');';
			}
			else 
			{
				//update prestudent
				
				//prestudent_id auf gueltigkeit pruefen
				if(!is_numeric($prestudent_id))
				{				
					$error=true;
					$error_log.= 'prestudent_id muss eine gueltige Zahl sein';
				}
				
				//update nur wenn änderungen gemacht
				$qry="SELECT * FROM public.tbl_prestudent WHERE prestudent_id='$prestudent_id';";
				if($results = pg_query($conn, $qry))
				{
					while($rows = pg_fetch_object($results))
					{
						$update=false;			
						if($rows->aufmerksamdurch_kurzbz!=$aufmerksamdurch_kurzbz) 	$update=true;
						if($rows->person_id!=$person_id)						$update=true;
						if($rows->studiengang_kz!=$studiengang_kz)				$update=true;
						if($rows->berufstaetigkeit_code!=$berufstaetigkeit_code)		$update=true;
						if($rows->zgv_code!=$zgv_code)		 				$update=true;
						if($rows->zgvort!=$zgvort)			 				$update=true;
						if($rows->zgvdatum!=$zgvdatum)		 				$update=true;
						if($rows->zgvmas_code!=$zgvmas_code)					$update=true;
						if($rows->zgvmaort!=$zgvmaort)						$update=true;
						if($rows->zgvmadatum!=$zgvmadatum) 					$update=true;
						if($rows->facheinschlberuf!=$facheinschlberuf) 				$update=true;
						if($rows->reihungstest_id!=$reihungstest_id)				$update=true;
						if($rows->punkte!=$punkte)				 			$update=true;
						if($rows->anmeldungreihungstest!=$anmeldungreihungstest)		$update=true;						
						if($rows->reihungstestangetreten!=$reihungstestangetreten)		$update=true;
						
						if($update)
						{
							$qry = 'UPDATE public.tbl_prestudent SET'.
							       ' aufmerksamdurch_kurzbz='.myaddslashes($aufmerksamdurch_kurzbz).','.
							       ' person_id='.myaddslashes($person_id).','.
							       ' studiengang_kz='.myaddslashes($studiengang_kz).','.
							       ' berufstaetigkeit_code='.myaddslashes($berufstaetigkeit_code).','.
							       ' zgv_code='.myaddslashes($zgv_code).','.
							       ' zgvort='.myaddslashes($zgvort).','.
							       ' zgvdatum='.myaddslashes($zgvdatum).','.
							       ' zgvmas_code='.myaddslashes($zgvmas_code).','.
							       ' zgvmaort='.myaddslashes($zgvmaort).','.
							       ' zgvmadatum='.myaddslashes($zgvmadatum).','.
							       ' facheinschlberuf='.($facheinschlberuf?'true':'false').','.
							       ' reihungstest_id='.myaddslashes($reihungstest_id).','.
							       ' punkte='.myaddslashes($punkte).','.
							       ' anmeldungreihungstest='.myaddslashes($anmeldungreihungstest).','.
							       ' reihungstestangetreten='.($reihungstestangetreten?'true':'false').','.
							       " insertamum=now()".','.
					        		       ' insertvon='.myaddslashes($insertvon).','.
					        		       " updateamum=now()".','.
					        		       " updatevon=".myaddslashes($updatevon).','.
							       ' ext_id='.myaddslashes($ext_id_pre).
							       ' WHERE prestudent_id='.myaddslashes($prestudent_id).';';
						}
					}
				}
			}
			
			if(pg_query($conn,$qry))
			{
				if($new_prestudent)
				{
					$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
					if($rowu=pg_fetch_object(pg_query($conn,$qry)))
					{
						$prestudent_id=$rowu->id;
					}
					else
					{					
						$error=true;
						$error_log.='Prestudent-Sequence konnte nicht ausgelesen werden';
					}
				}			
			}
			else
			{			
				$error=true;
				$error_log.='Fehler beim Speichern des Prestudent-Datensatzes:'.$nachname.' '.$qry."\n".pg_errormessage($conn)."\n";
			}
									
			if(!$error)
			{
				$anzahl_pre++;
				//Weitere Reihenfolge: student, benutzer
				
				//Student schon vorhanden?
				$qry="SELECT student_uid FROM public.tbl_student WHERE student_uid='$student_uid'";
				if($resultu = pg_query($conn, $qry))
				{
					if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($rowu=pg_fetch_object($resultu))
						{
							$student_uid=$rowu->student_uid;
							$new_student=false;		
						}
						else $new_student=true;
					}
					else $new_student=true;
				}
				else
				{
					$error=true;
					$error_log.='Fehler beim Zugriff auf Tabelle tbl_student bei student_pk: '.$ext_id_student;	
				}

				//Gruppenverband ermitteln
				$qry="SELECT fas_function_find_verband_from_student(".$ext_id_student.") as verband,
					fas_function_find_jahrgang_from_student(".$ext_id_student.") as jahrgang,
					fas_function_find_gruppe_from_student(".$ext_id_student.") as gruppe;";
				if($resultu = pg_query($conn_fas, $qry))
				{
					if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($rowu=pg_fetch_object($resultu))
						{
							$semester=$rowu->jahrgang;
							if ($rowu->verband==null)
							{
								$verband=' ';
							}
							else 
							{
								$verband=$rowu->verband;
							}
							if($rowu->gruppe==null)
							{
								$gruppe=' ';
							}
							else 
							{
								$gruppe=$rowu->gruppe;
							}
							if($semester!=null AND $verband!=null AND $gruppe!=null)
							{
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND verband=".myaddslashes($verband)." AND gruppe=".myaddslashes($gruppe).";";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry='INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES('.myaddslashes($studiengang_kz).', '.
										myaddslashes($semester).', '.
										myaddslashes($verband).', '.
										myaddslashes($gruppe).', '.
										'true, null , null );';
										
										pg_query($conn, $qry);
									}
								}
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND verband=".myaddslashes($verband)." AND gruppe=' ';";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry='INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES('.myaddslashes($studiengang_kz).', '.
										myaddslashes($semester).', '.
										myaddslashes($verband).', '.
										"'', true, null, null);";
										
										pg_query($conn, $qry);
									}
								}
								$qry="SELECT * from public.tbl_lehrverband WHERE studiengang_kz=".myaddslashes($studiengang_kz)." AND semester=".myaddslashes($semester)." AND  verband=' ' AND gruppe=' ';";
								if($resultg = pg_query($conn, $qry))
								{
									if(pg_num_rows($resultg)<1)
									{
										$qry='INSERT INTO public.tbl_lehrverband (studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung, ext_id) 
										VALUES('.myaddslashes($studiengang_kz).', '.
										myaddslashes($semester).', '.
										"'', '', true, null, null);";
										
										pg_query($conn, $qry);
									}
								}
							}
						}
					}
				}
				//presetudentrolle
				
				$qry="SELECT * FROM student_ausbildungssemester where student_fk='$ext_id_student';";
				if($resultru = pg_query($conn_fas, $qry))
				{
					while($rowru=pg_fetch_object($resultru))
					{
						$qry="SELECT semester FROM ausbildungssemester WHERE ausbildungssemester_pk='$rowru->ausbildungssemester_fk'";
						if($resultr = pg_query($conn_fas, $qry))
						{
							while($rowr=pg_fetch_object($resultr))
							{
								$ausbildungssemester=$rowr->semester;
								$date = date('Y-m-d', $datum_obj->mktime_fromtimestamp($rowru->creationdate));
								$status=$rowru->status;
								$stm=$rowru->studiensemester_fk;
								$qry="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='$prestudent_id' AND rolle_kurzbz='$rolle_kurzbz[$status]' AND studiensemester_kurzbz='$studiensemester_kurzbz[$stm]' AND ausbildungssemester='$ausbildungssemester';";
								if($resultu = pg_query($conn, $qry))
								{
									if(!pg_num_rows($resultu)>0) //wenn dieser eintrag noch nicht vorhanden ist
									{
										if($rowu=pg_fetch_object($resultu))
										{
											$qry="INSERT INTO public_tbl_prestudentenrolle (prestudent_id, rolle_kurzbz, studiensemester_kurzbz, 
												ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id)
												SET('$prestudent_id', '$rolle_kurzbz[$status]', '$studiensemester_kurzbz[$stm]', '$ausbildungssemester', '$datum',
												now(),'SYNC',now(),'SYNC', '$rowru->student_ausbildungssemester_pk')";
											pg_query($conn, $qry);
											echo "rolle: ".$qry;
										}
									}
								}
							}
						}
					}
				}
				
				
				if ($semester!=null and $semester!='' and is_numeric($semester) 
				    and $verband!=null and $gruppe!=null)
				{
					
					if($new_student)
					{
						//insert student
						
						$qry = 'INSERT INTO public.tbl_student (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe, 
							insertamum, insertvon, updateamum, updatevon, ext_id)
					        		VALUES('.myaddslashes($student_uid).', '.
							myaddslashes($matrikelnr).', '.
							myaddslashes($prestudent_id).', '.
							myaddslashes($studiengang_kz).', '.
							myaddslashes($semester).', '.
							myaddslashes($verband).', '.
							myaddslashes($gruppe).', '.
							"now()".', '.
							"'SYNC'".', '.
							"now()".', '.
							"'SYNC'".', '.
							myaddslashes($ext_id_student).'); ';
					}
					else 
					{
						//update student
																								
						//update nur wenn änderungen gemacht
						$qry="SELECT * FROM public.tbl_student WHERE student_uid='$student_uid';";
						if($results = pg_query($conn, $qry))
						{
							while($rows = pg_fetch_object($results))
							{
								$update=false;			
								if($rows->matrikelnr!=$matrikelnr)					 	$update=true;
								if($rows->prestudent_id!=$prestudent_id)					$update=true;
								if($rows->studiengang_kz!=$studiengang_kz)				$update=true;
								if($rows->semester!=$semester)						$update=true;
								if($rows->verband!=$verband)						$update=true;
								if($rows->gruppe!=$gruppe)			 			$update=true;						
								
								if($update)
								{
									$qry = 'UPDATE public.tbl_student SET'.
									       ' matrikelnr='.myaddslashes($matrikelnr).','.
									       ' prestudent_id='.myaddslashes($prestudent_id).','.
									       ' studiengang_kz='.myaddslashes($studiengang_kz).','.
									       ' semester='.myaddslashes($semester).','.
									       ' verband='.myaddslashes($verband).','.
									       ' gruppe='.myaddslashes($gruppe).','.
									       " insertamum=now()".','.
							        		       ' insertvon='.myaddslashes($insertvon).','.
							        		       " updateamum=now()".','.
							        		       " updatevon=".myaddslashes($updatevon).','.
									       ' ext_id='.myaddslashes($ext_id_student).
									       ' WHERE student_uid='.myaddslashes($student_uid).';';
								}
							}
						}
					}
					$anzahl_student++;
					if(pg_query($conn,$qry))
					{
						/*if($new_student)
						{
							$qry = "SELECT currval('public.tbl_student_student_id_seq') AS id;";
							if($rowz=pg_fetch_object(pg_query($conn,$qry)))
								$student_uid=$rowz->id;
							else
							{					
								$error=true;
								$error_log.='Student-Sequence konnte nicht ausgelesen werden';
							}
						}*/			
					}
					else
					{			
						$error=true;
						$error_log.='Fehler beim Speichern des Student-Datensatzes:'.$nachname.' / '.$qry."\n".pg_errormessage($conn)."\n";
					}
											
					if(!$error)
					{
						
						//Weitere Reihenfolge: benutzer
						
						//Benutzer schon vorhanden?
						$qry="SELECT uid, person_id FROM public.tbl_benutzer WHERE person_id='$person_id'";
						if($resultu = pg_query($conn, $qry))
						{
							if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
							{
								if($rowu=pg_fetch_object($resultu))
								{
									$new_benutzer=false;	
									$uid=$rowu->uid;	
								}
								else $new_benutzer=true;
							}
							else $new_benutzer=true;
						}
						else
						{
							$error=true;
							$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer bei student_pk: '.$row->student_pk;	
						}
						
											
						if($new_benutzer)
						{
							//insert benutzer
							$qry = 'INSERT INTO public.tbl_benutzer (uid, person_id, aktiv, alias, 
							insertamum, insertvon, updateamum, updatevon, ext_id)
					        		VALUES('.myaddslashes($student_uid).', '.
							myaddslashes($person_id).', '.
							myaddslashes($aktiv).', '.
							myaddslashes($alias).', '.
							"now()".', '.
							"'SYNC'".', '.
							"now()".', '.
							"'SYNC'".', '.
							myaddslashes($ext_id_benutzer).'); ';
							
						}
						else 
						{
							//update benutzer
							//person_id auf gueltigkeit pruefen
							
							if(!is_numeric($person_id))
							{				
								$error=true;
								$text.='person_id muss eine gueltige Zahl sein\n';
								$error_log.= 'person_id muss eine gueltige Zahl sein\n';
							}
							
							
							//update nur wenn änderungen gemacht
							$qry="SELECT * FROM public.tbl_benutzer WHERE ext_id='$ext_id_benutzer';";
							if($results = pg_query($conn, $qry))
							{
								while($rows = pg_fetch_object($results))
								{
									$update=false;			
									if($rows->aktiv!=$aktiv)		 	$update=true;						
									
									if($update)
									{
										$qry = 'UPDATE public.tbl_benutzer SET'.
										       ' uid='.myaddslashes($student_uid).','.
										       ' person_id='.myaddslashes($person_id).','.
										       ' aktiv='.myaddslashes($aktiv).','.
										       " insertamum=now()".','.
								        		       ' insertvon='.myaddslashes($insertvon).','.
								        		       " updateamum=now()".','.
								        		       " updatevon=".myaddslashes($updatevon).
										       ' WHERE ext_id='.myaddslashes($ext_id_benutzer).';';
									}
								}
							}
						}
						if(!pg_query($conn,$qry))
						{			
							$error=true;
							$error_log.='Fehler beim Speichern des Benutzer-Datensatzes:'.$nachname.' '.$qry."\n".pg_errormessage($conn)."\n";
						}
						$anzahl_benutzer++;					
						if(!$error)
						{
							if(pg_query($conn,$qry))
							{
								pg_query($conn,'COMMIT;');				
							}
							else
							{			
								$anzahl_fehler_benutzer++;
								$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
								$text.=$error_log;
								$text.="\n".$qry." R1\n";
								$text.="**********\n\n";
								pg_query($conn,'ROLLBACK;');
							}
						}
						else
						{
							$anzahl_fehler_benutzer++;
							$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
							$text.=$error_log;
							$text.="\n".$qry." R2\n";
							$text.="**********\n\n";
							pg_query($conn,'ROLLBACK;');
						}									
					}
					else
					{
						$anzahl_fehler_student++;
						$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
						$text.=$error_log;
						$text.="\n".$qry." R3\n";
						$text.="**********\n\n";
						pg_query($conn,'ROLLBACK;');
					}
				}
				else 
				{
					$anzahl_nichtstudenten++;
					/*$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
					$text.="Semester: ".$semester."/Verband: ".$verband." /Gruppe: ".$gruppe." / Stg:".$studiengang_kz."\n";
					$text.=$error_log;
					$text.="\n".$qry." C1\n";
					$text.="**********\n\n";*/
					pg_query($conn,'COMMIT;'); //Commit, wenn kein Gruppeneintrag gefunden (Interessent, Bewerber) => nur Person und Prestudent werden angelegt
				}
			}
			else
			{
				$anzahl_fehler_pre++;
				$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
				$text.=$error_log;
				$text.="\n".$qry." R4\n";
				$text.="**********\n\n";
				pg_query($conn,'ROLLBACK;');
			}						
		}
		else
		{
			$anzahl_fehler_person++;
			$text.="\n***********".$student_uid."/".$nachname.", ".$vorname."/".$matrikelnr."\n";
			$text.=$error_log;
			$text.=" R5\n";
			$text.="**********\n\n";
			pg_query($conn,'ROLLBACK;');
		}

	}
}		


//echo nl2br("\n".$text);
echo nl2br("\n".$error_log);
Echo nl2br("\n\nPersonen ohne Reihungstest: ".$notest." \n");
Echo nl2br("Personen: Übertragen: ".$anzahl_person." Fehler: ".$anzahl_fehler_person."\n");
Echo nl2br("Prestudenten: Übertragen: ".$anzahl_pre." Fehler: ".$anzahl_fehler_pre."\n");
Echo nl2br("Nicht-Studenten: ".$anzahl_nichtstudenten."\n");
Echo nl2br("Studenten: Übertragen: ".$anzahl_student." Fehler: ".$anzahl_fehler_student."\n");
Echo nl2br("Benutzer: Übertragen: ".$anzahl_benutzer." Fehler: ".$anzahl_fehler_benutzer."\n");
$error_log=$text;
$error_log.="\n\nPersonen ohne Reihungstest: ".$notest." \n";
$error_log.="Personen: Übertragen: ".$anzahl_person." Fehler: ".$anzahl_fehler_person."\n";
$error_log.="Prestudenten: Übertragen: ".$anzahl_pre." Fehler: ".$anzahl_fehler_pre."\n";
$error_log.="Nicht-Studenten: ".$anzahl_nichtstudenten."\n";
$error_log.="Studenten: Übertragen: ".$anzahl_student." Fehler: ".$anzahl_fehler_student."\n";
$error_log.="Benutzer: Übertragen: ".$anzahl_benutzer." Fehler: ".$anzahl_fehler_benutzer."\n";

mail($adress, 'SYNC Student', $error_log);
?>
</body>
</html>