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
require_once(dirname(__FILE__).'/basis_db.class.php');

class lehreinheitmitarbeiter extends basis_db
{
	public $new;      // boolean
	public $lehreinheitmitarbeiter = array(); // lehreinheitmitarbeiter Objekt

	//Tabellenspalten
	public $lehreinheit_id;			// integer
	public $mitarbeiter_uid;		// varchar(16)
	public $mitarbeiter_uid_old;	// verwendet bei Update der UID
	public $semesterstunden;		// smalint
	public $planstunden;			// smalint
	public $stundensatz;			// numeric(6,2)
	public $faktor;					// numeric(2,1)
	public $anmerkung;				// varchar(256)
	public $lehrfunktion_kurzbz; 	// varchar(16)
	public $bismelden;				// boolean
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	public $ext_id; 				// bigint
	public $vertrag_id;

	public $result = array();

	/**
	 * Konstruktor - Laedt optional einee LEMitarbeiterzuordnung
	 * @param $lehreinheit_id
	 * @param $uid
	 */
	public function __construct($lehreinheit_id=null, $mitarbeiter_uid=null)
	{
		parent::__construct();

		if(!is_null($lehreinheit_id) && !is_null($mitarbeiter_uid))
			$this->load($lehreinheit_id, $mitarbeiter_uid);
	}

