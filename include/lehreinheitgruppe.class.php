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

class lehreinheitgruppe extends basis_db
{
	public $new;      // boolean
	public $lehreinheitgruppe = array(); // lehreinheitgruppe Objekt

	//Tabellenspalten
	public $lehreinheitgruppe_id;	//integer
	public $lehreinheit_id;		// integer
	public $studiengang_kz;		// integer
	public $semester;				// smalint
	public $verband;				// char(1)
	public $gruppe;				// char(1)
	public $gruppe_kurzbz;			// varchar(16)
	public $ext_id;				// bigint
	public $updateamum;			// timestamp
	public $updatevon;				// varchar(16)
	public $insertamum;			// timestamp
	public $insertvon;				// varchar(16)

	/**
	 * Konstruktor - Laedt optional eine LEGruppe
	 * @param $lehreinheitgruppe_id
	 */
	public function __construct($lehreinheitgruppe_id=null)
	{
		parent::__construct();

		if(!is_null($lehreinheitgruppe_id))
			$this->load($lehreinheitgruppe_id);
	}

	/**
	 * Laedt die LEGruppe
	 * @param lehreinheit_id
	 */
	function load($lehreinheitgruppe_id)
	{
		if(!is_numeric($lehreinheitgruppe_id))
		{
			$this->errormsg = 'Lehreinheitgruppe_id ist ungueltig';
			return false;
		}
		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$this->db_add_param($lehreinheitgruppe_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->lehreinheitgruppe_id = $row->lehreinheitgruppe_id;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;

				return true;
			}
			else
			{
				$this->errormsg = 'Es existiert kein Eintrag mit dieser ID';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->verband)>1)
		{
			$this->verband = 'Verband darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe)>1)
		{
			$this->gruppe = 'Gruppe darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert GruppeLE in die Datenbank
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

		if($new)
		{
			$qry = 'INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, insertamum, insertvon)
			        VALUES('.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
					$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
					$this->db_add_param($this->semester, FHC_INTEGER).','.
					$this->db_add_param($this->verband).','.
					$this->db_add_param($this->gruppe).','.
					$this->db_add_param($this->gruppe_kurzbz).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehreinheitgruppe SET'.
			       ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
			       ' semester='.$this->db_add_param($this->semester, FHC_INTEGER).','.
			       ' verband='.$this->db_add_param($this->verband).','.
			       ' gruppe='.$this->db_add_param($this->gruppe).','.
			       ' gruppe_kurzbz='.$this->db_add_param($this->gruppe_kurzbz).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE lehreinheitgruppe_id=".$this->db_add_param($this->lehreinheitgruppe_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der GruppeLE';
			return false;
		}
	}

	/**
	 * Sieht nach ob Gruppe schon zu dieser Lehreinheit
	 * zugeordnet ist.
	 * @param lehreinheit_id
	 *        studiengang_kz
	 *        semester
	 *        verband
	 *        gruppe
	 *        gruppe_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function exists($lehreinheit_id, $studiengang_kz, $semester, $verband, $gruppe, $gruppe_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);

		if($gruppe_kurzbz!='')
		{
			$qry .= " AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);
		}
		else
		{
			$qry .= " AND semester=".$this->db_add_param($semester, FHC_INTEGER);
			if($verband!='')
				$qry .= " AND verband=".$this->db_add_param($verband);
			if($gruppe!='')
				$qry .= " AND gruppe=".$this->db_add_param($gruppe);
		}
        $qry.=';';

		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen der Lehreinheitgruppen';
			return false;
		}
	}

	/**
	 * Liefert alle Gruppenzuordnungen zu einer
	 * Lehreinheit.
	 * @param lehreinheit_id Lehreinheit zu der
	 *        die Gruppen geladen werden sollen
	 * @return true wenn ok, false im fehlerfall
	 */
	public function getLehreinheitgruppe($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$leg_obj = new lehreinheitgruppe();

				$leg_obj->lehreinheitgruppe_id = $row->lehreinheitgruppe_id;
				$leg_obj->lehreinheit_id = $row->lehreinheit_id;
				$leg_obj->studiengang_kz = $row->studiengang_kz;
				$leg_obj->semester = $row->semester;
				$leg_obj->verband = $row->verband;
				$leg_obj->gruppe = $row->gruppe;
				$leg_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$leg_obj->updateamum = $row->updateamum;
				$leg_obj->updatevon = $row->updatevon;
				$leg_obj->insertamum = $row->insertamum;
				$leg_obj->insertvon = $row->insertvon;
				$leg_obj->ext_id = $row->ext_id;

				$this->lehreinheitgruppe[] = $leg_obj;
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
	 * Loescht die Zuornung Gruppe-Lehreinheit
	 * @param lehreinheigruppe_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im fehlerfall
	 */
	public function delete($lehreinheitgruppe_id)
	{
		if(!is_numeric($lehreinheitgruppe_id))
		{
			$this->errormsg = 'Lehreinheitgruppe_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry_del = "DELETE FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$this->db_add_param($lehreinheitgruppe_id, FHC_INTEGER).';';
		$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheitgruppe_id=".$this->db_add_param($lehreinheitgruppe_id, FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$sql_undo = "INSERT INTO lehre.tbl_lehreinheitgruppe ".
							"(lehreinheitgruppe_id, lehreinheit_id, studiengang_kz, semester, ".
							"verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon) ".
							"VALUES(".$this->db_add_param($row->lehreinheitgruppe_id, FHC_INTEGER).','.
							$this->db_add_param($row->lehreinheit_id, FHC_INTEGER).','.
							$this->db_add_param($row->studiengang_kz, FHC_INTEGER).','.
							$this->db_add_param($row->semester, FHC_INTEGER).','.
							$this->db_add_param($row->verband).','.
							$this->db_add_param($row->gruppe).','.
							$this->db_add_param($row->gruppe_kurzbz).','.
							$this->db_add_param($row->updateamum).','.
							$this->db_add_param($row->updatevon).','.
							$this->db_add_param($row->insertamum).','.
							$this->db_add_param($row->insertvon).');';

				$log = new log();
				$log->sql = $qry_del;
				$log->sqlundo = $sql_undo;
				$log->mitarbeiter_uid = get_uid();
				if($row->gruppe_kurzbz!='')
					$grp = $row->gruppe_kurzbz;
				else
				{
					$qry_stg = "SELECT UPPER(typ::varchar(1) || kurzbz) as kuerzel FROM public.tbl_studiengang WHERE studiengang_kz=".$this->db_add_param($row->studiengang_kz, FHC_INTEGER).';';
					$this->db_query($qry_stg);
					$row_stg = $this->db_fetch_object();
					$grp = $row_stg->kuerzel.$row->semester.$row->verband.$row->gruppe;
				}
				$log->beschreibung = "Gruppenzuteilung loeschen $grp - $row->lehreinheit_id";
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
						$this->errormsg = 'Fehler beim Loeschen';
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
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
			$this->errormsg = 'Fehler beim Lesen aus der Datenbank';
			return false;
		}
	}

	/**
	 * Prueft ob die Gruppe schon dieser Lehreinheit zugeordnet ist
	 */
	public function checkVorhanden()
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					lehre.tbl_lehreinheitgruppe
				WHERE
					lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER)." AND
					studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER);
		if($this->semester!='')
			$qry.=" AND semester=".$this->db_add_param($this->semester, FHC_INTEGER);
		else
			$qry.=" AND (semester='' OR semester is null)";

		if($this->verband!='')
			$qry.=" AND trim(verband)=".$this->db_add_param($this->verband);
		else
			$qry.=" AND (trim(verband)='' OR verband is null)";

		if($this->gruppe!='')
			$qry.=" AND	trim(gruppe)=".$this->db_add_param($this->gruppe);
		else
			$qry.=" AND (trim(gruppe)='' OR gruppe is null)";

		if($this->gruppe_kurzbz!='')
			$qry.=" AND	trim(gruppe_kurzbz)=".$this->db_add_param($this->gruppe_kurzbz);
		else
			$qry.= " AND (trim(gruppe_kurzbz)='' OR gruppe_kurzbz is null)";

        $qry.=';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Interner Fehler';
				return false;
			}
		}
		else
		{
			$this->errormsg='Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 * Prueft ob zu der Lehreinheit bereits eine direkte Gruppe zugeordnet ist
	 * @param $lehreinheit_id ID der lehreinheit
	 * @return true und objekt mit Gruppe falls vorhanden oder false im fehlerfall
	 */
	public function getDirectGroup($lehreinheit_id)
	{
		$qry = "
			SELECT
				tbl_lehreinheitgruppe.*
			FROM
				lehre.tbl_lehreinheitgruppe
				JOIN public.tbl_gruppe USING(gruppe_kurzbz)
			WHERE
				tbl_gruppe.direktinskription
				AND tbl_lehreinheitgruppe.lehreinheit_id=".$this->db_add_param($lehreinheit_id);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->lehreinheitgruppe_id = $row->lehreinheitgruppe_id;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;

				return true;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
