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
require_once('../../include/lvangebot.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();
$db = new basis_db();
//error_reporting(0);

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;
$warnung = false;

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

/**
 * Prueft ob die Person den Lehrauftrag auf eine Firma ausgestellt bekommt
 *
 * @param $mitarbeiter_uid
 * @return boolean
 */
function LehrauftragAufFirma($mitarbeiter_uid)
{
	global $db;
	
	$qry_firma = "
				SELECT * FROM campus.vw_mitarbeiter LEFT JOIN public.tbl_adresse USING(person_id) 
				WHERE uid=".$db->db_add_param($mitarbeiter_uid)."
				ORDER BY zustelladresse DESC, firma_id LIMIT 1";
	if($result_firma = $db->db_query($qry_firma))
	{
		if($row_firma = $db->db_fetch_object($result_firma))
		{
			if($row_firma->firma_id=='')
				return false;
			else 
				return true;
		}
		else 
		{
			return false;
		}
	}
	else 
	{
		return false;
	}
}

/**
 * Liefert eine Liste mit den Gesamtstunden eines Lektors in den einzelnen Instituten
 *
 * @param $mitarbeiter_uid
 * @param $studiensemester_kurzbz
 * @return string
 */
function getStundenproInstitut($mitarbeiter_uid, $studiensemester_kurzbz, $oe_arr)
{
	global $db;
	
	$ret="Der Lektor ist in folgenden Organisationseinheiten zugeteilt:\n";
	
	//Liste mit den Stunden in den jeweiligen Instituten anzeigen
	$qry = "SELECT sum(tbl_lehreinheitmitarbeiter.semesterstunden) as summe, tbl_studiengang.bezeichnung
			FROM
				lehre.tbl_lehreinheitmitarbeiter 
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				mitarbeiter_uid=".$db->db_add_param($mitarbeiter_uid)." AND
				studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
				faktor>0 AND
				stundensatz>0 AND
				bismelden AND
				tbl_studiengang.oe_kurzbz in(".$db->db_implode4SQL($oe_arr).")
			GROUP BY tbl_studiengang.bezeichnung";
	
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$ret .=$row->summe.' Stunden '.$row->bezeichnung."\n";
		}
	}
	return $ret;
}