	/**
	 * Laedt die LEMitarbeiterzuordnung
	 * @param lehreinheit_id
	 * @param mitarbeiter_uid
	 * @return boolean
	 */
	public function load($lehreinheit_id, $mitarbeiter_uid=null)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter
				WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid).';';

        if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
				$this->semesterstunden = $row->semesterstunden;
				$this->planstunden = $row->planstunden;
				$this->stundensatz = $row->stundensatz;
				$this->faktor = $row->faktor;
				$this->anmerkung = $row->anmerkung;
				$this->bismelden = $this->db_parse_bool($row->bismelden);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->vertrag_id = $row->vertrag_id;
				$this->new=false;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler  beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Lektoren einer Lehreinheit
	 * @param lehreinheit_id
	 * @return array + true wenn ok / false im Fehlerfall
	 */
	public function getLehreinheitmitarbeiter($lehreinheit_id, $mitarbeiter_uid=null)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		if($mitarbeiter_uid!=null)
			$qry.=" AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid);
		$qry .=" ORDER BY mitarbeiter_uid;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new lehreinheitmitarbeiter();
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->planstunden = $row->planstunden;
				$obj->stundensatz = $row->stundensatz;
				$obj->faktor = $row->faktor;
				$obj->anmerkung = $row->anmerkung;
				$obj->bismelden = $this->db_parse_bool($row->bismelden);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
                $obj->vertrag_id = $row->vertrag_id;

				$this->lehreinheitmitarbeiter[] = $obj;
			}
			return true;
		}

		return false;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->stundensatz!='' && !is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muss eine gueltige Zahl sein';
			return false;
		}
		if($this->planstunden!='' && !is_numeric($this->planstunden))
		{
			$this->errormsg = 'Planstunden muss eine gueltige Zahl sein';
			return false;
		}

		if($this->semesterstunden!='' && !is_numeric($this->semesterstunden))
		{
			$this->errormsg = 'Semesterstunden muss eine gueltige Zahl sein';
			return false;
		}
		if($this->faktor!='' && !is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 255 Zeichen sein.';
			return false;
		}

		return true;
	}

	/**
	 * Speichert LEMitarbeiter in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if(!$this->planstunden=='')
			$this->planstunden = (int)mb_str_replace(',', '.', $this->planstunden);
		$this->semesterstunden = mb_str_replace(',', '.', $this->semesterstunden);

		if($new)
		{
			//Pruefen ob dieser Mitarbeiter schon zugeordnet ist
			$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid).';';
			if($this->db_query($qry))
			{
				if($this->db_num_rows()>0)
				{
					$this->errormsg='Der Mitarbeiter ist bereits zugeteilt!';
					return false;
				}
			}
			else
			{
				$this->errormsg='Fehler beim Pruefen der Zuordnung';
				return false;
			}

			//ToDo ID entfernen
			$qry = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden,
			                                                stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, insertamum, insertvon, vertrag_id)
			        VALUES('.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
					$this->db_add_param($this->mitarbeiter_uid).','.
					$this->db_add_param($this->semesterstunden).','.
					$this->db_add_param($this->planstunden, FHC_INTEGER).','.
					$this->db_add_param($this->stundensatz).','.
					$this->db_add_param($this->faktor).','.
					$this->db_add_param($this->anmerkung).','.
					$this->db_add_param($this->lehrfunktion_kurzbz).','.
					$this->db_add_param($this->bismelden, FHC_BOOLEAN).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->vertrag_id).');';
		}
		else
		{
			if($this->mitarbeiter_uid_old=='')
				$this->mitarbeiter_uid_old = $this->mitarbeiter_uid;

			//Wenn der Lektor geaendert wird, dann wird insertamum und insertvon neu gesetzt
			//damit in den Cronjobs erkannt wird welche Lektoren an diesem Tag geaendert wurden.
			$setinsert='';
			if($this->mitarbeiter_uid_old!=$this->mitarbeiter_uid)
			{
				$setinsert=", insertamum='".date('Y-m-d H:i:s')."', insertvon=".$this->db_add_param($this->updatevon);
			}

			$qry = 'UPDATE lehre.tbl_lehreinheitmitarbeiter SET'.
			       ' semesterstunden='.$this->db_add_param($this->semesterstunden).','.
			       ' planstunden='.$this->db_add_param($this->planstunden, FHC_INTEGER).','.
			       ' stundensatz='.$this->db_add_param($this->stundensatz).','.
			       ' faktor='.$this->db_add_param($this->faktor).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkung).','.
			       ' lehrfunktion_kurzbz='.$this->db_add_param($this->lehrfunktion_kurzbz).','.
			       ' mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).','.
			       ' bismelden='.$this->db_add_param($this->bismelden, FHC_BOOLEAN).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' vertrag_id='.$this->db_add_param($this->vertrag_id).
			       $setinsert.
			       " WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER)." AND
			               mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid_old).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der LEMitarbeiter:'.$this->db_last_error();
			return false;
		}
	}

	/**
	 * Prueft ob die Kombination Lehreinheit-Mitarbeiter
	 * bereits existiert
	 * @param $lehreinheit_id
	 * @param $uid
	 * @return true wenn die zuteilung existiert sonst false
	 */
	public function exists($lehreinheit_id, $uid)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter
				WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$this->db_add_param($uid).';';
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehreinheitmitarbeiterzuteilung';
			return false;
		}
	}

	/**
	 * Prueft ob ein Mitarbeiter einer LV zugeordnet ist
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @param $uid
	 * @return true wenn die Zuteilung existiert sonst false
	 */
	public function existsLV($lehrveranstaltung_id, $studiensemester_kurzbz=NULL,  $uid)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT
					1
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND mitarbeiter_uid=".$this->db_add_param($uid);

		if(!is_null($studiensemester_kurzbz))
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$qry .= ";";

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehreinheitmitarbeiterzuteilung';
			return false;
		}
	}

	/**
	 * Loescht die Zuteilung eines Mitarbeiters
	 * zu einer Lehreinheit
	 * @param $lehreinheit_id
	 * @param $mitarbeiter_uid
	 * @return true wenn ok, false im fehlerfall
	 */
	public function delete($lehreinheit_id, $mitarbeiter_uid)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}
		$qry_del = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid).";";
		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER)." AND mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$undo = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden, '.
			            ' stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, insertamum, insertvon, updateamum, updatevon)'.
				       	' VALUES('.$this->db_add_param($row->lehreinheit_id, FHC_INTEGER).','.
						$this->db_add_param($row->mitarbeiter_uid).','.
						$this->db_add_param($row->semesterstunden).','.
						$this->db_add_param($row->planstunden, FHC_INTEGER).','.
						$this->db_add_param($row->stundensatz).','.
						$this->db_add_param($row->faktor).','.
						$this->db_add_param($row->anmerkung).','.
						$this->db_add_param($row->lehrfunktion_kurzbz).','.
						$this->db_add_param($this->db_parse_bool($row->bismelden), FHC_BOOLEAN).','.
						$this->db_add_param($row->insertamum).','.
						$this->db_add_param($row->insertvon).','.
						$this->db_add_param($row->updateamum).','.
						$this->db_add_param($row->updatevon).');';

				$log = new log();
				$log->sqlundo = $undo;
				$log->sql = $qry_del;
				$log->mitarbeiter_uid = get_uid();
				$log->beschreibung = "Lektorzuteilung loeschen $mitarbeiter_uid - $lehreinheit_id";
				$this->db_query('BEGIN;');
				if($log->save(true))
				{
					if($this->db_query($qry_del))
					{
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'UNDO Eintrag konnte nicht erstellt werden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Daten';
			return false;
		}
	}

	/**
	 * Lädt alle Semester in der ein Lektor aktiv war.
	 *
	 * @param string $uid
	 * @return array
	 */
	public function getSemesterZuLektor($uid)
	{
		$qry = 'SELECT DISTINCT studiensemester_kurzbz, bezeichnung, start '
				. 'FROM lehre.tbl_lehreinheitmitarbeiter '
				. 'JOIN lehre.tbl_lehreinheit '
				. 'USING (lehreinheit_id) '
				. 'JOIN public.tbl_studiensemester '
				. 'USING (studiensemester_kurzbz) '
				. 'WHERE mitarbeiter_uid = ' . $this->db_add_param($uid)
				. ' ORDER BY start ';

		$result = $this->db_query($qry);
		$ret = array();

		while($row = $this->db_fetch_object($result))
		{
			$ret[$row->studiensemester_kurzbz] = $row->bezeichnung;
		}

		return $ret;
	}

	/**
	 * Lädt die Lehreinheiten eines Mitarbeiters für ein bestimmtes Semester.
	 *
	 * @param string $mitarbeiter_uid
	 * @param string $studiensemester_kurzbz
	 * @return array
	 */
	public function getLehreinheiten($mitarbeiter_uid, $studiensemester_kurzbz)
	{
		$qry = 'SELECT
					DISTINCT lehreinheit_id, lehrveranstaltung_id, lv_bezeichnung, lv_kurzbz, unr, lv_lehrform_kurzbz, stg_kurzbzlang,
					lv_semester, lehrform_kurzbz
				FROM
					campus.vw_lehreinheit
				WHERE
					mitarbeiter_uid = '.$this->db_add_param($mitarbeiter_uid).'
					AND studiensemester_kurzbz = '.$this->db_add_param($studiensemester_kurzbz).'
					ORDER BY lv_bezeichnung, unr ';

		$result = $this->db_query($qry);
		$ret = array();

		while($row = $this->db_fetch_object($result))
		{
			$ret[$row->lehreinheit_id] = $row;
		}

		return $ret;
	}

	/**
	 * Ladet Semesterwochenstunden-Summe eines Mitarbeiters eines Semesters.
	 * Nur bisrelevante SWS.
	 * @param String $uid
	 * @param String $studiensemester
	 * @return bool
	 */
	public function getLehreinheiten_SWS_BISMeldung($uid, $studiensemester)
	{
		$qry = '
			SELECT
				round(sum(semesterstunden) / 15, 2) AS sws
			FROM (
					 SELECT DISTINCT lehreinheit_id, studiensemester_kurzbz, mitarbeiter_uid, semesterstunden
					 FROM lehre.tbl_lehreinheitmitarbeiter lema
						  JOIN public.tbl_mitarbeiter ma USING (mitarbeiter_uid)
						  JOIN public.tbl_benutzer ON (mitarbeiter_uid = uid)
						  JOIN public.tbl_person USING (person_id)
						  JOIN bis.tbl_bisverwendung USING (mitarbeiter_uid)
						  JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
						  JOIN public.tbl_studiensemester ss USING (studiensemester_kurzbz)
					 WHERE mitarbeiter_uid = '. $this->db_add_param($uid).'
					   AND lema.bismelden
					   AND studiensemester_kurzbz = '. $this->db_add_param($studiensemester).'
				 ) tbl_semesterstunden
		';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->result[] = $row->sws;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Ladet Semesterwochenstunden-Summe gruppiert nach Studiengang und Studiensemester.
	 * Es werden die Studiensemester herangezogen, die im Zeitraum zwischen beginn und ende beginnen.
	 * @param String $uid
	 * @param String $beginn
	 * @param String $ende
	 * @return bool
	 */
	public function get_SWS_groupByStg($uid, $beginn, $ende)
	{
		$beginn = new DateTime($beginn);
		$ende = new DateTime($ende);

		$qry = '
			WITH semester_sws_tbl AS (
				SELECT DISTINCT lehreinheit_id, studiensemester_kurzbz, lema.semesterstunden, stg.studiengang_kz
				FROM lehre.tbl_lehreinheitmitarbeiter lema
					JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
					JOIN lehre.tbl_studienplan_lehrveranstaltung USING (lehrveranstaltung_id)
					JOIN lehre.tbl_studienplan USING (studienplan_id)
					JOIN lehre.tbl_studienordnung sto USING (studienordnung_id)
					JOIN public.tbl_studiengang stg ON stg.studiengang_kz = sto.studiengang_kz
					JOIN public.tbl_studiensemester ss USING (studiensemester_kurzbz)
				WHERE mitarbeiter_uid =  '. $this->db_add_param($uid). '
					 AND (
					 	ss.start BETWEEN
					 	'. $this->db_add_param($beginn->format('Y-m-d')). ' AND
					 	'. $this->db_add_param($ende->format('Y-m-d')). ')
				-- nur lehre, die bisgemeldet wird
					AND lema.bismelden
				-- keine lehreinheiten ohne semesterstunden
				AND lema.semesterstunden != 0
			)

			SELECT
				studiengang_kz,
				studiensemester_kurzbz,
				sum(semesterstunden)				AS summe,
				round(sum(semesterstunden) / 15, 2)	AS sws
			FROM
				semester_sws_tbl
			GROUP BY
				studiengang_kz,
				studiensemester_kurzbz
			ORDER BY
				studiengang_kz;
		';

		if ($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new StdClass();
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->sws = $row->sws;
				$this->result []= $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt die Lektoren einer Lehrveranstaltung in einem Studiensemester
	 * @param lehrveranstaltung_id
	 * @param studiensemester_kurzbz
	 * @param integer lehreinheit_id Optional Lehreinheit_id
	 * @return array + true wenn ok / false im Fehlerfall
	 */
	public function getMitarbeiterLV($lehrveranstaltung_id, $studiensemester_kurzbz, $lehreinheit_id=null)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}

		$qry = "SELECT DISTINCT
					vw_mitarbeiter.uid, vorname, nachname, titelpre, titelpost
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN campus.vw_mitarbeiter ON(tbl_lehreinheitmitarbeiter.mitarbeiter_uid=vw_mitarbeiter.uid)
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!is_null($lehreinheit_id))
			$qry .=" AND tbl_lehreinheit.lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		$qry .=" ORDER BY nachname, vorname;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new stdClass();
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->uid = $row->uid;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;

				$this->result[] = $obj;
			}
			return true;
		}

		return false;
	}

}
