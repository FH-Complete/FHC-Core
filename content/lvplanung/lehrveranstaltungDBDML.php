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
// * der Lehreinheiten
// *
// * Script sorgt fuer den Datenbankzugriff
// * fuer das XUL - Lehreinheiten-Modul
// *
// * Derzeitige Funktionen:
// * - Lehreinheitmitarbeiter Zuteilung hinzufuegen/bearbeiten/loeschen
// * - Lehreinheitgruppe Zutelung hinzufuegen/loeschen
// * - Lehreinheit anlegen/bearbeiten/loeschen
// ****************************************

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/lehreinheit.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/lehreinheitgruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/lehrstunde.class.php');

$user = get_uid();
$db = new basis_db();
error_reporting(0);

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

loadVariables($user);

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lv-plan'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}


function kollision($lehreinheit_id, $mitarbeiter_uid, $mitarbeiter_uid_old)
{
	global $db_stpl_table,$errormsg;

	//Lehrstunden laden
	$lehrstunden=new lehrstunde();
	$lehrstunde=new lehrstunde();
	$lehrstunden->load_lehrstunden_le($lehreinheit_id,$mitarbeiter_uid_old);

	foreach ($lehrstunden->lehrstunden as $ls)
	{
		$lehrstunde->load($ls->stundenplan_id);
		$lehrstunde->lektor_uid=$mitarbeiter_uid;
		if ($lehrstunde->kollision($db_stpl_table))
		{
			$errormsg=$lehrstunde->errormsg;
			return true;
		}
	}
	return false;
}