if(!$error)
{
    if(!empty($_POST['lehrveranstaltung']))
        $lva = new lehrveranstaltung($_POST['lehrveranstaltung']);
    
	if(isset($_POST['type']) && $_POST['type']=='lehreinheit_mitarbeiter_save')
	{
		//Lehreinheitmitarbeiter Zuteilung
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);

		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				$lva = new lehrveranstaltung($row->lehrveranstaltung_id);
				$oe_arr = $lva->getAllOe();

                if(!$rechte->isBerechtigtMultipleOe('admin', $oe_arr, 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $oe_arr, 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('lv-plan', $oe_arr, 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $oe_arr, 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigtMultipleOe('admin', $oe_arr, 'suid', $row->fachbereich_kurzbz))
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
			$semesterstunden_alt=$lem->semesterstunden;
			$bismelden_alt = $lem->bismelden;
			$faktor_alt = $lem->faktor;
			$stundensatz_alt = $lem->stundensatz;

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
						$stpl_table='lehre.tbl_stundenplandev';
						$qry = "UPDATE $stpl_table SET mitarbeiter_uid=".$db->db_add_param($lem->mitarbeiter_uid)." WHERE lehreinheit_id=".$db->db_add_param($lem->lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$db->db_add_param($lem->mitarbeiter_uid_old);
						if($db->db_query($qry))
						{
							$error = false;
						}
						else
						{
							$error = true;
							$return = false;
							$errormsg = 'Fehler beim Update im LV-Plan'.$qry;
						}
					}
					else
					{
						$return = false;
						$errormsg = "Fehler: Die Aenderung des Lektors fuehrt zu einer Kollision im LV-Plan!\n".$errormsg;
						$error = true;
					}
				}
				
				$fixangestellt=false;
				if(!$error)
				{
					//Pruefen ob die erlaubte Semesterstundenanzahl ueberschritten wurde.
					//Wenn ja dann ein Warning zurueckliefern
					$ma = new mitarbeiter();
					$ma->load($lem->mitarbeiter_uid);
					$fixangestellt=$ma->fixangestellt;

					$oe_obj = new organisationseinheit();					
					$stunden_oe_kurzbz=null;

					$stg_obj = new studiengang();
					$stg_obj->load($lva->studiengang_kz);

					//Maximale Stundenanzahl ermitteln
					if($fixangestellt)
						list($stunden_oe_kurzbz, $max_stunden) = $oe_obj->getStundengrenze($stg_obj->oe_kurzbz, true);
					else
						list($stunden_oe_kurzbz, $max_stunden) = $oe_obj->getStundengrenze($stg_obj->oe_kurzbz, false);
					
					//Summe der Stunden ermitteln
					$le = new lehreinheit();
					$le->load($lem->lehreinheit_id);
	
					if($lem->stundensatz<=0 || $lem->faktor<=0 || $lem->bismelden==false)
						$neue_stunden_eingerechnet=false;
					else
						$neue_stunden_eingerechnet=true;
						
					if(($stundensatz_alt<=0 || $faktor_alt<=0 || $bismelden_alt==false))
						$alte_stunden_eingerechnet=false;
					else
						$alte_stunden_eingerechnet=true;
					
					//Stundenreduzierung immer moeglich
					if(($lem->semesterstunden>$semesterstunden_alt) || $neue_stunden_eingerechnet)
					{
						$oe_obj = new organisationseinheit();
						$oe_arr = $oe_obj->getChilds($stunden_oe_kurzbz);
						$qry = "SELECT ";
						if($alte_stunden_eingerechnet && $neue_stunden_eingerechnet)
							$qry.=" (sum(tbl_lehreinheitmitarbeiter.semesterstunden)-($semesterstunden_alt)+($lem->semesterstunden)) as summe";
						elseif($alte_stunden_eingerechnet && !$neue_stunden_eingerechnet)
							$qry.=" (sum(tbl_lehreinheitmitarbeiter.semesterstunden)-($semesterstunden_alt)) as summe";
						elseif(!$alte_stunden_eingerechnet && $neue_stunden_eingerechnet)
							$qry.=" (sum(tbl_lehreinheitmitarbeiter.semesterstunden)+($lem->semesterstunden)) as summe";
						elseif(!$alte_stunden_eingerechnet && !$neue_stunden_eingerechnet)
							$qry.=" (sum(tbl_lehreinheitmitarbeiter.semesterstunden)) as summe";
						$qry.="	FROM
									lehre.tbl_lehreinheitmitarbeiter 
									JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
									JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
									JOIN public.tbl_studiengang USING(studiengang_kz)
								WHERE
									mitarbeiter_uid=".$db->db_add_param($lem->mitarbeiter_uid)." AND
									studiensemester_kurzbz=".$db->db_add_param($le->studiensemester_kurzbz)." AND
									faktor>0 AND
									stundensatz>0 AND
									bismelden";

						if(count($oe_arr)>0)
							$qry.=" AND tbl_studiengang.oe_kurzbz in(".$db->db_implode4SQL($oe_arr).")";
						
						if($db->db_query($qry))
						{
							if($row = $db->db_fetch_object())
							{
								if($row->summe>$max_stunden)
								{
									if(!$fixangestellt)
									{
										if(!LehrauftragAufFirma($lem->mitarbeiter_uid))
										{
											//Warnung wenn die Stundenzahl ueberschritten wurde
											$return = false;
											$error = true;
											$errormsg = "ACHTUNG: Die maximal erlaubte Semesterstundenanzahl des Lektors von $max_stunden Stunden ($stunden_oe_kurzbz) wurde ueberschritten!\n Daten wurden NICHT gespeichert!\n\n";									
										}
									}
									else 
									{
										$return = true;
										$error = false;
										$warnung = true;
										$errormsg = "Hinweis: Die maximal erlaubte Semesterstundenanzahl des Lektors von $max_stunden Stunden ($stunden_oe_kurzbz) wurde ueberschritten!\n Daten wurden gespeichert!\n\n";
									}	
									
									$errormsg.=getStundenproInstitut($lem->mitarbeiter_uid, $le->studiensemester_kurzbz, $oe_arr);
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
				}
				
				if(!$error)
				{
					if($lem->save())
					{
						//Fixangestellte bekommen eine Warnung wenn die Stunden ueberschritten wurden. Es wird aber
						//trotzdem gespeichert
						if($warnung)
						{
							$return=false;
							$error = true;							
						}
						else 
						{
							$return = true;
							$error = false;
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
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$lva = new lehrveranstaltung($row->lehrveranstaltung_id);
                
                if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz))
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

				$fixangestellt=false;
				//Stundensatz aus tbl_mitarbeiter holen
				$qry = "SELECT stundensatz, fixangestellt FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=".$db->db_add_param($_POST['mitarbeiter_uid']);
				if($result = $db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						if($row->stundensatz!='')
							$lem->stundensatz = $row->stundensatz;
						else
							$lem->stundensatz = '0';
						$fixangestellt = ($row->fixangestellt=='t'?true:false);
					}
					else
					{
						$error=true;
						$return=false;
						$errormsg='Mitarbeiter '.$db->convert_html_chars($_POST['mitarbeiter_uid']).' wurde nicht gefunden';
					}
				}
				else
				{
					$error=true;
					$return=false;
					$errormsg='Fehler bei einer Datenbankabfrage:'.$db->db_last_error();
				}
				
				$maxstunden=9999;

				$oe_obj = new organisationseinheit();					
				$stunden_oe_kurzbz=null;

				$stg_obj = new studiengang();
				$stg_obj->load($lva->studiengang_kz);

				//Maximale Stundenanzahl ermitteln
				if($fixangestellt)
					list($stunden_oe_kurzbz, $max_stunden) = $oe_obj->getStundengrenze($stg_obj->oe_kurzbz, true);
				else
					list($stunden_oe_kurzbz, $max_stunden) = $oe_obj->getStundengrenze($stg_obj->oe_kurzbz, false);

				//Bei freien Lektoren muss geprueft werden ob die Stundengrenze erreicht wurde
				if(!$fixangestellt && !LehrauftragAufFirma($lem->mitarbeiter_uid))
				{
					//Summe der Stunden ermitteln
					$le = new lehreinheit();
					$le->load($lem->lehreinheit_id);

					$oe_obj = new organisationseinheit();
					$oe_arr = $oe_obj->getChilds($stunden_oe_kurzbz);
	
					$qry = "SELECT
								sum(tbl_lehreinheitmitarbeiter.semesterstunden) as summe
							FROM
								lehre.tbl_lehreinheitmitarbeiter 
								JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
								JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
								JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE
								mitarbeiter_uid=".$db->db_add_param($lem->mitarbeiter_uid)." AND
								studiensemester_kurzbz=".$db->db_add_param($le->studiensemester_kurzbz)." AND
								faktor>0 AND
								stundensatz>0 AND
								bismelden";

					if(count($oe_arr)>0)
						$qry.=" AND tbl_studiengang.oe_kurzbz in(".$db->db_implode4SQL($oe_arr).")";

					if($result_std = $db->db_query($qry))
					{
						if($row_std = $db->db_fetch_object($result_std))
						{
							//Grenze ueberschritten
							if($row_std->summe>=$max_stunden)
							{
								$return = false;
								$error = true;
								$errormsg = "ACHTUNG: Die maximal erlaubte Semesterstundenanzahl des Lektors von $max_stunden Stunden ($stunden_oe_kurzbz) wurde ueberschritten!\n Daten wurden NICHT gespeichert!\n\n";
								$errormsg.=getStundenproInstitut($lem->mitarbeiter_uid, $le->studiensemester_kurzbz,$oe_arr);
							}
							else
							{
								//Stunden berechnen die noch maximal unterrichtet werden darf
								$maxstunden = $max_stunden-$row_std->summe;
							}
						}
					}
				}
				
				if(!$error)
				{
					//Faktor und Semesterstunden aus tbl_lehrveranstaltung holen
					$qry = "SELECT planfaktor, semesterstunden FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER).";";
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
								//wenn es sich um einen freien Lektor handelt, und dieser nicht mehr die volle Stundenanzahl unterrichten
								//darf, dann werden nur die restlichen zur Verfuegung stehenden Stunden zugeteilt.
								$lem->semesterstunden = ($row->semesterstunden>$maxstunden?$maxstunden:$row->semesterstunden);
								$lem->planstunden = ($row->semesterstunden>$maxstunden?$maxstunden:$row->semesterstunden);
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
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$lva = new lehrveranstaltung($row->lehrveranstaltung_id);
                
                if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz))
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
				$qry = "SELECT stundenplandev_id as id FROM lehre.tbl_stundenplandev WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)." AND mitarbeiter_uid=".$db->db_add_param($_POST['mitarbeiter_uid'])."
						UNION
						SELECT stundenplan_id as id FROM lehre.tbl_stundenplan WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)." AND mitarbeiter_uid=".$db->db_add_param($_POST['mitarbeiter_uid']);
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
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=(SELECT lehreinheit_id FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$db->db_add_param($_POST['lehreinheitgruppe_id'], FHC_INTEGER).")";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$lva = new lehrveranstaltung($row->lehrveranstaltung_id);
                
                if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz))
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
					tbl_lehreinheitgruppe.lehreinheitgruppe_id=".$db->db_add_param($_POST['lehreinheitgruppe_id'], FHC_INTEGER)." AND
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
							(lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband,'')), trim(COALESCE(gruppe,'')), trim(COALESCE(gruppe_kurzbz,''))) =
							(SELECT 
								lehreinheit_id, studiengang_kz, semester, trim(COALESCE(verband,'')), trim(COALESCE(gruppe,'')), trim(COALESCE(gruppe_kurzbz,''))
							 FROM 
							 	lehre.tbl_lehreinheitgruppe 
							WHERE 
								lehreinheitgruppe_id=".$db->db_add_param($_POST['lehreinheitgruppe_id'], FHC_INTEGER)."
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
								lehreinheitgruppe_id=".$db->db_add_param($_POST['lehreinheitgruppe_id'], FHC_INTEGER)."
							)
						";
				if($db->db_query($qry))
				{
					if($db->db_num_rows()>0)
					{
						$error = true;
						$return = false;
						$errormsg = 'Diese Gruppe kann nicht geloescht werden, da sie bereits im LV-Plan verplant ist. Bitte wenden Sie sich an die LV-Planung';
					}
				}
				else 
				{
					$errormsg = 'Fehler beim Pruefen des LV-Plans: '.$db->db_last_error();
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
		$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
				FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
				WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$lva = new lehrveranstaltung($row->lehrveranstaltung_id);
                
                if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid') &&
				   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz) &&
				   !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz))
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
					if($leg->errormsg=='')
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
						$errormsg=$leg->errormsg;
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
		if(isset($_POST['lehreinheit_id']) && $_POST['lehreinheit_id']!='')
			$qry = "SELECT tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.lehrveranstaltung_id,
                    (SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz
					FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach
					WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
		else
			$qry = "SELECT studiengang_kz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($_POST['lehrveranstaltung'], FHC_INTEGER);

		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$studiengang_kz = $row->studiengang_kz;
                $fachbereich_kurzbz = 0;
				if(isset($row->fachbereich_kurzbz))
					$fachbereich_kurzbz = $row->fachbereich_kurzbz;
                if(!isset($lva))
                    $lva = new lehrveranstaltung($row->lehrveranstaltung_id);
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

					if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
					   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
					   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid') &&
				       !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $fachbereich_kurzbz))
					{
						$error = true;
						$return = false;
						$errormsg = 'Keine Berechtigung';
					}
				}
				else
				{
                    if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'si') && !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'si') &&
					   !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') && !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') && !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid'))
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

					if (isset($_POST['start_kw'])) 
						$leDAO->start_kw=$_POST['start_kw'];

					$leDAO->raumtyp=$_POST['raumtyp'];
					$leDAO->raumtypalternativ=$_POST['raumtypalternativ'];
					$leDAO->sprache=$_POST['sprache'];

					if (isset($_POST['lehre'])) 
						$leDAO->lehre=($_POST['lehre']=='true'?true:false);

					if (isset($_POST['anmerkung'])) 
						$leDAO->anmerkung=$_POST['anmerkung'];

					$leDAO->lvnr=(isset($_POST['lvnr'])?$_POST['lvnr']:'');
					$leDAO->unr=(isset($_POST['unr'])?$_POST['unr']:'');
					if($leDAO->unr=='')
					{
						if(isset($_POST['lehreinheit_id']))
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
						if($_POST['do']=='create')
						{
							// Wenn ein LV-Angebot vorliegt, wird diese Gruppe automatisch zugeteilt
							$lvangebot = new lvangebot();
							$lvangebot->getAllFromLvId($leDAO->lehrveranstaltung_id, $leDAO->studiensemester_kurzbz);
							if(isset($lvangebot->result[0]) && $lvangebot->result[0]->gruppe_kurzbz!='')
							{
								$gruppe = new gruppe();
								$gruppe->load($lvangebot->result[0]->gruppe_kurzbz);

								$leg = new lehreinheitgruppe();
								$leg->lehreinheit_id = $leDAO->lehreinheit_id;
								$leg->studiengang_kz = $gruppe->studiengang_kz;
								$leg->semester = $gruppe->semester;
								$leg->gruppe_kurzbz = $gruppe->gruppe_kurzbz;
								$leg->insertamum = date('Y-m-d H:i:s');
								$leg->insertvon = $user;
								$leg->new = true;
								$leg->save();
							}
						}
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
				if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') && 
                   !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') && 
                   !$rechte->isBerechtigtMultipleOe('lv-plan', $lva->getAllOe(), 'suid'))
				{
					$return = false;
					$error = true;
					$errormsg = 'Keine Berechtigung';
				}
				else
				{
					// Loeschen verhindern wenn diese Lehreinheit schon verplant ist
					$qry = "SELECT stundenplandev_id as id FROM lehre.tbl_stundenplandev WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER)."
							UNION
							SELECT stundenplan_id as id FROM lehre.tbl_stundenplan WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
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
							$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehreinheit_id=".$db->db_add_param($_POST['lehreinheit_id'], FHC_INTEGER);
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
	elseif(isset($_POST['type']) && $_POST['type']=='lvangebot-gruppe-save')
	{
		$lehrveranstaltung_obj = new lehrveranstaltung();
        if(!$lehrveranstaltung_obj->load($_POST['lehrveranstaltung_id']))
            $errormsg = 'Fehler beim Laden der Lehrveranstaltung';
        
        if(!$rechte->isBerechtigtMultipleOe('admin', $lehrveranstaltung_obj->getAllOe(), 'suid') &&
           !$rechte->isBerechtigtMultipleOe('assistenz', $lehrveranstaltung_obj->getAllOe(), 'suid'))
        {
            $error = true;
            $return = false;
            $errormsg = 'Keine Berechtigung';
        }
        
        if(!$error)
        {
            isset($_POST['lvangebot_id']) ? $lvangebot_id = $_POST['lvangebot_id'] : $lvangebot_id = null;
            $datum_obj = new datum();
            $lvangebot = new lvangebot();
            $lvangebot->insertamum = date('Y-m-d H:i:s');
            $lvangebot->insertvon = $user;

            if($lvangebot_id)
            {
                $lvangebot->load($lvangebot_id);
                $lvangebot->new = false;
            }
            else
            {
                $lvangebot->new = true;
            }

            $studiengang = new studiengang();
            if(!$studiengang->load($lehrveranstaltung_obj->studiengang_kz))
                $errormsg = 'Fehler beim Laden des Studienganges';

            if($_POST['neue_gruppe'] == "false")
            {
                $gruppe_kurzbz = $_POST['gruppe'];
            }
            else
            {
                $gruppe = new gruppe();
                $gruppe_kurzbz = mb_strtoupper(substr($studiengang->kuerzel.$lehrveranstaltung_obj->semester.'-'.$_POST['studiensemester_kurzbz'].'-'.$lehrveranstaltung_obj->kurzbz,0,32));
                $gruppe_kurzbz = $gruppe->getNummerierteGruppenbez($gruppe_kurzbz);
                $gruppe->gruppe_kurzbz=$gruppe_kurzbz;
                $gruppe->studiengang_kz=$studiengang->studiengang_kz;
                $gruppe->bezeichnung=mb_substr($lehrveranstaltung_obj->bezeichnung,0,30);
                $gruppe->semester=$lehrveranstaltung_obj->semester;
                $gruppe->sort='';
                $gruppe->mailgrp=false;
                $gruppe->beschreibung=$lehrveranstaltung_obj->bezeichnung;
                $gruppe->sichtbar=true;
                $gruppe->generiert=false;
                $gruppe->aktiv=true;
                $gruppe->lehre=true;
                $gruppe->content_visible=false;
                $gruppe->orgform_kurzbz=$lehrveranstaltung_obj->orgform_kurzbz;
                $gruppe->gesperrt=false;
                $gruppe->zutrittssystem=false;
                $gruppe->insertamum=date('Y-m-d H:i:s');
                $gruppe->insertvon=$user;

                if(!$gruppe->save(true))
                {
                    $errormsg = 'Fehler beim Erstellen der Gruppe'.$gruppe->errormsg;
                    $return = false;
                }
            }

            $lvangebot->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
            $lvangebot->studiensemester_kurzbz = $_POST['studiensemester_kurzbz'];
            $lvangebot->gruppe_kurzbz = $gruppe_kurzbz;
            $lvangebot->incomingplaetze = $_POST['incomingplaetze'];
            $lvangebot->gesamtplaetze = $_POST['gesamtplaetze'];
            $lvangebot->anmeldefenster_start = $datum_obj->formatDatum($_POST['anmeldefenster_start'], 'Y-m-d');
            $lvangebot->anmeldefenster_ende = $datum_obj->formatDatum($_POST['anmeldefenster_ende'],'Y-m-d');

            if(!$lvangebot->save())
            {
                $errormsg = $lvangebot->errormsg;
                $return = false;
            }
            else
            {
                $return = true;
            }
        }
	}
	elseif(isset($_POST['type']) && $_POST['type']=='lvangebot_gruppe_del')
	{
		$lvangebot = new lvangebot();
        $lvangebot->load($_POST['lvangebot_id']);
        $lva = new lehrveranstaltung($lvangebot->lehrveranstaltung_id);
                
        if(!$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid') &&
           !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid') &&
           !$rechte->isBerechtigtMultipleOe('assistenz', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz) &&
           !$rechte->isBerechtigtMultipleOe('admin', $lva->getAllOe(), 'suid', $row->fachbereich_kurzbz))
        {
            $error = true;
            $return = false;
            $errormsg = 'Keine Berechtigung';
        }
        
        if(!$error)
        {
            if(!$lvangebot->delete($_POST['lvangebot_id']))
            {
                $errormsg = $this->errormsg;
                $return = false;
            }
            else
            {
                $return = true;
            }
        }
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type: '.$_POST['type'];
		$data = '';
	}
}
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
