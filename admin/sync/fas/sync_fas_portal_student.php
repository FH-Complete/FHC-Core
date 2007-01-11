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

function addslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

// ***********************************
// * VILESCI->PORTAL - Synchronisation
// ***********************************

//Mitarbeiter
$qry = "SELECT * FROM person JOIN student ON person_fk=person_pk WHERE uid NOT LIKE '\_dummy%'";

if($result = pg_query($conn_fas, $qry))
{
	$text.="\n Sync Student\n\n";
	while($row = pg_fetch_object($result))
	{
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
		$svnr=$row->svnr;
		$ersatzkennzeichen=$row->ersatzkennzeichen;
		$familienstand=$row->familienstand;
		$geschlecht=strtolower($row->geschlecht);
		$anzahlkinder=$row->anzahlderkinder;
		$aktiv=($row->aktiv=='t'?true:false);
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
		$aufmerksam_kurzbz=$row->aufmerksamdurch;
		$person_id='';
		$studiengang_kz='';
		$berufstaetigkeit_code=$row->berufstaetigkeit;
		$ausbildungcode='';
		$zgv_code=$row->zgv;
		$zgvort=$row->zgvort;
		$zgvdatum=$row->zgvdatum;
		$zgvmas_code=$row->zgvmagister;
		$zgvmaort=$row->zgvmagisterortort;
		$zgvmadatum=$row->zgvmagisterdatum;
		$facheinschlberuf=($row->berufstätigkeit=='J'?true:false);
		$reihungstest_id='';
		$punkte=$row->punkte;
		$ext_id_pre=$row->person_pk;
		$anmeldungreihungstest='';

		//Attribute Student
		$student_uid=$row->uid;
		$matrikelnr=$row->perskz;		
		$prestudent_id='';
		//studiengang_kz bei prestudent
		$semester='';
		$verband='';
		$gruppe='';
		$ext_id_student=$row->student_pk;

		//Ermittlung der Daten des Reihungstests
		$qry_rt1="SELECT student_fk, reihungstest_fk, anmeldungreihungstest FROM student_reihungstest WHERE student_fk=".$row->student_pk.";";
		if($result_rt1 = pg_query($conn, $qry_rt1))
		{		
			if($row_rt1=pg_fetch_object($result_rt1))
			{
				$qry_rt2="SELECT reihungstest_id FROM public.tbl_reihungstest WHERE ext_id=".$row_rt1->reihungstest_fk.";";
				if($result_rt2 = pg_query($conn, $qry_rt2))
				{		
					if($row_rt2=pg_fetch_object($result_rt2))
					{
						$reihungstest_id=$row_rt2->reihungstest_id;
						$anmeldungreihungstest=$row_rt1->anmeldungreihungstest;
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
				$error_log.="Fehler beim Ermitteln des Reihungstests von Student $row->familienname, $row->vorname aufgetreten!\n";
				$error=true;	
			}
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
		if(new_person)
		{
			//insert person
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, 
			                    gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id)
			        VALUES('.$this->addslashes($sprache).','.
					$this->addslashes($anrede).','.
					$this->addslashes($titelpost).','.
				        $this->addslashes($titelpre).','.
				        $this->addslashes($nachname).','.
				        $this->addslashes($vorname).','.
				        $this->addslashes($vornamen).','.
				        $this->addslashes($gebdatum).','.
				        $this->addslashes($gebort).','.
				        $this->addslashes($gebzeit).','.
				        $this->addslashes($foto).','.
				        $this->addslashes($anmerkungen).','.
				        $this->addslashes($homepage).','.
				        $this->addslashes($svnr).','.
				        $this->addslashes($ersatzkennzeichen).','.
				        $this->addslashes($familienstand).','.
				        $this->addslashes($anzahlkinder).','.
				        ($aktiv?'true':'false').','.
				        "now()".','.
				        $this->addslashes($insertvon).','.
				        "now()".','.
				        $this->addslashes($updatevon).','.
				        $this->addslashes($geschlecht).','.
				        $this->addslashes($geburtsnation).','.
				        $this->addslashes($staatsbuergerschaft).','.
				        $this->addslashes($ext_id_person).');';
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
			if($result = pg_query($conn, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					$update=false;			
					if($row->sprache!=$sprache) 				$update=true;
					if($row->anrede!=$anrede) 					$update=true;
					if($row->titelpost!=$titelpost) 				$update=true;
					if($row->titelpre!=$titelpre) 					$update=true;
					if($row->nachname!=$nachname) 				$update=true;
					if($row->vorname!=$vorname) 				$update=true;
					if($row->vornamen!=$vornamen) 				$update=true;
					if($row->gebdatum!=$gebdatum) 				$update=true;
					if($row->gebort!=$gebort) 					$update=true;
					//if($row->gebzeit!=$gebzeit) 				$update=true;
					//if($row->foto!=$foto) 					$update=true;
					if($row->anmerkungen!=$anmerkungen) 			$update=true;
					if($row->homepage!=$homepage) 				$update=true;
					if($row->svnr!=$svnr) 					$update=true;
					if($row->ersatzkennzeichen!=$ersatzkennzeichen) 	$update=true;
					if($row->familienstand!=$familienstand) 			$update=true;
					if($row->anzahlkinder!=$anzahlkinder) 			$update=true;
					if($row->aktiv!=$aktiv) 					$update=true;
					if($row->geburtsnation!=$geburtsnation) 			$update=true;
					if($row->geschlecht!=$geschlecht) 			$update=true;
					if($row->staatsbuergerschaft!=$staatsbuergerschaft)	$update=true;
					
					
					if($update)
					{
						$qry = 'UPDATE public.tbl_person SET'.
						       ' sprache='.$this->addslashes($sprache).','.
						       ' anrede='.$this->addslashes($anrede).','.
						       ' titelpost='.$this->addslashes($titelpost).','.
						       ' titelpre='.$this->addslashes($titelpre).','.
						       ' nachname='.$this->addslashes($nachname).','.
						       ' vorname='.$this->addslashes($vorname).','.
						       ' vornamen='.$this->addslashes($vornamen).','.
						       ' gebdatum='.$this->addslashes($gebdatum).','.
						       ' gebort='.$this->addslashes($gebort).','.
						       //' gebzeit='.$this->addslashes($gebzeit).','.
						       //' foto='.$this->addslashes($foto).','.
						       ' anmerkungen='.$this->addslashes($anmerkungen).','.
						       ' homepage='.$this->addslashes($homepage).','.
						       ' svnr='.$this->addslashes($svnr).','.
						       ' ersatzkennzeichen='.$this->addslashes($ersatzkennzeichen).','.
						       ' familienstand='.$this->addslashes($familienstand).','.
						       ' anzahlkinder='.$this->addslashes($anzahlkinder).','.
						       ' aktiv='.($aktiv?'true':'false').','.
						       ' updateamum='.$this->addslashes($updateamum).','.
						       ' updatevon='.$this->addslashes($updatevon).','.
						       ' geschlecht='.$this->addslashes($geschlecht).','.
						       ' geburtsnation='.$this->addslashes($geburtsnation).','.
						       ' staatsbuergerschaft='.$this->addslashes($staatsbuergerschaft).','.
						       " insertamum=now()".','.
				        		       ' insertvon='.$this->addslashes($insertvon).','.
				        		       " updateamum=now()".','.
				        		       " updatevon=".$this->addslashes($updatevon).','.
						       ' ext_id='.$this->addslashes($ext_id_person).
						       ' WHERE person_id='.$person_id.';';
					}
				}
			}
		}
		if(pg_query(conn,$qry))
		{
			if(new_person)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if($row=pg_fetch_object(pg_query($conn,$qry)))
					$person_id=$row->id;
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
			$qry="SELECT prestudent_id FROM public.tbl_prestudent WHERE ext_id='$row->student_pk'";
			if($resultu = pg_query($conn, $qry))
			{
				if(pg_num_rows($resultu)>0) //wenn dieser eintrag schon vorhanden ist
				{
					if($rowu=pg_fetch_object($resultu))
					{
						$prestudent_id=$rowu->prestudent_id;
						$new_prestudent=true;		
					}
					else $new_prestudent=false;
				}
				else $new_prestudent=false;
			}
			else
			{
				$error=true;
				$error_log.='Fehler beim Zugriff auf Tabelle tbl_prestudent bei student_pk: '.$row->student_pk;	
			}
			
			//Studiengang ermitteln
			$qry="SELECT studiengang_kz FROM public.tbl_studiengang WHERE ext_id";
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
			if(new_prestudent)
			{
				//insert prestudent
				
				$qry = 'INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, studiengang_kz,
					berufstaetigkeit_code, zgv_code, zgvort, zgvdatum, zgvmas_code, zgvmaort, zgvmadatum,
					facheinschlberuf, reihungstest_id, punkte, anmeldungreihungstest,
					insertamum, insertvon, updateamum, updatevon, ext_id)
			        VALUES('.$this->addslashes($aufmerksam_kurzbz).', '.
					$this->addslashes($person_id).', '.
					$this->addslashes($studiengang_kz).', '.
					$this->addslashes($berufstaetigkeit_code).', '.
					$this->addslashes($zgv_code).', '.
					$this->addslashes($zgvort).', '.
					$this->addslashes($zgvdatum).', '.
					$this->addslashes($zgvmas_code).', '.
					$this->addslashes($zgvmaort).', '.
					$this->addslashes($zgvmadatum).', '.
					$this->addslashes($facheinschlberuf).', '.
					$this->addslashes($reihungstest_id).', '.
					$this->addslashes($punkte).', '.
					$this->addslashes($anmeldungreihungstest).', '.
					"now()".', '.
					'SYNC'.', '.
					"now()".', '.
					'SYNC'.', '.
					$this->addslashes($ext_id_pre).';';
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
						
						if($update)
						{
							$qry = 'UPDATE public.tbl_prestudent SET'.
							       ' aufmerksamdurch_kurzbz='.$this->addslashes($aufmerksamdurch_kurzbz).','.
							       ' person_id='.$this->addslashes($person_id).','.
							       ' studiengang_kz='.$this->addslashes($studiengang_kz).','.
							       ' berufstaetigkeit_code='.$this->addslashes($berufstaetigkeit_code).','.
							       ' zgv_code='.$this->addslashes($zgv_code).','.
							       ' zgvort='.$this->addslashes($zgvort).','.
							       ' zgvdatum='.$this->addslashes($zgvdatum).','.
							       ' zgvmas_code='.$this->addslashes($zgvmas_code).','.
							       ' zgvmaort='.$this->addslashes($zgvmaort).','.
							       ' zgvmadatum='.$this->addslashes($person_id).','.
							       ' facheinschlberuf='.$this->addslashes($facheinschlberuf).','.
							       ' reihungstest_id='.$this->addslashes($reihungstest_id).','.
							       ' punkte='.$this->addslashes($punkte).','.
							       ' anmeldungreihungstest='.$this->addslashes($anmeldungreihungstest).','.
							       " insertamum=now()".','.
					        		       ' insertvon='.$this->addslashes($insertvon).','.
					        		       " updateamum=now()".','.
					        		       " updatevon=".$this->addslashes($updatevon).','.
							       ' ext_id='.$this->addslashes($ext_id_pre).
							       ' WHERE prestudent_id='.$prestudent_id.';';
						}
					}
				}
			}
			
			if(pg_query(conn,$qry))
			{
				if(new_pre)
				{
					$qry = "SELECT currval('public.tbl_prestudent_prestudent_id_seq') AS id;";
					if($row=pg_fetch_object(pg_query($conn,$qry)))
						$prestudent_id=$row->id;
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
							$new_student=true;		
						}
						else $new_student=false;
					}
					else $new_student=false;
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
				
				if(new_student)
				{
					//insert student
					
					$qry = 'INSERT INTO public.tbl_student (matrikelnr, prestudent_id, studiengang_kz, 
						semester, verband, gruppe, 
						insertamum, insertvon, updateamum, updatevon, ext_id)
				        		VALUES('.$this->addslashes($matrikelnr).', '.
						$this->addslashes($prestudent_id).', '.
						$this->addslashes($studiengang_kz).', '.
						$this->addslashes($semester).', '.
						$this->addslashes($verband).', '.
						$this->addslashes($gruppe).', '.
						"now()".', '.
						"SYNC".', '.
						"now()".', '.
						"SYNC".', '.
						$this->addslashes($ext_id_student).', ';
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
								       ' matrikelnr='.$this->addslashes($matrikelnr).','.
								       ' prestudent_id='.$this->addslashes($prestudent_id).','.
								       ' studiengang_kz='.$this->addslashes($studiengang_kz).','.
								       ' semester='.$this->addslashes($semester).','.
								       ' verband='.$this->addslashes($verband).','.
								       ' gruppe='.$this->addslashes($gruppe).','.
								       " insertamum=now()".','.
						        		       ' insertvon='.$this->addslashes($insertvon).','.
						        		       " updateamum=now()".','.
						        		       " updatevon=".$this->addslashes($updatevon).','.
								       ' ext_id='.$this->addslashes($ext_id_student).
								       ' WHERE student_id='.$student_id.';';
							}
						}
					}
				}
				if(pg_query(conn,$qry))
				{
					if(new_student)
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
								$new_beutzer=true;		
							}
							else $new_benutzer=false;
						}
						else $new_benutzer=false;
					}
					else
					{
						$error=true;
						$error_log.='Fehler beim Zugriff auf Tabelle tbl_benutzer bei student_pk: '.$row->student_pk;	
					}
					
					//Benutzer aktiv?
					$qry="SELECT * FORM (SELECT status, creationdate FROM student_ausbildungssemester WHERE student_fk= ".$row->student_pk."  AND
					studiensemester_fk=(SELECT studiensemester_pk FROM studiensemester WHERE aktuell='J') ORDER BY 2 DESC LIMIT 1) 
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
					
					if(new_benutzer)
					{
						//insert benutzer
						$qry = 'INSERT INTO public.tbl_benutzer (uid, person_id, aktiv, alias, 
						insertamum, insertvon, updateamum, updatevon, ext_id)
				        		VALUES('.$this->addslashes($student_uid).', '.
						$this->addslashes($person_id).', '.
						$this->addslashes($aktiv).', '.
						$this->addslashes($alias).', '.
						"now()".', '.
						"SYNC".', '.
						"now()".', '.
						"SYNC".', '.
						$this->addslashes($ext_id_benutzer).', ';
						
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
									       ' uid='.$this->addslashes($student_id).','.
									       ' person_id='.$this->addslashes($person_id).','.
									       ' aktiv='.$this->addslashes($aktiv).','.
									       " insertamum=now()".','.
							        		       ' insertvon='.$this->addslashes($insertvon).','.
							        		       " updateamum=now()".','.
							        		       " updatevon=".$this->addslashes($updatevon).
									       ' WHERE ext_id='.$ext_id_benutzer.';';
								}
							}
						}
					}
					if(pg_query(conn,$qry))
					{
						if(new_student)
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
						pg_query($this->conn,'COMMIT;');
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
?>

<html>
<head>
<title>Synchro - Vilesci -> Portal - Student</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

echo nl2br($text);
echo nl2br($error_log);

?>
</body>
</html>