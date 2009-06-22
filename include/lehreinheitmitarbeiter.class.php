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
				WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
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
				$this->bismelden = ($row->bismelden=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
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
		
		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id'";
		if($mitarbeiter_uid!=null)
			$qry.=" AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		$qry .=" ORDER BY mitarbeiter_uid";

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
				$obj->bismelden = ($row->bismelden=='t'?true:false);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;

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

		if($new)
		{
			//ToDo ID entfernen
			$qry = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden,
			                                                stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, ext_id, insertamum, insertvon)
			        VALUES('.$this->addslashes($this->lehreinheit_id).','.
					$this->addslashes($this->mitarbeiter_uid).','.
					$this->addslashes($this->semesterstunden).','.
					$this->addslashes($this->planstunden).','.
					$this->addslashes($this->stundensatz).','.
					$this->addslashes($this->faktor).','.
					$this->addslashes($this->anmerkung).','.
					$this->addslashes($this->lehrfunktion_kurzbz).','.
					($this->bismelden?'true':'false').','.
					$this->addslashes($this->ext_id).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			if($this->mitarbeiter_uid_old=='')
				$this->mitarbeiter_uid_old = $this->mitarbeiter_uid;
				
			//Wenn der Lektor geaendert wird, dann wird insertamum und insertvon neu gesetzt
			//damit in den Chronjobs erkannt wird welche Lektoren an diesem Tag geaendert wurden.
			$setinsert='';
			if($this->mitarbeiter_uid_old!=$this->mitarbeiter_uid)
			{
				$setinsert=", insertamum='".date('Y-m-d H:i:s')."', insertvon=".$this->addslashes($this->updatevon);
			}

			$qry = 'UPDATE lehre.tbl_lehreinheitmitarbeiter SET'.
			       ' semesterstunden='.$this->addslashes($this->semesterstunden).','.
			       ' planstunden='.$this->addslashes($this->planstunden).','.
			       ' stundensatz='.$this->addslashes($this->stundensatz).','.
			       ' faktor='.$this->addslashes($this->faktor).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' lehrfunktion_kurzbz='.$this->addslashes($this->lehrfunktion_kurzbz).','.
			       ' mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).','.
			       ' bismelden='.($this->bismelden?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' ext_id = '.$this->addslashes($this->ext_id).
			       $setinsert.
			       " WHERE lehreinheit_id=".$this->addslashes($this->lehreinheit_id)." AND
			               mitarbeiter_uid=".$this->addslashes($this->mitarbeiter_uid_old).";";
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
				WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($uid)."'";
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
		$qry_del = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$lehreinheit_id' AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$undo = 'INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, semesterstunden, planstunden, '.
			            ' stundensatz, faktor, anmerkung, lehrfunktion_kurzbz, bismelden, ext_id, insertamum, insertvon, updateamum, updatevon)'.
				       	' VALUES('.$this->addslashes($row->lehreinheit_id).','.
						$this->addslashes($row->mitarbeiter_uid).','.
						$this->addslashes($row->semesterstunden).','.
						$this->addslashes($row->planstunden).','.
						$this->addslashes($row->stundensatz).','.
						$this->addslashes($row->faktor).','.
						$this->addslashes($row->anmerkung).','.
						$this->addslashes($row->lehrfunktion_kurzbz).','.
						($row->bismelden=='t'?'true':'false').','.
						$this->addslashes($row->ext_id).','.
						$this->addslashes($row->insertamum).','.
						$this->addslashes($row->insertvon).','.
						$this->addslashes($row->updateamum).','.
						$this->addslashes($row->updatevon).');';

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
}
?>