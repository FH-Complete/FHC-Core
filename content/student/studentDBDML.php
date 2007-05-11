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
				$student->studiengang_kz = $_POST['studiengang_kz'];
				$student->semester = $_POST['semester'];
				$student->verband = ($_POST['verband']==''?' ':$_POST['verband']);
				$student->gruppe = ($_POST['gruppe']==''?' ':$_POST['gruppe']);
				
				$student->new=false;				

				if(!$error)
				{
					if($student->save())
					{
						$student_lvb = new student($conn);
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
								if(count($hlp1)>0)
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
										$student->semester = $hlp->result[0]->ausbildungssemester;
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
											$rolle->studiensemester_kurzbz = $hlp->result[0]->studiensemester_kurzbz;
											$rolle->ausbildungssemester = $hlp->result[0]->ausbildungssemester;
											$rolle->datum = date('Y-m-d');
											$rolle->insertamum = date('Y-m-d H:i:s');
											$rolle->insertvon = $user;
											$rolle->new = true;
											
											if($rolle->save_rolle())
											{
												//StudentLehrverband anlegen
												$studentlehrverband = new student($conn);
												$studentlehrverband->uid = $uid;
												$studentlehrverband->studiensemester_kurzbz = $hlp->result[0]->studiensemester_kurzbz;
												$studentlehrverband->studiengang_kz = $prestd->studiengang_kz;
												$studentlehrverband->semester = $hlp->result[0]->ausbildungssemester;
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
