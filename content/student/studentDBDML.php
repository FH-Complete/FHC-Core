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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

// ****************************************
// * Insert/Update/Delete
// * der Studenten
// *
// * Script sorgt fuer den Datenbanzugriff
// * fuer das XUL - Studenten-Modul
// *
// ****************************************

require_once('../../vilesci/config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzergruppe.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/bisio.class.php');
require_once('../../include/zeugnisnote.class.php');
require_once('../../include/lvgesamtnote.class.php');

$user = get_uid();

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

loadVariables($conn, $user);
//Berechtigungen laden
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}
	
// *** FUNKTIONEN ***

// ****
// * Generiert die Matrikelnummer
// * FORMAT: 0710254001
// * 07 = Jahr
// * 1/2/0  = WS/SS/Incomming
// * 0254 = Studiengangskennzahl vierstellig
// * 001 = Laufende Nummer
// ****
function generateMatrikelnummer($conn, $studiengang_kz, $studiensemester_kurzbz)
{
	$jahr = substr($studiensemester_kurzbz, 4);	
	$art = substr($studiensemester_kurzbz, 0, 2);
	switch($art)
	{
		case 'WS': $art = '1'; break;
		case 'SS': $art = '2'; break;
		default: $art = '0'; break;
	}
	if($art=='2')
		$jahr = $jahr-1;
	$matrikelnummer = sprintf("%02d",$jahr).$art.sprintf("%04d",$studiengang_kz);
	
	$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE '$matrikelnummer%' ORDER BY matrikelnr DESC LIMIT 1";
	
	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
		{
			$max = substr($row->matrikelnr,7);
		}
		else 
			$max = 0;
		
		$max += 1;
		return $matrikelnummer.sprintf("%03d",$max);
	}
	else 
	{
		return false;
	}
}

// ****
// * Generiert die UID
// * FORMAT: el07b001
// * el = studiengangskuerzel
// * 07 = Jahr
// * b/m/d/x = Bachelor/Master/Diplom/Incomming
// * 001 = Laufende Nummer ( Wenn StSem==SS dann wird zur nummer 500 dazugezaehlt)
// ****
function generateUID($conn, $matrikelnummer)
{
	$jahr = substr($matrikelnummer,0, 2);
	$art = substr($matrikelnummer, 2, 1);
	$stg = substr($matrikelnummer, 3, 4);
	$nr = substr($matrikelnummer, 7);
	
	if($art=='2')
		$nr = $nr+500;
	
	$stg_obj = new studiengang($conn);
	$stg_obj->load(ltrim($stg,'0'));
	
	return $stg_obj->kurzbz.$jahr.($art!='0'?$stg_obj->typ:'x').$nr;	
}
// ***