if(!$error)
{

	if(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_save')
	{
		//Lehreinheitmitarbeiter Zuteilung
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
		if($result = pg_query($conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				if(!$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('lv-plan', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz))
				{
					$error = true;
					$return = false;
					$errormsg = 'Keine Berechtigung';
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			$lem = new lehreinheitmitarbeiter();

			if(!$lem->load($_POST['lehreinheit_id'],$_POST['mitarbeiter_uid_old']))
			{
				$return = false;
				$errormsg = 'Fehler beim Laden:'.$lem->errormsg;
				$error = true;
			}

			if(!$error)
			{
				$lem->lehreinheit_id = $_POST['lehreinheit_id'];
				$lem->lehrfunktion_kurzbz = $_POST['lehrfunktion_kurzbz'];
				$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
				$lem->mitarbeiter_uid_old = $_POST['mitarbeiter_uid_old'];
				$lem->semesterstunden = $_POST['semesterstunden'];
				$lem->planstunden = $_POST['planstunden'];
				$lem->stundensatz = $_POST['stundensatz'];
				$lem->faktor = $_POST['faktor'];
				$lem->anmerkung = $_POST['anmerkung'];
				$lem->bismelden = ($_POST['bismelden']=='true'?true:false);
				$lem->updateamum = date('Y-m-d H:i:s');
				$lem->updatevon = $user;

				$lem->new=false;

				//Wenn sich der Lektor aendert und keine Kollision dadurch entsteht, dann werden die
				//Daten automatisch im Stundenplan geaendert
				if($ignore_kollision=='false' && $lem->mitarbeiter_uid!=$lem->mitarbeiter_uid_old)
				{
					//check kollision
					if(!kollision($lem->lehreinheit_id, $lem->mitarbeiter_uid, $lem->mitarbeiter_uid_old))
					{
						//Update im Stundenplan
						$stpl_table='lehre.'.TABLE_BEGIN.$db_stpl_table;
						$qry = "UPDATE $stpl_table SET mitarbeiter_uid='$lem->mitarbeiter_uid' WHERE lehreinheit_id='$lem->lehreinheit_id' AND mitarbeiter_uid='$lem->mitarbeiter_uid_old'";
						if($db->db_query($qry))
						{
							$error = false;
						}
						else
						{
							$error = true;
							$return = false;
							$errormsg = 'Fehler beim Update im Stundenplan'.$qry;
						}
					}
					else
					{
						$return = false;
						$errormsg = "Fehler: Die Aenderung des Lektors fuehrt zu einer Kollision im Stundenplan!\n".$errormsg;
						$error = true;
					}
				}

				if(!$error)
				{
					if($lem->save())
					{
						//Pruefen ob die erlaubte Semesterstundenanzahl ueberschritten wurde.
						//Wenn ja dann ein Warning zurueckliefern

						//Maximale Stundenanzahl ermitteln
						$ma = new mitarbeiter();
						$ma->load($lem->mitarbeiter_uid);

						if($ma->fixangestellt)
							$max_stunden = WARN_SEMESTERSTD_FIX;
						else
							$max_stunden = WARN_SEMESTERSTD_FREI;

						//Summer der Stunden ermitteln
						$le = new lehreinheit();
						$le->load($lem->lehreinheit_id);

						$qry = "SELECT
									sum(semesterstunden) as summe
								FROM
									lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
								WHERE
									mitarbeiter_uid='$lem->mitarbeiter_uid' AND
									studiensemester_kurzbz='$le->studiensemester_kurzbz' AND
									faktor>0 AND
									stundensatz>0 AND
									bismelden";

						if($db->db_query($qry))
						{
							if($row = $db->db_fetch_object())
							{
								if($row->summe>=$max_stunden)
								{
									//Warnung wenn die Stundenzahl ueberschritten wurde
									$return = false;
									$error = true;
									$errormsg = "Daten wurden gespeichert.\n\nWarnung: Die maximal erlaubte Semesterstundenanzahl von $max_stunden Stunden wurde ueberschritten";
								}
								else
								{
									$return = true;
									$error=false;
								}
							}
							else
							{
								$return = false;
								$error=true;
								$errormsg='Fehler beim Ermitteln der Gesamtstunden';
							}
						}
						else
						{
							$return = false;
							$error=true;
							$errormsg='Fehler beim Ermitteln der Gesamtstunden';
						}
					}
					else
					{
						$return = false;
						$errormsg  = $lem->errormsg;
						$error = true;
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_add')
	{
		//neue Lehreinheitmitarbeiterzuteilung anlegen
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				if(!$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('lv-plan', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz))
				{
					$error = true;
					$return = false;
					$errormsg = 'Keine Berechtigung';
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			if(isset($_POST['lehreinheit_id']) && isset($_POST['mitarbeiter_uid']))
			{
				$lem = new lehreinheitmitarbeiter();

				$lem->lehreinheit_id = $_POST['lehreinheit_id'];
				$lem->lehrfunktion_kurzbz = 'Lektor';
				$lem->mitarbeiter_uid = $_POST['mitarbeiter_uid'];

				$lem->anmerkung = '';
				$lem->bismelden = true;
				$lem->updateamum = date('Y-m-d H:i:s');
				$lem->updatevon = $user;
				$lem->insertamum = date('Y-m-d H:i:s');
				$lem->insertvon = $user;
				$lem->new=true;

				//Stundensatz aus tbl_mitarbeiter holen
				$qry = "SELECT stundensatz FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($_POST['mitarbeiter_uid'])."'";
				if($db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						if($row->stundensatz!='')
							$lem->stundensatz = $row->stundensatz;
						else
							$lem->stundensatz = '0';
					}
					else
					{
						$error=true;
						$return=false;
						$errormsg='Mitarbeiter '.addslashes($_POST['mitarbeiter_uid']).' wurde nicht gefunden';
					}
				}
				else
				{
					$error=true;
					$return=false;
					$errormsg='Fehler bei einer Datenbankabfrage:'.$db->db_last_error();
				}

				//Faktor und Semesterstunden aus tbl_lehrveranstaltung holen
				$qry = "SELECT planfaktor, semesterstunden FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."';";
				if($db->db_query($qry))
				{
					if($row = $db->db_fetch_object())
					{
						if($row->planfaktor!='')
							$lem->faktor = $row->planfaktor;
						else
							$lem->faktor = '1.0';
						
						if($row->semesterstunden!='')
						{
							$lem->semesterstunden = $row->semesterstunden;
							$lem->planstunden = $row->semesterstunden;
						}
						else	
						{
							$lem->planstunden = '0';
							$lem->semesterstunden = '0';
						}
					}
					else
					{
						$error = true;
						$return = false;
						$errormsg = 'Lehrveranstaltung wurde nicht gefunden';
					}
				}
				else
				{
					$error = true;
					$return = false;
					$errormsg = 'Fehler in einer Datenbankabfrage:'.$db->db_last_error();
				}

				if(!$error)
				{
					if($lem->save())
					{
						$return = true;
						$error = false;
					}
					else
					{
						$return = false;
						$errormsg = $lem->errormsg;
						$error = true;
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
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_del')
	{
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				if(!$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('lv-plan', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz))
				{
					$error = true;
					$return = false;
					$errormsg = 'Keine Berechtigung';
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			//Lehreinheitmitarbeiterzuteilung loeschen
			if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']) && isset($_POST['mitarbeiter_uid']))
			{
				//Wenn der Mitarbeiter im Stundenplan verplant ist, dann wird das Loeschen verhindert
				$qry = "SELECT stundenplandev_id as id FROM lehre.tbl_stundenplandev WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."' AND mitarbeiter_uid='".addslashes($_POST['mitarbeiter_uid'])."'
						UNION
						SELECT stundenplan_id as id FROM lehre.tbl_stundenplan WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."' AND mitarbeiter_uid='".addslashes($_POST['mitarbeiter_uid'])."'";
				if($db->db_query($qry))
				{
					if($db->db_num_rows()>0)
					{
						$return = false;
						$errormsg = 'Dieser Lektor kann nicht gelöscht werden da er schon verplant ist';
					}
					else
					{
						$leg = new lehreinheitmitarbeiter();
						if($leg->delete($_POST['lehreinheit_id'], $_POST['mitarbeiter_uid']))
						{
							$return = true;
						}
						else
						{
							$return = false;
							$errormsg = $leg->errormsg;
						}
					}
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler:'.$qry;
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Fehler beim Löschen der Zuordnung';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_del')
	{
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id=(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id='".addslashes($_POST['lehreinheitgruppe_id'])."')";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				if(!$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('lv-plan', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz))
				{
					$error = true;
					$return = false;
					$errormsg = 'Keine Berechtigung';
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			//Pruefen ob bereits eine Kreuzerlliste vorhanden ist
			$qry = "SELECT count(*) as anzahl FROM lehre.tbl_lehreinheitgruppe, lehre.tbl_lehreinheit, campus.tbl_uebung WHERE
					tbl_lehreinheitgruppe.lehreinheitgruppe_id='".addslashes($_POST['lehreinheitgruppe_id'])."' AND
					tbl_lehreinheitgruppe.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehreinheit_id=tbl_uebung.lehreinheit_id";
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					if($row->anzahl>0)
					{
						$error = true;
						$return = false;
						$errormsg = 'Diese Gruppe kann nicht geloescht werden da bereits Kreuzerllisten angelegt wurden';
					}
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Fehler beim Ermitteln ob eine Kreuzerlliste vorhanden ist';
			}

			//Pruefen ob diese Gruppe im Stundenplan schon verplant wurde
			if(!$error)
			{
				$qry = "SELECT stundenplandev_id as id FROM lehre.tbl_stundenplandev 
						WHERE 
							(lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband)), trim(COALESCE(gruppe)), trim(COALESCE(gruppe_kurzbz))) =
							(SELECT 
								lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband)), trim(COALESCE(gruppe)), trim(COALESCE(gruppe_kurzbz))
							 FROM 
							 	lehre.tbl_lehreinheitgruppe 
							WHERE 
								lehreinheitgruppe_id='".addslashes($_POST['lehreinheitgruppe_id'])."'
							)
						UNION
						SELECT stundenplan_id as id FROM lehre.tbl_stundenplan 
						WHERE 
							(lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband,'')), trim(COALESCE(gruppe,'')), trim(COALESCE(gruppe_kurzbz,''))) =
							(SELECT 
								lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband,'')), trim(COALESCE(gruppe,'')), trim(COALESCE(gruppe_kurzbz,''))
							 FROM 
							 	lehre.tbl_lehreinheitgruppe 
							WHERE 
								lehreinheitgruppe_id='".addslashes($_POST['lehreinheitgruppe_id'])."'
							)
						";
				if($db->db_query($qry))
				{
					if($db->db_num_rows()>0)
					{
						$error = true;
						$return = false;
						$errormsg = 'Diese Gruppe kann nicht geloescht werden da sie bereits im LV-Plan verplant ist. Bitte wenden Sie sich an die Stundenplanstelle';
					}
				}
				else 
				{
					$errormsg = 'Fehler beim Pruefen des Stundenplanes: '.$db->db_last_error();
					$return = false;
					$error = true;
				}

			}
			
			if(!$error)
			{
				//Lehreinheitgruppezuteilung loeschen
				if(isset($_POST['lehreinheitgruppe_id']) && is_numeric($_POST['lehreinheitgruppe_id']))
				{
					$leg = new lehreinheitgruppe();
					if($leg->delete($_POST['lehreinheitgruppe_id']))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $leg->errormsg;
					}
				}
				else
				{
					$return = false;
					$errormsg = 'Fehler beim Löschen der Zuordnung';
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit_gruppe_add')
	{
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				if(!$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('lv-plan', $row->studiengang_kz, 'suid') &&
				   !$rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid', $row->fachbereich_kurzbz))
				{
					$error = true;
					$return = false;
					$errormsg = 'Keine Berechtigung';
				}
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			//Lehreinheitgruppezuteilung anlegen
			if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']))
			{
				$leg = new lehreinheitgruppe();
				$leg->lehreinheit_id = $_POST['lehreinheit_id'];
				$leg->studiengang_kz = $_POST['studiengang_kz'];
				$leg->semester = $_POST['semester'];
				$leg->verband = $_POST['verband'];
				$leg->gruppe = $_POST['gruppe'];
				$leg->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
				$leg->insertamum = date('Y-m-d H:i:s');
				$leg->insertvon = $user;

				if(!$leg->checkVorhanden())
				{
					if($leg->save(true))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $leg->errormsg;
					}
				}
				else
				{
					$return = false;
					$errormsg = 'Diese Gruppe ist bereits zugeteilt';
				}
			}
			else
			{
				$return = false;
				$errormsg = 'Bitte zuerst eine Lehreinheit auswaehlen';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lehreinheit')
	{
		//Lehreinheit anlegen/aktualisieren
		if($_POST['lehreinheit_id']!='')
			$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz
					FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach
					WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
		else
			$qry = "SELECT studiengang_kz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='".addslashes($_POST['lehrveranstaltung'])."'";

		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$studiengang_kz = $row->studiengang_kz;
				$fachbereich_kurzbz = 0;
				if(isset($row->fachbereich_kurzbz))
					$fachbereich_kurzbz = $row->fachbereich_kurzbz;
			}
			else
			{
				$error = true;
				$return = false;
				$errormsg = 'Lehreinheit wurde nicht gefunden';
			}
		}
		else
		{
			$error = true;
			$return = false;
			$errormsg = 'Lehreinheit wurde nicht gefunden';
		}

		if(!$error)
		{
			$leDAO=new lehreinheit();
			if ($_POST['do']=='create' || ($_POST['do']=='update'))
			{
				if($_POST['do']=='update')
				{
					if(!$leDAO->load($_POST['lehreinheit_id']))
					{
						$return = false;
						$error = true;
						$errormsg = 'Fehler beim Laden der Lehreinheit';
					}

					if(!$rechte->isBerechtigt('admin', $studiengang_kz, 'suid') &&
					   !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid') &&
					   !$rechte->isBerechtigt('lv-plan', $studiengang_kz, 'suid') &&
				       !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid', $fachbereich_kurzbz)) /*&&
				       !$rechte->isBerechtigt('admin', $studiengang_kz, 'suid', $fachbereich_kurzbz))*/
					{
						$error = true;
						$return = false;
						$errormsg = 'Keine Berechtigung';
					}
				}
				else
				{
					if(!$rechte->isBerechtigt('admin', $studiengang_kz, 'si') && !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'si') &&
					   !$rechte->isBerechtigt('admin', $studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid') && !$rechte->isBerechtigt('lv-plan', $studiengang_kz, 'suid'))
					{
						$error = true;
						$return = false;
						$errormsg = 'Keine Berechtigung';
					}
				}

				if(!$error)
				{
					$leDAO->lehrveranstaltung_id=$_POST['lehrveranstaltung'];
					$leDAO->studiensemester_kurzbz=$_POST['studiensemester_kurzbz'];
					$leDAO->lehrfach_id=$_POST['lehrfach_id'];
					$leDAO->lehrform_kurzbz=$_POST['lehrform'];
					$leDAO->stundenblockung=$_POST['stundenblockung'];
					$leDAO->wochenrythmus=$_POST['wochenrythmus'];
					if (isset($_POST['start_kw'])) $leDAO->start_kw=$_POST['start_kw'];
					$leDAO->raumtyp=$_POST['raumtyp'];
					$leDAO->raumtypalternativ=$_POST['raumtypalternativ'];
					$leDAO->sprache=$_POST['sprache'];
					if (isset($_POST['lehre'])) $leDAO->lehre=($_POST['lehre']=='true'?true:false);
					if (isset($_POST['anmerkung'])) $leDAO->anmerkung=$_POST['anmerkung'];
					$leDAO->lvnr=(isset($_POST['lvnr'])?$_POST['lvnr']:'');
					$leDAO->unr=(isset($_POST['unr'])?$_POST['unr']:'');
					if($leDAO->unr=='')
					{
						$leDAO->unr = $_POST['lehreinheit_id'];
					}
					$leDAO->updateamum=date('Y-m-d H:i:s');
					$leDAO->updatevon=$user;

					if ($_POST['do']=='create')
					{
						// LE neu anlegen
						$leDAO->new=true;
						$leDAO->insertamum=date('Y-m-d H:i:s');
						$leDAO->insertvon=$user;
					}
					else if ($_POST['do']=='update')
					{
						// LE aktualisieren
						$leDAO->new=false;
					}
					if ($leDAO->save())
					{
						$data = $leDAO->lehreinheit_id;
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $leDAO->errormsg;
					}
				}
			}
			else if ($_POST['do']=='delete') //Lehreinheit loeschen
			{
				if(!$rechte->isBerechtigt('admin', $studiengang_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid') && !$rechte->isBerechtigt('lv-plan', $studiengang_kz, 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Keine Berechtigung';
				}
				else
				{
					// Loeschen verhindern wenn diese Lehreinheit schon verplant ist
					$qry = "SELECT stundenplandev_id as id FROM lehre.tbl_stundenplandev WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'
							UNION
							SELECT stundenplan_id as id FROM lehre.tbl_stundenplan WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
					if($db->db_query($qry))
					{
						if($db->db_num_rows()>0)
						{
							$return = false;
							$errormsg = 'Diese Lehreinheit ist bereits im LV-Plan verplant und kann daher nicht geloescht werden!';
						}
						else
						{
							//Loeschen verhindern wenn ein MoodleKurs existiert
							$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehreinheit_id='".addslashes($_POST['lehreinheit_id'])."'";
							if($db->db_query($qry))
							{
								if($db->db_num_rows()>0)
								{
									$return = false;
									$errormsg = 'Lehreinheit kann nicht geloescht werden, da dazu bereits ein Moodle-Kurs angelegt wurde';
								}
								else 
								{
									if ($leDAO->delete($_POST['lehreinheit_id']))
									{
										$return = true;
									}
									else
									{
										$return = false;
										$errormsg = 'Fehler beim Loeschen der Lehreinheit '.$leDAO->errormsg;
									}
								}
							}
						}
					}
					else
					{
						$return = false;
						$errormsg = 'unbekannter Fehler';
					}
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='getstundensatz')
	{
		if(isset($_POST['mitarbeiter_uid']))
		{
			$mitarbeiter = new mitarbeiter();
			if($mitarbeiter->load($_POST['mitarbeiter_uid']))
			{
				$data = $mitarbeiter->stundensatz;
				$return = true;
			}
			else
			{
				$errormsg = 'Fehler beim Laden des Mitarbeiters';
				$return = false;
			}
		}
		else
		{
			$errormsg = 'MitarbeiterUID muss uebergeben werden';
			$return = false;
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type: '.$_POST['type'];
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
