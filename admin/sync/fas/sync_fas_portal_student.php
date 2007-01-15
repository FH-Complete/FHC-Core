<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Synchronisiert Studentendatensaetze von FAS DB in PORTAL DB
 *
 */
require_once('../../../vilesci/config.inc.php');

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

//Mitarbeiter
$qry = "SELECT * FROM person JOIN student ON person_fk=person_pk WHERE uid NOT LIKE '\_dummy%'";

if($result = pg_query($conn_fas, $qry))
{
	$text.="\n Sync Student\n\n";
	while($row = pg_fetch_object($result))
	{
		echo "- ";
		ob_flush();
		flush();
		
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
		$familienstand=$row->familienstand;
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
		
		//Attribute Prestudent
		$aufmerksamdurch_kurzbz='';
		$person_id='';
		$studiengang_kz='';
		$berufstaetigkeit_code=$row->berufstaetigkeit;
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
		
		//Attribut Prestudentrolle
		$rolle_kurzbz='';

		//Ermittlung der Daten des Reihungstests
		$qry_rt1="SELECT student_fk, reihungstest_fk, anmeldedatum FROM student_reihungstest WHERE student_fk=".$row->student_pk.";";
		if($result_rt1 = pg_query($conn_fas, $qry_rt1))
		{		
			if($row_rt1=pg_fetch_object($result_rt1))
			{
				$qry_rt2="SELECT reihungstest_id FROM public.tbl_reihungstest WHERE ext_id=".$row_rt1->reihungstest_fk.";";
				if($result_rt2 = pg_query($conn, $qry_rt2))
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
				$error_log.="Kein Reihungstests von Student $row->familienname, $row->vorname gefunden!\n";	
				$reihungstest_id='';
				$anmeldungreihungstest='';
			}
		}
		
		//Student aktiv?
		$qry="SELECT * FROM (SELECT status, creationdate FROM student_ausbildungssemester WHERE student_fk= ".$row->student_pk."  AND
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
			if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
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
				$qry1="SELECT person_fas, person_portal FROM public.tbl_syncperson WHERE person_fas='$row->person_pk'";
				if($result1 = pg_query($conn, $qry1))
				{
					if(pg_num_rows($result1)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($row1=pg_fetch_object($result1))
						{ 
							//update
							$person_id=$row1->person_portal;
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
						$qryz="SELECT person_id FROM public.tbl_person 
							WHERE ('$row->svnr' is not null AND svnr = '$row->svnr') 
								OR ('$row->ersatzkennzeichen' is not null AND ersatzkennzeichen = '$row->ersatzkennzeichen')";
						if($resultz = pg_query($conn, $qryz))
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
				$error_log.= 'person_id muss eine gueltige Zahl sein';
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
						       ' WHERE person_id='.$person_id.';';
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
		}
		else
		{			
			$error=true;
			$error_log.='Fehler beim Speichern des Person-Datensatzes:'.$nachname.' '.$qry;
		}
		
		if(!$error)
		{
			//Weitere Reihenfolge: prestudent - student - benutzer
			
			//Prestudent schon vorhanden?
			$qry="SELECT prestudent_id FROM public.tbl_prestudent WHERE ext_id=".$row->student_pk.";";
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
				if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$studiengang_kz=$rowu->studiengang_kz;
					}
				}
			}
			echo $row->studiengang_fk."/".$studiengang_kz;
			if($row->aufmerksamdurch=='1')	$aufmerksamdurch_kurzbz='k.A.';
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
				if($result = pg_query($conn, $qry))
				{
					while($row = pg_fetch_object($result))
					{
						$update=false;			
						if($row->aufmerksam_durch_kurzbz!=$aufmerksamdurch_kurzbz) 	$update=true;
						if($row->person_id!=$person_id)						$update=true;
						if($row->studiengang_kz!=$studiengang_kz)				$update=true;
						if($row->berufstaetigkeit_code!=$berufstaetigkeit_code)		$update=true;
						if($row->zgv_code!=$zgv_code)		 				$update=true;
						if($row->zgvort!=$zgvort)			 				$update=true;
						if($row->zgvdatum!=$zgvdatum)		 				$update=true;
						if($row->zgvmas_code!=$zgvmas_code)					$update=true;
						if($row->zgvmaort!=$zgvmaort)						$update=true;
						if($row->zgvmadatum!=$zgvmadatum) 					$update=true;
						if($row->facheinschlberuf!=$facheinschlberuf) 				$update=true;
						if($row->reihungstest_id!=$reihungstest_id)				$update=true;
						if($row->punkte!=$punkte)				 			$update=true;
						if($row->anmeldungreihungstest!=$anmeldungreihungstest)		$update=true;						
						if($row->reihungstestangetreten!=$reihungstestangetreten)		$update=true;
						
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
							       ' zgvmadatum='.myaddslashes($person_id).','.
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
							       ' WHERE prestudent_id='.$prestudent_id.';';
						}
					}
				}
			}

			if(pg_query($conn,$qry))
			{
				if($new_pre)
				{
					$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
					if($row=pg_fetch_object(pg_query($conn,$qry)))
					{
						$prestudent_id=$row->id;
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
				$error_log.='Fehler beim Speichern des Prestudent-Datensatzes:'.$nachname.' '.$qry;
			}
									
			if(!$error)
			{
				//Weitere Reihenfolge: student, benutzer
				
				//Student schon vorhanden?
				$qry="SELECT student_id FROM public.tbl_student WHERE ext_id='$row->student_pk'";
				if($resultu = pg_query($conn, $qry))
				{
					if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($rowu=pg_fetch_object($resultu))
						{
							$student_id=$rowu->student_id;
							$new_student=false;		
						}
						else $new_student=true;
					}
					else $new_student=true;
				}
				else
				{
					$error=true;
					$error_log.='Fehler beim Zugriff auf Tabelle tbl_student bei student_pk: '.$row->student_pk;	
				}
				
				//Gruppenverband ermitteln
				$qry="SELECT fas_function_find_verband_from_student(".$row->student_pk.") as verband
					fas_function_find_jahrgang_from_student(".$row->student_pk.") as jahrgang
					fas_function_find_gruppe_from_student(".$row->student_pk.") as gruppe;";
				if($resultu = pg_query($conn, $qry))
				{
					if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
					{
						if($rowu=pg_fetch_object($resultu))
						{
							$semester=$rowu->$rowu->jahrgang;
							$verband=$rowu->$rowu->verband;
							$gruppe=$rowu->gruppe;
						}
					}
				}
				
				if($new_student)
				{
					//insert student
					
					$qry = 'INSERT INTO public.tbl_student (matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe, 
						insertamum, insertvon, updateamum, updatevon, ext_id)
				        		VALUES('.myaddslashes($matrikelnr).', '.
						myaddslashes($prestudent_id).', '.
						myaddslashes($studiengang_kz).', '.
						myaddslashes($semester).', '.
						myaddslashes($verband).', '.
						myaddslashes($gruppe).', '.
						"now()".', '.
						"'SYNC'".', '.
						"now()".', '.
						"'SYNC'".', '.
						myaddslashes($ext_id_student).', ';
				}
				else 
				{
					//update student
					
					//student_uid auf gueltigkeit pruefen
					if(!is_numeric($student_uid))
					{				
						$error=true;
						$error_log.= 'student_id muss eine gueltige Zahl sein';
					}
					
					//update nur wenn änderungen gemacht
					$qry="SELECT * FROM public.tbl_student WHERE student_id='$student_id';";
					if($result = pg_query($conn, $qry))
					{
						while($row = pg_fetch_object($result))
						{
							$update=false;			
							if($row->matrikelnr!=$matrikelnr)					 	$update=true;
							if($row->prestudent_id!=$prestudent_id)					$update=true;
							if($row->studiengang_kz!=$studiengang_kz)				$update=true;
							if($row->semester!=$semester)						$update=true;
							if($row->verband!=$verband)						$update=true;
							if($row->gruppe!=$gruppe)			 				$update=true;						
							
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
								       ' WHERE student_id='.$student_id.';';
							}
						}
					}
				}
				if(pg_query($conn,$qry))
				{
					if($new_student)
					{
						$qry = "SELECT currval('public.tbl_student_student_id_seq') AS id;";
						if($row=pg_fetch_object(pg_query($conn,$qry)))
							$student_id=$row->id;
						else
						{					
							$error=true;
							$error_log.='Student-Sequence konnte nicht ausgelesen werden';
						}
					}			
				}
				else
				{			
					$error=true;
					$error_log.='Fehler beim Speichern des Student-Datensatzes:'.$nachname.' '.$qry;
				}
										
				if(!$error)
				{
					//Weitere Reihenfolge: benutzer
					
					//Benutzer schon vorhanden?
					$qry="SELECT uid, person_id FROM public.tbl_benutzer WHERE ext_id='$row->student_pk'";
					if($resultu = pg_query($conn, $qry))
					{
						if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
						{
							if($rowu=pg_fetch_object($resultu))
							{
								$new_beutzer=false;		
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
						myaddslashes($ext_id_benutzer).', ';
						
					}
					else 
					{
						//update benutzer
						//uid auf gueltigkeit pruefen
						if(!is_numeric($uid))
						{				
							$error=true;
							$error_log.= 'uid muss eine gueltige Zahl sein';
						}
						if(!is_numeric($person_id))
						{				
							$error=true;
							$error_log.= 'person_id muss eine gueltige Zahl sein';
						}
						
						
						//update nur wenn änderungen gemacht
						$qry="SELECT * FROM public.tbl_benutzer WHERE ext_id='$ext_id_benutzer';";
						if($result = pg_query($conn, $qry))
						{
							while($row = pg_fetch_object($result))
							{
								$update=false;			
								if($row->aktiv!=$aktiv)		 	$update=true;						
								
								if($update)
								{
									$qry = 'UPDATE public.tbl_benutzer SET'.
									       ' uid='.myaddslashes($student_id).','.
									       ' person_id='.myaddslashes($person_id).','.
									       ' aktiv='.myaddslashes($aktiv).','.
									       " insertamum=now()".','.
							        		       ' insertvon='.myaddslashes($insertvon).','.
							        		       " updateamum=now()".','.
							        		       " updatevon=".myaddslashes($updatevon).
									       ' WHERE ext_id='.$ext_id_benutzer.';';
								}
							}
						}
					}
					if(pg_query($conn,$qry))
					{
						if($new_student)
						{
							$qry = "SELECT currval('public.tbl_student_student_id_seq') AS id;";
							if($row=pg_fetch_object(pg_query($conn,$qry)))
								$benutzer_id=$row->id;
							else
							{					
								$error=true;
								$error_log.='Benutzer-Sequence konnte nicht ausgelesen werden';
							}
						}			
					}
					else
					{			
						$error=true;
						$error_log.='Fehler beim Speichern des Student-Datensatzes:'.$nachname.' '.$qry;
					}
											
					if(!$error)
					{
						//Prestudentrolle anlegen
						
						//Status auslesen aus FAS
						$qry1="SELECT status, creationdate FROM student_ausbildungssemester WHERE student_fk= ".$row->student_pk.";";
						if($result1 = pg_query($conn, $qry1))
						{
							while($row1= pg_fetch_object($result1))
							{
								If(status=='1') 	$rolle_kurzbz='Interessent';
								If(status=='2') 	$rolle_kurzbz='Bewerber';
								If(status=='3') 	$rolle_kurzbz='Student';
								If(status=='4') 	$rolle_kurzbz='Ausserordentlicher';
								If(status=='5') 	$rolle_kurzbz='Abgewiesener';
								If(status=='6') 	$rolle_kurzbz='Aufgenommener';
								If(status=='7') 	$rolle_kurzbz='Wartender';
								If(status=='8') 	$rolle_kurzbz='Abbrecher';
								If(status=='9') 	$rolle_kurzbz='Unterbrecher';
								If(status=='10') 	$rolle_kurzbz='Outgoing';
								If(status=='11') 	$rolle_kurzbz='Incoming';
								If(status=='12') 	$rolle_kurzbz='Praktikant';
								If(status=='13') 	$rolle_kurzbz='Diplomant';
								If(status=='14') 	$rolle_kurzbz='Absolvent';
																
								//Prestudentrolle schon vorhanden?	
								$qry2="SELECT * FROM public.tbl_prestudentrolle WHERE prestudent_id='$prestudent_id' 
									AND rolle_kurzbez='$rolle_kurzbez' AND studiensemester_kurzbz='$studiensemester_kurzbz';";
								if($resultu = pg_query($conn, $qry2))
								{
									if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
									{
										if($rowu=pg_fetch_object($resultu))
										{
											//insert	
											$qry3 = 'INSERT INTO public.tbl_prestudentrolle (prestudent_id, rolle_kurzbz,
												studiensemester_kurzbz, ausbildungssemester, datum, 
												insertamum, insertvon, updateamum, updatevon, ext_id)
									        		VALUES('.myaddslashes($prestudent_uid).', '.
											myaddslashes($rolle_kurzbz).', '.
											myaddslashes($studiensemester_kurzbz).', '.
											myaddslashes($semester).', '.
											myaddslashes($$row1->creationdate).', '.
											"now()".', '.
											"'SYNC'".', '.
											"now()".', '.
											"'SYNC'".', '.
											myaddslashes($ext_id_student).', ';
										}
										else 
										{
											//update
											$qry3 = 'UPDATE public.tbl_prestudentrolle SET'.
											       ' ausbildungssemester='.myaddslashes($semester).','.
											       ' datum='.myaddslashes($row1->creationdate).','.
											       " insertamum=now()".','.
									        		       ' insertvon='.myaddslashes($insertvon).','.
									        		       " updateamum=now()".','.
									        		       " updatevon=".myaddslashes($updatevon).
											       ' WHERE prestudent_id='.$prestudent_id.' AND rolle_kurzbez='.$rolle_kurzbez.' AND studiensemester_kurzbz='.$studiensemester_kurzbz.';';
										}	
									}
									else 
									{
										//update
										$qry3 = 'UPDATE public.tbl_prestudentrolle SET'.
										       ' ausbildungssemester='.myaddslashes($semester).','.
										       ' datum='.myaddslashes($row1->creationdate).','.
										       " insertamum=now()".','.
								        		       ' insertvon='.myaddslashes($insertvon).','.
								        		       " updateamum=now()".','.
								        		       " updatevon=".myaddslashes($updatevon).
										       ' WHERE prestudent_id='.$prestudent_id.' AND rolle_kurzbez='.$rolle_kurzbez.' AND studiensemester_kurzbz='.$studiensemester_kurzbz.';';									
									}
									
								}
								else
								{
									$error=true;
									$error_log.='Fehler beim Zugriff auf Tabelle tbl_prestudentrolle bei student_pk: '.$row->student_pk;	
								}
							}
						}
						if(pg_query($conn,$qry3))
						{
							pg_query($conn,'COMMIT;');				
						}
						else
						{			
							$error=true;
							$error_log.='Fehler beim Speichern des Prestudentrolle-Datensatzes:'.$nachname.' '.$qry;
							pg_query($conn,'ROLLBACK;');
						}
					}
					else
					{
						pg_query($conn,'ROLLBACK;');
					}									
				}
				else
				{
					pg_query($conn,'ROLLBACK;');
				}
			}
			else
			{
				pg_query($conn,'ROLLBACK;');
			}						
		}
		else
		{
			pg_query($conn,'ROLLBACK;');
		}

	}
}		


echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>