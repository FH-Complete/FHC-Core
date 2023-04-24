<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Klasse zur Verwaltung der Anwesenheiten der Studierenden
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/../config/global.config.inc.php');

class anwesenheit extends basis_db
{
	public $new=true;			//  boolean
	public $result = array();

	public $anwesenheit_id; 	// serial
	public $uid; 				// varchar(32)
	public $einheiten; 			// numeric(3,1)
	public $datum; 				// date
	public $anwesend;			// boolean
	public $lehreinheit_id;		// bigint
	public $anmerkung;			// varchar(256)

	/**
	 * Konstruktor
	 * @param $anwesenheit_id ID des Datensatzes der geladen werden soll (Default=null)
	 */
	public function __construct($anwesenheit_id=null)
	{
		parent::__construct();

		if(!is_null($anwesenheit_id))
			$this->load($anwesenheit_id);
	}

	/**
	 * Laedt den Datensatz mit der ID $anwesenheit_id
	 * @param  $anwesenheit_id ID des Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($anwesenheit_id)
	{
		//Pruefen ob anwesenheit_id eine gueltige Zahl ist
		if(!is_numeric($anwesenheit_id) || $anwesenheit_id == '')
		{
			$this->errormsg = 'Anwesenheit_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_anwesenheit WHERE anwesenheit_id=".$this->db_add_param($anwesenheit_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->anwesenheit_id = $row->anwesenheit_id;
			$this->uid = $row->uid;
			$this->einheiten = $row->einheiten;
			$this->datum = $row->datum;
			$this->anwesend = $this->db_parse_bool($row->anwesend);
			$this->lehreinheit_id = $row->lehreinheit_id;
			$this->anmerkung = $row->anmerkung;
			$this->new=false;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->anwesenheit_id) && $this->anwesenheit_id!='')
		{
			$this->errormsg='anwesenheit_id enthaelt ungueltige Zeichen';
			return false;
		}

		//Gesamtlaenge pruefen
		if(mb_strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 255 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der bestehende Datensatz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_anwesenheit (uid, einheiten, anwesend, datum, lehreinheit_id, anmerkung) VALUES('.
			      $this->db_add_param($this->uid).', '.
			      $this->db_add_param($this->einheiten).', '.
			      $this->db_add_param($this->anwesend, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->datum).', '.
			      $this->db_add_param($this->lehreinheit_id).', '.
			      $this->db_add_param($this->anmerkung).');';
		}
		else
		{
			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->anwesenheit_id))
			{
				$this->errormsg = 'anwesenheit_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE campus.tbl_anwesenheit SET'.
				' uid='.$this->db_add_param($this->uid).', '.
				' einheiten='.$this->db_add_param($this->einheiten).', '.
				' anwesend='.$this->db_add_param($this->anwesend,FHC_BOOLEAN).', '.
		      	' datum='.$this->db_add_param($this->datum).', '.
		      	' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).', '.
		      	' anmerkung='.$this->db_add_param($this->anmerkung).' '.
		      	'WHERE anwesenheit_id='.$this->db_add_param($this->anwesenheit_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_anwesenheit_anwesenheit_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->anwesenheit_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return $this->anwesenheit_id;
	}

	/**
	 * Laedt die Anwesenheiten einer Lehreinheit/Datum
	 * @param $lehreinheit_id
	 * @param $datum
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAnwesenheitLehreinheit($lehreinheit_id, $datum=null)
	{
		$qry = "SELECT * FROM campus.tbl_anwesenheit
			WHERE
				lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);

		if(!is_null($datum))
			$qry.=" AND datum=".$this->db_add_param($datum);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new anwesenheit();

				$obj->anwesenheit_id = $row->anwesenheit_id;
				$obj->uid = $row->uid;
				$obj->einheiten = $row->einheiten;
				$obj->datum = $row->datum;
				$obj->anwesend = $this->db_parse_bool($row->anwesend);
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->anmerkung = $row->anmerkung;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function loadAnwesenheitMitarbeiter($mitarbeiter_uid, $lehreinheit_id)
	{
		$qry = "SELECT
					datum, a.einheiten,
					(SELECT true FROM campus.tbl_anwesenheit
					 WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id)." AND datum=a.datum LIMIT 1) as anwesend,
					(SELECT stundensatz FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
					AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid).") as stundensatz
				FROM
					(SELECT datum, count(distinct stunde) as einheiten FROM lehre.tbl_stundenplan
					 WHERE
						lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
						AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)."
					GROUP by datum) as a
				";

		if($result = $this->db_query($qry))
		{
			$this->anzahl_termine=0;
			$this->anzahl_anwesend=0;
			$this->anwesenheit = array();

			while($row = $this->db_fetch_object($result))
			{
				$anwesend = $this->db_parse_bool($row->anwesend);
				$key = $lehreinheit_id.'/'.$row->datum;

				$this->anwesenheit[$key]['anwesend'] = ($anwesend?true:false);
				$this->anwesenheit[$key]['lehreinheit_id'] = $lehreinheit_id;
				$this->anwesenheit[$key]['datum']=$row->datum;
				$this->anwesenheit[$key]['einheiten']=$row->einheiten;
				$this->anwesenheit[$key]['stundensatz']=$row->stundensatz;

				$this->anzahl_termine++;
				if($anwesend)
					$this->anzahl_anwesend++;
			}
			if($this->anzahl_termine>0)
			{
				$this->prozent_anwesend=$this->anzahl_anwesend/$this->anzahl_termine*100;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert für Student und Einheit wieviel Einheiten als anwesend/abwesend
	 * eingetragen sind.
	 *
	 * @param string $uid
	 * @param int $lehreinheit_id
	 * @param bool $anwesend
	 * @return int
	 */
	public function getAnwesenheit($uid, $lehreinheit_id, $anwesend = FALSE)
	{
		$qry = 'SELECT sum(einheiten) AS einheiten '
				. 'FROM campus.tbl_anwesenheit '
				. 'WHERE uid = ' . $this->db_add_param($uid)
				. ' AND lehreinheit_id = ' . $this->db_add_param($lehreinheit_id, FHC_INTEGER)
				. ' AND anwesend = ' . $this->db_add_param($anwesend, FHC_BOOLEAN);

		$result = $this->db_query($qry);
		$row = $this->db_fetch_object($result);

		return $row->einheiten;
	}

