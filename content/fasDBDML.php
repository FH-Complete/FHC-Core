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
// * Script sorgt fuer den Datenbanzugriff
// * der folgender FASonline Daten:
// *
// * - Adressen
// * - Kontakte
// * - Bankverbindungen
// ****************************************

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/log.class.php');
require_once('../include/adresse.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/bankverbindung.class.php');
require_once('../include/variable.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/fotostatus.class.php');
require_once('../include/anwesenheit.class.php');

$user = get_uid();

$db = new basis_db();
$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('mitarbeiter') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lv-plan'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	//in der Variable type wird die auszufuehrende Aktion mituebergeben
	if(isset($_POST['type']) && $_POST['type']=='adressesave') // ***** ADRESSEN ***** //
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Speichert die Adressdaten in die Datenbank
			$adresse = new adresse();

			if($_POST['neu']=='false')
			{
				$adresse->load($_POST['adresse_id']);
				$adresse->new = false;
			}
			else
			{
				$adresse->insertamum = date('Y-m-d H:i:s');
				$adresse->insertvon = $user;
				$adresse->new = true;
			}

			$adresse->adresse_id = $_POST['adresse_id'];
			$adresse->person_id = $_POST['person_id'];
			$adresse->name = $_POST['name'];
			$adresse->strasse = $_POST['strasse'];
			$adresse->plz = $_POST['plz'];
			$adresse->ort = $_POST['ort'];
			$adresse->gemeinde = $_POST['gemeinde'];
			$adresse->nation = $_POST['nation'];
			$adresse->typ = $_POST['typ'];
			$adresse->heimatadresse = ($_POST['heimatadresse']=='true'?true:false);
			$adresse->zustelladresse = ($_POST['zustelladresse']=='true'?true:false);
			$adresse->co_name = $_POST['co_name'];
			$adresse->firma_id = $_POST['firma_id'];
			$adresse->updateamum = date('Y-m-d H:i:s');
			$adresse->updatevon = $user;
			$adresse->rechnungsadresse = ($_POST['rechnungsadresse']=='true'?true:false);
			$adresse->anmerkung = $_POST['anmerkung'];

			//Wenn die Nation Oesterreich ist, dann muss die Gemeinde in der Tabelle Gemeinde vorkommen
			if($_POST['nation']=='A')
			{
				if(is_numeric($_POST['plz']) && $_POST['plz']<32000)
				{
					$qry = "SELECT * FROM bis.tbl_gemeinde WHERE lower(name)=lower(".$db->db_add_param($_POST['gemeinde']).")
							AND plz=".$db->db_add_param($_POST['plz']);
					if($db->db_query($qry))
					{
						if($row = $db->db_fetch_object())
						{
							$adresse->gemeinde = $row->name;
						}
						else
						{
							$error = true;
							$errormsg = 'Gemeinde ist ungueltig';
							$return = false;
						}
					}
					else
					{
						$error = true;
						$errormsg = 'Fehler beim Ermitteln der Gemeinde';
						$return = false;
					}
				}
				else
				{
					$error = true;
					$errormsg = 'Postleitzahl ist fuer diese Nation ungueltig';
					$return = false;
				}
			}

			if(!$error)
			{
				if($adresse->save())
				{
					$return = true;
					$data = $adresse->adresse_id;
				}
				else
				{
					$return = false;
					$errormsg = $adresse->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='adressedelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Loescht Adressen aus der DB
			$adresse = new adresse();
			if(!$adresse->load($_POST['adresse_id']))
			{
				$return = false;
				$errormsg = $adresse->errormsg;
			}
			else
			{
				if($adresse->heimatadresse)
				{
					//Heimatadressen nicht loeschen, da es sonst zu Problemen bei der BIS-Meldung kommt falls diese Adresse
					//schon einmal gemeldet wurde
					$return = false;
					$errormsg = 'Heimatadressen dürfen nicht gelöscht werden, da diese für die BIS-Meldung relevant sind. Um die Adresse dennoch zu löschen, entfernen sie das Hackerl bei Heimatadresse!';
				}
				else
				{
					if($adresse->delete($_POST['adresse_id']))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $adresse->errormsg;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='kontaktsave') // ***** KONTAKT ***** //
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Speichert die Kontaktdaten in die Datenbank
			$kontakt = new kontakt();

			if($_POST['neu']=='false')
			{
				$kontakt->load($_POST['kontakt_id']);
				$kontakt->new = false;
			}
			else
			{
				$kontakt->insertamum = date('Y-m-d H:i:s');
				$kontakt->insertvon = $user;
				$kontakt->new = true;
			}

			$kontakt->kontakt_id = $_POST['kontakt_id'];
			$kontakt->person_id = $_POST['person_id'];
			$kontakt->anmerkung = $_POST['anmerkung'];
			$kontakt->kontakt = $_POST['kontakt'];
			$kontakt->kontakttyp = $_POST['typ'];
			$kontakt->zustellung = ($_POST['zustellung']=='true'?true:false);
			$kontakt->standort_id = $_POST['standort_id'];
			$kontakt->updateamum = date('Y-m-d H:i:s');
			$kontakt->updatevon = $user;

			if($kontakt->save())
			{
				$return = true;
				$data = $kontakt->kontakt_id;
			}
			else
			{
				$return = false;
				$errormsg = $kontakt->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='kontaktdelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Loescht Kontaktdaten aus der Datenbank
			$kontakt = new kontakt();

			if($kontakt->delete($_POST['kontakt_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $kontakt->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='bankverbindungsave') // ***** BANKVERBINDUNG ***** //
	{
		if(!$rechte->isberechtigt('mitarbeiter/bankdaten') && !$rechte->isberechtigt('student/bankdaten'))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Speichert die Kontaktdaten in die Datenbank
			$bankverbindung = new bankverbindung();

			if($_POST['neu']=='false')
			{
				$bankverbindung->load($_POST['bankverbindung_id']);
				$bankverbindung->new = false;
			}
			else
			{
				$bankverbindung->insertamum = date('Y-m-d H:i:s');
				$bankverbindung->insertvon = $user;
				$bankverbindung->new = true;
			}

			$bankverbindung->bankverbindung_id = $_POST['bankverbindung_id'];
			$bankverbindung->person_id = $_POST['person_id'];
			$bankverbindung->name = $_POST['name'];
			$bankverbindung->anschrift = $_POST['anschrift'];
			$bankverbindung->bic = $_POST['bic'];
			$bankverbindung->blz = $_POST['blz'];
			$bankverbindung->iban = $_POST['iban'];
			$bankverbindung->kontonr = $_POST['kontonr'];
			$bankverbindung->typ = $_POST['typ'];
			$bankverbindung->verrechnung = ($_POST['verrechnung']=='true'?true:false);
			$bankverbindung->updateamum = date('Y-m-d H:i:s');
			$bankverbindung->updatevon = $user;

			if($bankverbindung->save())
			{
				$return = true;
				$data = $bankverbindung->bankverbindung_id;
			}
			else
			{
				$return = false;
				$errormsg = $bankverbindung->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='bankverbindungdelete')
	{
		if(!$rechte->isberechtigt('mitarbeiter/bankdaten') && !$rechte->isberechtigt('student/bankdaten'))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			//Loescht Bankverbindungen aus der Datenbank
			$bankverbindung = new bankverbindung();

			if($bankverbindung->delete($_POST['bankverbindung_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $bankverbindung->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktionsave') // ****************** BENUTZERFUNKTION **************** //
	{
		if(($_POST['studiengang_kz_berecht']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz_berecht'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz_berecht'], 'suid')) ||
		   ($_POST['studiengang_kz_berecht']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			$benutzerfunktion = new benutzerfunktion();
			if(isset($_POST['neu']) && $_POST['neu']=='true')
			{
				$benutzerfunktion->new = true;
				$benutzerfunktion->insertamum=date('Y-m-d H:i:s');
				$benutzerfunktion->insertvon = $user;
			}
			else
			{
				if(isset($_POST['benutzerfunktion_id']))
				{
					if($benutzerfunktion->load($_POST['benutzerfunktion_id']))
					{
						$benutzerfunktion->new = false;
					}
					else
					{
						$error = true;
						$errormsg = 'Fehler beim Laden der Funktion: '.$benutzerfunktion->errormsg;
						$return = false;
					}
				}
				else
				{
					$error = true;
					$errormsg = 'Benutzerfunktion_id wurde nicht uebergeben';
					$return = false;
				}
			}

			if($_POST['funktion_kurzbz']=='fbk' && $_POST['fachbereich_kurzbz']=='')
			{
				$error=true;
				$errormsg='Bei Koordinatoren muss auch ein Institut angegeben werden';
				$return=false;
			}
			if(!$error)
			{
				$benutzerfunktion->oe_kurzbz = $_POST['oe_kurzbz'];
				$benutzerfunktion->semester = $_POST['semester'];
				$benutzerfunktion->fachbereich_kurzbz = $_POST['fachbereich_kurzbz'];
				$benutzerfunktion->uid = $_POST['uid'];
				$benutzerfunktion->funktion_kurzbz = $_POST['funktion_kurzbz'];
				$benutzerfunktion->updateamum = date('Y-m-d H:i:s');
				$benutzerfunktion->updatevon = $user;
				$benutzerfunktion->datum_von = $_POST['datum_von'];
				$benutzerfunktion->datum_bis = $_POST['datum_bis'];
				$benutzerfunktion->bezeichnung = $_POST['bezeichnung'];
				$benutzerfunktion->wochenstunden = str_replace(',','.',$_POST['wochenstunden']);

				if($benutzerfunktion->save())
				{
					$return = true;
					$data = $benutzerfunktion->benutzerfunktion_id;
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler beim Speichern:'.$benutzerfunktion->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktiondelete')
	{
		if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
		{
			$return = false;
			$errormsg = 'Keine Berechtigung';
			$data = '';
			$error = true;
		}
		else
		{
			if(isset($_POST['benutzerfunktion_id']) && is_numeric($_POST['benutzerfunktion_id']))
			{
				$benutzerfunktion = new benutzerfunktion();
				if($benutzerfunktion->delete($_POST['benutzerfunktion_id']))
				{
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler beim Loeschen:'.$benutzerfunktion->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='variablechange') /**********************SONSTIGES*****************/
	{
		$variable = new variable();

		$variable->uid = $user;

		// Aendert die Variable Studiensemester
		if(isset($_POST['stsem']))
		{
			if(isset($_POST['wert']) && $_POST['wert']!=0)
			{
				$stsem = new studiensemester();
				$studiensemester_kurzbz = $stsem->jump($_POST['stsem'], $_POST['wert']);
			}
			else
				$studiensemester_kurzbz = $_POST['stsem'];

			$variable->name = 'semester_aktuell';
			$variable->wert = $studiensemester_kurzbz;
		}
		elseif(isset($_POST['kontofilterstg']))
		{
			$variable->name = 'kontofilterstg';
			$variable->wert = $_POST['kontofilterstg'];
		}
		elseif(isset($_POST['name']))
		{
			$variable->name = $_POST['name'];
			$variable->wert = $_POST['wert'];
		}
		else
		{
			$error = true;
		}

		if(!$error)
		{
			if($variable->save())
			{
				$return = true;
				$data = $variable->wert;
			}
			else
			{
				$return = false;
				$errormsg = $variable->errormsg;
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Falsche Paramenteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='imagedelete')
	{
		if(isset($_POST['person_id']) && is_numeric($_POST['person_id']))
		{
			if(($_POST['studiengang_kz']!='' && !$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
		   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'], 'suid')) ||
		   ($_POST['studiengang_kz']=='' && !$rechte->isBerechtigt('admin', null, 'suid') &&
		   !$rechte->isBerechtigt('mitarbeiter', null, 'suid')))
			{
				$return = false;
				$errormsg = 'Keine Berechtigung';
				$data = '';
				$error = true;
			}
			else
			{
				$qry = "UPDATE public.tbl_person SET foto=null WHERE person_id=".$db->db_add_param($_POST['person_id']).";";
				$qry.= "DELETE FROM public.tbl_person_fotostatus where fotostatus_kurzbz='akzeptiert' AND person_id=".$db->db_add_param($_POST['person_id']);
				if($db->db_query($qry))
				{
					$qry = "DELETE FROM public.tbl_akte WHERE person_id=".$db->db_add_param($_POST['person_id'])." AND dokument_kurzbz='Lichtbil'";
					if($db->db_query($qry))
					{
						$fs = new fotostatus();
						$fs->person_id = $_POST['person_id'];
						$fs->fotostatus_kurzbz='abgewiesen';
						$fs->datum = date('Y-m-d');
						$fs->insertamum = date('Y-m-d H:i:s');
						$fs->insertvon = $user;
						$fs->updateamum = date('Y-m-d H:i:s');
						$fs->updatevon = $user;
						$fs->save(true);

						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = 'Fehler beim Loeschen des grossen Bildes';
					}
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler beim Loeschen des Bildes';
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Falsche Parameteruebergabe'.$_POST['person_id'].'x';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getvariable')
	{
		$variable = new variable();

		if($variable->load($user, $_POST['name']))
		{
			$return = true;
			$data = $variable->wert;
		}
		else
		{
			if($variable->errormsg=='')
			{
				$return = true;
				$data = '';
			}
			else
			{
				$return = false;
				$errormsg = 'Fehler: '.$variable->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getprivatemailadressUID')
	{
		$variable = new variable();
		$variable->loadVariables($user);
		if(isset($_POST['uids']))
		{
			$pers_arr = explode(';',$_POST['uids']);
			$data='';
			$anz_error=0;

			foreach ($pers_arr as $uid)
			{
				if($uid!='')
				{
					$qry = "SELECT kontakt
						FROM
							public.tbl_kontakt
							JOIN public.tbl_benutzer USING(person_id)
						WHERE kontakttyp='email'
						AND uid=".$db->db_add_param($uid)." AND zustellung=true LIMIT 1";

					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							if($data!='')
								$data.=$variable->variable->emailadressentrennzeichen.$row->kontakt;
							else
								$data = $row->kontakt;
						}
						else
						{
							$anz_error++;
						}
					}
				}
			}
			if($data!='')
			{
				if($anz_error==0)
					$return = true;
				else
				{
					$return = false;
					$errormsg = "Bei $anz_error Personen wurde keine Emailadresse gefunden!";
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Es wurde keine Privatadresse gefunden';
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='anwesenheittoggle')
	{
		if(!$rechte->isBerechtigt('student/anwesenheit'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung fuer diese Aktion';
			$data = '';
			$error = true;
		}
		else
		{
			if(isset($_POST['student_uid']) && isset($_POST['lehreinheit_id']) && isset($_POST['datum']))
			{
				$student_uid = $_POST['student_uid'];
				$lehreinheit_id = $_POST['lehreinheit_id'];
				$datum = $_POST['datum'];
				$anwesenheit = new anwesenheit();
				if($anwesenheit->AnwesenheitToggle($lehreinheit_id, $datum, $student_uid))
				{
					$return = true;
					$errormsg = "";
				}
				else
				{
					$return = false;
					$errormsg = $anwesenheit->errormsg;
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='anwesenheittogglemitarbeiter')
	{
		if(!$rechte->isBerechtigt('student/anwesenheit'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung fuer diese Aktion';
			$data = '';
			$error = true;
		}
		else
		{
			if(isset($_POST['lehreinheit_id']) && isset($_POST['datum']))
			{
				$lehreinheit_id = $_POST['lehreinheit_id'];
				$datum = $_POST['datum'];

				if($_POST['setanwesend']=='false')
				{
					// Anwesenheit loeschen
					$anwesenheit = new anwesenheit();
					if($anwesenheit->getAnwesenheitLehreinheit($lehreinheit_id, $datum))
					{
						$return = true;
						$errormsg = "";
						foreach($anwesenheit->result as $row)
						{
							$aw = new anwesenheit();
							if(!$aw->delete($row->anwesenheit_id))
							{
								$errormsg.=$aw->errormsg;
								$return = false;
							}
						}
					}
					else
					{
						$return = false;
						$errormsg = $anwesenheit->errormsg;
					}
				}
				else
				{
					$error = false;
					// Anwesenheit bei allen zugeteilten Studierenden setzen
					// Teilnehmer ermitteln
					$einheiten = 0;
					// Anzahl der Einheiten ermitteln
					$qry = "SELECT distinct stunde
							FROM
								lehre.tbl_stundenplan
							WHERE
								lehreinheit_id=".$db->db_add_param($lehreinheit_id)."
								AND datum=".$db->db_add_param($datum).";";
					if($result = $db->db_query($qry))
					{
						$einheiten = $db->db_num_rows($result);
					}
					else
					{
						$return = false;
						$error = true;
						$errormsg = 'Fehler beim Ermitteln der Einheiten';
					}

					if(!$error)
					{
						$qry = "SELECT distinct uid, vorname, nachname, person_id
								FROM
									campus.vw_student_lehrveranstaltung
									JOIN public.tbl_benutzer USING(uid)
									JOIN public.tbl_person USING(person_id) WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id);

						if($result = $db->db_query($qry))
						{
							while($row = $db->db_fetch_object($result))
							{
								$anwesenheit = new anwesenheit();
								$anwesenheit->uid = $row->uid;
								$anwesenheit->einheiten = $einheiten;
								$anwesenheit->lehreinheit_id = $lehreinheit_id;
								$anwesenheit->datum = $datum;
								$anwesenheit->anwesend = true;
								$anwesenheit->anmerkung;
								$anwesenheit->save();
							}
							$return = true;
						}
						else
						{
							$return = false;
							$erorrmsg = 'Fehler beim Ermitteln der Studierenden';
						}
					}
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type';
		$data = '';
	}
}

//RDF mit den Returnwerden ausgeben
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return>'.($return?'true':'false').'</DBDML:return>
        	<DBDML:errormsg><![CDATA['.$errormsg.']]></DBDML:errormsg>
        	<DBDML:data><![CDATA['.$data.']]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
';
?>