if(!$error)
{
	
	if(isset($_POST['type']) && $_POST['type']=='savestudent')
	{
		//Studentendaten Speichern
		
		if(!$error)
		{
			$student = new student($conn, null, true);
			
			if(!$student->load($_POST['uid']))
			{
				$return = false;
				$errormsg = 'Fehler beim laden:'.$student->errormsg;
				$error = true;
			}
			
			if(!$error)
			{
				$student->uid = $_POST['uid'];
				$student->anrede = $_POST['anrede'];
				$student->titelpre = $_POST['titelpre'];
				$student->titelpost = $_POST['titelpost'];
				$student->vorname = $_POST['vorname'];
				$student->vornamen = $_POST['vornamen'];
				$student->nachname = $_POST['nachname'];
				$student->gebdatum = $_POST['geburtsdatum'];
				$student->gebort = $_POST['geburtsort'];
				$student->gebzeit = $_POST['geburtszeit'];
				$student->anmerkungen = $_POST['anmerkung'];
				$student->homepage = $_POST['homepage'];
				$student->svnr = $_POST['svnr'];
				$student->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$student->familienstand = $_POST['familienstand'];
				$student->geschlecht = $_POST['geschlecht'];
				$student->aktiv = ($_POST['aktiv']=='true'?true:false);
				$student->anzahlkinder = $_POST['anzahlderkinder'];
				$student->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$student->geburtsnation = $_POST['geburtsnation'];
				$student->sprache = $_POST['sprache'];
				$student->matrikelnr = $_POST['matrikelnummer'];
				
				$stsem = new studiensemester($conn, null, true);
				$stsem_kurzbz = $stsem->getaktorNext();
				//Wenn das ausgewaehlte Semester das aktuelle ist, dann wird auch in der
				//Tabelle Student der Stg/Semester/Verband/Gruppe geaendert.
				//Sonst nur in der Tabelle Studentlehrverband
				if($semester_aktuell == $stsem_kurzbz)
				{
					$student->studiengang_kz = $_POST['studiengang_kz'];
					$student->semester = $_POST['semester'];
					$student->verband = ($_POST['verband']==''?' ':$_POST['verband']);
					$student->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
				}
				
				$student->new=false;				

				if(!$error)
				{
					if($student->save())
					{
						$student_lvb = new student($conn, null, true);
						$student_lvb->uid = $_POST['uid'];
						$student_lvb->studiensemester_kurzbz = $semester_aktuell;
						$student_lvb->studiengang_kz = $_POST['studiengang_kz'];
						$student_lvb->semester = $_POST['semester'];
						$student_lvb->verband = ($_POST['verband']==''?' ':$_POST['verband']);
						$student_lvb->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
						$student_lvb->updateamum = date('Y-m-d H:i:s');
						$student_lvb->updatevon = $user;
						
						if($student_lvb->save_studentlehrverband(false))
						{
							$return = true;
							$error=false;
							$data = $student->uid;
						}
						else 
						{
							$error = true;
							$errormsg = $student_lvb->errormsg;
							$return = false;
						}
					}
					else
					{
						$return = false;
						$errormsg  = $student->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveperson')
	{
		//Personendaten Speichern

		if(!$error)
		{
			$person = new person($conn, null, true);
			
			if(!$person->load($_POST['person_id']))
			{
				$return = false;
				$errormsg = 'Fehler beim laden:'.$person->errormsg;
				$error = true;
			}
			
			if(!$error)
			{
				$person->person_id = $_POST['person_id'];
				$person->anrede = $_POST['anrede'];
				$person->titelpre = $_POST['titelpre'];
				$person->titelpost = $_POST['titelpost'];
				$person->vorname = $_POST['vorname'];
				$person->vornamen = $_POST['vornamen'];
				$person->nachname = $_POST['nachname'];
				$person->gebdatum = $_POST['geburtsdatum'];
				$person->gebort = $_POST['geburtsort'];
				$person->gebzeit = $_POST['geburtszeit'];
				$person->anmerkungen = $_POST['anmerkung'];
				$person->homepage = $_POST['homepage'];
				$person->svnr = $_POST['svnr'];
				$person->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$person->familienstand = $_POST['familienstand'];
				$person->geschlecht = $_POST['geschlecht'];
				$person->aktiv = ($_POST['aktiv']=='true'?true:false);
				$person->anzahlkinder = $_POST['anzahlderkinder'];
				$person->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$person->geburtsnation = $_POST['geburtsnation'];
				$person->sprache = $_POST['sprache'];
								
				$person->new=false;				

				if(!$error)
				{
					if($person->save())
					{
						$return = true;
						$error=false;
						$data = $person->person_id;
					}
					else
					{
						$return = false;
						$errormsg  = $person->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveprestudent')
	{
		//Studentendaten Speichern

		if(!$error)
		{
			$prestudent = new prestudent($conn, null, true);
			
			if(!$prestudent->load($_POST['prestudent_id']))
			{
				$return = false;
				$errormsg = 'Fehler beim laden:'.$prestudent->errormsg;
				$error = true;
			}
			
			if(!$error)
			{
				$prestudent->prestudent_id = $_POST['prestudent_id'];
				$prestudent->aufmerksamdurch_kurzbz = $_POST['aufmerksamdurch_kurzbz'];
				$prestudent->person_id = $_POST['person_id'];
				$prestudent->studiengang_kz = $_POST['studiengang_kz'];
				$prestudent->berufstaetigkeit_code = $_POST['berufstaetigkeit_code'];
				$prestudent->ausbildungcode = $_POST['ausbildungcode'];
				$prestudent->zgv_code = $_POST['zgv_code'];
				$prestudent->zgvort = $_POST['zgvort'];
				$prestudent->zgvdatum = $_POST['zgvdatum'];
				$prestudent->zgvmas_code = $_POST['zgvmas_code'];
				$prestudent->zgvmaort = $_POST['zgvmaort'];
				$prestudent->zgvmadatum = $_POST['zgvmadatum'];
				$prestudent->aufnahmeschluessel = $_POST['aufnahmeschluessel'];
				$prestudent->facheinschlberuf = ($_POST['facheinschlberuf']=='true'?true:false);
				$prestudent->reihungstest_id = $_POST['reihungstest_id'];
				$prestudent->anmeldungreihungstest = $_POST['anmeldungreihungstest'];
				$prestudent->reihungstestangetreten = ($_POST['reihungstestangetreten']=='true'?true:false);
				$prestudent->punkte = $_POST['punkte'];
				$prestudent->bismelden = ($_POST['bismelden']=='true'?true:false);
				$prestudent->anmerkung = $_POST['anmerkung'];
				//$prestudent->insertamum = date('Y-m-d H:i:s');
				//$prestudent->insertvon = $user;
				$prestudent->updateamum = date('Y-m-d H:i:s');
				$prestudent->updatevon = $user;				
				$prestudent->new=false;	
				
				if(!$error)
				{
					if($prestudent->save())
					{
						$return = true;
						$error=false;
						$data = $prestudent->prestudent_id;
					}
					else
					{
						$return = false;
						$errormsg  = $prestudent->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='addrolle')
	{
		//Prestudentrolle hinzufuegen

		if(!$error)
		{
			if(isset($_POST['prestudent_id']))
			{
				$prestd = new prestudent($conn);
				if($prestd->getLastStatus($_POST['prestudent_id']))
				{
					$hlp = new prestudent($conn);
					$hlp->getPrestudentRolle($_POST['prestudent_id'], $_POST['rolle_kurzbz'], $prestd->studiensemester_kurzbz);
					if(count($hlp->result)>0)
					{
						$errormsg = 'Diese Rolle ist bereits vorhanden';
						$return = false;
					}
					else 
					{
						$prestd_neu = new prestudent($conn);
						$prestd_neu->prestudent_id = $_POST['prestudent_id'];
						$prestd_neu->rolle_kurzbz = $_POST['rolle_kurzbz'];
						$prestd_neu->studiensemester_kurzbz = $prestd->studiensemester_kurzbz;
						$prestd_neu->datum = date('Y-m-d');
						$prestd_neu->ausbildungssemester = $prestd->ausbildungssemester;
						$prestd_neu->insertamum = date('Y-m-d H:i:s');
						$prestd_neu->insertvon = $user;
						$prestd_neu->new = true;
						
						if($prestd_neu->save_rolle())
						{
							//Unterbrecher und Abbrecher werden ins 0. Semester verschoben
							if($_POST['rolle_kurzbz']=='Unterbrecher' || $_POST['rolle_kurzbz']=='Abbrecher')
							{
								$student = new student($conn);
								$uid = $student->getUid($_POST['prestudent_id']);
								$student->load($uid);
								$student->studiensemester_kurzbz=$semester_aktuell;
								$student->semester = '0';
								$student->save(false, false);
								$student->save_studentlehrverband(false);
							}
							
							//Wenn Unterbrecher zu Studenten werden, dann wird das Semester mituebergeben
							if($_POST['rolle_kurzbz']=='Student')
							{
								$student = new student($conn);
								$uid = $student->getUid($_POST['prestudent_id']);
								$student->load($uid);
								$student->studiensemester_kurzbz=$semester_aktuell;
								$student->semester = $_POST['semester'];
								$student->save(false, false);
								$student->save_studentlehrverband(false);
							}
							$return = true;
						}
						else 
						{
							$return = false;
							$errormsg = $prestd_neu->errormsg;
						}
					}
				}
				else 
				{
					$return = false;
					$errormsg = 'Es ist keine Rolle fuer diesen Prestudent vorhanden';
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'Prestudent_id muss angegeben werden';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='BewerberZuStudent')
	{
		// macht aus einem Bewerber einen Studenten
		// Voraussetzungen:
		// - ZGV muss ausgefuellt sein (bei Master beide)
		// - Kaution muss bezahlt sein
		// - Rolle Bewerber muss existieren
		// Wenn die Voraussetzungen erfuellt sind, dann wird die Matrikelnr
		// und UID generiert und der Studentendatensatz angelegt.

		if(!$error)
		{
			if(isset($_POST['prestudent_id']))
			{
				$prestd = new prestudent($conn);
				if($prestd->load($_POST['prestudent_id']))
				{
					if($prestd->zgv_code!='')
					{
						$stg = new studiengang($conn);
						$stg->load($prestd->studiengang_kz);
						
						if($stg->typ=='m' && $prestd->zgvmas_code=='')
						{
							$return = false;
							$errormsg = 'ZGV Master muss eingegeben werden';
						}
						else 
						{
							//Pruefen ob die Rolle Bewerber existiert
							$hlp = new prestudent($conn);
							$hlp->getPrestudentRolle($_POST['prestudent_id'], 'Bewerber',null,'datum DESC, insertamum DESC');
						
							if(count($hlp->result)>0)
							{
								//pruefen ob schon eine Studentenrolle Existiert
								$hlp1 = new prestudent($conn);
								$hlp1->getPrestudentRolle($_POST['prestudent_id'], 'Student', $hlp->result[0]->studiensemester_kurzbz);
								if(count($hlp1->result)>0)
								{
									$return = false;
									$errormsg = 'Diese Person ist bereits Student';
								}
								else 
								{
									//pruefen ob die Kaution bezahlt wurde
									//??
									
									pg_query($conn, 'BEGIN;');
									
									//Matrikelnummer und UID generieren
									$matrikelnr = generateMatrikelnummer($conn, $prestd->studiengang_kz, $hlp->result[0]->studiensemester_kurzbz);
									$uid = generateUID($conn, $matrikelnr);
									
									$return = false;
									$errormsg = "Matrikelnummer: $matrikelnr, UID: $uid";
									
									//Benutzerdatensatz anlegen
									$benutzer = new benutzer($conn);
									$benutzer->uid = $uid;
									$benutzer->person_id = $prestd->person_id;
									$benutzer->aktiv = true;
									
									$qry_alias = "SELECT * FROM public.tbl_benutzer WHERE alias='$prestd->vorname.$prestd->nachname'";
									$result_alias = pg_query($conn, $qry_alias);
									if(pg_num_rows($result_alias)==0)								
										$benutzer->alias = $prestd->vorname.'.'.$prestd->nachname;
									else 
										$benutzer->alias = '';
									
									$benutzer->insertamum = date('Y-m-d H:i:s');
									$benutzer->insertvon = $user;
																	
									if($benutzer->save(true, false))
									{
										//Studentendatensatz anlegen
										$student = new student($conn);
										$student->uid = $uid;
										$student->matrikelnr = $matrikelnr;
										$student->prestudent_id = $prestd->prestudent_id;
										$student->studiengang_kz = $prestd->studiengang_kz;
										$student->semester = 1; //$hlp->result[0]->ausbildungssemester
										$student->verband = ' ';
										$student->gruppe = ' ';
										$student->insertamum = date('Y-m-d H:i:s');
										$student->insertvon = $user;
										
										if($student->save(true, false))
										{
											//Prestudentrolle hinzugfuegen
											$rolle = new prestudent($conn);
											$rolle->prestudent_id = $prestd->prestudent_id;
											$rolle->rolle_kurzbz = 'Student';
											$rolle->studiensemester_kurzbz = $semester_aktuell; //$hlp->result[0]->studiensemester_kurzbz;
											$rolle->ausbildungssemester = 1; //$hlp->result[0]->ausbildungssemester
											$rolle->datum = date('Y-m-d');
											$rolle->insertamum = date('Y-m-d H:i:s');
											$rolle->insertvon = $user;
											$rolle->new = true;
											
											if($rolle->save_rolle())
											{
												//StudentLehrverband anlegen
												$studentlehrverband = new student($conn);
												$studentlehrverband->uid = $uid;
												$studentlehrverband->studiensemester_kurzbz = $semester_aktuell; //$hlp->result[0]->studiensemester_kurzbz;
												$studentlehrverband->studiengang_kz = $prestd->studiengang_kz;
												$studentlehrverband->semester = 1; //$hlp->result[0]->ausbildungssemester
												$studentlehrverband->verband = ' ';
												$studentlehrverband->gruppe = ' ';
												$studentlehrverband->insertamum = date('Y-m-d H:i:s');
												$studentlehrverband->insertvon = $user;
												
												if($studentlehrverband->save_studentlehrverband(true))
												{
													$return = true;
													pg_query($conn, 'COMMIT;');
												}
												else 
												{
													$errormsg = 'Fehler beim Speichern des Studentlehrverbandes: '.$studentlehrverband->errormsg;
													$return = false;
													pg_query($conn, 'ROLLBACK;');
												}
											}
											else 
											{
												$errormsg = 'Fehler beim Speichern des Rolle: '.$rolle->errormsg;
												$return = false;
												pg_query($conn, 'ROLLBACK;');
											}
										}
										else 
										{
											$errormsg = 'Fehler beim Speichern des Studenten: '.$student->errormsg;
											$return = false;
											pg_query($conn, 'ROLLBACK;');
										}
									}
									else 
									{
										$errormsg = 'Fehler beim Speichern des Benutzers: '.$benutzer->errormsg;
										$return = false;
										pg_query($conn, 'ROLLBACK;');
									}
								}
							}
							else 
							{
								$return = false;
								$errormsg = 'Die Person muss zuerst Bewerber sein bevor Sie zum Studenten gemacht werden kann';
							}
						}
					}
					else 
					{
						$return = false;
						$errormsg = 'ZGV muss eingegeben werden';
					}
				}
				else 
				{
					$return = false;
					$errormsg = 'Prestudent wurde nicht gefunden';
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'Prestudent_id muss angegeben werden';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='gruppenzuteilung')
	{
		if(isset($_POST['uid']) && isset($_POST['gruppe_kurzbz']))
		{
			$benutzergruppe = new benutzergruppe($conn);
			
			$uids = explode(';',$_POST['uid']);
			$errormsg = '';
			foreach ($uids as $uid)
			{
				if($uid!='')
				{
					if(!$benutzergruppe->load($uid, $_POST['gruppe_kurzbz']))
					{
						$benutzergruppe->uid = $uid;
						$benutzergruppe->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
						$benutzergruppe->studiensemester_kurzbz = $semester_aktuell;
						$benutzergruppe->insertamum = date('Y-m-d H:i:s');
						$benutzergruppe->insertvon = $user;				
						$benutzergruppe->new = true;
						
						if(!$benutzergruppe->save())
						{
							$errormsg .= "$uid konnte nicht hinzugefuegt werden\n";
						}
					}
					else
						$errormsg .= "Der Student $uid ist bereits in dieser Gruppe\n";
				}
			}
			if($errormsg=='')
				$return = true;
			else 
				$return = false;
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteGruppenzuteilung')
	{
		if(isset($_POST['uid']) && isset($_POST['gruppe_kurzbz']))
		{
			$uids = explode(';',$_POST['uid']);
			$errormsg = '';
			foreach ($uids as $uid)
			{
				$benutzergruppe = new benutzergruppe($conn);
	
				if(!$benutzergruppe->delete($uid, $_POST['gruppe_kurzbz']))
				{
					$errormsg .= "$uid konnte nicht aus der Gruppe geloescht werden\n";
				}
			}
			if($errormsg=='')
				$return = true;
			else 
				$return = false;
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteAkte')
	{
		if(isset($_POST['akte_id']) && is_numeric($_POST['akte_id']))
		{
			$akte = new akte($conn);

			if($akte->delete($_POST['akte_id']))
			{
				$return = true;
			}
			else 
			{
				$return = false;
				$errormsg = $akte->errormsg;
			}				
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['akte_id'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebuchung') // ***** KONTO *****
	{
		//Speichert eine Buchung
		if(isset($_POST['buchungsnr']) && is_numeric($_POST['buchungsnr']))
		{
			$buchung = new konto($conn, null, true);

			if($buchung->load($_POST['buchungsnr']))
			{
				$buchung->betrag = $_POST['betrag'];
				$buchung->buchungsdatum = $_POST['buchungsdatum'];
				$buchung->buchungstext = $_POST['buchungstext'];
				$buchung->mahnspanne = $_POST['mahnspanne'];
				$buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
				$buchung->new = false;
				$buchung->updateamum = date('Y-m-d H:i:s');
				$buchung->updatevon = $user;
				
				if($buchung->save())
				{
					$return = true;
				}
				else 
				{
					$return = false;
					$errormsg = 'Fehler beim Speicher:'.$buchung->errormsg;
				}
			}
			else 
			{
				$errormsg = 'Buchung wurde nicht gefunden:'.$_POST['buchungsnr'];
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['buchungsnr'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savegegenbuchung')
	{
		//Speichert eine Buchung
		if(isset($_POST['buchungsnr']) && is_numeric($_POST['buchungsnr']))
		{
			$buchung = new konto($conn, null, true);

			if($buchung->load($_POST['buchungsnr']))
			{
				if($buchung->buchungsnr_verweis=='')
				{
					$kto = new konto($conn, null, true);
					//$buchung->betrag*(-1);					
					$buchung->betrag = $kto->getDifferenz($_POST['buchungsnr']);
					$buchung->buchungsdatum = date('Y-m-d');
					$buchung->mahnspanne = '0';
					$buchung->buchungsnr_verweis = $buchung->buchungsnr;
					$buchung->new = true;
					$buchung->insertamum = date('Y-m-d H:i:s');
					$buchung->insertvon = $user;
					
					if($buchung->save())
					{
						$data = $buchung->buchungsnr;
						$return = true;
					}
					else 
					{
						$return = false;
						$errormsg = 'Fehler beim Speichern:'.$buchung->errormsg;
					}
				}
				else 
				{
					$return = false;
					$errormsg = 'Gegenbuchungen koennen nur auf die obersten Buchungen getaetigt werden';
				}
			}
			else 
			{
				$errormsg = 'Buchung wurde nicht gefunden:'.$_POST['buchungsnr'];
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['buchungsnr'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebuchung')
	{
		//Loescht eine Buchung
		if(isset($_POST['buchungsnr']) && is_numeric($_POST['buchungsnr']))
		{
			$buchung = new konto($conn, null, true);

			if($buchung->delete($_POST['buchungsnr']))
			{
				$return = true;
			}
			else 
			{
				$errormsg = $buchung->errormsg;
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe'.$_POST['buchungsnr'];
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='neuebuchung')
	{
		//Speichert eine neue Buchung
		//Gleichzeitiges speichern mehrerer Personen ist moeglich
		//Personen werden durch ';' getrennt
		$person_ids = explode(';',$_POST['person_ids']);
		$errormsg = '';
		foreach ($person_ids as $person_id)
		{
			if($person_id!='')
			{
				$buchung = new konto($conn, null, true);
				$buchung->person_id = $person_id;
				$buchung->studiengang_kz = $_POST['studiengang_kz'];
				$buchung->studiensemester_kurzbz = $semester_aktuell;
				$buchung->buchungsnr_verweis='';
				$buchung->betrag = $_POST['betrag'];
				$buchung->buchungsdatum = $_POST['buchungsdatum'];
				$buchung->buchungstext = $_POST['buchungstext'];
				$buchung->mahnspanne = $_POST['mahnspanne'];
				$buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
				$buchung->insertamum = date('Y-m-d H:i:s');
				$buchung->insertvon = $user;
				$buchung->new = true;
				
				if($buchung->save())
				{
					$data = $buchung->buchungsnr;
				}
				else 
				{
					$errormsg .= "Fehler bei $person_id: $buchung->errormsg\n";
				}
			}
		}
		if($errormsg=='')
			$return = true;
		else 
			$return = false;			
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentadd')
	{
		//Speichert die Zuordnung von Dokumenten zu einem Prestudent
		//Gleichzeitiges zuteilen mehrerer Dokumente auf einmal ist moeglich
		//Dokumente werden durch ';' getrennt uebergeben
		$dokumente = explode(';',$_POST['dokumente']);
		$errormsg = '';
		foreach ($dokumente as $dokument_kurzbz)
		{
			if($dokument_kurzbz!='')
			{
				$dok = new dokument($conn, null, null, true);
				$dok->dokument_kurzbz = $dokument_kurzbz;
				$dok->prestudent_id = $_POST['prestudent_id'];
				$dok->mitarbeiter_uid = $user;
				$dok->datum = date('Y-m-d');
				$dok->insertamum = date('Y-m-d H:i:s');
				$dok->insertvon = $user;				
				$dok->new = true;
				
				if(!$dok->save())
				{
					$errormsg .= "Fehler bei $dokument_kurzbz: $dok->errormsg\n";
				}
			}
		}
		if($errormsg=='')
			$return = true;
		else 
			$return = false;			
	}
	elseif(isset($_POST['type']) && $_POST['type']=='dokumentprestudentdel')
	{
		//Loescht die Zuordnung von Dokumenten zu einem Prestudent
		//Gleichzeitiges loeschen mehrerer Dokumente auf einmal ist moeglich
		//Dokumente werden durch ';' getrennt uebergeben
		$dokumente = explode(';',$_POST['dokumente']);
		$errormsg = '';
		foreach ($dokumente as $dokument_kurzbz)
		{
			if($dokument_kurzbz!='')
			{
				$dok = new dokument($conn, null, null, true);
				if($dok->load($dokument_kurzbz, $_POST['prestudent_id']))
				{
					if($dok->mitarbeiter_uid==$user)
					{
						if(!$dok->delete($dokument_kurzbz, $_POST['prestudent_id']))
						{
							$errormsg .= "Fehler bei $dokument_kurzbz: $dok->errormsg\n";
						}
					}
					else 
					{
						$errormsg.="Fehler bei $dokument_kurzbz: Loeschen nur durch $mitarbeiter_uid moeglich\n";
					}
				}
				else 
				{
					$errormsg.="Dokumentenzuteilung existiert nicht: $dokument_kurzbz\n";
				}
			}
		}
		if($errormsg=='')
			$return = true;
		else 
			$return = false;			
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebetriebsmittel')
	{
		//Loescht eine Buchung
		if(isset($_POST['betriebsmittel_id']) && is_numeric($_POST['betriebsmittel_id']) &&
		   isset($_POST['person_id']) && is_numeric($_POST['person_id']))
		{
			$btm = new betriebsmittelperson($conn, null,null, true);

			if($btm->delete($_POST['betriebsmittel_id'], $_POST['person_id']))
			{
				$return = true;
			}
			else 
			{
				$errormsg = $btm->errormsg;
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebetriebsmittel')
	{
		//Speichert eine Betriebsmittelzuordnung
		$bm = new betriebsmittel($conn, null, true);
		
		//Nachschauen ob dieses Betriebsmittel schon existiert
		if($bm->getBetriebsmittel($_POST['betriebsmitteltyp'],$_POST['nummer']))
		{
			if(count($bm->result)>0)
			{
				//Wenn ein Eintrag gefunden wurde, dann wird die Beschreibung aktualisiert
				if($bm->load($bm->result[0]->betriebsmittel_id))
				{
					$bm->beschreibung = $_POST['beschreibung'];
					if(!$bm->save(false))
					{
						$return = false;
						$error = true;
						$errormsg = 'Fehler beim Speichern des Betriebsmittels';
					}
					else 
					{
						$betriebsmittel_id = $bm->betriebsmittel_id;
					}
				}
				else 
				{
					$return = false;
					$error = true;
					$errormsg = 'Gefundener Eintrag konnte nicht geladen werden!?!?';
				}
			}
			else
			{
				//Wenn kein Eintrag gefunden wurde, dann wird ein neuer Eintrag angelegt
				$bm->betriebsmitteltyp = $_POST['betriebsmitteltyp'];
				$bm->nummer = $_POST['nummer'];
				$bm->beschreibung = $_POST['beschreibung'];
				$bm->reservieren = false;
				$bm->ort_kurzbz = null;
				$bm->insertamum = date('Y-m-d H:i:s');
				$bm->insertvon = $user;
			
				if($bm->save(true))
				{
					$betriebsmittel_id = $bm->betriebsmittel_id;
				}
				else 
				{
					$error = true;
					$return = false;
					$errormsg = 'Fehler beim Anlegen des Betriebsmittels';
				}
			}
						
			//Zuordnung Betriebsmittel-Person anlgegen
			$bmp = new betriebsmittelperson($conn, null, null, true);
			if($_POST['neu']!='true')
			{
				if($bmp->load($betriebsmittel_id, $_POST['person_id']))
				{
					$bmp->updateamum = date('Y-m-d H:i:s');
					$bmp->updatevon = $user;
					$bmp->new = false;
				}
				else 
				{
					$error = true;
					$return = false;
					$errormsg = 'Fehler beim laden der Betriebmittelperson Zuordnung';
				}
			}
			else 
			{
				$bmp->insertamum = date('Y-m-d H:i:s');
				$bmp->insertvon = $user;
				$bmp->new = true;
			}

			if(!$error)
			{
				$bmp->person_id = $_POST['person_id'];
				$bmp->betriebsmittel_id=$betriebsmittel_id;
				$bmp->anmerkung = $_POST['anmerkung'];
				$bmp->kaution = str_replace(',','.',$_POST['kaution']);
				$bmp->ausgegebenam = $_POST['ausgegebenam'];
				$bmp->retouram = $_POST['retouram'];
				
				if($bmp->save())
				{
					$return = true;
					$data = $betriebsmittel_id;
				}
				else 
				{
					$return = false;
					$errormsg = $bmp->errormsg;
				}
			}
		}
		else 
		{
			$errormsg = 'Fehler:'.$bm->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletebisio')
	{
		//Loescht einen BisIO Eintrag
		if(isset($_POST['bisio_id']) && is_numeric($_POST['bisio_id']))
		{
			$bisio = new bisio($conn);

			if($bisio->delete($_POST['bisio_id']))
			{
				$return = true;
			}
			else 
			{
				$errormsg = $bisio->errormsg;
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg  = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savebisio')
	{
		//Speichert einen BisIO Eintrag
		
		$bisio = new bisio($conn);

		if($_POST['neu']=='true')
		{
			$bisio->insertamum = date('Y-m-d H:i:s');
			$bisio->insertvon = $user;
			$bisio->new = true;
		}
		else 
		{
			if($bisio->load($_POST['bisio_id']))
				$bisio->new = false;
			else 
			{
				$error = true;
				$errormsg = $bisio->errormsg;
				$return = false;
			}				
		}
		
		$bisio->bisio_id = (isset($_POST['bisio_id'])?$_POST['bisio_id']:'');
		$bisio->mobilitaetsprogramm_code = $_POST['mobilitaetsprogramm_code'];
		$bisio->nation_code = $_POST['nation_code'];
		$bisio->von = $_POST['von'];
		$bisio->bis = $_POST['bis'];
		$bisio->zweck_code = $_POST['zweck_code'];
		$bisio->student_uid = $_POST['student_uid'];
		$bisio->updateamum = date('Y-m-d H:i:s');
		$bisio->updatevon = $user;
		
		if(!$error)
		{
			if($bisio->save())
			{
				$return = true;
				$data = $bisio->bisio_id;
			}
			else 
			{
				$errormsg = $bisio->errormsg;
				$return = false;
			}
		}		
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savenote')
	{
		//Speichert einen Noteneintrag
		
		$noten = new zeugnisnote($conn);

		if(isset($_POST['lehrveranstaltung_id']) && isset($_POST['student_uid']) && isset($_POST['studiensemester_kurzbz']))
		{
			if($noten->load($_POST['lehrveranstaltung_id'], $_POST['student_uid'], $_POST['studiensemester_kurzbz']))
			{
				$noten->new = false;
				$noten->updateamum = date('Y-m-d H:i:s');
				$noten->updatevon = $user;
			}
			else 
			{
				$noten->new = true;
				$noten->insertamum = date('Y-m-d H:i:s');
				$noten->insertvon = $user;
			}
			
			$noten->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
			$noten->student_uid = $_POST['student_uid'];
			$noten->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
			$noten->benotungsdatum = date('Y-m-d H:i:s');
			$noten->note = $_POST['note'];
					
			if($noten->save())
			{
				$return = true;
			}
			else 
			{
				$errormsg = $noten->errormsg;
				$return = false;
			}
		}
		else 
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='movenote')
	{
		//Speichert einen LVGesamtNoten Eintrag in die Tbl Zeugnisnote
		//Die Daten werden per POST uebermittelt. Es wird ein Feld Anzahl mituebergeben
		//mit der Anzahl der Felder. Die Felder sind durchnummeriert zB lehreinheit_id_0, lehreinheit_id_1, ...
		$errormsg = '';
		
		for($i=0;$i<$_POST['anzahl'];$i++)
		{
			$lvgesamtnote = new lvgesamtnote($conn);
			$zeugnisnote = new zeugnisnote($conn);
			
			if($lvgesamtnote->load($_POST['lehrveranstaltung_id_'.$i], $_POST['student_uid_'.$i], $_POST['studiensemester_kurzbz_'.$i]))
			{					
				if($zeugnisnote->load($_POST['lehrveranstaltung_id_'.$i], $_POST['student_uid_'.$i], $_POST['studiensemester_kurzbz_'.$i]))
				{
					$zeugnisnote->new = false;
					$zeugnisnote->updateamum = date('Y-m-d H:i:s');
					$zeugnisnote->updatevon = $user;
				}
				else 
				{
					$zeugnisnote->new = true;
					$zeugnisnote->insertamum = date('Y-m-d H:i:s');
					$zeugnisnote->insertvon = $user;
					$zeugnisnote->lehrveranstaltung_id = $_POST['lehrveranstaltung_id_'.$i];
					$zeugnisnote->student_uid = $_POST['student_uid_'.$i];
					$zeugnisnote->studiensemester_kurzbz = $_POST['studiensemester_kurzbz_'.$i];
				}
				
				$zeugnisnote->note = $lvgesamtnote->note;
				$zeugnisnote->uebernahmedatum = date('Y-m-d H:i:s');
				$zeugnisnote->benotungsdatum = $lvgesamtnote->benotungsdatum;
				$zeugnisnote->bemerkung = $lvgesamtnote->bemerkung;
					
				if(!$zeugnisnote->save())
				{
					$errormsg .= "\n".$zeugnisnote->errormsg;
				}
			}
			else 
			{
				$errormsg .= "\nLvGesamtNote wurde nicht gefunden";
			}
		}
		if($errormsg=='')
			$return = true;
		else 
			$return = false;
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type: "'.$_POST['type'].'"';
		$data = '';
	}
}
?>
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return><?php echo ($return?'true':'false'); ?></DBDML:return>
        	<DBDML:errormsg><![CDATA[<?php echo $errormsg; ?>]]></DBDML:errormsg>
        	<DBDML:data><![CDATA[<?php echo $data ?>]]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