	/**
	 * Liefert die Anwesenheiten/Abwesenheiten eines Studenten bei einer LV
	 *
	 * @param string $student_uid
	 * @param int $lehrveranstaltung_id
	 * @param string $studiensemester_kurzbz
	 * @param boolean $anwesend
	 * @return boolean
	 */
	public function getAnwesenheitLehrveranstaltung($student_uid, $lehrveranstaltung_id, $studiensemester_kurzbz, $anwesend=false)
	{
		$qry = 'SELECT
					distinct tbl_anwesenheit.*
				FROM
					campus.tbl_anwesenheit
					JOIN campus.vw_student_lehrveranstaltung USING(uid)
				WHERE
					uid='.$this->db_add_param($student_uid).'
					AND vw_student_lehrveranstaltung.lehreinheit_id=tbl_anwesenheit.lehreinheit_id
					AND lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).'
					AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz).'
					AND anwesend=' . $this->db_add_param($anwesend, FHC_BOOLEAN).'
				ORDER BY datum';

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new anwesenheit();

				$obj->anwesenheit_id = $row->anwesenheit_id;
				$obj->uid = $row->uid;
				$obj->einheiten = $row->einheiten;
				$obj->datum = $row->datum;
				$obj->anwesend = $this->db_parse_bool($row->anwesend);
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->anmerkung = $row->anmerkung;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert die Termine an denen eine Abwesenheit eingetragen ist.
	 *
	 * @param string $uid
	 * @param int $lehreinheit_id
	 * @return array
	 */
	public function getAbwesendTermine($uid, $lehreinheit_id)
	{

		$qry = 'SELECT datum, einheiten '
				. 'FROM campus.tbl_anwesenheit '
				. 'WHERE uid = ' . $this->db_add_param($uid)
				. ' AND lehreinheit_id = ' . $this->db_add_param($lehreinheit_id)
				. ' AND anwesend = FALSE '
				. 'ORDER BY datum';

		$result = $this->db_query($qry);
		$ret_obj = array();

		while($row = $this->db_fetch_object($result))
		{
			$ret_obj[] = $row;
		}

		return $ret_obj;
	}

	public function getAmpel($anwesenheit_relativ)
	{

		if($anwesenheit_relativ < FAS_ANWESENHEIT_ROT)
		{
			return 'red';
		}
		elseif($anwesenheit_relativ < FAS_ANWESENHEIT_GELB)
		{
			return 'yellow';
		}
		else
		{
			return 'green';
		}
	}

	/**
	 * Prueft ob Anwesenheiten erfasst wurden
	 * @param $lehreinheit_id ID der Lehreinheit
	 * @param $datum Datum
	 * @param $uid UID des Studierenden
	 * @return boolean true wenn vorhanden, sonst false
	 */
	public function AnwesenheitExists($lehreinheit_id, $datum, $uid=null)
	{
		$qry = "SELECT
					1
				FROM
					campus.tbl_anwesenheit
				WHERE
					anwesend=true
					AND lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
					AND datum=".$this->db_add_param($datum);

		if($uid!='')
			$qry.=" AND uid=".$this->db_add_param($uid);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft ob Anwesenheiten erfasst wurden
	 * @param $lehreinheit_id ID der Lehreinheit
	 * @param $datum Datum
	 * @param $uid UID des Studierenden
	 * @return boolean true wenn vorhanden, sonst false
	 */
	public function AnwesenheitEntryExists($lehreinheit_id, $datum, $uid=null)
	{
		$qry = "SELECT
					1
				FROM
					campus.tbl_anwesenheit
				WHERE
					lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
					AND datum=".$this->db_add_param($datum)."
					AND uid=".$this->db_add_param($uid);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Anwesenheiten in Prozent von Studierenden bei Lehrveranstaltungen
	 * Wenn die StudentUID uebergeben wird, werden alle Lehrveranstaltungen zu denen der Studierenden zugeteilt ist inkl Prozent der Anwesenheit
	 * Wenn die LehrveranstaltungID uebergeben wird, werden alle Studierenden geholt die zugeteilt sind inkl Prozent der Anwesenheit
	 * Es werden pro Student die Anwesenheiten berechnet aufgrund der Lehreinheit zu der sie zugeordnet sind
	 * @param $studiensemester_kurzbz
	 * @param $student_uid
	 * @param $lehrveranstaltung_id
	 * @return boolean true wenn ok, false im fehlerfall
	 */
	public function loadAnwesenheitStudiensemester($studiensemester_kurzbz, $student_uid=null, $lehrveranstaltung_id=null)
	{
		$qry = "SELECT
					lehrveranstaltung_id, vorname, nachname, wahlname, student_uid as uid, bezeichnung,
					gesamt as gesamtstunden, anwesend, nichtanwesend, trunc(100-(nichtanwesend/gesamt)*100,2) as prozent
				FROM
				(
					SELECT
						vorname, nachname, wahlname, lehrveranstaltung_id, bezeichnung, gruppe, student_uid,
						count(stundenplan_id) as gesamt,
						case when anwesend.summe is null then 0 else anwesend.summe end as anwesend,
						case when nichtanwesend.summe is null then 0 else nichtanwesend.summe end as nichtanwesend
					FROM
					(
						SELECT
							sum(stundenplan_id) as stundenplan_id, datum, stunde, lehrveranstaltung_id,
							bezeichnung, studiensemester_kurzbz, studiengang_kz,
							TRIM(
								CASE WHEN stp.gruppe_kurzbz is not null then stp.gruppe_kurzbz
								else stp.semester||(case when verband is null then '' else stp.verband end)||(case when stp.gruppe is null then '' else stp.gruppe end) end) as gruppe
						FROM
							lehre.tbl_lehrveranstaltung lv
							JOIN lehre.tbl_lehreinheit le using (lehrveranstaltung_id)
							JOIN lehre.tbl_stundenplan stp using (lehreinheit_id,studiengang_kz)
						WHERE
							studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."
							AND (titel not like '%Nebenprüfung%' OR titel is null)

						group by datum, stunde, lehrveranstaltung_id, bezeichnung, studiensemester_kurzbz, studiengang_kz, stp.gruppe_kurzbz, stp.semester, stp.verband, stp.gruppe
					)x
					JOIN (
						SELECT semester::text  as gruppe, public.tbl_studentlehrverband.studiensemester_kurzbz, student_uid, studiengang_kz
						FROM
							public.tbl_studentlehrverband
						WHERE studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."

						UNION

						SELECT  semester||verband  as gruppe, public.tbl_studentlehrverband.studiensemester_kurzbz, student_uid, studiengang_kz
						FROM
							public.tbl_studentlehrverband
						WHERE
							studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."

						UNION

						SELECT  semester||verband||gruppe as gruppe, public.tbl_studentlehrverband.studiensemester_kurzbz, student_uid, studiengang_kz
						FROM
							public.tbl_studentlehrverband
						WHERE
							studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."

						UNION

						SELECT gruppe_kurzbz as gruppe, public.tbl_benutzergruppe.studiensemester_kurzbz, uid as student_uid, studiengang_kz
						FROM
							public.tbl_benutzergruppe
						JOIN
							public.tbl_gruppe using (gruppe_kurzbz)
						WHERE studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."

					)a using (gruppe, studiensemester_kurzbz, studiengang_kz)
					JOIN public.tbl_benutzer b on b.uid = student_uid
					JOIN public.tbl_person p using(person_id)
					LEFT JOIN(
						SELECT
							lehrveranstaltung_id, studiensemester_kurzbz, uid as student_uid, sum(einheiten) as summe
						FROM
							campus.tbl_anwesenheit a
							JOIN lehre.tbl_lehreinheit le using (lehreinheit_id)
							JOIN lehre.tbl_lehrveranstaltung lv using (lehrveranstaltung_id)
						WHERE
							anwesend = true AND studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."
						GROUP BY
							lehrveranstaltung_id, bezeichnung, uid, studiensemester_kurzbz
					)anwesend using(lehrveranstaltung_id, student_uid, studiensemester_kurzbz)
					LEFT JOIN(
						SELECT lehrveranstaltung_id, studiensemester_kurzbz, uid as student_uid, sum(einheiten) as summe
						FROM
							campus.tbl_anwesenheit a
							JOIN lehre.tbl_lehreinheit le using (lehreinheit_id)
							JOIN lehre.tbl_lehrveranstaltung lv using (lehrveranstaltung_id)
						WHERE
							anwesend = false AND studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."
						GROUP BY
							lehrveranstaltung_id, bezeichnung, uid, studiensemester_kurzbz
					)nichtanwesend using(lehrveranstaltung_id, student_uid, studiensemester_kurzbz)
					WHERE
						lehrveranstaltung_id > 0
			";

			if(!is_null($student_uid))
				$qry.=" AND student_uid=".$this->db_add_param($student_uid);
			if(!is_null($lehrveranstaltung_id))
				$qry.="	AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id);

			$qry.="group by
					vorname, nachname, wahlname, lehrveranstaltung_id, bezeichnung, gruppe, student_uid, anwesend.summe, nichtanwesend.summe
				)m";

			if($lehrveranstaltung_id != '')
				$qry.=" order by nachname, vorname ";
			elseif($student_uid != '')
				$qry.=" order by bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->bezeichnung = $row->bezeichnung;
				$obj->anwesend = $row->anwesend;
				$obj->nichtanwesend = $row->nichtanwesend;
				$obj->gesamtstunden = $row->gesamtstunden;

				$obj->erfassteanwesenheit = $row->anwesend+$row->nichtanwesend;
				if($row->gesamtstunden=='' || $obj->erfassteanwesenheit=='')
					$obj->prozent=100;
				else
					$obj->prozent = number_format(100-(100/$obj->gesamtstunden*$row->nichtanwesend),2);
				$obj->vorname = $row->vorname;
				$obj->wahlname = $row->wahlname;
				$obj->nachname = $row->nachname;
				$obj->uid = $row->uid;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Aendert die bestehende Anwesenheit
	 * @param $lehreinheit_id ID der Lehreinheit
	 * @param $datum Datum
	 * @param $uid UID des Studierenden
	 * @return boolean true ok, sonst false
	 */
	public function AnwesenheitToggle($lehreinheit_id, $datum, $uid)
	{
		if($this->AnwesenheitEntryExists($lehreinheit_id, $datum, $uid))
		{
			$qry = "UPDATE
						campus.tbl_anwesenheit
					SET anwesend= NOT anwesend
					WHERE
						lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
						AND datum=".$this->db_add_param($datum)."
						AND uid=".$this->db_add_param($uid);

			if($result = $this->db_query($qry))
			{
				if($this->db_affected_rows($result)>0)
					return true;
				else
				{
					$this->errormsg='Anwesenheitsliste wurde noch nicht erfasst';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			// Anwesenheitsliste wurde noch nicht erfasst. Eintrag neu anlegen

			// Einheiten ermitteln
			$qry = "SELECT
						distinct stunde
					FROM
						lehre.tbl_stundenplan
					WHERE
						lehreinheit_id=".$this->db_add_param($lehreinheit_id)."
						AND datum=".$this->db_add_param($datum);

			if($result = $this->db_query($qry))
			{
				if($anzahl = $this->db_num_rows($result))
				{
					$einheiten = $anzahl;
				}
			}
			if($einheiten>0)
			{
				$this->lehreinheit_id=$lehreinheit_id;
				$this->datum = $datum;
				$this->uid = $uid;
				$this->anwesend=true;
				$this->new=true;
				$this->einheiten=$einheiten;
				if($this->save())
					return true;
				else
				{
					$this->errormsg = 'Fehler beim Speichern der Daten';
					return true;
				}
			}
			else
			{
				$this->errormsg = 'Anzahl der Einheiten fuer diesen Tag konnte nicht ermittelt werden';
				return false;
			}
		}
	}

	/**
	 * Loescht eine Anwesenheit
	 * @param anwesenheit_id integer ID der Anwesenheit.
	 * @return boolean true wenn ok , false im fehlerfall
	 */
	public function delete($anwesenheit_id)
	{
		$qry = "DELETE FROM campus.tbl_anwesenheit WHERE anwesenheit_id=".$this->db_add_param($anwesenheit_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Anwesenheit';
			return false;
		}
	}
}
